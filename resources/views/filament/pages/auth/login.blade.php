<x-filament-panels::page.simple class="kanban-login-page" heading="" subheading="">
    <div class="kanban-login-shell">
        <aside class="kanban-login-story" aria-labelledby="kanban-login-story-heading">
            <div class="kanban-login-brand">
                <span class="kanban-login-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/logo_mw.png') }}" alt="" />
                </span>
                <span>{{ filament()->getBrandName() }}</span>
            </div>

            <div class="kanban-login-story-copy">
                <p class="kanban-login-kicker">Flujo de trabajo, sin ruido</p>
                <h1 id="kanban-login-story-heading">Convierte cada pendiente en avance.</h1>
                <p>
                    Un espacio claro para organizar proyectos, coordinar equipos y mantener el trabajo en movimiento.
                </p>
            </div>

            <div class="kanban-login-board" aria-hidden="true">
                <div class="kanban-login-column">
                    <div class="kanban-login-column-heading">
                        <span>Por hacer</span>
                    </div>
                    <div class="kanban-login-board-card">
                        <span class="kanban-login-board-card-line"></span>
                        <span class="kanban-login-board-card-title">Definir alcance</span>
                        <span class="kanban-login-board-card-meta">Hoy</span>
                    </div>
                    <div class="kanban-login-board-card kanban-login-board-card--quiet">
                        <span class="kanban-login-board-card-line"></span>
                        <span class="kanban-login-board-card-title">Preparar agenda</span>
                    </div>
                </div>

                <div class="kanban-login-column">
                    <div class="kanban-login-column-heading">
                        <span>En curso</span>
                    </div>
                    <div class="kanban-login-board-card kanban-login-board-card--accent">
                        <span class="kanban-login-board-card-line"></span>
                        <span class="kanban-login-board-card-title">Revisar entregable</span>
                        <span class="kanban-login-board-card-meta">En equipo</span>
                    </div>
                </div>

                <div class="kanban-login-column">
                    <div class="kanban-login-column-heading">
                        <span>Hecho</span>
                    </div>
                    <div class="kanban-login-board-card kanban-login-board-card--done">
                        <span class="kanban-login-board-card-line"></span>
                        <span class="kanban-login-board-card-title">Publicar avance</span>
                        <span class="kanban-login-board-card-meta">Listo</span>
                    </div>
                </div>
            </div>

            <div class="kanban-login-story-footer">
                <span>Todo el trabajo visible. Cada siguiente paso, claro.</span>
            </div>
        </aside>

        <section class="kanban-login-form-panel" aria-labelledby="kanban-login-form-heading">
            <div class="kanban-login-form-wrap">
                <div class="kanban-login-form-header">
                    <span class="kanban-login-eyebrow">
                        <span class="kanban-login-status-dot" aria-hidden="true"></span>
                        Espacio de trabajo
                    </span>
                    <h2 id="kanban-login-form-heading">Bienvenido de nuevo</h2>
                    <p>Retoma el flujo de tu equipo desde donde lo dejaste.</p>
                </div>

                <div class="kanban-login-form">
                    {{ $this->content }}
                </div>

                <p class="kanban-login-security-note">
                    <x-filament::icon icon="heroicon-o-lock-closed" aria-hidden="true" />
                    Acceso seguro para tu equipo.
                </p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
