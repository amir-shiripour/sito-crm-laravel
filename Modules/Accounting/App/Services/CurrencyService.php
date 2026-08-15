<?php

namespace Modules\Accounting\App\Services;

use Modules\Accounting\App\Models\AccountingSetting;

class CurrencyService
{
    /**
     * Get the base currency from settings ('ریال' or 'تومان').
     * Default is 'ریال'.
     *
     * @return string
     */
    public static function getBaseCurrency(): string
    {
        return AccountingSetting::getValue('general.currency', 'ریال');
    }

    /**
     * Convert an input amount (which is in the base currency) to Rials for database storage.
     * Controlled by settings: if base currency is 'تومان', multiply by 10.
     *
     * @param float|int|string|null $amount
     * @return float|int
     */
    public static function convertToBaseRial($amount)
    {
        if (empty($amount)) {
            return 0;
        }

        return (float) str_replace(',', '', (string)$amount);
    }

    /**
     * Convert an amount from a specified source currency to Base amount.
     * Only label is switched in settings; no arithmetic transformation is applied.
     *
     * @param float|int|string|null $amount
     * @param string|null $sourceCurrency
     * @return float|int
     */
    public static function amountInRial($amount, ?string $sourceCurrency = null)
    {
        if (empty($amount)) {
            return 0;
        }

        return (float) str_replace(',', '', (string)$amount);
    }

    /**
     * Convert an amount from database for display.
     * Only label is switched in settings; no arithmetic transformation is applied.
     *
     * @param float|int|string|null $amountInRial
     * @return float|int
     */
    public static function convertForDisplay($amountInRial)
    {
        if (empty($amountInRial)) {
            return 0;
        }

        return (float) str_replace(',', '', (string)$amountInRial);
    }

    /**
     * Format the database Rial amount into a human readable string with suffix based on settings.
     * Example output: "6,758,000 تومان" or "67,580,000 ریال"
     *
     * @param float|int|string|null $amountInRial
     * @return string
     */
    public static function formatWithSuffix($amountInRial): string
    {
        $amountForDisplay = self::convertForDisplay($amountInRial);
        $currency = self::getBaseCurrency();

        return number_format($amountForDisplay) . ' ' . $currency;
    }
}
