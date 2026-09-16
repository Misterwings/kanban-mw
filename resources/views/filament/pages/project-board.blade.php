<x-filament-panels::page>
    <style>
        .kb-page { --kb-ink: #172033; --kb-muted: #667085; --kb-line: #dbe5e5; --kb-paper: #f6f9f8; --kb-teal: #0f766e; color: var(--kb-ink); }
        .kb-hero { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1.25rem 1.5rem; border: 1px solid var(--kb-line); border-radius: 1.25rem; background: linear-gradient(135deg, #f2fbf7, #fff); box-shadow: 0 12px 30px rgba(17, 62, 62, .06); }
        .kb-kicker { margin: 0 0 .35rem; color: var(--kb-teal); font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .kb-title { margin: 0; font-size: clamp(1.4rem, 3vw, 2.25rem); letter-spacing: -.04em; }
        .kb-meta { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .85rem; color: var(--kb-muted); font-size: .8rem; }
        .kb-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .65rem; border-radius: 999px; background: #eaf5f1; color: #17665f; font-weight: 700; }
        .kb-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .kb-button { border: 0; border-radius: .7rem; padding: .65rem .9rem; cursor: pointer; font-size: .82rem; font-weight: 800; transition: transform .15s ease, box-shadow .15s ease; }
        .kb-button:hover { transform: translateY(-1px); box-shadow: 0 7px 15px rgba(17, 62, 62, .12); }
        .kb-button-primary { background: var(--kb-teal); color: white; }
        .kb-button-soft { background: #e7f3ef; color: #0f625c; }
        .kb-button-plain { background: transparent; color: var(--kb-muted); }
        .kb-banner { margin-top: 1rem; padding: .85rem 1rem; border-radius: .8rem; background: #fff6df; color: #8a5d08; font-size: .85rem; }
        .kb-banner-finished { background: #e8f7ef; color: #146c43; }
        .kb-view-switcher { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1.1rem; }
        .kb-view-caption { margin: 0; color: var(--kb-muted); font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .kb-view-tabs { display: inline-flex; gap: .25rem; padding: .25rem; border: 1px solid var(--kb-line); border-radius: .85rem; background: #edf5f2; }
        .kb-view-tab { min-height: 2.4rem; padding: .55rem .85rem; border: 0; border-radius: .6rem; background: transparent; color: #45605f; cursor: pointer; font: inherit; font-size: .78rem; font-weight: 800; }
        .kb-view-tab[aria-selected="true"] { background: #fff; color: var(--kb-teal); box-shadow: 0 4px 12px rgba(17, 62, 62, .1); }
        .kb-timeline-section { overflow: hidden; }
        .kb-timeline-intro { display: flex; justify-content: space-between; align-items: end; gap: 1rem; flex-wrap: wrap; }
        .kb-timeline-summary { display: flex; gap: .6rem; flex-wrap: wrap; color: var(--kb-muted); font-size: .75rem; }
        .kb-timeline-summary span { padding: .35rem .55rem; border-radius: .5rem; background: #f1f6f5; }
        .kb-timeline-scroll { overflow-x: auto; margin-top: 1rem; padding: 1.25rem .25rem .5rem; }
        .kb-timeline-chart { min-width: 58rem; }
        .kb-timeline-axis, .kb-timeline-row { display: grid; grid-template-columns: 14rem minmax(0, 1fr); }
        .kb-timeline-axis-label { align-self: end; padding: 0 .75rem .55rem 0; color: var(--kb-muted); font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .kb-timeline-axis-track, .kb-timeline-track { position: relative; }
        .kb-timeline-axis-track { height: 2.75rem; border-bottom: 1px solid var(--kb-line); }
        .kb-timeline-period { position: absolute; inset-block: 0; overflow: hidden; padding: .25rem .35rem; border-left: 1px solid var(--kb-line); color: var(--kb-muted); font-size: .68rem; font-weight: 800; white-space: nowrap; }
        .kb-timeline-period:last-of-type { border-right: 1px solid var(--kb-line); }
        .kb-timeline-marker { position: absolute; top: -.85rem; z-index: 5; color: var(--kb-teal); font-size: .62rem; font-weight: 900; text-transform: uppercase; white-space: nowrap; }
        .kb-timeline-marker-start { transform: translateX(0); }
        .kb-timeline-marker-end { transform: translateX(-100%); }
        .kb-timeline-row { min-height: 5.5rem; border-bottom: 1px solid #edf2f1; }
        .kb-timeline-task { display: grid; align-content: center; gap: .2rem; padding: .75rem .75rem .75rem 0; }
        .kb-timeline-task-title { overflow: hidden; color: var(--kb-ink); font-size: .82rem; font-weight: 850; text-overflow: ellipsis; white-space: nowrap; }
        .kb-timeline-task-meta { overflow: hidden; color: var(--kb-muted); font-size: .68rem; text-overflow: ellipsis; white-space: nowrap; }
        .kb-timeline-track { min-height: 5.5rem; background: rgba(238, 246, 244, .45); }
        .kb-timeline-grid-cell { position: absolute; inset-block: 0; border-left: 1px solid rgba(219, 229, 229, .8); }
        .kb-timeline-grid-cell:last-of-type { border-right: 1px solid rgba(219, 229, 229, .8); }
        .kb-timeline-bar { position: absolute; top: 1.55rem; z-index: 3; display: flex; align-items: center; min-width: 1.25rem; height: 2.35rem; overflow: hidden; padding: 0 .65rem; border: 1px solid transparent; border-radius: .65rem; color: #fff; cursor: pointer; font: inherit; font-size: .7rem; font-weight: 850; text-align: left; text-overflow: ellipsis; white-space: nowrap; box-shadow: 0 5px 12px rgba(17, 62, 62, .12); transition: transform .15s ease, box-shadow .15s ease; }
        .kb-timeline-bar:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(17, 62, 62, .18); }
        .kb-timeline-bar-pending { background: #768b89; }
        .kb-timeline-bar-in-progress { background: #c47719; }
        .kb-timeline-bar-done { background: #1d856b; }
        .kb-timeline-bar-readonly { cursor: default; }
        .kb-timeline-boundary, .kb-timeline-today { position: absolute; inset-block: 0; z-index: 2; width: 1px; pointer-events: none; }
        .kb-timeline-boundary { border-left: 1px dashed #74a59d; }
        .kb-timeline-today { width: 2px; background: #dc684d; }
        .kb-timeline-today-label { position: absolute; top: -.95rem; left: .3rem; color: #b34c37; font-size: .62rem; font-weight: 900; white-space: nowrap; }
        .kb-timeline-legend { display: flex; gap: .65rem; flex-wrap: wrap; margin-top: 1rem; color: var(--kb-muted); font-size: .7rem; }
        .kb-timeline-legend span { display: inline-flex; align-items: center; gap: .3rem; }
        .kb-timeline-legend i { display: inline-block; width: .65rem; height: .65rem; border-radius: .2rem; }
        .kb-timeline-legend .pending { background: #768b89; }
        .kb-timeline-legend .in-progress { background: #c47719; }
        .kb-timeline-legend .done { background: #1d856b; }
        .kb-timeline-empty { display: grid; place-items: center; min-height: 12rem; padding: 2rem; border: 1px dashed var(--kb-line); border-radius: .85rem; background: #f8fbfa; text-align: center; }
        .kb-timeline-empty strong { color: var(--kb-ink); font-size: .95rem; }
        .kb-timeline-empty p { max-width: 30rem; margin: .4rem 0 0; color: var(--kb-muted); font-size: .78rem; }
        .kb-unscheduled { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--kb-line); }
        .kb-unscheduled-list { display: grid; gap: .5rem; margin: .75rem 0 0; padding: 0; list-style: none; }
        .kb-unscheduled-item { display: flex; justify-content: space-between; align-items: center; gap: .75rem; padding: .65rem .75rem; border: 1px solid var(--kb-line); border-radius: .7rem; background: #fbfdfc; }
        .kb-unscheduled-item strong { display: block; color: var(--kb-ink); font-size: .78rem; }
        .kb-unscheduled-item span { display: block; margin-top: .2rem; color: var(--kb-muted); font-size: .68rem; }
        .kb-board { display: grid; grid-template-columns: repeat(3, minmax(260px, 1fr)); gap: 1rem; margin-top: 1.25rem; overflow-x: auto; padding-bottom: .35rem; }
        .kb-column { min-height: 22rem; padding: .8rem; border: 1px solid var(--kb-line); border-radius: 1rem; background: var(--kb-paper); }
        .kb-column-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .75rem; }
        .kb-column-title { margin: 0; font-size: .95rem; font-weight: 850; }
        .kb-column-count { min-width: 1.65rem; padding: .2rem .4rem; border-radius: 999px; background: #fff; color: var(--kb-muted); text-align: center; font-size: .75rem; font-weight: 800; }
        .kb-dropzone { min-height: 18rem; display: grid; align-content: start; gap: .65rem; }
        .kb-dropzone.is-editable { padding: .3rem; border-radius: .75rem; }
        .kb-dropzone.is-editable:focus-within, .kb-dropzone.is-editable:hover { outline: 2px dashed #99c9bf; outline-offset: 3px; }
        .kb-card { position: relative; padding: .85rem; border: 1px solid #d8e3e2; border-radius: .85rem; background: #fff; box-shadow: 0 5px 12px rgba(17, 62, 62, .045); }
        .kb-card[draggable="true"] { cursor: grab; }
        .kb-card[draggable="true"]:active { cursor: grabbing; opacity: .7; }
        .kb-card-description { margin: 0; font-size: .88rem; font-weight: 750; line-height: 1.35; }
        .kb-card-dates { margin: .55rem 0 0; color: var(--kb-muted); font-size: .72rem; }
        .kb-assignees { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .65rem; }
        .kb-assignee { padding: .2rem .45rem; border-radius: .45rem; background: #eef4f4; color: #45605f; font-size: .68rem; font-weight: 700; }
        .kb-card-footer { display: flex; justify-content: space-between; gap: .4rem; align-items: center; margin-top: .75rem; }
        .kb-status-select, .kb-input, .kb-textarea { width: 100%; border: 1px solid #cad9d7; border-radius: .65rem; background: #fff; color: var(--kb-ink); font: inherit; }
        .kb-status-select { width: auto; padding: .28rem .35rem; font-size: .7rem; }
        .kb-input { padding: .65rem .7rem; font-size: .82rem; }
        .kb-textarea { min-height: 7rem; padding: .7rem; resize: vertical; font-size: .82rem; }
        .kb-field { display: grid; gap: .35rem; }
        .kb-field label { color: #526565; font-size: .73rem; font-weight: 800; }
        .kb-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .7rem; }
        .kb-form-grid .full { grid-column: 1 / -1; }
        .kb-section { margin-top: 1.25rem; padding: 1rem; border: 1px solid var(--kb-line); border-radius: 1rem; background: #fff; }
        .kb-section-head { display: flex; justify-content: space-between; gap: .75rem; align-items: baseline; margin-bottom: .85rem; }
        .kb-section-title { margin: 0; font-size: 1rem; font-weight: 850; }
        .kb-section-subtitle { margin: 0; color: var(--kb-muted); font-size: .75rem; }
        .kb-followup-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 1rem; }
        .kb-entry { padding: .7rem 0; border-top: 1px solid #edf2f1; }
        .kb-entry:first-child { border-top: 0; padding-top: 0; }
        .kb-entry-meta { color: var(--kb-muted); font-size: .7rem; }
        .kb-entry-body { margin: .25rem 0 0; white-space: pre-wrap; font-size: .83rem; }
        .kb-cost { display: flex; justify-content: space-between; gap: .7rem; align-items: start; padding: .7rem 0; border-top: 1px solid #edf2f1; }
        .kb-cost:first-child { border-top: 0; padding-top: 0; }
        .kb-total { margin: .75rem 0 0; padding-top: .75rem; border-top: 1px solid var(--kb-line); color: var(--kb-teal); font-size: 1rem; font-weight: 850; }
        .kb-modal-backdrop { position: fixed; z-index: 50; inset: 0; display: grid; place-items: center; padding: 1rem; background: rgba(9, 27, 28, .45); }
        .kb-modal { width: min(40rem, 100%); max-height: calc(100vh - 2rem); overflow-y: auto; padding: 1.2rem; border-radius: 1rem; background: #fff; box-shadow: 0 20px 60px rgba(0,0,0,.22); }
        .kb-modal-head { display: flex; justify-content: space-between; gap: .75rem; align-items: start; margin-bottom: 1rem; }
        .kb-error { color: #b42318; font-size: .72rem; }
        .kb-muted { color: var(--kb-muted); font-size: .76rem; }
        .kb-readonly-status { display: inline-flex; align-items: center; min-height: 2rem; padding: .25rem .55rem; border-radius: .55rem; background: #edf5f2; color: #17665f; font-size: .72rem; font-weight: 800; }
        .kb-button:focus-visible, .kb-input:focus-visible, .kb-textarea:focus-visible, .kb-status-select:focus-visible { outline: 3px solid #2d8c80; outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { .kb-button { transition: none; } .kb-button:hover { transform: none; } }
        @media (max-width: 900px) { .kb-board { grid-template-columns: repeat(3, minmax(280px, 86vw)); } .kb-followup-grid { grid-template-columns: 1fr; } }
        @media (max-width: 560px) { .kb-form-grid { grid-template-columns: 1fr; } .kb-form-grid .full { grid-column: auto; } .kb-hero { padding: 1rem; } .kb-actions { width: 100%; } .kb-button { flex: 1; } }
        .dark .kb-page { --kb-ink: #e8f0ef; --kb-muted: #9aacab; --kb-line: #2f4445; --kb-paper: #172627; --kb-teal: #61c6b7; }
        .dark .kb-hero, .dark .kb-section, .dark .kb-card, .dark .kb-modal { background: #132021; }
        .dark .kb-hero { background: linear-gradient(135deg, #173332, #132021); }
        .dark .kb-card, .dark .kb-input, .dark .kb-textarea, .dark .kb-status-select { border-color: #385253; background: #18292a; color: #e8f0ef; }
        .dark .kb-column-count, .dark .kb-assignee, .dark .kb-view-tabs, .dark .kb-timeline-summary span { background: #243a3a; color: #b8d1ce; }
        .dark .kb-view-tab { color: #b8d1ce; }
        .dark .kb-view-tab[aria-selected="true"] { background: #132021; color: #8fe0d1; }
        .dark .kb-timeline-track { background: rgba(23, 47, 48, .7); }
        .dark .kb-timeline-grid-cell { border-color: rgba(56, 82, 83, .75); }
        .dark .kb-timeline-empty, .dark .kb-unscheduled-item { background: #18292a; }
        .dark .kb-banner-finished, .dark .kb-readonly-status { background: #173b2d; color: #a9e4c3; }
        @media (max-width: 700px) { .kb-view-switcher { align-items: flex-start; flex-direction: column; } .kb-view-tabs { width: 100%; } .kb-view-tab { flex: 1; } .kb-timeline-chart { min-width: 52rem; } }
    </style>

    <div class="kb-page" x-data="{ draggingTaskId: null, dropStatus(status) { if (this.draggingTaskId) { $wire.updateTaskStatus(this.draggingTaskId, status); this.draggingTaskId = null; } } }">
        <section class="kb-hero">
            <div>
                <p class="kb-kicker">Espacio de trabajo</p>
                <h1 class="kb-title">{{ $project->name }}</h1>
                <div class="kb-meta">
                    <span class="kb-pill">{{ $project->status->label() }}</span>
                    <span>Líder: {{ $project->leader?->name ?? 'Pendiente de asignar' }}</span>
                    <span>Propietario: {{ $project->owner->name }}</span>
                    @if ($project->start_date || $project->end_date)
                        <span>{{ $project->start_date?->format('d/m/Y') ?? 'Sin fecha' }} · {{ $project->end_date?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                    @endif
                </div>
            </div>
            @if ($this->canEditBoard)
                <div class="kb-actions">
                    <button type="button" class="kb-button kb-button-primary" wire:click="openNewTask">+ Nueva tarea</button>
                </div>
            @endif
        </section>

        <nav class="kb-view-switcher" aria-label="Cambiar vista del proyecto">
            <p class="kb-view-caption">Vista del proyecto</p>
            <div class="kb-view-tabs" role="tablist" aria-label="Vistas disponibles">
                <button
                    id="kanban-tab"
                    type="button"
                    class="kb-view-tab"
                    role="tab"
                    aria-selected="{{ $viewMode === 'kanban' ? 'true' : 'false' }}"
                    aria-controls="kanban-view"
                    wire:click="setViewMode('kanban')"
                >Tablero Kanban</button>
                <button
                    id="timeline-tab"
                    type="button"
                    class="kb-view-tab"
                    role="tab"
                    aria-selected="{{ $viewMode === 'timeline' ? 'true' : 'false' }}"
                    aria-controls="timeline-view"
                    wire:click="setViewMode('timeline')"
                >Línea de tiempo</button>
            </div>
        </nav>

        @if ($project->isFinished())
            <div class="kb-banner kb-banner-finished" role="status"><strong>Proyecto finalizado.</strong> Las tareas están bloqueadas en estado Hecho.</div>
        @elseif ($project->isCloneDraft())
            <div class="kb-banner">Esta copia está lista para configurar. Completa fechas, líder, fechas de tareas y responsables antes de iniciar el seguimiento.</div>
        @endif

        @if ($viewMode === 'kanban')
            <div id="kanban-view" role="tabpanel" aria-labelledby="kanban-tab">
                <section class="kb-board" aria-label="Tablero Kanban">
                    @foreach (\App\Enums\TaskStatus::cases() as $status)
                        <article class="kb-column">
                            <div class="kb-column-head">
                                <h2 class="kb-column-title">{{ $status->label() }}</h2>
                                <span class="kb-column-count">{{ count($this->tasksByStatus[$status->value] ?? []) }}</span>
                            </div>
                            <div class="kb-dropzone {{ $this->canEditBoard ? 'is-editable' : '' }}"
                                @if ($this->canEditBoard)
                                    @dragover.prevent
                                    @drop.prevent="dropStatus('{{ $status->value }}')"
                                @endif
                            >
                                @forelse ($this->tasksByStatus[$status->value] ?? [] as $task)
                                    <div class="kb-card" wire:key="task-{{ $task->id }}"
                                        @if ($this->canEditBoard && ! $task->isDraft())
                                            draggable="true"
                                            @dragstart="draggingTaskId = {{ $task->id }}"
                                        @endif
                                    >
                                        <p class="kb-card-description">{{ $task->description }}</p>
                                        <p class="kb-card-dates">
                                            @if ($task->start_date && $task->end_date)
                                                {{ $task->start_date->format('d/m/Y') }} a {{ $task->end_date->format('d/m/Y') }}
                                            @else
                                                Fechas pendientes de asignar
                                            @endif
                                        </p>
                                        <div class="kb-assignees">
                                            @forelse ($task->assignees as $assignee)
                                                <span class="kb-assignee">{{ $assignee->name }}</span>
                                            @empty
                                                <span class="kb-muted">Sin responsables</span>
                                            @endforelse
                                        </div>
                                        <div class="kb-card-footer">
                                            @if ($this->canEditBoard && ! $task->isDraft())
                                                <select class="kb-status-select" wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)" aria-label="Cambiar estado de {{ $task->description }}">
                                                    @foreach (\App\Enums\TaskStatus::cases() as $option)
                                                        <option value="{{ $option->value }}" @selected($task->status === $option)>{{ $option->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="kb-button kb-button-plain" wire:click="openEditTask({{ $task->id }})">Editar</button>
                                            @elseif ($this->canEditBoard)
                                                <span class="kb-muted">Configura la tarea para moverla</span>
                                                <button type="button" class="kb-button kb-button-plain" wire:click="openEditTask({{ $task->id }})">Editar</button>
                                            @else
                                                <span class="{{ $project->isFinished() ? 'kb-readonly-status' : 'kb-muted' }}" @if ($project->isFinished()) title="Las tareas están bloqueadas porque el proyecto finalizó." @endif>{{ $task->status->label() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="kb-muted">{{ $this->canEditBoard ? 'Arrastra aquí una tarea o crea una nueva.' : 'No hay tareas en esta columna.' }}</p>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                </section>
            </div>
        @else
            @include('filament.pages.project-timeline', ['timeline' => $this->timeline])
        @endif

        <section class="kb-followup-grid">
            <div class="kb-section">
                <div class="kb-section-head">
                    <div>
                        <h2 class="kb-section-title">Seguimiento</h2>
                        <p class="kb-section-subtitle">Observaciones y notas del proyecto.</p>
                    </div>
                </div>
                <div class="kb-form-grid">
                    <form class="kb-field full" wire:submit.prevent="saveObservation">
                        <label for="newObservation">Nueva observación</label>
                        <textarea id="newObservation" class="kb-textarea" wire:model="newObservation" placeholder="Comparte un hallazgo o comentario..."></textarea>
                        @error('newObservation') <span class="kb-error">{{ $message }}</span> @enderror
                        <button class="kb-button kb-button-soft" type="submit">Guardar observación</button>
                    </form>
                    @if ($this->canAddNote)
                        <form class="kb-field full" wire:submit.prevent="saveNote">
                            <label for="newNote">Nota de seguimiento</label>
                            <textarea id="newNote" class="kb-textarea" wire:model="newNote" placeholder="Registra información operativa mientras el proyecto está activo..."></textarea>
                            @error('newNote') <span class="kb-error">{{ $message }}</span> @enderror
                            <button class="kb-button kb-button-primary" type="submit">Guardar nota</button>
                        </form>
                    @endif
                </div>
                <div style="margin-top: 1rem;">
                    @forelse ($project->comments as $comment)
                        <article class="kb-entry">
                            <div class="kb-entry-meta">{{ $comment->type->label() }} · {{ $comment->user->name }} · {{ $comment->created_at->format('d/m/Y H:i') }}</div>
                            <p class="kb-entry-body">{{ $comment->body }}</p>
                        </article>
                    @empty
                        <p class="kb-muted">Aún no hay entradas de seguimiento.</p>
                    @endforelse
                </div>
            </div>

            <div class="kb-section">
                <div class="kb-section-head">
                    <div>
                        <h2 class="kb-section-title">Costos</h2>
                        <p class="kb-section-subtitle">Valores registrados en pesos colombianos.</p>
                    </div>
                </div>
                @if ($this->canAddCost)
                    <form class="kb-form-grid" wire:submit.prevent="saveCost">
                        <div class="kb-field">
                            <label for="costAmount">Valor (COP)</label>
                            <input id="costAmount" class="kb-input" type="number" min="0.01" step="0.01" wire:model="costAmount" placeholder="0">
                            @error('costAmount') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-field">
                            <label for="costDate">Fecha</label>
                            <input id="costDate" class="kb-input" type="date" wire:model="costDate">
                            @error('costDate') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-field full">
                            <label for="costDescription">Descripción</label>
                            <input id="costDescription" class="kb-input" type="text" wire:model="costDescription" placeholder="¿En qué se utilizó?"><br>
                            @error('costDescription') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <button class="kb-button kb-button-primary full" type="submit">Registrar costo</button>
                    </form>
                @endif
                <div style="margin-top: 1rem;">
                    @forelse ($project->costs as $cost)
                        <article class="kb-cost">
                            <div><strong>{{ $cost->description }}</strong><div class="kb-entry-meta">{{ $cost->incurred_on->format('d/m/Y') }} · {{ $cost->user->name }}</div></div>
                            <strong>${{ number_format((float) $cost->amount, 2, ',', '.') }}</strong>
                        </article>
                    @empty
                        <p class="kb-muted">Aún no hay costos registrados.</p>
                    @endforelse
                    <p class="kb-total">Total: ${{ number_format((float) $project->costs->sum('amount'), 2, ',', '.') }} COP</p>
                </div>
            </div>
        </section>

        @if ($project->isFinished())
            <section class="kb-section">
                <div class="kb-section-head">
                    <div>
                        <h2 class="kb-section-title">Oportunidades de mejora</h2>
                        <p class="kb-section-subtitle">Retroalimentación disponible después del cierre del proyecto.</p>
                    </div>
                </div>
                @if ($this->canAddImprovement)
                    <form class="kb-field" wire:submit.prevent="saveImprovementOpportunities">
                        <textarea class="kb-textarea" wire:model="improvementOpportunities" placeholder="¿Qué debería hacerse diferente en proyectos futuros?"></textarea>
                        @error('improvementOpportunities') <span class="kb-error">{{ $message }}</span> @enderror
                        <button class="kb-button kb-button-primary" type="submit">Guardar retroalimentación</button>
                    </form>
                @elseif ($project->improvement_opportunities)
                    <p class="kb-entry-body">{{ $project->improvement_opportunities }}</p>
                @else
                    <p class="kb-muted">Aún no se han registrado oportunidades de mejora.</p>
                @endif
            </section>
        @endif

        @if ($showTaskForm)
            <div class="kb-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="task-form-title">
                <div class="kb-modal">
                    <div class="kb-modal-head">
                        <div><p class="kb-kicker">{{ $editingTaskId ? 'Editar tarea' : 'Nueva tarea' }}</p><h2 id="task-form-title" class="kb-section-title">Información de la tarea</h2></div>
                        <button type="button" class="kb-button kb-button-plain" wire:click="closeTaskForm">Cerrar</button>
                    </div>
                    <form class="kb-form-grid" wire:submit.prevent="saveTask">
                        <div class="kb-field full">
                            <label for="taskDescription">Descripción</label>
                            <textarea id="taskDescription" class="kb-textarea" wire:model="taskDescription" placeholder="Describe el resultado esperado..."></textarea>
                            @error('description') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-field">
                            <label for="taskStartDate">Fecha de inicio</label>
                            <input id="taskStartDate" class="kb-input" type="date" wire:model="taskStartDate">
                            @error('start_date') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-field">
                            <label for="taskEndDate">Fecha final</label>
                            <input id="taskEndDate" class="kb-input" type="date" wire:model="taskEndDate">
                            @error('end_date') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-field full">
                            <label for="taskAssigneeIds">Responsables</label>
                            <select id="taskAssigneeIds" class="kb-input" multiple wire:model="taskAssigneeIds" size="5">
                                @foreach ($this->assignableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }}</option>
                                @endforeach
                            </select>
                            <span class="kb-muted">Usa Ctrl/Cmd para seleccionar varios responsables.</span>
                            @error('assignee_ids') <span class="kb-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="kb-actions full">
                            <button type="button" class="kb-button kb-button-plain" wire:click="closeTaskForm">Cancelar</button>
                            <button type="submit" class="kb-button kb-button-primary">Guardar tarea</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
