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
        Schema::create('accounting_cheques', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['incoming', 'outgoing'])->comment('دریافتی / پرداختی');
            $table->decimal('amount', 15, 2);
            $table->string('payee_name')->nullable();
            $table->string('cheque_number');
            $table->string('bank_name');
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('description')->nullable();
            
            $table->unsignedBigInteger('client_id')->nullable();
            
            $table->enum('status', ['pending', 'deposited', 'cleared', 'bounced', 'transferred'])->default('pending')->comment('در جریان / واگذار به بانک / وصول شده / برگشتی / خرج شده');
            
            $table->unsignedBigInteger('deposited_fund_account_id')->nullable();
            $table->foreign('deposited_fund_account_id')->references('id')->on('accounting_fund_accounts')->onDelete('set null');
            
            $table->unsignedBigInteger('cleared_fund_account_id')->nullable();
            $table->foreign('cleared_fund_account_id')->references('id')->on('accounting_fund_accounts')->onDelete('set null');
            
            $table->unsignedBigInteger('related_invoice_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_cheques');
    }
};
