<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->timestamps();

            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_messages');
    }
};
