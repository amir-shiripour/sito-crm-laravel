<?php

namespace Modules\Accounting\App\Services;

use Modules\Accounting\App\Models\FundAccount;
use Exception;

class FundAccountService
{
    public function createFundAccount(array $data): FundAccount
    {
        // Handle specific type requirements if needed (e.g., clearing bank fields if type is cash)
        $data = $this->sanitizeDataBasedOnType($data);
        return FundAccount::create($data);
    }

    public function updateFundAccount(FundAccount $fundAccount, array $data): FundAccount
    {
        $data = $this->sanitizeDataBasedOnType($data);
        $fundAccount->update($data);
        return $fundAccount;
    }

    public function deleteFundAccount(FundAccount $fundAccount): void
    {
        // TODO: Check if fund account has associated transactions before deleting
        // If it has transactions, it shouldn't be deleted or should be handled carefully
        if ($fundAccount->transactions()->exists()) {
             throw new Exception('این حساب خزانه‌داری دارای تراکنش است و قابل حذف نمی‌باشد. می‌توانید آن را غیرفعال کنید.');
        }

        $fundAccount->delete();
    }

    /**
     * Clears bank-specific fields if the type is not a bank.
     */
    protected function sanitizeDataBasedOnType(array $data): array
    {
        if (isset($data['type']) && $data['type'] !== 'bank') {
            $data['bank_name'] = null;
            $data['branch_name'] = null;
            $data['account_holder_name'] = null;
            $data['account_number'] = null;
            $data['card_number'] = null;
            $data['iban'] = null;
        }

        // If it's not a gateway, clear the gateway ID
        if (isset($data['type']) && $data['type'] !== 'gateway') {
             $data['core_gateway_id'] = null;
        }

        return $data;
    }
}
