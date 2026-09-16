<x-filament-widgets::widget>
    <x-filament::section :heading="$heading" :description="$description">
        <div class="grid gap-4 lg:grid-cols-3">
            <section class="flex min-h-[18rem] flex-col overflow-hidden rounded-2xl border border-danger-200 bg-danger-50/60 p-4 dark:border-danger-400/30 dark:bg-danger-500/10" aria-labelledby="dashboard-overdue-heading">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 id="dashboard-overdue-heading" class="text-sm font-semibold text-gray-950 dark:text-white">Tareas vencidas</h3>
                            <p class="mt-0.5 text-xs text-danger-700 dark:text-danger-300">Necesitan acción</p>
                        </div>
                    </div>
                    <x-filament::badge color="danger">{{ $overdueTasks->count() }}</x-filament::badge>
                </div>

                <div class="mt-4 flex-1 space-y-2" role="list">
                    @forelse ($overdueTasks as $task)
                        <a
                            href="{{ $projectBoardUrl($task->project_id) }}"
                            wire:navigate.hover
                            wire:key="dashboard-overdue-task-{{ $task->id }}"
                            class="group flex items-start gap-3 rounded-xl border border-transparent bg-white/80 p-3 shadow-sm motion-safe:transition motion-safe:duration-200 motion-safe:ease-out motion-safe:hover:-translate-y-0.5 motion-safe:hover:border-danger-300 motion-safe:hover:shadow-md dark:bg-white/5 dark:hover:border-danger-400/50"
                            role="listitem"
                            aria-label="{{ $task->description }} en {{ $task->project->name }}, venció el {{ $task->end_date->format('d/m/Y') }}"
                        >
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">
                                <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-gray-950 dark:text-white">{{ $task->description }}</span>
                                <span class="mt-1 block truncate text-xs text-danger-700 dark:text-danger-300">{{ $task->project->name }} <span aria-hidden="true">·</span> Venció el {{ $task->end_date->format('d/m/Y') }}</span>
                            </span>
                            <x-filament::icon icon="heroicon-m-arrow-up-right" class="mt-1 h-4 w-4 shrink-0 text-danger-500 motion-safe:transition motion-safe:duration-200 motion-safe:group-hover:translate-x-0.5 motion-safe:group-hover:-translate-y-0.5" />
                        </a>
                    @empty
                        <div class="flex min-h-28 flex-col items-center justify-center rounded-xl border border-dashed border-danger-300 p-4 text-center dark:border-danger-400/30">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-success-600 dark:text-success-400" />
                            <p class="mt-2 text-sm font-medium text-gray-950 dark:text-white">Todo al día</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No hay tareas vencidas.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="flex min-h-[18rem] flex-col overflow-hidden rounded-2xl border border-warning-200 bg-warning-50/60 p-4 dark:border-warning-400/30 dark:bg-warning-500/10" aria-labelledby="dashboard-upcoming-heading">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 id="dashboard-upcoming-heading" class="text-sm font-semibold text-gray-950 dark:text-white">Próximos vencimientos</h3>
                            <p class="mt-0.5 text-xs text-warning-700 dark:text-warning-300">Planifica tu semana</p>
                        </div>
                    </div>
                    <x-filament::badge color="warning">{{ $upcomingTasks->count() }}</x-filament::badge>
                </div>

                <div class="mt-4 flex-1 space-y-2" role="list">
                    @forelse ($upcomingTasks as $task)
                        <a
                            href="{{ $projectBoardUrl($task->project_id) }}"
                            wire:navigate.hover
                            wire:key="dashboard-upcoming-task-{{ $task->id }}"
                            class="group flex items-start gap-3 rounded-xl border border-transparent bg-white/80 p-3 shadow-sm motion-safe:transition motion-safe:duration-200 motion-safe:ease-out motion-safe:hover:-translate-y-0.5 motion-safe:hover:border-warning-300 motion-safe:hover:shadow-md dark:bg-white/5 dark:hover:border-warning-400/50"
                            role="listitem"
                            aria-label="{{ $task->description }} en {{ $task->project->name }}, vence el {{ $task->end_date->format('d/m/Y') }}"
                        >
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                                <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-gray-950 dark:text-white">{{ $task->description }}</span>
                                <span class="mt-1 block truncate text-xs text-warning-700 dark:text-warning-300">{{ $task->project->name }} <span aria-hidden="true">·</span> Vence el {{ $task->end_date->format('d/m/Y') }}</span>
                            </span>
                            <x-filament::icon icon="heroicon-m-arrow-up-right" class="mt-1 h-4 w-4 shrink-0 text-warning-500 motion-safe:transition motion-safe:duration-200 motion-safe:group-hover:translate-x-0.5 motion-safe:group-hover:-translate-y-0.5" />
                        </a>
                    @empty
                        <div class="flex min-h-28 flex-col items-center justify-center rounded-xl border border-dashed border-warning-300 p-4 text-center dark:border-warning-400/30">
                            <x-filament::icon icon="heroicon-o-calendar" class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                            <p class="mt-2 text-sm font-medium text-gray-950 dark:text-white">Agenda despejada</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No hay vencimientos en los próximos 7 días.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="flex min-h-[18rem] flex-col overflow-hidden rounded-2xl border border-info-200 bg-info-50/60 p-4 dark:border-info-400/30 dark:bg-info-500/10" aria-labelledby="dashboard-setup-heading">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-300">
                            <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 id="dashboard-setup-heading" class="text-sm font-semibold text-gray-950 dark:text-white">Proyectos por configurar</h3>
                            <p class="mt-0.5 text-xs text-info-700 dark:text-info-300">Deja todo listo para empezar</p>
                        </div>
                    </div>
                    <x-filament::badge color="info">{{ $setupProjects->count() }}</x-filament::badge>
                </div>

                <div class="mt-4 flex-1 space-y-2" role="list">
                    @forelse ($setupProjects as $project)
                        @php
                            $missing = collect([
                                blank($project->leader_id) ? 'Líder' : null,
                                blank($project->start_date) ? 'Fecha de inicio' : null,
                                blank($project->end_date) ? 'Fecha final' : null,
                            ])->filter()->values();
                        @endphp
                        <a
                            href="{{ $projectBoardUrl($project->id) }}"
                            wire:navigate.hover
                            wire:key="dashboard-setup-project-{{ $project->id }}"
                            class="group block rounded-xl border border-transparent bg-white/80 p-3 shadow-sm motion-safe:transition motion-safe:duration-200 motion-safe:ease-out motion-safe:hover:-translate-y-0.5 motion-safe:hover:border-info-300 motion-safe:hover:shadow-md dark:bg-white/5 dark:hover:border-info-400/50"
                            role="listitem"
                            aria-label="{{ $project->name }}. Falta configurar: {{ $missing->implode(', ') }}"
                        >
                            <span class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-300">
                                    <x-filament::icon icon="heroicon-o-briefcase" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-950 dark:text-white">{{ $project->name }}</span>
                                <x-filament::icon icon="heroicon-m-arrow-up-right" class="h-4 w-4 shrink-0 text-info-500 motion-safe:transition motion-safe:duration-200 motion-safe:group-hover:translate-x-0.5 motion-safe:group-hover:-translate-y-0.5" />
                            </span>
                            <span class="mt-2 flex flex-wrap gap-1 pl-11">
                                @foreach ($missing as $field)
                                    <span class="rounded-md bg-info-100 px-1.5 py-0.5 text-[0.68rem] font-medium text-info-700 dark:bg-info-500/20 dark:text-info-300">{{ $field }}</span>
                                @endforeach
                            </span>
                        </a>
                    @empty
                        <div class="flex min-h-28 flex-col items-center justify-center rounded-xl border border-dashed border-info-300 p-4 text-center dark:border-info-400/30">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-success-600 dark:text-success-400" />
                            <p class="mt-2 text-sm font-medium text-gray-950 dark:text-white">Configuración al día</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No hay proyectos pendientes.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
