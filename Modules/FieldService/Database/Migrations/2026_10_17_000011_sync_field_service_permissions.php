<?php

use Illuminate\Database\Migrations\Migration;
use Modules\FieldService\Installer;

return new class extends Migration
{
    public function up(): void
    {
        Installer::syncModulePermissions();
    }

    public function down(): void
    {
        // Non-destructive
    }
};
