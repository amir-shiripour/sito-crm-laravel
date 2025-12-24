<?php

return [
    'groups' => [
        'sms' => [
            'label' => 'پیامک‌ها',
            'permissions' => [
                'sms.settings.view'   => 'مشاهده تنظیمات پیامک',
                'sms.settings.manage' => 'مدیریت تنظیمات پیامک',
                'sms.messages.view'   => 'مشاهده لیست پیامک‌ها',
                'sms.messages.send'   => 'ارسال پیامک دستی',
                'sms.templates.manage'=> 'مدیریت الگوهای پیامک',
            ],
        ],
    ],
];
