<section id="timeline-view" class="kb-section kb-timeline-section" role="tabpanel" aria-labelledby="timeline-tab" aria-label="Línea de tiempo del proyecto">
    <div class="kb-timeline-intro">
        <div>
            <p class="kb-kicker">Planificación visual</p>
            <h2 class="kb-section-title">Línea de tiempo</h2>
            <p class="kb-section-subtitle">Consulta la duración y el avance de cada tarea en el calendario del proyecto.</p>
        </div>
        @if ($timeline['has_range'])
            <div class="kb-timeline-summary" aria-label="Resumen del periodo">
                <span>{{ $timeline['range_start_label'] }} a {{ $timeline['range_end_label'] }}</span>
                <span>Duración estimada: {{ $timeline['total_days'] }} días</span>
                <span>{{ count($timeline['tasks']) }} tareas programadas</span>
            </div>
        @endif
    </div>

    @if ($timeline['has_range'])
        <div class="kb-timeline-scroll" tabindex="0" aria-label="Desplazamiento horizontal de la línea de tiempo">
            <div class="kb-timeline-chart">
                <div class="kb-timeline-axis">
                    <div class="kb-timeline-axis-label">Tareas</div>
                    <div class="kb-timeline-axis-track" aria-label="{{ $timeline['unit_label'] }} del proyecto">
                        @foreach ($timeline['periods'] as $period)
                            <div class="kb-timeline-period" style="left: {{ $period['left'] }}%; width: {{ $period['width'] }}%;" title="{{ $period['aria_label'] }}">
                                {{ $period['label'] }}
                            </div>
                        @endforeach
                        @if ($timeline['project_start_position'] !== null)
                            <span class="kb-timeline-marker kb-timeline-marker-start" style="left: {{ $timeline['project_start_position'] }}%;">Inicio</span>
                        @endif
                        @if ($timeline['project_end_position'] !== null)
                            <span class="kb-timeline-marker kb-timeline-marker-end" style="left: {{ $timeline['project_end_position'] }}%;">Fin</span>
                        @endif
                        @if ($timeline['today_position'] !== null)
                            <span class="kb-timeline-today" style="left: {{ $timeline['today_position'] }}%;"><span class="kb-timeline-today-label">Hoy</span></span>
                        @endif
                    </div>
                </div>

                @forelse ($timeline['tasks'] as $task)
                    @php
                        $barClass = match ($task['status']) {
                            'done' => 'kb-timeline-bar-done',
                            'in_progress' => 'kb-timeline-bar-in-progress',
                            default => 'kb-timeline-bar-pending',
                        };
                        $taskDates = $task['start_label'].' a '.$task['end_label'];
                        $taskAriaLabel = $task['description'].'. Estado: '.$task['status_label'].'. Fechas: '.$taskDates.'. Responsables: '.$task['assignees_label'].'.';
                    @endphp
                    <article class="kb-timeline-row" wire:key="timeline-task-{{ $task['id'] }}">
                        <div class="kb-timeline-task">
                            <strong class="kb-timeline-task-title" title="{{ $task['description'] }}">{{ $task['description'] }}</strong>
                            <span class="kb-timeline-task-meta">{{ $taskDates }}</span>
                            <span class="kb-timeline-task-meta">Responsables: {{ $task['assignees_label'] }}</span>
                        </div>
                        <div class="kb-timeline-track" aria-label="{{ $taskAriaLabel }}">
                            @foreach ($timeline['periods'] as $period)
                                <span class="kb-timeline-grid-cell" style="left: {{ $period['left'] }}%; width: {{ $period['width'] }}%;" aria-hidden="true"></span>
                            @endforeach
                            @if ($timeline['project_start_position'] !== null)
                                <span class="kb-timeline-boundary" style="left: {{ $timeline['project_start_position'] }}%;" aria-hidden="true"></span>
                            @endif
                            @if ($timeline['project_end_position'] !== null)
                                <span class="kb-timeline-boundary" style="left: {{ $timeline['project_end_position'] }}%;" aria-hidden="true"></span>
                            @endif
                            @if ($timeline['today_position'] !== null)
                                <span class="kb-timeline-today" style="left: {{ $timeline['today_position'] }}%;" aria-hidden="true"></span>
                            @endif
                            @if ($this->canEditBoard)
                                <button
                                    type="button"
                                    class="kb-timeline-bar {{ $barClass }}"
                                    style="left: {{ $task['left'] }}%; width: {{ $task['width'] }}%;"
                                    wire:click="openEditTask({{ $task['id'] }})"
                                    title="Editar {{ $taskAriaLabel }}"
                                    aria-label="Editar {{ $taskAriaLabel }}"
                                >{{ $task['description'] }}</button>
                            @else
                                <div
                                    class="kb-timeline-bar kb-timeline-bar-readonly {{ $barClass }}"
                                    style="left: {{ $task['left'] }}%; width: {{ $task['width'] }}%;"
                                    role="img"
                                    aria-label="{{ $taskAriaLabel }}"
                                    title="{{ $taskAriaLabel }}"
                                >{{ $task['description'] }}</div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="kb-timeline-empty">
                        <div>
                            <strong>No hay tareas con fechas para mostrar.</strong>
                            <p>Completa las fechas de inicio y final de las tareas para verlas en la línea de tiempo.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="kb-timeline-legend" aria-label="Leyenda de estados">
            <span><i class="pending" aria-hidden="true"></i>Pendiente</span>
            <span><i class="in-progress" aria-hidden="true"></i>En curso</span>
            <span><i class="done" aria-hidden="true"></i>Hecho</span>
            @if ($timeline['today_position'] !== null)
                <span><i style="background: #dc684d;" aria-hidden="true"></i>Hoy</span>
            @endif
        </div>
    @else
        <div class="kb-timeline-empty" style="margin-top: 1rem;">
            <div>
                <strong>Aún no hay fechas para construir la línea de tiempo.</strong>
                <p>Completa las fechas del proyecto o de sus tareas para visualizar la planificación.</p>
            </div>
        </div>
    @endif

    @if ($timeline['unscheduled_tasks'] !== [])
        <div class="kb-unscheduled">
            <div class="kb-section-head">
                <div>
                    <h3 class="kb-section-title">Tareas por programar</h3>
                    <p class="kb-section-subtitle">Estas tareas no tienen un periodo completo y no pueden ubicarse en el calendario.</p>
                </div>
            </div>
            <ul class="kb-unscheduled-list">
                @foreach ($timeline['unscheduled_tasks'] as $task)
                    <li class="kb-unscheduled-item" wire:key="unscheduled-task-{{ $task['id'] }}">
                        <div>
                            <strong>{{ $task['description'] }}</strong>
                            <span>{{ $task['status_label'] }} · Responsables: {{ $task['assignees_label'] }}</span>
                        </div>
                        @if ($this->canEditBoard)
                            <button type="button" class="kb-button kb-button-plain" wire:click="openEditTask({{ $task['id'] }})">Programar</button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
