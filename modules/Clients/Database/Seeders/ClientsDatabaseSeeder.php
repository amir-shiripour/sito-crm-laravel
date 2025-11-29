<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;

class ClientsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $this->call([
            ClientsPermissionSeeder::class,
            ClientsModuleSeeder::class,
        ]);
    }
}
