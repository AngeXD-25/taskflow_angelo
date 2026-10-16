<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Administrador',
            'description' => 'Acceso total: gestión de usuarios y perfiles',
            'is_active' => true,
        ]);

        Role::updateOrCreate(['slug' => 'editor'], [
            'name' => 'Editor',
            'description' => 'Puede crear, ver y editar tareas',
            'is_active' => true,
        ]);

        Role::updateOrCreate(['slug' => 'lector'], [
            'name' => 'Lector',
            'description' => 'Solo puede visualizar tareas asignadas o creadas',
            'is_active' => true,
        ]);
    }
}