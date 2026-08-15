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
        Schema::table('accounting_fund_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_fund_accounts', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('type')->constrained('accounting_categories')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('accounting_fund_accounts', 'category_id')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE `accounting_fund_accounts` DROP FOREIGN KEY `accounting_fund_accounts_category_id_foreign`');
            } catch (\Throwable $e) {}

            Schema::table('accounting_fund_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('accounting_fund_accounts', 'category_id')) {
                    $table->dropColumn('category_id');
                }
            });
        }
    }
};
