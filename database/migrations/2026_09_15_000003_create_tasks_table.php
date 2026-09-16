<?php

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default(TaskStatus::Pending->value);
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status', 'position']);
            $table->index(['start_date', 'end_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
