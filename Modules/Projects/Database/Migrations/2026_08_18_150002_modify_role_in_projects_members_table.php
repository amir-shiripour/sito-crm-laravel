<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects_members', function (Blueprint $table) {
            $table->string('role', 50)->default('viewer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects_members', function (Blueprint $table) {
            $table->enum('role', ['viewer', 'editor', 'manager'])->default('viewer')->change();
        });
    }
};
