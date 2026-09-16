<?php

namespace App\Services;

use App\Enums\ProjectEntryType;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Jobs\SendTaskAssignmentDigest;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectCost;
use App\Models\Task;
use App\Models\TaskAssignmentNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function createProject(array $data, User $owner): Project
    {
        $leaderId = $data['leader_id'] ?? $owner->id;
        $this->ensureAssignableUser($leaderId);

        return Project::create([
            'name' => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'leader_id' => $leaderId,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Created,
        ]);
    }

    public function updateProject(Project $project, array $data): Project
    {
        if (array_key_exists('leader_id', $data) && $data['leader_id'] !== null) {
            $this->ensureAssignableUser($data['leader_id']);
        }

        if (($data['start_date'] ?? null) && ($data['end_date'] ?? null)
            && $data['start_date'] > $data['end_date']) {
            throw ValidationException::withMessages([
                'end_date' => 'La fecha final debe ser posterior o igual a la fecha de inicio.',
            ]);
        }

        $project->update([
            'name' => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'leader_id' => $data['leader_id'] ?? null,
        ]);

        return $project->refresh();
    }

    public function cloneProject(Project $source, User $owner): Project
    {
        return DB::transaction(function () use ($source, $owner): Project {
            $source->load('tasks');

            $copy = Project::create([
                'name' => $source->name.' (Copia)',
                'start_date' => null,
                'end_date' => null,
                'leader_id' => null,
                'owner_id' => $owner->id,
                'status' => ProjectStatus::Created,
                'cloned_from_id' => $source->id,
            ]);

            foreach ($source->tasks as $sourceTask) {
                $copy->tasks()->create([
                    'description' => $sourceTask->description,
                    'start_date' => null,
                    'end_date' => null,
                    'status' => TaskStatus::Pending,
                    'position' => $sourceTask->position,
                    'created_by_id' => $owner->id,
                ]);
            }

            return $this->syncStatus($copy->fresh());
        });
    }

    /**
     * Create one or more tasks in the same operation so each assignee gets one digest.
     *
     * @param  array<int, array<string, mixed>>  $taskData
     */
    public function createTasks(Project $project, array $taskData, User $actor): Collection
    {
        return DB::transaction(function () use ($project, $taskData, $actor): Collection {
            $actor->can('create', [Task::class, $project]) || abort(403);
            $operationId = (string) Str::uuid();
            $created = collect();

            foreach ($taskData as $data) {
                $created->push($this->createTaskInOperation($project, $data, $actor, $operationId));
            }

            $this->syncStatus($project->fresh());

            return $created;
        });
    }

    public function updateTask(Task $task, array $data, User $actor): Task
    {
        return DB::transaction(function () use ($task, $data, $actor): Task {
            $actor->can('update', $task) || abort(403);
            $project = $task->project;
            $allowIncomplete = $project->cloned_from_id !== null && $task->isDraft();

            $this->validateTaskData($data, $allowIncomplete);
            $newAssigneeIds = $this->validatedAssigneeIds($data['assignee_ids'] ?? []);
            $oldAssigneeIds = $task->assignees()->pluck('users.id')->all();
            $newAssignments = array_values(array_diff($newAssigneeIds, $oldAssigneeIds));

            $task->update([
                'description' => $data['description'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ]);
            $task->assignees()->sync($newAssigneeIds);

            if ($newAssignments !== []) {
                $this->queueAssignmentNotifications($task, $newAssignments, (string) Str::uuid());
            }

            $this->syncStatus($project->fresh());

            return $task->fresh(['assignees']);
        });
    }

    public function changeTaskStatus(Task $task, TaskStatus $status, User $actor): Task
    {
        return DB::transaction(function () use ($task, $status, $actor): Task {
            $project = $task->project()->firstOrFail();
            $task->setRelation('project', $project);

            if ($project->isFinished()) {
                throw ValidationException::withMessages([
                    'status' => 'Las tareas de un proyecto finalizado permanecen en estado Hecho.',
                ]);
            }

            $actor->can('changeStatus', $task) || abort(403);

            if ($task->isDraft() && $status !== TaskStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Completa fechas y responsables antes de iniciar la tarea clonada.',
                ]);
            }

            $task->update([
                'status' => $status,
                'completed_at' => $status === TaskStatus::Done ? now() : null,
            ]);

            $this->syncStatus($project->fresh());

            return $task->fresh(['assignees']);
        });
    }

    public function addComment(Project $project, User $user, string $body, ProjectEntryType $type): ProjectComment
    {
        $ability = $type === ProjectEntryType::Note ? 'addNote' : 'addComment';
        $user->can($ability, $project) || abort(403);

        return $project->comments()->create([
            'user_id' => $user->id,
            'type' => $type,
            'body' => trim($body),
        ]);
    }

    public function addCost(Project $project, User $user, array $data): ProjectCost
    {
        $user->can('addCost', $project) || abort(403);

        return $project->costs()->create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'description' => trim($data['description']),
            'incurred_on' => $data['incurred_on'],
        ]);
    }

    public function setImprovementOpportunities(Project $project, User $user, string $body): Project
    {
        $user->can('addImprovement', $project) || abort(403);

        $project->update(['improvement_opportunities' => trim($body)]);

        return $project->refresh();
    }

    public function syncStatus(Project $project): Project
    {
        $taskCount = $project->tasks()->count();
        $doneCount = $project->tasks()->where('status', TaskStatus::Done->value)->count();
        $status = match (true) {
            $taskCount === 0 => ProjectStatus::Created,
            $doneCount === $taskCount => ProjectStatus::Finished,
            default => ProjectStatus::InProgress,
        };

        $updates = ['status' => $status];

        if ($status === ProjectStatus::Finished && $project->status !== ProjectStatus::Finished) {
            $updates['completed_at'] = now();
        }

        if ($status !== ProjectStatus::Finished && $project->status === ProjectStatus::Finished) {
            $updates['completed_at'] = null;
        }

        if ($project->status !== $status || array_key_exists('completed_at', $updates)) {
            $project->updateQuietly($updates);
        }

        return $project->refresh();
    }

    private function createTaskInOperation(Project $project, array $data, User $actor, string $operationId): Task
    {
        $this->validateTaskData($data);
        $assigneeIds = $this->validatedAssigneeIds($data['assignee_ids']);

        $task = $project->tasks()->create([
            'description' => trim($data['description']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => TaskStatus::Pending,
            'position' => ((int) $project->tasks()->max('position')) + 1,
            'created_by_id' => $actor->id,
        ]);

        $task->assignees()->sync($assigneeIds);
        $this->queueAssignmentNotifications($task, $assigneeIds, $operationId);

        return $task->fresh(['assignees']);
    }

    private function validateTaskData(array $data, bool $allowIncomplete = false): void
    {
        if (blank($data['description'] ?? null)) {
            throw ValidationException::withMessages(['description' => 'La descripción es obligatoria.']);
        }

        if ($allowIncomplete && blank($data['start_date'] ?? null) && blank($data['end_date'] ?? null)
            && empty($data['assignee_ids'] ?? [])) {
            return;
        }

        if (blank($data['start_date'] ?? null) || blank($data['end_date'] ?? null)) {
            throw ValidationException::withMessages([
                'start_date' => 'Las fechas son obligatorias para una tarea activa.',
                'end_date' => 'Las fechas son obligatorias para una tarea activa.',
            ]);
        }

        if ($data['start_date'] > $data['end_date']) {
            throw ValidationException::withMessages([
                'end_date' => 'La fecha final debe ser posterior o igual a la fecha de inicio.',
            ]);
        }

        if (empty($data['assignee_ids'] ?? [])) {
            throw ValidationException::withMessages([
                'assignee_ids' => 'Debe asignar al menos un responsable.',
            ]);
        }
    }

    /** @return array<int, int> */
    private function validatedAssigneeIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $valid = User::query()->assignable()->whereKey($ids)->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if (count($valid) !== count($ids)) {
            throw ValidationException::withMessages([
                'assignee_ids' => 'Solo se pueden asignar usuarios activos administradores o colaboradores.',
            ]);
        }

        return $valid;
    }

    private function ensureAssignableUser(int|string|null $userId): void
    {
        if (! $userId || ! User::query()->assignable()->whereKey($userId)->exists()) {
            throw ValidationException::withMessages([
                'leader_id' => 'El líder debe ser un usuario activo administrador o colaborador.',
            ]);
        }
    }

    /** @param array<int, int> $assigneeIds */
    private function queueAssignmentNotifications(Task $task, array $assigneeIds, string $operationId): void
    {
        foreach ($assigneeIds as $userId) {
            $notification = TaskAssignmentNotification::firstOrCreate(
                ['operation_id' => $operationId, 'user_id' => $userId],
                ['task_ids' => []],
            );

            $taskIds = array_values(array_unique([
                ...($notification->task_ids ?? []),
                $task->id,
            ]));
            $notification->update(['task_ids' => $taskIds]);

            SendTaskAssignmentDigest::dispatch($notification->id)->afterCommit();
        }
    }
}
