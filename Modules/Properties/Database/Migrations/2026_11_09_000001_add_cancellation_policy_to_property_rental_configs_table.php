<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_rental_configs')) {
            Schema::table('property_rental_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('property_rental_configs', 'cancellation_policy')) {
                    $table->text('cancellation_policy')->nullable()->after('house_rules');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_rental_configs')) {
            Schema::table('property_rental_configs', function (Blueprint $table) {
                if (Schema::hasColumn('property_rental_configs', 'cancellation_policy')) {
                    $table->dropColumn('cancellation_policy');
                }
            });
        }
    }
};
