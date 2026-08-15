<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('accounting_documents', 'cheque_id')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('cheque_id')->nullable()->after('attachment');
                $table->foreign('cheque_id')->references('id')->on('accounting_cheques')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('accounting_documents', 'cheque_id')) {
            try {
                DB::statement('ALTER TABLE `accounting_documents` DROP FOREIGN KEY `accounting_documents_cheque_id_foreign`');
            } catch (\Throwable $e) {}

            Schema::table('accounting_documents', function (Blueprint $table) {
                if (Schema::hasColumn('accounting_documents', 'cheque_id')) {
                    $table->dropColumn('cheque_id');
                }
            });
        }
    }
};
