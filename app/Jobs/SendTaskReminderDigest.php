<?php

namespace App\Jobs;

use App\Mail\TaskReminderDigestMail;
use App\Models\Task;
use App\Models\TaskReminderDelivery;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Throwable;

class SendTaskReminderDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @param array<int, int> $taskIds */
    public function __construct(
        public int $userId,
        public string $reminderDate,
        public array $taskIds,
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user?->is_active) {
            return;
        }

        $tasks = Task::query()
            ->with(['project.leader', 'assignees'])
            ->whereKey($this->taskIds)
            ->pendingReminder($this->reminderDate)
            ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
            ->get();

        if ($tasks->isEmpty()) {
            return;
        }

        Mail::to($user)->send(new TaskReminderDigestMail($user, $tasks, Carbon::parse($this->reminderDate)));

        TaskReminderDelivery::query()
            ->where('user_id', $user->id)
            ->whereDate('reminder_date', $this->reminderDate)
            ->whereIn('task_id', $this->taskIds)
            ->update(['sent_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        TaskReminderDelivery::query()
            ->where('user_id', $this->userId)
            ->whereDate('reminder_date', $this->reminderDate)
            ->whereIn('task_id', $this->taskIds)
            ->whereNull('sent_at')
            ->delete();
    }
}
