<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Tablero Kanban de Tareas') }}
            </h2>

            @if(!Auth::user()->isLector())
                <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow transition">
                    + Nueva Tarea
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

            <!-- COLUMNA 1: PENDIENTE -->
            <div class="bg-gray-100/80 p-4 rounded-xl border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                        Pendientes
                    </h3>
                    <span class="text-xs font-bold bg-white px-2.5 py-0.5 rounded-full text-gray-600 shadow-sm">
                        {{ $pendingTasks->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($pendingTasks as $task)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                    {{ $task->priority === 'high' ? 'bg-red-100 text-red-700' : ($task->priority === 'medium' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $task->priority === 'high' ? 'Alta' : ($task->priority === 'medium' ? 'Media' : 'Baja') }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                                </span>
                            </div>

                            <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $task->title }}</h4>
                            @if($task->description)
                                <p class="text-xs text-gray-500 mb-3">{{ Str::limit($task->description, 80) }}</p>
                            @endif

                            <div class="text-[11px] text-gray-500 mb-3">
                                <strong>Responsable:</strong> {{ $task->assignedUser->name ?? 'Sin asignar' }}
                            </div>

                            @if(Auth::user()->isAdmin() || Auth::user()->isEditor() || Auth::user()->isLector() || Auth::id() === $task->assigned_to)
                                <form action="{{ route('tasks.toggle-status', $task) }}" method="POST" class="pt-2 border-t border-gray-100">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 py-1.5 focus:ring-indigo-500">
                                        <option value="pending" selected>Pendiente</option>
                                        <option value="in_progress">Mover a En Proceso</option>
                                        <option value="completed">Mover a Completada</option>
                                    </select>
                                </form>
                            @endif

                            <div class="mt-3 flex items-center justify-end gap-3 text-xs">
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 font-semibold hover:underline">Ver</a>
                                @if(!Auth::user()->isLector())
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 font-semibold hover:underline">Editar</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Sin tareas pendientes</p>
                    @endforelse
                </div>
            </div>

            <!-- COLUMNA 2: EN PROCESO -->
            <div class="bg-gray-100/80 p-4 rounded-xl border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        En Proceso
                    </h3>
                    <span class="text-xs font-bold bg-white px-2.5 py-0.5 rounded-full text-gray-600 shadow-sm">
                        {{ $inProgressTasks->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($inProgressTasks as $task)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                    {{ $task->priority === 'high' ? 'bg-red-100 text-red-700' : ($task->priority === 'medium' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $task->priority === 'high' ? 'Alta' : ($task->priority === 'medium' ? 'Media' : 'Baja') }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                                </span>
                            </div>

                            <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $task->title }}</h4>
                            @if($task->description)
                                <p class="text-xs text-gray-500 mb-3">{{ Str::limit($task->description, 80) }}</p>
                            @endif

                            <div class="text-[11px] text-gray-500 mb-3">
                                <strong>Responsable:</strong> {{ $task->assignedUser->name ?? 'Sin asignar' }}
                            </div>

                            @if(Auth::user()->isAdmin() || Auth::user()->isEditor() || Auth::user()->isLector() || Auth::id() === $task->assigned_to)
                                <form action="{{ route('tasks.toggle-status', $task) }}" method="POST" class="pt-2 border-t border-gray-100">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 py-1.5 focus:ring-indigo-500">
                                        <option value="pending">Mover a Pendiente</option>
                                        <option value="in_progress" selected>En Proceso</option>
                                        <option value="completed">Mover a Completada</option>
                                    </select>
                                </form>
                            @endif

                            <div class="mt-3 flex items-center justify-end gap-3 text-xs">
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 font-semibold hover:underline">Ver</a>
                                @if(!Auth::user()->isLector())
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 font-semibold hover:underline">Editar</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Sin tareas en proceso</p>
                    @endforelse
                </div>
            </div>

            <!-- COLUMNA 3: COMPLETADA -->
            <div class="bg-gray-100/80 p-4 rounded-xl border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Completadas
                    </h3>
                    <span class="text-xs font-bold bg-white px-2.5 py-0.5 rounded-full text-gray-600 shadow-sm">
                        {{ $completedTasks->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($completedTasks as $task)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 opacity-90">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                    Completada
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                                </span>
                            </div>

                            <h4 class="font-bold text-gray-800 text-sm mb-1 line-through">{{ $task->title }}</h4>
                            @if($task->description)
                                <p class="text-xs text-gray-500 mb-3">{{ Str::limit($task->description, 80) }}</p>
                            @endif

                            <div class="text-[11px] text-gray-500 mb-3">
                                <strong>Responsable:</strong> {{ $task->assignedUser->name ?? 'Sin asignar' }}
                            </div>

                            @if(Auth::user()->isAdmin() || Auth::user()->isEditor() || Auth::user()->isLector() || Auth::id() === $task->assigned_to)
                                <form action="{{ route('tasks.toggle-status', $task) }}" method="POST" class="pt-2 border-t border-gray-100">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 py-1.5 focus:ring-indigo-500">
                                        <option value="pending">Mover a Pendiente</option>
                                        <option value="in_progress">Mover a En Proceso</option>
                                        <option value="completed" selected>Completada</option>
                                    </select>
                                </form>
                            @endif

                            <div class="mt-3 flex items-center justify-end gap-3 text-xs">
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 font-semibold hover:underline">Ver</a>
                                @if(!Auth::user()->isLector())
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 font-semibold hover:underline">Editar</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Sin tareas completadas</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>