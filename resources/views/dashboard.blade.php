<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('TaskFlow - Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 1. Tarjetas de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <p class="text-xs font-bold uppercase text-gray-500">Pendientes</p>
                    <p class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $metrics['pending'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <p class="text-xs font-bold uppercase text-gray-500">En Proceso</p>
                    <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ $metrics['in_progress'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-green-500">
                    <p class="text-xs font-bold uppercase text-gray-500">Completadas</p>
                    <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $metrics['completed'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-red-500">
                    <p class="text-xs font-bold uppercase text-gray-500">Vencidas</p>
                    <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $metrics['overdue'] ?? 0 }}</p>
                </div>
            </div>

            <!-- 2. Formulario de Creación (Oculto para Lectores) -->
            @if(!Auth::user()->isLector())
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Nueva Tarea</h3>
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Título *</label>
                            <input type="text" name="title" required class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asignar Responsable</label>
                            <select name="assigned_to" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="">-- Sin asignar --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">
                                        {{ $u->name }} ({{ $u->role->name ?? 'Sin Rol' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select name="status" required class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="pending">Pendiente</option>
                                <option value="in_progress">En Proceso</option>
                                <option value="completed">Completada</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prioridad *</label>
                            <select name="priority" required class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="low">Baja</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Límite</label>
                            <input type="date" name="due_date" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" rows="2" class="mt-1 w-full rounded-md border-gray-300 text-sm" placeholder="Detalles de la tarea..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm transition">
                            + Crear Tarea
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- 3. Tabla de Tareas y Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                    <h3 class="text-lg font-bold text-gray-800">Listado de Tareas</h3>

                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2">
                        <select name="status" class="text-xs rounded-md border-gray-300">
                            <option value="">-- Todos los Estados --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencidas</option>
                        </select>

                        <select name="priority" class="text-xs rounded-md border-gray-300">
                            <option value="">-- Todas las Prioridades --</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                        </select>

                        <button type="submit" class="px-3 py-1 bg-gray-800 text-white text-xs rounded-md">Filtrar</button>
                        <a href="{{ route('dashboard') }}" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded-md">Limpiar</a>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Título</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Creador / Responsable</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Prioridad</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Fecha Límite</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900">{{ $task->title }}</p>
                                        @if($task->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($task->description, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div><span class="font-bold">Creador:</span> {{ $task->user->name ?? 'N/A' }}</div>
                                        <div class="mt-0.5"><span class="font-bold">Responsable:</span> 
                                            @if($task->assignedUser)
                                                <span class="text-indigo-600 font-semibold">{{ $task->assignedUser->name }}</span>
                                            @else
                                                <span class="text-gray-400 italic">Sin asignar</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $task->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ $task->status === 'pending' ? 'Pendiente' : ($task->status === 'in_progress' ? 'En Proceso' : 'Completada') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $task->priority === 'medium' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $task->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($task->priority == 'high' ? 'Alta' : ($task->priority == 'medium' ? 'Media' : 'Baja')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if(!Auth::user()->isLector())
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 font-semibold text-xs">Editar</a>
                                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 font-semibold text-xs">Eliminar</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Solo Lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        No hay tareas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>