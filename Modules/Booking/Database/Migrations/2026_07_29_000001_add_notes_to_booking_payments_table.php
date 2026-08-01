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
        if (Schema::hasTable('booking_payments') && !Schema::hasColumn('booking_payments', 'notes')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('gateway_ref');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_payments') && Schema::hasColumn('booking_payments', 'notes')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
