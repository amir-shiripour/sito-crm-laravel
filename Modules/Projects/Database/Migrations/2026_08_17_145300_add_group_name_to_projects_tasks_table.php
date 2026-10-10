<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('projects_tasks') && !Schema::hasColumn('projects_tasks', 'group_name')) {
            Schema::table('projects_tasks', function (Blueprint $table) {
                $table->string('group_name', 120)->nullable()->after('status_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects_tasks') && Schema::hasColumn('projects_tasks', 'group_name')) {
            Schema::table('projects_tasks', function (Blueprint $table) {
                $table->dropColumn('group_name');
            });
        }
    }
};
