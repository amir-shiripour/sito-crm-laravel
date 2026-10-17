<?php

return [
    // مانیتورینگ زنده ناوگان (GIS Fleet Map)
    [
        'title'      => 'مانیتورینگ زنده ناوگان',
        'route'      => 'user.fieldservice.fleet',
        'permission' => 'fieldservice.fleet.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" />
            <path d="M9 4v13" />
            <path d="M15 7v5.5" />
            <path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" />
            <path d="M19 18v.01" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.1,
    ],

    // داشبورد فرماندهی نصاب‌باشی
    [
        'title'      => 'داشبورد فرماندهی نصاب‌باشی',
        'route'      => 'user.fieldservice.dashboard',
        'permission' => 'fieldservice.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 4h6v8h-6z" />
            <path d="M4 16h6v4h-6z" />
            <path d="M14 12h6v8h-6z" />
            <path d="M14 4h6v4h-6z" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.2,
    ],

    // سفارش‌ها و ماموریت‌ها
    [
        'title'      => 'سفارش‌ها و ماموریت‌ها',
        'route'      => 'user.fieldservice.orders.index',
        'permission' => 'fieldservice.orders.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
            <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
            <path d="M9 14l2 2l4 -4" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.3,
    ],

    // تکنسین‌ها و متخصصین
    [
        'title'      => 'متخصصین و ناوگان فنی',
        'route'      => 'user.fieldservice.technicians.index',
        'permission' => 'fieldservice.technicians.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.4,
    ],

    // مدیریت ساختمان‌ها و تاسیسات (B2B)
    [
        'title'      => 'شناسنامه ساختمان‌ها (B2B)',
        'route'      => 'user.fieldservice.buildings.index',
        'permission' => 'fieldservice.b2b.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 21l18 0" />
            <path d="M9 8l1 0" />
            <path d="M9 12l1 0" />
            <path d="M9 16l1 0" />
            <path d="M14 8l1 0" />
            <path d="M14 12l1 0" />
            <path d="M14 16l1 0" />
            <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.5,
    ],

    // فروشگاه‌های همکار و گارانتی
    [
        'title'      => 'فروشگاه‌های همکار و گارانتی',
        'route'      => 'user.fieldservice.vendors.index',
        'permission' => 'fieldservice.b2b.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 21l18 0" />
            <path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
            <path d="M5 21l0 -10.15" />
            <path d="M19 21l0 -10.15" />
            <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.6,
    ],

    // تسویه‌حساب‌های پایا
    [
        'title'      => 'تسویه‌های بانکی پایا',
        'route'      => 'user.fieldservice.settlements.index',
        'permission' => 'fieldservice.settlements.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
            <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.7,
    ],

    // تنظیمات سامانه نصاب‌باشی
    [
        'title'      => 'تنظیمات نصاب‌باشی',
        'route'      => 'user.fieldservice.settings.index',
        'permission' => 'fieldservice.settings.manage',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        </svg>',
        'group'     => 'fieldservice',
        'position'  => 30.8,
    ],
];
