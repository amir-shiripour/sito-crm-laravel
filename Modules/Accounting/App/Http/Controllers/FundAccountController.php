<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\App\Http\Requests\StoreFundAccountRequest;
use Modules\Accounting\App\Http\Requests\UpdateFundAccountRequest;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Services\AccountingEngine;
use Modules\Accounting\App\Services\CurrencyService;
use Exception;

class FundAccountController extends Controller
{
    protected AccountingEngine $accountingEngine;

    public function __construct(AccountingEngine $accountingEngine)
    {
        $this->accountingEngine = $accountingEngine;
    }

    public function index()
    {
        try {
            $this->accountingEngine->syncWalletsToFundAccounts();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Wallet fund account sync error: ' . $e->getMessage());
        }

        $query = FundAccount::with(['category', 'transactions']);
        $allQuery = FundAccount::where('status', 1);

        if (!\Modules\Accounting\App\Helpers\AccountingWalletHelper::isWalletEnabled()) {
            $excludeWalletNotes = function ($q) {
                $q->whereNull('notes')
                  ->orWhere(function ($q2) {
                      $q2->where('notes', 'not like', '%wallet_aggregated_account%')
                         ->where('notes', 'not like', '%wallet_id:%');
                  });
            };
            $query->where($excludeWalletNotes);
            $allQuery->where($excludeWalletNotes);
        }

        $fundAccounts = $query->latest()->paginate(15);
        $allFundAccounts = $allQuery->get();
        $currencySuffix = CurrencyService::getBaseCurrency();
        return view('accounting::fund_accounts.index', compact('fundAccounts', 'allFundAccounts', 'currencySuffix'));
    }

    public function create()
    {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق نقدی',
            'gateway' => 'درگاه اینترنتی',
        ];
        $assetCategories = Category::where('type', 'asset')->where('status', true)->get();
        return view('accounting::fund_accounts.create', compact('types', 'assetCategories'));
    }

    public function store(StoreFundAccountRequest $request)
    {
        FundAccount::create($request->validated());

        return redirect()->route('admin.accounting.fund-accounts.index')
            ->with('success', 'حساب خزانه‌داری با موفقیت ایجاد شد.');
    }

    public function edit(FundAccount $fundAccount)
    {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق نقدی',
            'gateway' => 'درگاه اینترنتی',
        ];
        $assetCategories = Category::where('type', 'asset')->where('status', true)->get();
        return view('accounting::fund_accounts.edit', compact('fundAccount', 'types', 'assetCategories'));
    }

    public function update(UpdateFundAccountRequest $request, FundAccount $fundAccount)
    {
        $fundAccount->update($request->validated());

        return redirect()->route('admin.accounting.fund-accounts.index')
            ->with('success', 'حساب خزانه‌داری با موفقیت ویرایش شد.');
    }

    public function destroy(FundAccount $fundAccount)
    {
        // Check if there are any transactions associated with this fund account
        if ($fundAccount->transactions()->exists()) {
            return redirect()->route('admin.accounting.fund-accounts.index')
                ->with('error', 'امکان حذف این حساب وجود ندارد زیرا دارای تراکنش‌های مالی است.');
        }

        $fundAccount->delete();
        return redirect()->route('admin.accounting.fund-accounts.index')
            ->with('success', 'حساب خزانه‌داری با موفقیت حذف شد.');
    }

    public function transfer(Request $request)
    {
        if ($request->has('amount')) {
            $request->merge([
                'amount' => str_replace(',', '', $request->input('amount'))
            ]);
        }

        $validated = $request->validate([
            'from_fund_account_id' => 'required|exists:accounting_fund_accounts,id|different:to_fund_account_id',
            'to_fund_account_id' => 'required|exists:accounting_fund_accounts,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
        ], [
            'from_fund_account_id.different' => 'حساب مبدا و مقصد نمی‌توانند یکسان باشند.',
        ]);

        try {
            $this->accountingEngine->recordTransfer(
                $validated['amount'],
                $validated['from_fund_account_id'],
                $validated['to_fund_account_id'],
                $validated['description'] ?? 'انتقال وجه'
            );

            return redirect()->route('admin.accounting.fund-accounts.index')
                ->with('success', 'انتقال وجه با موفقیت انجام شد.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در انتقال وجه: ' . $e->getMessage()]);
        }
    }
}
