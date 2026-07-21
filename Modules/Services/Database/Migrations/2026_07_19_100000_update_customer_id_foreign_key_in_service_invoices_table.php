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
        Schema::table('service_invoices', function (Blueprint $table) {
            // Drop the old foreign key that points to the 'users' table.
            // Laravel's default name is table_column_foreign
            $table->dropForeign('service_invoices_customer_id_foreign');

            // Add the new foreign key pointing to the 'clients' table.
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('clients')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_invoices', function (Blueprint $table) {
            // Drop the new foreign key that points to the 'clients' table.
            $table->dropForeign('service_invoices_customer_id_foreign');

            // Re-add the old foreign key pointing to the 'users' table.
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
