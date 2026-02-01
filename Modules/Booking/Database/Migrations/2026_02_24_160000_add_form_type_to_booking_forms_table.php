<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            Schema::hasTable('booking_forms') &&
            !Schema::hasColumn('booking_forms', 'form_type')
        ) {
            Schema::table('booking_forms', function (Blueprint $table) {
                $table->string('form_type', 50)
                    ->default('CUSTOM')
                    ->after('name');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('booking_forms') &&
            Schema::hasColumn('booking_forms', 'form_type')
        ) {
            Schema::table('booking_forms', function (Blueprint $table) {
                $table->dropColumn('form_type');
            });
        }
    }
};
