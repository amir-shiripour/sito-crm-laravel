<?php

use Modules\Warehouse\Entities\WarehouseSetting;

return [
    [
        'title'      => 'داشبورد انبار',
        'route'      => 'admin.warehouse.dashboard',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
        'group'      => 'warehouse',
        'position'   => 25.0,
        'permission' => 'warehouse.view',
    ],
    [
        'title'      => 'انبار موجودی',
        'route'      => 'admin.warehouse.inventory.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
        'group'      => 'warehouse',
        'position'   => 25.1,
        'permission' => 'warehouse.inventory.view',
        'show'       => function () {
            return class_exists(WarehouseSetting::class) && WarehouseSetting::isInventoryEnabled();
        },
    ],
    [
        'title'      => 'مدیریت اموال',
        'route'      => 'admin.warehouse.assets.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
        'group'      => 'warehouse',
        'position'   => 25.2,
        'permission' => 'warehouse.assets.view',
        'show'       => function () {
            return class_exists(WarehouseSetting::class) && WarehouseSetting::isAssetsEnabled();
        },
    ],
    [
        'title'      => 'تنظیمات انبار',
        'route'      => 'admin.warehouse.settings.edit',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37c1 .608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        'group'      => 'warehouse-settings',
        'position'   => 25.9,
        'permission' => 'warehouse.settings.manage',
    ],
];
