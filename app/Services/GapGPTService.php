<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Modules\Settings\Entities\Setting;
use Modules\Settings\Entities\GapGPTLog;
use Illuminate\Support\Facades\Auth;

class GapGPTService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;
    protected int $timeout;

    /**
     * @param array|null $config تنظیمات اختیاری (برای تست اتصال قبل از ذخیره)
     */
    public function __construct(?array $config = null)
    {
        if ($config) {
            // استفاده از تنظیمات دستی
            $this->apiKey = $config['gapgpt_api_key'] ?? '';
            $this->baseUrl = rtrim($config['gapgpt_base_url'] ?? 'https://api.gapgpt.app', '/');
            $this->defaultModel = $config['gapgpt_default_model'] ?? 'gpt-4o-mini';
            $this->timeout = (int) ($config['gapgpt_timeout'] ?? 30);
        } else {
            // بارگذاری تنظیمات از دیتابیس
            $settings = Setting::whereIn('key', [
                'gapgpt_api_key',
                'gapgpt_base_url',
                'gapgpt_default_model',
                'gapgpt_timeout'
            ])->pluck('value', 'key');

            $this->apiKey = $settings['gapgpt_api_key'] ?? '';
            $this->baseUrl = rtrim($settings['gapgpt_base_url'] ?? 'https://api.gapgpt.app', '/');
            $this->defaultModel = $settings['gapgpt_default_model'] ?? 'gpt-4o-mini';
            $this->timeout = (int) ($settings['gapgpt_timeout'] ?? 30);
        }
    }

    /**
     * ارسال درخواست Chat Completion (مانند ChatGPT)
     *
     * @param array $messages آرایه‌ای از پیام‌ها شامل role و content
     * @param string|null $model نام مدل (اختیاری)
     * @param float $temperature میزان خلاقیت (0 تا 2)
     * @param int $maxTokens حداکثر توکن‌های پاسخ
     * @return array|null پاسخ API یا null در صورت خطا
     */
    public function chat(array $messages, ?string $model = null, float $temperature = 0.7, int $maxTokens = 1000): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $url = $this->baseUrl . '/v1/chat/completions';
        $model = $model ?? $this->defaultModel;
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post($url, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            $duration = round((microtime(true) - $startTime) * 1000); // میلی‌ثانیه

            if ($response->successful()) {
                $data = $response->json();

                // ذخیره لاگ موفقیت
                $this->logRequest(
                    $model,
                    $messages,
                    $data,
                    $data['usage']['prompt_tokens'] ?? 0,
                    $data['usage']['completion_tokens'] ?? 0,
                    $data['usage']['total_tokens'] ?? 0,
                    $duration,
                    'success'
                );

                return $data;
            }

            // ذخیره لاگ خطا
            $this->logRequest(
                $model,
                $messages,
                null,
                0,
                0,
                0,
                $duration,
                'error',
                'HTTP ' . $response->status() . ': ' . $response->body()
            );

            return null;

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);

            // ذخیره لاگ استثنا
            $this->logRequest(
                $model,
                $messages,
                null,
                0,
                0,
                0,
                $duration,
                'error',
                $e->getMessage()
            );

            return null;
        }
    }

    /**
     * یک متد کمکی ساده برای دریافت فقط متن پاسخ
     *
     * @param string $prompt متن درخواست کاربر
     * @param string|null $systemPrompt متن دستورالعمل سیستم (اختیاری)
     * @return string|null متن پاسخ یا null
     */
    public function ask(string $prompt, ?string $systemPrompt = null): ?string
    {
        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $result = $this->chat($messages);

        return $result['choices'][0]['message']['content'] ?? null;
    }

    /**
     * دریافت لیست مدل‌های در دسترس
     * مناسب برای تست اتصال
     */
    public function getModels()
    {
         if (empty($this->apiKey)) {
            return null;
        }

        $url = $this->baseUrl . '/v1/models';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout($this->timeout)
            ->get($url);

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * ذخیره لاگ درخواست در دیتابیس
     */
    protected function logRequest($model, $prompt, $response, $promptTokens, $completionTokens, $totalTokens, $duration, $status, $errorMessage = null)
    {
        try {
            GapGPTLog::create([
                'user_id' => Auth::id(), // کاربر فعلی (اگر لاگین باشد)
                'model' => $model,
                'prompt' => $prompt, // آرایه پیام‌ها به صورت JSON ذخیره می‌شود (توسط کست مدل)
                'response' => $response, // پاسخ کامل به صورت JSON
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'duration_ms' => $duration,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // اگر خطایی در ذخیره لاگ رخ داد، نادیده می‌گیریم تا روند اصلی مختل نشود
            // \Log::error('Failed to log GapGPT request: ' . $e->getMessage());
        }
    }
}
