<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0); // محاسبه‌شده هنگام stop
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'user_id']);
            $table->index(['task_id', 'user_id']);

            if (Schema::hasTable('projects_tasks')) {
                $table->foreign('task_id')->references('id')->on('projects_tasks')->nullOnDelete();
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_time_logs');
    }
};
