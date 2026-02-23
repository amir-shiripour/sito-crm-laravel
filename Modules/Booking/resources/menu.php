<?php

return [

    // داشبورد نوبت‌دهی
    [
        'title'      => __('booking::menu.dashboard'),
        'route'      => 'user.booking.dashboard',
        'permission' => 'booking.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="5" width="16" height="14" rx="2" />
            <path d="M16 3v4" />
            <path d="M8 3v4" />
            <path d="M4 11h16" />
            <rect x="8" y="15" width="2" height="2" rx="0.5" />
        </svg>',
        'group'     => 'booking',
        'position'  => 11,
    ],

    // لیست سرویس‌ها
    [
        'title'      => __('booking::menu.services'),
        'route'      => 'user.booking.services.index',
        'permission' => 'booking.services.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M9 8h6" />
            <path d="M9 12h6" />
            <path d="M9 16h6" />
        </svg>',
        'group'     => 'booking',
        'position'  => 12,
    ],

    [
        'title'      => __('booking::menu.categories'),
        'route'      => 'user.booking.categories.index',
        'permission' => 'booking.categories.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="4" width="7" height="7" rx="1" />
            <rect x="13" y="4" width="7" height="7" rx="1" />
            <rect x="4" y="13" width="7" height="7" rx="1" />
            <rect x="13" y="13" width="7" height="7" rx="1" />
        </svg>',
        'group'     => 'booking',
        'position'  => 12.5,
    ],

    [
        'title'      => __('booking::menu.forms'),
        'route'      => 'user.booking.forms.index',
        'permission' => 'booking.forms.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M8 8h8" />
            <path d="M8 12h8" />
            <path d="M8 16h4" />
        </svg>',
        'group'     => 'booking',
        'position'  => 12.6,
    ],

    [
        'title' => __('booking::menu.providers_schedule'),
        'route' => 'user.booking.providers.index',
        'permission' => 'booking.availability.manage',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M9 8h6" />
            <path d="M9 12h6" />
            <path d="M9 16h6" />
        </svg>',
        'group' => 'booking',
        'position' => 14,
    ],

    // لیست نوبت‌ها
    [
        'title'      => __('booking::menu.appointments'),
        'route'      => 'user.booking.appointments.index',
        'permission' => 'booking.appointments.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="5" width="16" height="14" rx="2" />
            <path d="M16 3v4" />
            <path d="M8 3v4" />
            <path d="M4 11h16" />
            <path d="M10 16l2 2l4 -4" />
        </svg>',
        'group'     => 'booking',
        'position'  => 13,
    ],

    // صورت وضعیت
    [
        'title'      => __('booking::menu.statement'),
        'route'      => 'user.booking.statement.index',
        'permission' => 'booking.reports.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
            <path d="M9 17h6" />
            <path d="M9 13h6" />
        </svg>',
        'group'     => 'booking',
        'position'  => 15,
    ],

    // تنظیمات نوبت‌دهی
    [
        'title'      => __('booking::menu.settings'),
        'route'      => 'user.booking.settings.edit',
        'permission' => 'booking.settings.manage',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        </svg>',
        'group'     => 'booking',
        'position'  => 19,
    ],

];
