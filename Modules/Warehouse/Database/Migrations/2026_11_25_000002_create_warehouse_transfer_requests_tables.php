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
        Schema::create('warehouse_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 60)->unique();
            $table->string('request_number', 100)->nullable()->index();
            $table->date('request_date')->index();
            $table->unsignedBigInteger('source_warehouse_id')->nullable()->index();
            $table->unsignedBigInteger('destination_warehouse_id')->index();
            $table->unsignedBigInteger('requester_user_id')->nullable()->index();
            $table->string('requester_name', 255)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedInteger('total_items_count')->default(0);
            $table->decimal('total_requested_quantity', 14, 2)->default(0);
            $table->decimal('total_delivered_quantity', 14, 2)->default(0);
            $table->decimal('total_cost_value', 18, 2)->default(0);
            $table->string('status', 30)->default('delivered')->index(); // pending, delivered, rejected, cancelled
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->nullOnDelete();

            $table->foreign('destination_warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->restrictOnDelete();

            $table->foreign('requester_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('warehouse_transfer_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_request_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('requested_quantity', 12, 2);
            $table->decimal('delivered_quantity', 12, 2)->default(0);
            $table->decimal('total_cost_value', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('transfer_request_id')
                ->references('id')
                ->on('warehouse_transfer_requests')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('warehouse_items')
                ->restrictOnDelete();
        });

        Schema::table('warehouse_item_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_request_id')->nullable()->after('purchase_receipt_id')->index();
            $table->unsignedBigInteger('source_batch_id')->nullable()->after('transfer_request_id')->index();

            $table->foreign('transfer_request_id')
                ->references('id')
                ->on('warehouse_transfer_requests')
                ->nullOnDelete();

            $table->foreign('source_batch_id')
                ->references('id')
                ->on('warehouse_item_batches')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_item_batches', function (Blueprint $table) {
            $table->dropForeign(['transfer_request_id']);
            $table->dropForeign(['source_batch_id']);
            $table->dropColumn(['transfer_request_id', 'source_batch_id']);
        });

        Schema::dropIfExists('warehouse_transfer_request_items');
        Schema::dropIfExists('warehouse_transfer_requests');
    }
};
