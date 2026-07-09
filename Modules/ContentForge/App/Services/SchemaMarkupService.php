<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Enums\PostType;

final class SchemaMarkupService
{
    public function generate(ContentPost $post): array
    {
        $type = $post->type === PostType::Post ? 'BlogPosting' : 'WebPage';

        return [
            '@context'      => 'https://schema.org',
            '@type'         => $type,
            'headline'      => $post->seo_title ?? $post->title,
            'description'   => $post->seo_description,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified'  => $post->updated_at->toIso8601String(),
            'author'        => [
                '@type' => 'Person',
                'name'  => $post->author->name ?? 'مدیر سیستم',
            ],
            'image'         => $post->og_image ? asset('storage/' . $post->og_image) : ($post->cover_image ? asset('storage/' . $post->cover_image) : null),
            'url'           => $post->canonical_url ?? url()->current(),
        ];
    }
}
