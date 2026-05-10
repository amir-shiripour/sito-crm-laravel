<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_settings', 'user_appointment_flow')) {
                $table->string('user_appointment_flow', 20)
                    ->default('SERVICE_FIRST')
                    ->after('operator_appointment_flow');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booking_settings', 'user_appointment_flow')) {
                $table->dropColumn('user_appointment_flow');
            }
        });
    }
};
