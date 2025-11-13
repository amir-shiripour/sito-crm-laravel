<?php
return [
    [
        'title' => 'لیست '.config('clients.labels.plural'),
        'route' => 'user.clients.index',
        'permission' => 'clients.manage', // اگر تعریف نشده، installer باید آن را بسازد
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>', // دلخواه برای نمایش در منو
        'group' => 'clients',
        'position' => 10,
    ],
    [
        'title' => 'افزودن '.config('clients.labels.singular'),
        'route' => 'user.clients.create',
        'permission' => 'clients.create', // اگر تعریف نشده، installer باید آن را بسازد
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>', // دلخواه برای نمایش در منو
        'group' => 'clients',
        'position' => 11,
    ],
    [
        'title' => 'فرم ساز '.config('clients.labels.plural'),
        'route' => 'user.settings.clients.forms',
        'permission' => 'clients.manage', // اگر تعریف نشده، installer باید آن را بسازد
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-list-details"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 5h8" /><path d="M13 9h5" /><path d="M13 15h8" /><path d="M13 19h5" /><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /></svg>', // دلخواه برای نمایش در منو
        'group' => 'clients-settings',
        'position' => 12,
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
