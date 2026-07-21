<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the main table with NO foreign key constraints
        Schema::create('service_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('status_id');

            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();

            $table->date('issue_date');
            $table->date('due_date')->nullable();

            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);

            $table->string('currency')->default('toman');
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Create items table
        Schema::create('service_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('service_id')->nullable();

            $table->string('description');
            $table->string('unit')->default('item');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->json('custom_field_values')->nullable();

            $table->timestamps();
        });

        // 3. Add basic constraints that we know exist (users)
        Schema::table('service_invoices', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        // 4. Add invoice_items constraint
        Schema::table('service_invoice_items', function (Blueprint $table) {
            $table->foreign('invoice_id')
                ->references('id')
                ->on('service_invoices')
                ->cascadeOnDelete();
        });

        // Note: project_id, service_id, status_id will be added later when those tables exist
    }

    public function down(): void
    {
        Schema::dropIfExists('service_invoice_items');
        Schema::dropIfExists('service_invoices');
    }
};
