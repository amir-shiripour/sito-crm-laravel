<?php

return [

    // درایور پیش‌فرض
    'default_driver' => env('SMS_DRIVER', 'null'),

    // تنظیمات عمومی OTP
    'otp' => [
        'length'               => env('SMS_OTP_LENGTH', 5),
        'expires_in_minutes'   => env('SMS_OTP_EXPIRES', 5),
        'resend_after_seconds' => env('SMS_OTP_RESEND_AFTER', 90),
    ],

    // انواع پیامک (برای گزارش‌ها و فیلتر)
    'types' => [
        'system'    => 'سیستمی',
        'manual'    => 'دستی',
        'scheduled' => 'برنامه‌ریزی‌شده',
        'otp'       => 'کد ورود (OTP)',
        'workflow'  => 'جریان‌کار',
        'reminder'  => 'یادآوری',
        'other'     => 'سایر',
    ],

    // وضعیت ارسال
    'statuses' => [
        'pending' => 'در صف',
        'queued'  => 'منتظر ارسال',
        'sent'    => 'ارسال شده',
        'failed'  => 'ناموفق',
    ],

    // نگاشت کلید درایورها به کلاس‌ها
    'drivers' => [
        'null' => [
            'label'  => 'درایور تست (Null)',
            'class'  => Modules\Sms\Services\Drivers\NullDriver::class,
            'config' => [],
        ],

        // یک درایور نمونه مبتنی بر HTTP - توسعه‌دهنده می‌تواند مطابق سرویس خود آن را تکمیل کند
        'example_http' => [
            'label' => 'درایور نمونه HTTP',
            'class' => Modules\Sms\Services\Drivers\ExampleHttpDriver::class,
            'config' => [
                'base_url'      => env('SMS_HTTP_BASE_URL'),
                'api_key'       => env('SMS_HTTP_API_KEY'),
                'sender_number' => env('SMS_HTTP_SENDER'),
            ],
        ],
    ],

];
