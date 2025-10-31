<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام قابل نمایش ماژول (مثال: مدیریت مشتریان)
            $table->string('slug')->unique(); // نام کلیدی منحصربه‌فرد (مثال: customers)
            $table->text('description')->nullable(); // توضیحات ماژول
            $table->string('version')->default('1.0.0'); // نسخه ماژول
            $table->boolean('active')->default(false); // وضعیت فعال/غیرفعال بودن
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
};

