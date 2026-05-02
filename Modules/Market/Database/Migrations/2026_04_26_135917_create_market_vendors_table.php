<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. جدول هسته اصلی فروشندگان (با اضافه شدن فیلدهای احراز هویت و مالی)
        Schema::create('market_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // اطلاعات فروشگاه
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('description')->nullable();

            // اطلاعات مالک (KYC)
            $table->enum('legal_type', ['real', 'legal'])->default('real'); // شخص حقیقی یا حقوقی
            $table->string('national_code')->nullable(); // کد ملی یا شناسه ملی
            $table->string('economic_code')->nullable(); // کد اقتصادی (برای حقوقی)

            // اطلاعات مالی تسویه حساب
            $table->string('shaba_number', 50)->nullable();
            $table->string('account_owner_name')->nullable();
            $table->string('bank_name')->nullable();

            // وضعیت‌ها و قرارداد
            $table->timestamp('contract_accepted_at')->nullable(); // تاریخ تایید قرارداد قوانین
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending'); // وضعیت احراز هویت
            $table->text('kyc_rejection_reason')->nullable(); // دلیل رد کلی احراز هویت
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending'); // وضعیت فعالیت فروشگاه
            $table->decimal('commission_rate', 5, 2)->nullable(); // درصد پورسانت اختصاصی

            $table->timestamps();
        });

        // ۲. جدول آدرس‌های فروشنده (انبار، دفتر مرکزی و ...)
        Schema::create('market_vendor_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();

            // نوع آدرس: store (دفتر/فروشگاه), warehouse (انبار تحویل کالا), return (آدرس مرجوعی)
            $table->enum('type', ['store', 'warehouse', 'return'])->default('store');

            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->text('address');
            $table->string('postal_code', 20)->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // ۳. جدول مدارک فروشنده (جهت احراز هویت)
        Schema::create('market_vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();

            // نوع مدرک: national_card (کارت ملی), business_license (جواز کسب), vat_certificate (ارزش افزوده)
            $table->string('type');
            $table->string('file_path'); // مسیر ذخیره فایل

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); // دلیل رد مدرک توسط ادمین

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_vendor_documents');
        Schema::dropIfExists('market_vendor_addresses');
        Schema::dropIfExists('market_vendors');
    }
};
