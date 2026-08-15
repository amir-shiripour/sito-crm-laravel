<?php

namespace Modules\Accounting\App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Services\CurrencyService;
use Modules\Clients\Entities\Client;
use Nwidart\Modules\Facades\Module;
use Livewire\Attributes\Computed;
use Illuminate\Validation\ValidationException;

class ExpenseCreate extends Component
{
    // Main Expense Details
    public $description;
    public $amount = 0;
    public $category_id;
    public $client_id;
    public $document_date;
    public $reference_number;
    public $notes;

    // Payment Rows
    public $payments = [];

    // Excess Payment Handling
    public $showExcessPaymentModal = false;
    public $excess_payment_bank_id;
    public $excess_payment_description;

    // Available data for dropdowns
    public $banks = [];
    public $categories = [];
    public $customers = [];
    public $availableCheques = [];
    public $currencySuffix;

    protected function cleanupNumbers()
    {
        $this->amount = (float) str_replace(',', '', $this->amount);
        foreach ($this->payments as $index => $payment) {
            $this->payments[$index]['amount'] = (float) str_replace(',', '', $payment['amount']);
        }
    }

    public function mount()
    {
        $this->banks = FundAccount::with('transactions')->where('status', 1)->get();
        $this->categories = Category::where('type', 'expense')->where('status', 1)->get();
        $this->customers = Module::isEnabled('Clients') ? Client::select('id', 'full_name', 'username')->get() : collect();
        $serviceChequeIds = [];
        if (Module::has('Services') && Module::isEnabled('Services')) {
            $serviceChequeIds = \Modules\Services\App\Http\Models\Payment::where('method', 'like', 'cheque-%')
                ->where('status', '!=', 'canceled')
                ->pluck('method')
                ->map(fn($m) => (int) str_replace('cheque-', '', $m))
                ->filter()->toArray();
        }

        $this->availableCheques = Cheque::whereIn('status', ['registered', 'pending'])
            ->whereNull('related_invoice_id')
            ->doesntHave('attachedInvoices')
            ->when(!empty($serviceChequeIds), fn($q) => $q->whereNotIn('id', $serviceChequeIds))
            ->get();
        $this->document_date = jdate()->format('Y/m/d');
        $this->currencySuffix = CurrencyService::getBaseCurrency();
        $this->excess_payment_bank_id = $this->banks->first()?->id;

        if (empty($this->payments)) {
            $this->addPayment();
        }
    }

    public function updatedAmount($value)
    {
        if (count($this->payments) === 1) {
            $this->payments[0]['amount'] = (float) str_replace(',', '', $value);
        }
    }

    #[Computed]
    public function cleanedAmount()
    {
        return (float) str_replace(',', '', $this->amount ?? 0);
    }

    #[Computed]
    public function totalPaidAmount()
    {
        return collect($this->payments)->sum(function ($payment) {
            return (float) str_replace(',', '', $payment['amount'] ?? 0);
        });
    }

    #[Computed]
    public function remainingAmountToPay()
    {
        return $this->cleanedAmount - $this->totalPaidAmount;
    }

    public function addPayment()
    {
        $this->payments[] = [
            'method' => 'bank',
            'amount' => $this->remainingAmountToPay > 0 ? $this->remainingAmountToPay : '',
            'bank_id' => $this->banks->first()?->id,
            'cheque_id' => null,
            'description' => ''
        ];
    }

    public function removePayment($index)
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function rules()
    {
        $rules = [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|exists:accounting_categories,id',
            'client_id' => 'nullable|exists:clients,id',
            'document_date' => 'required|date_format:Y/m/d',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:bank,cheque',
            'payments.*.amount' => 'required|numeric|min:1',
            'payments.*.bank_id' => 'required_if:payments.*.method,bank|exists:accounting_fund_accounts,id',
            'payments.*.cheque_id' => 'required_if:payments.*.method,cheque|nullable|exists:accounting_cheques,id',
            'excess_payment_bank_id' => 'required_if:showExcessPaymentModal,true|exists:accounting_fund_accounts,id',
            'excess_payment_description' => 'nullable|string|max:255',
        ];

        $category = Category::find($this->category_id);
        if ($category && $category->title === 'دسته مشتریان') {
            $rules['client_id'] = 'required|exists:clients,id';
        }

        return $rules;
    }

    public function save()
    {
        $this->cleanupNumbers();

        if ($this->remainingAmountToPay < 0) {
            $this->showExcessPaymentModal = true;
            return;
        }

        $this->executeSave();
    }

    public function saveWithExcess()
    {
        $this->executeSave();
    }

    private function executeSave()
    {
        try {
            $validatedData = $this->validate();

            $gregorianDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $this->document_date)->toCarbon()->format('Y-m-d');

            DB::transaction(function () use ($gregorianDate, $validatedData) {
                $baseExpenseAmount = CurrencyService::convertToBaseRial($validatedData['amount']);

                // Create the main expense document
                $documentNumber = 'EXP-' . date('YmdHis') . '-' . rand(100, 999);
                $expenseDocument = Document::create([
                    'document_number' => $documentNumber,
                    'description' => $validatedData['description'],
                    'document_date' => $gregorianDate,
                ]);

                foreach ($validatedData['payments'] as $payment) {
                    $paymentAmountInRial = CurrencyService::convertToBaseRial($payment['amount']);

                    if ($payment['method'] === 'bank') {
                        $fundAccount = FundAccount::with('transactions')->find($payment['bank_id']);
                        if (!$fundAccount) throw new \Exception('حساب خزانه‌داری انتخاب شده یافت نشد.');

                        // Compute balance from transactions (no balance column in fund_accounts table)
                        $currentBalance = $fundAccount->transactions->sum('credit') - $fundAccount->transactions->sum('debit');

                        $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                        if (!$allowNegative && $currentBalance < $paymentAmountInRial) {
                            throw new \Exception('موجودی حساب ' . $fundAccount->name . ' (' . CurrencyService::formatWithSuffix($currentBalance) . ') برای پرداخت کافی نیست.');
                        }

                        // Record the debit transaction (money going out)
                        \Modules\Accounting\App\Models\Transaction::create([
                            'document_id' => $expenseDocument->id,
                            'category_id' => $validatedData['category_id'],
                            'fund_account_id' => $fundAccount->id,
                            'debit' => $paymentAmountInRial,
                            'credit' => 0,
                            'description' => $validatedData['description'],
                            'transaction_date' => now(),
                        ]);

                    } elseif ($payment['method'] === 'cheque') {
                        $cheque = Cheque::find($payment['cheque_id']);
                        if (!$cheque || $cheque->type !== 'received' || $cheque->status !== 'registered') {
                            throw new \Exception('چک انتخاب شده معتبر نیست یا قابل واگذاری نمی‌باشد.');
                        }

                        if ($cheque->amount < $paymentAmountInRial) {
                            throw new \Exception('مبلغ چک واگذار شده (' . CurrencyService::formatWithSuffix($cheque->amount) . ') کمتر از مبلغ پرداختی است.');
                        }

                        $cheque->update([
                            'status' => 'endorsed',
                            'description' => $cheque->description . "\n" . "واگذار شده بابت هزینه: " . $validatedData['description'],
                            'documentable_id' => $expenseDocument->id,
                            'documentable_type' => Document::class,
                        ]);

                        \Modules\Accounting\App\Models\Transaction::create([
                            'document_id' => $expenseDocument->id,
                            'category_id' => $validatedData['category_id'],
                            'fund_account_id' => null,
                            'debit' => $paymentAmountInRial,
                            'credit' => 0,
                            'description' => $validatedData['description'] . ' (با چک ' . $cheque->cheque_number . ')',
                            'transaction_date' => now(),
                        ]);
                    }
                }

                if ($this->remainingAmountToPay < 0) {
                    $excessAmountInRial = CurrencyService::convertToBaseRial(abs($this->remainingAmountToPay));
                    $excessFundAccount = FundAccount::findOrFail($this->excess_payment_bank_id);

                    $excessFundAccount->balance += $excessAmountInRial;
                    $excessFundAccount->save();
                }
            });

            session()->flash('success', 'هزینه و پرداخت‌های مربوطه با موفقیت ثبت شد.');
            return $this->redirect(route('admin.accounting.expenses.index'), navigate: true);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['general_error' => 'خطا در ثبت هزینه: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('accounting::livewire.expense-create')
            ->layout('layouts.user', ['title' => 'ثبت هزینه جدید']);
    }
}
