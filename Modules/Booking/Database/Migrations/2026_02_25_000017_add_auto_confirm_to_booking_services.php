<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $table->boolean('auto_confirm_online_booking')->default(false)->after('online_booking_mode');
        });

        Schema::table('booking_service_providers', function (Blueprint $table) {
            $table->boolean('override_auto_confirm')->nullable()->after('override_online_booking_mode');
        });
    }

    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropColumn('auto_confirm_online_booking');
        });

        Schema::table('booking_service_providers', function (Blueprint $table) {
            $table->dropColumn('override_auto_confirm');
        });
    }
};
