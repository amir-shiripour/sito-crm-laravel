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
        Schema::create('market_order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('admin_label');
            $table->string('client_label');
            $table->string('color_class')->default('bg-gray-100 text-gray-800');
            $table->string('system_type')->default('processing'); // pending, processing, shipped, delivered, canceled, returned
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('market_orders', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
            $table->foreignId('market_order_status_id')->nullable()->constrained('market_order_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_orders', function (Blueprint $table) {
            $table->dropForeign(['market_order_status_id']);
            $table->dropColumn('market_order_status_id');
            $table->string('delivery_status')->default('processing');
        });
        
        Schema::dropIfExists('market_order_statuses');
    }
};
