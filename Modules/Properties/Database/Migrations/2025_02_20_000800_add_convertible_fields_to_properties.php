<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'is_convertible')) {
                    $table->boolean('is_convertible')->default(false)->after('rent_price');
                }
                if (!Schema::hasColumn('properties', 'convertible_with')) {
                    $table->string('convertible_with')->nullable()->after('is_convertible');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn(['is_convertible', 'convertible_with']);
            });
        }
    }
};
