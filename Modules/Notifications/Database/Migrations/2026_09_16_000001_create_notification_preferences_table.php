<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 50); // e.g. tasks, reminders, clients, sales, system, broadcast
            $table->boolean('channel_database')->default(true);
            $table->boolean('channel_sms')->default(false);
            $table->boolean('channel_mail')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'category'], 'user_notif_pref_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
