<?php

namespace Modules\Accounting\app\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\app\Models\Bank;
use Modules\Accounting\app\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;

class DocumentService
{
    /**
     * Store a newly created document in storage and update bank balance.
     *
     * @param array $data
     * @return Document
     * @throws Exception
     */
    public function store(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            // Amount is already cleaned in the request class
            $amount = (float) $data['amount'];
            $bankId = $data['bank_id'];

            $bank = Bank::where('id', $bankId)->lockForUpdate()->firstOrFail();

            if ($data['type'] === 'expense') {
                $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                if (!$allowNegative && $bank->balance < $amount) {
                    throw new Exception('موجودی حساب (' . number_format($bank->balance) . ' ریال) برای ثبت این هزینه کافی نیست و امکان منفی شدن موجودی در تنظیمات غیرفعال است.');
                }
                $bank->balance -= $amount;
            } else { // income
                $bank->balance += $amount;
            }

            $bank->save();

            $document = Document::create($data);

            return $document;
        });
    }

    /**
     * Remove the specified document from storage and revert bank balance.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $document = Document::findOrFail($id);
            $bank = $document->bank()->lockForUpdate()->firstOrFail();
            $amount = (float) $document->amount;

            if ($document->type === 'expense') {
                $bank->balance += $amount;
            } elseif ($document->type === 'income') {
                $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                if (!$allowNegative && $bank->balance < $amount) {
                    throw new Exception('موجودی حساب برای بازگشت این درآمد کافی نیست و امکان منفی شدن موجودی در تنظیمات غیرفعال است.');
                }
                $bank->balance -= $amount;
            }

            $bank->save();

            return $document->delete();
        });
    }
}
