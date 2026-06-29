<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_calls', function (Blueprint $table) {
            $table->id();
            
            // Soft link to clients table
            $table->unsignedBigInteger('client_id')->nullable();
            $table->index('client_id');
            
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')->on('sales_campaigns')->nullOnDelete();
            
            $table->unsignedBigInteger('user_id')->nullable(); // Operator
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            
            $table->date('call_date');
            $table->time('call_time')->nullable();
            $table->integer('duration_seconds')->nullable();
            
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('status', ['planned', 'answered', 'no_answer', 'busy', 'cancelled', 'failed'])->default('planned');
            
            $table->string('reason', 255)->nullable();
            $table->text('result')->nullable();
            
            $table->string('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            
            $table->string('contact_phone')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_calls');
    }
};
