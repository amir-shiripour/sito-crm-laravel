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
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_settings', 'allow_manual_time_override')) {
                    $col = $table->boolean('allow_manual_time_override')->default(false);
                    if (Schema::hasColumn('booking_settings', 'allow_appointment_entry_exit_times')) {
                        $col->after('allow_appointment_entry_exit_times');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'allow_manual_time_override')) {
                    $table->dropColumn('allow_manual_time_override');
                }
            });
        }
    }
};
