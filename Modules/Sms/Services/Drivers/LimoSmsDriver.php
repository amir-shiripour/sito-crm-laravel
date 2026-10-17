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
        $this->apiKey  = $config['api_key']  ?? env('LIMOSMS_API_KEY');

        // Fallback to env if config is empty (useful for CLI/Cron)
        $sender = !empty($config['sender']) ? $config['sender'] : (!empty($config['sender_number']) ? $config['sender_number'] : (env('LIMOSMS_SENDER') ?: env('SMS_SENDER')));
        $this->sender = $sender ?: null;

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

        $senderNumber = $message->from ?: ($this->sender ?: (env('LIMOSMS_SENDER') ?: env('SMS_SENDER')));

        if (empty($senderNumber)) {
            $error = 'LimoSms SenderNumber is missing. Please configure LIMOSMS_SENDER or SMS_SENDER in .env or SMS settings.';
            Log::error('[LimoSms] sendText: ' . $error);
            $message->markAsFailed('limosms', $error);
            return;
        }

        $url = $this->baseUrl . '/sendsms';

        $payload = [
            'Message'      => $message->message ?? '',
            'SenderNumber' => $senderNumber,
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
     * ارسال گروهی پیامک متنی با متن مشترک برای چند شماره (یک ریکوئست به /api/sendsms)
     *
     * @param  SmsMessage[]  $messages
     * @param  string        $commonBody
     * @param  string[]      $mobileNumbers
     */
    public function sendBulkText(array $messages, string $commonBody, array $mobileNumbers): void
    {
        if (empty($messages) || empty($mobileNumbers)) {
            return;
        }

        if (empty($this->apiKey)) {
            $error = 'LimoSms api_key is not configured.';
            Log::error('[LimoSms] sendBulkText: ' . $error);
            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $error);
            }
            return;
        }

        $senderNumber = $this->sender ?: (env('LIMOSMS_SENDER') ?: env('SMS_SENDER'));
        if (empty($senderNumber)) {
            $error = 'LimoSms SenderNumber is missing. Please configure LIMOSMS_SENDER or SMS_SENDER.';
            Log::error('[LimoSms] sendBulkText: ' . $error);
            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $error);
            }
            return;
        }

        $url = $this->baseUrl . '/sendsms';

        $payload = [
            'Message'            => $commonBody,
            'SenderNumber'       => $senderNumber,
            'MobileNumber'       => array_values($mobileNumbers),
            'SendToBlocksNumber' => true,
        ];

        try {
            Log::info('[LimoSms] sendBulkText request', [
                'url'    => $url,
                'count'  => count($mobileNumbers),
                'sender' => $senderNumber,
            ]);

            $response = Http::withHeaders([
                'ApiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(45)
                ->post($url, $payload);

            $body = $response->json();

            Log::info('[LimoSms] sendBulkText response', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if (! $response->successful()) {
                $err = 'HTTP ' . $response->status();
                foreach ($messages as $msg) {
                    $msg->markAsFailed('limosms', $err, ['response' => $body]);
                }
                return;
            }

            $isSuccess = $body['Success'] ?? $body['success'] ?? true;
            if ($isSuccess === false) {
                $err = $body['Message'] ?? $body['message'] ?? 'LimoSms rejected bulk request';
                foreach ($messages as $msg) {
                    $msg->markAsFailed('limosms', $err, ['response' => $body]);
                }
                return;
            }

            foreach ($messages as $msg) {
                $msg->markAsSent('limosms', $body ?? []);
            }

        } catch (\Throwable $e) {
            Log::error('[LimoSms] sendBulkText exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $e->getMessage());
            }
        }
    }

    /**
     * ارسال متناظر گروهی - هر شماره، یک متن مشخص (یک ریکوئست به /api/sendpeertopeersms)
     *
     * @param  SmsMessage[]  $messages
     * @param  string[]      $mobileNumbers
     * @param  string[]      $bodies
     */
    public function sendPeerToPeer(array $messages, array $mobileNumbers, array $bodies): void
    {
        if (empty($messages) || empty($mobileNumbers) || empty($bodies)) {
            return;
        }

        if (empty($this->apiKey)) {
            $error = 'LimoSms api_key is not configured.';
            Log::error('[LimoSms] sendPeerToPeer: ' . $error);
            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $error);
            }
            return;
        }

        $senderNumber = $this->sender ?: (env('LIMOSMS_SENDER') ?: env('SMS_SENDER'));
        if (empty($senderNumber)) {
            $error = 'LimoSms SenderNumber is missing. Please configure LIMOSMS_SENDER or SMS_SENDER.';
            Log::error('[LimoSms] sendPeerToPeer: ' . $error);
            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $error);
            }
            return;
        }

        $url = $this->baseUrl . '/sendpeertopeersms';

        $payload = [
            'SenderNumber'       => $senderNumber,
            'MobileNumber'       => array_values($mobileNumbers),
            'Message'            => array_values($bodies),
            'SendToBlocksNumber' => true,
        ];

        try {
            Log::info('[LimoSms] sendPeerToPeer request', [
                'url'    => $url,
                'count'  => count($mobileNumbers),
                'sender' => $senderNumber,
            ]);

            $response = Http::withHeaders([
                'ApiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(45)
                ->post($url, $payload);

            $body = $response->json();

            Log::info('[LimoSms] sendPeerToPeer response', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if (! $response->successful()) {
                $err = 'HTTP ' . $response->status();
                foreach ($messages as $msg) {
                    $msg->markAsFailed('limosms', $err, ['response' => $body]);
                }
                return;
            }

            $isSuccess = $body['Success'] ?? $body['success'] ?? true;
            if ($isSuccess === false) {
                $err = $body['Message'] ?? $body['message'] ?? 'LimoSms rejected peer-to-peer request';
                foreach ($messages as $msg) {
                    $msg->markAsFailed('limosms', $err, ['response' => $body]);
                }
                return;
            }

            foreach ($messages as $msg) {
                $msg->markAsSent('limosms', $body ?? []);
            }

        } catch (\Throwable $e) {
            Log::error('[LimoSms] sendPeerToPeer exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            foreach ($messages as $msg) {
                $msg->markAsFailed('limosms', $e->getMessage());
            }
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
     * ارسال OTP
     */
    public function sendOtp(SmsMessage $message): void
    {
        if (!empty($message->template_key)) {
            $this->sendPattern($message, is_array($message->params) ? $message->params : []);
            return;
        }
        $this->sendText($message);
    }

    /**
     * دریافت اعتبار و اطلاعات پروفایل از وب‌سرویس لیمو اس‌ام‌اس
     */
    public function fetchBalance(): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $url = $this->baseUrl . '/getcurrentcredit';

            $response = Http::withHeaders([
                'ApiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(8)->post($url, []);

            if (! $response->successful()) {
                Log::warning('[LimoSms] fetchBalance failed with HTTP status: ' . $response->status(), [
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (! is_array($data)) {
                return null;
            }

            $isSuccess = !empty($data['success']) || !empty($data['Success']);
            if (! $isSuccess) {
                Log::warning('[LimoSms] fetchBalance returned unsuccessful response', [
                    'response' => $data,
                ]);
                return null;
            }

            $result = $data['result'] ?? $data['Result'] ?? [];
            if (! is_array($result)) {
                $result = [];
            }

            // استخراج شناسه کاربر در لیمو (مستقیم یا داخل userSmsPrice)
            $userId = $result['userSmsPrice']['userId']
                ?? $result['userSmsPrice']['UserId']
                ?? $result['UserId']
                ?? $result['userId']
                ?? $result['Id']
                ?? $result['id']
                ?? null;

            if ($userId !== null) {
                $userId = (string) $userId;
            }

            // استخراج موجودی
            $credit = $result['credit']
                ?? $result['Credit']
                ?? $result['balance']
                ?? $result['Balance']
                ?? null;

            if ($credit !== null) {
                $credit = (float) $credit;
            }

            return [
                'driver'     => 'limosms',
                'balance'    => $credit,
                'user_id'    => $userId,
                'currency'   => 'ریال',
                'charge_url' => $userId ? "https://sms.vardi.ir/Payment/PayWithBank?UserId={$userId}" : null,
                'login_url'  => $userId ? "https://sms.vardi.ir/DirectLogin/{$userId}" : null,
                'meta'       => $result,
            ];

        } catch (\Throwable $e) {
            Log::error('[LimoSms] fetchBalance exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
