<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <div class="flex items-center gap-3">
                <a href="{{ route('tasks.board') }}" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-sm transition">
                    Ver Tablero Kanban
                </a>
                @if(!Auth::user()->isLector())
                    <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow transition">
                        + Nueva Tarea
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        <!-- Tarjetas de Métricas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase">Pendientes</p>
                <p class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $metrics['pending'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase">En Proceso</p>
                <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ $metrics['in_progress'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase">Completadas</p>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $metrics['completed'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase">Vencidas</p>
                <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $metrics['overdue'] }}</p>
            </div>
        </div>

        <!-- Calendario del Mes y Próximas Tareas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Calendario Mensual (2 columnas) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        Calendario de Tareas — {{ now()->translatedFormat('F Y') }}
                    </h3>
                </div>

                @php
                    $startOfMonth = now()->startOfMonth();
                    $daysInMonth = now()->daysInMonth;
                    $firstDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Lun) a 7 (Dom)
                @endphp

                <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-gray-500 mb-2">
                    <div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    @for($i = 1; $i < $firstDayOfWeek; $i++)
                        <div class="h-20 bg-gray-50 rounded-lg"></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $currentDate = now()->startOfMonth()->addDays($day - 1)->toDateString();
                            $dayTasks = $calendarTasks->get($currentDate, collect());
                            $isToday = $currentDate === now()->toDateString();
                        @endphp
                        <div class="h-20 p-1.5 rounded-lg border {{ $isToday ? 'border-indigo-500 bg-indigo-50/40' : 'border-gray-100 bg-white' }} overflow-y-auto">
                            <div class="text-xs font-bold {{ $isToday ? 'text-indigo-700' : 'text-gray-700' }} mb-1">
                                {{ $day }}
                            </div>
                            @foreach($dayTasks as $t)
                                <a href="{{ route('tasks.show', $t) }}" class="block truncate text-[10px] px-1.5 py-0.5 rounded mb-0.5 font-medium
                                    {{ $t->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($t->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $t->title }}
                                </a>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Próximas Tareas (1 columna) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Próximas Tareas</h3>
                <div class="space-y-3">
                    @forelse($upcomingTasks as $task)
                        <div class="p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('tasks.show', $task) }}" class="font-bold text-sm text-gray-800 hover:text-indigo-600 truncate">
                                    {{ $task->title }}
                                </a>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                    {{ $task->priority === 'high' ? 'bg-red-100 text-red-700' : ($task->priority === 'medium' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Vence: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">No tienes tareas pendientes.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>