<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('market_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('code_offset')->default(100000);

            $table->json('target_attributes')->nullable(); // ویژگی‌های عمومی
            $table->json('variant_fields')->nullable()->after('target_attributes'); // 💡 محورهای تنوع (اضافه شد)

            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('market_categories');
    }
};
