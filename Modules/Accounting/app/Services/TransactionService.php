<?php

namespace Modules\Accounting\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Transaction;
use Exception;

class TransactionService
{
    /**
     * A new transaction (deposit, withdraw, or transfer) and updates bank balances.
     *
     * @param string $type The type of transaction: 'deposit', 'withdraw', 'transfer'.
     * @param int $amount The amount of the transaction.
     * @param int|null $toBankId The ID of the destination bank.
     * @param int|null $fromBankId The ID of the source bank.
     * @param string|null $description A description for the transaction.
     * @param string|null $date The date of the transaction (Y-m-d H:i:s).
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(
        string $type,
        int|float $amount,
        ?int $toBankId = null,
        ?int $fromBankId = null,
        ?string $description = null,
        ?string $date = null
    ): Transaction {
        // Basic validation
        if ($type === 'deposit' && !$toBankId) {
            throw new Exception('Deposit requires a destination bank.');
        }
        if ($type === 'withdraw' && !$fromBankId) {
            throw new Exception('Withdrawal requires a source bank.');
        }
        if ($type === 'transfer' && (!$fromBankId || !$toBankId)) {
            throw new Exception('Transfer requires both source and destination banks.');
        }

        return DB::transaction(function () use ($type, $amount, $toBankId, $fromBankId, $description, $date) {
            // 1. Lock and update balances
            if ($fromBankId) {
                $fromBank = Bank::where('id', $fromBankId)->lockForUpdate()->firstOrFail();
                // Use getRawOriginal to bypass the MoneyCast when checking
                $currentBalance = (float)$fromBank->getRawOriginal('current_balance');
                if ($currentBalance < (float)$amount) {
                    throw new Exception('موجودی حساب مبدا برای این تراکنش کافی نمی باشد.');
                }

                // We should update the raw value using DB query to avoid Cast interference during save
                Bank::where('id', $fromBankId)->update([
                    'current_balance' => $currentBalance - $amount
                ]);
            }

            if ($toBankId) {
                $toBank = Bank::where('id', $toBankId)->lockForUpdate()->firstOrFail();
                $currentBalanceTo = (float)$toBank->getRawOriginal('current_balance');

                Bank::where('id', $toBankId)->update([
                    'current_balance' => $currentBalanceTo + $amount
                ]);
            }

            // 2. Create the transaction record
            // For transaction, since we use MoneyCast on 'amount', we need to pass the raw integer value
            // and let the cast format it on retrieve.
            $transaction = Transaction::create([
                'from_bank_id' => $fromBankId,
                'to_bank_id' => $toBankId,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'transaction_date' => $date ?? now(),
            ]);

            return $transaction;
        });
    }
}
