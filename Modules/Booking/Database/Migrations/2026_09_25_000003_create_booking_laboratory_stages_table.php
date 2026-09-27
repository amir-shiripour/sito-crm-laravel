<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking_laboratory_stages')) {
            Schema::create('booking_laboratory_stages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('booking_laboratory_orders')->cascadeOnDelete();
                $table->string('stage_key', 50);
                $table->string('stage_title', 150);
                $table->unsignedInteger('offset_value')->default(1);
                $table->string('offset_unit', 20)->default('days'); // 'days', 'hours'
                $table->unsignedSmallInteger('sort_order')->default(1);
                
                $table->dateTime('due_at')->index();
                $table->boolean('is_completed')->default(false)->index();
                $table->dateTime('completed_at')->nullable();
                $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_receive_stage')->default(false); // Indicates the final "دریافت کار" stage
                
                $table->string('status', 30)->default('pending')->index(); // 'pending', 'due_today', 'overdue', 'completed'
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_laboratory_stages');
    }
};
