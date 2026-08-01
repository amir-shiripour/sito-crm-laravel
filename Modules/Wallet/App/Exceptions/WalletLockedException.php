<?php

namespace Modules\Wallet\App\Exceptions;

use Exception;

class WalletLockedException extends Exception
{
    public function __construct(string $message = "کیف پول مورد نظر مسدود یا غیرفعال می‌باشد.", int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
