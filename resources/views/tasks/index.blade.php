<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('TaskFlow - Gestión de Tareas') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Mensajes de éxito -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- 1. PANEL CON METRICAS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs font-bold uppercase text-gray-500">Pendientes</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-xs font-bold uppercase text-gray-500">En Progreso</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $inProgressCount }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-bold uppercase text-gray-500">Completadas</p>
                <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $completedCount }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-xs font-bold uppercase text-gray-500">Vencidas</p>
                <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $overdueCount }}</p>
            </div>
        </div>

        <!-- 2. FORMULARIO PARA CREAR TAREA -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Nueva Tarea</h3>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Título *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prioridad *</label>
                        <select name="priority" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Límite</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado *</label>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Detalles de la tarea...">
                    </div>

                    <div class="flex items-end justify-end">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm transition">
                            + Crear Tarea
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 3. FILTROS -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-wrap items-center justify-between gap-4">
            <span class="text-sm font-bold text-gray-700">Filtrar Tareas:</span>
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:ring-indigo-500">
                    <option value="">-- Todos los Estados --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                </select>

                <select name="priority" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:ring-indigo-500">
                    <option value="">-- Todas las Prioridades --</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                </select>

                @if(request('status') || request('priority'))
                    <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:underline">Limpiar Filtros</a>
                @endif
            </form>
        </div>

        <!-- 4. LISTADO / TABLA DE TAREAS -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Tarea</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Fecha Límite</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900 {{ $task->status == 'completed' ? 'line-through text-gray-400' : '' }}">{{ $task->title }}</p>
                                    @if($task->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $task->description }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $task->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $task->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $task->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ $task->status == 'pending' ? 'Pendiente' : ($task->status == 'in_progress' ? 'En Progreso' : 'Completada') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $task->priority == 'high' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $task->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $task->priority == 'low' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ ucfirst($task->priority == 'high' ? 'Alta' : ($task->priority == 'medium' ? 'Media' : 'Baja')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($task->due_date)
                                        <span class="{{ $task->due_date < now()->toDateString() && $task->status != 'completed' ? 'text-red-600 font-bold' : '' }}">
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Sin fecha</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Editar</a>
                                    
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar esta tarea?')" class="text-red-600 hover:text-red-900 font-medium text-xs">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                    No hay tareas registradas. ¡Crea una nueva arriba!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>