<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_tasks', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('assigned_to');
                if (Schema::hasTable('users')) {
                    $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('projects_tasks', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            }
        });
    }
};
