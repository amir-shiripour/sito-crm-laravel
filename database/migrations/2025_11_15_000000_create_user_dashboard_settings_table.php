<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('layout')->nullable(); // Stores the order and visibility of widgets
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dashboard_settings');
    }
};
