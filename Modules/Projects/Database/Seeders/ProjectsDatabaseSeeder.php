<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ProjectsPermissionSeeder::class,
            ProjectStatusSeeder::class,
            ProjectsRoleSeeder::class,
        ]);
    }
}
