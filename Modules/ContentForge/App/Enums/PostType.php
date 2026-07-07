<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Enums;

enum PostType: string
{
    case Page = 'page';
    case Post = 'post';
}
