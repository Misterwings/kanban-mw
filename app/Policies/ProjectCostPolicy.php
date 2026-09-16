<?php

namespace App\Policies;

use App\Models\ProjectCost;
use App\Models\User;

class ProjectCostPolicy
{
    public function create(User $user, ProjectCost $cost): bool
    {
        return $user->can('addCost', $cost->project);
    }
}
