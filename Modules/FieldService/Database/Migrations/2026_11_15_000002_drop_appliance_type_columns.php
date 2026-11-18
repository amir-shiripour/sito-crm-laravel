<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_service_categories') && Schema::hasColumn('field_service_categories', 'appliance_type')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->dropColumn('appliance_type');
            });
        }

        if (Schema::hasTable('field_service_orders') && Schema::hasColumn('field_service_orders', 'appliance_type')) {
            Schema::table('field_service_orders', function (Blueprint $table) {
                $table->dropColumn('appliance_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_categories') && ! Schema::hasColumn('field_service_categories', 'appliance_type')) {
            Schema::table('field_service_categories', function (Blueprint $table) {
                $table->string('appliance_type')->nullable()->after('icon');
            });
        }

        if (Schema::hasTable('field_service_orders') && ! Schema::hasColumn('field_service_orders', 'appliance_type')) {
            Schema::table('field_service_orders', function (Blueprint $table) {
                $table->string('appliance_type')->nullable()->after('vendor_id');
            });
        }
    }
};
