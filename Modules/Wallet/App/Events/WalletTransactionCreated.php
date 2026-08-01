<?php

namespace Modules\Wallet\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Wallet\App\Models\WalletTransaction;

class WalletTransactionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WalletTransaction $transaction
    ) {}
}
