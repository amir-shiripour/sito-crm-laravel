<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('booking_settings', 'monitoring_quick_status_enabled')) {
                    $table->boolean('monitoring_quick_status_enabled')
                        ->default(false)
                        ->after('queue_max_size');
                }
                if (! Schema::hasColumn('booking_settings', 'monitoring_refresh_interval_seconds')) {
                    $table->unsignedInteger('monitoring_refresh_interval_seconds')
                        ->default(15)
                        ->after('monitoring_quick_status_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'monitoring_refresh_interval_seconds')) {
                    $table->dropColumn('monitoring_refresh_interval_seconds');
                }
                if (Schema::hasColumn('booking_settings', 'monitoring_quick_status_enabled')) {
                    $table->dropColumn('monitoring_quick_status_enabled');
                }
            });
        }
    }
};
