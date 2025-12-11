<?php

namespace Modules\Sms\Services\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sms\Entities\SmsMessage;

class LimoSmsDriver implements DriverInterface
{
    protected ?string $apiKey;
    protected ?string $sender;
    protected string $baseUrl;

    public function __construct(array $config = [])
    {
        $this->apiKey  = $config['api_key']  ?? null;
        $this->sender  = $config['sender']   ?? ($config['sender_number'] ?? null);
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.limosms.com/api', '/');

        // اگر کاربر فقط دامین را داده بود، خودمان /api را اضافه می‌کنیم
        if (! str_contains($this->baseUrl, '/api')) {
            $this->baseUrl .= '/api';
        }

        Log::debug('[LimoSms] driver initialized', [
            'base_url' => $this->baseUrl,
            'sender'   => $this->sender,
            'has_key'  => ! empty($this->apiKey),
        ]);
    }


    /**
     * ارسال پیامک متنی معمولی
     */
    public function sendText(SmsMessage $message): void
    {
        if (empty($this->apiKey)) {
            $error = 'LimoSms api_key is not configured.';
            Log::error('[LimoSms] sendText: ' . $error);
            $message->markAsFailed('limosms', $error);
            return;
        }

        $url = $this->baseUrl . '/sendsms';

        $payload = [
            'Message'      => $message->message ?? '',
            'SenderNumber' => $message->from ?: $this->sender,
            'MobileNumber' => [$message->to], // لیمو آرایه شماره‌ها می‌خواهد
        ];

        try {
            Log::info('[LimoSms] sendText request', [
                'url'     => $url,
                'payload' => $payload,
                'sms_id'  => $message->id,
            ]);

            $response = Http::withHeaders([
                'ApiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($url, $payload);

            $body = $response->json();

            Log::info('[LimoSms] sendText response', [
                'status'  => $response->status(),
                'body'    => $body,
                'sms_id'  => $message->id,
            ]);

            if (! $response->successful()) {
                $message->markAsFailed(
                    'limosms',
                    'HTTP ' . $response->status(),
                    ['response' => $body]
                );
                return;
            }

            // اگر در آینده ساختار پاسخ لیمو مشخص شد، اینجا میشه دقیق‌تر چک کرد
            $message->markAsSent('limosms', $body ?? []);

        } catch (\Throwable $e) {
            Log::error('[LimoSms] sendText exception: ' . $e->getMessage(), [
                'sms_id' => $message->id,
                'trace'  => $e->getTraceAsString(),
            ]);

            $message->markAsFailed('limosms', $e->getMessage());
        }
    }

    /**
     * ارسال پیامک پترنی از طریق LimoSms (endpoint: /api/sendpatternmessage)
     *
     * $message->template_key به عنوان OtpId استفاده می‌شود
     * $params (یا $message->params) به عنوان آرایه ReplaceToken
     */
    public function sendPattern(SmsMessage $message, array $params = []): void
    {
        if (empty($this->apiKey)) {
            $error = 'LimoSms api_key is not configured.';
            Log::error('[LimoSms] sendPattern: ' . $error);
            $message->markAsFailed('limosms', $error);
            return;
        }

        if (empty($message->template_key)) {
            $error = 'LimoSms pattern send requires template_key (OtpId).';
            Log::error('[LimoSms] sendPattern: ' . $error, ['sms_id' => $message->id]);
            $message->markAsFailed('limosms', $error);
            return;
        }

        $url = $this->baseUrl . '/sendpatternmessage';

        // اگر پارامترها خالی بود، از params داخل خود مدل استفاده کن
        $tokens = $params;
        if (empty($tokens) && is_array($message->params)) {
            $tokens = $message->params;
        }
        $tokens = array_values($tokens ?? []);

        $payload = [
            'OtpId'        => (int) $message->template_key,
            'ReplaceToken' => $tokens,
            'MobileNumber' => $message->to,
        ];

        try {
            Log::info('[LimoSms] sendPattern request', [
                'url'     => $url,
                'payload' => $payload,
                'sms_id'  => $message->id,
            ]);

            $response = Http::withHeaders([
                'ApiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($url, $payload);

            $body = $response->json();

            Log::info('[LimoSms] sendPattern response', [
                'status' => $response->status(),
                'body'   => $body,
                'sms_id' => $message->id,
            ]);

            if (! $response->successful()) {
                $message->markAsFailed(
                    'limosms',
                    'HTTP ' . $response->status(),
                    ['response' => $body]
                );
                return;
            }

            // اگر جواب استاندارد Success / Message داشته باشد، می‌تونی در آینده چک دقیق‌تری بزاری
            $message->markAsSent('limosms', $body ?? []);

        } catch (\Throwable $e) {
            Log::error('[LimoSms] sendPattern exception: ' . $e->getMessage(), [
                'sms_id' => $message->id,
                'trace'  => $e->getTraceAsString(),
            ]);

            $message->markAsFailed('limosms', $e->getMessage());
        }
    }


    /**
     * OTP هم فعلاً مثل پیام ساده ارسال می‌شود
     */
    public function sendOtp(SmsMessage $message): void
    {
        $this->sendText($message);
    }

    /**
     * دریافت اعتبار (اگر در آینده endpoint رسمی پیدا کردیم، اینجا تکمیل می‌کنیم)
     */
    public function fetchBalance(): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        // فعلاً فقط یک ساختار خام برمی‌گردونیم تا صفحه تنظیمات نخوابد
        return [
            'driver'  => 'limosms',
            'balance' => null,
            'meta'    => [],
        ];
    }
}
