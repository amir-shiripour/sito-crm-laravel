<?php

namespace Modules\Wallet\App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\App\Enums\TransactionStatus;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Events\WalletBalanceUpdated;
use Modules\Wallet\App\Events\WalletTransactionCreated;
use Modules\Wallet\App\Exceptions\InsufficientFundsException;
use Modules\Wallet\App\Exceptions\WalletLockedException;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Models\WalletTransaction;
use InvalidArgumentException;

class WalletService
{
    /**
     * Get or create a wallet for a holder model.
     */
    public function getOrCreateWallet(Model $holder, string $slug = 'default', string $currency = 'IRR'): Wallet
    {
        return Wallet::firstOrCreate(
            [
                'holder_type' => $holder->getMorphClass(),
                'holder_id'   => $holder->getKey(),
                'slug'        => $slug,
            ],
            [
                'name'        => $slug === 'default' ? 'کیف پول اصلی' : $slug,
                'balance'     => 0,
                'currency'    => $currency,
                'is_active'   => true,
            ]
        );
    }

    /**
     * Deposit funds into wallet.
     */
    public function deposit(
        Model $holder,
        float $amount,
        string|TransactionType $type = TransactionType::DEPOSIT,
        ?Model $payable = null,
        ?string $description = null,
        ?array $meta = [],
        string $slug = 'default'
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException("مبلغ شارژ باید بزرگتر از صفر باشد.");
        }

        $typeValue = $type instanceof TransactionType ? $type->value : $type;

        return DB::transaction(function () use ($holder, $amount, $typeValue, $payable, $description, $meta, $slug) {
            $wallet = Wallet::where('holder_type', $holder->getMorphClass())
                ->where('holder_id', $holder->getKey())
                ->where('slug', $slug)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                $wallet = $this->getOrCreateWallet($holder, $slug);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            if (! $wallet->is_active) {
                throw new WalletLockedException();
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter  = $balanceBefore + $amount;

            $wallet->balance = $balanceAfter;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => $typeValue,
                'status'         => TransactionStatus::COMPLETED,
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'payable_type'   => $payable?->getMorphClass(),
                'payable_id'     => $payable?->getKey(),
                'description'    => $description ?? 'شارژ کیف پول',
                'meta'           => $meta,
            ]);

            event(new WalletTransactionCreated($transaction));
            event(new WalletBalanceUpdated($wallet, $transaction));

            return $transaction;
        });
    }

    /**
     * Withdraw funds from wallet.
     */
    public function withdraw(
        Model $holder,
        float $amount,
        string|TransactionType $type = TransactionType::WITHDRAW,
        ?Model $payable = null,
        ?string $description = null,
        ?array $meta = [],
        string $slug = 'default'
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException("مبلغ برداشت باید بزرگتر از صفر باشد.");
        }

        $typeValue = $type instanceof TransactionType ? $type->value : $type;

        return DB::transaction(function () use ($holder, $amount, $typeValue, $payable, $description, $meta, $slug) {
            $wallet = Wallet::where('holder_type', $holder->getMorphClass())
                ->where('holder_id', $holder->getKey())
                ->where('slug', $slug)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                $wallet = $this->getOrCreateWallet($holder, $slug);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            if (! $wallet->is_active) {
                throw new WalletLockedException();
            }

            $balanceBefore = (float) $wallet->balance;

            if ($balanceBefore < $amount) {
                throw new InsufficientFundsException();
            }

            $balanceAfter = $balanceBefore - $amount;

            $wallet->balance = $balanceAfter;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => $typeValue,
                'status'         => TransactionStatus::COMPLETED,
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'payable_type'   => $payable?->getMorphClass(),
                'payable_id'     => $payable?->getKey(),
                'description'    => $description ?? 'برداشت از کیف پول',
                'meta'           => $meta,
            ]);

            event(new WalletTransactionCreated($transaction));
            event(new WalletBalanceUpdated($wallet, $transaction));

            return $transaction;
        });
    }

    /**
     * Transfer funds between two holders.
     */
    public function transfer(
        Model $fromHolder,
        Model $toHolder,
        float $amount,
        ?string $description = null,
        ?array $meta = [],
        string $fromSlug = 'default',
        string $toSlug = 'default'
    ): array {
        return DB::transaction(function () use ($fromHolder, $toHolder, $amount, $description, $meta, $fromSlug, $toSlug) {
            $withdrawTx = $this->withdraw(
                holder: $fromHolder,
                amount: $amount,
                type: TransactionType::TRANSFER,
                payable: $toHolder,
                description: $description ?? 'انتقال موجودی به کاربر دیگر',
                meta: array_merge($meta, ['transfer_role' => 'sender']),
                slug: $fromSlug
            );

            $depositTx = $this->deposit(
                holder: $toHolder,
                amount: $amount,
                type: TransactionType::TRANSFER,
                payable: $fromHolder,
                description: $description ?? 'دریافت انتقال موجودی از کاربر دیگر',
                meta: array_merge($meta, ['transfer_role' => 'recipient', 'related_transaction_id' => $withdrawTx->id]),
                slug: $toSlug
            );

            return [
                'withdraw_transaction' => $withdrawTx,
                'deposit_transaction' => $depositTx,
            ];
        });
    }

    /**
     * Refund a transaction.
     */
    public function refund(
        WalletTransaction $transaction,
        ?float $amount = null,
        ?string $reason = null
    ): WalletTransaction {
        $refundAmount = $amount ?? (float) $transaction->amount;
        $wallet = $transaction->wallet;
        $holder = $wallet->holder;

        if ($refundAmount <= 0) {
            throw new InvalidArgumentException("مبلغ عودت وجه باید بزرگتر از صفر باشد.");
        }

        $description = "عودت وجه مربوط به تراکنش #{$transaction->uuid}" . ($reason ? " ($reason)" : '');
        $meta = [
            'original_transaction_id' => $transaction->id,
            'original_transaction_uuid' => $transaction->uuid,
            'refund_reason' => $reason,
        ];

        // If original was a payment or withdraw, refund means depositing back to holder
        if (in_array($transaction->type, [TransactionType::WITHDRAW, TransactionType::PAYMENT])) {
            return $this->deposit(
                holder: $holder,
                amount: $refundAmount,
                type: TransactionType::REFUND,
                payable: $transaction->payable,
                description: $description,
                meta: $meta,
                slug: $wallet->slug
            );
        }

        // If original was deposit or bonus, refund means withdrawing back
        return $this->withdraw(
            holder: $holder,
            amount: $refundAmount,
            type: TransactionType::REFUND,
            payable: $transaction->payable,
            description: $description,
            meta: $meta,
            slug: $wallet->slug
        );
    }
}
