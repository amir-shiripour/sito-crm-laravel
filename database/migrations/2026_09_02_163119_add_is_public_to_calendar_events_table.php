<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('calendar_events') && !Schema::hasColumn('calendar_events', 'is_public')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->boolean('is_public')->default(true)->after('is_all_day')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('calendar_events') && Schema::hasColumn('calendar_events', 'is_public')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }
    }
};
