<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;

class ServicesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Modules\Services\Installer::syncModulePermissions();
    }
}
