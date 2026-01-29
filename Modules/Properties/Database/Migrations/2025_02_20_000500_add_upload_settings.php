<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('property_settings')->insert([
            ['key' => 'max_images', 'value' => '10', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_file_size', 'value' => '10240', 'created_at' => now(), 'updated_at' => now()], // KB
            ['key' => 'allowed_mimes', 'value' => 'jpeg,png,jpg,gif', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('property_settings')->whereIn('key', ['max_images', 'max_file_size', 'allowed_mimes'])->delete();
    }
};
