<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('custom_user_fields', function (Blueprint $table) {
            // تغییر از enum به string
            $table->string('field_type')->default('text')->change();
        });
    }

    public function down(): void {
        Schema::table('custom_user_fields', function (Blueprint $table) {
            // برگشت به حالت قبل در صورت نیاز
            $table->enum('field_type', ['text','number','date','email'])->change();
        });
    }
};
