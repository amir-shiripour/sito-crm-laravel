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
     * ارسال گروهی پیامک متنی با متن یکسان برای چند شماره
     *
     * @param  \Modules\Sms\Entities\SmsMessage[]  $messages
     * @param  string                                $commonBody
     * @param  string[]                              $mobileNumbers
     */
    public function sendBulkText(array $messages, string $commonBody, array $mobileNumbers): void;

    /**
     * ارسال متناظر گروهی (هر شماره، یک متن مشخص در یک درخواست)
     *
     * @param  \Modules\Sms\Entities\SmsMessage[]  $messages
     * @param  string[]                              $mobileNumbers
     * @param  string[]                              $bodies
     */
    public function sendPeerToPeer(array $messages, array $mobileNumbers, array $bodies): void;

    /**
     * در صورت پشتیبانی، مانده اعتبار سرویس را برمی‌گرداند.
     */
    public function fetchBalance(): ?array;
}
