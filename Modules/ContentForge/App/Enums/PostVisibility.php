<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Enums;

enum PostVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case Password = 'password';
}
