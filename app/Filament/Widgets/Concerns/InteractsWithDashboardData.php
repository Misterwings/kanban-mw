<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardData
{
    protected function dashboardUser(): User
    {
        /** @var User $user */
        $user = filament()->auth()->user();

        return $user;
    }

    protected function dashboardToday(): CarbonImmutable
    {
        return CarbonImmutable::today(config('kanban.timezone'));
    }

    protected function dashboardProjectsQuery(): Builder
    {
        return Project::query()->visibleTo($this->dashboardUser());
    }

    protected function dashboardOpenProjectsQuery(): Builder
    {
        return $this->dashboardProjectsQuery()
            ->where('status', '!=', ProjectStatus::Finished->value);
    }

    protected function dashboardProjectsNeedingSetupQuery(): Builder
    {
        $user = $this->dashboardUser();
        $query = $this->dashboardOpenProjectsQuery();

        if ($user->isCollaborator()) {
            $query->where('owner_id', $user->getKey());
        }

        return $query
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('leader_id')
                    ->orWhereNull('start_date')
                    ->orWhereNull('end_date');
            });
    }

    protected function dashboardTasksQuery(bool $assignedOnly = false): Builder
    {
        $user = $this->dashboardUser();

        $query = Task::query()->whereHas(
            'project',
            fn (Builder $project): Builder => $project
                ->visibleTo($user)
                ->where('status', '!=', ProjectStatus::Finished->value),
        );

        if ($assignedOnly && $user->isCollaborator()) {
            $query->whereHas(
                'assignees',
                fn (Builder $assignees): Builder => $assignees->whereKey($user->getKey()),
            );
        }

        return $query;
    }

    protected function dashboardOpenTasksQuery(): Builder
    {
        return $this->dashboardTasksQuery(assignedOnly: $this->dashboardUser()->isCollaborator())
            ->where('status', '!=', TaskStatus::Done->value);
    }
}
