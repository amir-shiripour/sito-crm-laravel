<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('version_logs', function (Blueprint $class) {
            $class->id();
            $class->string('version_number')->unique(); // مثال: 1.2.0
            $class->string('title')->nullable(); // عنوان انتشار
            $class->date('release_date');
            $class->text('summary')->nullable(); // خلاصه تغییرات
            $class->json('changelog')->nullable(); // لیست دقیق تغییرات بصورت آرایه
            $class->boolean('is_current')->default(false); // آیا نسخه فعلی سیستم است؟
            $class->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_logs');
    }
};
