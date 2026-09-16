<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Project $project): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin() || $user->isObserver() || $project->owner_id === $user->id
            || $project->leader_id === $user->id
            || $project->tasks()->whereHas('assignees', fn ($query) => $query->whereKey($user->id))->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isCollaborator());
    }

    public function update(User $user, Project $project): bool
    {
        return $user->is_active && ($user->isAdmin() || $project->owner_id === $user->id);
    }

    public function clone(User $user, Project $project): bool
    {
        return $user->is_active && ($user->isAdmin() || $project->owner_id === $user->id);
    }

    public function addComment(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function addNote(User $user, Project $project): bool
    {
        return $project->isActive()
            && $this->view($user, $project)
            && ($user->isAdmin() || $user->isCollaborator());
    }

    public function addCost(User $user, Project $project): bool
    {
        return $this->addNote($user, $project);
    }

    public function addImprovement(User $user, Project $project): bool
    {
        return $project->isFinished()
            && $user->is_active
            && ($user->isAdmin() || ($user->role === UserRole::Collaborator
                && ($project->owner_id === $user->id || $project->leader_id === $user->id)));
    }
}
