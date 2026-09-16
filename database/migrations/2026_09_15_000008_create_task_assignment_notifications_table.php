<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('operation_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('task_ids');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['operation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_notifications');
    }
};
