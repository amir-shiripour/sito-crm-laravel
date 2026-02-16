<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gapgpt_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // کاربری که درخواست داده
            $table->string('model'); // مدل استفاده شده
            $table->longText('prompt')->nullable(); // متن درخواست (JSON یا متن ساده)
            $table->longText('response')->nullable(); // متن پاسخ
            $table->integer('prompt_tokens')->default(0); // توکن‌های ورودی
            $table->integer('completion_tokens')->default(0); // توکن‌های خروجی
            $table->integer('total_tokens')->default(0); // مجموع توکن‌ها
            $table->integer('duration_ms')->default(0); // مدت زمان پاسخدهی به میلی‌ثانیه
            $table->string('status')->default('success'); // success یا error
            $table->text('error_message')->nullable(); // متن خطا در صورت وجود
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gapgpt_logs');
    }
};
