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
        Schema::table('service_package_items', function (Blueprint $table) {
            if (!Schema::hasColumn('service_package_items', 'custom_fields_discounts')) {
                $table->json('custom_fields_discounts')->nullable()->after('custom_fields_prices');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_package_items', function (Blueprint $table) {
            if (Schema::hasColumn('service_package_items', 'custom_fields_discounts')) {
                $table->dropColumn('custom_fields_discounts');
            }
        });
    }
};
