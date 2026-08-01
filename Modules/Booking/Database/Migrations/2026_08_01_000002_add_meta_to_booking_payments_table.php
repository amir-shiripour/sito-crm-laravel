<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_payments') && !Schema::hasColumn('booking_payments', 'meta')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->json('meta')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_payments') && Schema::hasColumn('booking_payments', 'meta')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }
    }
};
