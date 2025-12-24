<?php

return [

    // ویجت ثبت تماس سریع
    'client_calls_quick_create' => [
        'label'      => 'ثبت تماس سریع',
        'view'       => 'clientcalls::widgets.quick-call', // view ویجت
        'permission' => 'client-calls.create',             // فقط اگر این پرمیشن را داشته باشد
        'group'      => 'تماس‌ها',
    ],

    // اگر خواستی بعداً ویجت‌های دیگر اضافه کن مثلا:
    /*
    'client_calls_recent' => [
        'label'      => 'آخرین تماس‌ها',
        'view'       => 'clientcalls::widgets.recent-calls',
        'permission' => 'client-calls.view',
        'group'      => 'تماس‌ها',
    ],
    */
];
