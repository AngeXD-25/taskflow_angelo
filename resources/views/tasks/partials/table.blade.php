<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Título</th>
                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Creador / Responsable</th>
                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Prioridad</th>
                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($tasksList as $task)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-900">{{ $task->title }}</p>
                        @if($task->description)
                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($task->description, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs">
                        <div><span class="font-semibold text-gray-600">Creador:</span> {{ $task->user->name ?? 'N/A' }}</div>
                        <div class="mt-0.5"><span class="font-semibold text-gray-600">Responsable:</span> 
                            @if($task->assignedUser)
                                <span class="text-indigo-600 font-bold">{{ $task->assignedUser->name }}</span>
                            @else
                                <span class="text-gray-400 italic">Sin asignar</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full 
                            {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $task->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ $task->status === 'pending' ? 'Pendiente' : ($task->status === 'in_progress' ? 'En Proceso' : 'Completada') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full 
                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $task->priority === 'medium' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $task->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($task->priority == 'high' ? 'Alta' : ($task->priority == 'medium' ? 'Media' : 'Baja')) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 font-medium">
                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @if(!Auth::user()->isLector())
                            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Editar</a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs">Eliminar</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400 italic">Solo Lectura</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">
                        No hay tareas registradas en esta sección.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>