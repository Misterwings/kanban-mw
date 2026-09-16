<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Filament\Pages\ProjectBoard;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_dashboard_render(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('kanban-login-shell')
            ->assertSee('Convierte cada pendiente en avance.')
            ->assertSee('Bienvenido de nuevo')
            ->assertSee('Espacio de trabajo')
            ->assertSee('wire:submit="authenticate"', false);

        $admin = $this->makeUser(UserRole::Admin);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Escritorio')
            ->assertSee('Resumen de actividad')
            ->assertSee('Usuarios activos')
            ->assertSee('Atención operativa')
            ->assertSee('Portafolio de proyectos')
            ->assertSee('Todo al día')
            ->assertSee('Agenda despejada')
            ->assertSee('Configuración al día')
            ->assertSee('panel-page-enter')
            ->assertDontSee('wire:transition.navigate');
    }

    public function test_a_user_can_authenticate_with_its_password(): void
    {
        $user = $this->makeUser(UserRole::Admin, ['password' => 'password']);

        $this->assertTrue(Auth::attempt([
            'email' => $user->email,
            'password' => 'password',
        ]));
        $this->assertAuthenticatedAs($user);
    }

    public function test_observer_can_render_a_board_without_clone_action(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $observer = $this->makeUser(UserRole::Observer);
        $project = Project::create([
            'name' => 'Proyecto visible',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Created,
        ]);

        $this->actingAs($observer)
            ->get(ProjectBoard::getUrl(['project' => $project]))
            ->assertOk()
            ->assertSee('Proyecto visible')
            ->assertDontSee('wire:click="cloneProject"');
    }

    public function test_project_board_keeps_only_the_named_clone_action(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $project = Project::create([
            'name' => 'Proyecto clonable',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Created,
        ]);

        $this->actingAs($owner)
            ->get(ProjectBoard::getUrl(['project' => $project]))
            ->assertOk()
            ->assertSee('Clonar Proyecto')
            ->assertDontSee('wire:click="cloneProject"');
    }

    public function test_collaborator_and_observer_can_render_the_project_list(): void
    {
        foreach ([UserRole::Collaborator, UserRole::Observer] as $role) {
            $this->actingAs($this->makeUser($role))
                ->get('/admin/projects')
                ->assertOk();
        }
    }

    public function test_collaborator_dashboard_only_shows_its_workload(): void
    {
        $collaborator = $this->makeUser(UserRole::Collaborator);
        $otherOwner = $this->makeUser(UserRole::Collaborator);
        $visibleProject = Project::create([
            'name' => 'Proyecto visible del colaborador',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'leader_id' => $otherOwner->id,
            'owner_id' => $otherOwner->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $visibleTask = Task::create([
            'project_id' => $visibleProject->id,
            'description' => 'Tarea asignada al colaborador',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-18',
            'status' => TaskStatus::InProgress,
            'position' => 1,
            'created_by_id' => $otherOwner->id,
        ]);
        $visibleTask->assignees()->attach($collaborator->id);

        $privateProject = Project::create([
            'name' => 'Proyecto privado de otro usuario',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'leader_id' => $otherOwner->id,
            'owner_id' => $otherOwner->id,
            'status' => ProjectStatus::InProgress,
        ]);
        Task::create([
            'project_id' => $privateProject->id,
            'description' => 'Tarea privada de otro usuario',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-18',
            'status' => TaskStatus::InProgress,
            'position' => 1,
            'created_by_id' => $otherOwner->id,
        ]);

        $this->actingAs($collaborator)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Mis proyectos activos')
            ->assertSee('Tareas para hoy')
            ->assertSee('Mi agenda')
            ->assertSee('Proyecto visible del colaborador')
            ->assertSee('Tarea asignada al colaborador')
            ->assertSee('wire:navigate.hover')
            ->assertDontSee('Proyecto privado de otro usuario')
            ->assertDontSee('Tarea privada de otro usuario')
            ->assertDontSee('Usuarios activos');
    }

    public function test_observer_dashboard_shows_portfolio_without_admin_metrics(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $observer = $this->makeUser(UserRole::Observer);
        $project = Project::create([
            'name' => 'Proyecto del portafolio',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::InProgress,
        ]);
        Task::create([
            'project_id' => $project->id,
            'description' => 'Tarea del portafolio',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-18',
            'status' => TaskStatus::Pending,
            'position' => 1,
            'created_by_id' => $owner->id,
        ]);

        $this->actingAs($observer)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Proyectos activos')
            ->assertSee('Tareas abiertas')
            ->assertSee('Atención operativa')
            ->assertSee('Proyecto del portafolio')
            ->assertSee('Tarea del portafolio')
            ->assertDontSee('Usuarios activos')
            ->assertDontSee('Mis proyectos activos');
    }

    public function test_finished_project_board_is_read_only_for_task_statuses(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $project = Project::create([
            'name' => 'Proyecto finalizado',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::Finished,
            'completed_at' => now(),
        ]);
        Task::create([
            'project_id' => $project->id,
            'description' => 'Tarea completada',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'status' => TaskStatus::Done,
            'position' => 1,
            'created_by_id' => $owner->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(ProjectBoard::getUrl(['project' => $project]))
            ->assertOk()
            ->assertSee('Proyecto finalizado.')
            ->assertSee('Las tareas están bloqueadas en estado Hecho.')
            ->assertDontSee('wire:change="updateTaskStatus')
            ->assertDontSee('draggable="true"');
    }

    public function test_project_board_can_switch_between_kanban_and_timeline_views(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $project = Project::create([
            'name' => 'Proyecto con calendario',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
            'leader_id' => $owner->id,
            'owner_id' => $owner->id,
            'status' => ProjectStatus::InProgress,
        ]);
        Task::create([
            'project_id' => $project->id,
            'description' => 'Tarea programada',
            'start_date' => '2026-09-16',
            'end_date' => '2026-09-18',
            'status' => TaskStatus::InProgress,
            'position' => 1,
            'created_by_id' => $owner->id,
        ]);
        Task::create([
            'project_id' => $project->id,
            'description' => 'Tarea por programar',
            'start_date' => null,
            'end_date' => null,
            'status' => TaskStatus::Pending,
            'position' => 2,
            'created_by_id' => $owner->id,
        ]);

        $this->actingAs($owner);

        Livewire::test(ProjectBoard::class, ['project' => $project])
            ->assertSet('viewMode', 'kanban')
            ->assertSee('Tablero Kanban')
            ->assertSee('Línea de tiempo')
            ->call('setViewMode', 'timeline')
            ->assertSet('viewMode', 'timeline')
            ->assertSee('Tarea programada')
            ->assertSee('Tareas por programar')
            ->assertSee('Tarea por programar')
            ->assertDontSee('wire:change="updateTaskStatus');

        $this->assertDatabaseHas('tasks', [
            'id' => $project->tasks()->where('description', 'Tarea programada')->value('id'),
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function test_timeline_chooses_an_adaptive_time_scale(): void
    {
        $owner = $this->makeUser(UserRole::Collaborator);
        $this->actingAs($owner);
        $ranges = [
            ['2026-09-15', '2026-09-30', 'day', 16],
            ['2026-01-01', '2026-04-15', 'week', 16],
            ['2026-01-01', '2026-06-30', 'month', 6],
        ];

        foreach ($ranges as [$startDate, $endDate, $expectedUnit, $expectedPeriods]) {
            $project = Project::create([
                'name' => "Proyecto {$expectedUnit}",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'leader_id' => $owner->id,
                'owner_id' => $owner->id,
                'status' => ProjectStatus::InProgress,
            ]);

            $page = new ProjectBoard;
            $page->mount($project);
            $timeline = $page->getTimelineProperty();

            $this->assertSame($expectedUnit, $timeline['unit']);
            $this->assertCount($expectedPeriods, $timeline['periods']);
        }
    }

    /** @param array<string, mixed> $attributes */
    private function makeUser(UserRole $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'is_active' => true,
        ], $attributes));
    }
}
