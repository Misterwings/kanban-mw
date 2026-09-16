<?php

use App\Enums\ProjectEntryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type')->default(ProjectEntryType::Observation->value);
            $table->text('body');
            $table->timestamps();

            $table->index(['project_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};
