<?php
return [
    [
        'title' => 'لیست '.config('clients.labels.plural'),
        'route' => 'user.clients.index',
        // دسترسی پایه برای دیدن لیست؛ جزئیات اسکوپ با view.all / view.assigned / view.own کنترل می‌شود
        'permission' => 'clients.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>',
        'group' => 'clients',
        'position' => 10,
    ],
    [
        'title' => 'افزودن '.config('clients.labels.singular'),
        'route' => 'user.clients.create',
        'permission' => 'clients.create',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>',
        'group' => 'clients',
        'position' => 11,
    ],
    [
        'title' => 'تنظیمات عمومی '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.username',
        'permission' => 'clients.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-adjustments-horizontal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 6l8 0" /><path d="M16 6l4 0" /><path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 12l2 0" /><path d="M10 12l10 0" /><path d="M17 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 18l11 0" /><path d="M19 18l1 0" /></svg>',
        'group' => 'clients-settings',
        'position' => 12,
    ],
    [
        'title' => 'فرم ساز '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.forms',
        'permission' => 'clients.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-list-details"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 5h8" /><path d="M13 9h5" /><path d="M13 15h8" /><path d="M13 19h5" /><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /></svg>',
        'group' => 'clients-settings',
        'position' => 13,
    ],
    [
        'title' => 'مدیریت وضعیت '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.statuses',
        'permission' => 'clients.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-progress-alert"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 20.777a8.942 8.942 0 0 1 -2.48 -.969" /><path d="M14 3.223a9.003 9.003 0 0 1 0 17.554" /><path d="M4.579 17.093a8.961 8.961 0 0 1 -1.227 -2.592" /><path d="M3.124 10.5c.16 -.95 .468 -1.85 .9 -2.675l.169 -.305" /><path d="M6.907 4.579a8.954 8.954 0 0 1 3.093 -1.356" /><path d="M12 8v4" /><path d="M12 16v.01" /></svg>',
        'group' => 'clients-settings',
        'position' => 14,
    ],
    [
        'title' => 'تنظیمات ورود پرتال '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.auth',
        'permission' => 'clients.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-shield-lock">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 3l7 4v5c0 4.243 -2.686 7.5 -7 9c-4.314 -1.5 -7 -4.757 -7 -9v-5z" />
                    <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                    <path d="M12 15v2" />
              </svg>',
        'group' => 'clients-settings',
        'position' => 15,
    ],
    [
        'title' => 'ایمپورت '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.import',
        'permission' => 'clients.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-file-import"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2l7 -7m-2 7h7" /></svg>',
        'group' => 'clients-settings',
        'position' => 16,
    ],

    /*[
        'title' => 'پروفایل مشتری',
        'route' => 'user.clients.profile',
        'permission' => 'clients.view',
        'icon' => 'user',
        'group' => 'clients',
        'position' => 13,
    ],*/
];
