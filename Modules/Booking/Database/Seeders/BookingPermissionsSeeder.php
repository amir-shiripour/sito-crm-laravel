<?php

namespace Modules\Booking\Database\Seeders;

use Illuminate\Database\Seeder;

class BookingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Modules\Booking\Installer::syncModulePermissions();
    }
}
