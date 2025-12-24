<?php

return [

    'name' => 'Tasks',

    'statuses' => [
        'TODO'        => 'در انتظار (TODO)',
        'IN_PROGRESS' => 'در حال انجام (IN_PROGRESS)',
        'DONE'        => 'انجام شده (DONE)',
        'CANCELED'    => 'لغو شده (CANCELED)',
    ],

    'priorities' => [
        'LOW'      => 'کم (LOW)',
        'MEDIUM'   => 'متوسط (MEDIUM)',
        'HIGH'     => 'زیاد (HIGH)',
        'CRITICAL' => 'بحرانی (CRITICAL)',
    ],

    'types' => [
        'GENERAL'   => 'وظیفه عمومی (GENERAL)',
        'FOLLOW_UP' => 'پیگیری (FOLLOW_UP)',
    ],

    'default_items_per_page' => 15,
];
