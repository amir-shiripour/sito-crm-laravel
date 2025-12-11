<?php

use Modules\Sms\Services\Drivers\NullDriver;
use Modules\Sms\Services\Drivers\LimoSmsDriver;

return [
    'default_driver' => env('SMS_DRIVER', 'limosms'),

    'drivers' => [
        'null'    => NullDriver::class,
        'limosms' => LimoSmsDriver::class,
    ],

    'driver_config' => [
        'null' => [
            'sender' => env('SMS_SENDER', null),
        ],

        'limosms' => [
            'api_key'      => env('LIMOSMS_API_KEY'),
            'sender'       => env('LIMOSMS_SENDER'),
            'base_url'     => env('LIMOSMS_BASE_URL', 'https://api.limosms.com'),
            'send_url'     => '/api/sendsms',
            'pattern_url'  => '/api/sendpatternmessage',   // مطابق داک لیمو اگر اسم متد فرق کرد، اینجا عوض کن
            'balance_url'  => '/api/getcurrentcredit',        // آدرس دقیق را از داک بگیر و اصلاح کن
            'send_to_blocked' => true,
        ],
    ],

    'otp' => [
        'length' => 5,
        'ttl'    => 5,
    ],
];
