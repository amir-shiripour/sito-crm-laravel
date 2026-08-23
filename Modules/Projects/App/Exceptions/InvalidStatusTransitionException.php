<?php

namespace Modules\Projects\App\Exceptions;

use RuntimeException;

class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(string $fromName, string $toName)
    {
        parent::__construct("انتقال وضعیت از «{$fromName}» به «{$toName}» مجاز نیست.");
    }
}
