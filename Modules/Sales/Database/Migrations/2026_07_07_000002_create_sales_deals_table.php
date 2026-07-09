<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Soft link to clients (base module)
            $table->unsignedBigInteger('client_id')->nullable();
            $table->index('client_id');
            
            // Foreign key to sales_pipelines (inside Sales module)
            $table->foreignId('pipeline_stage_id')
                ->constrained('sales_pipelines')
                ->onDelete('restrict');
            
            // Account Manager
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            // Financial fields
            $table->decimal('expected_revenue', 15, 2)->default(0);
            $table->decimal('actual_revenue', 15, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->integer('probability')->nullable(); // percentage (0-100)
            
            // Analytics
            $table->timestamp('stage_entered_at')->nullable();
            
            // Marketing Attribution & Reason
            $table->string('lead_source')->nullable();
            $table->foreignId('loss_reason_id')
                ->nullable()
                ->constrained('sales_loss_reasons')
                ->nullOnDelete();
            
            // Custom fields JSON
            $table->json('custom_fields')->nullable();
            
            // Status
            $table->enum('status', ['open', 'won', 'lost'])->default('open');
            $table->index('status');
            
            // Creator
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_deals');
    }
};
