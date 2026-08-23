<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 20)->default('#6366f1');
            $table->string('icon', 50)->nullable();
            $table->enum('type', ['project', 'task', 'checklist'])->default('project');
            $table->boolean('is_final')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->json('attributes')->nullable();
            $table->json('allowed_transitions')->nullable();
            $table->json('allowed_roles')->nullable();
            $table->json('allowed_users')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_statuses');
    }
};
