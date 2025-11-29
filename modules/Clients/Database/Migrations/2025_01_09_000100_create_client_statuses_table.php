<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();              // مثل: new, active, canceled
            $table->string('label');                      // برچسب نمایشی: جدید، فعال، لغو شده
            $table->string('color')->nullable();          // مثل: emerald, red, slate ...
            $table->boolean('is_system')->default(false); // برای پیش‌فرض‌ها
            $table->boolean('is_active')->default(true);  // فعال / غیرفعال
            $table->boolean('show_in_quick')->default(true); // نمایش در ایجاد سریع
            $table->unsignedInteger('sort_order')->default(0);

            // وابستگی: فقط از چه وضعیت‌هایی قابل انتخاب است
            // مثال ذخیره: ["new","active"] (بر اساس key)
            $table->json('allowed_from')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_statuses');
    }
};

