<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_categories')) {
            Schema::table('property_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('property_categories', 'icon')) {
                    $table->string('icon', 100)->nullable()->after('color');
                }
                if (!Schema::hasColumn('property_categories', 'image')) {
                    $table->string('image', 255)->nullable()->after('icon');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_categories')) {
            Schema::table('property_categories', function (Blueprint $table) {
                if (Schema::hasColumn('property_categories', 'image')) {
                    $table->dropColumn('image');
                }
                if (Schema::hasColumn('property_categories', 'icon')) {
                    $table->dropColumn('icon');
                }
            });
        }
    }
};
