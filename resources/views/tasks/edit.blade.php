<<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Tarea') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <form action="{{ route('tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Título *</label>
                        <input type="text" name="title" value="{{ old('title', $task->title) }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description', $task->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                                <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completada</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prioridad *</label>
                            <select name="priority" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asignar Responsable</label>
                            <select name="assigned_to" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="">-- Sin asignar --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('assigned_to', $task->assigned_to) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->role->name ?? 'Sin Rol' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Límite</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $task->due_date) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm transition">
                            Actualizar Tarea
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>