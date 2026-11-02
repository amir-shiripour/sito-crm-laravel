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
                if (!Schema::hasColumn('property_rental_configs', 'discount_type')) {
                    $table->string('discount_type')->default('percent')->after('has_discount'); // 'percent' or 'fixed'
                }
                if (!Schema::hasColumn('property_rental_configs', 'discount_value')) {
                    $table->decimal('discount_value', 15, 0)->nullable()->after('discount_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_rental_configs')) {
            Schema::table('property_rental_configs', function (Blueprint $table) {
                $columns = ['discount_type', 'discount_value'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('property_rental_configs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
