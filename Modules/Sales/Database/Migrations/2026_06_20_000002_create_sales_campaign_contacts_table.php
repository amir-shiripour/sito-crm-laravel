<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('sales_campaigns')->cascadeOnDelete();
            
            // Soft link to clients table, because contact might not be a client yet
            $table->unsignedBigInteger('client_id')->nullable();
            
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            $table->enum('status', ['pending', 'contacted', 'responded', 'converted', 'lost'])->default('pending');
            $table->enum('source', ['manual', 'import', 'crm_filter', 'api'])->default('manual');
            
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            
            // If the clients table exists, you can add a soft index, but no strict constraint to avoid tight coupling if modules are removed
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_campaign_contacts');
    }
};
