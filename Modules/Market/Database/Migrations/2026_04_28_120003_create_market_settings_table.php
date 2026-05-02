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
        Schema::create('market_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // کلید تنظیمات (مثلاً general.is_market_active)
            $table->text('value')->nullable(); // مقدار تنظیمات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_settings');
    }
};
