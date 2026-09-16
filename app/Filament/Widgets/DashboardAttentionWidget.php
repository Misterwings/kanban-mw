<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ProjectBoard;
use App\Filament\Widgets\Concerns\InteractsWithDashboardData;
use Filament\Widgets\Widget;

class DashboardAttentionWidget extends Widget
{
    use InteractsWithDashboardData;

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-attention-widget';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()?->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = $this->dashboardUser();
        $today = $this->dashboardToday();
        $todayValue = $today->toDateString();
        $nextWeekValue = $today->addDays(7)->toDateString();
        $openTasks = $this->dashboardOpenTasksQuery()
            ->with('project')
            ->whereNotNull('end_date');

        return [
            'heading' => $user->isCollaborator() ? 'Mi agenda' : 'Atención operativa',
            'description' => $user->isCollaborator()
                ? 'Tareas asignadas y proyectos que requieren acción.'
                : 'Elementos del portafolio que requieren seguimiento.',
            'overdueTasks' => (clone $openTasks)
                ->whereDate('end_date', '<', $todayValue)
                ->orderBy('end_date')
                ->limit(5)
                ->get(),
            'upcomingTasks' => (clone $openTasks)
                ->whereBetween('end_date', [$todayValue, $nextWeekValue])
                ->orderBy('end_date')
                ->limit(5)
                ->get(),
            'setupProjects' => $this->dashboardProjectsNeedingSetupQuery()
                ->with('leader')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'projectBoardUrl' => static fn (int $projectId): string => ProjectBoard::getUrl(['project' => $projectId]),
        ];
    }
}
