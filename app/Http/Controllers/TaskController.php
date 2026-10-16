<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
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

        // 2. Filtros dinámicos de la tabla (incluyendo la opción de 'vencidas')
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('due_date', '<', $today)
                      ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->with(['user', 'assignedUser'])->latest()->get();

        // 3. Obtener todos los usuarios activos para asignar responsables
        $users = User::where('is_active', true)->get();

        // 4. Métricas precisas y sin solapamiento
        $baseMetrics = Task::query();
        if (!$user->isAdmin()) {
            $baseMetrics->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        $metrics = [
            // Pendientes a tiempo (no vencidas) o sin fecha
            'pending' => (clone $baseMetrics)->where('status', 'pending')
                ->where(function ($q) use ($today) {
                    $q->whereNull('due_date')
                      ->orWhere('due_date', '>=', $today);
                })->count(),

            // En proceso a tiempo (no vencidas) o sin fecha
            'in_progress' => (clone $baseMetrics)->where('status', 'in_progress')
                ->where(function ($q) use ($today) {
                    $q->whereNull('due_date')
                      ->orWhere('due_date', '>=', $today);
                })->count(),

            // Completadas (sin importar la fecha)
            'completed' => (clone $baseMetrics)->where('status', 'completed')->count(),

            // Vencidas estrictas (fecha menor a hoy y no completadas)
            'overdue' => (clone $baseMetrics)->where('due_date', '<', $today)
                ->where('status', '!=', 'completed')->count(),
        ];

        return view('dashboard', compact('tasks', 'users', 'metrics'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->isLector()) {
            abort(403, 'Los usuarios Lectores no pueden crear tareas.');
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