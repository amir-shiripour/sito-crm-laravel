<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('manual'); // system / scheduled / manual / otp
            $table->string('channel')->default('sms');
            $table->string('status')->default('pending');
            $table->string('driver')->nullable();

            $table->string('to');
            $table->string('from')->nullable();
            $table->text('message')->nullable();

            $table->string('template_key')->nullable();
            $table->json('params')->nullable();

            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->text('error')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['related_type', 'related_id']);
            $table->index(['type', 'status']);
            $table->index('to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
