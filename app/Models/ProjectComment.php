<?php

namespace App\Models;

use App\Enums\ProjectEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'user_id', 'type', 'body'];

    protected function casts(): array
    {
        return ['type' => ProjectEntryType::class];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
