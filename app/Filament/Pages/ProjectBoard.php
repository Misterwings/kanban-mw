<?php

namespace App\Filament\Pages;

use App\Enums\ProjectEntryType;
use App\Enums\TaskStatus;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ProjectBoard extends Page
{
    protected static ?string $slug = 'projects/{project}/board';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Tablero del proyecto';

    protected string $view = 'filament.pages.project-board';

    public Project $project;

    public string $viewMode = 'kanban';

    public bool $showTaskForm = false;

    public ?int $editingTaskId = null;

    public string $taskDescription = '';

    public string $taskStartDate = '';

    public string $taskEndDate = '';

    /** @var array<int, int> */
    public array $taskAssigneeIds = [];

    public string $newObservation = '';

    public string $newNote = '';

    public string $costAmount = '';

    public string $costDescription = '';

    public string $costDate = '';

    public string $improvementOpportunities = '';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->is_active;
    }

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->project = $project;
        $this->costDate = now(config('kanban.timezone'))->toDateString();
        $this->improvementOpportunities = (string) $project->improvement_opportunities;
        $this->refreshProject();
    }

    public function getTitle(): string
    {
        return $this->project->name;
    }

    public function setViewMode(string $viewMode): void
    {
        if (! in_array($viewMode, ['kanban', 'timeline'], true)) {
            return;
        }

        $this->viewMode = $viewMode;
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProjectResource::getUrl() => 'Proyectos',
            static::getUrl(['project' => $this->project]) => $this->project->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Editar encabezado')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => ProjectResource::getUrl('edit', ['record' => $this->project]))
                ->visible(fn (): bool => auth()->user()->can('update', $this->project)),
            Action::make('clone')
                ->label('Clonar Proyecto')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->requiresConfirmation()
                ->action('cloneProject')
                ->visible(fn (): bool => auth()->user()->can('clone', $this->project)),
        ];
    }

    public function getTasksByStatusProperty(): array
    {
        return collect(TaskStatus::cases())
            ->mapWithKeys(fn (TaskStatus $status): array => [
                $status->value => $this->project->tasks->where('status', $status)->values(),
            ])
            ->all();
    }

    public function getTimelineProperty(): array
    {
        $toDate = static function ($date): ?CarbonImmutable {
            return $date ? CarbonImmutable::parse($date->format('Y-m-d')) : null;
        };

        $taskRows = $this->project->tasks->map(function (Task $task) use ($toDate): array {
            $start = $toDate($task->start_date);
            $end = $toDate($task->end_date);
            $isScheduled = $start !== null && $end !== null && ! $end->lessThan($start);
            $assignees = $task->assignees->pluck('name')->values()->all();

            return [
                'id' => (int) $task->id,
                'description' => $task->description,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'start' => $start,
                'end' => $end,
                'start_label' => $start?->format('d/m/Y'),
                'end_label' => $end?->format('d/m/Y'),
                'assignees_label' => $assignees === [] ? 'Sin responsables' : implode(', ', $assignees),
                'scheduled' => $isScheduled,
            ];
        });

        $scheduledTasks = $taskRows->filter(fn (array $task): bool => $task['scheduled'])->values();
        $unscheduledTasks = $taskRows->reject(fn (array $task): bool => $task['scheduled'])->values();
        $projectStart = $toDate($this->project->start_date);
        $projectEnd = $toDate($this->project->end_date);
        $allDates = collect([
            $projectStart,
            $projectEnd,
            ...$scheduledTasks->flatMap(fn (array $task): array => [$task['start'], $task['end']])->all(),
        ])->filter()->sortBy(fn (CarbonImmutable $date): int => $date->getTimestamp())->values();

        if ($allDates->isEmpty()) {
            return [
                'has_range' => false,
                'range_start' => null,
                'range_end' => null,
                'range_start_label' => null,
                'range_end_label' => null,
                'unit' => null,
                'unit_label' => null,
                'total_days' => 0,
                'periods' => [],
                'tasks' => [],
                'unscheduled_tasks' => $unscheduledTasks->all(),
                'project_start_position' => null,
                'project_end_position' => null,
                'today_position' => null,
            ];
        }

        /** @var CarbonImmutable $rangeStart */
        $rangeStart = $allDates->first();
        /** @var CarbonImmutable $rangeEnd */
        $rangeEnd = $allDates->last();
        $totalDays = $rangeStart->diffInDays($rangeEnd) + 1;
        $unit = $totalDays <= 31 ? 'day' : ($totalDays <= 120 ? 'week' : 'month');
        $unitLabel = match ($unit) {
            'day' => 'Días',
            'week' => 'Semanas',
            default => 'Meses',
        };
        $periods = [];
        $cursor = $rangeStart;

        while ($cursor->lessThanOrEqualTo($rangeEnd)) {
            $periodEnd = match ($unit) {
                'day' => $cursor,
                'week' => $cursor->endOfWeek(),
                default => $cursor->endOfMonth(),
            };

            if ($periodEnd->greaterThan($rangeEnd)) {
                $periodEnd = $rangeEnd;
            }

            $periodDays = $cursor->diffInDays($periodEnd) + 1;
            $periods[] = [
                'key' => $cursor->format('Y-m-d'),
                'label' => match ($unit) {
                    'day' => $cursor->locale('es')->translatedFormat('d M'),
                    'week' => 'Sem. '.$cursor->locale('es')->translatedFormat('d M'),
                    default => $cursor->locale('es')->translatedFormat('M Y'),
                },
                'aria_label' => $cursor->locale('es')->translatedFormat('l, d F Y'),
                'left' => round(($rangeStart->diffInDays($cursor) / $totalDays) * 100, 4),
                'width' => round(($periodDays / $totalDays) * 100, 4),
            ];
            $cursor = $periodEnd->addDay();
        }

        $position = static function (?CarbonImmutable $date) use ($rangeStart, $totalDays): ?float {
            if ($date === null) {
                return null;
            }

            return round(($rangeStart->diffInDays($date) / $totalDays) * 100, 4);
        };
        $today = CarbonImmutable::today(config('kanban.timezone'));
        $todayPosition = $today->greaterThanOrEqualTo($rangeStart) && $today->lessThanOrEqualTo($rangeEnd)
            ? $position($today)
            : null;
        $tasks = $scheduledTasks->map(function (array $task) use ($rangeStart, $totalDays): array {
            $duration = $task['start']->diffInDays($task['end']) + 1;
            $task['left'] = round(($rangeStart->diffInDays($task['start']) / $totalDays) * 100, 4);
            $task['width'] = round(($duration / $totalDays) * 100, 4);
            unset($task['start'], $task['end'], $task['scheduled']);

            return $task;
        })->all();

        return [
            'has_range' => true,
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'range_start_label' => $rangeStart->locale('es')->translatedFormat('d M Y'),
            'range_end_label' => $rangeEnd->locale('es')->translatedFormat('d M Y'),
            'unit' => $unit,
            'unit_label' => $unitLabel,
            'total_days' => $totalDays,
            'periods' => $periods,
            'tasks' => $tasks,
            'unscheduled_tasks' => $unscheduledTasks->all(),
            'project_start_position' => $position($projectStart),
            'project_end_position' => $position($projectEnd),
            'today_position' => $todayPosition,
        ];
    }

    public function getAssignableUsersProperty(): Collection
    {
        return User::query()->assignable()->get(['id', 'name', 'email']);
    }

    public function getCanEditBoardProperty(): bool
    {
        return $this->project->isActive()
            && auth()->user()->can('update', $this->project);
    }

    public function getCanAddNoteProperty(): bool
    {
        return auth()->user()->can('addNote', $this->project);
    }

    public function getCanAddCostProperty(): bool
    {
        return auth()->user()->can('addCost', $this->project);
    }

    public function getCanAddImprovementProperty(): bool
    {
        return auth()->user()->can('addImprovement', $this->project);
    }

    public function openNewTask(): void
    {
        Gate::authorize('create', [Task::class, $this->project]);
        $this->resetTaskForm();
        $this->showTaskForm = true;
    }

    public function openEditTask(int $taskId): void
    {
        $task = $this->project->tasks()->with('assignees')->findOrFail($taskId);
        Gate::authorize('update', $task);

        $this->editingTaskId = $task->id;
        $this->showTaskForm = true;
        $this->taskDescription = $task->description;
        $this->taskStartDate = $task->start_date?->toDateString() ?? '';
        $this->taskEndDate = $task->end_date?->toDateString() ?? '';
        $this->taskAssigneeIds = $task->assignees->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function saveTask(): void
    {
        $service = app(ProjectService::class);
        $data = [
            'description' => $this->taskDescription,
            'start_date' => $this->taskStartDate ?: null,
            'end_date' => $this->taskEndDate ?: null,
            'assignee_ids' => $this->taskAssigneeIds,
        ];

        if ($this->editingTaskId) {
            $task = $this->project->tasks()->findOrFail($this->editingTaskId);
            $service->updateTask($task, $data, auth()->user());
            $message = 'Tarea actualizada.';
        } else {
            $service->createTasks($this->project, [$data], auth()->user());
            $message = 'Tarea creada y asignada.';
        }

        $this->resetTaskForm();
        $this->refreshProject();
        Notification::make()->title($message)->success()->send();
    }

    public function updateTaskStatus(int $taskId, string $status): void
    {
        $task = $this->project->tasks()->with('assignees')->findOrFail($taskId);

        if ($this->project->isFinished()) {
            Notification::make()
                ->title('Proyecto finalizado')
                ->body('Las tareas permanecen bloqueadas en estado Hecho.')
                ->warning()
                ->send();

            return;
        }

        $newStatus = TaskStatus::tryFrom($status);

        if (! $newStatus) {
            throw ValidationException::withMessages(['status' => 'Estado de tarea no válido.']);
        }

        app(ProjectService::class)->changeTaskStatus($task, $newStatus, auth()->user());
        $this->refreshProject();

        Notification::make()
            ->title('Estado actualizado')
            ->body("La tarea ahora está en {$newStatus->label()}.")
            ->success()
            ->send();
    }

    public function saveObservation(): void
    {
        $this->validate(['newObservation' => ['required', 'string', 'max:5000']]);

        app(ProjectService::class)->addComment(
            $this->project,
            auth()->user(),
            $this->newObservation,
            ProjectEntryType::Observation,
        );

        $this->newObservation = '';
        $this->refreshProject();
        Notification::make()->title('Observación registrada.')->success()->send();
    }

    public function saveNote(): void
    {
        $this->validate(['newNote' => ['required', 'string', 'max:5000']]);

        app(ProjectService::class)->addComment(
            $this->project,
            auth()->user(),
            $this->newNote,
            ProjectEntryType::Note,
        );

        $this->newNote = '';
        $this->refreshProject();
        Notification::make()->title('Nota de seguimiento registrada.')->success()->send();
    }

    public function saveCost(): void
    {
        $this->validate([
            'costAmount' => ['required', 'numeric', 'min:0.01'],
            'costDescription' => ['required', 'string', 'max:255'],
            'costDate' => ['required', 'date'],
        ]);

        app(ProjectService::class)->addCost($this->project, auth()->user(), [
            'amount' => $this->costAmount,
            'description' => $this->costDescription,
            'incurred_on' => $this->costDate,
        ]);

        $this->costAmount = '';
        $this->costDescription = '';
        $this->refreshProject();
        Notification::make()->title('Costo registrado.')->success()->send();
    }

    public function saveImprovementOpportunities(): void
    {
        $this->validate(['improvementOpportunities' => ['required', 'string', 'max:10000']]);

        app(ProjectService::class)->setImprovementOpportunities(
            $this->project,
            auth()->user(),
            $this->improvementOpportunities,
        );

        $this->refreshProject();
        Notification::make()->title('Oportunidades de mejora guardadas.')->success()->send();
    }

    public function cloneProject(): mixed
    {
        Gate::authorize('clone', $this->project);
        $copy = app(ProjectService::class)->cloneProject($this->project, auth()->user());

        Notification::make()->title('Proyecto clonado.')->success()->send();

        return redirect()->to(static::getUrl(['project' => $copy]));
    }

    public function closeTaskForm(): void
    {
        $this->resetTaskForm();
    }

    private function resetTaskForm(): void
    {
        $this->showTaskForm = false;
        $this->editingTaskId = null;
        $this->taskDescription = '';
        $this->taskStartDate = '';
        $this->taskEndDate = '';
        $this->taskAssigneeIds = [];
        $this->resetValidation();
    }

    private function refreshProject(): void
    {
        $this->project = Project::query()
            ->with([
                'owner',
                'leader',
                'tasks.assignees',
                'comments.user',
                'costs.user',
            ])
            ->findOrFail($this->project->id);
    }
}
