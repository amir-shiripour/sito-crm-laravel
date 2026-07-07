<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('content_posts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('content_tags')->onDelete('cascade');
            $table->primary(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_post_tag');
    }
};
