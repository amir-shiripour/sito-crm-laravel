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
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'building_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unsignedBigInteger('building_id')->nullable()->after('document_type');
                // فعلاً فارن کی نمی‌زنیم چون جدول buildings هنوز وجود ندارد
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
        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'building_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('building_id');
            });
        }
    }
};
