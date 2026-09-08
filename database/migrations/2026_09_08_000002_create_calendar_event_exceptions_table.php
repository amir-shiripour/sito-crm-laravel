<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('calendar_event_exceptions')) {
            Schema::create('calendar_event_exceptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('calendar_event_id')->index();
                $table->date('exception_date')->index();
                $table->string('action', 20)->default('skip');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->string('color', 30)->nullable();
                $table->dateTime('start_time')->nullable();
                $table->dateTime('end_time')->nullable();
                $table->boolean('is_all_day')->nullable();
                $table->timestamps();

                $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onDelete('cascade');
                $table->unique(['calendar_event_id', 'exception_date'], 'cal_ev_exc_event_date_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_exceptions');
    }
};
