<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Observers;

use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Services\SlugService;
use Modules\ContentForge\App\Services\ShortLinkService;
use Modules\ContentForge\App\Services\SchemaMarkupService;
use Modules\ContentForge\Entities\ContentSetting;
use Illuminate\Support\Str;

class ContentPostObserver
{
    public function creating(ContentPost $post): void
    {
        // 1. Auto-generate slug if empty
        if (empty($post->slug)) {
            $post->slug = SlugService::generate($post->title, 'content_posts');
        }

        // 2. Calculate reading time
        $this->calculateReadingTime($post);

        // 3. Generate short_code
        if (empty($post->short_code)) {
            $length = (int) ContentSetting::getValue('short_link.code_length', 6);
            $post->short_code = Str::random($length);
        }
    }

    public function saving(ContentPost $post): void
    {
        // Auto-generate slug if title changed and slug is empty
        if (empty($post->slug)) {
            $post->slug = SlugService::generate($post->title, 'content_posts', $post->id);
        }

        // Recalculate reading time on save
        $this->calculateReadingTime($post);

        // Auto Schema Markup if enabled
        if (ContentSetting::getValue('seo.auto_schema_markup', 'true') === 'true') {
            $post->schema_markup = app(SchemaMarkupService::class)->generate($post);
        }

        // Auto SEO description if empty
        if (empty($post->seo_description) && ContentSetting::getValue('seo.auto_generate_description', 'true') === 'true') {
            $post->seo_description = Str::limit(strip_tags($post->excerpt ?? $post->body_html ?? ''), (int) ContentSetting::getValue('seo.description_length', 160));
        }
    }

    public function created(ContentPost $post): void
    {
        // Generate entry in content_short_links
        \Modules\ContentForge\App\Models\ContentShortLink::firstOrCreate(
            ['post_id' => $post->id],
            [
                'code' => $post->short_code,
            ]
        );
    }

    protected function calculateReadingTime(ContentPost $post): void
    {
        $bodyText = strip_tags($post->body_html ?? '');
        // Persian words split by space/punctuation
        $words = preg_split('/[\s,‌.()]+/u', $bodyText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        $wpm = (int) ContentSetting::getValue('general.reading_time_wpm', 200);
        $post->reading_time = (int) max(1, ceil($wordCount / ($wpm ?: 200)));
    }
}
