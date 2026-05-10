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
                if (!Schema::hasColumn('booking_settings', 'tax_enabled')) {
                    $table->boolean('tax_enabled')->default(false)->after('allow_appointment_entry_exit_times');
                }
                if (!Schema::hasColumn('booking_settings', 'tax_type')) {
                    $table->string('tax_type', 20)->default('PERCENT')->after('tax_enabled');
                }
                if (!Schema::hasColumn('booking_settings', 'tax_amount')) {
                    $table->decimal('tax_amount', 14, 2)->nullable()->after('tax_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_settings')) {
            Schema::table('booking_settings', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('booking_settings', 'tax_amount')) {
                    $columnsToDrop[] = 'tax_amount';
                }
                if (Schema::hasColumn('booking_settings', 'tax_type')) {
                    $columnsToDrop[] = 'tax_type';
                }
                if (Schema::hasColumn('booking_settings', 'tax_enabled')) {
                    $columnsToDrop[] = 'tax_enabled';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
