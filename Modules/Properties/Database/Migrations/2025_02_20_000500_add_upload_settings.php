<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('property_settings')) {
            // Check if settings already exist to avoid duplicates
            $existingKeys = DB::table('property_settings')
                ->whereIn('key', ['max_images', 'max_file_size', 'allowed_mimes'])
                ->pluck('key')
                ->toArray();

            $dataToInsert = [];
            if (!in_array('max_images', $existingKeys)) {
                $dataToInsert[] = ['key' => 'max_images', 'value' => '10', 'created_at' => now(), 'updated_at' => now()];
            }
            if (!in_array('max_file_size', $existingKeys)) {
                $dataToInsert[] = ['key' => 'max_file_size', 'value' => '10240', 'created_at' => now(), 'updated_at' => now()]; // KB
            }
            if (!in_array('allowed_mimes', $existingKeys)) {
                $dataToInsert[] = ['key' => 'allowed_mimes', 'value' => 'jpeg,png,jpg,gif', 'created_at' => now(), 'updated_at' => now()];
            }

            if (!empty($dataToInsert)) {
                DB::table('property_settings')->insert($dataToInsert);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_settings')) {
            DB::table('property_settings')->whereIn('key', ['max_images', 'max_file_size', 'allowed_mimes'])->delete();
        }
    }
};
