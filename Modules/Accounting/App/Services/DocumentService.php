<?php

namespace Modules\Accounting\App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\Entities\Cheque;
use Modules\Clients\Entities\Client;
use Modules\Wallet\App\Models\WalletTransaction;
use Modules\Wallet\App\Services\WalletService;

class DocumentService
{
    public function createExpense(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            $amount = (float)$data['amount'];
            $fundAccountId = $data['fund_account_id'] ?? null;
            $chequeIds = $data['cheque_ids'] ?? (!empty($data['cheque_id']) ? [$data['cheque_id']] : []);
            $debitCategoryId = $data['category_id'];
            $documentDate = $data['document_date'];
            $description = $data['description'];

            $cheques = collect();
            $totalChequeAmount = 0;

            if (!empty($chequeIds)) {
                $cheques = Cheque::whereIn('id', $chequeIds)->get();
                $totalChequeAmount = (float)$cheques->sum('amount');
                if ($totalChequeAmount > $amount) {
                    throw new Exception('مجموع مبلغ چک‌های انتخاب شده بیشتر از مبلغ کل هزینه است و قابل ثبت نمی‌باشد.');
                }
            }

            $remainingAmount = $amount - $totalChequeAmount;
            $bankCreditCatId = null;

            if ($remainingAmount > 0) {
                if (!$fundAccountId) {
                    throw new Exception('مجموع مبلغ چک‌ها کمتر از کل هزینه است. لطفاً حساب خزانه‌داری را جهت پرداخت مانده انتخاب نمایید.');
                }
                $fundAccount = FundAccount::findOrFail($fundAccountId);
                if (!$fundAccount->category_id) {
                    throw new Exception('حساب خزانه‌داری انتخاب شده به سرفصل حسابداری متصل نیست.');
                }
                $bankCreditCatId = $fundAccount->category_id;
            }

            $documentable = null;
            if (!empty($data['client_id'])) {
                $rawClientId = (string)$data['client_id'];
                if (str_contains($rawClientId, ':')) {
                    list($cClass, $cId) = explode(':', $rawClientId, 2);
                    if (class_exists($cClass)) {
                        $documentable = $cClass::find($cId);
                    }
                }
                if (!$documentable && class_exists(Client::class)) {
                    $documentable = Client::find($rawClientId);
                }
                if (!$documentable && class_exists(User::class)) {
                    $documentable = User::find($rawClientId);
                }
            }

            $documentNumber = 'EXP-' . date('YmdHis') . '-' . rand(100, 999);
            $document = Document::create([
                'document_number' => $documentNumber,
                'document_date' => $documentDate,
                'description' => $description,
                'documentable_type' => $documentable ? get_class($documentable) : null,
                'documentable_id' => $documentable?->id,
                'status' => 'active',
                'cheque_id' => $cheques->first()?->id,
            ]);

            // 1. Debit Row (Expense Category)
            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $debitCategoryId,
                'fund_account_id' => null,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $documentDate,
            ]);

            // 2. Credit Rows for Cheques (if any)
            $chequeNumbers = [];
            foreach ($cheques as $cheque) {
                $chequeCreditCatId = null;
                if ($cheque->type === 'payable') {
                    $chequeCreditCatId = AccountingSetting::get('defaults.cheques_payable_category_id')
                        ?: Category::whereIn('title', ['اسناد پرداختی', 'اسناد پرداختنی'])->first()?->id;
                    if (!$chequeCreditCatId) throw new Exception('سرفصل پیش‌فرض "اسناد پرداختنی" یافت نشد.');
                } else {
                    $chequeCreditCatId = AccountingSetting::get('defaults.cheques_receivable_category_id')
                        ?: Category::whereIn('title', ['اسناد دریافتنی', 'اسناد مالی دریافتنی'])->first()?->id;
                    if (!$chequeCreditCatId) throw new Exception('سرفصل پیش‌فرض "اسناد دریافتنی" یافت نشد.');
                }

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $chequeCreditCatId,
                    'fund_account_id' => null,
                    'debit' => 0,
                    'credit' => (float)$cheque->amount,
                    'description' => $description . " (پرداخت با چک صیادی {$cheque->cheque_number})",
                    'transaction_date' => $documentDate,
                ]);

                $cheque->update(['status' => 'transferred']);
                $cheque->attachedDocuments()->syncWithoutDetaching([
                    $document->id => ['notes' => $description, 'created_at' => now(), 'updated_at' => now()]
                ]);

                $chequeNumbers[] = $cheque->cheque_number;
            }

            // 3. Credit Row for Bank/Treasury (if remainingAmount > 0)
            if ($remainingAmount > 0 && $bankCreditCatId) {
                $fundAccount = FundAccount::find($fundAccountId);
                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $bankCreditCatId,
                    'fund_account_id' => $fundAccountId,
                    'debit' => 0,
                    'credit' => $remainingAmount,
                    'description' => $description . ($cheques->isNotEmpty() ? " (پرداخت مانده از حساب {$fundAccount->name})" : ""),
                    'transaction_date' => $documentDate,
                ]);
            }

            $referenceNumber = !empty($data['reference_number'])
                ? $data['reference_number']
                : (!empty($chequeNumbers) ? ('چک ' . implode('، ', $chequeNumbers) . ($remainingAmount > 0 ? ' + خزانه‌داری' : '')) : null);

            if ($referenceNumber) {
                $document->update(['reference_number' => $referenceNumber]);
            }

            // 4. Wallet Transaction logic if payment account is Wallet
            if ($remainingAmount > 0 && $fundAccountId) {
                $fundAccount = FundAccount::find($fundAccountId);
                if ($fundAccount && $fundAccount->isWalletAccount() && $documentable && \Modules\Accounting\App\Helpers\AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                    $walletService = app(WalletService::class);
                    $walletTx = $walletService->withdraw(
                        holder: $documentable,
                        amount: $remainingAmount,
                        type: \Modules\Wallet\App\Enums\TransactionType::PAYMENT,
                        payable: $document,
                        description: "پرداخت هزینه #{$document->document_number} - {$description}",
                        meta: ['document_id' => $document->id, 'document_number' => $document->document_number]
                    );

                    DB::table('accounting_source_documents')->insert([
                        'document_id' => $document->id,
                        'sourceable_type' => get_class($walletTx),
                        'sourceable_id' => $walletTx->id,
                        'module' => 'wallet',
                        'event_type' => 'wallet_expense_payment',
                        'snapshot' => json_encode([
                            'uuid' => $walletTx->uuid,
                            'amount' => $remainingAmount,
                            'document_number' => $document->document_number,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $document;
        });
    }

    public function updateExpense(Document $document, array $data): Document
    {
        return DB::transaction(function () use ($document, $data) {
            $amount = (float)$data['amount'];
            $fundAccountId = $data['fund_account_id'] ?? null;
            $chequeIds = $data['cheque_ids'] ?? (!empty($data['cheque_id']) ? [$data['cheque_id']] : []);
            $debitCategoryId = $data['category_id'];
            $documentDate = $data['document_date'];
            $description = $data['description'];

            // Handle previous attached cheques
            $document->load('cheques');
            $existingChequeIds = $document->cheques->pluck('id')->toArray();
            if ($document->cheque_id && !in_array($document->cheque_id, $existingChequeIds)) {
                $existingChequeIds[] = $document->cheque_id;
            }

            $removedChequeIds = array_diff($existingChequeIds, $chequeIds);
            if (!empty($removedChequeIds)) {
                $removedCheques = Cheque::whereIn('id', $removedChequeIds)->get();
                foreach ($removedCheques as $oldCheque) {
                    if (in_array($oldCheque->status, ['transferred', 'issued'])) {
                        $oldCheque->update(['status' => 'pending']);
                    }
                    $document->cheques()->detach($oldCheque->id);
                }
            }

            $cheques = collect();
            $totalChequeAmount = 0;

            if (!empty($chequeIds)) {
                $cheques = Cheque::whereIn('id', $chequeIds)->get();
                $totalChequeAmount = (float)$cheques->sum('amount');
                if ($totalChequeAmount > $amount) {
                    throw new Exception('مجموع مبلغ چک‌های انتخاب شده بیشتر از مبلغ کل هزینه است.');
                }
            }

            $remainingAmount = $amount - $totalChequeAmount;
            $bankCreditCatId = null;

            if ($remainingAmount > 0) {
                if (!$fundAccountId) {
                    throw new Exception('مجموع مبلغ چک‌ها کمتر از کل هزینه است. لطفاً حساب خزانه‌داری را جهت پرداخت مانده انتخاب نمایید.');
                }
                $fundAccount = FundAccount::findOrFail($fundAccountId);
                if (!$fundAccount->category_id) {
                    throw new Exception('حساب خزانه‌داری انتخاب شده به سرفصل حسابداری متصل نیست.');
                }
                $bankCreditCatId = $fundAccount->category_id;
            }

            // Revert previous wallet transaction if any exists
            $existingWalletSource = DB::table('accounting_source_documents')
                ->where('document_id', $document->id)
                ->where('sourceable_type', WalletTransaction::class)
                ->first();

            if ($existingWalletSource && \Modules\Accounting\App\Helpers\AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                $prevWalletTx = WalletTransaction::find($existingWalletSource->sourceable_id);
                if ($prevWalletTx) {
                    $walletService = app(WalletService::class);
                    $walletService->refund($prevWalletTx, null, "اصلاح سند هزینه #{$document->document_number}");
                }
                DB::table('accounting_source_documents')->where('id', $existingWalletSource->id)->delete();
            }

            // Remove previous transactions
            $document->transactions()->delete();

            // 1. Re-create Debit Row
            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $debitCategoryId,
                'fund_account_id' => null,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $documentDate,
            ]);

            // 2. Re-create Credit Rows for Cheques
            $chequeNumbers = [];
            foreach ($cheques as $cheque) {
                $chequeCreditCatId = null;
                if ($cheque->type === 'payable') {
                    $chequeCreditCatId = AccountingSetting::get('defaults.cheques_payable_category_id')
                        ?: Category::whereIn('title', ['اسناد پرداختی', 'اسناد پرداختنی'])->first()?->id;
                    if (!$chequeCreditCatId) throw new Exception('سرفصل پیش‌فرض "اسناد پرداختنی" یافت نشد.');
                } else {
                    $chequeCreditCatId = AccountingSetting::get('defaults.cheques_receivable_category_id')
                        ?: Category::whereIn('title', ['اسناد دریافتنی', 'اسناد مالی دریافتنی'])->first()?->id;
                    if (!$chequeCreditCatId) throw new Exception('سرفصل پیش‌فرض "اسناد دریافتنی" یافت نشد.');
                }

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $chequeCreditCatId,
                    'fund_account_id' => null,
                    'debit' => 0,
                    'credit' => (float)$cheque->amount,
                    'description' => $description . " (پرداخت با چک صیادی {$cheque->cheque_number})",
                    'transaction_date' => $documentDate,
                ]);

                $cheque->update(['status' => 'transferred']);
                $cheque->attachedDocuments()->syncWithoutDetaching([
                    $document->id => ['notes' => $description, 'created_at' => now(), 'updated_at' => now()]
                ]);

                $chequeNumbers[] = $cheque->cheque_number;
            }

            // 3. Re-create Credit Row for Bank/Treasury
            if ($remainingAmount > 0 && $bankCreditCatId) {
                $fundAccount = FundAccount::find($fundAccountId);
                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $bankCreditCatId,
                    'fund_account_id' => $fundAccountId,
                    'debit' => 0,
                    'credit' => $remainingAmount,
                    'description' => $description . ($cheques->isNotEmpty() ? " (پرداخت مانده از حساب {$fundAccount->name})" : ""),
                    'transaction_date' => $documentDate,
                ]);
            }

            $referenceNumber = !empty($data['reference_number'])
                ? $data['reference_number']
                : (!empty($chequeNumbers) ? ('چک ' . implode('، ', $chequeNumbers) . ($remainingAmount > 0 ? ' + خزانه‌داری' : '')) : null);

            $documentable = null;
            if (!empty($data['client_id'])) {
                $rawClientId = (string)$data['client_id'];
                if (str_contains($rawClientId, ':')) {
                    list($cClass, $cId) = explode(':', $rawClientId, 2);
                    if (class_exists($cClass)) {
                        $documentable = $cClass::find($cId);
                    }
                }
                if (!$documentable && class_exists(Client::class)) {
                    $documentable = Client::find($rawClientId);
                }
                if (!$documentable && class_exists(User::class)) {
                    $documentable = User::find($rawClientId);
                }
            }

            $document->update([
                'description' => $description,
                'document_date' => $documentDate,
                'documentable_type' => $documentable ? get_class($documentable) : null,
                'documentable_id' => $documentable?->id,
                'reference_number' => $referenceNumber,
                'cheque_id' => $cheques->first()?->id,
            ]);

            if (array_key_exists('attachment', $data) && $data['attachment']) {
                $document->update(['attachment' => $data['attachment']]);
            }

            // Wallet Transaction logic if payment account is Wallet
            if ($remainingAmount > 0 && $fundAccountId) {
                $fundAccount = FundAccount::find($fundAccountId);
                if ($fundAccount && $fundAccount->isWalletAccount() && $documentable && \Modules\Accounting\App\Helpers\AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                    $walletService = app(WalletService::class);
                    $walletTx = $walletService->withdraw(
                        holder: $documentable,
                        amount: $remainingAmount,
                        type: \Modules\Wallet\App\Enums\TransactionType::PAYMENT,
                        payable: $document,
                        description: "پرداخت هزینه #{$document->document_number} - {$description}",
                        meta: ['document_id' => $document->id, 'document_number' => $document->document_number]
                    );

                    DB::table('accounting_source_documents')->insert([
                        'document_id' => $document->id,
                        'sourceable_type' => get_class($walletTx),
                        'sourceable_id' => $walletTx->id,
                        'module' => 'wallet',
                        'event_type' => 'wallet_expense_payment',
                        'snapshot' => json_encode([
                            'uuid' => $walletTx->uuid,
                            'amount' => $remainingAmount,
                            'document_number' => $document->document_number,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $document;
        });
    }

    public function store(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            $amount = (float)$data['amount'];
            $bankId = $data['bank_id'];

            $bank = Bank::where('id', $bankId)->lockForUpdate()->firstOrFail();

            if ($data['type'] === 'expense') {
                $allowNegative = (bool)AccountingSetting::getValue('banking.allow_negative_balance', false);
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

    public function update(Document $document, array $newData): Document
    {
        return DB::transaction(function () use ($document, $newData) {
            $oldAmount = (float)$document->amount;
            $newAmount = (float)$newData['amount'];
            $oldBankId = $document->bank_id;
            $newBankId = $newData['bank_id'];
            $type = $document->type;

            $allowNegative = (bool)AccountingSetting::getValue('banking.allow_negative_balance', false);

            if ($oldBankId) {
                $oldBank = Bank::where('id', $oldBankId)->lockForUpdate()->firstOrFail();
                if ($type === 'expense') {
                    $oldBank->balance += $oldAmount;
                } else { // income
                    if (!$allowNegative && $oldBank->balance < $oldAmount) {
                        throw new Exception('موجودی حساب قبلی برای بازگشت این تراکنش کافی نیست.');
                    }
                    $oldBank->balance -= $oldAmount;
                }
                $oldBank->save();
            }

            if ($newBankId) {
                if ($oldBankId === $newBankId) {
                    $newBank = $oldBank;
                } else {
                    $newBank = Bank::where('id', $newBankId)->lockForUpdate()->firstOrFail();
                }

                if ($type === 'expense') {
                    if (!$allowNegative && $newBank->balance < $newAmount) {
                        throw new Exception('موجودی حساب جدید برای ثبت این هزینه کافی نیست.');
                    }
                    $newBank->balance -= $newAmount;
                } else { // income
                    $newBank->balance += $newAmount;
                }
                $newBank->save();
            }

            $document->update($newData);

            return $document;
        });
    }

    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $document = Document::findOrFail($id);

            // Revert bank balance if a bank is associated
            if ($document->bank_id) {
                $bank = $document->bank()->lockForUpdate()->firstOrFail();
                $amount = (float)$document->amount;

                if ($document->type === 'expense' || $document->type === 'transfer_out') {
                    $bank->balance += $amount;
                } elseif ($document->type === 'income' || $document->type === 'transfer_in') {
                    $allowNegative = (bool)AccountingSetting::getValue('banking.allow_negative_balance', false);
                    if (!$allowNegative && $bank->balance < $amount) {
                        throw new Exception('موجودی حساب برای بازگشت این تراکنش کافی نیست.');
                    }
                    $bank->balance -= $amount;
                }
                $bank->save();
            }

            // If the document was for an endorsed cheque, revert the cheque status
            if ($document->payment_method === 'cheque_endorsed') {
                $cheque = Cheque::where('documentable_id', $document->id)
                    ->where('documentable_type', Document::class)
                    ->first();
                if ($cheque) {
                    $cheque->update(['status' => 'registered', 'documentable_id' => null, 'documentable_type' => null]);
                }
            }

            return $document->delete();
        });
    }
}
