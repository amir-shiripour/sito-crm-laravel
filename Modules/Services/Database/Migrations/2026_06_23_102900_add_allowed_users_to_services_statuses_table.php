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
        Schema::table('services_statuses', function (Blueprint $table) {
            $table->json('allowed_users')->nullable()->after('allowed_roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services_statuses', function (Blueprint $table) {
            $table->dropColumn('allowed_users');
        });
    }
};
