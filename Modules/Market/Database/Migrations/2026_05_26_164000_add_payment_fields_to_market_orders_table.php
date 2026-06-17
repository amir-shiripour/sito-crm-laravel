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
        Schema::table('market_orders', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('payment_status');
            $table->string('payment_ref_id')->nullable()->after('transaction_id');
            $table->timestamp('paid_at')->nullable()->after('payment_ref_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_orders', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_ref_id', 'paid_at']);
        });
    }
};
