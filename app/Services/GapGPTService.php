<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Entities\Setting;
use Modules\Settings\Entities\GapGPTLog;
use Illuminate\Support\Facades\Auth;

class GapGPTService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;
    protected int $timeout;

    public function __construct(?array $config = null)
    {
        if ($config) {
            $this->apiKey = $config['gapgpt_api_key'] ?? '';
            $this->baseUrl = rtrim($config['gapgpt_base_url'] ?? 'https://api.gapgpt.app', '/');
            $this->defaultModel = $config['gapgpt_default_model'] ?? 'gpt-4o-mini';
            $this->timeout = (int) ($config['gapgpt_timeout'] ?? 30);
        } else {
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

    public function chat(array $messages, ?string $model = null, float $temperature = 0.7, int $maxTokens = 2000, ?int $timeout = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('GapGPT Error: API Key is missing.');
            return null;
        }

        $url = $this->baseUrl . '/v1/chat/completions';
        $model = $model ?? $this->defaultModel;
        $finalTimeout = $timeout ?? $this->timeout;

        $startTime = microtime(true);

        Log::info("GapGPT Request Started. Model: $model, Timeout: $finalTimeout sec");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($finalTimeout)
            ->post($url, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                // 'stream' => false, // حذف شد تا اگر سرویس فورس استریم دارد، کار کند
            ]);

            $duration = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $body = $response->body();
                $data = $response->json(); // تلاش برای پارس JSON استاندارد

                // حالت ۱: پاسخ استاندارد JSON
                if (isset($data['choices']) && !empty($data['choices'])) {
                    Log::info("GapGPT Request Successful (Standard JSON). Duration: {$duration}ms");
                    $this->logRequest($model, $messages, $data, $data['usage']['prompt_tokens'] ?? 0, $data['usage']['completion_tokens'] ?? 0, $data['usage']['total_tokens'] ?? 0, $duration, 'success');
                    return $data;
                }

                // حالت ۲: پاسخ استریم (SSE)
                if (strpos($body, 'data: ') !== false) {
                    Log::info("GapGPT Request Successful (Stream Detected). Parsing chunks...");

                    $fullContent = '';
                    $lines = explode("\n", $body);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, 'data: ')) {
                            $jsonStr = substr($line, 6); // حذف 'data: '
                            if ($jsonStr === '[DONE]') continue;

                            $chunk = json_decode($jsonStr, true);
                            if (isset($chunk['choices'][0]['delta']['content'])) {
                                $fullContent .= $chunk['choices'][0]['delta']['content'];
                            }
                        }
                    }

                    if (!empty($fullContent)) {
                        Log::info("GapGPT Stream Parsed Successfully. Content Length: " . strlen($fullContent));

                        // ساخت یک پاسخ شبیه به استاندارد برای سازگاری با بقیه کد
                        $fakeData = [
                            'choices' => [
                                [
                                    'message' => [
                                        'role' => 'assistant',
                                        'content' => $fullContent
                                    ],
                                    'finish_reason' => 'stop',
                                    'index' => 0
                                ]
                            ],
                            'usage' => [
                                'prompt_tokens' => 0, // در استریم معمولاً محاسبه نمی‌شود
                                'completion_tokens' => 0,
                                'total_tokens' => 0
                            ]
                        ];

                        $this->logRequest($model, $messages, $fakeData, 0, 0, 0, $duration, 'success');
                        return $fakeData;
                    }
                }

                // اگر به اینجا رسیدیم یعنی نه JSON استاندارد بود نه استریم معتبر
                Log::warning("GapGPT Response Unknown Format: " . substr($body, 0, 500));
                $this->logRequest($model, $messages, null, 0, 0, 0, $duration, 'error', 'Unknown Format: ' . substr($body, 0, 200));
                return null;
            }

            Log::error("GapGPT HTTP Error: " . $response->status() . " - " . $response->body());
            $this->logRequest($model, $messages, null, 0, 0, 0, $duration, 'error', 'HTTP ' . $response->status() . ': ' . $response->body());
            return null;

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            Log::error("GapGPT Exception: " . $e->getMessage());
            $this->logRequest($model, $messages, null, 0, 0, 0, $duration, 'error', $e->getMessage());
            return null;
        }
    }

    public function ask(string $prompt, ?string $systemPrompt = null, ?int $timeout = null, int $maxTokens = 2000): ?string
    {
        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $result = $this->chat($messages, null, 0.7, $maxTokens, $timeout);

        if ($result && isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }

        return null;
    }

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

    protected function logRequest($model, $prompt, $response, $promptTokens, $completionTokens, $totalTokens, $duration, $status, $errorMessage = null)
    {
        try {
            GapGPTLog::create([
                'user_id' => Auth::id(),
                'model' => $model,
                'prompt' => $prompt,
                'response' => $response,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'duration_ms' => $duration,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save GapGPTLog: ' . $e->getMessage());
        }
    }
}
