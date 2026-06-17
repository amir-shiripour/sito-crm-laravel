<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_product_reviews', function (Blueprint $table) {
            $table->foreignId('vendor_product_id')
                ->nullable()
                ->after('client_id')
                ->constrained('market_vendor_products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('market_product_reviews', function (Blueprint $table) {
            $table->dropForeign(['vendor_product_id']);
            $table->dropColumn('vendor_product_id');
        });
    }
};
