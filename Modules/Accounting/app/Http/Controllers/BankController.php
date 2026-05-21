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
        // $this->middleware('can:accounting.banks.transfer')->only('transfer'); // Add permission if needed later
    }

    public function index()
    {
        $banks = Bank::latest()->paginate(15);
        // Pass all active banks for the transfer modal
        $allBanks = Bank::where('status', 1)->get();
        return view('accounting::banks.index', compact('banks', 'allBanks'));
    }

    public function create()
    {
        return view('accounting::banks.create');
    }

    public function store(StoreBankRequest $request)
    {
        $this->bankService->createBank($request->validated());

        return redirect()->route('admin.accounting.banks.index')
            ->with('success', 'حساب بانکی با موفقیت ایجاد شد.');
    }

    public function edit(Bank $bank)
    {
        return view('accounting::banks.edit', compact('bank'));
    }

    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $this->bankService->updateBank($bank, $request->validated());

        return redirect()->route('admin.accounting.banks.index')
            ->with('success', 'حساب بانکی با موفقیت ویرایش شد.');
    }

    public function destroy(Bank $bank)
    {
        $this->bankService->deleteBank($bank);

        return redirect()->route('admin.accounting.banks.index')
            ->with('success', 'حساب بانکی با موفقیت حذف شد.');
    }

    public function transfer(Request $request)
    {
        // Sanitize amount
        $amount = $request->input('amount');
        if ($amount) {
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $amount = str_replace($persian, $english, $amount);
            $amount = str_replace(',', '', $amount);
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

                // Check negative balance setting for the source bank
                $allowNegative = (bool) \Modules\Accounting\App\Models\AccountingSetting::getValue('banking.allow_negative_balance', false);
                if (!$allowNegative && $fromBank->balance < $amount) {
                    throw new \Exception('موجودی حساب مبدا (' . number_format($fromBank->balance) . ' ریال) برای این انتقال کافی نمی‌باشد.');
                }

                // Deduct from source
                $fromBank->balance -= $amount;
                $fromBank->save();

                // Add to destination
                $toBank->balance += $amount;
                $toBank->save();

                $description = $validated['description'] ?? "انتقال وجه از حساب {$fromBank->bank_name} به {$toBank->bank_name}";

                // Create Transfer Out Document for source bank
                Document::create([
                    'bank_id' => $fromBank->id,
                    'type' => 'transfer_out',
                    'amount' => $amount,
                    'document_date' => now(),
                    'description' => $description,
                    'payment_method' => 'transfer',
                ]);

                // Create Transfer In Document for destination bank
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
