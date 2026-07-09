<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use App\Services\GapGPTService;
use Modules\Settings\Entities\Setting;

final class ContentAIService
{
    private ?GapGPTService $gapgpt = null;
    private bool $isAvailable = false;

    public function __construct()
    {
        if (class_exists(GapGPTService::class)) {
            $apiKey = Setting::where('key', 'gapgpt_api_key')->value('value');
            if (!empty($apiKey)) {
                $this->gapgpt = new GapGPTService();
                $this->isAvailable = true;
            }
        }
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function suggestTitle(string $topic): ?string
    {
        if (!$this->isAvailable || !$this->gapgpt) return null;

        return $this->gapgpt->ask(
            prompt: "یک عنوان جذاب و SEO-friendly فارسی برای مقاله‌ای با موضوع «{$topic}» پیشنهاد بده. فقط عنوان را بنویس.",
            systemPrompt: 'تو یک متخصص تولید محتوای فارسی هستی.',
            timeout: 15
        );
    }

    public function generateExcerpt(string $content, int $maxWords = 50): ?string
    {
        if (!$this->isAvailable || !$this->gapgpt) return null;

        return $this->gapgpt->ask(
            prompt: "از متن زیر یک خلاصه حداکثر {$maxWords} کلمه‌ای فارسی بنویس:\n\n{$content}",
            timeout: 20
        );
    }

    public function generateSeoDescription(string $title, string $excerpt): ?string
    {
        if (!$this->isAvailable || !$this->gapgpt) return null;

        return $this->gapgpt->ask(
            prompt: "برای صفحه‌ای با عنوان «{$title}» و خلاصه زیر، یک متا دسکریپشن SEO حداکثر 160 کاراکتر فارسی بنویس:\n{$excerpt}",
            timeout: 15,
            maxTokens: 200
        );
    }

    public function suggestTags(string $content, int $count = 5): ?array
    {
        if (!$this->isAvailable || !$this->gapgpt) return null;

        $result = $this->gapgpt->ask(
            prompt: "از محتوای زیر، {$count} برچسب (tag) مرتبط و SEO-friendly فارسی پیشنهاد بده. فقط برچسب‌ها را با کاما جدا کن:\n\n{$content}",
            timeout: 15,
            maxTokens: 100
        );

        if (!$result) return null;
        return array_filter(array_map('trim', explode(',', $result)));
    }

    public function improveText(string $text): ?string
    {
        if (!$this->isAvailable || !$this->gapgpt) return null;

        return $this->gapgpt->ask(
            prompt: "متن زیر را به فارسی روان‌تر و حرفه‌ای‌تر بازنویسی کن:\n\n{$text}",
            timeout: 30
        );
    }
}
