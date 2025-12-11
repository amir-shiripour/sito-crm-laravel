<?php

namespace Modules\Sms\Services\Drivers;

use Modules\Sms\Entities\SmsMessage;

abstract class BaseDriver
{
    public function __construct(protected array $config = [])
    {
    }

    /**
     * ارسال پیامک متنی ساده
     */
    abstract public function sendText(string $to, string $message, array $options = []): SmsMessage;

    /**
     * ارسال پیامک پترنی / الگو
     *
     * @param  string  $patternKey  (مثلاً patternId یا templateId)
     */
    abstract public function sendPattern(string $to, string $patternKey, array $data = [], array $options = []): SmsMessage;

    /**
     * دریافت مانده اعتبار (در صورت پشتیبانی)
     */
    public function getBalance(): ?int
    {
        return null;
    }
}
