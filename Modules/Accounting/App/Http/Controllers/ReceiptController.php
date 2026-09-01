<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\App\Http\Requests\StoreReceiptRequest;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\Entities\Invoice;
use Modules\Accounting\App\Services\CurrencyService;
use Modules\Accounting\App\Services\ReceiptService;
use Modules\Accounting\App\Models\AccountingSetting;
use Exception;
use Modules\Services\App\Http\Models\Payment;
use Morilog\Jalali\Jalalian;
use Nwidart\Modules\Facades\Module;

class ReceiptController extends Controller
{
    protected ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    public function create(Request $request)
    {
        $invoice = null;
        $isForInvoice = false;
        $balanceDue = 0;
        $totalPaid = 0;

        if ($request->has('invoice_id')) {
            $invoice = Invoice::with('document.transactions')->findOrFail($request->invoice_id);
            $isForInvoice = true;

            // Use the new accessors to get financial data
            $totalPaid = $invoice->paid_amount;
            $balanceDue = $invoice->remaining_amount;
        }

        $categories = Category::where('status', true)->get();
        $fundAccounts = FundAccount::with('transactions')->where('status', true)->get()->map(function ($bank) {
            $bank->balance_val = (float)$bank->current_balance;
            return $bank;
        });
        $currencySuffix = CurrencyService::getBaseCurrency();

        $receivableCheques = \Modules\Accounting\Entities\Cheque::where('type', 'receivable')
            ->where('status', 'pending')
            ->get()
            ->filter(function ($cheque) {
                $hasRelatedInvoice = !empty($cheque->related_invoice_id);
                $hasAttachedInvoices = $cheque->attachedInvoices()->exists();
                $hasAttachedDocuments = $cheque->attachedDocuments()->exists();
                $hasExpenseDocuments = $cheque->expenseDocuments()->exists();

                $hasServicePayment = false;
                if (Module::has('Services') && Module::isEnabled('Services') && $cheque->created_at) {
                    $hasServicePayment = Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', '!=', 'canceled')
                        ->where('created_at', '>=', $cheque->created_at->subMinutes(5))
                        ->exists();
                }

                return !$hasRelatedInvoice && !$hasAttachedInvoices && !$hasAttachedDocuments && !$hasExpenseDocuments && !$hasServicePayment;
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

                $cheque->due_date_jalali = $cheque->due_date ? Jalalian::fromCarbon($cheque->due_date)->format('Y/m/d') : '—';
                $cheque->type_label = $cheque->type === 'receivable' ? 'چک دریافتی' : 'چک پرداختی';
                $cheque->status_label = $st['label'];
                $cheque->status_badge = $st['badge'];
                return $cheque;
            });

        return view('accounting::receipts.create', compact(
            'categories', 'fundAccounts', 'currencySuffix', 'invoice',
            'isForInvoice', 'balanceDue', 'totalPaid', 'receivableCheques'
        ));
    }

    public function store(StoreReceiptRequest $request)
    {
        try {
            $document = $this->receiptService->store($request->validated());

            return redirect()->route('admin.accounting.documents.show', $document->id)
                ->with('success', 'رسید دریافت وجه با موفقیت ثبت شد.');

        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت رسید: ' . $e->getMessage()]);
        }
    }
}
