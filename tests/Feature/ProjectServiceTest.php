<?php

namespace Tests\Feature;

use App\Enums\ProjectEntryType;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Jobs\SendTaskAssignmentDigest;
use App\Mail\TaskAssignmentDigestMail;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectCost;
use App\Models\TaskAssignmentNotification;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_drive_project_lifecycle_and_send_assignment_digest(): void
    {
        Mail::fake();

        $owner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $project = $this->makeProject($owner);
        $service = app(ProjectService::class);

        $task = $service->createTasks($project, [[
            'description' => 'Preparar entrega',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'assignee_ids' => [$assignee->id],
        ]], $owner)->sole();

        $this->assertSame(ProjectStatus::InProgress, $project->fresh()->status);
        $this->assertSame([$assignee->id], $task->assignees->pluck('id')->all());
        $notification = TaskAssignmentNotification::query()->where('user_id', $assignee->id)->sole();
        $this->assertSame([$task->id], $notification->task_ids);
        (new SendTaskAssignmentDigest($notification->id))->handle();
        $notification->refresh();
        $this->assertNotNull($notification->sent_at);
        Mail::assertSent(TaskAssignmentDigestMail::class, function (TaskAssignmentDigestMail $mail) use ($assignee, $task): bool {
            return $mail->recipient->is($assignee)
                && $mail->tasks->contains('id', $task->id);
        });

        $service->changeTaskStatus($task, TaskStatus::Done, $owner);
        $finished = $project->fresh();

        $this->assertSame(ProjectStatus::Finished, $finished->status);
        $this->assertNotNull($finished->completed_at);
        $this->assertNotNull($task->fresh()->completed_at);
        $finishedTask = $task->fresh(['project']);
        $this->assertFalse(Gate::forUser($owner)->allows('changeStatus', $finishedTask));

        try {
            $service->changeTaskStatus($finishedTask, TaskStatus::InProgress, $owner);
            $this->fail('Un proyecto finalizado no debe permitir cambios de estado en sus tareas.');
        } catch (ValidationException) {
            // Expected: finished projects keep their tasks in the done state.
        }

        $this->assertSame(ProjectStatus::Finished, $project->fresh()->status);
        $this->assertNotNull($project->fresh()->completed_at);
        $this->assertSame(TaskStatus::Done, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_clone_copies_only_task_descriptions_and_resets_followup_data(): void
    {
        Mail::fake();

        $owner = $this->makeUser(UserRole::Collaborator);
        $newOwner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $project = $this->makeProject($owner, [
            'name' => 'Proyecto original',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);
        $service = app(ProjectService::class);

        $task = $service->createTasks($project, [[
            'description' => 'Tarea que debe copiarse',
            'start_date' => '2026-09-02',
            'end_date' => '2026-09-03',
            'assignee_ids' => [$assignee->id],
        ]], $owner)->sole();
        $task->update(['status' => TaskStatus::InProgress]);
        $project->update(['improvement_opportunities' => 'No copiar']);
        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'type' => ProjectEntryType::Observation,
            'body' => 'No copiar esta observacion',
        ]);
        ProjectCost::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'amount' => 125000,
            'description' => 'No copiar este costo',
            'incurred_on' => '2026-09-04',
        ]);

        $copy = $service->cloneProject($project, $newOwner);
        $copyTask = $copy->tasks()->with('assignees')->sole();

        $this->assertSame('Proyecto original (Copia)', $copy->name);
        $this->assertSame($project->id, $copy->cloned_from_id);
        $this->assertSame($newOwner->id, $copy->owner_id);
        $this->assertNull($copy->start_date);
        $this->assertNull($copy->end_date);
        $this->assertNull($copy->leader_id);
        $this->assertSame(ProjectStatus::InProgress, $copy->status);
        $this->assertNull($copy->completed_at);
        $this->assertNull($copy->improvement_opportunities);
        $this->assertSame($task->description, $copyTask->description);
        $this->assertSame(TaskStatus::Pending, $copyTask->status);
        $this->assertNull($copyTask->start_date);
        $this->assertNull($copyTask->end_date);
        $this->assertCount(0, $copyTask->assignees);
        $this->assertDatabaseCount('project_comments', 1);
        $this->assertDatabaseCount('project_costs', 1);
    }

    public function test_regular_tasks_require_dates_and_an_assignable_user(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $inactive = $this->makeUser(UserRole::Collaborator, false);
        $project = $this->makeProject($owner);
        $service = app(ProjectService::class);

        $this->expectException(ValidationException::class);

        $service->createTasks($project, [[
            'description' => 'Tarea incompleta',
            'start_date' => null,
            'end_date' => null,
            'assignee_ids' => [$inactive->id],
        ]], $owner);
    }

    public function test_incomplete_cloned_tasks_cannot_leave_pending_status(): void
    {
        Mail::fake();

        $owner = $this->makeUser(UserRole::Collaborator);
        $newOwner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $source = $this->makeProject($owner);
        $service = app(ProjectService::class);

        $service->createTasks($source, [[
            'description' => 'Tarea para clonar',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'assignee_ids' => [$assignee->id],
        ]], $owner);
        $copy = $service->cloneProject($source, $newOwner);
        $copyTask = $copy->tasks()->sole();

        $this->expectException(ValidationException::class);

        $service->changeTaskStatus($copyTask, TaskStatus::InProgress, $newOwner);
    }

    public function test_visibility_and_followup_permissions_are_role_aware(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $assignee = $this->makeUser(UserRole::Collaborator);
        $unrelated = $this->makeUser(UserRole::Collaborator);
        $observer = $this->makeUser(UserRole::Observer);
        $admin = $this->makeUser(UserRole::Admin);
        $project = $this->makeProject($owner, ['leader_id' => $assignee->id]);
        $task = $project->tasks()->create([
            'description' => 'Tarea visible para responsable',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'status' => TaskStatus::Pending,
            'created_by_id' => $owner->id,
        ]);
        $task->assignees()->attach($assignee->id);

        $this->assertTrue(Gate::forUser($observer)->allows('view', $project));
        $this->assertTrue(Gate::forUser($assignee)->allows('view', $project));
        $this->assertFalse(Gate::forUser($unrelated)->allows('view', $project));
        $this->assertTrue(Gate::forUser($owner)->allows('update', $project));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $project));
        $this->assertFalse(Gate::forUser($assignee)->allows('update', $project));
        $this->assertTrue(Gate::forUser($observer)->allows('addComment', $project));
        $this->assertFalse(Gate::forUser($observer)->allows('addNote', $project));
        $this->assertTrue(Gate::forUser($owner)->allows('addNote', $project));

        $project->update([
            'status' => ProjectStatus::Finished,
            'completed_at' => Carbon::now(),
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('addImprovement', $project));
        $this->assertTrue(Gate::forUser($assignee)->allows('addImprovement', $project));
        $this->assertTrue(Gate::forUser($admin)->allows('addImprovement', $project));
        $this->assertFalse(Gate::forUser($observer)->allows('addImprovement', $project));
    }

    private function makeUser(UserRole $role, bool $active = true): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => $active,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function makeProject(User $owner, array $attributes = []): Project
    {
        return Project::create(array_merge([
            'name' => 'Proyecto de prueba',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Created,
        ], $attributes));
    }
}
