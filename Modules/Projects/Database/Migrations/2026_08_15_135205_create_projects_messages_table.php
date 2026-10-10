<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects_messages')) {
            Schema::create('projects_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id');
                $table->unsignedBigInteger('user_id');
                $table->text('body');
                $table->json('attachments')->nullable();
                $table->timestamps();

                if (Schema::hasTable('projects')) {
                    $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_messages');
    }
};
