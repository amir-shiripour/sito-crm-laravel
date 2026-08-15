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
        DB::statement("ALTER TABLE `accounting_cheques` CHANGE COLUMN `status` `status` ENUM('pending', 'deposited', 'cleared', 'bounced', 'transferred', 'returned') NOT NULL DEFAULT 'pending' COMMENT 'در جریان / واگذار به بانک / وصول شده / برگشتی / خرج شده / عودت داده شده'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to the previous ENUM values
        DB::statement("ALTER TABLE `accounting_cheques` CHANGE COLUMN `status` `status` ENUM('pending', 'deposited', 'cleared', 'bounced', 'transferred') NOT NULL DEFAULT 'pending' COMMENT 'در جریان / واگذار به بانک / وصول شده / برگشتی / خرج شده'");
    }
};
