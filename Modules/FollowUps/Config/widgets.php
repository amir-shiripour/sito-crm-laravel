<?php

return [

    'followups_quick_create' => [
        'label'      => 'ثبت پیگیری سریع',
        'view'       => 'followups::widgets.quick-followup', // ویوی ویجت
        'permission' => 'followups.create',                  // فقط کسانی که این پرمیشن را دارند
        'group'      => 'پیگیری‌ها',
    ],

    'followups_pulse_and_overdue' => [
        'label'      => 'نبض پیگیری‌های امروز و معوقات',
        'view'       => 'followups::widgets.pulse-overview', // حتماً پیشوند ماژول وارد شود
        'permission' => 'followups.view',
        'group'      => 'گزارشات پیگیری',
    ],
];
