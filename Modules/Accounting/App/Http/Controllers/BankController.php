<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\StoreBankRequest;
use Modules\Accounting\App\Http\Requests\UpdateBankRequest;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Services\BankService;
use Modules\Accounting\App\Services\CurrencyService;

class BankController extends Controller
{
    protected $bankService;

    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;

        $this->middleware('can:accounting.banks.view')->only('index');
        $this->middleware('can:accounting.banks.create')->only(['create', 'store']);
        $this->middleware('can:accounting.banks.edit')->only(['edit', 'update']);
        $this->middleware('can:accounting.banks.delete')->only('destroy');
    }

    public function index()
    {
        $banks = Bank::latest()->paginate(15);
        $allBanks = Bank::where('status', 1)->get();
        $currencySuffix = CurrencyService::getBaseCurrency();
        return view('accounting::banks.index', compact('banks', 'allBanks', 'currencySuffix'));
    }

    public function create()
    {
        $currencySuffix = CurrencyService::getBaseCurrency();
        return view('accounting::banks.create', compact('currencySuffix'));
    }

    public function store(StoreBankRequest $request)
    {
        $data = $request->validated();

        // Convert balance to base rial
        if (isset($data['balance'])) {
            $data['balance'] = CurrencyService::convertToBaseRial($data['balance']);
        }

        $this->bankService->createBank($data);

        return redirect()->route('admin.accounting.banks.index')
            ->with('success', 'حساب بانکی با موفقیت ایجاد شد.');
    }

    public function edit(Bank $bank)
    {
        $currencySuffix = CurrencyService::getBaseCurrency();

        // Convert balance for display
        $bank->balance = CurrencyService::convertForDisplay($bank->balance);

        return view('accounting::banks.edit', compact('bank', 'currencySuffix'));
    }

    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $data = $request->validated();

        // Convert balance to base rial
        if (isset($data['balance'])) {
            $data['balance'] = CurrencyService::convertToBaseRial($data['balance']);
        }

        $this->bankService->updateBank($bank, $data);

        return redirect()->route('admin.accounting.banks.index')
            ->with('success', 'حساب بانکی با موفقیت ویرایش شد.');
    }

    public function destroy(Bank $bank)
    {
        try {
            $this->bankService->deleteBank($bank);
            return redirect()->route('admin.accounting.banks.index')
                ->with('success', 'حساب بانکی با موفقیت حذف شد.');
        } catch (\Exception $e) {
            return redirect()->route('admin.accounting.banks.index')
                ->with('error', $e->getMessage());
        }
    }

    public function transfer(Request $request)
    {
        // Sanitize amount
        $amount = $request->input('amount');
        if ($amount) {
            $amount = CurrencyService::convertToBaseRial($amount);
            $request->merge(['amount' => $amount]);
        }

        $validated = $request->validate([
            'from_bank_id' => 'required|exists:accounting_banks,id|different:to_bank_id',
            'to_bank_id' => 'required|exists:accounting_banks,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
        ], [
            'from_bank_id.different' => 'حساب مبدا و مقصد نمی‌توانند یکسان باشند.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $fromBank = Bank::lockForUpdate()->findOrFail($validated['from_bank_id']);
                $toBank = Bank::lockForUpdate()->findOrFail($validated['to_bank_id']);
                $amount = (float) $validated['amount'];

                $allowNegative = (bool) \Modules\Accounting\App\Models\AccountingSetting::getValue('banking.allow_negative_balance', false);
                if (!$allowNegative && $fromBank->balance < $amount) {
                    throw new \Exception('موجودی حساب مبدا (' . CurrencyService::formatWithSuffix($fromBank->balance) . ') برای این انتقال کافی نمی‌باشد.');
                }

                $fromBank->balance -= $amount;
                $fromBank->save();

                $toBank->balance += $amount;
                $toBank->save();

                $description = $validated['description'] ?? "انتقال وجه از حساب {$fromBank->bank_name} به {$toBank->bank_name}";

                Document::create([
                    'bank_id' => $fromBank->id,
                    'type' => 'transfer_out',
                    'amount' => $amount,
                    'document_date' => now(),
                    'description' => $description,
                    'payment_method' => 'transfer',
                ]);

                Document::create([
                    'bank_id' => $toBank->id,
                    'type' => 'transfer_in',
                    'amount' => $amount,
                    'document_date' => now(),
                    'description' => $description,
                    'payment_method' => 'transfer',
                ]);
            });

            return redirect()->route('admin.accounting.banks.index')
                ->with('success', 'انتقال وجه با موفقیت انجام شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در انتقال وجه: ' . $e->getMessage()]);
        }
    }
}
