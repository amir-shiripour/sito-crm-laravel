<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_convertible')->default(false)->after('rent_price');
            $table->string('convertible_with')->nullable()->after('is_convertible');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['is_convertible', 'convertible_with']);
        });
    }
};
