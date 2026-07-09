<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\Entities\ContentSetting;

final class ThemeResolver
{
    public function resolveForPost(ContentPost $post, string $view): string
    {
        $chain = array_filter([
            $post->theme_key,
            $post->category?->theme_key,
            $post->entity?->theme_key,
            ContentSetting::getValue('general.default_theme_key', 'content'),
            'content',
        ]);

        foreach ($chain as $themeKey) {
            $viewPath = "themes.{$themeKey}.{$view}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        return "contentforge::web.{$view}";
    }

    public function resolveForArchive(ContentEntity $entity, string $view = 'archive'): string
    {
        $chain = array_filter([
            $entity->theme_key,
            ContentSetting::getValue('general.default_theme_key', 'content'),
            'content',
        ]);

        foreach ($chain as $themeKey) {
            $viewPath = "themes.{$themeKey}.{$view}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        return "contentforge::web.{$view}";
    }
}
