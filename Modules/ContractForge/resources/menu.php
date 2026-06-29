<?php

return [
    [
        'title'      => 'قراردادهای بیماران',
        'route'      => 'user.contracts.index',
        'permission' => 'contractforge.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
            <path d="M9 9l1 1l2 -2" />
            <path d="M9 13h6" />
            <path d="M9 17h6" />
        </svg>',
        'group'     => 'contracts',
        'position'  => 18,
    ],
    [
        'title'      => 'قالب‌های قرارداد',
        'route'      => 'user.contracts.templates.index',
        'permission' => 'contractforge.manage',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M9 8h6" />
            <path d="M9 12h6" />
            <path d="M9 16h6" />
        </svg>',
        'group'     => 'contracts',
        'position'  => 18.1,
    ],
    [
        'title'      => 'قوانین قرارداد ساز',
        'route'      => 'user.contracts.rules.index',
        'permission' => 'contractforge.manage',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="6" cy="6" r="2" />
            <circle cx="18" cy="6" r="2" />
            <circle cx="12" cy="18" r="2" />
            <path d="M6 8v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2v-4" />
            <path d="M12 14v2" />
        </svg>',
        'group'     => 'contracts',
        'position'  => 18.2,
    ],
    [
        'title'      => 'تنظیمات قراردادها',
        'route'      => 'user.contracts.settings.edit',
        'permission' => 'contractforge.settings.manage',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="12" cy="12" r="3" />
            <path d="M18 15a6 6 0 1 1 -12 0" />
            <path d="M12 3v3" />
            <path d="M12 18v3" />
            <path d="M20 12h-3" />
            <path d="M7 12h-3" />
        </svg>',
        'group'     => 'contracts-settings',
        'position'  => 18.3,
    ],
];
