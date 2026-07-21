<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services_custom_fields', function (Blueprint $table) {
            $table->boolean('show_in_invoice')->default(true)->after('has_pricing');
        });
    }

    public function down(): void
    {
        Schema::table('services_custom_fields', function (Blueprint $table) {
            $table->dropColumn('show_in_invoice');
        });
    }
};
