<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_services') && !Schema::hasColumn('booking_services', 'credit_client_wallet')) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->boolean('credit_client_wallet')->default(false)->after('auto_confirm_online_booking');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services') && Schema::hasColumn('booking_services', 'credit_client_wallet')) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->dropColumn('credit_client_wallet');
            });
        }
    }
};
