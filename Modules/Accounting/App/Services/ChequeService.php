<?php

namespace Modules\Accounting\App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Clients\Entities\Client;

class ChequeService
{
    protected AccountingEngine $engine;

    public function __construct(AccountingEngine $engine)
    {
        $this->engine = $engine;
    }

    public function createCheque(array $data): Cheque
    {
        return DB::transaction(function () use ($data) {
            $type = $data['type'];
            $data['status'] = 'pending';
            $data['registered_by_user_id'] = auth()->id();

            if (request()->hasFile('image')) {
                $path = request()->file('image')->store('cheques', 'public');
                $data['image_path'] = $path;
            }

            $cheque = Cheque::create($data);

            $client = Client::findOrFail($data['client_id']);

            if ($type === 'receivable') {
                $this->recordReceivableChequeDocument($cheque, $client);
            } else {
                $this->recordPayableChequeDocument($cheque, $client);
            }

            return $cheque;
        });
    }

    /**
     * Attach a cheque to an invoice.
     */
    public function attachToInvoice(Cheque $cheque, int $invoiceId, ?string $notes = null): void
    {
        $cheque->attachedInvoices()->syncWithoutDetaching([
            $invoiceId => ['notes' => $notes, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Attach a cheque to an accounting document.
     */
    public function attachToDocument(Cheque $cheque, int $documentId, ?string $notes = null): void
    {
        $cheque->attachedDocuments()->syncWithoutDetaching([
            $documentId => ['notes' => $notes, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    private function recordReceivableChequeDocument(Cheque $cheque, Client $client): void
    {
        $receivableDocsCatId = AccountingSetting::get('defaults.cheques_receivable_category_id');
        $customerAccountCatId = AccountingSetting::get('defaults.receivables_category_id');

        if (!$receivableDocsCatId || !$customerAccountCatId) {
            throw new Exception('سرفصل‌های پیش‌فرض برای "اسناد دریافتنی" یا "حساب‌های دریافتنی" در تنظیمات مشخص نشده است.');
        }

        $this->engine->recordJournalEntry(
            $cheque, $cheque->amount, $receivableDocsCatId, $customerAccountCatId, null,
            "دریافت چک شماره {$cheque->cheque_number} از مشتری: {$client->full_name}"
        );
    }

    private function recordPayableChequeDocument(Cheque $cheque, Client $client): void
    {
        $payableDocsCatId = AccountingSetting::get('defaults.cheques_payable_category_id');
        $supplierAccountCatId = AccountingSetting::get('defaults.payables_category_id');

        if (!$payableDocsCatId || !$supplierAccountCatId) {
            throw new Exception('سرفصل‌های پیش‌فرض برای "اسناد پرداختنی" یا "حساب‌های پرداختنی" در تنظیمات مشخص نشده است.');
        }

        $this->engine->recordJournalEntry(
            $cheque, $cheque->amount, $supplierAccountCatId, $payableDocsCatId, null,
            "صدور چک شماره {$cheque->cheque_number} در وجه: {$cheque->payee_name}"
        );
    }

    public function deposit(Cheque $cheque, int $fundAccountId): Cheque
    {
        if ($cheque->type !== 'receivable' || $cheque->status !== 'pending') {
            throw new Exception("فقط چک‌های دریافتنی در وضعیت 'در جریان' قابل واگذاری به بانک هستند.");
        }

        $inTransitCatId = AccountingSetting::get('defaults.cheques_in_transit_category_id');
        $receivableDocsCatId = AccountingSetting::get('defaults.cheques_receivable_category_id');
        if(!$inTransitCatId || !$receivableDocsCatId) throw new Exception('سرفصل‌های پیش‌فرض چک تعریف نشده‌اند.');

        return DB::transaction(function () use ($cheque, $fundAccountId, $inTransitCatId, $receivableDocsCatId) {
            $cheque->update(['status' => 'deposited', 'deposited_fund_account_id' => $fundAccountId]);

            $this->engine->recordJournalEntry(
                $cheque, $cheque->amount, $inTransitCatId, $receivableDocsCatId, null,
                "واگذاری چک شماره {$cheque->cheque_number} به بانک"
            );

            return $cheque;
        });
    }

    public function clear(Cheque $cheque, int $fundAccountId): Cheque
    {
        if ($cheque->status === 'cleared') {
            throw new Exception("این چک قبلاً وصول شده است.");
        }

        return DB::transaction(function () use ($cheque, $fundAccountId) {
            $fundAccount = FundAccount::findOrFail($fundAccountId);
            $bankCatId = $fundAccount->category_id;

            if (!$bankCatId) {
                throw new Exception('حساب بانکی انتخاب شده به سرفصل حسابداری متصل نیست.');
            }

            $cheque->update(['status' => 'cleared', 'cleared_fund_account_id' => $fundAccountId]);

            if ($cheque->type === 'receivable') {
                $creditCatId = $cheque->getOriginal('status') === 'bounced'
                    ? AccountingSetting::get('defaults.receivables_category_id')
                    : AccountingSetting::get('defaults.cheques_in_transit_category_id');

                if (!$creditCatId) throw new Exception('سرفصل پیش‌فرض "حساب‌های دریافتنی" یا "اسناد در جریان وصول" تعریف نشده است.');

                $this->engine->recordJournalEntry($cheque, $cheque->amount, $bankCatId, $creditCatId, $fundAccountId, "وصول چک شماره {$cheque->cheque_number}");

            } else { // payable
                $payableDocsCatId = AccountingSetting::get('defaults.cheques_payable_category_id');
                if (!$payableDocsCatId) throw new Exception('سرفصل پیش‌فرض "اسناد پرداختنی" تعریف نشده است.');

                $this->engine->recordJournalEntry($cheque, $cheque->amount, $payableDocsCatId, $bankCatId, $fundAccountId, "پاس شدن چک پرداختی شماره {$cheque->cheque_number}");
            }

            return $cheque;
        });
    }

    public function revertClearance(Cheque $cheque): Cheque
    {
        if ($cheque->status !== 'cleared') {
            throw new Exception("فقط چک‌های وصول شده قابل برگشت به حالت قبل هستند.");
        }

        return DB::transaction(function () use ($cheque) {
            // Find the clearance document (usually the latest one)
            $document = $cheque->documents()->latest()->first();

            if ($document) {
                // Delete the transactions and the document to reverse the accounting effect
                $document->transactions()->delete();
                $document->delete();
            }

            // Determine previous status based on cheque type
            // For receivable, it must have been 'deposited' before being 'cleared' (unless it was bounced and cleared again, but standard is deposited)
            // Note: If we want to be 100% accurate we could check the previous document, but defaulting to 'deposited' for receivable and 'pending' for payable is the standard flow.
            $previousStatus = $cheque->type === 'receivable' ? 'deposited' : 'pending';

            $cheque->update([
                'status' => $previousStatus,
                'cleared_fund_account_id' => null
            ]);

            return $cheque;
        });
    }

    public function bounce(Cheque $cheque): Cheque
    {
        if ($cheque->status === 'bounced') throw new Exception("این چک قبلاً برگشت خورده است.");

        $receivableDocsCatId = AccountingSetting::get('defaults.cheques_receivable_category_id');
        $customerAccountCatId = AccountingSetting::get('defaults.receivables_category_id');
        $inTransitCatId = AccountingSetting::get('defaults.cheques_in_transit_category_id');
        $payableDocsCatId = AccountingSetting::get('defaults.cheques_payable_category_id');
        $supplierAccountCatId = AccountingSetting::get('defaults.payables_category_id');

        if(!$receivableDocsCatId || !$customerAccountCatId || !$inTransitCatId || !$payableDocsCatId || !$supplierAccountCatId) {
            throw new Exception('سرفصل‌های پیش‌فرض برای عملیات برگشت چک تعریف نشده‌اند.');
        }

        return DB::transaction(function () use ($cheque, $receivableDocsCatId, $customerAccountCatId, $inTransitCatId, $payableDocsCatId, $supplierAccountCatId) {
            $originalStatus = $cheque->status;
            $cheque->update(['status' => 'bounced']);

            if ($cheque->type === 'receivable') {
                $sourceCatId = $originalStatus === 'deposited' ? $inTransitCatId : $receivableDocsCatId;
                $this->engine->recordJournalEntry($cheque, $cheque->amount, $customerAccountCatId, $sourceCatId, null, "برگشت خوردن چک شماره {$cheque->cheque_number}");
            } else { // payable
                $this->engine->recordJournalEntry($cheque, $cheque->amount, $payableDocsCatId, $supplierAccountCatId, null, "برگشت خوردن چک پرداختی شماره {$cheque->cheque_number}");
            }

            return $cheque;
        });
    }

    public function endorse(Cheque $cheque, int $debitCategoryId, string $description): Cheque
    {
        if ($cheque->type !== 'receivable' || $cheque->status !== 'pending') {
            throw new Exception("فقط چک‌های دریافتنی در وضعیت 'در جریان' قابل خرج کردن هستند.");
        }

        $receivableDocsCatId = AccountingSetting::get('defaults.cheques_receivable_category_id');
        if(!$receivableDocsCatId) throw new Exception('سرفصل پیش‌فرض اسناد دریافتنی تعریف نشده.');

        return DB::transaction(function () use ($cheque, $debitCategoryId, $description, $receivableDocsCatId) {
            $cheque->update(['status' => 'transferred']);

            $this->engine->recordJournalEntry(
                $cheque, $cheque->amount, $debitCategoryId, $receivableDocsCatId, null,
                "خرج کردن چک شماره {$cheque->cheque_number}. {$description}"
            );

            return $cheque;
        });
    }

    public function returnChequeWithCash(Cheque $cheque, int $fundAccountId): Cheque
    {
        if (!in_array($cheque->status, ['pending', 'bounced', 'deposited', 'transferred'])) {
            throw new Exception("فقط چک‌های 'در جریان'، 'واگذار شده'، 'خرج شده' یا 'برگشتی' قابل عودت و تسویه نقدی هستند.");
        }

        return DB::transaction(function () use ($cheque, $fundAccountId) {
            $fundAccount = FundAccount::findOrFail($fundAccountId);
            $fundCatId = $fundAccount->category_id;

            if (!$fundCatId) {
                throw new Exception('حساب انتخاب شده به سرفصل حسابداری متصل نیست.');
            }

            $originalStatus = $cheque->getOriginal('status');
            $cheque->update(['status' => 'returned']);

            if ($cheque->type === 'receivable') {
                $creditCatId = $originalStatus === 'bounced'
                    ? AccountingSetting::get('defaults.receivables_category_id')
                    : AccountingSetting::get('defaults.cheques_receivable_category_id');

                if (!$creditCatId) throw new Exception('سرفصل پیش‌فرض "حساب‌های دریافتنی" یا "اسناد دریافتنی" تعریف نشده است.');

                $this->engine->recordJournalEntry(
                    $cheque, $cheque->amount, $fundCatId, $creditCatId, $fundAccountId,
                    "عودت چک شماره {$cheque->cheque_number} و دریافت وجه نقد"
                );
            } else { // payable
                $debitCatId = $originalStatus === 'bounced'
                    ? AccountingSetting::get('defaults.payables_category_id')
                    : AccountingSetting::get('defaults.cheques_payable_category_id');

                if (!$debitCatId) throw new Exception('سرفصل پیش‌فرض "حساب‌های پرداختنی" یا "اسناد پرداختنی" تعریف نشده است.');

                $this->engine->recordJournalEntry(
                    $cheque, $cheque->amount, $debitCatId, $fundCatId, $fundAccountId,
                    "عودت چک پرداختی شماره {$cheque->cheque_number} و پرداخت وجه نقد"
                );
            }

            return $cheque;
        });
    }
}
