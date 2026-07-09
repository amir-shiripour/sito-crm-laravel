<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_url');
            $table->string('to_url');
            $table->string('type', 3)->default('301');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_redirects');
    }
};
