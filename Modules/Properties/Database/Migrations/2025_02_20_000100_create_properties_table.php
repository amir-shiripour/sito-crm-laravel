<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            return;
        }

        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // مشخصات اصلی
            $table->string('title');
            $table->string('code')->unique()->nullable(); // کد ملک
            $table->text('description')->nullable();

            // نوع فایل و ملک
            $table->string('listing_type')->nullable(); // sale, rent, presale
            $table->string('property_type')->nullable(); // apartment, villa, land, office

            // قیمت و متراژ (برای مراحل بعد، اما اینجا تعریف می‌کنیم)
            $table->decimal('price', 15, 0)->nullable(); // قیمت کل / قیمت فروش
            $table->decimal('deposit_price', 15, 0)->nullable(); // قیمت رهن
            $table->decimal('rent_price', 15, 0)->nullable(); // قیمت اجاره
            $table->decimal('area', 10, 2)->nullable(); // متراژ

            // آدرس و موقعیت
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // مدیا
            $table->string('cover_image')->nullable();

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

    public function down(): void
    {
        Schema::dropIfExists('properties');

        // Clean up storage
        Storage::disk('public')->deleteDirectory('properties');
    }
};
