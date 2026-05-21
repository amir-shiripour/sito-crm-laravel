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
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_bank_id')->nullable()->comment('بانک مبدا (در صورت برداشت یا انتقال)')->constrained('accounting_banks')->nullOnDelete();
            $table->foreignId('to_bank_id')->nullable()->comment('بانک مقصد (در صورت واریز یا انتقال)')->constrained('accounting_banks')->nullOnDelete();
            $table->bigInteger('amount');
            $table->enum('type', ['deposit', 'withdraw', 'transfer'])->comment('نوع تراکنش: واریز، برداشت، انتقال');
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_transactions');
    }
};
