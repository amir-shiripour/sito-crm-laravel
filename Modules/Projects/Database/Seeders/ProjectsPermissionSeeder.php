<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Modules\Projects\Installer::syncModulePermissions();
    }
}
