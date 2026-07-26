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
        ]);

        if (class_exists(\Modules\Market\Installer::class)) {
            $installer = new \Modules\Market\Installer();
            // اجرای لاجیک ساخت نقش‌ها، دسترسی‌ها و وضعیت‌ها
            $installer->setupPermissionsAndStatuses();
        }
    }
}
