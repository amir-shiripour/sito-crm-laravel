<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('type')->default('generic'); // generic / otp / system
            $table->text('body')->nullable(); // متن پیامک (در صورت عدم استفاده از پترن خارجی)
            $table->string('provider_pattern')->nullable(); // کد پترن در سرویس پیامک
            $table->boolean('is_active')->default(true);
            $table->json('params')->nullable(); // پارامترهای مورد انتظار
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
