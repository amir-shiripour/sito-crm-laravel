<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects_checklist_items', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_checklist_items', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('task_id');
                if (Schema::hasTable('users')) {
                    $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                }
            }
            if (!Schema::hasColumn('projects_checklist_items', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('assigned_to');
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                }
            }
            if (!Schema::hasColumn('projects_checklist_items', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('projects_checklist_items', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects_checklist_items', function (Blueprint $table) {
            $columns = ['assigned_to', 'created_by', 'description', 'priority'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('projects_checklist_items', $col)) {
                    if (in_array($col, ['assigned_to', 'created_by'])) {
                        try { $table->dropForeign([$col]); } catch (\Exception $e) {}
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
