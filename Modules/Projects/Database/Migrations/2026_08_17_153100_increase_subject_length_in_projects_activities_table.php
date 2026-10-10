<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('projects_activities') && Schema::hasColumn('projects_activities', 'subject')) {
            Schema::table('projects_activities', function (Blueprint $table) {
                $table->text('subject')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects_activities') && Schema::hasColumn('projects_activities', 'subject')) {
            Schema::table('projects_activities', function (Blueprint $table) {
                $table->string('subject', 120)->nullable()->change();
            });
        }
    }
};
