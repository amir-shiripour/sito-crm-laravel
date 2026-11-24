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
        Schema::create('warehouse_item_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('location_in_warehouse', 255)->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('warehouse_items')
                ->cascadeOnDelete();

            $table->unique(['warehouse_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_item_stocks');
    }
};
