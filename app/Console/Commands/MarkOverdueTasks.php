<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class MarkOverdueTasks extends Command
{
    protected $signature = 'tasks:mark-overdue';
    protected $description = 'Actualiza el estado de las tareas cuya fecha límite pasó';

    public function handle()
    {
        $count = Task::where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'completed')
            ->count();

        $this->info("Se detectaron {$count} tareas vencidas.");
    }
}