<?php

namespace Modules\Sms\Services\Drivers;

use Illuminate\Support\Facades\Http;
use Modules\Sms\Entities\SmsMessage;

class ExampleHttpDriver extends BaseDriver
{
    public function sendText(string $to, string $message, array $options = []): SmsMessage
    {
        $payload = [
            'api_key' => $this->config['api_key'] ?? null,
            'from'    => $options['from'] ?? ($this->config['sender_number'] ?? null),
            'to'      => $to,
            'text'    => $message,
        ];

        $response = Http::baseUrl($this->config['base_url'] ?? '')
            ->post('/send-sms', $payload);

        $status = SmsMessage::STATUS_SENT;
        $error  = null;

        if (! $response->successful()) {
            $status = SmsMessage::STATUS_FAILED;
            $error  = $response->body();
        }

        return SmsMessage::create([
            'driver'       => 'example_http',
            'type'         => $options['type']     ?? 'system',
            'category'     => $options['category'] ?? null,
            'from'         => $payload['from']     ?? null,
            'to'           => $to,
            'message'      => $message,
            'pattern_key'  => $options['pattern_key'] ?? null,
            'pattern_data' => $options['pattern_data'] ?? null,
            'status'       => $status,
            'error_message'=> $error,
            'meta'         => [
                'provider_response' => $response->json(),
            ],
        ]);
    }

    public function sendPattern(string $to, string $patternKey, array $data = [], array $options = []): SmsMessage
    {
        $payload = [
            'api_key'    => $this->config['api_key'] ?? null,
            'from'       => $options['from'] ?? ($this->config['sender_number'] ?? null),
            'to'         => $to,
            'patternKey' => $patternKey,
            'data'       => $data,
        ];

        $response = Http::baseUrl($this->config['base_url'] ?? '')
            ->post('/send-pattern', $payload);

        $status = SmsMessage::STATUS_SENT;
        $error  = null;

        if (! $response->successful()) {
            $status = SmsMessage::STATUS_FAILED;
            $error  = $response->body();
        }

        return SmsMessage::create([
            'driver'        => 'example_http',
            'type'          => $options['type']     ?? 'system',
            'category'      => $options['category'] ?? null,
            'from'          => $payload['from'] ?? null,
            'to'            => $to,
            'message'       => null,
            'pattern_key'   => $patternKey,
            'pattern_data'  => $data,
            'status'        => $status,
            'error_message' => $error,
            'meta'          => [
                'provider_response' => $response->json(),
            ],
        ]);
    }

    public function getBalance(): ?int
    {
        $response = Http::baseUrl($this->config['base_url'] ?? '')
            ->get('/balance', [
                'api_key' => $this->config['api_key'] ?? null,
            ]);

        if (! $response->successful()) {
            return null;
        }

        return (int) ($response->json('balance') ?? 0);
    }
}
