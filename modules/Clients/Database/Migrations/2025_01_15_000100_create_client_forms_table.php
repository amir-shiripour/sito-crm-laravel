<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // مثلا "فرم پیش‌فرض" | "فرم کامل"
            $table->string('key')->unique();  // اسلاگ فرم
            $table->boolean('is_active')->default(false);
            $table->json('schema');           // تعریف فیلدها و تنظیمات
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('client_forms');
    }
};
