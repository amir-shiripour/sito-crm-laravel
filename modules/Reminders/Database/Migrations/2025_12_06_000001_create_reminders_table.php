<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('related_type', 100);
            $table->unsignedBigInteger('related_id');
            $table->timestamp('remind_at');
            $table->string('channel', 50)->default('IN_APP');
            $table->string('message')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['related_type', 'related_id']);
            $table->index(['remind_at', 'is_sent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
