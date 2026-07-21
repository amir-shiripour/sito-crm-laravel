<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_invoice_items', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('total');
        });

        // Migrate any existing custom_field_values JSON into meta.custom_fields
        DB::statement("
            UPDATE service_invoice_items
            SET meta = JSON_OBJECT('custom_fields', custom_field_values)
            WHERE custom_field_values IS NOT NULL
              AND custom_field_values != ''
              AND JSON_VALID(custom_field_values)
        ");

        Schema::table('service_invoice_items', function (Blueprint $table) {
            $table->dropColumn('custom_field_values');
        });
    }

    public function down(): void
    {
        Schema::table('service_invoice_items', function (Blueprint $table) {
            $table->json('custom_field_values')->nullable()->after('total');
        });

        // Restore data from meta.custom_fields back to custom_field_values
        DB::statement("
            UPDATE service_invoice_items
            SET custom_field_values = JSON_EXTRACT(meta, '$.custom_fields')
            WHERE meta IS NOT NULL
              AND meta != ''
              AND JSON_VALID(meta)
        ");

        Schema::table('service_invoice_items', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
