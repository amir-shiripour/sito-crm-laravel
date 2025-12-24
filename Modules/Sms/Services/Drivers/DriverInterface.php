<?php

namespace Modules\Sms\Services\Drivers;

use Modules\Sms\Entities\SmsMessage;

interface DriverInterface
{
    public function sendText(SmsMessage $message): void;

    /**
     * @param  SmsMessage  $message
     * @param  array       $params   پارامترهای پترن (الگو)
     */
    public function sendPattern(SmsMessage $message, array $params = []): void;

    /**
     * ارسال OTP. به صورت پیش‌فرض می‌تواند همان sendText باشد.
     */
    public function sendOtp(SmsMessage $message): void;

    /**
     * در صورت پشتیبانی، مانده اعتبار سرویس را برمی‌گرداند.
     */
    public function fetchBalance(): ?array;
}
