<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('projects_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->date('due_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('projects_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_checklist_items');
    }
};
