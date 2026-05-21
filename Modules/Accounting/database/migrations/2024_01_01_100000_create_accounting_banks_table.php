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
        Schema::create('accounting_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('branch_name')->nullable();
            $table->string('account_holder_name');
            $table->string('account_number')->unique();
            $table->string('card_number')->unique()->nullable();
            $table->string('iban')->unique();
            $table->bigInteger('initial_balance')->default(0);
            $table->bigInteger('current_balance')->default(0);
            $table->string('currency')->default('IRR');
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_banks');
    }
};
