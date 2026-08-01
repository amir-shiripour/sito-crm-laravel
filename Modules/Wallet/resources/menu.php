<?php

return [
    [
        'title'      => 'مدیریت کیف پول‌ها',
        'route'      => 'user.wallet.index',
        'permission' => 'wallet.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a1 1 0 0 0 1 -1v-3" />
            <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4z" />
        </svg>',
        'group'     => 'finance',
        'position'  => 30,
    ],
    [
        'title'      => 'تراکنش‌های مالی',
        'route'      => 'user.wallet.transactions.index',
        'permission' => 'wallet.transactions.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M9 14l-4 -4l4 -4" />
            <path d="M5 10h11a4 4 0 1 1 0 8h-1" />
        </svg>',
        'group'     => 'finance',
        'position'  => 31,
    ],
];
