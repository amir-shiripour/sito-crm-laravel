<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services_statuses', function (Blueprint $table) {
            // Drop the old column if it exists
            if (Schema::hasColumn('services_statuses', 'triggers_conversion')) {
                $table->dropColumn('triggers_conversion');
            }
            // Add the new flexible attributes column
            $table->json('attributes')->nullable()->after('is_readonly');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services_statuses', function (Blueprint $table) {
            $table->dropColumn('attributes');
            // Re-add the old column if needed for rollback
            $table->boolean('triggers_conversion')->default(false)->after('is_readonly');
        });
    }
};
