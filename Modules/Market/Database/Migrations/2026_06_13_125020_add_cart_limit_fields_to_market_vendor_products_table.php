<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('market_vendor_products', function (Blueprint $table) {
            $table->decimal('cart_amount_step', 15, 2)->nullable()->after('max_purchase_qty');
            $table->integer('purchase_step')->nullable()->after('cart_amount_step');
        });
    }

    public function down(): void {
        Schema::table('market_vendor_products', function (Blueprint $table) {
            $table->dropColumn(['cart_amount_step', 'purchase_step']);
        });
    }
};
