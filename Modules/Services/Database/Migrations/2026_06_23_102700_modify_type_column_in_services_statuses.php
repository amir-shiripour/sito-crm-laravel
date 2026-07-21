<?php

use Illuminate\Database\Migrations\Migration;
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
        DB::statement("ALTER TABLE `services_statuses` MODIFY `type` VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // بازگشت به حالت قبل (در صورت نیاز)
        DB::statement("ALTER TABLE `services_statuses` MODIFY `type` ENUM('project', 'invoice', 'payment') NOT NULL");
    }
};
