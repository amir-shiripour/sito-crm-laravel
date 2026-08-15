<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\App\Http\Requests\StoreInvoiceRequest;
use Modules\Accounting\App\Http\Requests\UpdateInvoiceRequest;
use Modules\Accounting\App\Services\InvoiceService;
use Modules\Accounting\Entities\Invoice;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Clients\Entities\Client;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        // $this->authorize('accounting.invoices.view');
        $invoices = Invoice::with('customer')->latest()->paginate(20);
        return view('accounting::invoices.index', compact('invoices'));
    }

    public function create()
    {
        // $this->authorize('accounting.invoices.create');

        $customers = Client::all();

        $settings = AccountingSetting::all()->pluck('value', 'key');

        $units = $settings->get('units.list', ['عدد']);
        $currency = $settings->get('general.currency', 'ریال');

        $newInvoiceNumber = null;
        if ($settings->get('numbering.mode') === 'auto') {
            $prefix = $settings->get('numbering.prefix', 'INV');
            $separator = $settings->get('numbering.separator', '-');
            $length = (int)$settings->get('numbering.length', 4);
            $includeYear = (bool)$settings->get('numbering.include_year', true);

            $lastInvoice = Invoice::withTrashed()->latest('id')->first();
            $nextId = ($lastInvoice ? $lastInvoice->id : 0) + 1;

            $paddedNumber = str_pad($nextId, $length, '0', STR_PAD_LEFT);

            $jalaliDate = Jalalian::fromCarbon(now());
            $year = $includeYear ? $jalaliDate->getYear() . $separator : '';

            $newInvoiceNumber = $prefix . $separator . $year . $paddedNumber;
        }

        return view('accounting::invoices.create', compact('customers', 'units', 'currency', 'settings', 'newInvoiceNumber'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        // $this->authorize('accounting.invoices.create');
        try {
            $invoice = $this->invoiceService->store($request->validated());
            return redirect()->route('admin.accounting.invoices.show', $invoice)
                ->with('success', 'فاکتور با موفقیت ایجاد شد.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ایجاد فاکتور: ' . $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.view');
        // Eager load all necessary relations for the view
        $invoice->load('items', 'customer', 'document.transactions');

        $settings = AccountingSetting::all()->pluck('value', 'key');
        $currency = $settings->get('general.currency', 'ریال');

        return view('accounting::invoices.show', compact('invoice', 'settings', 'currency'));
    }

    public function edit(Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.edit');
        if ($invoice->status !== 'draft') {
            return redirect()->route('admin.accounting.invoices.show', $invoice)->with('error', 'فقط فاکتورهای پیش‌نویس قابل ویرایش هستند.');
        }
        $customers = Client::all();
        $settings = AccountingSetting::all()->pluck('value', 'key');
        $units = $settings->get('units.list', ['عدد']);
        $currency = $settings->get('general.currency', 'ریال');
        $invoice->load('items');
        return view('accounting::invoices.edit', compact('invoice', 'customers', 'units', 'currency', 'settings'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.edit');
        try {
            $this->invoiceService->update($invoice, $request->validated());
            return redirect()->route('admin.accounting.invoices.show', $invoice)
                ->with('success', 'فاکتور با موفقیت ویرایش شد.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش فاکتور: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.edit');

        DB::beginTransaction();
        try {
            if ($invoice->status === 'draft') {
                // Action: Approve the invoice
                $this->invoiceService->approve($invoice);
                $message = 'فاکتور با موفقیت به "فاکتور رسمی" تبدیل و سند حسابداری آن صادر شد.';

            } elseif ($invoice->status === 'approved') {
                // Action: Revert to draft
                // We use the accessor here, which is already loaded in the 'show' method.
                if ($invoice->transactions->isNotEmpty()) {
                    throw new Exception('امکان بازگردانی به پیش‌فاکتور وجود ندارد زیرا برای این فاکتور تراکنش مالی (رسید) ثبت شده است.');
                }

                // Delete the associated financial document
                if ($invoice->document) {
                    $invoice->document()->delete();
                }

                // Revert status and clear the official invoice number
                $invoice->update([
                    'status' => 'draft',
                    'invoice_number' => null,
                ]);

                $message = 'فاکتور با موفقیت به "پیش‌فاکتور" بازگردانی و سند مالی آن ابطال شد.';
            } else {
                throw new Exception('تغییر وضعیت برای این فاکتور مجاز نیست.');
            }

            DB::commit();
            return redirect()->route('admin.accounting.invoices.show', $invoice)->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }
    }


    public function destroy(Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.delete');
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'فقط فاکتورهای پیش‌نویس قابل حذف هستند.');
        }
        $invoice->delete();
        return redirect()->route('admin.accounting.invoices.index')->with('success', 'فاکتور با موفقیت حذف شد.');
    }

    public function print(Invoice $invoice)
    {
        // $this->authorize('accounting.invoices.view');

        $invoice->load('items', 'customer');

        $template = AccountingSetting::get('appearance.invoice_template', 'standard');

        $sellerInfo = [
            'name' => AccountingSetting::get('appearance.seller_name'),
            'economic_number' => AccountingSetting::get('appearance.economic_number'),
            'registration_number' => AccountingSetting::get('appearance.registration_number'),
            'national_id' => AccountingSetting::get('appearance.national_id'),
            'address' => AccountingSetting::get('appearance.address'),
            'phone_fax' => AccountingSetting::get('appearance.phone_fax'),
            'stamp_signature_image' => AccountingSetting::get('appearance.stamp_signature_image'),
            'custom_fields' => AccountingSetting::get('appearance.custom_fields', [])
        ];

        if ($template === 'official') {
            return view('accounting::invoices.print_official', compact('invoice', 'sellerInfo'));
        }

        return view('accounting::invoices.print', compact('invoice', 'sellerInfo'));
    }
}
