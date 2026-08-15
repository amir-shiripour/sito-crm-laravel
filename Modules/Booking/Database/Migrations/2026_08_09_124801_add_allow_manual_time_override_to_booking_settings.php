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
        Schema::table('booking_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_settings', 'allow_manual_time_override')) {
                $table->boolean('allow_manual_time_override')->default(false)->after('allow_appointment_entry_exit_times');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booking_settings', 'allow_manual_time_override')) {
                $table->dropColumn('allow_manual_time_override');
            }
        });
    }
};
