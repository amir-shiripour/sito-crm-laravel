<?php

namespace Modules\Accounting\App\Livewire;

use Livewire\Component;
use Modules\Accounting\App\Models\Invoice;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // 1. Import the Log facade
use Morilog\Jalali\Jalalian;

class RecordPayment extends Component
{
    public Invoice $invoice;

    // Form fields
    public $bank_id;
    public $amount;
    public $payment_method = 'card_transfer';
    public $transaction_date;
    public $reference_code;
    public $description;

    public $banks = [];
    public $paymentMethods = [
        'card_transfer' => 'کارت به کارت',
        'bank_deposit' => 'واریز به حساب (فیش)',
        'pos' => 'دستگاه کارتخوان (POS)',
        'online' => 'پرداخت آنلاین',
        'cheque' => 'چک',
        'cash' => 'نقد',
    ];

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->banks = Bank::all();
        $this->amount = $this->invoice->total_amount - $this->invoice->transactions->sum('amount');
        $this->transaction_date = Jalalian::now()->format('Y/m/d');
    }

    /**
     * متد کمکی برای تبدیل اعداد فارسی/عربی به انگلیسی
     */
    private function convertToEnglishNumbers($string)
    {
        if (!$string) return $string;

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian, $english, $string);
        $string = str_replace($arabic, $english, $string);

        return $string;
    }

    public function savePayment()
    {
        // 1. استانداردسازی داده‌ها قبل از اعتبارسنجی
        $this->amount = $this->convertToEnglishNumbers($this->amount);
        $this->reference_code = $this->convertToEnglishNumbers($this->reference_code);

        // Defensively unformat the amount before validation
        if (is_string($this->amount)) {
            $this->amount = str_replace(',', '', $this->amount);
        }

        $remainingAmount = $this->invoice->total_amount - $this->invoice->transactions->sum('amount');

        $this->validate([
            'bank_id' => 'required|exists:accounting_banks,id',
            'amount' => 'required|numeric|min:1|max:' . $remainingAmount,
            'payment_method' => 'required|string',
            'transaction_date' => 'required|string',
            'reference_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                // 1. Create the transaction record
                $this->invoice->transactions()->create([
                    'bank_id' => $this->bank_id,
                    'amount' => $this->amount,
                    'type' => 'deposit', // ✅ مشکل اینجا بود که از income به deposit اصلاح شد
                    'payment_method' => $this->payment_method,
                    'reference_code' => $this->reference_code,
                    'description' => $this->description,
                    'transaction_date' => Jalalian::fromFormat('Y/m/d', $this->transaction_date)->toCarbon(),
                ]);

                // 2. Update the bank balance
                $bank = Bank::find($this->bank_id);
                $bank->balance += $this->amount;
                $bank->save();

                // 3. Update the invoice status
                $this->invoice->load('transactions');
                $totalPaid = $this->invoice->transactions->sum('amount');
                if ($totalPaid >= $this->invoice->total_amount) {
                    $this->invoice->status = 'paid';
                } else {
                    $this->invoice->status = 'partially_paid';
                }
                $this->invoice->save();
            });

            session()->flash('success', 'پرداخت با موفقیت ثبت شد.');
            return $this->redirect(route('admin.accounting.invoices.index'), navigate: true);

        } catch (\Exception $e) {
            // 2. Log the full error details
            Log::error('Error saving payment: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // 3. Fix the notification dispatch to show the error message
            $this->dispatch('notify', [['type' => 'error', 'text' => 'خطا در ثبت پرداخت: ' . $e->getMessage()]]);
        }
    }

    public function render()
    {
        return view('accounting::livewire.record-payment')
            ->layout('layouts.user', ['title' => 'ثبت پرداختی برای فاکتور ' . $this->invoice->invoice_number]);
    }
}
