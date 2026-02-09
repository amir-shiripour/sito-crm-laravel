<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking_settings')) {
            return;
        }

        Schema::table('booking_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_settings', 'allow_appointment_entry_exit_times')) {
                $table->boolean('allow_appointment_entry_exit_times')->default(false)->after('operator_appointment_flow');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_settings')) {
            return;
        }

        Schema::table('booking_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booking_settings', 'allow_appointment_entry_exit_times')) {
                $table->dropColumn('allow_appointment_entry_exit_times');
            }
        });
    }
};
