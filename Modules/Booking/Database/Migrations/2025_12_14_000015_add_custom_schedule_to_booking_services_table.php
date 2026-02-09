<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'custom_schedule_enabled')) {
                    $table->boolean('custom_schedule_enabled')->default(false)->after('provider_can_customize');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'custom_schedule_enabled')) {
                    $table->dropColumn('custom_schedule_enabled');
                }
            });
        }
    }
};
