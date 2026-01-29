<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        // حالا که جدول property_categories وجود دارد، رابطه را به جدول properties اضافه می‌کنیم
        Schema::table('properties', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('property_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // ابتدا کلید خارجی را حذف می‌کنیم
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('property_categories');
    }
};
