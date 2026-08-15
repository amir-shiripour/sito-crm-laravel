<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                if (!Schema::hasColumn('service_package_items', 'custom_fields')) {
                    $table->json('custom_fields')->nullable()->after('billing_period');
                }
                if (!Schema::hasColumn('service_package_items', 'custom_fields_prices')) {
                    $table->json('custom_fields_prices')->nullable()->after('custom_fields');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                $table->dropColumn(['custom_fields', 'custom_fields_prices']);
            });
        }
    }
};
