<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kanban.test'],
            [
                'name' => 'Administrador Kanban',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'colaborador@kanban.test'],
            [
                'name' => 'Colaborador Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Collaborator,
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'observador@kanban.test'],
            [
                'name' => 'Observador Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Observer,
                'is_active' => true,
            ],
        );
    }
}
