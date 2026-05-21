<?php

namespace Modules\Accounting\App\Services;

use Modules\Accounting\App\Models\Bank;
use Illuminate\Support\Facades\DB;

class BankService
{
    public function createBank(array $data): Bank
    {
        // The 'balance' field now directly represents the initial balance.
        // No need to copy from a non-existent 'initial_balance'.
        return Bank::create($data);
    }

    public function updateBank(Bank $bank, array $data): Bank
    {
        // Ensure balance is not updated directly through this method
        // to maintain data integrity. Balance should only be updated via transactions.
        unset($data['balance']);
        $bank->update($data);
        return $bank;
    }

    public function deleteBank(Bank $bank): void
    {
        // Add a check to prevent deletion if there are associated transactions
        if ($bank->transactions()->exists()) {
            throw new \Exception('Cannot delete bank account with existing transactions.');
        }
        $bank->delete();
    }
}
