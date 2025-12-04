<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade'); // برای نگهداری نقش
            $table->string('widget_key'); // شناسه ویجت (مثل نام ویجت یا کلید آن)
            $table->boolean('is_active')->default(true); // تنظیم فعال بودن ویجت
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('widget_settings');
    }
}

