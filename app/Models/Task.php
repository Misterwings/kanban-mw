<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'description',
        'start_date',
        'end_date',
        'status',
        'position',
        'created_by_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_at');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopePendingReminder(Builder $query, string $date): Builder
    {
        return $query
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereHas('project', fn (Builder $project): Builder => $project->where('status', '!=', 'finished'));
    }

    public function isDraft(): bool
    {
        return $this->start_date === null || $this->end_date === null || $this->assignees->isEmpty();
    }
}
