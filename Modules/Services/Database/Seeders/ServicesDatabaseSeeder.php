<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Services\App\Http\Models\Status;

class ServicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
        ]);
    }
}
