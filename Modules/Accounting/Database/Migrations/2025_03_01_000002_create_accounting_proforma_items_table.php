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
        if (!Schema::hasTable('accounting_proforma_items')) {
            Schema::create('accounting_proforma_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proforma_id')->constrained('accounting_proformas')->cascadeOnDelete();
                $table->string('description');
                $table->string('unit', 50)->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->unsignedBigInteger('unit_price');
                $table->unsignedBigInteger('discount')->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->unsignedBigInteger('tax_amount')->default(0);
                $table->unsignedBigInteger('total_price');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_proforma_items');
    }
};
