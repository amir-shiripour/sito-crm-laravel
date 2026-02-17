<?php

return [
    'name' => 'Workflows',

    'default_reminder_channel' => 'IN_APP',

    'dependencies' => [
        'tasks'     => 'Tasks',
        'followups' => 'FollowUps',
        'reminders' => 'Reminders',
        'sms'       => 'Sms',
    ],

    'tokens' => [
        'appointment' => [
            'client_name' => 'نام بیمار',
            'client_phone' => 'شماره بیمار',
            'service_name' => 'نام خدمت',
            'provider_name' => 'نام پزشک',
            'appointment_date_jalali' => 'تاریخ نوبت (شمسی)',
            'appointment_time_jalali' => 'ساعت نوبت',
            'appointment_datetime_jalali' => 'تاریخ و ساعت نوبت',
            'payment_link' => 'لینک پرداخت',
        ],
        'statement' => [
            'statement_id' => 'شناسه صورت وضعیت',
            'provider_name' => 'نام پزشک',
            'provider_phone' => 'شماره پزشک',
            'start_date' => 'تاریخ شروع',
            'end_date' => 'تاریخ پایان',
            'status' => 'وضعیت',
            'first_appointment_time' => 'ساعت اولین نوبت',
            'last_appointment_time' => 'ساعت آخرین نوبت',
            'notes' => 'یادداشت',
        ],
    ],
];
