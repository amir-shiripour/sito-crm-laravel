<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_services', 'installments')) {
                    $column = $table->json('installments')->nullable();
                    if (Schema::hasColumn('booking_services', 'custom_prices')) {
                        $column->after('custom_prices');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::table('booking_services', function (Blueprint $table) {
                if (Schema::hasColumn('booking_services', 'installments')) {
                    $table->dropColumn('installments');
                }
            });
        }
    }
};
