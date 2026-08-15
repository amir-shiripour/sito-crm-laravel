<?php

namespace Modules\Accounting\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\Invoice;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\FundAccount;
use Exception;

class ReceiptService
{
    protected AccountingEngine $engine;
    protected InvoiceService $invoiceService;
    protected ChequeService $chequeService;

    public function __construct(AccountingEngine $engine, InvoiceService $invoiceService, ChequeService $chequeService)
    {
        $this->engine = $engine;
        $this->invoiceService = $invoiceService;
        $this->chequeService = $chequeService;
    }

    /**
     * Store a new receipt and update related models.
     *
     * @param array $data Validated request data.
     * @return \Modules\Accounting\App\Models\Document
     * @throws Exception
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $invoice = isset($data['invoice_id']) ? Invoice::findOrFail($data['invoice_id']) : null;
            $paymentMethod = $data['payment_method'];

            if ($paymentMethod === 'cash') {
                $document = $this->handleCashPayment($data, $invoice);
            } elseif ($paymentMethod === 'cheque') {
                $document = $this->handleChequePayment($data, $invoice);
            } else {
                throw new Exception("روش پرداخت انتخاب شده معتبر نیست.");
            }

            // Update Invoice Status if applicable
            if ($invoice) {
                $this->invoiceService->updateInvoiceStatus($invoice);
            }

            return $document;
        });
    }

    private function handleCashPayment(array $data, ?Invoice $invoice)
    {
        $amount = CurrencyService::convertToBaseRial($data['amount']);

        $debitCategoryId = FundAccount::findOrFail($data['fund_account_id'])->category_id;
        if (!$debitCategoryId) {
            throw new Exception('حساب خزانه انتخاب شده به سرفصل حسابداری متصل نیست.');
        }

        if ($invoice) {
            $creditCategoryId = AccountingSetting::get('defaults.receivables_category_id');
        } else {
            $creditCategoryId = $data['category_id'];
        }

        return $this->engine->recordJournalEntry(
            $invoice, $amount, $debitCategoryId, $creditCategoryId,
            $data['fund_account_id'], $data['description'], $data['document_date']
        );
    }

    private function handleChequePayment(array $data, ?Invoice $invoice)
    {
        if (!empty($data['cheque_id'])) {
            $cheque = \Modules\Accounting\Entities\Cheque::findOrFail($data['cheque_id']);
            $amount = (float) $cheque->amount;

            // 1. Update status to 'transferred' with note 'به علت ثبت دستی'
            $cheque->update([
                'status'      => 'transferred',
                'description' => $cheque->description ? ($cheque->description . ' - به علت ثبت دستی') : 'به علت ثبت دستی',
            ]);

            // 2. Record the journal entry
            $debitCategoryId = AccountingSetting::get('defaults.cheques_receivable_category_id')
                ?: \Modules\Accounting\App\Models\Category::where('title', 'like', '%اسناد دریافتنی%')->first()?->id;
            if (!$debitCategoryId) {
                throw new Exception('سرفصل پیش‌فرض اسناد دریافتنی مشخص نشده است.');
            }

            if ($invoice) {
                $creditCategoryId = AccountingSetting::get('defaults.receivables_category_id')
                    ?: \Modules\Accounting\App\Models\Category::where('title', 'like', '%حساب‌های دریافتنی%')->first()?->id;
            } else {
                $creditCategoryId = $data['category_id'] ?? null;
            }

            $documentDate = $data['document_date'] ?? date('Y-m-d');
            $description = $data['description'] . " (خرج چک صیادی {$cheque->cheque_number} - به علت ثبت دستی)";

            $document = $this->engine->recordJournalEntry(
                $cheque, // Documentable is the cheque
                $amount, $debitCategoryId, $creditCategoryId,
                null,
                $description, $documentDate
            );

            $cheque->attachedDocuments()->syncWithoutDetaching([
                $document->id => ['notes' => 'خرج شده به علت ثبت دستی', 'created_at' => now(), 'updated_at' => now()]
            ]);

            return $document;
        }

        // Fallback for array format if needed
        $chequeData = $data['cheque'];
        $amount = CurrencyService::convertToBaseRial($chequeData['amount']);

        $cheque = $this->chequeService->createCheque(array_merge($chequeData, [
            'type' => 'receivable',
            'status' => 'transferred',
            'notes'  => 'به علت ثبت دستی',
            'registered_by_user_id' => auth()->id(),
        ]));

        $debitCategoryId = AccountingSetting::get('defaults.cheques_receivable_category_id');
        if (!$debitCategoryId) {
            throw new Exception('سرفصل پیش‌فرض اسناد دریافتنی در تنظیمات مشخص نشده است.');
        }

        $creditCategoryId = $invoice ? AccountingSetting::get('defaults.receivables_category_id') : ($data['category_id'] ?? null);

        return $this->engine->recordJournalEntry(
            $cheque,
            $amount, $debitCategoryId, $creditCategoryId,
            null,
            $data['description'] . " (به علت ثبت دستی)", $data['document_date']
        );
    }
}
