<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('color', 30)->default('#6366f1');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        if (Schema::hasTable('projects_tasks') && !Schema::hasColumn('projects_tasks', 'phase_id')) {
            Schema::table('projects_tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('phase_id')->nullable()->after('status_id')->index();
                $table->foreign('phase_id')->references('id')->on('projects_phases')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects_tasks') && Schema::hasColumn('projects_tasks', 'phase_id')) {
            Schema::table('projects_tasks', function (Blueprint $table) {
                $table->dropForeign(['phase_id']);
                $table->dropColumn('phase_id');
            });
        }
        Schema::dropIfExists('projects_phases');
    }
};
