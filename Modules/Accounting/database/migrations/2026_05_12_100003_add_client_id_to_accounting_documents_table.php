<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Re-defining the class to match the filename and contain the correct logic.
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure the operation is safe to re-run
        if (!Schema::hasColumn('accounting_documents', 'client_id')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->after('category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('accounting_documents', 'client_id')) {
            Schema::table('accounting_documents', function (Blueprint $table) {
                // Ensure the foreign key exists before dropping
                $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('accounting_documents');
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('client_id', $foreignKey->getColumns())) {
                        $table->dropForeign(['client_id']);
                        break;
                    }
                }
                $table->dropColumn('client_id');
            });
        }
    }
};
