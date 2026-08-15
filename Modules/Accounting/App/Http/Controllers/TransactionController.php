<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\StoreTransactionRequest;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Services\TransactionService;
use Modules\Accounting\App\Services\AccountingEngine;

class TransactionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('can:accounting.transactions.view')->only('index');
        // $this->middleware('can:accounting.transactions.create')->only(['create', 'store']);
    }

    public function index()
    {
        // همگام‌سازی تراکنش‌های کیف پول که در سند حسابداری ثبت نشده‌اند
        try {
            $engine = app(AccountingEngine::class);
            $engine->syncWalletTransactions();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Wallet accounting sync error: ' . $e->getMessage());
        }

        $query = Transaction::with(['document', 'category', 'fundAccount'])
            ->latest('transaction_date')
            ->latest('id');

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('fundAccount', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('category', fn($cq) => $cq->where('title', 'like', "%{$search}%"));
            });
        }

        if (request()->filled('type')) {
            if (request('type') === 'credit') {
                $query->where('credit', '>', 0);
            } elseif (request('type') === 'debit') {
                $query->where('debit', '>', 0);
            }
        }

        if (request()->filled('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        if (request()->filled('fund_account_id')) {
            $query->where('fund_account_id', request('fund_account_id'));
        }

        if (request()->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', request('from_date'));
        }

        if (request()->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', request('to_date'));
        }

        $transactions = $query->paginate(25);
        $categories = \Modules\Accounting\App\Models\Category::where('status', 1)->get();
        $fundAccounts = \Modules\Accounting\App\Models\FundAccount::where('status', 1)->get();

        return view('accounting::transactions.index', compact('transactions', 'categories', 'fundAccounts'));
    }

    public function create()
    {
        $banks = Bank::where('is_active', 1)->get();
        return view('accounting::transactions.create', compact('banks'));
    }

    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $bank = Bank::findOrFail($request->bank_id);
                $amount = (float) $request->amount;

                if ($request->type === 'expense') {
                    $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                    if (!$allowNegative && $bank->balance < $amount) {
                        throw new \Exception('موجودی حساب (' . number_format($bank->balance) . ' ریال) برای این تراکنش کافی نمی‌باشد.');
                    }
                    $bank->balance -= $amount;
                } else {
                    $bank->balance += $amount;
                }
                $bank->save();

                Transaction::create($request->validated());
            });

            return redirect()->route('admin.accounting.transactions.index')
                ->with('success', 'تراکنش با موفقیت ثبت شد.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت تراکنش: ' . $e->getMessage()]);
        }
    }
}
