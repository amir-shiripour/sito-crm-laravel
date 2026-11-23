<?php

namespace Modules\Warehouse\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseSetting;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Default Settings
        WarehouseSetting::setValue('inventory_enabled', true);
        WarehouseSetting::setValue('assets_enabled', true);
        WarehouseSetting::setValue('market_integration_enabled', false);

        // 2. Default Main Inventory Warehouse
        Warehouse::firstOrCreate(
            [
                'type' => 'inventory',
                'is_main' => true,
            ],
            [
                'name' => 'انبار مرکزی موجودی',
                'code' => 'WH-INV-MAIN-01',
                'color' => '#4f46e5',
                'is_active' => true,
                'location' => 'دفتر مرکزی',
                'description' => 'انبار مرکزی برای تجمیع موجودی اقلام و کالاهای کل سازمان',
            ]
        );

        // 3. Default Main Asset Warehouse
        Warehouse::firstOrCreate(
            [
                'type' => 'asset',
                'is_main' => true,
            ],
            [
                'name' => 'انبار مرکزی اموال',
                'code' => 'WH-AST-MAIN-01',
                'color' => '#0d9488',
                'is_active' => true,
                'location' => 'دفتر مرکزی',
                'description' => 'انبار مرکزی برای ثبت و استقرار دارایی‌های ثابت و اثاثیه سازمانی',
            ]
        );
    }
}
