<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'is_template')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('is_template')->default(false)->after('progress')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'is_template')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('is_template');
            });
        }
    }
};
