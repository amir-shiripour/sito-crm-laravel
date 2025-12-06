<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('task_type', 50)->default('GENERAL');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('status', 50)->default('TODO');
            $table->string('priority', 50)->default('MEDIUM');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();

            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['task_type', 'status', 'assignee_id']);
            $table->index(['related_type', 'related_id']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
