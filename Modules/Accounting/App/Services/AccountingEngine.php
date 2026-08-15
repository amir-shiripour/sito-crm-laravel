<?php

namespace Modules\Accounting\App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\App\Helpers\AccountingWalletHelper;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\App\Models\Category;
use Illuminate\Support\Str;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Settings\Entities\Setting;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Models\WalletTransaction;

class AccountingEngine
{

    public function recordJournalEntry(
        ?Model $documentable,
        $amount,
        $debitCategoryId,
        $creditCategoryId,
        $fundAccountId = null,
        $description = '',
        $documentDate = null
    ) {
        if ($amount <= 0) {
            throw new Exception("مبلغ تراکنش باید بزرگتر از صفر باشد.");
        }

        if ($debitCategoryId == $creditCategoryId) {
            throw new Exception("سرفصل بدهکار و بستانکار نمی‌توانند یکسان باشند.");
        }

        return DB::transaction(function () use ($documentable, $amount, $debitCategoryId, $creditCategoryId, $fundAccountId, $description, $documentDate) {

            $debitFundAccountId = null;
            $creditFundAccountId = null;

            if ($fundAccountId) {
                $fundAccount = FundAccount::find($fundAccountId);
                if ($fundAccount) {
                    if ($debitCategoryId == $fundAccount->category_id) {
                        $debitFundAccountId = $fundAccountId;
                    }
                    if ($creditCategoryId == $fundAccount->category_id) {
                        $creditFundAccountId = $fundAccountId;
                    }
                }
            }

            if ($creditFundAccountId) {
                $this->checkNegativeBalance($creditFundAccountId, $amount);
            }

            $document = Document::create([
                'document_number' => $this->generateDocumentNumber(),
                'document_date' => $documentDate ?: now(),
                'description' => $description,
                'documentable_id' => $documentable ? $documentable->id : null,
                'documentable_type' => $documentable ? get_class($documentable) : null,
            ]);

            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $debitCategoryId,
                'fund_account_id' => $debitFundAccountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $document->document_date,
            ]);

            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $creditCategoryId,
                'fund_account_id' => $creditFundAccountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
                'transaction_date' => $document->document_date,
            ]);

            return $document;
        });
    }


    public function recordMultiLineDocument(
        array $rows,
        string $documentDescription = '',
        ?Model $documentable = null,
        ?string $documentDate = null
    ): Document {
        if (empty($rows)) {
            throw new Exception("سند حسابداری باید حداقل یک ردیف داشته باشد.");
        }

        return DB::transaction(function () use ($rows, $documentDescription, $documentable, $documentDate) {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($rows as $index => $row) {
                $debit = (float) ($row['debit'] ?? 0);
                $credit = (float) ($row['credit'] ?? 0);

                if ($debit < 0 || $credit < 0) throw new Exception("مبالغ ردیف " . ($index + 1) . " نمی‌توانند منفی باشند.");
                if ($debit > 0 && $credit > 0) throw new Exception("ردیف (" . ($index + 1) . ") نمی‌تواند هم بدهکار و هم بستانکار باشد.");
                if ($debit == 0 && $credit == 0) throw new Exception("مبلغ ردیف " . ($index + 1) . " باید مشخص شود.");
                if ($credit > 0 && !empty($row['fund_account_id'])) {
                    $fundAccount = FundAccount::find($row['fund_account_id']);
                    if ($fundAccount && $fundAccount->category_id == $row['category_id']) {
                        $this->checkNegativeBalance($row['fund_account_id'], $credit);
                    }
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if (abs($totalDebit - $totalCredit) > 0.001) {
                throw new Exception("جمع بدهکار و بستانکار سند برابر نیستند.");
            }

            $document = Document::create([
                'document_number' => $this->generateDocumentNumber(),
                'document_date' => $documentDate ?: now(),
                'description' => $documentDescription,
                'documentable_id' => $documentable ? $documentable->id : null,
                'documentable_type' => $documentable ? get_class($documentable) : null,
            ]);

            foreach ($rows as $row) {
                Transaction::create([
                    'document_id' => $document->id,
                    'category_id' => $row['category_id'],
                    'fund_account_id' => $row['fund_account_id'] ?? null,
                    'debit' => (float) ($row['debit'] ?? 0),
                    'credit' => (float) ($row['credit'] ?? 0),
                    'description' => $row['description'] ?? '',
                    'transaction_date' => $document->document_date,
                ]);
            }

            return $document;
        });
    }

    public function recordTransfer($amount, $fromFundAccountId, $toFundAccountId, $description = 'انتقال وجه بین حساب‌ها')
    {
        $assetCategory = Category::where('title', 'موجودی حساب‌های بانکی')->where('type', 'asset')->where('is_system', true)->first();

        if (!$assetCategory) throw new Exception("سرفصل سیستمی 'موجودی حساب‌های بانکی' یافت نشد.");
        if ($amount <= 0) throw new Exception("مبلغ انتقال باید بزرگتر از صفر باشد.");

        $amountInRial = CurrencyService::convertToBaseRial($amount);
        $this->checkNegativeBalance($fromFundAccountId, $amountInRial);

        return DB::transaction(function () use ($amountInRial, $fromFundAccountId, $toFundAccountId, $description, $assetCategory) {
            $document = Document::create([
                'document_number' => $this->generateDocumentNumber(),
                'document_date' => now(),
                'description' => $description,
            ]);

            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $assetCategory->id,
                'fund_account_id' => $toFundAccountId,
                'debit' => $amountInRial,
                'credit' => 0,
                'description' => $description,
                'transaction_date' => $document->document_date,
            ]);

            Transaction::create([
                'document_id' => $document->id,
                'category_id' => $assetCategory->id,
                'fund_account_id' => $fromFundAccountId,
                'debit' => 0,
                'credit' => $amountInRial,
                'description' => $description,
                'transaction_date' => $document->document_date,
            ]);

            return $document;
        });
    }


    private function generateDocumentNumber()
    {
        $prefix = 'DOC-' . date('Ymd') . '-';
        $random = strtoupper(Str::random(6));

        while (Document::where('document_number', $prefix . $random)->exists()) {
            $random = strtoupper(Str::random(6));
        }

        return $prefix . $random;
    }


    private function checkNegativeBalance($fundAccountId, $amountToDeduct)
    {
        if (!$fundAccountId) {
            return;
        }

        $allowNegative = AccountingSetting::get('banking.allow_negative_balance', false);

        if ($allowNegative) {
            return;
        }

        $currentBalance = Transaction::where('fund_account_id', $fundAccountId)
            ->sum(DB::raw('debit - credit'));

        if (($currentBalance - $amountToDeduct) < 0) {
            throw new Exception('موجودی حساب کافی نیست. ثبت سند باعث منفی شدن موجودی می‌شود.');
        }
    }


    public function recordFromServiceInvoice(Model $invoice): Document
    {
        $exists = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($invoice))
            ->where('sourceable_id', $invoice->id)
            ->where('module', 'services')
            ->whereIn('event_type', ['invoice_issued', 'invoice_paid', 'invoice_created'])
            ->first();

        if ($exists) {
            $doc = Document::find($exists->document_id);
            if ($doc) {
                return $doc;
            }
        }

        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $incomeCatId = AccountingSetting::get('defaults.sales_income_category_id');
        $taxCatId = AccountingSetting::get('defaults.sales_tax_category_id');

        if (!$receivableCatId || !$incomeCatId) {
            throw new Exception('سرفصل‌های پیش‌فرض برای درآمد یا حساب‌های دریافتنی در تنظیمات حسابداری مشخص نشده است.');
        }

        $sourceCurrency = $invoice->currency ?? (\Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'toman');
        $totalAmount = CurrencyService::amountInRial((float) ($invoice->total ?? 0), $sourceCurrency);
        $taxAmount = CurrencyService::amountInRial((float) ($invoice->tax_amount ?? 0), $sourceCurrency);
        $taxableAmount = $totalAmount - $taxAmount;

        $rows = [];
        $rows[] = [
            'category_id' => $receivableCatId,
            'debit' => $totalAmount,
            'credit' => 0,
            'description' => "بدهی مشتری بابت فاکتور خدمات #{$invoice->invoice_number}",
        ];
        $defaultIncomeCatId = $incomeCatId;
        $incomeBreakdown = [];

        if (method_exists($invoice, 'items')) {
            $invoice->loadMissing(['items.service', 'service']);
        }

        $items = $invoice->items;
        if ($items && $items->count() > 0) {
            $sumItemsTotal = (float) $items->sum('total');
            $allocatedTaxable = 0;
            $itemsCount = $items->count();

            foreach ($items as $idx => $item) {
                $itemCatId = $item->service?->accounting_category_id ?? $invoice->service?->accounting_category_id ?? $defaultIncomeCatId;

                if ($idx === $itemsCount - 1) {
                    $itemNet = $taxableAmount - $allocatedTaxable;
                } else {
                    $itemRatio = $sumItemsTotal > 0 ? ((float)$item->total / $sumItemsTotal) : (1 / $itemsCount);
                    $itemNet = round($taxableAmount * $itemRatio);
                    $allocatedTaxable += $itemNet;
                }

                $incomeBreakdown[$itemCatId] = ($incomeBreakdown[$itemCatId] ?? 0) + $itemNet;
            }
        } else {
            $catId = $invoice->service?->accounting_category_id ?? $defaultIncomeCatId;
            $incomeBreakdown[$catId] = $taxableAmount;
        }

        foreach ($incomeBreakdown as $catId => $amount) {
            if ($amount <= 0) continue;
            $categoryTitle = Category::find($catId)?->title ?? 'خدمات';
            $rows[] = [
                'category_id' => $catId,
                'debit' => 0,
                'credit' => $amount,
                'description' => "درآمد حاصل از ({$categoryTitle}) فاکتور #{$invoice->invoice_number}",
            ];
        }

        if ($taxAmount > 0) {
            if ($taxCatId) {
                $rows[] = [
                    'category_id' => $taxCatId,
                    'debit' => 0,
                    'credit' => $taxAmount,
                    'description' => "مالیات بر ارزش افزوده فاکتور #{$invoice->invoice_number}",
                ];
            }
        }

        $docDate = $invoice->issue_date
            ? ($invoice->issue_date instanceof \DateTimeInterface ? $invoice->issue_date->format('Y-m-d') : date('Y-m-d', strtotime($invoice->issue_date)))
            : date('Y-m-d');

        $document = $this->recordMultiLineDocument($rows, "سند خودکار فاکتور خدمات #{$invoice->invoice_number}", $invoice, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($invoice),
            'sourceable_id' => $invoice->id,
            'module' => 'services',
            'event_type' => 'invoice_paid',
            'snapshot' => json_encode(['invoice_number' => $invoice->invoice_number, 'total' => $totalAmount]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }


    public function recordBookingPayment(Model $payment, ?Model $appointment = null): Document
    {
        $cashFundId = AccountingSetting::get('defaults.cash_fund_id');
        $incomeCatId = AccountingSetting::get('defaults.sales_income_category_id');

        if (!$incomeCatId) {
            throw new Exception('سرفصل پیش‌فرض درآمد خدمات در تنظیمات حسابداری مشخص نشده است.');
        }

        $amount = CurrencyService::amountInRial((float) ($payment->amount ?? 0));
        $appointmentId = $appointment ? $appointment->id : ($payment->appointment_id ?? '---');

        $rows = [];
        $rows[] = [
            'category_id' => $incomeCatId,
            'debit' => $amount,
            'credit' => 0,
            'fund_account_id' => $cashFundId,
            'description' => "دریافت بابت نوبت‌دهی #{$appointmentId}",
        ];
        $rows[] = [
            'category_id' => $incomeCatId,
            'debit' => 0,
            'credit' => $amount,
            'description' => "درآمد رزرو نوبت #{$appointmentId}",
        ];

        $docDate = $payment->paid_at ? date('Y-m-d', strtotime($payment->paid_at)) : date('Y-m-d');
        $document = $this->recordMultiLineDocument($rows, "سند خودکار پرداخت نوبت‌دهی #{$appointmentId}", $payment, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($payment),
            'sourceable_id' => $payment->id,
            'module' => 'booking',
            'event_type' => 'payment_confirmed',
            'snapshot' => json_encode(['appointment_id' => $appointmentId, 'amount' => $amount]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }


    public function recordMarketOrder(Model $order): Document
    {
        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $incomeCatId = AccountingSetting::get('defaults.sales_income_category_id');

        if (!$incomeCatId) {
            throw new Exception('سرفصل پیش‌فرض درآمد در تنظیمات حسابداری مشخص نشده است.');
        }

        $sourceCurrency = $order->currency ?? 'toman';
        $total = CurrencyService::amountInRial((float) ($order->grand_total ?? 0), $sourceCurrency);
        $tax = CurrencyService::amountInRial((float) ($order->total_tax ?? 0), $sourceCurrency);
        $netSales = $total - $tax;

        $rows = [];
        $rows[] = [
            'category_id' => $receivableCatId ?: $incomeCatId,
            'debit' => $total,
            'credit' => 0,
            'description' => "دریافت سفارش فروشگاه #{$order->id}",
        ];
        $rows[] = [
            'category_id' => $incomeCatId,
            'debit' => 0,
            'credit' => $netSales,
            'description' => "درآمد فروش کالا سفارش #{$order->id}",
        ];

        if ($tax > 0) {
            $taxCatId = AccountingSetting::get('defaults.sales_tax_category_id');
            if ($taxCatId) {
                $rows[] = [
                    'category_id' => $taxCatId,
                    'debit' => 0,
                    'credit' => $tax,
                    'description' => "مالیات ارزش افزوده سفارش #{$order->id}",
                ];
            }
        }

        $docDate = $order->paid_at ? date('Y-m-d', strtotime($order->paid_at)) : date('Y-m-d');
        $document = $this->recordMultiLineDocument($rows, "سند خودکار سفارش فروشگاه #{$order->id}", $order, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($order),
            'sourceable_id' => $order->id,
            'module' => 'market',
            'event_type' => 'order_paid',
            'snapshot' => json_encode(['order_id' => $order->id, 'grand_total' => $total]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }

    public function recordServicePayment(Model $payment): ?Document
    {
        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $cashFundId = AccountingSetting::get('defaults.cash_fund_id');
        $bankFundId = AccountingSetting::get('defaults.bank_fund_id');

        if (!$receivableCatId) {
            throw new Exception('سرفصل پیش‌فرض برای حساب‌های دریافتنی در تنظیمات حسابداری مشخص نشده است.');
        }

        $sourceCurrency = null;
        if ($payment->invoice) {
            $sourceCurrency = $payment->invoice->currency ?? null;
        }
        if (!$sourceCurrency && \Nwidart\Modules\Facades\Module::has('Services')) {
            $sourceCurrency = \Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'toman';
        }
        $rawAmount = (float) ($payment->amount ?? 0);
        $amount = CurrencyService::amountInRial($rawAmount, $sourceCurrency);
        $invoiceNumber = $payment->invoice ? $payment->invoice->invoice_number : '---';
        $method = $payment->method;

        if (Str::startsWith((string)$method, 'cheque-')) {
            return null;
        }

        $fundAccountId = $cashFundId;

        $method = $payment->method;

        if (Str::startsWith((string)$method, 'transfer-')) {
            $transferId = str_replace('transfer-', '', $method);
            $settingsStr = Setting::where('key', 'bank_transfer_accounts')->value('value');
            if ($settingsStr) {
                $accounts = json_decode($settingsStr, true);
                if (is_array($accounts)) {
                    foreach ($accounts as $acc) {
                        if (isset($acc['id']) && $acc['id'] === $transferId) {
                            if (!empty($acc['bank_id'])) {
                                $fundAccountId = $acc['bank_id'];
                            } elseif ($bankFundId) {
                                $fundAccountId = $bankFundId;
                            } else {
                                $cleanBankName = str_replace(['بانک ', 'حساب '], '', $acc['bank_name'] ?? '');
                                $matchedFund = FundAccount::where('type', 'bank')
                                    ->where(function($q) use ($acc, $cleanBankName) {
                                        if (!empty($acc['account_number'])) $q->orWhere('account_number', 'like', '%' . $acc['account_number'] . '%');
                                        if (!empty($acc['card_number'])) $q->orWhere('card_number', 'like', '%' . $acc['card_number'] . '%');
                                        if (!empty($cleanBankName)) $q->orWhere('name', 'like', '%' . $cleanBankName . '%');
                                    })->first();

                                if ($matchedFund) {
                                    $fundAccountId = $matchedFund->id;
                                } else {
                                    $defaultBankFund = FundAccount::where('type', 'bank')->where('status', true)->first();
                                    if ($defaultBankFund) {
                                        $fundAccountId = $defaultBankFund->id;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
        } elseif (Str::startsWith((string)$method, 'pos-')) {
            $posId = str_replace('pos-', '', $method);
            $settingsStr = Setting::where('key', 'pos_devices')->value('value');
            if ($settingsStr) {
                $devices = json_decode($settingsStr, true);
                if (is_array($devices)) {
                    foreach ($devices as $dev) {
                        if (isset($dev['id']) && $dev['id'] === $posId) {
                            if (!empty($dev['bank_id'])) {
                                $fundAccountId = $dev['bank_id'];
                            }
                            break;
                        }
                    }
                }
            }
        } elseif ($payment->gateway) {
            $gatewayName = '';
            if ($payment->gateway === 'zarinpal') $gatewayName = 'زرین‌پال';
            elseif ($payment->gateway === 'zibal') $gatewayName = 'زیبال';
            elseif ($payment->gateway === 'behpardakht') $gatewayName = 'به‌پرداخت';

            if ($gatewayName) {
                $gatewayFund = FundAccount::where('type', 'gateway')
                    ->where('name', 'like', '%' . $gatewayName . '%')
                    ->first();
                if ($gatewayFund) {
                    $fundAccountId = $gatewayFund->id;
                }
            }
        }

        $debitCategoryId = null;
        if ($fundAccountId) {
            $fund = FundAccount::find($fundAccountId);
            if ($fund) {
                $debitCategoryId = $fund->category_id;
            }
        }

        if (!$debitCategoryId) {
            if ($cashFundId) {
                $fund = FundAccount::find($cashFundId);
                if ($fund) {
                    $debitCategoryId = $fund->category_id;
                    $fundAccountId = $cashFundId;
                }
            }
        }

        if (!$debitCategoryId) {
            $assetCat = Category::where('title', 'موجودی حساب‌های بانکی')->where('type', 'asset')->first();
            if ($assetCat) {
                $debitCategoryId = $assetCat->id;
            } else {
                throw new Exception("سرفصل متناظر برای واریز وجه یافت نشد. لطفاً سرفصل‌های پیش‌فرض حسابداری را بررسی کنید.");
            }
        }

        $rows = [];
        $rows[] = [
            'category_id' => $debitCategoryId,
            'fund_account_id' => $fundAccountId,
            'debit' => $amount,
            'credit' => 0,
            'description' => "دریافت وجه بابت فاکتور خدمات #{$invoiceNumber}",
        ];

        $rows[] = [
            'category_id' => $receivableCatId,
            'fund_account_id' => null,
            'debit' => 0,
            'credit' => $amount,
            'description' => "تسویه بدهی فاکتور خدمات #{$invoiceNumber}",
        ];

        $docDate = $payment->paid_at ? date('Y-m-d', strtotime($payment->paid_at)) : date('Y-m-d');
        $document = $this->recordMultiLineDocument($rows, "سند خودکار دریافت وجه فاکتور خدمات #{$invoiceNumber}", $payment, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($payment),
            'sourceable_id' => $payment->id,
            'module' => 'services',
            'event_type' => 'payment_received',
            'snapshot' => json_encode(['invoice_number' => $invoiceNumber, 'amount' => $amount, 'method' => $method]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }


    public function cancelServicePayment(Model $payment): ?Document
    {
        $existingCancel = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($payment))
            ->where('sourceable_id', $payment->id)
            ->where('module', 'services')
            ->where('event_type', 'payment_cancelled')
            ->first();

        if ($existingCancel) {
            return Document::find($existingCancel->document_id);
        }

        $sourceDoc = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($payment))
            ->where('sourceable_id', $payment->id)
            ->where('module', 'services')
            ->where('event_type', 'payment_received')
            ->first();

        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $cashFundId = AccountingSetting::get('defaults.cash_fund_id');

        $fundAccountId = $cashFundId;
        $amount = 0;

        if ($sourceDoc) {
            $originalDoc = Document::with('transactions')->find($sourceDoc->document_id);
            if ($originalDoc) {
                $debitTx = $originalDoc->transactions->firstWhere('debit', '>', 0);
                if ($debitTx) {
                    $amount = (float) $debitTx->debit;
                    if ($debitTx->fund_account_id) {
                        $fundAccountId = $debitTx->fund_account_id;
                    }
                }
            }
        }

        if ($amount <= 0) {
            $sourceCurrency = null;
            if ($payment->invoice) {
                $sourceCurrency = $payment->invoice->currency ?? null;
            }
            if (!$sourceCurrency && \Nwidart\Modules\Facades\Module::has('Services')) {
                $sourceCurrency = \Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'toman';
            }
            $rawAmount = (float) ($payment->amount ?? 0);
            $amount = CurrencyService::amountInRial($rawAmount, $sourceCurrency);
        }

        if ($amount <= 0) {
            return null;
        }

        if (!$receivableCatId) {
            $receivableCat = Category::where('title', 'LIKE', '%حساب‌های دریافتنی%')->orWhere('account_code', '1103')->first();
            $receivableCatId = $receivableCat?->id;
        }

        if (!$receivableCatId) {
            throw new Exception('سرفصل پیش‌فرض برای حساب‌های دریافتنی در تنظیمات حسابداری مشخص نشده است.');
        }

        $invoiceNumber = $payment->invoice ? $payment->invoice->invoice_number : '---';

        $rows = [];
        $rows[] = [
            'category_id' => $receivableCatId,
            'fund_account_id' => null,
            'debit' => $amount,
            'credit' => 0,
            'description' => "اصلاح/برگشت بدهی فاکتور خدمات #{$invoiceNumber} بابت لغو دریافت",
        ];

        $fundAccount = $fundAccountId ? FundAccount::find($fundAccountId) : null;
        $fundCategoryId = $fundAccount?->category_id;
        if (!$fundCategoryId) {
            $assetCat = Category::where('title', 'موجودی حساب‌های بانکی')->where('type', 'asset')->first();
            $fundCategoryId = $assetCat?->id ?: $receivableCatId;
        }

        $rows[] = [
            'category_id' => $fundCategoryId,
            'fund_account_id' => $fundAccountId,
            'debit' => 0,
            'credit' => $amount,
            'description' => "برگشت/عودت وجه از خزانه‌داری بابت لغو دریافت فاکتور خدمات #{$invoiceNumber}",
        ];

        $docDate = date('Y-m-d');
        $document = $this->recordMultiLineDocument($rows, "سند برگشتی لغو دریافت وجه فاکتور خدمات #{$invoiceNumber}", $payment, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($payment),
            'sourceable_id' => $payment->id,
            'module' => 'services',
            'event_type' => 'payment_cancelled',
            'snapshot' => json_encode(['invoice_number' => $invoiceNumber, 'amount' => $amount]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (class_exists(\App\Services\ActivityLogger::class)) {
            $fundAccountName = $fundAccount?->name ?? 'خزانه‌داری';
            \App\Services\ActivityLogger::log(
                'service_payment_cancelled',
                "مبلغ " . number_format($amount) . " ریال بابت لغو پرداخت فاکتور '{$invoiceNumber}' از خزانه‌داری ({$fundAccountName}) برگشت داده شد.",
                $payment,
                [
                    'invoice_number' => $invoiceNumber,
                    'amount' => $amount,
                    'fund_account' => $fundAccountName
                ]
            );
        }

        return $document;
    }

    public function cancelServiceInvoice(Model $invoice): ?Document
    {
        $existingCancel = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($invoice))
            ->where('sourceable_id', $invoice->id)
            ->where('module', 'services')
            ->where('event_type', 'invoice_cancelled')
            ->first();

        if ($existingCancel) {
            return Document::find($existingCancel->document_id);
        }

        $sourceDoc = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($invoice))
            ->where('sourceable_id', $invoice->id)
            ->where('module', 'services')
            ->whereIn('event_type', ['invoice_issued', 'invoice_paid', 'invoice_created'])
            ->first();

        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $incomeCatId = AccountingSetting::get('defaults.sales_income_category_id');
        $taxCatId = AccountingSetting::get('defaults.sales_tax_category_id');

        if (!$receivableCatId || !$incomeCatId) {
            return null;
        }

        $sourceCurrency = $invoice->currency ?? (\Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'toman');
        $totalAmount = CurrencyService::amountInRial((float) ($invoice->total ?? 0), $sourceCurrency);
        $taxAmount = CurrencyService::amountInRial((float) ($invoice->tax_amount ?? 0), $sourceCurrency);
        $taxableAmount = $totalAmount - $taxAmount;

        if ($totalAmount <= 0) {
            return null;
        }

        $rows = [];
        $defaultIncomeCatId = $incomeCatId;
        $incomeBreakdown = [];

        if (method_exists($invoice, 'items')) {
            $invoice->loadMissing(['items.service', 'service']);
        }

        $items = $invoice->items;
        if ($items && $items->count() > 0) {
            $sumItemsTotal = (float) $items->sum('total');
            $allocatedTaxable = 0;
            $itemsCount = $items->count();

            foreach ($items as $idx => $item) {
                $itemCatId = $item->service?->accounting_category_id ?? $invoice->service?->accounting_category_id ?? $defaultIncomeCatId;

                if ($idx === $itemsCount - 1) {
                    $itemNet = $taxableAmount - $allocatedTaxable;
                } else {
                    $itemRatio = $sumItemsTotal > 0 ? ((float)$item->total / $sumItemsTotal) : (1 / $itemsCount);
                    $itemNet = round($taxableAmount * $itemRatio);
                    $allocatedTaxable += $itemNet;
                }

                $incomeBreakdown[$itemCatId] = ($incomeBreakdown[$itemCatId] ?? 0) + $itemNet;
            }
        } else {
            $catId = $invoice->service?->accounting_category_id ?? $defaultIncomeCatId;
            $incomeBreakdown[$catId] = $taxableAmount;
        }

        foreach ($incomeBreakdown as $catId => $amount) {
            if ($amount <= 0) continue;
            $categoryTitle = Category::find($catId)?->title ?? 'خدمات';
            $rows[] = [
                'category_id' => $catId,
                'debit' => $amount,
                'credit' => 0,
                'description' => "اصلاح/کاهش درآمد حاصل از ({$categoryTitle}) بابت لغو فاکتور #{$invoice->invoice_number}",
            ];
        }

        if ($taxAmount > 0 && $taxCatId) {
            $rows[] = [
                'category_id' => $taxCatId,
                'debit' => $taxAmount,
                'credit' => 0,
                'description' => "اصلاح/کاهش مالیات بر ارزش افزوده بابت لغو فاکتور #{$invoice->invoice_number}",
            ];
        }

        $rows[] = [
            'category_id' => $receivableCatId,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => "اصلاح بدهی مشتری بابت لغو فاکتور خدمات #{$invoice->invoice_number}",
        ];

        $docDate = date('Y-m-d');
        $document = $this->recordMultiLineDocument($rows, "سند برگشتی لغو فاکتور خدمات #{$invoice->invoice_number}", $invoice, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($invoice),
            'sourceable_id' => $invoice->id,
            'module' => 'services',
            'event_type' => 'invoice_cancelled',
            'snapshot' => json_encode(['invoice_number' => $invoice->invoice_number, 'total' => $totalAmount]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'service_invoice_cancelled',
                "سند حسابداری لغو فاکتور خدمات شماره '{$invoice->invoice_number}' به مبلغ " . number_format($totalAmount) . " ریال ثبت گردید.",
                $invoice,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'total' => $totalAmount
                ]
            );
        }

        return $document;
    }


    public function deleteDocumentForSource(Model $sourceable): bool
    {
        $sourceDoc = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($sourceable))
            ->where('sourceable_id', $sourceable->id)
            ->first();

        if ($sourceDoc) {
            $document = Document::find($sourceDoc->document_id);
            if ($document) {
                Transaction::where('document_id', $document->id)->delete();
                $document->delete();
            }
            DB::table('accounting_source_documents')->where('id', $sourceDoc->id)->delete();
            return true;
        }

        $documents = Document::where('documentable_type', get_class($sourceable))
            ->where('documentable_id', $sourceable->id)
            ->get();

        foreach ($documents as $doc) {
            Transaction::where('document_id', $doc->id)->delete();
            $doc->delete();
        }

        return true;
    }


    public function getOrCreateFundAccountForWallet(?Wallet $wallet = null): FundAccount
    {
        $walletCategory = Category::firstOrCreate(
            ['title' => 'کیف پول کاربران'],
            [
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
                'level' => 3,
                'account_code' => '1105'
            ]
        );

        $noteKey = 'wallet_aggregated_account';
        $accountName = 'کیف پول کاربران';

        $fundAccount = FundAccount::where('notes', 'like', "%{$noteKey}%")->first();

        if (!$fundAccount) {
            $fundAccount = FundAccount::create([
                'name' => $accountName,
                'type' => 'cash',
                'category_id' => $walletCategory->id,
                'currency' => 'IRR',
                'status' => true,
                'notes' => "کیف پول متناظر ماژول Wallet [{$noteKey}]",
            ]);
        } else {
            $fundAccount->update([
                'name' => $accountName,
                'status' => true,
            ]);
        }

        return $fundAccount;
    }


    public function syncWalletsToFundAccounts(): void
    {
        if (!AccountingWalletHelper::isWalletEnabled() || !class_exists(Wallet::class)) {
            return;
        }

        $mainFundAccount = $this->getOrCreateFundAccountForWallet();
        $oldIndividualAccounts = FundAccount::where('notes', 'like', '%wallet_id:%')->get();

        foreach ($oldIndividualAccounts as $oldAccount) {
            DB::table('accounting_transactions')
                ->where('fund_account_id', $oldAccount->id)
                ->update(['fund_account_id' => $mainFundAccount->id]);

            $oldAccount->forceDelete();
        }
    }


    public function syncWalletTransactions(): void
    {
        if (!AccountingWalletHelper::isWalletEnabled() || !class_exists(WalletTransaction::class)) {
            return;
        }

        $unsynced = WalletTransaction::whereNotIn('id', function ($q) {
            $q->select('sourceable_id')
                ->from('accounting_source_documents')
                ->where('sourceable_type', WalletTransaction::class);
        })->get();

        foreach ($unsynced as $walletTx) {
            $this->recordWalletTransaction($walletTx);
        }
    }

    public function recordWalletTransaction(Model $walletTx): ?Document
    {
        $exists = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($walletTx))
            ->where('sourceable_id', $walletTx->id)
            ->exists();

        if ($exists) {
            return null;
        }

        $walletCategory = Category::firstOrCreate(
            ['title' => 'کیف پول کاربران'],
            [
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
                'level' => 3,
                'account_code' => '1105'
            ]
        );

        $walletFundAccount = $this->getOrCreateFundAccountForWallet($walletTx->wallet);

        $typeValue = is_object($walletTx->type) && property_exists($walletTx->type, 'value')
            ? $walletTx->type->value
            : (string) $walletTx->type;

        $typeLabel = is_object($walletTx->type) && method_exists($walletTx->type, 'label')
            ? $walletTx->type->label()
            : $typeValue;

        $holderName = 'نامشخص';
        if ($walletTx->wallet && $walletTx->wallet->holder) {
            $holder = $walletTx->wallet->holder;
            $holderName = $holder->name ?? $holder->full_name ?? $holder->username ?? 'نامشخص';
        }

        $sourceCurrency = $walletTx->wallet ? ($walletTx->wallet->currency ?? null) : null;
        $rawAmount = (float) $walletTx->amount;
        $amount = CurrencyService::amountInRial($rawAmount, $sourceCurrency);
        $txDesc = $walletTx->description ?: $typeLabel;
        $description = "تراکنش کیف پول ({$typeLabel}) - کاربر: {$holderName} - {$txDesc}";

        $isPositive = in_array($typeValue, ['deposit', 'refund', 'commission', 'bonus']);

        $rows = [];
        if ($isPositive) {
            // واریز / ورودی به کیف پول (بدهکار شدن حساب خزانه کیف پول)
            $rows[] = [
                'category_id' => $walletCategory->id,
                'fund_account_id' => $walletFundAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ];
            $rows[] = [
                'category_id' => $walletCategory->id,
                'fund_account_id' => null,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ];
        } else {
            $rows[] = [
                'category_id' => $walletCategory->id,
                'fund_account_id' => $walletFundAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ];
            $rows[] = [
                'category_id' => $walletCategory->id,
                'fund_account_id' => null,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ];
        }

        $docDate = $walletTx->created_at ? $walletTx->created_at->format('Y-m-d H:i:s') : now();
        $document = $this->recordMultiLineDocument($rows, $description, $walletTx, $docDate);

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => get_class($walletTx),
            'sourceable_id' => $walletTx->id,
            'module' => 'wallet',
            'event_type' => 'wallet_transaction_created',
            'snapshot' => json_encode([
                'uuid' => $walletTx->uuid,
                'amount' => $amount,
                'type' => $typeValue,
                'holder' => $holderName,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $document;
    }
}
