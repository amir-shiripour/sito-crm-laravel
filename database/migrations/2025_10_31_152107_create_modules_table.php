<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // اگر لازم است محافظت کنید که دوبار ساخته نشود:
        if (Schema::hasTable('modules')) {
            return;
        }

        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // پایه
            $table->string('name');                       // نام قابل‌نمایش
            $table->string('slug')->unique();             // اسلاگ یکتا (e.g. UserManagement)
            $table->text('description')->nullable();      // توضیحات

            // نسخه و هسته
            $table->string('version')->default('1.0.0');  // نسخه ماژول (ادغام هر دو فایل)
            $table->boolean('is_core')->default(false);   // آیا ماژول هسته است؟

            // وضعیت نصب/فعال‌بودن
            $table->boolean('active')->default(false);    // فعال/غیرفعال
            $table->boolean('installed')->default(false); // نصب شده؟
            $table->timestamp('installed_at')->nullable();// زمان نصب

            // تنظیمات
            $table->json('config')->nullable();           // تنظیمات خاص ماژول (JSON)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
