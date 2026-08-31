<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\App\Services\ChequeService;
use Modules\Accounting\App\Http\Requests\StoreChequeRequest;
use Modules\Accounting\App\Http\Requests\EndorseChequeRequest;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Clients\Entities\Client;
use Illuminate\Http\Request;
use Exception;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Morilog\Jalali\Jalalian;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Accounting\App\Services\CurrencyService;
use Nwidart\Modules\Facades\Module;

class ChequeController extends Controller
{
    use AuthorizesRequests;

    protected ChequeService $chequeService;

    public function __construct(ChequeService $chequeService)
    {
        $this->chequeService = $chequeService;
    }

    public function index()
    {
        // $this->authorize('accounting.cheques.view');
        $checkDueDates = AccountingSetting::get('general.check_cheque_due_dates', true);

        $cheques = Cheque::with(['client', 'relatedInvoice', 'attachedInvoices', 'expenseDocuments', 'attachedDocuments'])->latest('due_date')->get()->map(function ($cheque) use ($checkDueDates) {
            $cheque->due_date_jalali = Jalalian::fromCarbon($cheque->due_date)->format('Y/m/d');
            $cheque->issue_date_jalali = $cheque->issue_date ? Jalalian::fromCarbon($cheque->issue_date)->format('Y/m/d') : '—';

            $attachedInvoice = $cheque->relatedInvoice ?? $cheque->attachedInvoices->first();
            $cheque->invoice_info = $attachedInvoice ? [
                'id' => $attachedInvoice->id,
                'number' => $attachedInvoice->invoice_number ?: $attachedInvoice->proforma_invoice_number ?: ('#' . $attachedInvoice->id)
            ] : null;

            $expenseDoc = $cheque->expenseDocuments->first() ?? $cheque->attachedDocuments->first();
            $cheque->expense_info = $expenseDoc ? [
                'id' => $expenseDoc->id,
                'description' => $expenseDoc->description
            ] : null;

            $servicePayment = null;
            if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                $payment = Payment::with('invoice')
                    ->where('method', 'cheque-' . $cheque->id)
                    ->where('status', '!=', 'canceled')
                    ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                    ->where(function ($q) use ($cheque) {
                        if ($cheque->cheque_number) {
                            $q->where('transaction_id', $cheque->cheque_number)
                              ->orWhere('notes', 'like', '%' . $cheque->cheque_number . '%');
                        }
                    })
                    ->first();
                if ($payment && $payment->invoice) {
                    $servicePayment = [
                        'invoice_number' => $payment->invoice->invoice_number ?: $payment->invoice->proforma_invoice_number ?: ('#' . $payment->invoice->id)
                    ];
                }
            }
            $cheque->service_payment_info = $servicePayment;

            $cheque->is_payable = !$checkDueDates || $cheque->due_date <= now();

            return $cheque;
        });
        $fundAccounts = FundAccount::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();

        return view('accounting::cheques.index', compact('cheques', 'fundAccounts', 'categories'));
    }

    public function create(Request $request)
    {
        // $this->authorize('accounting.cheques.create');
        $clients = Client::all();
        $customers = $clients;
        $currency = config('accounting.currency', 'ریال');
        $type = $request->query('type', 'receivable');
        $redirectTo = $request->query('redirect_to');

        return view('accounting::cheques.create', compact('clients', 'customers', 'currency', 'type', 'redirectTo'));
    }

    public function store(StoreChequeRequest $request)
    {
        // $this->authorize('accounting.cheques.create');
        try {
            $cheque = $this->chequeService->createCheque($request->validated());

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'چک دریافتی جدید با موفقیت ثبت شد و در لیست انتخاب قرار گرفت.')
                    ->with('cheque_id', $cheque->id);
            }

            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت ثبت و سند حسابداری آن صادر شد.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function show(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.view');
        $cheque->load('documents.transactions.category', 'client', 'depositedFundAccount', 'clearedFundAccount');
        $fundAccounts = FundAccount::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        return view('accounting::cheques.show', compact('cheque', 'fundAccounts', 'categories'));
    }

    public function print(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.view');
        $cheque->load('client');
        return view('accounting::cheques.print', compact('cheque'));
    }

    public function edit(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.edit');
        $clients = Client::all();
        $customers = $clients;
        $banks = FundAccount::where('status', 1)->get();
        $currencySuffix = CurrencyService::getBaseCurrency();

        return view('accounting::cheques.edit', compact('cheque', 'clients', 'customers', 'banks', 'currencySuffix'));
    }

    public function update(Request $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.edit');
        if ($request->has('issue_date')) {
            try {
                $datePart = explode(' ', $request->issue_date)[0];
                if (str_contains($datePart, '/')) {
                    $gregorianDate = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
                    $request->merge(['issue_date' => $gregorianDate]);
                }
            } catch (Exception $e) {}
        }

        if ($request->has('due_date')) {
            try {
                $datePart = explode(' ', $request->due_date)[0];
                if (str_contains($datePart, '/')) {
                    $gregorianDate = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
                    $request->merge(['due_date' => $gregorianDate]);
                }
            } catch (Exception $e) {}
        }

        if ($request->has('amount')) {
            $amount = is_numeric($request->amount) ? $request->amount : (float)str_replace(',', '', (string)$request->amount);
            $request->merge(['amount' => $amount]);
        }

        $validated = $request->validate([
            'type' => 'required|in:receivable,payable,received,issued',
            'amount' => 'required|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'payee_name' => 'required|string|max:255',
            'cheque_number' => 'required|string|max:255',
            'sayyad_id' => 'nullable|string|max:30',
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d',
            'client_id' => 'nullable|exists:clients,id',
            'bank_id' => 'nullable|exists:accounting_fund_accounts,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validated['type'] === 'received') $validated['type'] = 'receivable';
        if ($validated['type'] === 'issued') $validated['type'] = 'payable';

        $cheque->update($validated);

        return redirect()->route('admin.accounting.cheques.index')->with('success', 'اطلاعات چک با موفقیت ویرایش شد.');
    }

    public function reconcile(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        $banks = FundAccount::where('status', 1)->get();
        return view('accounting::cheques.reconcile', compact('cheque', 'banks'));
    }

    public function reconcileProcess(Request $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        $request->validate([
            'status' => 'required|in:passed,returned,cleared,bounced',
            'bank_id' => 'nullable|exists:accounting_fund_accounts,id',
            'fund_account_id' => 'nullable|exists:accounting_fund_accounts,id',
        ]);

        $fundAccountId = $request->input('fund_account_id', $request->input('bank_id'));

        try {
            $status = $request->status;
            if ($status === 'passed' || $status === 'cleared') {
                if (!$fundAccountId) {
                    return back()->withInput()->withErrors(['bank_id' => 'انتخاب حساب خزانه‌داری الزامی است.']);
                }
                $this->chequeService->clear($cheque, (int)$fundAccountId);

                if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                    $payment = Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', 'pending')
                        ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                        ->first();
                    if ($payment && $payment->invoice) {
                        $invoice = $payment->invoice;
                        $payment->update(['status' => 'paid', 'paid_at' => now()]);
                        $invoice->updatePaymentStatus(true);
                        $StatusModel = Status::class;
                        if ($invoice->isPaid()) {
                            $statusObj = $StatusModel::where('name', 'پرداخت شده')->where('type', 'payment')->first();
                            if ($statusObj) {
                                $invoice->update(['status_id' => $statusObj->id]);
                            }
                        }
                    }
                }

                return redirect()->route('admin.accounting.cheques.index')->with('success', 'وضعیت چک با موفقیت به وصول شده تغییر یافت.');
            } elseif ($status === 'returned' || $status === 'bounced') {
                $this->chequeService->bounce($cheque);
                return redirect()->route('admin.accounting.cheques.index')->with('success', 'وضعیت چک با موفقیت به برگشت خورده تغییر یافت.');
            }

            return redirect()->route('admin.accounting.cheques.index');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function destroy(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'فقط چک‌های در جریان قابل حذف هستند.');
        }

        if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
            Payment::where('method', 'cheque-' . $cheque->id)
                ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                ->update(['status' => 'canceled']);
        }

        $cheque->delete();
        return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت حذف شد.');
    }

    // --- Action Methods ---

    public function deposit(Request $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        $request->validate(['fund_account_id' => 'required|exists:accounting_fund_accounts,id']);
        try {
            $this->chequeService->deposit($cheque, $request->fund_account_id);
            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت به بانک واگذار شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function clear(Request $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        $request->validate(['fund_account_id' => 'required|exists:accounting_fund_accounts,id']);
        try {
            $this->chequeService->clear($cheque, $request->fund_account_id);

            if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                $payment = Payment::where('method', 'cheque-' . $cheque->id)
                    ->where('status', 'pending')
                    ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                    ->first();
                if ($payment && $payment->invoice) {
                    $invoice = $payment->invoice;
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);
                    $invoice->updatePaymentStatus(true);
                    $StatusModel = Status::class;
                    if ($invoice->isPaid()) {
                        $status = $StatusModel::where('name', 'پرداخت شده')->where('type', 'payment')->first();
                        if ($status) {
                            $invoice->update(['status_id' => $status->id]);
                        }
                    }
                }
            }

            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت وصول شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revertClearance(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        try {
            $this->chequeService->revertClearance($cheque);

            if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                $checkSetting = AccountingSetting::get('general.check_cheque_due_dates', true);
                if ($checkSetting) {
                    $payment = Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', 'paid')
                        ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                        ->first();
                    if ($payment && $payment->invoice) {
                        $invoice = $payment->invoice;
                        $payment->update(['status' => 'pending']);
                        $invoice->updatePaymentStatus(true);
                        $StatusModel = Status::class;
                        if ($invoice->isOverdue()) {
                            $status = $StatusModel::where('name', 'معوقه')->where('type', 'payment')->first();
                        } else {
                            $status = $StatusModel::where('name', 'در انتظار پرداخت')->where('type', 'payment')->first();
                        }
                        if ($status) {
                            $invoice->update(['status_id' => $status->id]);
                        }
                    }
                }
            }

            return redirect()->route('admin.accounting.cheques.index')->with('success', 'عملیات وصول چک با موفقیت لغو شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bounce(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        try {
            $this->chequeService->bounce($cheque);
            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت برگشت زده شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function endorse(EndorseChequeRequest $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        try {
            $this->chequeService->endorse($cheque, $request->debit_category_id, $request->description);
            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت خرج شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function returnChequeWithCash(Request $request, Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        $request->validate(['fund_account_id' => 'required|exists:accounting_fund_accounts,id']);
        try {
            $this->chequeService->returnChequeWithCash($cheque, $request->fund_account_id);

            if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                $payment = Payment::where('method', 'cheque-' . $cheque->id)
                    ->where('status', '!=', 'canceled')
                    ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                    ->first();
                if ($payment && $payment->invoice) {
                    app(\Modules\Services\App\Http\Controllers\InvoiceController::class)->cancelPayment(request(), $payment->invoice, $payment);
                }
            }

            return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت عودت و وجه نقد دریافت شد.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
