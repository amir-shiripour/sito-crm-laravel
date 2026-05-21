<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('properties')) {
            Schema::create('properties', function (Blueprint $table) {
                $table->id();

                // مشخصات اصلی
                $table->string('title');
                $table->string('code')->unique()->nullable(); // کد ملک
                $table->text('description')->nullable();

                // نوع فایل و ملک
                $table->string('listing_type')->nullable(); // sale, rent, presale
                $table->string('property_type')->nullable(); // apartment, villa, land, office
                $table->string('usage_type')->nullable(); // residential, industrial, commercial, agricultural

                // قیمت و متراژ
                $table->decimal('price', 15, 0)->nullable(); // قیمت کل / قیمت فروش
                $table->decimal('min_price', 15, 0)->nullable(); // حداقل قیمت
                $table->decimal('deposit_price', 15, 0)->nullable(); // قیمت رهن
                $table->decimal('rent_price', 15, 0)->nullable(); // قیمت اجاره
                $table->decimal('advance_price', 15, 0)->nullable(); // پیش پرداخت
                $table->decimal('area', 10, 2)->nullable(); // متراژ

                // آدرس و موقعیت
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();

                // مدیا
                $table->string('cover_image')->nullable();

                // تاریخ‌ها
                $table->date('delivery_date')->nullable();

                // روابط
                $table->foreignId('status_id')
                    ->nullable()
                    ->constrained('property_statuses')
                    ->nullOnDelete();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('agent_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                // دسته‌بندی شخصی (رابطه بعداً اضافه می‌شود چون جدولش هنوز نیست)
                $table->unsignedBigInteger('category_id')->nullable();

                $table->json('meta')->nullable(); // برای ویژگی‌های اضافی

                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('property_settings');
        Schema::dropIfExists('properties');

        // Clean up storage
        Storage::disk('public')->deleteDirectory('properties');
    }
};
