<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_short_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('content_posts')->onDelete('cascade');
            $table->string('code', 8)->unique();
            $table->string('custom_code')->nullable();
            $table->integer('click_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_short_links');
    }
};
