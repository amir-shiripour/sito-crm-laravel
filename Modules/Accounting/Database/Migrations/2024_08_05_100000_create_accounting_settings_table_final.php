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
        // Drop the table if it exists to ensure a clean slate
        Schema::dropIfExists('accounting_settings');

        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            // No timestamps needed for this table
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_settings');
    }
};
