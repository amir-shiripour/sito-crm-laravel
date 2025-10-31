<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This is the pivot table (جدول واسط)
        // It answers: "Which modules are required for which theme?"
        Schema::create('theme_module_requirements', function (Blueprint $table) {
            $table->id();

            // Foreign key for the theme
            $table->foreignId('theme_id')
                ->constrained('themes')
                ->onDelete('cascade'); // If theme is deleted, delete this record

            // Foreign key for the module
            $table->foreignId('module_id')
                ->constrained('modules')
                ->onDelete('cascade'); // If module is deleted, delete this record

            // Ensure a theme cannot require the same module twice
            $table->unique(['theme_id', 'module_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_module_requirements');
    }
};

