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
        Schema::create('warehouse_purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 60)->unique();
            $table->string('invoice_number', 100)->nullable()->index();
            $table->date('purchase_date')->index();
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->string('supplier_name', 255)->nullable()->index();
            $table->unsignedInteger('total_items_count')->default(0);
            $table->decimal('total_quantity', 14, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->boolean('update_current_prices')->default(false);
            $table->string('status', 30)->default('registered')->index(); // registered, cancelled
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('warehouse_purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_receipt_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 18, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('purchase_receipt_id')
                ->references('id')
                ->on('warehouse_purchase_receipts')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('warehouse_items')
                ->restrictOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->restrictOnDelete();

            $table->foreign('batch_id')
                ->references('id')
                ->on('warehouse_item_batches')
                ->nullOnDelete();
        });

        Schema::table('warehouse_item_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_receipt_id')->nullable()->after('warehouse_id')->index();

            $table->foreign('purchase_receipt_id')
                ->references('id')
                ->on('warehouse_purchase_receipts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_item_batches', function (Blueprint $table) {
            $table->dropForeign(['purchase_receipt_id']);
            $table->dropColumn('purchase_receipt_id');
        });

        Schema::dropIfExists('warehouse_purchase_receipt_items');
        Schema::dropIfExists('warehouse_purchase_receipts');
    }
};
