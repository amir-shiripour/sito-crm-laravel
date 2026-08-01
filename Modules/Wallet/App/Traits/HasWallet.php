<?php

namespace Modules\Wallet\App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Services\WalletService;

trait HasWallet
{
    /**
     * Get all wallets for the model.
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'holder');
    }

    /**
     * Get default wallet for the model.
     */
    public function defaultWallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'holder')->where('slug', 'default');
    }

    /**
     * Get or create a specific wallet by slug.
     */
    public function getWallet(string $slug = 'default'): Wallet
    {
        /** @var WalletService $service */
        $service = app(WalletService::class);
        return $service->getOrCreateWallet($this, $slug);
    }

    /**
     * Get wallet balance.
     */
    public function getBalance(string $slug = 'default'): float
    {
        return (float) $this->getWallet($slug)->balance;
    }

    /**
     * Quick Deposit helper.
     */
    public function deposit(
        float $amount,
        string|TransactionType $type = 'deposit',
        mixed $payable = null,
        ?string $description = null,
        ?array $meta = [],
        string $slug = 'default'
    ) {
        return app(WalletService::class)->deposit($this, $amount, $type, $payable, $description, $meta, $slug);
    }

    /**
     * Quick Withdraw helper.
     */
    public function withdraw(
        float $amount,
        string|TransactionType $type = 'withdraw',
        mixed $payable = null,
        ?string $description = null,
        ?array $meta = [],
        string $slug = 'default'
    ) {
        return app(WalletService::class)->withdraw($this, $amount, $type, $payable, $description, $meta, $slug);
    }
}
