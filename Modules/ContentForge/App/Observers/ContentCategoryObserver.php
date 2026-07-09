<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Observers;

use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Services\SlugService;

class ContentCategoryObserver
{
    public function creating(ContentCategory $category): void
    {
        if (empty($category->slug)) {
            $category->slug = SlugService::generate($category->name, 'content_categories');
        }
    }

    public function saving(ContentCategory $category): void
    {
        if (empty($category->slug)) {
            $category->slug = SlugService::generate($category->name, 'content_categories', $category->id);
        }
    }
}
