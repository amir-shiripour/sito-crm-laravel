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
        if (!Schema::hasColumn('accounting_documents', 'reference_number')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->string('reference_number')->nullable()->after('description');
            });
        }
        if (!Schema::hasColumn('accounting_documents', 'attachment')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('reference_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('accounting_documents', 'reference_number')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->dropColumn('reference_number');
            });
        }
        if (Schema::hasColumn('accounting_documents', 'attachment')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }
};
