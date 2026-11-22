<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendar_events') && !Schema::hasColumn('calendar_events', 'sms_config')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->json('sms_config')->nullable()->after('repeat_count');
            });
        }

        if (Schema::hasTable('calendar_event_exceptions') && !Schema::hasColumn('calendar_event_exceptions', 'sms_config')) {
            Schema::table('calendar_event_exceptions', function (Blueprint $table) {
                $table->json('sms_config')->nullable()->after('is_all_day');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendar_events') && Schema::hasColumn('calendar_events', 'sms_config')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropColumn('sms_config');
            });
        }

        if (Schema::hasTable('calendar_event_exceptions') && Schema::hasColumn('calendar_event_exceptions', 'sms_config')) {
            Schema::table('calendar_event_exceptions', function (Blueprint $table) {
                $table->dropColumn('sms_config');
            });
        }
    }
};
