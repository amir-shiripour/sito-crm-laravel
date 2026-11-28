<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_issue_documents')) {
            Schema::create('warehouse_issue_documents', function (Blueprint $table) {
                $table->id();
                $table->string('issue_number', 50)->unique();
                $table->date('issue_date');
                $table->string('issue_type', 40)->default('patient_consumption')->index();
                $table->unsignedBigInteger('source_warehouse_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('patient_name', 190)->nullable();
                $table->string('patient_case_number', 100)->nullable()->index();
                $table->unsignedBigInteger('consumer_user_id')->nullable()->index();
                $table->string('consumer_name', 190)->nullable();
                $table->unsignedInteger('items_count')->default(0);
                $table->decimal('total_quantity', 14, 2)->default(0);
                $table->decimal('total_cost_value', 16, 2)->default(0);
                $table->string('status', 30)->default('registered')->index();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('source_warehouse_id')->references('id')->on('warehouse_warehouses')->nullOnDelete();
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
                $table->foreign('consumer_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('warehouse_issue_items')) {
            Schema::create('warehouse_issue_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('issue_document_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost_avg', 15, 2)->default(0);
                $table->decimal('total_cost', 16, 2)->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('issue_document_id')->references('id')->on('warehouse_issue_documents')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('warehouse_items')->cascadeOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('warehouse_warehouses')->cascadeOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('warehouse_issue_item_batches')) {
            Schema::create('warehouse_issue_item_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('issue_item_id')->index();
                $table->unsignedBigInteger('batch_id')->index();
                $table->decimal('quantity_deducted', 12, 2);
                $table->decimal('unit_purchase_price', 15, 2)->default(0);
                $table->decimal('total_cost', 16, 2)->default(0);
                $table->timestamps();

                $table->foreign('issue_item_id')->references('id')->on('warehouse_issue_items')->cascadeOnDelete();
                $table->foreign('batch_id')->references('id')->on('warehouse_item_batches')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_issue_item_batches');
        Schema::dropIfExists('warehouse_issue_items');
        Schema::dropIfExists('warehouse_issue_documents');
    }
};
