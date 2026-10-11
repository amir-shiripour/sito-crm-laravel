<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Booking\Installer;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Automatically syncs the new laboratory permissions and grants them
     * to default admin roles (super-admin, admin) during Git pull or module update.
     */
    public function up(): void
    {
        Installer::syncModulePermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
