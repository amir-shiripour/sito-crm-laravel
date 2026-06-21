<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_follow_ups', function (Blueprint $table) {
            $table->id();
            
            // Soft link to clients table
            $table->unsignedBigInteger('client_id')->nullable();
            $table->index('client_id');
            
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')->on('sales_campaigns')->nullOnDelete();
            
            $table->unsignedBigInteger('call_id')->nullable();
            $table->foreign('call_id')->references('id')->on('sales_calls')->nullOnDelete();
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->enum('status', ['open', 'in_progress', 'done', 'cancelled'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            $table->dateTime('due_date')->nullable();
            $table->dateTime('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->json('tags')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_follow_ups');
    }
};
