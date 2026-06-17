<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_order_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('market_orders')->cascadeOnDelete();
            $table->string('key');
            $table->text('value');
            $table->index(['order_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_order_meta');
    }
};
