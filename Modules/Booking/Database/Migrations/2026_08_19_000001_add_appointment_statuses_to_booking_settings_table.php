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
                if (!Schema::hasColumn('booking_settings', 'appointment_statuses')) {
                    $col = $table->json('appointment_statuses')->nullable();
                    if (Schema::hasColumn('booking_settings', 'ads')) {
                        $col->after('ads');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                if (Schema::hasColumn('booking_settings', 'appointment_statuses')) {
                    $table->dropColumn('appointment_statuses');
                }
            });
        }
    }
};
