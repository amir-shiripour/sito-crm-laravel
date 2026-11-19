<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_attributes') && !Schema::hasColumn('property_attributes', 'description')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->text('description')->nullable()->after('icon');
            });
        }

        if (Schema::hasTable('property_attribute_values') && !Schema::hasColumn('property_attribute_values', 'description')) {
            Schema::table('property_attribute_values', function (Blueprint $table) {
                $table->text('description')->nullable()->after('value');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_attributes') && Schema::hasColumn('property_attributes', 'description')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasTable('property_attribute_values') && Schema::hasColumn('property_attribute_values', 'description')) {
            Schema::table('property_attribute_values', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
