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
        if (!Schema::hasColumn('accounting_cheques', 'image_path')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('description');
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
        if (Schema::hasColumn('accounting_cheques', 'image_path')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};
