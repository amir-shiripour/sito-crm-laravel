<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('bot_answers')->cascadeOnDelete();
            $table->foreignId('parent_item_id')->nullable()->constrained('bot_menu_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('response_type')->default('text'); // text, menu_items, product_list, url
            $table->text('response_text')->nullable();
            $table->json('response_entity_ids')->nullable();
            $table->string('response_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_menu_items');
    }
};
