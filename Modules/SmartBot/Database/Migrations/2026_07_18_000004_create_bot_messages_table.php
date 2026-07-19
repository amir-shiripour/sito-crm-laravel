<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('bot_sessions')->cascadeOnDelete();
            $table->string('role'); // user, bot
            $table->text('content');
            $table->foreignId('question_id')->nullable()->constrained('bot_questions')->nullOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('bot_answers')->nullOnDelete();
            $table->boolean('resolved')->default(true);
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_messages');
    }
};
