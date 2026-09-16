<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\Concerns\InteractsWithDashboardData;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    use InteractsWithDashboardData;

    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Resumen de actividad';

    protected ?string $description = 'Indicadores actualizados con la información visible para tu perfil.';

    protected function getStats(): array
    {
        $user = $this->dashboardUser();
        $today = $this->dashboardToday();
        $todayValue = $today->toDateString();
        $nextWeekValue = $today->addDays(7)->toDateString();

        if ($user->isAdmin()) {
            return [
                Stat::make('Proyectos activos', $this->dashboardOpenProjectsQuery()->count())
                    ->description('En seguimiento actualmente')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('primary')
                    ->icon('heroicon-o-briefcase')
                    ->url(ProjectResource::getUrl()),
                Stat::make('Proyectos por configurar', $this->dashboardProjectsNeedingSetupQuery()->count())
                    ->description('Sin líder o fechas completas')
                    ->descriptionIcon('heroicon-m-wrench-screwdriver')
                    ->color('warning')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->url(ProjectResource::getUrl()),
                Stat::make('Tareas vencidas', $this->dashboardOpenTasksQuery()
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '<', $todayValue)
                    ->count())
                    ->description('Requieren seguimiento')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
                    ->icon('heroicon-o-clock'),
                Stat::make('Usuarios activos', User::query()->where('is_active', true)->count())
                    ->description('Con acceso a la plataforma')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('success')
                    ->icon('heroicon-o-users')
                    ->url(UserResource::getUrl()),
            ];
        }

        if ($user->isObserver()) {
            return [
                Stat::make('Proyectos activos', $this->dashboardOpenProjectsQuery()->count())
                    ->description('En seguimiento actualmente')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('primary')
                    ->icon('heroicon-o-briefcase')
                    ->url(ProjectResource::getUrl()),
                Stat::make('Proyectos finalizados', $this->dashboardProjectsQuery()
                    ->where('status', ProjectStatus::Finished->value)
                    ->count())
                    ->description('Histórico del portafolio')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success')
                    ->icon('heroicon-o-archive-box')
                    ->url(ProjectResource::getUrl()),
                Stat::make('Tareas abiertas', $this->dashboardOpenTasksQuery()->count())
                    ->description('Dentro de proyectos activos')
                    ->descriptionIcon('heroicon-m-clipboard-document-list')
                    ->color('warning')
                    ->icon('heroicon-o-list-bullet'),
                Stat::make('Cierres próximos', $this->dashboardOpenProjectsQuery()
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [$todayValue, $today->addDays(14)->toDateString()])
                    ->count())
                    ->description('Finalizan en los próximos 14 días')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),
            ];
        }

        return [
            Stat::make('Mis proyectos activos', $this->dashboardOpenProjectsQuery()->count())
                ->description('Proyectos visibles para ti')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->icon('heroicon-o-briefcase')
                ->url(ProjectResource::getUrl()),
            Stat::make('Tareas para hoy', $this->dashboardOpenTasksQuery()
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereDate('start_date', '<=', $todayValue)
                ->whereDate('end_date', '>=', $todayValue)
                ->count())
                ->description('Asignadas y en curso')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Tareas vencidas', $this->dashboardOpenTasksQuery()
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', $todayValue)
                ->count())
                ->description('Requieren tu atención')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->icon('heroicon-o-clock'),
            Stat::make('Próximos 7 días', $this->dashboardOpenTasksQuery()
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [$todayValue, $nextWeekValue])
                ->count())
                ->description('Tareas próximas a vencer')
                ->descriptionIcon('heroicon-m-arrow-right')
                ->color('success')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
