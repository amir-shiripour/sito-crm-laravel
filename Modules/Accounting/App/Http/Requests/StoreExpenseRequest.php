<?php

namespace Modules\Accounting\App\Http\Requests;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\App\Helpers\AccountingWalletHelper;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\Entities\Cheque;
use Modules\Clients\Entities\Client;
use Modules\Wallet\App\Services\WalletService;
use Morilog\Jalali\Jalalian;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $documentDate = null;
        if ($this->document_date) {
            try {
                $datePart = explode(' ', $this->document_date)[0];
                $documentDate = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->toDateString();
            } catch (Exception $e) {
                $documentDate = $this->document_date;
            }
        }

        $chequeIds = $this->cheque_ids ?? ($this->cheque_id ? [$this->cheque_id] : []);
        if (is_string($chequeIds)) {
            $chequeIds = array_filter(explode(',', $chequeIds));
        }
        $rawCheques = $this->cheques ?? [];
        $cleanedCheques = [];
        $totalChequeFees = 0;

        if (!empty($rawCheques) && is_array($rawCheques)) {
            foreach ($rawCheques as $key => $chq) {
                $cId = $chq['id'] ?? (is_numeric($key) && !isset($chq['id']) ? $key : null);
                if (!$cId && isset($chq['cheque_id'])) {
                    $cId = $chq['cheque_id'];
                }
                if ($cId) {
                    $cFee = isset($chq['fee']) ? (float)str_replace(',', '', (string)$chq['fee']) : 0;
                    $cleanedCheques[] = [
                        'id' => (int)$cId,
                        'fee' => max(0, $cFee),
                        'fee_bank_id' => !empty($chq['fee_bank_id']) ? (int)$chq['fee_bank_id'] : null,
                    ];
                    $totalChequeFees += max(0, $cFee);
                    if (!in_array($cId, $chequeIds)) {
                        $chequeIds[] = (int)$cId;
                    }
                }
            }
        } else {
            foreach ($chequeIds as $cId) {
                $cFee = 0;
                if (!empty($this->cheque_fees) && isset($this->cheque_fees[$cId])) {
                    $cFee = (float)str_replace(',', '', (string)$this->cheque_fees[$cId]);
                }
                $cleanedCheques[] = [
                    'id' => (int)$cId,
                    'fee' => max(0, $cFee),
                    'fee_bank_id' => null,
                ];
                $totalChequeFees += max(0, $cFee);
            }
        }

        $cleanedAmount = $this->amount ? (float)str_replace(',', '', (string)$this->amount) : null;

        // Process Bank Accounts
        $rawBankAccounts = $this->bank_accounts ?? [];
        $cleanedBankAccounts = [];
        $totalBankFees = 0;

        if (!empty($rawBankAccounts) && is_array($rawBankAccounts)) {
            foreach ($rawBankAccounts as $acc) {
                $bId = $acc['bank_id'] ?? ($acc['fund_account_id'] ?? null);
                if ($bId) {
                    $bAmount = isset($acc['amount']) ? (float)str_replace(',', '', (string)$acc['amount']) : 0;
                    $bFee = isset($acc['fee']) ? (float)str_replace(',', '', (string)$acc['fee']) : 0;
                    $cleanedBankAccounts[] = [
                        'bank_id' => (int)$bId,
                        'amount' => max(0, $bAmount),
                        'fee' => max(0, $bFee),
                        'client_id' => $acc['client_id'] ?? null,
                    ];
                    $totalBankFees += max(0, $bFee);
                }
            }
        } elseif (!empty($this->bank_id)) {
            // Legacy single bank account support
            $topFee = $this->fee ? (float)str_replace(',', '', (string)$this->fee) : 0;
            $cleanedBankAccounts[] = [
                'bank_id' => (int)$this->bank_id,
                'amount' => $cleanedAmount ?: 0,
                'fee' => max(0, $topFee),
                'client_id' => $this->client_id ?? null,
            ];
            $totalBankFees += max(0, $topFee);
        }

        $totalCombinedFee = $totalBankFees + $totalChequeFees;
        if ($totalCombinedFee == 0 && $this->fee) {
            $totalCombinedFee = (float)str_replace(',', '', (string)$this->fee);
        }

        $this->merge([
            'amount' => $cleanedAmount,
            'fee' => $totalCombinedFee,
            'document_date' => $documentDate,
            'cheque_ids' => array_values(array_unique($chequeIds)),
            'cheques' => $cleanedCheques,
            'bank_accounts' => $cleanedBankAccounts,
            'payment_type' => $this->payment_type ?? (!empty($chequeIds) ? 'cheque' : 'bank'),
        ]);
    }

    public function rules(): array
    {
        $clientsCategory = Category::where('title', 'مشتریان')->where('is_system', true)->first();
        $clientsCategoryId = $clientsCategory ? $clientsCategory->id : null;

        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'fee' => 'nullable|numeric|min:0',
            'document_date' => 'required|date',
            'category_id' => 'required|exists:accounting_categories,id',
            'payment_type' => 'nullable|in:bank,cheque',
            'bank_accounts' => 'nullable|array',
            'bank_accounts.*.bank_id' => 'required|exists:accounting_fund_accounts,id',
            'bank_accounts.*.amount' => 'nullable|numeric|min:0',
            'bank_accounts.*.fee' => 'nullable|numeric|min:0',
            'bank_accounts.*.client_id' => 'nullable',
            'bank_id' => 'nullable|exists:accounting_fund_accounts,id',
            'cheque_ids' => 'required_if:payment_type,cheque|nullable|array',
            'cheque_ids.*' => 'exists:accounting_cheques,id',
            'cheques' => 'nullable|array',
            'cheques.*.id' => 'exists:accounting_cheques,id',
            'cheques.*.fee' => 'nullable|numeric|min:0',
            'cheques.*.fee_bank_id' => 'nullable|exists:accounting_fund_accounts,id',
            'client_id' => [
                'nullable',
                Rule::requiredIf($this->category_id == $clientsCategoryId),
            ],
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $amount = (float)$this->amount;
            $paymentType = $this->payment_type ?? 'bank';
            $bankAccounts = $this->bank_accounts ?? [];
            $chequeIds = $this->cheque_ids ?? [];
            $chequesData = $this->cheques ?? [];

            $totalChequesAmount = 0;
            if ($paymentType === 'cheque') {
                if (empty($chequeIds)) {
                    $validator->errors()->add('cheque_ids', 'انتخاب حداقل یک چک جهت پرداخت الزامی است.');
                    return;
                }

                $cheques = Cheque::whereIn('id', $chequeIds)->get();
                $totalChequesAmount = (float)$cheques->sum('amount');

                if ($totalChequesAmount > $amount) {
                    $validator->errors()->add('cheque_ids', 'مجموع مبلغ چک‌های انتخاب شده (' . number_format($totalChequesAmount) . ') بیشتر از مبلغ کل هزینه (' . number_format($amount) . ') است.');
                }

                $remainingExpenseAmount = max(0, $amount - $totalChequesAmount);
                $totalChequeFees = collect($chequesData)->sum('fee');
                $totalBankAccountsAmount = collect($bankAccounts)->sum('amount');

                if ($remainingExpenseAmount > 0) {
                    if (empty($bankAccounts)) {
                        $validator->errors()->add('bank_accounts', 'مجموع چک‌ها کمتر از مبلغ هزینه است. افزودن حداقل یک حساب خزانه‌داری جهت پرداخت مانده (' . number_format($remainingExpenseAmount) . ') الزامی است.');
                    } elseif (abs($totalBankAccountsAmount - $remainingExpenseAmount) > 1) {
                        $validator->errors()->add('bank_accounts', 'مجموع مبالغ تخصیص‌یافته به حساب‌های خزانه‌داری (' . number_format($totalBankAccountsAmount) . ') باید دقیقاً برابر با مانده هزینه (' . number_format($remainingExpenseAmount) . ') باشد.');
                    }
                }

                if ($remainingExpenseAmount == 0 && ($totalChequeFees > 0 || collect($bankAccounts)->sum('fee') > 0)) {
                    if (empty($bankAccounts)) {
                        $validator->errors()->add('bank_accounts', 'به دلیل وجود کارمزد، انتخاب حداقل یک حساب خزانه‌داری جهت کسر کارمزد الزامی است.');
                    }
                }
            } else {
                // Bank mode
                if (empty($bankAccounts)) {
                    $validator->errors()->add('bank_accounts', 'انتخاب حداقل یک حساب خزانه‌داری جهت پرداخت هزینه الزامی است.');
                    return;
                }

                $totalBankAccountsAmount = collect($bankAccounts)->sum('amount');
                if (abs($totalBankAccountsAmount - $amount) > 1) {
                    $validator->errors()->add('bank_accounts', 'مجموع مبالغ تخصیص‌یافته به حساب‌های خزانه‌داری (' . number_format($totalBankAccountsAmount) . ') باید دقیقاً برابر با مبلغ کل هزینه (' . number_format($amount) . ') باشد.');
                }
            }

            // Validate wallet accounts balance
            foreach ($bankAccounts as $index => $acc) {
                $fundAccountId = $acc['bank_id'] ?? null;
                if (!$fundAccountId) continue;

                $fundAccount = FundAccount::find($fundAccountId);
                if ($fundAccount && $fundAccount->isWalletAccount()) {
                    $rowClientId = $acc['client_id'] ?? $this->client_id;
                    if (empty($rowClientId)) {
                        $validator->errors()->add("bank_accounts.{$index}.client_id", "برای ردیف حساب «{$fundAccount->name}»، انتخاب مشتری جهت کسر از کیف پول الزامی است.");
                    } else {
                        $documentable = null;
                        $rawClientId = (string)$rowClientId;
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

                        if ($documentable && AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                            $requiredAmount = (float)($acc['amount'] ?? 0) + (float)($acc['fee'] ?? 0);
                            $walletService = app(WalletService::class);
                            $wallet = $walletService->getOrCreateWallet($documentable);
                            if ((float)$wallet->balance < $requiredAmount) {
                                $docName = $documentable->name ?? ($documentable->full_name ?? 'مشتری');
                                $validator->errors()->add("bank_accounts.{$index}.amount", "موجودی کیف پول «{$docName}» (" . number_format($wallet->balance) . " ریال) کمتر از مبلغ مورد نیاز این ردیف (" . number_format($requiredAmount) . " ریال) می‌باشد.");
                            }
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'description.required' => 'وارد کردن شرح هزینه الزامی است.',
            'amount.required' => 'وارد کردن مبلغ الزامی است.',
            'amount.numeric' => 'مبلغ باید یک عدد معتبر باشد.',
            'document_date.required' => 'وارد کردن تاریخ الزامی است.',
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'bank_accounts.required' => 'انتخاب حداقل یک حساب پرداختی الزامی است.',
            'cheque_ids.required_if' => 'انتخاب حداقل یک چک جهت پرداخت الزامی است.',
            'cheque_ids.array' => 'فرمت چک‌های انتخاب شده معتبر نیست.',
            'client_id.required' => 'برای دسته‌بندی "مشتریان"، انتخاب مشتری الزامی است.',
        ];
    }
}
