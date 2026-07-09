<?php

return [
    // میز کار فروش
    [
        'title' => 'میز کار فروش',
        'route' => 'user.sales.cockpit',
        'permission' => 'sales.cockpit.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M4 13h16" /><path d="M10 4v16" /></svg>',
        'group' => 'sales',
        'position' => 20,
    ],

    // کاریز فروش (Kanban)
    [
        'title' => 'کاریز فروش',
        'route' => 'user.sales.pipeline',
        'permission' => 'sales.pipelines.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M12 4v16" /><path d="M12 12h8" /><path d="M4 12h8" /></svg>',
        'group' => 'sales',
        'position' => 21,
    ],

    // مدیریت سرنخ‌ها (Lead Manager)
    [
        'title' => 'مدیریت سرنخ‌ها',
        'route' => 'user.sales.leads.index',
        'permission' => 'sales.leads.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 3.2A9 9 0 1 0 20.8 14a1 1 0 0 0-1-1H13a1 1 0 0 1-1-1V4.2a1 1 0 0 0-1-1z" /><path d="M14 3.5A9 9 0 0 1 20.5 10H15a1 1 0 0 1-1-1V3.5z" /></svg>',
        'group' => 'sales',
        'position' => 22,
    ],

    // کمپین‌ها
    [
        'title' => 'کمپین‌های فروش',
        'route' => 'user.sales.campaigns.index',
        'permission' => 'sales.campaigns.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M12 14l0 7" /><path d="M10 14l-2.5 5.5" /><path d="M14 14l2.5 5.5" /><path d="M12 10l0 -7" /><path d="M10 10l-2.5 -5.5" /><path d="M14 10l2.5 -5.5" /></svg>',
        'group' => 'sales',
        'position' => 23,
    ],

    // گزارشات
    [
        'title' => 'گزارشات فروش',
        'route' => 'user.sales.reports.index',
        'permission' => 'sales.reports.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h5.697" /><path d="M18 14v4h4" /><path d="M18 11v-4a2 2 0 0 0 -2 -2h-2" /><path d="M8 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M8 11h4" /><path d="M8 15h3" /></svg>',
        'group' => 'sales',
        'position' => 24,
    ],

    // تنظیمات
    [
        'title' => 'تنظیمات فروش',
        'route' => 'user.sales.settings.index',
        'permission' => 'sales.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-adjustments-horizontal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 6l8 0" /><path d="M16 6l4 0" /><path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 12l2 0" /><path d="M10 12l10 0" /><path d="M17 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 18l11 0" /><path d="M19 18l1 0" /></svg>',
        'group' => 'sales-settings',
        'position' => 25,
    ],
];
