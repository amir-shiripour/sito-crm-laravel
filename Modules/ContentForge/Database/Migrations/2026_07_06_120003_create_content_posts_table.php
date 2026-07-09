<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('content_entities')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('content_categories')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('post'); // page, post
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('og_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('attachments')->nullable();
            $table->string('theme_key')->nullable();
            $table->string('status')->default('draft');
            $table->string('visibility')->default('public');
            $table->string('password')->nullable();
            $table->string('short_code', 8)->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('reading_time')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->integer('comment_count')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('schema_markup')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_posts');
    }
};
