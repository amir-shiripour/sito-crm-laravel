<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'owner_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->foreignId('owner_id')->nullable()->after('category_id')->constrained('property_owners')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'owner_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
                $table->dropColumn('owner_id');
            });
        }
    }
};
