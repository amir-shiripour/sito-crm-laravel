<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // 💡 کلمه 'image' به لیست enum اضافه شد
            $table->enum('type', ['text', 'color', 'select', 'image'])->default('select');

            $table->string('unit', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('market_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('market_attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('meta_value', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('market_attribute_values');
        Schema::dropIfExists('market_attributes');
    }
};
