<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // جدول تعریف ویژگی‌ها
        Schema::create('property_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // عنوان ویژگی (مثلاً: سال ساخت، آسانسور)
            $table->string('type')->default('text'); // text, number, select, checkbox
            $table->string('section'); // details (اطلاعات تکمیلی), features (امکانات)
            $table->json('options')->nullable(); // برای select box
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول مقادیر ویژگی‌ها برای هر ملک
        Schema::create('property_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('property_attributes')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            // جلوگیری از تکرار مقدار برای یک ویژگی در یک ملک
            $table->unique(['property_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_attribute_values');
        Schema::dropIfExists('property_attributes');
    }
};
