<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\StoreTransactionRequest;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\App\Models\Cheque;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:accounting.transactions.view')->only('index');
        $this->middleware('can:accounting.transactions.create')->only(['create', 'store']);
    }

    public function index()
    {
        // 1. آماده‌سازی کوئری برای تراکنش‌های درآمد (Transactions where type = 'income')
        $transactionsQuery = Transaction::select(
            'id',
            'bank_id',
            'amount',
            'transaction_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), 'درآمد مستقیم (تراکنش)') as description"),
            DB::raw("'transaction' as source_type"),
            DB::raw("NULL as documentable_id"),
            DB::raw("NULL as documentable_type")
        )->where('type', 'income');

        // 2. آماده‌سازی کوئری برای چک‌های دریافتی وصول شده (Cheques where type='received' and status='passed')
        $chequesQuery = Cheque::select(
            'id',
            'reconciled_bank_id as bank_id',
            'amount',
            'reconciliation_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), CONCAT('وصول چک دریافتی شماره ', cheque_number)) as description"),
            DB::raw("'cheque' as source_type"),
            DB::raw("NULL as documentable_id"),
            DB::raw("NULL as documentable_type")
        )->where('type', 'received')->where('status', 'passed');

        // 3. آماده‌سازی کوئری برای اسناد از نوع درآمد (مانند پرداخت فاکتورها)
        $documentsQuery = Document::select(
            'id',
            'bank_id',
            'amount',
            'document_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), 'سند درآمد') as description"),
            DB::raw("'document' as source_type"),
            'documentable_id',
            'documentable_type'
        )->where('type', 'income');

        // 4. ترکیب کوئری‌ها (UNION)، مرتب‌سازی بر اساس تاریخ و صفحه‌بندی
        $records = $transactionsQuery->union($chequesQuery)->union($documentsQuery)
            ->orderBy('date', 'desc')
            ->paginate(20);

        // 5. بهینه‌سازی واکشی اطلاعات مرتبط (Eager Loading)
        $bankIds = $records->pluck('bank_id')->unique()->filter();
        $banks = Bank::whereIn('id', $bankIds)->get()->keyBy('id');

        // واکشی اسناد مرتبط با فاکتورها به صورت بهینه
        $documentIds = $records->where('source_type', 'document')->pluck('id');
        $documentsWithInvoices = Document::with('documentable')
            ->whereIn('id', $documentIds)
            ->where('documentable_type', \Modules\Accounting\App\Models\Invoice::class)
            ->get()
            ->keyBy('id');

        // اتصال اطلاعات به هر رکورد
        foreach ($records as $record) {
            $record->bank = $banks->get($record->bank_id);
            if ($record->source_type === 'document' && $documentsWithInvoices->has($record->id)) {
                $record->invoice = $documentsWithInvoices->get($record->id)->documentable;
            }
        }

        return view('accounting::transactions.index', compact('records'));
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
