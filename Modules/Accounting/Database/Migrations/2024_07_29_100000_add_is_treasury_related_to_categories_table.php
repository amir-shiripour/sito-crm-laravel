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
        if (!Schema::hasColumn('accounting_categories', 'is_treasury_related')) {
            Schema::table('accounting_categories', function (Blueprint $table) {
                $table->boolean('is_treasury_related')->default(false)->after('is_system');
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
        if (Schema::hasColumn('accounting_categories', 'is_treasury_related')) {
            Schema::table('accounting_categories', function (Blueprint $table) {
                $table->dropColumn('is_treasury_related');
            });
        }
    }
};
