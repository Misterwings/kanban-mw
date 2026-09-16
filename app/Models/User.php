<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'leader_id');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class)->withPivot('assigned_at');
    }

    public function projectComments()
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function projectCosts()
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCollaborator(): bool
    {
        return $this->role === UserRole::Collaborator;
    }

    public function isObserver(): bool
    {
        return $this->role === UserRole::Observer;
    }

    public function isAssignable(): bool
    {
        return $this->is_active && in_array($this->role, [UserRole::Admin, UserRole::Collaborator], true);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->role instanceof UserRole;
    }

    public function scopeAssignable($query)
    {
        return $query
            ->where('is_active', true)
            ->whereIn('role', [UserRole::Admin->value, UserRole::Collaborator->value])
            ->orderBy('name');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }
}
