<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Archived = 'archived';
}
