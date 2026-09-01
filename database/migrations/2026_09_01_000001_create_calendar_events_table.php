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
        if (!Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->string('color', 30)->default('#4f46e5');
                $table->dateTime('start_time')->index();
                $table->dateTime('end_time')->nullable()->index();
                $table->boolean('is_all_day')->default(false);
                $table->string('status', 30)->default('active');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
