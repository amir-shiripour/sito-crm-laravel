<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('bot_questions')->cascadeOnDelete();
            $table->text('answer_text');
            $table->string('answer_type')->default('text'); // text, product_list, link, mixed
            $table->string('entity_type')->nullable(); // e.g. market_product
            $table->json('entity_ids')->nullable(); // IDs of products to fetch
            $table->boolean('show_add_to_cart')->default(false);
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_answers');
    }
};
