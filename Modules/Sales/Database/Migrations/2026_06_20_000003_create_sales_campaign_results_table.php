<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_campaign_results', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('campaign_id')->constrained('sales_campaigns')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('sales_campaign_contacts')->cascadeOnDelete();
            
            $table->enum('result_type', [
                'call_response', 'sms_reply', 'email_open', 
                'purchase', 'signup', 'no_response', 
                'interested', 'not_interested'
            ])->default('no_response');
            
            $table->boolean('converted')->default(false);
            
            $table->decimal('revenue', 15, 2)->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            
            $table->text('notes')->nullable();
            
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            
            $table->timestamps();
            
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_campaign_results');
    }
};
