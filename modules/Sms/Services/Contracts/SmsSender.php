<?php

namespace Modules\Sms\Services\Contracts;

use Modules\Sms\Entities\SmsMessage;

interface SmsSender
{
    /**
     * ارسال پیامک متنی ساده.
     *
     * @param  string  $to
     * @param  string  $message
     * @param  array   $options   مثل [ 'type' => 'manual', 'related_type' => 'CLIENT', ... ]
     */
    public function sendText(string $to, string $message, array $options = []): SmsMessage;

    /**
     * ارسال پیامک به صورت پترن/الگو.
     *
     * @param  string  $to
     * @param  string  $patternKey  کلید الگو در ماژول یا کد پترن سرویس دهنده
     * @param  array   $params
     * @param  array   $options
     */
    public function sendPattern(string $to, string $patternKey, array $params = [], array $options = []): SmsMessage;

    /**
     * ارسال/تولید کد OTP و بازگرداندن مدل پیامک.
     *
     * @param  string  $to
     * @param  string  $context  مثل 'login_user', 'login_client'
     * @param  array   $options
     */
    public function sendOtp(string $to, string $context = 'login', array $options = []): SmsMessage;
}
