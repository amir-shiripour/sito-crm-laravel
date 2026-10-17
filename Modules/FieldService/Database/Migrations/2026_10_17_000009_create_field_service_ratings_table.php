<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_ratings')) {
            Schema::create('field_service_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('field_service_orders')->cascadeOnDelete();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignId('technician_id')->constrained('field_service_technicians')->cascadeOnDelete();
                $table->unsignedTinyInteger('score')->default(5); // 1 to 5
                $table->json('feedback_tags')->nullable(); // ['on_time', 'fair_price', 'expert_knowledge', 'clean_work']
                $table->text('comment')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();

                $table->unique('order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_ratings');
    }
};
