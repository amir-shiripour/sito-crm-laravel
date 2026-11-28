<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class ServicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            ServicesPermissionsSeeder::class,
        ]);

        if (
            Module::has('Workflows')
            && Module::isEnabled('Workflows')
            && Schema::hasTable('workflows')
            && Schema::hasTable('workflow_triggers')
            && Schema::hasTable('workflow_stages')
            && Schema::hasTable('workflow_actions')
        ) {
            Artisan::call('services:seed-workflows');
        }
    }
}
