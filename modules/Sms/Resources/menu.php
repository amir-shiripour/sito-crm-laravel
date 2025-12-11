<?php

return [
    // لیست / گزارش پیامک‌ها
    [
        'title'      => 'گزارش پیامک‌ها',
        'route'      => 'user.sms.logs.index',       // ✅ موجود در routes/user.php
        'permission' => 'sms.messages.view',         // باید در permissions ماژول تعریف شده باشد
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                               viewBox="0 0 24 24" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-messages">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M6 20l-2 1l1 -2v-11a2 2 0 0 1 2 -2h9a2 2 0 0 1 2 2v3" />
                            <path d="M12 21h8a2 2 0 0 0 2 -2v-7a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v7a2 2 0 0 0 2 2z" />
                        </svg>',
        'group'     => 'sms',
        'position'  => 50,
    ],

    // ارسال دستی پیامک
    [
        'title'      => 'ارسال پیامک',
        'route'      => 'user.sms.send.create',      // ✅ صفحه ارسال
        'permission' => 'sms.messages.send',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                               viewBox="0 0 24 24" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-send">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 14l11 -11" />
                            <path d="M21 3l-6 18a0.55 .55 0 0 1 -1 0l-3 -7l-7 -3a0.55 .55 0 0 1 0 -1l18 -6" />
                        </svg>',
        'group'     => 'sms',
        'position'  => 51,
    ],

    // تنظیمات پیامک
    [
        'title'      => 'تنظیمات پیامک',
        'route'      => 'user.sms.settings.index',   // ✅ صفحه تنظیمات
        'permission' => 'sms.settings.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                               viewBox="0 0 24 24" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-adjustments-horizontal">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M14 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M4 6l8 0" /><path d="M16 6l4 0" />
                            <path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M4 12l2 0" /><path d="M10 12l10 0" />
                            <path d="M17 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M4 18l11 0" /><path d="M19 18l1 0" />
                        </svg>',
        'group'     => 'sms-settings',
        'position'  => 90,
    ],
];
