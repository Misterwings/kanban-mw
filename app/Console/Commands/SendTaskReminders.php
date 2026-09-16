<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskReminderDigest;
use App\Models\Task;
use App\Models\TaskReminderDelivery;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders {--date= : Fecha YYYY-MM-DD para pruebas}';

    protected $description = 'Envía recordatorios diarios agrupados de tareas pendientes.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'), config('kanban.timezone'))->toDateString()
            : Carbon::now(config('kanban.timezone'))->toDateString();

        $tasks = Task::query()
            ->with(['project', 'assignees'])
            ->pendingReminder($date)
            ->get();

        $dispatched = 0;

        $tasks
            ->flatMap(fn (Task $task) => $task->assignees->map(fn ($user) => [
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]))
            ->groupBy('user_id')
            ->each(function ($assignments, $userId) use ($date, &$dispatched): void {
                $taskIds = $assignments->pluck('task_id')->unique()->values()->all();
                $newTaskIds = [];

                foreach ($taskIds as $taskId) {
                    $delivery = TaskReminderDelivery::query()
                        ->where('task_id', $taskId)
                        ->where('user_id', $userId)
                        ->whereDate('reminder_date', $date)
                        ->first();

                    if ($delivery) {
                        continue;
                    }

                    try {
                        TaskReminderDelivery::create([
                            'task_id' => $taskId,
                            'user_id' => $userId,
                            'reminder_date' => $date,
                        ]);
                        $newTaskIds[] = $taskId;
                    } catch (UniqueConstraintViolationException) {
                        // Another scheduler run created this delivery first.
                    }
                }

                if ($newTaskIds !== []) {
                    SendTaskReminderDigest::dispatch((int) $userId, $date, $newTaskIds);
                    $dispatched++;
                }
            });

        $this->info("{$dispatched} recordatorio(s) programado(s) para {$date}.");

        return self::SUCCESS;
    }
}
