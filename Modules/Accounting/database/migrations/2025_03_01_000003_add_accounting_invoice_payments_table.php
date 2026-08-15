<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('accounting_invoice_payments')) {
            Schema::create('accounting_invoice_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('accounting_invoices')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('amount');
                $table->string('method'); // cash, transfer, pos, cheque, online
                $table->foreignId('fund_account_id')->nullable()->constrained('accounting_fund_accounts')->nullOnDelete();
                $table->foreignId('cheque_id')->nullable()->constrained('accounting_cheques')->nullOnDelete();
                $table->string('transaction_ref')->nullable();
                $table->timestamp('paid_at');
                $table->text('notes')->nullable();
                $table->enum('status', ['active', 'canceled'])->default('active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_invoice_payments');
    }
};
