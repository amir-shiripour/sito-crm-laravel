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
                if (!Schema::hasColumn('service_package_items', 'custom_fields_quantities')) {
                    $table->json('custom_fields_quantities')->nullable()->after('custom_fields_prices');
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
                if (Schema::hasColumn('service_package_items', 'custom_fields_quantities')) {
                    $table->dropColumn('custom_fields_quantities');
                }
            });
        }
    }
};
