<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Change the ENUM values for the 'type' column
        DB::statement("ALTER TABLE `accounting_cheques` CHANGE COLUMN `type` `type` ENUM('receivable', 'payable') NOT NULL COMMENT 'دریافتی / پرداختی'");

        // Add 'bank_branch' column if it doesn't exist
        if (!Schema::hasColumn('accounting_cheques', 'bank_branch')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->string('bank_branch')->nullable()->after('bank_name');
            });
        }

        // Add 'registered_by_user_id' column if it doesn't exist
        if (!Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->foreignId('registered_by_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
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
        // Revert the ENUM change
        DB::statement("ALTER TABLE `accounting_cheques` CHANGE COLUMN `type` `type` ENUM('incoming', 'outgoing') NOT NULL");

        // Drop columns if they exist
        if (Schema::hasColumn('accounting_cheques', 'bank_branch')) {
            Schema::table('accounting_cheques', function (Blueprint $table) {
                $table->dropColumn('bank_branch');
            });
        }
        if (Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
            try {
                DB::statement('ALTER TABLE `accounting_cheques` DROP FOREIGN KEY `accounting_cheques_registered_by_user_id_foreign`');
            } catch (\Throwable $e) {}

            Schema::table('accounting_cheques', function (Blueprint $table) {
                if (Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
                    $table->dropColumn('registered_by_user_id');
                }
            });
        }
    }
};
