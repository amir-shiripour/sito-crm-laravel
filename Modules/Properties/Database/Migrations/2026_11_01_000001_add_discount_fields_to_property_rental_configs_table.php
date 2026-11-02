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
                if (!Schema::hasColumn('property_rental_configs', 'has_discount')) {
                    $table->boolean('has_discount')->default(false)->after('price_holiday');
                }
                if (!Schema::hasColumn('property_rental_configs', 'discount_percent')) {
                    $table->unsignedTinyInteger('discount_percent')->nullable()->after('has_discount');
                }
                if (!Schema::hasColumn('property_rental_configs', 'old_daily_rate')) {
                    $table->decimal('old_daily_rate', 15, 0)->nullable()->after('discount_percent');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_rental_configs')) {
            Schema::table('property_rental_configs', function (Blueprint $table) {
                $columns = ['has_discount', 'discount_percent', 'old_daily_rate'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('property_rental_configs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
