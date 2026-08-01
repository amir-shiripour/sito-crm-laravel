<?php

namespace Modules\Wallet\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Wallet\Installer;

class WalletDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // همگام‌سازی پرمیژن‌ها بدون فراخوانی install() جهت جلوگیری از لوپ بی‌نهایت
        $installer = new Installer();
        $installer->syncPermissions();
    }
}
