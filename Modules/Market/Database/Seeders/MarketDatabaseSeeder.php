<?php

namespace Modules\Market\Database\Seeders;

use Illuminate\Database\Seeder;

class MarketDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CheckoutFormSeeder::class,
            // Other seeders for the Market module can be added here
        ]);
    }
}
