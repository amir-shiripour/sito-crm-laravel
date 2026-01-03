<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // بررسی اینکه جدول‌های وابسته وجود دارند
        if (!Schema::hasTable('clients')) {
            throw new \Exception('جدول clients باید قبل از client_calls ایجاد شود. لطفاً ترتیب مایگریشن‌ها را بررسی کنید.');
        }

        if (!Schema::hasTable('users')) {
            throw new \Exception('جدول users باید قبل از client_calls ایجاد شود. لطفاً ترتیب مایگریشن‌ها را بررسی کنید.');
        }

        Schema::create('client_calls', function (Blueprint $table) {
            $table->id();

            // FK‌ها
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id')->nullable(); // کسی که تماس را ثبت کرده / انجام داده

            // تاریخ و زمان تماس (جداگانه، طبق نیازت)
            $table->date('call_date');
            $table->time('call_time')->nullable();

            // علت و نتیجه
            $table->string('reason', 255)->nullable();  // علت تماس
            $table->text('result')->nullable();         // نتیجه تماس / توضیحات

            // وضعیت تماس: planned, done, cancelled, failed, ...
            $table->string('status', 50)->default('done');

            $table->timestamps();

            // ایجاد foreign key constraint ها
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_calls');
    }
};
