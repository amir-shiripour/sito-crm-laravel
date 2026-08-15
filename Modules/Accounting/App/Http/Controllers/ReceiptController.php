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
        $fundAccounts = FundAccount::where('status', true)->get();
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
                if (\Nwidart\Modules\Facades\Module::has('Services') && \Nwidart\Modules\Facades\Module::isEnabled('Services')) {
                    $hasServicePayment = \Modules\Services\App\Http\Models\Payment::where('method', 'cheque-' . $cheque->id)
                        ->where('status', '!=', 'canceled')
                        ->exists();
                }

                return !$hasRelatedInvoice && !$hasAttachedInvoices && !$hasAttachedDocuments && !$hasExpenseDocuments && !$hasServicePayment;
            })
            ->values()
            ->map(function ($cheque) {
                $cheque->due_date_jalali = \Morilog\Jalali\Jalalian::fromCarbon($cheque->due_date)->format('Y/m/d');
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
