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
        Schema::table('market_vendor_vision_settings', function (Blueprint $table) {
            $table->json('selected_groups')->nullable()->after('default_customer_code');
            $table->json('selected_subgroups')->nullable()->after('selected_groups');
            $table->string('price_field', 30)->default('Pricef1')->after('selected_subgroups');
            $table->string('compare_price_field', 30)->nullable()->after('price_field');
            $table->json('selected_warehouses')->nullable()->after('compare_price_field');
            $table->string('initial_product_status', 20)->default('draft')->after('selected_warehouses');
            $table->json('cached_metadata')->nullable()->after('initial_product_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_vendor_vision_settings', function (Blueprint $table) {
            $table->dropColumn([
                'selected_groups',
                'selected_subgroups',
                'price_field',
                'compare_price_field',
                'selected_warehouses',
                'initial_product_status',
                'cached_metadata',
            ]);
        });
    }
};
