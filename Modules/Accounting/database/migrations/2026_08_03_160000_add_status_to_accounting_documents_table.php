<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('accounting_documents', 'status')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('accounting_documents', 'status')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
