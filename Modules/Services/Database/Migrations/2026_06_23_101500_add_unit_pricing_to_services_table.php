<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('has_unit_pricing')->default(false)->after('setup_fee');
            $table->string('unit_name', 100)->nullable()->after('has_unit_pricing');
            $table->unsignedBigInteger('unit_price')->nullable()->after('unit_name');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            //
        });
    }
};
