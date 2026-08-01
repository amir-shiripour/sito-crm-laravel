<?php

namespace Modules\Wallet\App\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    public function __construct(string $message = "موجودی کیف پول برای انجام این تراکنش کافی نمی‌باشد.", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
