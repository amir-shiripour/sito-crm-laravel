<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('custom_user_fields', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');                         // نام نقش (Spatie)
            $table->string('field_name');                        // مثل: national_code, address
            $table->string('label');                             // برچسب نمایشی
            $table->enum('field_type', ['text','number','date','email']);
            $table->boolean('is_required')->default(false);
            $table->json('rules')->nullable();                   // چرا: امکان ولیدیشن‌های اضافه (unique, regex,…)
            $table->timestamps();
            $table->unique(['role_name','field_name']);
        });

        Schema::create('user_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('field_name');                        // مطابق با custom_user_fields.field_name
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['user_id','field_name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_custom_values');
        Schema::dropIfExists('custom_user_fields');
    }
};
