<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('jalali_year');
            $table->unsignedTinyInteger('jalali_month');
            $table->unsignedTinyInteger('jalali_day');
            $table->string('jalali_date', 10); // e.g. 1405-01-01
            $table->date('gregorian_date')->nullable();
            $table->string('title');
            $table->boolean('is_holiday')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['jalali_date', 'title']);
            $table->index(['jalali_year', 'jalali_month']);
            $table->index('gregorian_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_events');
    }
};
