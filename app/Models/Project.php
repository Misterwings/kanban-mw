<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'leader_id',
        'owner_id',
        'status',
        'completed_at',
        'cloned_from_id',
        'improvement_opportunities',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ProjectStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function clonedFrom()
    {
        return $this->belongsTo(self::class, 'cloned_from_id');
    }

    public function clones()
    {
        return $this->hasMany(self::class, 'cloned_from_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position')->orderBy('id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function costs()
    {
        return $this->hasMany(ProjectCost::class)->latest('incurred_on')->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isObserver()) {
            return $query;
        }

        return $query->where(function (Builder $visible) use ($user): void {
            $visible
                ->where('owner_id', $user->getKey())
                ->orWhere('leader_id', $user->getKey())
                ->orWhereHas('tasks.assignees', function (Builder $tasks) use ($user): void {
                    $tasks->whereKey($user->getKey());
                });
        });
    }

    public function isFinished(): bool
    {
        return $this->status === ProjectStatus::Finished;
    }

    public function isActive(): bool
    {
        return ! $this->isFinished();
    }

    public function isCloneDraft(): bool
    {
        return $this->cloned_from_id !== null
            && $this->start_date === null
            && $this->end_date === null
            && $this->leader_id === null;
    }

    public function totalCost(): float
    {
        return (float) $this->costs()->sum('amount');
    }
}
