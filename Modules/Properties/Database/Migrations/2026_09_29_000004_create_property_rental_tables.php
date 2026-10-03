<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول پیکربندی اقامتگاه روزانه
        if (!Schema::hasTable('property_rental_configs')) {
            Schema::create('property_rental_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                
                // ظرفیت پذیرش مهمان
                $table->unsignedSmallInteger('base_guests')->default(2);
                $table->unsignedSmallInteger('max_guests')->default(4);
                $table->json('weekend_days')->nullable(); // روزهای آخر هفته منتخب
                
                // ساختار قیمت‌گذاری روزانه
                $table->decimal('price_per_night', 15, 0)->nullable(); // نرخ عادی
                $table->decimal('price_weekend', 15, 0)->nullable();   // نرخ آخر هفته
                $table->decimal('price_holiday', 15, 0)->nullable();   // نرخ ایام پیک و تعطیلات
                $table->decimal('extra_guest_fee', 15, 0)->nullable(); // هزینه نفر اضافه به ازای هر شب
                $table->decimal('cleaning_fee', 15, 0)->nullable();    // هزینه نظافت
                
                // زمان‌بندی و حداقل اقامت
                $table->time('check_in_time')->default('14:00:00');
                $table->time('check_out_time')->default('12:00:00');
                $table->unsignedSmallInteger('min_stay_nights')->default(1);
                
                // قوانین و رزرو آنی
                $table->json('house_rules')->nullable();
                $table->boolean('instant_booking')->default(false);
                
                $table->timestamps();
            });
        }

        // جدول قیمت‌های فصلی / مناسبتی
        if (!Schema::hasTable('property_rental_prices')) {
            Schema::create('property_rental_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('price_per_night', 15, 0);
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        // جدول روزهای مسدود / رزرو شده
        if (!Schema::hasTable('property_rental_blocks')) {
            Schema::create('property_rental_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('property_rental_blocks');
        Schema::dropIfExists('property_rental_prices');
        Schema::dropIfExists('property_rental_configs');
    }
};
