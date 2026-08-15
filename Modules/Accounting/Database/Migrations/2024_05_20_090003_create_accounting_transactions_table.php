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
            $table->foreignId('document_id')->constrained('accounting_documents')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('accounting_categories');
            $table->foreignId('fund_account_id')->nullable()->constrained('accounting_fund_accounts')->nullOnDelete();

            $table->decimal('debit', 15, 2)->default(0)->comment('مبلغ بدهکار');
            $table->decimal('credit', 15, 2)->default(0)->comment('مبلغ بستانکار');

            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
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
        Schema::dropIfExists('accounting_transactions');
    }
};
