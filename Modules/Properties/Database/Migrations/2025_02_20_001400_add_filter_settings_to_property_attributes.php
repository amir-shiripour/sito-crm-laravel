<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('property_attributes')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('property_attributes', 'is_filterable')) {
                    $table->boolean('is_filterable')->default(false)->after('is_active');
                }

                if (!Schema::hasColumn('property_attributes', 'is_range_filter')) {
                    $table->boolean('is_range_filter')->default(false)->after('is_filterable');
                }
            });
        }
    }


    public function down(): void
    {
        Schema::table('property_attributes', function (Blueprint $table) {
            $table->dropColumn(['is_filterable', 'is_range_filter']);
        });
    }
};
