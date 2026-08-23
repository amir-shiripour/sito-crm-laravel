<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects_roles')) {
            Schema::create('projects_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->string('color', 30)->default('indigo');
                $table->string('icon', 50)->default('shield');
                $table->boolean('is_system')->default(false);
                $table->json('permissions')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects_roles');
    }
};
