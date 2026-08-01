<?php

namespace Modules\Wallet\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Models\WalletTransaction;

class WalletBalanceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Wallet $wallet,
        public WalletTransaction $transaction
    ) {}
}
