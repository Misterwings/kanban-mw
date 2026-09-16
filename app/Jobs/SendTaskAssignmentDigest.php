<?php

namespace App\Jobs;

use App\Mail\TaskAssignmentDigestMail;
use App\Models\Task;
use App\Models\TaskAssignmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignmentDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $notificationId)
    {
    }

    public function handle(): void
    {
        $notification = TaskAssignmentNotification::with('user')->find($this->notificationId);

        if (! $notification || $notification->sent_at || ! $notification->user?->is_active) {
            return;
        }

        $tasks = Task::query()
            ->with(['project.leader', 'assignees'])
            ->whereKey($notification->task_ids ?? [])
            ->get();

        if ($tasks->isEmpty()) {
            $notification->update(['sent_at' => now()]);

            return;
        }

        Mail::to($notification->user)->send(new TaskAssignmentDigestMail($notification->user, $tasks));
        $notification->update(['sent_at' => now()]);
    }
}
