<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Jobs\SendTaskReminderDigest;
use App\Mail\TaskReminderDigestMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskReminderDelivery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TaskReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminders_are_grouped_by_user_and_deduplicated_per_day(): void
    {
        Mail::fake();
        Queue::fake();

        $owner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $project = Project::create([
            'name' => 'Proyecto con recordatorios',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::InProgress,
        ]);

        $first = $this->makeTask($project, $owner, 'Primera tarea', '2026-09-10', '2026-09-20');
        $second = $this->makeTask($project, $owner, 'Segunda tarea', '2026-09-15', '2026-09-16');
        $outsideWindow = $this->makeTask($project, $owner, 'Fuera de ventana', '2026-09-16', '2026-09-16');
        $done = $this->makeTask($project, $owner, 'Tarea terminada', '2026-09-15', '2026-09-16', TaskStatus::Done);
        $first->assignees()->attach($assignee->id);
        $second->assignees()->attach($assignee->id);
        $outsideWindow->assignees()->attach($assignee->id);
        $done->assignees()->attach($assignee->id);

        $this->artisan('tasks:send-reminders', ['--date' => '2026-09-15'])
            ->expectsOutput('1 recordatorio(s) programado(s) para 2026-09-15.')
            ->assertSuccessful();

        Queue::assertPushed(SendTaskReminderDigest::class, function (SendTaskReminderDigest $job) use ($assignee, $first, $second): bool {
            $taskIds = $job->taskIds;
            sort($taskIds);
            $expectedIds = [$first->id, $second->id];
            sort($expectedIds);

            return $job->userId === $assignee->id
                && $job->reminderDate === '2026-09-15'
                && $taskIds === $expectedIds;
        });
        (new SendTaskReminderDigest($assignee->id, '2026-09-15', [$first->id, $second->id]))->handle();

        Mail::assertSent(TaskReminderDigestMail::class, 1);
        Mail::assertSent(TaskReminderDigestMail::class, function (TaskReminderDigestMail $mail) use ($assignee, $first, $second): bool {
            $taskIds = $mail->tasks->modelKeys();
            sort($taskIds);
            $expectedIds = [$first->id, $second->id];
            sort($expectedIds);

            return $mail->recipient->is($assignee)
                && $taskIds === $expectedIds;
        });
        $this->assertSame(2, TaskReminderDelivery::query()->where('user_id', $assignee->id)->count());
        $this->assertSame(2, TaskReminderDelivery::query()->whereNotNull('sent_at')->count());

        $this->artisan('tasks:send-reminders', ['--date' => '2026-09-15'])
            ->expectsOutput('0 recordatorio(s) programado(s) para 2026-09-15.')
            ->assertSuccessful();

        Mail::assertSent(TaskReminderDigestMail::class, 1);
        $this->assertDatabaseMissing('task_reminder_deliveries', ['task_id' => $outsideWindow->id]);
        $this->assertDatabaseMissing('task_reminder_deliveries', ['task_id' => $done->id]);
    }

    public function test_queued_reminder_rechecks_the_task_window_before_sending(): void
    {
        Mail::fake();

        $owner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $project = Project::create([
            'name' => 'Proyecto con tarea retrasada',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $task = $this->makeTask($project, $owner, 'Tarea fuera de fecha', '2026-09-15', '2026-09-20');
        $task->assignees()->attach($assignee->id);
        $delivery = TaskReminderDelivery::create([
            'task_id' => $task->id,
            'user_id' => $assignee->id,
            'reminder_date' => '2026-09-15',
        ]);

        $task->update(['end_date' => '2026-09-14']);

        (new SendTaskReminderDigest($assignee->id, '2026-09-15', [$task->id]))->handle();

        Mail::assertNothingSent();
        $this->assertNull($delivery->fresh()->sent_at);
    }

    private function makeUser(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function makeTask(
        Project $project,
        User $creator,
        string $description,
        string $startDate,
        string $endDate,
        TaskStatus $status = TaskStatus::Pending,
    ): Task {
        return $project->tasks()->create([
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'created_by_id' => $creator->id,
        ]);
    }
}
