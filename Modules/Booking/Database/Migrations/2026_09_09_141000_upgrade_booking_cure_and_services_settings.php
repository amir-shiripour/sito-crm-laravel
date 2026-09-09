<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'cure_dental_chart_enabled')) {
                    $table->boolean('cure_dental_chart_enabled')->default(true)->after('cure_show_tooth_filter');
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'requires_tooth_selection')) {
                    $table->boolean('requires_tooth_selection')->default(true)->after('custom_schedule_enabled');
                }
                if (!Schema::hasColumn('booking_services', 'base_price_mode')) {
                    $table->string('base_price_mode', 20)->default('per_unit')->after('base_price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'cure_dental_chart_enabled')) {
                    $table->dropColumn('cure_dental_chart_enabled');
                }
            });
        }

        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'requires_tooth_selection')) {
                    $table->dropColumn('requires_tooth_selection');
                }
                if (Schema::hasColumn('booking_services', 'base_price_mode')) {
                    $table->dropColumn('base_price_mode');
                }
            });
        }
    }
};
