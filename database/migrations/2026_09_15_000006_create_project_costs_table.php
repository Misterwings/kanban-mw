<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_costs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('incurred_on');
            $table->timestamps();

            $table->index(['project_id', 'incurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_costs');
    }
};
