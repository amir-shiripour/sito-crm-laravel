<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('property_rental_bookings')) {
            Schema::create('property_rental_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_code', 64)->unique();

                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->foreignId('host_id')->constrained('property_hosts')->cascadeOnDelete();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

                // تاریخ‌ها و مدت اقامت
                $table->date('check_in_date');
                $table->date('check_out_date');
                $table->string('check_in_jalali', 30)->nullable();
                $table->string('check_out_jalali', 30)->nullable();
                $table->unsignedSmallInteger('nights_count')->default(1);

                // مهمانان
                $table->unsignedSmallInteger('guests_count')->default(1);
                $table->unsignedSmallInteger('extra_guests_count')->default(0);

                // اقلام مالی و مبالغ (تومان یا ریال طبق تنظیم سیستم)
                $table->decimal('nights_total', 15, 0)->default(0);
                $table->decimal('extra_guests_fee', 15, 0)->default(0);
                $table->decimal('cleaning_fee', 15, 0)->default(0);
                $table->decimal('discount_amount', 15, 0)->default(0);
                $table->decimal('grand_total', 15, 0)->default(0);
                $table->decimal('prepayment_amount', 15, 0)->default(0);
                $table->decimal('paid_amount', 15, 0)->default(0);

                // کارمزد و تسویه میزبان
                $table->decimal('commission_percent', 5, 2)->default(0);
                $table->decimal('commission_amount', 15, 0)->default(0);
                $table->decimal('host_payout_amount', 15, 0)->default(0);

                // وضعیت‌های رزرو و پرداخت
                $table->string('status', 40)->default('pending_host')->index();
                $table->string('payment_status', 40)->default('unpaid')->index();
                $table->string('payment_method', 50)->nullable();

                // زمان‌بندی‌ها و مهلت‌ها
                $table->timestamp('payment_deadline_at')->nullable();
                $table->timestamp('host_action_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();

                // یادداشت‌ها و دلایل
                $table->text('cancellation_reason')->nullable();
                $table->text('notes')->nullable();

                // مشخصات مهمان جهت تطبیق یا رزرو برای غیر
                $table->string('guest_full_name', 190)->nullable();
                $table->string('guest_phone', 30)->nullable();
                $table->string('guest_national_code', 20)->nullable();

                $table->json('meta')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        // افزودن ستون booking_id به جدول property_rental_blocks جهت پیوند مسدودی تقویم با رزرو
        if (Schema::hasTable('property_rental_blocks') && !Schema::hasColumn('property_rental_blocks', 'booking_id')) {
            Schema::table('property_rental_blocks', function (Blueprint $table) {
                $table->foreignId('booking_id')
                    ->nullable()
                    ->after('property_id')
                    ->constrained('property_rental_bookings')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_rental_blocks') && Schema::hasColumn('property_rental_blocks', 'booking_id')) {
            Schema::table('property_rental_blocks', function (Blueprint $table) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            });
        }

        Schema::dropIfExists('property_rental_bookings');
    }
};
