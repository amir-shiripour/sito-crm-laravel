<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('projects_settings')) {
            Schema::create('projects_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        // Migrate any existing settings from the central settings table if present
        if (Schema::hasTable('settings')) {
            $existing = DB::table('settings')
                ->where('key', 'like', 'projects_%')
                ->get();

            foreach ($existing as $row) {
                DB::table('projects_settings')->updateOrInsert(
                    ['key' => $row->key],
                    [
                        'value' => $row->value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects_settings');
    }
};
