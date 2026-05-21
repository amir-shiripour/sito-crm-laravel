<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_master_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('market_brands');
            $table->foreignId('category_id')->constrained('market_categories');

            $table->string('crm_code')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('gtin')->nullable();
            $table->string('title');
            $table->string('slug')->unique();

            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('enable_reviews')->default(true);

            $table->boolean('single_sell')->default(false);
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('shipping_class')->default('standard');

            // 💡 فیلد allow_vendor_variant_creation حذف شد
            $table->json('variant_axes_permissions')->nullable();

            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('market_master_products');
    }
};
