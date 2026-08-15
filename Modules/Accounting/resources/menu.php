<?php

return [
    [
        'title'      => 'داشبورد حسابداری',
        'route'      => 'admin.accounting.dashboard',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.0,
    ],
    [
        'title'      => 'گردش تراکنش‌ها',
        'route'      => 'admin.accounting.transactions.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.1,
    ],
    [
        'title'      => 'مدیریت هزینه‌ها',
        'route'      => 'admin.accounting.expenses.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.2,
    ],
    [
        'title'      => 'مدیریت چک‌ها',
        'route'      => 'admin.accounting.cheques.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.3,
    ],
    [
        'title'      => 'حساب‌های خزانه‌داری',
        'route'      => 'admin.accounting.fund-accounts.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.4,
    ],
    [
        'title'      => 'اسناد مالی',
        'route'      => 'admin.accounting.documents.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.5,
    ],
    [
        'title'      => 'سرفصل‌های حسابداری',
        'route'      => 'admin.accounting.categories.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.6,
    ],
    [
        'title'      => 'گزارشات مالی',
        'route'      => 'admin.accounting.reports.index',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6main2 2 0 012 2H7a2 2 0 01-2-2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m4 6h.01" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.7,
    ],
    [
        'title'      => 'تنظیمات حسابداری',
        'route'      => 'admin.accounting.settings.edit',
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37c1 .608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'group'      => 'accounting',
        'position'   => 20.8,
    ],
];
