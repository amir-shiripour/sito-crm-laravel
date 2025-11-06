<?php

return [
    'user' => [
        'primary' => [
            [
                'label' => 'داشبورد',
                'route' => 'dashboard',
                'icon' => 'home',
                'description' => 'نمای کلی وضعیت کسب‌وکار و فعالیت‌های اخیر.',
                'active' => ['dashboard'],
            ],
            [
                'label' => 'پروفایل من',
                'route' => 'profile.show',
                'icon' => 'user-circle',
                'description' => 'ویرایش اطلاعات حساب، امنیت و ترجیحات شخصی.',
                'active' => ['profile.show'],
            ],
        ],
        'secondary' => [
            [
                'label' => 'اطلاعیه‌ها',
                'route' => 'dashboard',
                'icon' => 'bell',
                'description' => 'دسترسی سریع به اعلان‌ها و پیام‌های جدید.',
                'active' => ['notifications.*'],
            ],
            [
                'label' => 'مستندات',
                'url' => 'https://docs.sito-crm.local',
                'icon' => 'document',
                'description' => 'راهنمای استفاده و مستندات فنی سیستم.',
            ],
        ],
    ],
    'admin' => [
        'quick_actions' => [
            [
                'label' => 'کاربر جدید',
                'route' => 'admin.users.create',
                'icon' => 'user-plus',
                'can' => 'menu.see.users',
            ],
            [
                'label' => 'ماژول جدید',
                'route' => 'admin.modules.create',
                'icon' => 'cube',
            ],
            [
                'label' => 'گزارش لحظه‌ای',
                'route' => 'admin.dashboard',
                'icon' => 'chart',
            ],
        ],
        'sidebar' => [
            [
                'heading' => 'اصلی',
                'items' => [
                    [
                        'label' => 'داشبورد',
                        'route' => 'admin.dashboard',
                        'icon' => 'chart',
                        'active' => ['admin.dashboard'],
                    ],
                ],
            ],
            [
                'heading' => 'مدیریت',
                'items' => [
                    [
                        'label' => 'کاربران',
                        'route' => 'admin.users.index',
                        'icon' => 'users',
                        'active' => ['admin.users.*'],
                        'can' => 'menu.see.users',
                    ],
                    [
                        'label' => 'نقش‌ها و دسترسی‌ها',
                        'route' => 'admin.roles.index',
                        'icon' => 'shield',
                        'active' => ['admin.roles.*'],
                        'can' => 'menu.see.roles',
                    ],
                    [
                        'label' => 'ماژول‌ها',
                        'route' => 'admin.modules.index',
                        'icon' => 'cube',
                        'active' => ['admin.modules.*'],
                    ],
                ],
            ],
            [
                'heading' => 'پشتیبانی',
                'items' => [
                    [
                        'label' => 'تنظیمات سیستم',
                        'route' => 'admin.settings.index',
                        'icon' => 'cog',
                        'active' => ['admin.settings.*'],
                    ],
                    [
                        'label' => 'لاگ‌ها و مانیتورینگ',
                        'route' => 'admin.logs.index',
                        'icon' => 'activity',
                        'active' => ['admin.logs.*'],
                    ],
                ],
            ],
        ],
    ],
];
