<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->default('null');
            $table->string('sender')->nullable();
            $table->json('config')->nullable();

            // در صورت مولتی تننت بودن، می‌توان این دو ستون را بعدا با توجه به ساختار پروژه اضافه کرد
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();

            $table->bigInteger('balance')->nullable();
            $table->timestamp('balance_checked_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_gateway_settings');
    }
};
