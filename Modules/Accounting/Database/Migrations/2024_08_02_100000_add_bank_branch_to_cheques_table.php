<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('accounting_cheques', 'bank_branch')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->string('bank_branch')->nullable()->after('bank_name');
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
        if (Schema::hasColumn('accounting_cheques', 'bank_branch')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->dropColumn('bank_branch');
            });
        }
    }
};
