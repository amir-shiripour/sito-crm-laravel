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
        Schema::create('warehouse_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['inventory', 'asset'])->default('inventory')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('is_main')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('color', 10)->nullable();
            $table->string('code', 50)->nullable()->index();
            $table->string('location')->nullable();
            $table->string('manager')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('warehouse_warehouses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_warehouses');
    }
};
