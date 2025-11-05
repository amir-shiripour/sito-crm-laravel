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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام قابل نمایش e.g., "مدیریت کاربران"
            $table->string('slug')->unique(); // اسلاگ ماشینی e.g., "UserManagement"
            $table->text('description')->nullable(); // توضیحات
            $table->string('version')->default('1.0.0'); // نسخه ماژول
            $table->boolean('active')->default(false); // وضعیت فعال/غیرفعال
            $table->boolean('is_core')->default(false); // آیا ماژول هسته است و قابل غیرفعال شدن نیست؟
            $table->json('config')->nullable(); // تنظیمات خاص ماژول
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

