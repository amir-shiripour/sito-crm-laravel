<?php

namespace Modules\Accounting\App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\App\Helpers\AccountingWalletHelper;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
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
            } catch (\Exception $e) {
                $documentDate = $this->document_date;
            }
        }

        $chequeIds = $this->cheque_ids ?? ($this->cheque_id ? [$this->cheque_id] : []);
        if (is_string($chequeIds)) {
            $chequeIds = array_filter(explode(',', $chequeIds));
        }

        $this->merge([
            'amount' => $this->amount ? str_replace(',', '', $this->amount) : null,
            'document_date' => $documentDate,
            'cheque_ids' => $chequeIds,
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
            'document_date' => 'required|date',
            'category_id' => 'required|exists:accounting_categories,id',
            'payment_type' => 'nullable|in:bank,cheque',
            'bank_id' => 'required_if:payment_type,bank|nullable|exists:accounting_fund_accounts,id',
            'cheque_ids' => 'required_if:payment_type,cheque|nullable|array',
            'cheque_ids.*' => 'exists:accounting_cheques,id',
            'client_id' => [
                'nullable',
                Rule::requiredIf($this->category_id == $clientsCategoryId),
            ],
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $amount = (float)$this->amount;
            $bankId = $this->bank_id;
            $totalChequeAmount = 0;

            if ($this->payment_type === 'cheque' && !empty($this->cheque_ids)) {
                $cheques = \Modules\Accounting\Entities\Cheque::whereIn('id', $this->cheque_ids)->get();
                $totalChequeAmount = (float)$cheques->sum('amount');

                if ($totalChequeAmount > $amount) {
                    $validator->errors()->add('cheque_ids', 'مجموع مبلغ چک‌های انتخاب شده (' . number_format($totalChequeAmount) . ') بیشتر از مبلغ کل هزینه است.');
                } elseif ($totalChequeAmount < $amount && !$this->bank_id) {
                    $remaining = $amount - $totalChequeAmount;
                    $validator->errors()->add('bank_id', 'مجموع مبلغ چک‌ها (' . number_format($totalChequeAmount) . ') کمتر از کل مبلغ هزینه است. انتخاب حساب خزانه‌داری جهت پرداخت مانده (' . number_format($remaining) . ') الزامی است.');
                }
            }

            if ($bankId) {
                $fundAccount = FundAccount::find($bankId);
                if ($fundAccount && $fundAccount->isWalletAccount()) {
                    if (empty($this->client_id)) {
                        $validator->errors()->add('client_id', 'در صورت پرداخت از حساب «کیف پول کاربران»، انتخاب مشتری الزامی است.');
                    } else {
                        $documentable = null;
                        $rawClientId = (string)$this->client_id;
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
                            $requiredAmount = max(0, $amount - $totalChequeAmount);
                            $walletService = app(WalletService::class);
                            $wallet = $walletService->getOrCreateWallet($documentable);
                            if ((float)$wallet->balance < $requiredAmount) {
                                $validator->errors()->add('bank_id', 'موجودی کیف پول (' . number_format($wallet->balance) . ' ریال) کمتر از مبلغ پرداختی مورد نیاز (' . number_format($requiredAmount) . ' ریال) می‌باشد.');
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
            'bank_id.required_if' => 'انتخاب حساب پرداختی الزامی است.',
            'cheque_ids.required_if' => 'انتخاب حداقل یک چک جهت پرداخت الزامی است.',
            'cheque_ids.array' => 'فرمت چک‌های انتخاب شده معتبر نیست.',
            'client_id.required' => 'برای دسته‌بندی "مشتریان"، انتخاب مشتری الزامی است.',
        ];
    }
}
