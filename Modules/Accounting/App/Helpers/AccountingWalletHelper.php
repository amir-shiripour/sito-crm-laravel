<?php

namespace Modules\Accounting\App\Helpers;

use App\Models\Module as DbModule;
use Nwidart\Modules\Facades\Module as NModule;

class AccountingWalletHelper
{
    /**
     * Check if Wallet module is active both in Nwidart modules and in DB.
     */
    public static function isWalletEnabled(): bool
    {
        static $status = null;
        if ($status !== null) {
            return $status;
        }

        try {
            // Check Nwidart module status
            if (class_exists(NModule::class)) {
                if (!NModule::has('Wallet') || !NModule::isEnabled('Wallet')) {
                    return $status = false;
                }
            }

            // Check DB module status
            if (class_exists(DbModule::class)) {
                $dbModule = DbModule::where('slug', 'wallet')->first();
                if ($dbModule && !$dbModule->active) {
                    return $status = false;
                }
            }

            return $status = true;
        } catch (\Throwable $e) {
            return $status = false;
        }
    }
}
