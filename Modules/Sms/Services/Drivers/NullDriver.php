<?php

namespace Modules\Sms\Services\Drivers;

use Illuminate\Support\Facades\Log;
use Modules\Sms\Entities\SmsMessage;

class NullDriver implements DriverInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function sendText(SmsMessage $message): void
    {
        Log::info('[SMS-NULL] sendText', [
            'to'      => $message->to,
            'message' => $message->message,
            'id'      => $message->id,
        ]);

        $message->markAsSent('null', ['debug' => true]);
    }

    public function sendPattern(SmsMessage $message, array $params = []): void
    {
        Log::info('[SMS-NULL] sendPattern', [
            'to'      => $message->to,
            'pattern' => $message->template_key,
            'params'  => $params,
            'id'      => $message->id,
        ]);

        $message->markAsSent('null', ['debug' => true]);
    }

    public function sendOtp(SmsMessage $message): void
    {
        Log::info('[SMS-NULL] sendOtp', [
            'to'   => $message->to,
            'code' => $message->message,
            'id'   => $message->id,
        ]);

        $message->markAsSent('null', ['debug' => true]);
    }

    public function fetchBalance(): ?array
    {
        return [
            'driver'  => 'null',
            'balance' => null,
            'meta'    => ['debug' => true],
        ];
    }
}
