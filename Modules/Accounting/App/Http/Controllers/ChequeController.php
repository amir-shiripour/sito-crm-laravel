<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\App\Services\ChequeService;
use Modules\Accounting\App\Http\Requests\StoreChequeRequest;
use Modules\Accounting\App\Http\Requests\EndorseChequeRequest;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Clients\Entities\Client;
use Illuminate\Http\Request;
use Exception;
use Morilog\Jalali\Jalalian;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        $checkDueDates = \Modules\Accounting\App\Models\AccountingSetting::get('general.check_cheque_due_dates', true);

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
            if (\Nwidart\Modules\Facades\Module::has('Services') && \Nwidart\Modules\Facades\Module::isEnabled('Services')) {
                $payment = \Modules\Services\App\Http\Models\Payment::with('invoice')
                    ->where('method', 'cheque-' . $cheque->id)
                    ->where('status', '!=', 'canceled')
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

    public function destroy(Cheque $cheque)
    {
        // $this->authorize('accounting.cheques.action');
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'فقط چک‌های در جریان قابل حذف هستند.');
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

            if (\Nwidart\Modules\Facades\Module::has('Services') && \Nwidart\Modules\Facades\Module::isEnabled('Services')) {
                $payment = \Modules\Services\App\Http\Models\Payment::where('method', 'cheque-' . $cheque->id)
                    ->where('status', 'pending')
                    ->first();
                if ($payment && $payment->invoice) {
                    $invoice = $payment->invoice;
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);
                    $invoice->updatePaymentStatus(true);
                    $StatusModel = \Modules\Services\App\Http\Models\Status::class;
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

            if (\Nwidart\Modules\Facades\Module::has('Services') && \Nwidart\Modules\Facades\Module::isEnabled('Services')) {
                $checkSetting = \Modules\Accounting\App\Models\AccountingSetting::get('general.check_cheque_due_dates', true);
                if ($checkSetting) {
                    $payment = \Modules\Services\App\Http\Models\Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', 'paid')
                        ->first();
                    if ($payment && $payment->invoice) {
                        $invoice = $payment->invoice;
                        $payment->update(['status' => 'pending']);
                        $invoice->updatePaymentStatus(true);
                        $StatusModel = \Modules\Services\App\Http\Models\Status::class;
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

            if (\Nwidart\Modules\Facades\Module::has('Services') && \Nwidart\Modules\Facades\Module::isEnabled('Services')) {
                $payment = \Modules\Services\App\Http\Models\Payment::where('method', 'cheque-' . $cheque->id)
                    ->where('status', '!=', 'canceled')
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
