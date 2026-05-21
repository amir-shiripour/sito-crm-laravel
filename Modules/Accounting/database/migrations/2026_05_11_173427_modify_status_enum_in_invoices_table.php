<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define the new full list of ENUM values
        $new_enum_values = "'draft', 'unpaid', 'partially_paid', 'paid', 'overdue', 'pending_review', 'cancelled', 'refunded', 'bad_debt'";

        // Use a raw DB statement to modify the column
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM({$new_enum_values}) NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Define the original list of ENUM values for rollback
        $original_enum_values = "'draft', 'unpaid', 'paid', 'cancelled'";

        // Use a raw DB statement to revert the column
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM({$original_enum_values}) NOT NULL DEFAULT 'unpaid'");
    }
};
