<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Muestra el Dashboard con métricas y el listado formateado según el rol.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();


        $baseQuery = Task::query();
        if (!$user->isAdmin()) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        // Métricas del Dashboard
        $metrics = [
            'pending' => (clone $baseQuery)->where('status', 'pending')
                ->where(fn($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $today))->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')
                ->where(fn($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $today))->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'overdue' => (clone $baseQuery)->where('due_date', '<', $today)
                ->where('status', '!=', 'completed')->count(),
        ];

        // Tareas con fecha límite para el calendario del mes actual
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $calendarTasks = (clone $baseQuery)
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->with(['assignedUser'])
            ->get()
            ->groupBy('due_date');

        // Próximas tareas pendientes o en proceso
        $upcomingTasks = (clone $baseQuery)
            ->where('status', '!=', 'completed')
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->take(5)
            ->get();




        // 1. Consulta base según el rol
        $query = Task::query();

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        // 2. Filtro dinámico por Estado
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('due_date', '<', $today)
                      ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filtro por Prioridad
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $allTasks = $query->with(['user', 'assignedUser'])->latest()->get();

        // 3. Si es Editor, separar las tareas en dos grupos: asignadas a él vs creadas por él
        if ($user->isEditor()) {
            $assignedTasks = $allTasks->filter(fn($t) => $t->assigned_to === $user->id);
            $createdTasks  = $allTasks->filter(fn($t) => $t->user_id === $user->id);
        } else {
            $assignedTasks = collect();
            $createdTasks  = $allTasks;
        }

        // 4. Métricas precisas y sin duplicidad
        $baseMetrics = Task::query();
        if (!$user->isAdmin()) {
            $baseMetrics->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        $metrics = [
            'pending' => (clone $baseMetrics)->where('status', 'pending')
                ->where(function ($q) use ($today) {
                    $q->whereNull('due_date')
                      ->orWhere('due_date', '>=', $today);
                })->count(),

            'in_progress' => (clone $baseMetrics)->where('status', 'in_progress')
                ->where(function ($q) use ($today) {
                    $q->whereNull('due_date')
                      ->orWhere('due_date', '>=', $today);
                })->count(),

            'completed' => (clone $baseMetrics)->where('status', 'completed')->count(),

            'overdue' => (clone $baseMetrics)->where('due_date', '<', $today)
                ->where('status', '!=', 'completed')->count(),
        ];

        return view('dashboard', compact('allTasks', 'assignedTasks', 'createdTasks', 'metrics', 'calendarTasks', 'upcomingTasks'));
    }


    public function board()
    {
        $user = Auth::user();

        $query = Task::with(['user', 'assignedUser'])->latest();

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        $tasks = $query->get();

        // Agrupar tareas por columna Kanban
        $pendingTasks = $tasks->where('status', 'pending');
        $inProgressTasks = $tasks->where('status', 'in_progress');
        $completedTasks = $tasks->where('status', 'completed');

        return view('tasks.board', compact('pendingTasks', 'inProgressTasks', 'completedTasks'));
    }





    /**
     * Muestra el formulario para crear una nueva tarea en una vista dedicada.
     */
    public function create()
    {
        if (Auth::user()->isLector()) {
            abort(403, 'Los usuarios Lectores no tienen permiso para crear tareas.');
        }

        $users = User::where('is_active', true)->get();

        return view('tasks.create', compact('users'));
    }

    /**
     * Almacena una nueva tarea en la base de datos.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isLector()) {
            abort(403, 'Los usuarios Lectores no tienen permiso para crear tareas.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        Auth::user()->tasks()->create($validated);

        return redirect()->route('dashboard')->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Formulario de edición de tarea.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();

        if ($user->isLector()) {
            abort(403, 'Los usuarios Lectores no pueden editar tareas.');
        }

        if (!$user->isAdmin() && $task->user_id !== $user->id && $task->assigned_to !== $user->id) {
            abort(403);
        }

        $users = User::where('is_active', true)->get();

        return view('tasks.edit', compact('task', 'users'));
    }

    /**
     * Actualiza la tarea.
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($user->isLector()) {
            abort(403);
        }

        if (!$user->isAdmin() && $task->user_id !== $user->id && $task->assigned_to !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'status'      => 'required|in:pending,in_progress,completed',
        'priority'    => 'required|in:low,medium,high',
        'due_date'    => 'nullable|date',
        'assigned_to' => 'nullable|exists:users,id',
        'attachment'  => 'nullable|file|mimes:docx,xlsx,pptx,pdf,jpg,png,gif|max:2048', // Validación estricta
    ]);

        // Manejo de subida de archivos
            if ($request->hasFile('attachment')) {
        // Si ya tenía un archivo anterior, lo borramos de storage
             if ($task->attachment && Storage::disk('public')->exists($task->attachment)) {
                 Storage::disk('public')->delete($task->attachment);
            }
        // Guardamos el nuevo archivo en storage/app/public/tasks
        $validated['attachment'] = $request->file('attachment')->store('tasks', 'public');
    }

        $task->update($validated);

        return redirect()->route('dashboard')->with('success', 'Tarea actualizada exitosamente.');
    }

    /**
     * Elimina la tarea.
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();

        if ($user->isLector()) {
            abort(403);
        }

        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('dashboard')->with('success', 'Tarea eliminada exitosamente.');
    }

    public function toggleStatus(Request $request, Task $task)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->user_id !== $user->id && $task->assigned_to !== $user->id) {
            abort(403, 'No tienes permiso para modificar el estado de esta tarea.');
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'El estado de la tarea ha sido actualizado.');
    }    

}