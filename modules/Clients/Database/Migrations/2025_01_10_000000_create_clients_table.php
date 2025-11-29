<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // ستون یوزرنیم (اولِ جدول، یکتا و NOT NULL):
            $table->string('username', 191)->unique();

            // به‌جای name از full_name (snake_case)
            $table->string('full_name', 255);

            // ایمیل: nullable و بدون unique (در صورت نیاز بعداً ایندکس ساده بذار)
            $table->string('email')->nullable();

            $table->string('phone')->nullable();
            $table->string('national_code', 20)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('status_id')               // وضعیت فعلی کلاینت
            ->nullable()
                ->constrained('client_statuses')
                ->nullOnDelete();

            // داده‌های سفارشی فرم‌ساز
            $table->json('meta')->nullable();

            // سازنده/مالک رکورد
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ایندکس‌های کمکی
            $table->index('created_by');
            $table->index('full_name');
            $table->index('username');
            $table->index('phone');
            $table->index('national_code');
            $table->index('status_id');
            // اگر بعداً روی email جستجو داری:
            // $table->index('email');
        });

        // Pivot برای اتصال چند کاربر به یک کلاینت (Many-to-Many)
        Schema::create('client_user', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');

            $table->primary(['client_id', 'user_id']);

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user');
        Schema::dropIfExists('clients');
    }
};
