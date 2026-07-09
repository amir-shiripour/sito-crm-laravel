<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('content_entities')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_tags');
    }
};
