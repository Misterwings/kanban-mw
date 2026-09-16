<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Filament\Pages\ProjectBoard;
use App\Filament\Widgets\Concerns\InteractsWithDashboardData;
use App\Models\Project;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DashboardProjectsWidget extends TableWidget
{
    use InteractsWithDashboardData;

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()?->is_active;
    }

    public function table(Table $table): Table
    {
        $user = $this->dashboardUser();

        return $table
            ->heading($user->isCollaborator() ? 'Mis proyectos' : 'Portafolio de proyectos')
            ->description($user->isCollaborator()
                ? 'Proyectos propios, liderados o con tareas asignadas.'
                : 'Proyectos visibles para tu perfil, ordenados por actividad reciente.')
            ->query(
                $this->dashboardProjectsQuery()
                    ->with('leader')
                    ->withCount([
                        'tasks',
                        'tasks as completed_tasks_count' => fn (Builder $query): Builder => $query
                            ->where('status', TaskStatus::Done->value),
                    ])
                    ->orderByDesc('updated_at'),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (ProjectStatus $state): string => $state->label())
                    ->color(fn (ProjectStatus $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('leader.name')
                    ->label('Líder')
                    ->placeholder('Pendiente'),
                TextColumn::make('progress')
                    ->label('Avance')
                    ->state(function (Project $record): string {
                        $totalTasks = (int) $record->tasks_count;

                        if ($totalTasks === 0) {
                            return 'Sin tareas';
                        }

                        return round(((int) $record->completed_tasks_count / $totalTasks) * 100).'%';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'Sin tareas') {
                            return 'gray';
                        }

                        return ((int) $state >= 100) ? 'success' : (((int) $state >= 50) ? 'primary' : 'warning');
                    }),
                TextColumn::make('end_date')
                    ->label('Fin planeado')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha')
                    ->sortable(),
            ])
            ->recordUrl(fn (Project $record): string => ProjectBoard::getUrl(['project' => $record]))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading($user->isCollaborator() ? 'Aún no tienes proyectos visibles' : 'No hay proyectos registrados')
            ->emptyStateDescription($user->isCollaborator()
                ? 'Cuando seas propietario, líder o responsable de una tarea, tus proyectos aparecerán aquí.'
                : 'Los proyectos creados aparecerán en este resumen.');
    }
}
