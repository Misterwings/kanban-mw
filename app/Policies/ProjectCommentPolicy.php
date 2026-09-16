<?php

namespace App\Policies;

use App\Models\ProjectComment;
use App\Models\User;

class ProjectCommentPolicy
{
    public function create(User $user, ProjectComment $comment): bool
    {
        return $user->can('view', $comment->project);
    }
}
