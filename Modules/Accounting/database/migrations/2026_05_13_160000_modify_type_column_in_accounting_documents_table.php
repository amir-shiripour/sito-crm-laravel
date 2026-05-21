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
        // Changing ENUM is not always fully supported by Doctrine DBAL out of the box in all configurations.
        // A direct ALTER TABLE statement is the safest way to modify an ENUM to include more options,
        // or to change it to a string/varchar. We will change it to a string for flexibility.

        DB::statement("ALTER TABLE `accounting_documents` MODIFY `type` VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to ENUM might cause data loss if there are 'transfer_out' records,
        // so we'll leave it as VARCHAR in the down method or just add the enum back cautiously.
        DB::statement("ALTER TABLE `accounting_documents` MODIFY `type` ENUM('income', 'expense') NOT NULL");
    }
};
