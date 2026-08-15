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
        Schema::table('accounting_cheques', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
                $table->foreignId('registered_by_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
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
        if (Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE `accounting_cheques` DROP FOREIGN KEY `accounting_cheques_registered_by_user_id_foreign`');
            } catch (\Throwable $e) {}

            Schema::table('accounting_cheques', function (Blueprint $table) {
                if (Schema::hasColumn('accounting_cheques', 'registered_by_user_id')) {
                    $table->dropColumn('registered_by_user_id');
                }
            });
        }
    }
};
