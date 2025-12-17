<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_settings', 'operator_appointment_flow')) {
                $table->string('operator_appointment_flow', 30)->default('PROVIDER_FIRST')->after('service_form_selection_scope');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booking_settings', 'operator_appointment_flow')) {
                $table->dropColumn('operator_appointment_flow');
            }
        });
    }
};
