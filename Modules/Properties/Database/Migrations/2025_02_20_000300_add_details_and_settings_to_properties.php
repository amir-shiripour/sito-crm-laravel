<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // اضافه کردن فیلدهای جدید به جدول properties
        Schema::table('properties', function (Blueprint $table) {
            // برای زمین و باغ
            $table->string('usage_type')->nullable(); // residential, industrial, commercial, agricultural

            // برای پیش فروش
            $table->date('delivery_date')->nullable();
            $table->decimal('advance_price', 15, 0)->nullable(); // پیش پرداخت

            // قیمت کف (برای فروش و پیش فروش)
            $table->decimal('min_price', 15, 0)->nullable();
        });

        // ایجاد جدول تنظیمات املاک
        Schema::create('property_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // تنظیمات پیش‌فرض
        DB::table('property_settings')->insert([
            ['key' => 'currency', 'value' => 'toman', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['usage_type', 'delivery_date', 'advance_price', 'min_price']);
        });

        Schema::dropIfExists('property_settings');
    }
};
