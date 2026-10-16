<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('TaskFlow - Panel de Control') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Rol activo: <span class="font-semibold text-indigo-600">{{ Auth::user()->role->name ?? 'Usuario' }}</span>
                </p>
            </div>

            <!-- Botón de Nueva Tarea (Solo Admin y Editor) -->
            @if(!Auth::user()->isLector())
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nueva Tarea
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alerta de éxito -->
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 1. Tarjetas de Métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Pendientes</p>
                    <p class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $metrics['pending'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">En Proceso</p>
                    <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ $metrics['in_progress'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Completadas</p>
                    <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $metrics['completed'] ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-red-500">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Vencidas</p>
                    <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $metrics['overdue'] ?? 0 }}</p>
                </div>
            </div>

            <!-- 2. Filtros de Tareas -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="text-base font-bold text-gray-800">Filtros de Tareas</h3>
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2 w-full md:w-auto">
                    <select name="status" class="text-xs rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Todos los Estados --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completadas</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencidas</option>
                    </select>

                    <select name="priority" class="text-xs rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Todas las Prioridades --</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-md transition">Filtrar</button>
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-md transition">Limpiar</a>
                </form>
            </div>

            <!-- 3. LISTAS DE TAREAS -->

            @if(Auth::user()->isEditor())
                <!-- VISTA EXCLUSIVA PARA EDITOR: TABLAS SEPARADAS -->

                <!-- A. Tareas Asignadas al Editor -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                    <h3 class="text-lg font-bold text-indigo-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Tareas Asignadas a Mí
                    </h3>
                    @include('tasks.partials.table', ['tasksList' => $assignedTasks])
                </div>

                <!-- B. Tareas Creadas por el Editor -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Tareas Creadas por Mí
                    </h3>
                    @include('tasks.partials.table', ['tasksList' => $createdTasks])
                </div>

            @else
                <!-- VISTA PARA ADMINISTRADOR Y LECTOR: UNICA TABLA -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Listado de Tareas General</h3>
                    @include('tasks.partials.table', ['tasksList' => $allTasks])
                </div>
            @endif

        </div>
    </div>
</x-app-layout>