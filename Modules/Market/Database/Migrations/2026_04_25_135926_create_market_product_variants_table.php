<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_product_id')->constrained('market_master_products')->cascadeOnDelete();

            $table->string('variant_code')->unique();
            $table->json('variant_attributes');

            // 💡 NEW: فیلدهای قیمت و انبار برای حالت تک‌فروشندگی
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('stock')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('market_product_variants');
    }
};
