<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // اضافه کردن فیلدهای جدید به جدول properties
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'usage_type')) {
                    $table->string('usage_type')->nullable(); // residential, industrial, commercial, agricultural
                }
                if (!Schema::hasColumn('properties', 'delivery_date')) {
                    $table->date('delivery_date')->nullable();
                }
                if (!Schema::hasColumn('properties', 'advance_price')) {
                    $table->decimal('advance_price', 15, 0)->nullable(); // پیش پرداخت
                }
                if (!Schema::hasColumn('properties', 'min_price')) {
                    $table->decimal('min_price', 15, 0)->nullable();
                }
            });
        }

        // ایجاد جدول تنظیمات املاک
        if (!Schema::hasTable('property_settings')) {
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
    }

    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn(['usage_type', 'delivery_date', 'advance_price', 'min_price']);
            });
        }

        Schema::dropIfExists('property_settings');
    }
};
