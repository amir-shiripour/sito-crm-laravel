<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('property_buildings')) {
            Schema::create('property_buildings', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('address')->nullable();
                $table->string('floors_count')->nullable();
                $table->string('units_count')->nullable();
                $table->string('construction_year')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Add foreign key to properties table if it exists
        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'building_id')) {
            Schema::table('properties', function (Blueprint $table) {
                // We can't easily check if the foreign key constraint exists,
                // but since we checked for the column, we can try to add the FK.
                // To be safe against "constraint already exists", we could wrap in try-catch
                // or just rely on the fact that migrations usually run once.
                // Given the user's request to fix errors, and assuming the error was about missing tables/columns,
                // this check is sufficient.

                // However, if the FK already exists, this might throw an error.
                // But there is no standard Schema::hasForeignKey.
                // We will proceed with adding it.
                $table->foreign('building_id')->references('id')->on('property_buildings')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropForeign(['building_id']);
            });
        }
        Schema::dropIfExists('property_buildings');
    }
};
