<?php

return [
    [
        'title'      => 'داشبورد حسابداری',
        'route'      => 'admin.accounting.dashboard',
        'permission' => 'accounting.dashboard.view',
        'icon'       => '',
        'group'      => 'accounting',
        'position'   => 20.0,
    ],
    [
        'title'      => 'فاکتور ها',
        'route'      => 'admin.accounting.invoices.index',
        'icon'       => 'hero-outline-credit-card',
        'group'      => 'accounting',
        'position'   => 20.5,
    ],
    [
        'title'      => 'حساب های بانکی',
        'route'      => 'admin.accounting.banks.index',
        'permission' => 'accounting.banks.view',
        'icon'       => 'hero-outline-credit-card',
        'group'      => 'accounting',
        'position'   => 20.1,
    ],
    [
        'title'      => 'هزینه',
        'route'      => 'admin.accounting.expenses.index',
        'permission' => 'accounting.expenses.view',
        'icon'       => 'hero-outline-arrow-down-tray',
        'group'      => 'accounting',
        'position'   => 20.2,
    ],
    [
        'title'      => 'درآمد',
        'route'      => 'admin.accounting.transactions.index',
        'permission' => 'accounting.transactions.view',
        'icon'       => 'hero-outline-arrows-right-left',
        'group'      => 'accounting',
        'position'   => 20.3,
    ],
    [
        'title'      => 'مدیریت چک ها',
        'route'      => 'admin.accounting.cheques.index',
       // 'permission' => 'accounting.cheques.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h9a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-9a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3" /><path d="M9 5v-1a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v1" /><path d="M15 12h-6" /><path d="M15 9h-3" /></svg>',
        'group'      => 'accounting',
        'position'   => 20.4,
    ],
    [
        'title'      => 'اسناد مالی',
        'route'      => 'admin.accounting.documents.index',
        'permission' => 'accounting.documents.view', // Assuming a permission for documents
        'icon'       => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>', // Placeholder icon
        'group'      => 'accounting',
        'position'   => 20.6, // Position after invoices
    ],
    [
        'title'      => 'تنظیمات حسابداری',
        'route'      => 'admin.accounting.settings',
        'permission' => 'accounting.settings.view',
        'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /></svg>',
        'group'      => 'accounting',
        'position'   => 40,
    ],
];
