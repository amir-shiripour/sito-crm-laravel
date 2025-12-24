<?php

return [

    'followups_quick_create' => [
        'label'      => 'ثبت پیگیری سریع',
        'view'       => 'followups::widgets.quick-followup', // ویوی ویجت
        'permission' => 'followups.create',                  // فقط کسانی که این پرمیشن را دارند
        'group'      => 'پیگیری‌ها',
    ],

];
