<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('market_product_questions')->cascadeOnDelete();
            $table->foreignId('master_product_id')->constrained('market_master_products')->cascadeOnDelete();
            
            // Authors
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('market_vendors')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('text');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('dislikes_count')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_product_questions');
    }
};
