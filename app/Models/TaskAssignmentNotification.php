<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentNotification extends Model
{
    use HasFactory;

    protected $fillable = ['operation_id', 'user_id', 'task_ids', 'sent_at'];

    protected function casts(): array
    {
        return [
            'task_ids' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
