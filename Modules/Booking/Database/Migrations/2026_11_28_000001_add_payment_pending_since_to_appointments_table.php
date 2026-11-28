<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'payment_pending_since')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->timestamp('payment_pending_since')->nullable()->after('cancel_reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'payment_pending_since')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('payment_pending_since');
            });
        }
    }
};
