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
        if (!Schema::hasTable('accounting_proformas')) {
            Schema::create('accounting_proformas', function (Blueprint $table) {
                $table->id();
                $table->string('proforma_number')->unique();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->date('issue_date');
                $table->date('valid_until')->nullable();
                $table->unsignedBigInteger('subtotal')->default(0);
                $table->unsignedBigInteger('discount_amount')->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->unsignedBigInteger('tax_amount')->default(0);
                $table->unsignedBigInteger('total')->default(0);
                $table->string('currency', 10)->default('IRR');
                $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'converted', 'expired'])->default('draft');
                $table->unsignedBigInteger('converted_to_invoice_id')->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_proformas');
    }
};
