<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Services\CurrencyService;
use Modules\Accounting\App\Services\AccountingEngine;
use Exception;

class DocumentController extends Controller
{
    protected AccountingEngine $accountingEngine;

    public function __construct(AccountingEngine $accountingEngine)
    {
        $this->accountingEngine = $accountingEngine;
        // Permissions will be set up later
        // $this->middleware('can:accounting.documents.view')->only(['index', 'show']);
        // $this->middleware('can:accounting.documents.create')->only(['create', 'store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->accountingEngine->syncWalletTransactions();
        } catch (\Throwable $e) {
            Log::error('Wallet documents sync error: ' . $e->getMessage());
        }

        $query = Document::with(['transactions.fundAccount', 'sourceDocument']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('source_type')) {
            if ($request->source_type === 'auto') {
                $query->whereHas('sourceDocument');
            } elseif ($request->source_type === 'manual') {
                $query->whereDoesntHave('sourceDocument');
            } else {
                $query->whereHas('sourceDocument', function ($q) use ($request) {
                    $q->where('module', $request->source_type);
                });
            }
        }

        $documents = $query->latest('document_date')
            ->latest('id') // Fallback for same date
            ->paginate(15);

        $currencySuffix = CurrencyService::getBaseCurrency();

        return view('accounting::documents.index', compact('documents', 'currencySuffix'));
    }

    /**
     * Show the form for creating a new resource (multi-line document).
     */
    public function create()
    {
        $categories = Category::where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();
        $currencySuffix = CurrencyService::getBaseCurrency();

        return view('accounting::documents.create', compact('categories', 'fundAccounts', 'currencySuffix'));
    }

    /**
     * Store a newly created resource in storage (multi-line document).
     */
    public function store(Request $request)
    {
        if ($request->has('document_date')) {
            try {
                $datePart = explode(' ', $request->document_date)[0];
                if (str_contains($datePart, '/')) {
                    $gregorianDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
                    $request->merge(['document_date' => $gregorianDate]);
                }
            } catch (\Exception $e) {}
        }

        if ($request->has('rows') && is_array($request->rows)) {
            $sanitizedRows = $request->rows;
            foreach ($sanitizedRows as &$row) {
                if (isset($row['debit'])) {
                    $row['debit'] = is_numeric($row['debit']) ? $row['debit'] : (float)str_replace(',', '', (string)$row['debit']);
                }
                if (isset($row['credit'])) {
                    $row['credit'] = is_numeric($row['credit']) ? $row['credit'] : (float)str_replace(',', '', (string)$row['credit']);
                }
            }
            $request->merge(['rows' => $sanitizedRows]);
        }

        $request->validate([
            'document_date' => 'required|date_format:Y-m-d',
            'document_description' => 'nullable|string|max:1000',
            'rows' => 'required|array|min:2', // At least two rows for double-entry
            'rows.*.category_id' => 'required|exists:accounting_categories,id',
            'rows.*.fund_account_id' => 'nullable|exists:accounting_fund_accounts,id',
            'rows.*.description' => 'nullable|string|max:1000',
            'rows.*.debit' => 'nullable|numeric|min:0',
            'rows.*.credit' => 'nullable|numeric|min:0',
        ]);

        $rows = collect($request->rows)->map(function ($row) {
            // Convert amounts from display currency to base Rial for internal processing
            $row['debit'] = CurrencyService::convertToBaseRial($row['debit'] ?? 0);
            $row['credit'] = CurrencyService::convertToBaseRial($row['credit'] ?? 0);
            return $row;
        })->toArray();

        try {
            $document = $this->accountingEngine->recordMultiLineDocument(
                $rows,
                $request->document_description ?? '', // Ensure it's a string, not null
                null, // documentable is null for manual journal entries
                $request->document_date
            );

            return redirect()->route('admin.accounting.documents.index')
                ->with('success', 'سند حسابداری چند ردیفه با موفقیت ثبت شد. (شماره سند: ' . $document->document_number . ')');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت سند: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $document->load(['transactions.category', 'transactions.fundAccount', 'sourceDocument']);
        $currencySuffix = CurrencyService::getBaseCurrency();

        $totalDebit = CurrencyService::convertForDisplay($document->transactions->sum('debit'));
        $totalCredit = CurrencyService::convertForDisplay($document->transactions->sum('credit'));

        return view('accounting::documents.show', compact('document', 'currencySuffix', 'totalDebit', 'totalCredit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $document->load('transactions');
        $categories = Category::where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();
        $currencySuffix = CurrencyService::getBaseCurrency();

        return view('accounting::documents.edit', compact('document', 'categories', 'fundAccounts', 'currencySuffix'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'document_date' => 'required|date_format:Y-m-d',
            'document_description' => 'nullable|string|max:1000',
            'rows' => 'required|array|min:2',
            'rows.*.category_id' => 'required|exists:accounting_categories,id',
            'rows.*.fund_account_id' => 'nullable|exists:accounting_fund_accounts,id',
            'rows.*.debit' => 'nullable|numeric|min:0',
            'rows.*.credit' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($document, $request) {
                // Delete existing transactions for full voucher replacement
                $document->transactions()->delete();

                $rows = collect($request->rows)->map(function ($row) {
                    $row['debit'] = CurrencyService::convertToBaseRial($row['debit'] ?? 0);
                    $row['credit'] = CurrencyService::convertToBaseRial($row['credit'] ?? 0);
                    return $row;
                })->toArray();

                $document->update([
                    'document_date' => $request->document_date,
                    'description' => $request->document_description ?? '',
                ]);

                foreach ($rows as $row) {
                    $document->transactions()->create([
                        'category_id' => $row['category_id'],
                        'fund_account_id' => $row['fund_account_id'] ?? null,
                        'debit' => $row['debit'],
                        'credit' => $row['credit'],
                        'description' => $row['description'] ?? $document->description,
                        'transaction_date' => $document->document_date,
                    ]);
                }
            });

            return redirect()->route('admin.accounting.documents.index')
                ->with('success', 'سند حسابداری با موفقیت ویرایش شد.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش سند: ' . $e->getMessage()]);
        }
    }

    public function cancel(Document $document)
    {
        if ($document->status === 'cancelled') {
            return back()->with('error', 'این سند قبلاً لغو شده است.');
        }

        if ($document->sourceDocument) {
            return back()->with('error', 'امکان لغو مستقیم اسناد سیستمی اتوماتیک از این بخش وجود ندارد. این سند باید از ماژول مربوطه لغو گردد.');
        }

        try {
            DB::transaction(function () use ($document) {
                $document->load(['transactions']);

                $existingCancelDoc = DB::table('accounting_source_documents')
                    ->where('sourceable_type', get_class($document))
                    ->where('sourceable_id', $document->id)
                    ->where('event_type', 'document_cancelled')
                    ->first();

                if (!$existingCancelDoc) {
                    $debitSum = (float) $document->transactions->sum('debit');
                    $reversalRows = [];

                    foreach ($document->transactions as $tx) {
                        $reversalRows[] = [
                            'category_id' => $tx->category_id,
                            'fund_account_id' => $tx->fund_account_id,
                            'debit' => (float) $tx->credit,
                            'credit' => (float) $tx->debit,
                            'description' => "برگشت/اصلاح سند دستی شماره #{$document->document_number} بابت لغو سند",
                        ];
                    }

                    if (!empty($reversalRows)) {
                        $reversalDoc = $this->accountingEngine->recordMultiLineDocument(
                            $reversalRows,
                            "سند برگشتی بابت لغو سند دستی #{$document->document_number}",
                            $document,
                            now()->toDateString()
                        );

                        DB::table('accounting_source_documents')->insert([
                            'document_id' => $reversalDoc->id,
                            'sourceable_type' => get_class($document),
                            'sourceable_id' => $document->id,
                            'module' => 'accounting',
                            'event_type' => 'document_cancelled',
                            'snapshot' => json_encode(['document_number' => $document->document_number, 'amount' => $debitSum]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $document->update(['status' => 'cancelled']);

                if (class_exists(\App\Services\ActivityLogger::class)) {
                    \App\Services\ActivityLogger::log(
                        'document_cancelled',
                        "سند دستی به شماره '{$document->document_number}' لغو گردید و مبالغ مربوطه در خزانه‌داری/حساب‌ها اصلاح گردید.",
                        $document,
                        [
                            'document_number' => $document->document_number,
                            'description' => $document->description,
                        ]
                    );
                }
            });

            return back()->with('success', 'سند دستی با موفقیت لغو شد و مبلغ مربوطه به خزانه‌داری/حساب‌ها بازگردانده شد.');
        } catch (Exception $e) {
            Log::error('Document cancellation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'خطا در لغو سند: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (SoftDelete).
     */
    public function destroy(Document $document)
    {
        try {
            DB::transaction(function () use ($document) {
                $document->transactions()->delete();
                $document->delete();
            });

            return redirect()->route('admin.accounting.documents.index')
                ->with('success', 'سند حسابداری با موفقیت حذف شد.');
        } catch (Exception $e) {
            return back()->withErrors(['msg' => 'خطا در حذف سند: ' . $e->getMessage()]);
        }
    }
}
