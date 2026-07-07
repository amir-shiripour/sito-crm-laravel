<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Spam = 'spam';
}
