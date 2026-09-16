<?php

use App\Enums\ProjectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default(ProjectStatus::Created->value)->index();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('cloned_from_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('improvement_opportunities')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['leader_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
