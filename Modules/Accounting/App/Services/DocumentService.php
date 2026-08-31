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
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\WalletTransaction;
use Modules\Wallet\App\Services\WalletService;

class DocumentService
{
    public function createExpense(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            $amount = (float)$data['amount'];
            $debitCategoryId = $data['category_id'];
            $documentDate = $data['document_date'];
            $description = $data['description'];

            // 1. Process Cheques
            $chequeIds = $data['cheque_ids'] ?? (!empty($data['cheque_id']) ? [$data['cheque_id']] : []);
            $rawCheques = $data['cheques'] ?? [];
            $cheques = collect();
            $totalChequeAmount = 0;
            $chequeFeesMap = []; // cheque_id => ['fee' => float, 'fee_bank_id' => int|null]

            if (!empty($rawCheques)) {
                foreach ($rawCheques as $chq) {
                    $cId = $chq['id'] ?? null;
                    if ($cId) {
                        $chequeFeesMap[$cId] = [
                            'fee' => (float)($chq['fee'] ?? 0),
                            'fee_bank_id' => !empty($chq['fee_bank_id']) ? (int)$chq['fee_bank_id'] : null,
                        ];
                        if (!in_array($cId, $chequeIds)) {
                            $chequeIds[] = $cId;
                        }
                    }
                }
            }

            if (!empty($chequeIds)) {
                $cheques = Cheque::whereIn('id', $chequeIds)->get();
                $totalChequeAmount = (float)$cheques->sum('amount');
                if ($totalChequeAmount > $amount) {
                    throw new Exception('مجموع مبلغ چک‌های انتخاب شده بیشتر از مبلغ کل هزینه است و قابل ثبت نمی‌باشد.');
                }
            }

            // 2. Process Bank Accounts
            $rawBankAccounts = $data['bank_accounts'] ?? [];
            if (empty($rawBankAccounts) && (!empty($data['fund_account_id']) || !empty($data['bank_id']))) {
                $rawBankAccounts = [
                    [
                        'bank_id' => $data['fund_account_id'] ?? $data['bank_id'],
                        'amount' => max(0, $amount - $totalChequeAmount),
                        'fee' => $data['fee'] ?? 0,
                        'client_id' => $data['client_id'] ?? null,
                    ]
                ];
            }

            $totalBankFees = 0;
            foreach ($rawBankAccounts as $acc) {
                $totalBankFees += (float)($acc['fee'] ?? 0);
            }
            $totalChequeFees = 0;
            foreach ($chequeFeesMap as $cData) {
                $totalChequeFees += (float)($cData['fee'] ?? 0);
            }
            $totalFee = $totalBankFees + $totalChequeFees;

            $extraBankCreditForChequeFees = [];
            foreach ($chequeFeesMap as $cId => $cData) {
                $cFee = (float)($cData['fee'] ?? 0);
                if ($cFee > 0) {
                    $targetBankId = $cData['fee_bank_id'] ?? null;
                    if (!$targetBankId && !empty($rawBankAccounts)) {
                        $targetBankId = $rawBankAccounts[0]['bank_id'] ?? null;
                    }
                    if ($targetBankId) {
                        $extraBankCreditForChequeFees[$targetBankId] = ($extraBankCreditForChequeFees[$targetBankId] ?? 0) + $cFee;
                    }
                }
            }

            // Resolve documentable
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

            // 3. Debit Row (Main Expense Category)
            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $debitCategoryId,
                'fund_account_id' => null,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $documentDate,
            ]);

            // 4. Debit Row for Total Fee (if totalFee > 0)
            if ($totalFee > 0) {
                $feeCategoryId = AccountingSetting::get('defaults.bank_fee_category_id')
                    ?: Category::where('title', 'like', '%کارمزد%')->where('type', 'expense')->first()?->id
                        ?: $debitCategoryId;

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $feeCategoryId,
                    'fund_account_id' => null,
                    'debit' => $totalFee,
                    'credit' => 0,
                    'description' => "کارمزد بانکی و انتقال بابت " . $description,
                    'transaction_date' => $documentDate,
                ]);
            }

            // 5. Credit Rows for Cheques
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

                $chequeFeeInfo = $chequeFeesMap[$cheque->id]['fee'] ?? 0;
                $chqDesc = $description . " (پرداخت با چک صیادی {$cheque->cheque_number}" . ($chequeFeeInfo > 0 ? " — کارمزد: " . number_format($chequeFeeInfo) : "") . ")";

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $chequeCreditCatId,
                    'fund_account_id' => null,
                    'debit' => 0,
                    'credit' => (float)$cheque->amount,
                    'description' => $chqDesc,
                    'transaction_date' => $documentDate,
                ]);

                $cheque->update(['status' => 'transferred']);
                $cheque->attachedDocuments()->syncWithoutDetaching([
                    $document->id => ['notes' => $description, 'created_at' => now(), 'updated_at' => now()]
                ]);

                $chequeNumbers[] = $cheque->cheque_number;
            }

            // 6. Credit Rows for Multiple Bank Accounts
            $bankNames = [];
            foreach ($rawBankAccounts as $acc) {
                $fundAccountId = (int)($acc['bank_id'] ?? 0);
                if (!$fundAccountId) continue;

                $accAmount = (float)($acc['amount'] ?? 0);
                $accFee = (float)($acc['fee'] ?? 0);
                $accExtraChequeFee = (float)($extraBankCreditForChequeFees[$fundAccountId] ?? 0);
                $totalBankCredit = $accAmount + $accFee + $accExtraChequeFee;

                if ($totalBankCredit <= 0) continue;

                $fundAccount = FundAccount::findOrFail($fundAccountId);
                if (!$fundAccount->category_id) {
                    throw new Exception("حساب خزانه‌داری «{$fundAccount->name}» به سرفصل حسابداری متصل نیست.");
                }

                $creditDesc = $description;
                $feeDetailParts = [];
                if ($accFee > 0) {
                    $feeDetailParts[] = "کارمزد: " . number_format($accFee);
                }
                if ($accExtraChequeFee > 0) {
                    $feeDetailParts[] = "کارمزد چک: " . number_format($accExtraChequeFee);
                }

                if ($cheques->isNotEmpty()) {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name}" . (!empty($feeDetailParts) ? " + " . implode(' + ', $feeDetailParts) : "") . ")";
                } elseif (!empty($feeDetailParts)) {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name} — شامل " . implode(' + ', $feeDetailParts) . ")";
                } else {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name})";
                }

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $fundAccount->category_id,
                    'fund_account_id' => $fundAccount->id,
                    'debit' => 0,
                    'credit' => $totalBankCredit,
                    'description' => $creditDesc,
                    'transaction_date' => $documentDate,
                ]);

                $bankNames[] = $fundAccount->name;

                // Handle Wallet Deduction if account is Wallet
                if ($fundAccount->isWalletAccount() && AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                    $rowHolder = null;
                    if (!empty($acc['client_id'])) {
                        $rawRowClientId = (string)$acc['client_id'];
                        if (str_contains($rawRowClientId, ':')) {
                            list($cClass, $cId) = explode(':', $rawRowClientId, 2);
                            if (class_exists($cClass)) {
                                $rowHolder = $cClass::find($cId);
                            }
                        }
                        if (!$rowHolder && class_exists(Client::class)) {
                            $rowHolder = Client::find($rawRowClientId);
                        }
                        if (!$rowHolder && class_exists(User::class)) {
                            $rowHolder = User::find($rawRowClientId);
                        }
                    }
                    $rowHolder = $rowHolder ?: $documentable;

                    if ($rowHolder) {
                        $walletService = app(WalletService::class);
                        $walletTx = $walletService->withdraw(
                            holder: $rowHolder,
                            amount: $totalBankCredit,
                            type: TransactionType::PAYMENT,
                            payable: $document,
                            description: "پرداخت هزینه #{$document->document_number} - {$description}" . (!empty($feeDetailParts) ? " (شامل " . implode(' + ', $feeDetailParts) . ")" : ""),
                            meta: ['document_id' => $document->id, 'document_number' => $document->document_number, 'fund_account_id' => $fundAccount->id]
                        );

                        DB::table('accounting_source_documents')->insert([
                            'document_id' => $document->id,
                            'sourceable_type' => get_class($walletTx),
                            'sourceable_id' => $walletTx->id,
                            'module' => 'wallet',
                            'event_type' => 'wallet_expense_payment',
                            'snapshot' => json_encode([
                                'uuid' => $walletTx->uuid,
                                'amount' => $totalBankCredit,
                                'document_number' => $document->document_number,
                                'fund_account_id' => $fundAccount->id,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Process any cheque fees assigned to a bank account not present in rawBankAccounts
            $processedBankIds = array_filter(array_map(function ($acc) {
                return (int)($acc['bank_id'] ?? 0);
            }, $rawBankAccounts));

            foreach ($extraBankCreditForChequeFees as $fBankId => $cExtraFee) {
                if (!in_array($fBankId, $processedBankIds) && $cExtraFee > 0) {
                    $fundAccount = FundAccount::find($fBankId);
                    if ($fundAccount && $fundAccount->category_id) {
                        Transaction::create([
                            'document_id' => $document->id,
                            'category_id' => $fundAccount->category_id,
                            'fund_account_id' => $fundAccount->id,
                            'debit' => 0,
                            'credit' => $cExtraFee,
                            'description' => $description . " (کسر کارمزد انتقال چک از حساب {$fundAccount->name}: " . number_format($cExtraFee) . ")",
                            'transaction_date' => $documentDate,
                        ]);
                        $bankNames[] = $fundAccount->name;
                    }
                }
            }

            // Reference Number
            $referenceNumber = !empty($data['reference_number'])
                ? $data['reference_number']
                : (!empty($chequeNumbers)
                    ? ('چک ' . implode('، ', $chequeNumbers) . (!empty($bankNames) ? ' + ' . implode('، ', $bankNames) : ''))
                    : null);

            if ($referenceNumber) {
                $document->update(['reference_number' => $referenceNumber]);
            }

            return $document;
        });
    }

    public function updateExpense(Document $document, array $data): Document
    {
        return DB::transaction(function () use ($document, $data) {
            $amount = (float)$data['amount'];
            $debitCategoryId = $data['category_id'];
            $documentDate = $data['document_date'];
            $description = $data['description'];

            // 1. Process Cheques
            $chequeIds = $data['cheque_ids'] ?? (!empty($data['cheque_id']) ? [$data['cheque_id']] : []);
            $rawCheques = $data['cheques'] ?? [];
            $chequeFeesMap = [];

            if (!empty($rawCheques)) {
                foreach ($rawCheques as $chq) {
                    $cId = $chq['id'] ?? null;
                    if ($cId) {
                        $chequeFeesMap[$cId] = [
                            'fee' => (float)($chq['fee'] ?? 0),
                            'fee_bank_id' => !empty($chq['fee_bank_id']) ? (int)$chq['fee_bank_id'] : null,
                        ];
                        if (!in_array($cId, $chequeIds)) {
                            $chequeIds[] = $cId;
                        }
                    }
                }
            }

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

            // 2. Process Bank Accounts
            $rawBankAccounts = $data['bank_accounts'] ?? [];
            if (empty($rawBankAccounts) && (!empty($data['fund_account_id']) || !empty($data['bank_id']))) {
                $rawBankAccounts = [
                    [
                        'bank_id' => $data['fund_account_id'] ?? $data['bank_id'],
                        'amount' => max(0, $amount - $totalChequeAmount),
                        'fee' => $data['fee'] ?? 0,
                        'client_id' => $data['client_id'] ?? null,
                    ]
                ];
            }

            // Calculate total fees
            $totalBankFees = 0;
            foreach ($rawBankAccounts as $acc) {
                $totalBankFees += (float)($acc['fee'] ?? 0);
            }
            $totalChequeFees = 0;
            foreach ($chequeFeesMap as $cData) {
                $totalChequeFees += (float)($cData['fee'] ?? 0);
            }
            $totalFee = $totalBankFees + $totalChequeFees;

            // Map cheque fees to bank accounts
            $extraBankCreditForChequeFees = [];
            foreach ($chequeFeesMap as $cId => $cData) {
                $cFee = (float)($cData['fee'] ?? 0);
                if ($cFee > 0) {
                    $targetBankId = $cData['fee_bank_id'] ?? null;
                    if (!$targetBankId && !empty($rawBankAccounts)) {
                        $targetBankId = $rawBankAccounts[0]['bank_id'] ?? null;
                    }
                    if ($targetBankId) {
                        $extraBankCreditForChequeFees[$targetBankId] = ($extraBankCreditForChequeFees[$targetBankId] ?? 0) + $cFee;
                    }
                }
            }

            // Revert all previous wallet transactions for this document
            $existingWalletSources = DB::table('accounting_source_documents')
                ->where('document_id', $document->id)
                ->where(function ($q) {
                    $q->where('sourceable_type', WalletTransaction::class)
                        ->orWhere('sourceable_type', 'Modules\Wallet\App\Models\WalletTransaction');
                })
                ->get();

            if ($existingWalletSources->isNotEmpty() && AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                $walletService = app(WalletService::class);
                foreach ($existingWalletSources as $src) {
                    $prevWalletTx = WalletTransaction::find($src->sourceable_id);
                    if ($prevWalletTx) {
                        $walletService->refund($prevWalletTx, null, "اصلاح سند هزینه #{$document->document_number}");
                    }
                }
                DB::table('accounting_source_documents')
                    ->whereIn('id', $existingWalletSources->pluck('id'))
                    ->delete();
            }

            // Remove previous transactions
            $document->transactions()->delete();

            // 3. Re-create Debit Row (Main Expense Category)
            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $debitCategoryId,
                'fund_account_id' => null,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $documentDate,
            ]);

            // 4. Re-create Debit Row for Total Fee (if totalFee > 0)
            if ($totalFee > 0) {
                $feeCategoryId = AccountingSetting::get('defaults.bank_fee_category_id')
                    ?: Category::where('title', 'like', '%کارمزد%')->where('type', 'expense')->first()?->id
                        ?: $debitCategoryId;

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $feeCategoryId,
                    'fund_account_id' => null,
                    'debit' => $totalFee,
                    'credit' => 0,
                    'description' => "کارمزد بانکی و انتقال بابت " . $description,
                    'transaction_date' => $documentDate,
                ]);
            }

            // 5. Re-create Credit Rows for Cheques
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

                $chequeFeeInfo = $chequeFeesMap[$cheque->id]['fee'] ?? 0;
                $chqDesc = $description . " (پرداخت با چک صیادی {$cheque->cheque_number}" . ($chequeFeeInfo > 0 ? " — کارمزد: " . number_format($chequeFeeInfo) : "") . ")";

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $chequeCreditCatId,
                    'fund_account_id' => null,
                    'debit' => 0,
                    'credit' => (float)$cheque->amount,
                    'description' => $chqDesc,
                    'transaction_date' => $documentDate,
                ]);

                $cheque->update(['status' => 'transferred']);
                $cheque->attachedDocuments()->syncWithoutDetaching([
                    $document->id => ['notes' => $description, 'created_at' => now(), 'updated_at' => now()]
                ]);

                $chequeNumbers[] = $cheque->cheque_number;
            }

            // Resolve documentable
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

            // 6. Re-create Credit Rows for Multiple Bank Accounts
            $bankNames = [];
            foreach ($rawBankAccounts as $acc) {
                $fundAccountId = (int)($acc['bank_id'] ?? 0);
                if (!$fundAccountId) continue;

                $accAmount = (float)($acc['amount'] ?? 0);
                $accFee = (float)($acc['fee'] ?? 0);
                $accExtraChequeFee = (float)($extraBankCreditForChequeFees[$fundAccountId] ?? 0);
                $totalBankCredit = $accAmount + $accFee + $accExtraChequeFee;

                if ($totalBankCredit <= 0) continue;

                $fundAccount = FundAccount::findOrFail($fundAccountId);
                if (!$fundAccount->category_id) {
                    throw new Exception("حساب خزانه‌داری «{$fundAccount->name}» به سرفصل حسابداری متصل نیست.");
                }

                $creditDesc = $description;
                $feeDetailParts = [];
                if ($accFee > 0) {
                    $feeDetailParts[] = "کارمزد: " . number_format($accFee);
                }
                if ($accExtraChequeFee > 0) {
                    $feeDetailParts[] = "کارمزد چک: " . number_format($accExtraChequeFee);
                }

                if ($cheques->isNotEmpty()) {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name}" . (!empty($feeDetailParts) ? " + " . implode(' + ', $feeDetailParts) : "") . ")";
                } elseif (!empty($feeDetailParts)) {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name} — شامل " . implode(' + ', $feeDetailParts) . ")";
                } else {
                    $creditDesc .= " (پرداخت از حساب {$fundAccount->name})";
                }

                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $fundAccount->category_id,
                    'fund_account_id' => $fundAccount->id,
                    'debit' => 0,
                    'credit' => $totalBankCredit,
                    'description' => $creditDesc,
                    'transaction_date' => $documentDate,
                ]);

                $bankNames[] = $fundAccount->name;

                // Handle Wallet Deduction if account is Wallet
                if ($fundAccount->isWalletAccount() && AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                    $rowHolder = null;
                    if (!empty($acc['client_id'])) {
                        $rawRowClientId = (string)$acc['client_id'];
                        if (str_contains($rawRowClientId, ':')) {
                            list($cClass, $cId) = explode(':', $rawRowClientId, 2);
                            if (class_exists($cClass)) {
                                $rowHolder = $cClass::find($cId);
                            }
                        }
                        if (!$rowHolder && class_exists(Client::class)) {
                            $rowHolder = Client::find($rawRowClientId);
                        }
                        if (!$rowHolder && class_exists(User::class)) {
                            $rowHolder = User::find($rawRowClientId);
                        }
                    }
                    $rowHolder = $rowHolder ?: $documentable;

                    if ($rowHolder) {
                        $walletService = app(WalletService::class);
                        $walletTx = $walletService->withdraw(
                            holder: $rowHolder,
                            amount: $totalBankCredit,
                            type: TransactionType::PAYMENT,
                            payable: $document,
                            description: "پرداخت هزینه #{$document->document_number} - {$description}" . (!empty($feeDetailParts) ? " (شامل " . implode(' + ', $feeDetailParts) . ")" : ""),
                            meta: ['document_id' => $document->id, 'document_number' => $document->document_number, 'fund_account_id' => $fundAccount->id]
                        );

                        DB::table('accounting_source_documents')->insert([
                            'document_id' => $document->id,
                            'sourceable_type' => get_class($walletTx),
                            'sourceable_id' => $walletTx->id,
                            'module' => 'wallet',
                            'event_type' => 'wallet_expense_payment',
                            'snapshot' => json_encode([
                                'uuid' => $walletTx->uuid,
                                'amount' => $totalBankCredit,
                                'document_number' => $document->document_number,
                                'fund_account_id' => $fundAccount->id,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Process any cheque fees assigned to a bank account not present in rawBankAccounts
            $processedBankIds = array_filter(array_map(function ($acc) {
                return (int)($acc['bank_id'] ?? 0);
            }, $rawBankAccounts));

            foreach ($extraBankCreditForChequeFees as $fBankId => $cExtraFee) {
                if (!in_array($fBankId, $processedBankIds) && $cExtraFee > 0) {
                    $fundAccount = FundAccount::find($fBankId);
                    if ($fundAccount && $fundAccount->category_id) {
                        Transaction::create([
                            'document_id' => $document->id,
                            'category_id' => $fundAccount->category_id,
                            'fund_account_id' => $fundAccount->id,
                            'debit' => 0,
                            'credit' => $cExtraFee,
                            'description' => $description . " (کسر کارمزد انتقال چک از حساب {$fundAccount->name}: " . number_format($cExtraFee) . ")",
                            'transaction_date' => $documentDate,
                        ]);
                        $bankNames[] = $fundAccount->name;
                    }
                }
            }

            // Reference Number
            $referenceNumber = !empty($data['reference_number'])
                ? $data['reference_number']
                : (!empty($chequeNumbers)
                    ? ('چک ' . implode('، ', $chequeNumbers) . (!empty($bankNames) ? ' + ' . implode('، ', $bankNames) : ''))
                    : null);

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
