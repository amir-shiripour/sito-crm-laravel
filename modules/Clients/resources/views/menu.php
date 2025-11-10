<?php
// modules/Clients/Resources/menu.php
return [
    [
        'title' => 'مشتریان',
        'route' => 'user.clients.index',
        'permission' => 'menu.see.clients', // اگر تعریف نشده، installer باید آن را بسازد
        'icon' => 'users', // دلخواه برای نمایش در منو
        'group' => 'clients',
        'position' => 10,
    ],
    [
        'title' => 'پروفایل مشتری',
        'route' => 'user.clients.profile',
        'permission' => 'clients.view',
        'icon' => 'user',
        'group' => 'clients',
        'position' => 20,
    ],
];
