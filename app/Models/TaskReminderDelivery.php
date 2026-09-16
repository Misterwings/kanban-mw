<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReminderDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'user_id', 'reminder_date', 'sent_at'];

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
