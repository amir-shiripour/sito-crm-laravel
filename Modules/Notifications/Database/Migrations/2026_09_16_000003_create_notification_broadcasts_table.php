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
        Schema::create('notification_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('category', 50)->default('broadcast');
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->string('severity', 20)->default('info');   // info, success, warning, danger
            $table->string('target_type', 30)->default('all');  // all, role, users
            $table->json('target_values')->nullable();          // array of role names or user IDs
            $table->json('channels')->nullable();               // ["database", "sms"]
            $table->string('action_url')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_broadcasts');
    }
};
