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

        return view('dashboard', compact('allTasks', 'assignedTasks', 'createdTasks', 'metrics'));
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
        ]);

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
}