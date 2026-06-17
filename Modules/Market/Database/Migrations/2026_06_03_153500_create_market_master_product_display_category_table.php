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
        Schema::create('market_product_display_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_product_id')->constrained('market_master_products')->cascadeOnDelete();
            $table->foreignId('display_category_id')->constrained('market_display_categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_product_display_category');
    }
};
