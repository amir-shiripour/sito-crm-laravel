<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_forms')) {
            return;
        }

        Schema::create('booking_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 20)->default('ACTIVE');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->json('schema_json');

            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_forms');
    }
};
