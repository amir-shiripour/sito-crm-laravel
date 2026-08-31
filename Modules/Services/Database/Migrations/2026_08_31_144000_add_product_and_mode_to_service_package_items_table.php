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
        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                if (!Schema::hasColumn('service_package_items', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->after('service_id');
                }
                if (!Schema::hasColumn('service_package_items', 'product_variant_id')) {
                    $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('service_package_items', 'mode')) {
                    $table->string('mode')->default('service')->after('product_variant_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                if (Schema::hasColumn('service_package_items', 'mode')) {
                    $table->dropColumn('mode');
                }
                if (Schema::hasColumn('service_package_items', 'product_variant_id')) {
                    $table->dropColumn('product_variant_id');
                }
                if (Schema::hasColumn('service_package_items', 'product_id')) {
                    $table->dropColumn('product_id');
                }
            });
        }
    }
};
