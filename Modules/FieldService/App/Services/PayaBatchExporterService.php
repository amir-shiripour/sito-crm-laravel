<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Collection;
use Modules\FieldService\App\Enums\SettlementStatus;
use Modules\FieldService\App\Models\PayaSettlement;

class PayaBatchExporterService
{
    /**
     * Generate standard CSV text for Paya batch transfer.
     * Format: شبا,مبلغ (ریال),نام صاحب حساب,شناسه پیگیری,توضیحات
     */
    public function generatePayaCsv(Collection $settlements, string $batchRef): string
    {
        $lines = [];
        $lines[] = 'IBAN,Amount,AccountOwner,TrackingCode,BatchRef,Description';

        foreach ($settlements as $settlement) {
            $shaba = preg_replace('/[^A-Za-z0-9]/', '', $settlement->shaba_number);
            $amount = (int) $settlement->amount;
            $name = str_replace(',', ' ', $settlement->account_owner_name);
            $code = $settlement->tracking_code;
            $desc = "تسویه کارکرد تکنسین نصاب‌باشی - {$settlement->technician?->full_name}";

            $lines[] = "{$shaba},{$amount},\"{$name}\",{$code},{$batchRef},\"{$desc}\"";

            // Mark settlement as processing
            $settlement->status = SettlementStatus::PROCESSING;
            $settlement->batch_reference = $batchRef;
            $settlement->processed_at = now();
            $settlement->save();
        }

        return implode("\r\n", $lines);
    }
}
