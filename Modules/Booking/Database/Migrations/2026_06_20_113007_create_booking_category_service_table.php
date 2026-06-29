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
        Schema::create('booking_category_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('booking_services')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('booking_categories')->onDelete('cascade');
            
            // Ensure a service cannot have the exact same category attached twice
            $table->unique(['service_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_category_service');
    }
};
