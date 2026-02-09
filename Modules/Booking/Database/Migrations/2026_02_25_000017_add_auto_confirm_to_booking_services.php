<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'auto_confirm_online_booking')) {
                    $table->boolean('auto_confirm_online_booking')->default(false)->after('online_booking_mode');
                }
            });
        }

        if (Schema::hasTable('booking_service_providers')) {
            Schema::table('booking_service_providers', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_service_providers', 'override_auto_confirm')) {
                    $table->boolean('override_auto_confirm')->nullable()->after('override_online_booking_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'auto_confirm_online_booking')) {
                    $table->dropColumn('auto_confirm_online_booking');
                }
            });
        }

        if (Schema::hasTable('booking_service_providers')) {
            Schema::table('booking_service_providers', function (Blueprint $table) {
                if (Schema::hasColumn('booking_service_providers', 'override_auto_confirm')) {
                    $table->dropColumn('override_auto_confirm');
                }
            });
        }
    }
};
