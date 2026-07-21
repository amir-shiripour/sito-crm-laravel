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
        Schema::table('services_projects', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['customer_id']);

            // Add the new foreign key constraint pointing to the 'clients' table
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
        Schema::table('services_projects', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['customer_id']);

            // Re-add the old foreign key constraint pointing to the 'users' table
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
