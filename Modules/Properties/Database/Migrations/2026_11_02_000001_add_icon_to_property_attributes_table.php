<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_attributes') && !Schema::hasColumn('property_attributes', 'icon')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->string('icon', 100)->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_attributes') && Schema::hasColumn('property_attributes', 'icon')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
    }
};
