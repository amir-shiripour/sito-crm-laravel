<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Http\Requests\StoreExpenseRequest;
use Modules\Accounting\App\Http\Requests\UpdateExpenseRequest;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\FundAccount;

// Updated from Bank
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\App\Services\CurrencyService;
use Modules\Accounting\App\Services\DocumentService;
use Modules\Accounting\Entities\Cheque;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Payment;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\WalletTransaction;
use Modules\Wallet\App\Services\WalletService;
use Modules\Accounting\App\Helpers\AccountingWalletHelper;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class ExpenseController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
        // Commented out middleware for testing
        // $this->middleware('can:accounting.expenses.view')->only('index');
        // $this->middleware('can:accounting.expenses.create')->only(['create', 'store']);
        // $this->middleware('can:accounting.expenses.edit')->only(['edit', 'update']);
        // $this->middleware('can:accounting.expenses.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Document::whereHas('transactions', function ($q) {
            $q->whereHas('category', function ($cq) {
                $cq->where('type', 'expense');
            });
        })->with(['transactions.category', 'transactions.fundAccount', 'cheque', 'cheques']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('transactions', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($fundAccountId = $request->input('fund_account_id')) {
            $query->whereHas('transactions', function ($q) use ($fundAccountId) {
                $q->where('fund_account_id', $fundAccountId);
            });
        }

        if ($paymentType = $request->input('payment_type')) {
            if ($paymentType === 'cheque') {
                $query->where(function ($q) {
                    $q->whereNotNull('cheque_id')->orWhereHas('cheques');
                })->whereDoesntHave('transactions', function ($q) {
                    $q->whereNotNull('fund_account_id');
                });
            } elseif ($paymentType === 'multi_cheque') {
                $query->whereHas('cheques')->whereDoesntHave('transactions', function ($q) {
                    $q->whereNotNull('fund_account_id');
                });
            } elseif ($paymentType === 'bank') {
                $query->whereNull('cheque_id')->whereDoesntHave('cheques');
            } elseif ($paymentType === 'split') {
                $query->where(function ($q) {
                    $q->whereNotNull('cheque_id')->orWhereHas('cheques');
                })->whereHas('transactions', function ($q) {
                    $q->whereNotNull('fund_account_id');
                });
            }
        }

        $documents = $query->latest('document_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('type', 'expense')->where('status', 1)->get();
        $fundAccounts = FundAccount::where('status', 1)->get();

        return view('accounting::expenses.index', compact('documents', 'categories', 'fundAccounts'));
    }

    private function getAvailableReceivableCheques($currentChequeIds = [])
    {
        $currentChequeIds = is_array($currentChequeIds) ? $currentChequeIds : ($currentChequeIds ? [$currentChequeIds] : []);
        return Cheque::where(function ($q) use ($currentChequeIds) {
            $q->where(function ($sub) {
                $sub->whereIn('status', ['pending', 'registered'])
                    ->whereNull('related_invoice_id')
                    ->doesntHave('attachedInvoices');
            });

            if (!empty($currentChequeIds)) {
                $q->orWhereIn('id', $currentChequeIds);
            }
        })
            ->get()
            ->filter(function ($cheque) use ($currentChequeIds) {
                if (in_array($cheque->id, $currentChequeIds)) return true;
                if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                    $hasServicePayment = Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', '!=', 'canceled')
                        ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                        ->exists();
                    if ($hasServicePayment) return false;
                }
                return true;
            })
            ->values()
            ->map(function ($cheque) {
                $statusMap = [
                    'pending' => ['label' => 'در جریان', 'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60'],
                    'registered' => ['label' => 'ثبت اولیه', 'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-200 dark:border-blue-700/60'],
                    'deposited' => ['label' => 'واگذار به بانک', 'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300 border border-sky-200 dark:border-sky-700/60'],
                    'cleared' => ['label' => 'وصول شده', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/60'],
                    'bounced' => ['label' => 'برگشت خورده', 'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 border border-rose-200 dark:border-rose-700/60'],
                    'transferred' => ['label' => 'منتقل شده', 'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 border border-purple-200 dark:border-purple-700/60'],
                    'returned' => ['label' => 'عودت شده', 'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'],
                    'endorsed' => ['label' => 'خرج شده', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 border border-orange-200 dark:border-orange-700/60'],
                ];
                $st = $statusMap[$cheque->status] ?? [
                    'label' => $cheque->status ?? 'در جریان',
                    'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60'
                ];

                $cheque->due_date_jalali = $cheque->due_date ? \Morilog\Jalali\Jalalian::fromCarbon($cheque->due_date)->format('Y/m/d') : '—';
                $cheque->type_label = $cheque->type === 'receivable' ? 'چک دریافتی' : 'چک پرداختی';
                $cheque->status_label = $st['label'];
                $cheque->status_badge = $st['badge'];
                return $cheque;
            });
    }

    public function create()
    {
        $categories = Category::where('type', 'expense')->where('status', 1)->get();
        $banks = FundAccount::with('transactions')->where('status', 1)->get()->map(function ($bank) {
            $bank->balance_val = $bank->isWalletAccount()
                ? (float)($bank->current_balance > 0 ? $bank->current_balance : 999999999999)
                : (float)($bank->transactions->sum('debit') - $bank->transactions->sum('credit'));
            return $bank;
        });
        $walletService = AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class) ? app(WalletService::class) : null;
        $customers = collect();

        if (class_exists(Client::class)) {
            $clients = Client::select('id', 'full_name', 'username', 'national_code')->get()->map(function ($client) use ($walletService) {
                $wallet = $walletService ? $walletService->getOrCreateWallet($client) : null;
                $client->value_key = $client->getMorphClass() . ':' . $client->id;
                $client->display_name = ($client->full_name ?: $client->username) . ($client->national_code ? ' (' . $client->national_code . ')' : '') . ' [مشتری]';
                $client->wallet_balance = $wallet ? (float)$wallet->balance : 0;
                return $client;
            });
            $customers = $customers->concat($clients);
        }

        if (class_exists(User::class)) {
            $users = User::select('id', 'name', 'email')->get()->map(function ($user) use ($walletService) {
                $wallet = $walletService ? $walletService->getOrCreateWallet($user) : null;
                $user->value_key = $user->getMorphClass() . ':' . $user->id;
                $user->display_name = ($user->name ?: $user->email) . ' [کاربر سیستم]';
                $user->wallet_balance = $wallet ? (float)$wallet->balance : 0;
                return $user;
            });
            $customers = $customers->concat($users);
        }

        $currencySuffix = CurrencyService::getBaseCurrency();

        $receivableCheques = $this->getAvailableReceivableCheques();

        return view('accounting::expenses.create', compact('categories', 'banks', 'customers', 'currencySuffix', 'receivableCheques'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $validated = $request->validated();

        try {
            $gregorianDate = $validated['document_date'];

            $expenseDocument = $this->documentService->createExpense([
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'fee' => $validated['fee'] ?? 0,
                'category_id' => $validated['category_id'],
                'client_id' => $validated['client_id'] ?? null,
                'fund_account_id' => $validated['bank_id'] ?? null,
                'bank_accounts' => $validated['bank_accounts'] ?? [],
                'cheque_ids' => $validated['cheque_ids'] ?? [],
                'cheques' => $validated['cheques'] ?? [],
                'document_date' => $gregorianDate,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('expense_attachments', 'public');
                $expenseDocument->update(['attachment' => $path]);
            }

            ActivityLogger::log(
                'expense_created',
                "سند هزینه به شماره '{$expenseDocument->document_number}' به مبلغ " . number_format($validated['amount']) . " ثبت شد." . (!empty($validated['fee']) ? " (کارمزد: " . number_format($validated['fee']) . ")" : ""),
                $expenseDocument,
                [
                    'document_number' => $expenseDocument->document_number,
                    'amount' => $validated['amount'],
                    'fee' => $validated['fee'] ?? 0,
                    'description' => $validated['description'],
                ]
            );

            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'ثبت هزینه با موفقیت انجام شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت هزینه: ' . $e->getMessage()]);
        }
    }

    public function show(Document $expense)
    {
        $expense->load(['fundAccount', 'category', 'client', 'documentable', 'transactions.category', 'transactions.fundAccount', 'cheque', 'cheques']);
        return view('accounting::expenses.show', compact('expense'));
    }

    public function edit(Document $expense)
    {
        $categories = Category::where('type', 'expense')->where('status', 1)->get();
        $banks = FundAccount::with('transactions')->where('status', 1)->get()->map(function ($bank) {
            $bank->balance_val = $bank->isWalletAccount()
                ? (float)($bank->current_balance > 0 ? $bank->current_balance : 999999999999)
                : (float)($bank->transactions->sum('debit') - $bank->transactions->sum('credit'));
            return $bank;
        });

        $expense->load(['transactions.category', 'transactions.fundAccount', 'documentable', 'cheque', 'cheques']);

        $feeCatId = AccountingSetting::get('defaults.bank_fee_category_id');
        $debitTxs = $expense->transactions->where('debit', '>', 0);
        $feeTx = $debitTxs->first(function ($t) use ($feeCatId) {
            return ($feeCatId && $t->category_id == $feeCatId) || str_contains($t->description ?? '', 'کارمزد');
        });
        $mainDebitTx = $debitTxs->first(function ($t) use ($feeTx) {
            return !$feeTx || $t->id !== $feeTx->id;
        }) ?? $debitTxs->first();

        $expenseFee = $feeTx ? (float)$feeTx->debit : 0;
        $expenseAmount = $mainDebitTx ? (float)$mainDebitTx->debit : (float)$expense->transactions->sum('debit');
        $expenseCategoryId = $mainDebitTx?->category_id;

        $creditTxs = $expense->transactions->where('credit', '>', 0);
        $bankCreditTxs = $creditTxs->whereNotNull('fund_account_id');
        $expenseFundAccountId = $bankCreditTxs->first()?->fund_account_id;
        $expenseClientId = ($expense->documentable_type === Client::class || $expense->documentable_type === 'Modules\Clients\Entities\Client') ? $expense->documentable_id : null;
        $expenseClientIdKey = $expense->documentable_type && $expense->documentable_id ? ($expense->documentable_type . ':' . $expense->documentable_id) : null;

        $existingBankAccounts = [];
        foreach ($bankCreditTxs as $bTx) {
            $accTotal = (float)$bTx->credit;
            $accFee = 0;
            if (preg_match('/کارمزد\D*([\d,]+)/u', $bTx->description ?? '', $m)) {
                $accFee = (float)str_replace(',', '', $m[1]);
            }
            $accAmount = max(0, $accTotal - $accFee);
            $existingBankAccounts[] = [
                'bank_id' => (string)$bTx->fund_account_id,
                'amount' => $accAmount,
                'fee' => $accFee > 0 ? $accFee : '',
                'client_id' => $expenseClientIdKey,
            ];
        }

        if (empty($existingBankAccounts)) {
            $existingBankAccounts = [
                [
                    'bank_id' => $expenseFundAccountId ? (string)$expenseFundAccountId : '',
                    'amount' => $expenseAmount,
                    'fee' => $expenseFee > 0 ? $expenseFee : '',
                    'client_id' => $expenseClientIdKey,
                ]
            ];
        }

        $existingWalletTxAmount = 0;
        $existingWalletDocumentableType = null;
        $existingWalletDocumentableId = null;

        $existingSource = DB::table('accounting_source_documents')
            ->where('document_id', $expense->id)
            ->where('sourceable_type', WalletTransaction::class)
            ->first();
        if ($existingSource) {
            $prevTx = WalletTransaction::find($existingSource->sourceable_id);
            if ($prevTx) {
                $existingWalletTxAmount = (float)$prevTx->amount;
                $existingWalletDocumentableType = $expense->documentable_type;
                $existingWalletDocumentableId = $expense->documentable_id;
            }
        }

        $walletService = AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class) ? app(WalletService::class) : null;
        $customers = collect();

        if (class_exists(Client::class)) {
            $clients = Client::select('id', 'full_name', 'username', 'national_code')->get()->map(function ($client) use ($walletService, $existingWalletDocumentableType, $existingWalletDocumentableId, $existingWalletTxAmount) {
                $wallet = $walletService ? $walletService->getOrCreateWallet($client) : null;
                $bal = $wallet ? (float)$wallet->balance : 0;
                if ($existingWalletDocumentableType === $client->getMorphClass() && $existingWalletDocumentableId == $client->id) {
                    $bal += $existingWalletTxAmount;
                }
                $client->value_key = $client->getMorphClass() . ':' . $client->id;
                $client->display_name = ($client->full_name ?: $client->username) . ($client->national_code ? ' (' . $client->national_code . ')' : '') . ' [مشتری]';
                $client->wallet_balance = $bal;
                return $client;
            });
            $customers = $customers->concat($clients);
        }

        if (class_exists(User::class)) {
            $users = User::select('id', 'name', 'email')->get()->map(function ($user) use ($walletService, $existingWalletDocumentableType, $existingWalletDocumentableId, $existingWalletTxAmount) {
                $wallet = $walletService ? $walletService->getOrCreateWallet($user) : null;
                $bal = $wallet ? (float)$wallet->balance : 0;
                if ($existingWalletDocumentableType === $user->getMorphClass() && $existingWalletDocumentableId == $user->id) {
                    $bal += $existingWalletTxAmount;
                }
                $user->value_key = $user->getMorphClass() . ':' . $user->id;
                $user->display_name = ($user->name ?: $user->email) . ' [کاربر سیستم]';
                $user->wallet_balance = $bal;
                return $user;
            });
            $customers = $customers->concat($users);
        }

        $currencySuffix = CurrencyService::getBaseCurrency();

        $expenseChequeIds = $expense->cheques->pluck('id')->toArray();
        if (empty($expenseChequeIds) && $expense->cheque_id) {
            $expenseChequeIds = [$expense->cheque_id];
        }

        $existingCheques = [];
        foreach ($expense->cheques as $chq) {
            $chqFee = 0;
            $chqTx = $creditTxs->whereNull('fund_account_id')->first(function ($t) use ($chq) {
                return str_contains($t->description ?? '', (string)$chq->cheque_number);
            });
            if ($chqTx && preg_match('/کارمزد[^\d]*([\d,]+)/u', $chqTx->description ?? '', $m)) {
                $chqFee = (float)str_replace(',', '', $m[1]);
            }
            $existingCheques[] = [
                'id' => (string)$chq->id,
                'fee' => $chqFee > 0 ? $chqFee : '',
                'fee_bank_id' => null,
            ];
        }

        $receivableCheques = $this->getAvailableReceivableCheques($expenseChequeIds);

        return view('accounting::expenses.edit', compact(
            'expense', 'banks', 'categories', 'customers', 'currencySuffix',
            'expenseCategoryId', 'expenseFundAccountId', 'expenseAmount', 'expenseFee', 'expenseClientId', 'expenseClientIdKey',
            'receivableCheques', 'expenseChequeIds', 'existingBankAccounts', 'existingCheques'
        ));
    }

    public function update(UpdateExpenseRequest $request, Document $expense)
    {
        $data = $request->validated();

        $paymentType = $data['payment_type'] ?? 'bank';
        $data['fund_account_id'] = $data['bank_id'] ?? null;
        $data['cheque_ids'] = $paymentType === 'cheque' ? ($data['cheque_ids'] ?? []) : [];
        $data['cheques'] = $paymentType === 'cheque' ? ($data['cheques'] ?? []) : [];
        $data['bank_accounts'] = $data['bank_accounts'] ?? [];
        unset($data['bank_id']);

        if ($request->hasFile('attachment')) {
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('expense_attachments', 'public');
        } elseif ($request->boolean('delete_attachment')) {
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
                $data['attachment'] = null;
            }
        }

        try {
            if (!empty($data['client_id'])) {
                $data['documentable_type'] = Client::class;
                $data['documentable_id'] = $data['client_id'];
            } else {
                $data['documentable_type'] = null;
                $data['documentable_id'] = null;
            }
            unset($data['client_id']);

            $this->documentService->updateExpense($expense, $data);

            ActivityLogger::log(
                'expense_updated',
                "سند هزینه به شماره '{$expense->document_number}' ویرایش شد.",
                $expense,
                [
                    'document_number' => $expense->document_number,
                    'amount' => $data['amount'],
                    'fee' => $data['fee'] ?? 0,
                    'description' => $data['description'],
                ]
            );

            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'سند هزینه با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش هزینه: ' . $e->getMessage()]);
        }
    }

    public function cancel(Document $expense)
    {
        if ($expense->status === 'cancelled') {
            return back()->withErrors(['msg' => 'این سند هزینه قبلاً لغو شده است.']);
        }

        try {
            DB::transaction(function () use ($expense) {
                $expense->load(['transactions.category', 'transactions.fundAccount', 'cheque', 'cheques', 'documentable']);

                $debitTxs = $expense->transactions->where('debit', '>', 0);
                $creditTxs = $expense->transactions->where('credit', '>', 0);
                $bankCreditTxs = $creditTxs->whereNotNull('fund_account_id');

                $expense->update(['status' => 'cancelled']);
                $chequesToRelease = $expense->cheques;
                if ($expense->cheque && !$chequesToRelease->contains('id', $expense->cheque->id)) {
                    $chequesToRelease->push($expense->cheque);
                }
                foreach ($chequesToRelease as $cheque) {
                    if (in_array($cheque->status, ['transferred', 'issued'])) {
                        $cheque->update(['status' => 'pending']);
                    }
                }

                foreach ($bankCreditTxs as $bTx) {
                    $fundAccount = $bTx->fundAccount;
                    $refundAmount = (float)$bTx->credit;
                    if ($fundAccount && $refundAmount > 0) {
                        Transaction::create([
                            'document_id' => $expense->id,
                            'category_id' => $fundAccount->category_id,
                            'fund_account_id' => $fundAccount->id,
                            'debit' => $refundAmount,
                            'credit' => 0,
                            'description' => "برگشت وجه به خزانه‌داری ({$fundAccount->name}) بابت لغو سند هزینه #{$expense->document_number}",
                            'transaction_date' => now()->toDateString(),
                        ]);
                    }
                }

                foreach ($debitTxs as $dTx) {
                    Transaction::create([
                        'document_id' => $expense->id,
                        'category_id' => $dTx->category_id,
                        'fund_account_id' => null,
                        'debit' => 0,
                        'credit' => (float)$dTx->debit,
                        'description' => "اصلاح سرفصل هزینه بابت لغو سند #{$expense->document_number}",
                        'transaction_date' => now()->toDateString(),
                    ]);
                }

                if (AccountingWalletHelper::isWalletEnabled() && class_exists(WalletService::class)) {
                    $walletService = app(WalletService::class);
                    $existingWalletSources = DB::table('accounting_source_documents')
                        ->where('document_id', $expense->id)
                        ->where(function ($q) {
                            $q->where('sourceable_type', WalletTransaction::class)
                                ->orWhere('sourceable_type', 'Modules\Wallet\App\Models\WalletTransaction');
                        })
                        ->get();

                    foreach ($existingWalletSources as $wSrc) {
                        $prevWalletTx = WalletTransaction::find($wSrc->sourceable_id);
                        if ($prevWalletTx) {
                            $walletService->refund($prevWalletTx, null, "برگشت وجه به کیف پول بابت لغو سند هزینه #{$expense->document_number}");
                        }
                    }
                }

                ActivityLogger::log(
                    'expense_cancelled',
                    "سند هزینه به شماره '{$expense->document_number}' و مبلغ " . number_format($totalExpenseDebit) . " ریال لغو گردید.",
                    $expense,
                    [
                        'document_number' => $expense->document_number,
                        'amount' => $totalExpenseDebit,
                        'description' => $expense->description,
                    ]
                );

                $fundAccountName = $fundAccount?->name ?? ($expense->cheque ? "چک صیادی شماره {$expense->cheque->cheque_number}" : 'خزانه‌داری');
                ActivityLogger::log(
                    'treasury_refund',
                    "مبلغ " . number_format($expenseAmount) . " ریال بابت لغو سند هزینه '{$expense->document_number}' به خزانه‌داری ({$fundAccountName}) بازگردانده شد.",
                    $fundAccount ?? $expense,
                    [
                        'document_number' => $expense->document_number,
                        'fund_account_name' => $fundAccountName,
                        'amount' => $expenseAmount,
                    ]
                );
            });

            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'سند هزینه با موفقیت لغو شد و مبلغ مربوطه به خزانه‌داری بازگردانده شد.');
        } catch (\Exception $e) {
            Log::error('Expense cancellation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['msg' => 'خطا در لغو هزینه: ' . $e->getMessage()]);
        }
    }

    public function destroy(Document $expense)
    {
        return back()->withErrors(['msg' => 'امکان حذف مستقیم اسناد هزینه وجود ندارد. لطفاً از گزینه‌ی لغو هزینه استفاده کنید.']);
    }
}
