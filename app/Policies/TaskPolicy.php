<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isActive()
            && $user->is_active
            && ($user->isAdmin() || $project->owner_id === $user->id);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->isActive()
            && $user->is_active
            && ($user->isAdmin() || $task->project->owner_id === $user->id);
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $task->project->isActive()
            && $user->is_active
            && ($user->isAdmin() || $task->project->owner_id === $user->id);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
