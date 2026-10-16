<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Usuarios y Roles') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asignar Rol</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @foreach($users as $u)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $u->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $u->email }}</td>
                                <td class="px-6 py-4">
                                    @if($u->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('users.update-role', $u) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="role_id" class="text-xs rounded-md border-gray-300">
                                            <option value="">-- Sin Rol --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $u->role_id == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                                            Guardar
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('users.toggle-status', $u) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold text-{{ $u->is_active ? 'red' : 'green' }}-600 hover:underline">
                                            {{ $u->is_active ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>