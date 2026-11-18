<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_service_technicians')) {
            DB::statement("ALTER TABLE field_service_technicians ALTER COLUMN rating_avg SET DEFAULT 0.00");
            DB::table('field_service_technicians')
                ->where('rating_count', 0)
                ->update(['rating_avg' => 0.00]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_service_technicians')) {
            DB::statement("ALTER TABLE field_service_technicians ALTER COLUMN rating_avg SET DEFAULT 5.00");
        }
    }
};
