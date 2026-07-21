<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Http\Requests\StoreInvoiceRequest;
use Modules\Settings\Entities\Setting;
use Spatie\Browsershot\Browsershot;
use Morilog\Jalali\Jalalian;
use Modules\Services\App\Http\Models\Order;
use Carbon\Carbon;
use Modules\Workflows\Services\WorkflowEngine;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with('customer', 'status', 'service')
            ->whereNotNull('invoice_number')
            ->when(
                $request->search,
                fn($q, $s) => $q
                    ->where('invoice_number', 'like', "%$s%")
                    ->orWhere('client_name', 'like', "%$s%")
            )
            ->when($request->status_id, fn($q, $v) => $q->where('status_id', $v))
            ->when($request->payment_mode, fn($q, $v) => $q->where('payment_mode', $v))
            ->when($request->customer_id, fn($q, $v) => $q->where('customer_id', $v))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('issue_date', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('issue_date', '<=', $v))
            ->latest();

        $invoices = $query->paginate(20)->withQueryString();

        $invoices->each(function ($invoice) {
            $invoice->updatePaymentStatus(true);
            $invoice->load('status');
        });

        $statuses = Status::whereIn('type', ['invoice', 'payment'])->orderBy('sort_order')->get();

        $customers = Client::orderBy('full_name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::invoices.index', compact('invoices', 'statuses', 'customers', 'currency'));
    }

    public function proformas(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with('customer', 'status', 'service')
            ->whereNull('invoice_number')
            ->when(
                $request->search,
                fn($q, $s) => $q
                    ->where('proforma_invoice_number', 'like', "%$s%")
                    ->orWhere('client_name', 'like', "%$s%")
            )
            ->when($request->status_id, fn($q, $v) => $q->where('status_id', $v))
            ->latest();

        $proformas = $query->paginate(20)->withQueryString();

        $statuses = Status::whereIn('type', ['invoice', 'payment'])->orderBy('sort_order')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::proformas.index', compact('proformas', 'statuses', 'currency'));
    }

    /**
     * Dedicated route: GET services/invoices/create
     * Always renders the "invoice" form, no query param needed.
     */
    public function createInvoice(Request $request)
    {
        return $this->buildCreateView('invoice');
    }

    /**
     * Dedicated route: GET services/proformas/create
     * Always renders the "proforma" form, no query param needed.
     */
    public function createProforma(Request $request)
    {
        return $this->buildCreateView('proforma');
    }

    private function buildCreateView(string $type)
    {
        $this->authorize('create', Invoice::class);

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['payment_currency'] ?? 'toman';
        $defaultTaxRate = $settings['services_default_tax_rate'] ?? 9;
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';
        $taxApplyCustomFields = !empty($settings['services_tax_apply_custom_fields']);

        $invoiceStatuses = Status::whereIn('type', ['invoice', 'payment'])->orderBy('sort_order')->get();

        $proformaAuto = !empty($settings['services_proforma_invoice_auto']);
        $proformaInvoiceNumber = '';
        if ($proformaAuto) {
            $proformaInvoiceNumber = Invoice::generateProformaNumber();
        }

        $invoiceAuto = !empty($settings['services_invoice_auto_numbering']) || !empty($settings['services_invoice_auto']);
        $invoiceNumber = '';
        if ($invoiceAuto) {
            $invoiceNumber = Invoice::generateNumber();
        }

        return view('services::invoices.create', [
            'type' => $type,
            'services' => Service::active()->with('customFields')->orderBy('name')->get(),
            'customers' => Client::orderBy('full_name')->get(),
            'currency' => $currency,
            'settings' => $settings,
            'invoiceStatuses' => $invoiceStatuses,
            'proformaAuto' => $proformaAuto,
            'proformaInvoiceNumber' => $proformaInvoiceNumber,
            'invoiceAuto' => $invoiceAuto,
            'invoiceNumber' => $invoiceNumber,
            'defaultTaxRate' => $defaultTaxRate,
            'taxMode' => $taxMode,
            'taxApplyCustomFields' => $taxApplyCustomFields,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $isProforma = $data['invoice_type'] === 'proforma';

        $settings = Setting::pluck('value', 'key')->toArray();
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';
        $taxApplyCustomFields = !empty($settings['services_tax_apply_custom_fields']);

        [$preparedItems, $subtotal, $totalDiscount, $itemsTotal, $itemsTaxTotal] = $this->buildItems($data['items'], $taxMode, $taxApplyCustomFields);
        $extraDiscount = $this->computeExtraDiscount($data, $itemsTotal);
        [$totalTax, $grandTotal] = $this->applyInvoiceTax($itemsTotal, $data['tax_percent'] ?? 0, $extraDiscount, $taxMode, $itemsTaxTotal);
        $totalDiscount += $extraDiscount;

        if ($taxMode === 'item') {
            $data['tax_percent'] = 0;
        }

        $invoiceData = $this->buildInvoiceData(
            $data, $request->user()->id,
            $subtotal, $totalDiscount, $totalTax, $grandTotal,
            $isProforma
        );

        $invoice = null;

        DB::transaction(function () use (&$invoice, $invoiceData, $preparedItems, $data, $grandTotal, $isProforma) {
            $invoice = Invoice::create($invoiceData);
            $invoice->items()->createMany($preparedItems);
            $invoice->updatePaymentStatus(false);

            if (!$isProforma) {
                $this->syncOrdersForInvoice($invoice, $preparedItems);
            }
        });

        $invoice->save();

        if (class_exists(WorkflowEngine::class)) {
            try {
                app(WorkflowEngine::class)->start('invoice_created', 'INVOICE', $invoice->id, [
                    'is_proforma' => $isProforma,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Workflows] Error starting invoice_created workflow: ' . $e->getMessage());
            }
        }

        $message = $isProforma ? 'پیش فاکتور با موفقیت صادر شد.' : 'فاکتور با موفقیت صادر شد.';

        return redirect()
            ->route('services.invoices.show', $invoice)
            ->with('success', $message);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->updatePaymentStatus(true);
        $invoice->load('status');

        $invoice->load('items.service.customFields', 'customer', 'status', 'activities.user');

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? 'toman';
        $activePaymentMethods = $this->activePaymentMethods($settings);
        $zarinpalActive = !empty($settings['zarinpal_active']);
        $zibalActive = !empty($settings['zibal_active']);
        $behpardakhtActive = !empty($settings['behpardakht_active']);
        $defaultGateway = $settings['default_gateway'] ?? 'zarinpal';

        $invoiceStatuses = Status::whereIn('type', ['invoice', 'payment'])->orderBy('sort_order')->get();
        $paymentStatuses = Status::where('type', 'payment')->orderBy('sort_order')->get();

        $canPay = $invoice->status && $invoice->status->allowsPayment();

        return view('services::invoices.show', compact(
            'invoice', 'currency', 'settings',
            'activePaymentMethods',
            'zarinpalActive', 'zibalActive', 'behpardakhtActive',
            'defaultGateway', 'invoiceStatuses', 'paymentStatuses',
            'canPay'
        ));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status && $invoice->status->locksInvoice()) {
            return redirect()->route('services.invoices.show', $invoice)
                ->with('error', 'این فاکتور قفل شده و قابل ویرایش نیست.');
        }

        $invoice->load('items.service.customFields');

        $settings = Setting::pluck('value', 'key')->toArray();
        $inst = $this->installmentSettings($settings);

        $invoiceStatuses = Status::whereIn('type', ['invoice', 'payment'])->orderBy('sort_order')->get();
        $paymentStatuses = Status::where('type', 'payment')->orderBy('sort_order')->get();
        $isProforma = !$invoice->invoice_number;

        return view('services::invoices.edit', [
            'invoice' => $invoice,
            'isProforma' => $isProforma,
            'services' => Service::active()->with('customFields')->orderBy('name')->get(),
            'customers' => Client::orderBy('full_name')->get(),
            'currency' => $settings['payment_currency'] ?? 'toman',
            'settings' => $settings,
            'installmentTypes' => $inst['types'],
            'installmentDueDays' => $inst['dueDays'],
            'roundingMode' => $inst['mode'],
            'roundingFactor' => $inst['factor'],
            'invoiceStatuses' => $invoiceStatuses,
            'paymentStatuses' => $paymentStatuses,
            'defaultTaxRate' => $settings['services_default_tax_rate'] ?? 9,
            'taxMode' => $settings['services_tax_mode'] ?? 'invoice',
            'taxApplyCustomFields' => !empty($settings['services_tax_apply_custom_fields']),
        ]);
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->status && $invoice->status->locksInvoice()) {
            return redirect()->route('services.invoices.show', $invoice)
                ->with('error', 'این فاکتور قفل شده و قابل ویرایش نیست.');
        }

        $data = $request->validated();

        $settings = Setting::pluck('value', 'key')->toArray();
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';
        $taxApplyCustomFields = !empty($settings['services_tax_apply_custom_fields']);

        [$preparedItems, $subtotal, $totalDiscount, $itemsTotal, $itemsTaxTotal] = $this->buildItems($data['items'], $taxMode, $taxApplyCustomFields);
        $extraDiscount = $this->computeExtraDiscount($data, $itemsTotal);
        [$totalTax, $grandTotal] = $this->applyInvoiceTax($itemsTotal, $data['tax_percent'] ?? 0, $extraDiscount, $taxMode, $itemsTaxTotal);
        $totalDiscount += $extraDiscount;

        if ($taxMode === 'item') {
            $data['tax_percent'] = 0;
        }

        $invoiceData = [
            'proforma_invoice_number' => $data['proforma_invoice_number'] ?? $invoice->proforma_invoice_number,
            'customer_id' => $data['customer_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_email' => $data['client_email'] ?? null,
            'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
            'due_date' => $data['due_date'] ?? $invoice->due_date,
            'subtotal' => (int)round($subtotal),
            'discount_amount' => $totalDiscount,
            'tax_percent' => (float)($data['tax_percent'] ?? 0),
            'tax_amount' => (int)round($totalTax),
            'total' => (int)round($grandTotal),
            'notes' => $data['notes'] ?? null,
            'payment_mode' => $data['payment_mode'] ?? $invoice->payment_mode,
            'payment_method' => $data['payment_method'] ?? $invoice->payment_method,
            'payment_gateway' => $data['gateway'] ?? $invoice->payment_gateway,
            'installment_down_payment' => $data['installment_down_payment'] ?? $invoice->installment_down_payment,
            'installment_steps' => $data['installment_steps'] ?? $invoice->installment_steps,
            'installment_interest_rate' => $data['installment_interest_rate'] ?? $invoice->installment_interest_rate,
            'installment_option_id' => $data['installment_option_id'] ?? $invoice->installment_option_id,
            'installment_option_title' => $data['installment_option_title'] ?? $invoice->installment_option_title,
            'installment_start_date' => $data['installment_start_date'] ?? $invoice->installment_start_date,
            'installment_schedule' => isset($data['installment_schedule'])
                ? json_decode($data['installment_schedule'], true)
                : $invoice->installment_schedule,
        ];

        DB::transaction(function () use ($invoice, $invoiceData, $preparedItems, $data) {
            $invoice->update($invoiceData);
            $invoice->items()->delete();
            $invoice->items()->createMany($preparedItems);

            if ($invoiceData['payment_mode'] === 'installment') {
                $this->syncInstallmentStatus($invoice);
            } else {
                $invoice->updatePaymentStatus(false);
            }

            if ($invoice->invoice_number) {
                $this->syncOrdersForInvoice($invoice, $preparedItems);
            }
        });

        $invoice->save();

        return redirect()
            ->route('services.invoices.show', $invoice)
            ->with('success', 'فاکتور ویرایش شد.');
    }

    public function createPayment(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $invoice->load('items.service', 'customer', 'status');
        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['payment_currency'] ?? 'toman';

        return view('services::invoices.payment', [
            'invoice' => $invoice,
            'currency' => $currency,
            'settings' => $settings,
        ]);
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_method' => 'required|string',
            'gateway'        => 'nullable|string',
            'amount'         => 'required|numeric|min:1',
            'paid_at'        => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $newPaidAmount = (int)str_replace(',', '', $request->amount);
        $payment = null;

        // به‌روز رسانی paid_amount و وضعیت فاکتور به صورت بومی
        DB::transaction(function () use ($invoice, $request, $newPaidAmount, &$payment) {
            $paidAt = now();
            if ($request->filled('paid_at')) {
                try {
                    $paidAt = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->paid_at)->toCarbon();
                } catch (\Exception $e) {}
            }

            $payment = $invoice->payments()->create([
                'user_id'        => $request->user()->id,
                'amount'         => $newPaidAmount,
                'method'         => $request->payment_method,
                'gateway'        => $request->gateway,
                'paid_at'        => $paidAt,
                'transaction_id' => $request->transaction_id,
                'notes'          => 'پرداخت برای فاکتور #' . $invoice->invoice_number,
            ]);

            $invoice->paid_amount = $invoice->calculatePaidAmount();
            
            // محاسبه وضعیت بومی
            $StatusModel = \Modules\Services\App\Http\Models\Status::class;
            if ($invoice->isPaid()) {
                $status = $StatusModel::where('name', 'پرداخت شده')->where('type', 'payment')->first();
            } elseif ($invoice->isOverdue()) {
                $status = $StatusModel::where('name', 'معوقه')->where('type', 'payment')->first();
            } else {
                $status = $StatusModel::where('name', 'در انتظار پرداخت')->where('type', 'payment')->first();
            }

            if ($status) {
                $invoice->status_id = $status->id;
            }
            $invoice->save();
        });

        // شلیک رویدادهای فاکتور به جای پرداخت
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            try {
                $eventKey = $invoice->isPaid() ? 'invoice_paid' : 'invoice_unpaid';
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start($eventKey, 'INVOICE', $invoice->id, [
                    'amount'      => $payment->amount,
                    'is_paid'     => $invoice->isPaid(),
                    'is_overdue'  => $invoice->isOverdue(),
                    'remaining'   => $invoice->remainingAmount(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Workflows] Error starting workflow: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('services.invoices.show', $invoice)
            ->with('success', 'پرداخت با موفقیت ثبت شد.');
    }

    public function cancelPayment(Request $request, Invoice $invoice, Payment $payment)
    {
        $this->authorize('update', $invoice);

        if ($payment->invoice_id !== $invoice->id) {
            return back()->with('error', 'این پرداخت متعلق به این فاکتور نیست.');
        }

        if ($payment->status === 'canceled') {
            return back()->with('error', 'این پرداخت قبلاً لغو شده است.');
        }

        DB::transaction(function () use ($invoice, $payment) {
            $payment->update(['status' => 'canceled']);
            
            // به‌روز رسانی paid_amount
            $invoice->paid_amount = $invoice->calculatePaidAmount();
            
            // محاسبه وضعیت بومی
            $StatusModel = \Modules\Services\App\Http\Models\Status::class;
            if ($invoice->isPaid()) {
                $status = $StatusModel::where('name', 'پرداخت شده')->where('type', 'payment')->first();
            } elseif ($invoice->isOverdue()) {
                $status = $StatusModel::where('name', 'معوقه')->where('type', 'payment')->first();
            } else {
                $status = $StatusModel::where('name', 'در انتظار پرداخت')->where('type', 'payment')->first();
            }

            if ($status) {
                $invoice->status_id = $status->id;
            }
            $invoice->save();
        });

        // شلیک رویدادهای فاکتور به جای پرداخت
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            try {
                $eventKey = $invoice->isPaid() ? 'invoice_paid' : 'invoice_unpaid';
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start($eventKey, 'INVOICE', $invoice->id, [
                    'amount'      => $payment->amount,
                    'is_paid'     => $invoice->isPaid(),
                    'is_overdue'  => $invoice->isOverdue(),
                    'remaining'   => $invoice->remainingAmount(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Workflows] Error starting workflow: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'پرداخت با موفقیت لغو شد.');
    }

    private function syncInstallmentStatus(Invoice $invoice)
    {
        $schedule = $invoice->installment_schedule;
        $allPaid = true;
        $paidAmount = 0;

        $paidStatus = Status::where('type', 'payment')->where('name', 'paid')->first();

        if (is_array($schedule)) {
            foreach ($schedule as $inst) {
                if (isset($inst['status_id']) && $paidStatus && $inst['status_id'] == $paidStatus->id) {
                    $paidAmount += $inst['amount'] ?? 0;
                } else {
                    $allPaid = false;
                }
            }
        }

        $invoice->paid_amount = $paidAmount;
        $invoice->updatePaymentStatus(false);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate(['status_id' => 'required|exists:services_statuses,id']);

        $status = Status::find($request->status_id);

        if (!$status) {
            return back()->with('error', 'وضعیت نامعتبر است.');
        }

        if (!empty($status->allowed_roles) && !$request->user()->hasAnyRole($status->allowed_roles)) {
            return back()->with('error', 'شما اجازه تغییر به این وضعیت را ندارید.');
        }

        $invoice->status_id = $status->id;

        if ($status->convertsToInvoice() && !$invoice->invoice_number) {
            $settings = Setting::pluck('value', 'key')->toArray();
            $invoiceAuto = !empty($settings['services_invoice_auto_numbering']) || !empty($settings['services_invoice_auto']);
            $invoiceNumber = $invoiceAuto ? Invoice::generateNumber() : null;

            if (!$invoiceNumber) {
                return back()->with('error', 'شماره‌گذاری خودکار فاکتور فعال نیست. لطفاً از طریق صفحه فاکتور اقدام به تبدیل نمایید.');
            }

            $invoice->invoice_number = $invoiceNumber;
            $invoice->converted_at = now();
            // ساخت سفارشات پس از تغییر وضعیت به فاکتور
            $this->syncOrdersForInvoice($invoice);
        }

        $invoice->save();

        if (class_exists(WorkflowEngine::class)) {
            try {
                app(WorkflowEngine::class)->start('invoice_status_changed', 'INVOICE', $invoice->id, [
                    'new_status_id'   => $status->id,
                    'new_status_name' => $status->name,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Workflows] Error starting invoice_status_changed workflow: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'وضعیت فاکتور به‌روز شد.');
    }

    public function cancel(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // بررسی مجوز لغو (role check)
        $cancelledStatus = Status::where('type', 'payment')
            ->where('name', 'لغو شده')
            ->first()
            ?? Status::where('type', 'payment')
                ->where('name', 'LIKE', '%لغو%')
                ->first();

        if (!$cancelledStatus) {
            return back()->with('error', 'وضعیت "لغو شده" در بخش پرداخت تعریف نشده است. لطفاً ابتدا آن را بسازید.');
        }

        if (!empty($cancelledStatus->allowed_roles) && !auth()->user()->hasAnyRole($cancelledStatus->allowed_roles)) {
            return back()->with('error', 'شما اجازه لغو این فاکتور را ندارید.');
        }

        // Status changes are delegated to the Workflow Engine.
        // We only fire the event here.

        // فراخوانی WorkflowEngine برای invoice_cancelled جهت اجرای اتوماسیون‌های گردش کار
        // (مثلای لغو سفارش‌های مرتبط)
        if (class_exists(WorkflowEngine::class)) {
            try {
                $invoice->refresh();
                app(WorkflowEngine::class)->start('invoice_cancelled', 'INVOICE', $invoice->id, [
                    'cancelled_status_id'   => $cancelledStatus->id,
                    'cancelled_status_name' => $cancelledStatus->name,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Workflows] Error starting invoice_cancelled workflow: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'فاکتور با موفقیت لغو شد و تمام پرداخت‌های آن نیز لغو گردید.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if ($invoice->invoice_number) {
            return redirect()
                ->back()
                ->with('error', 'فاکتور نهایی قابل حذف نیست. در صورت نیاز، وضعیت آن را به "لغو شده" تغییر دهید.');
        }

        $invoice->delete();

        return redirect()
            ->route('services.invoices.index')
            ->with('success', 'پیش فاکتور با موفقیت حذف شد.');
    }

    public function printView(Request $request, Invoice $invoice)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'لینک نامعتبر یا منقضی شده است.');
        }

        $invoice->load('items.service.customFields', 'customer', 'status');

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? 'toman';
        $sellerInfo = $this->sellerInfo();
        [$siteName, $appLogo] = $this->siteBrand($settings, $sellerInfo);
        $paymentStatuses = Status::where('type', 'payment')->orderBy('sort_order')->get();
        $printMode = $settings['services_print_mode'] ?? 'standard';
        $viewName = $printMode === 'official' ? 'services::invoices.print_official' : 'services::invoices.print';
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';

        return view($viewName, compact('invoice', 'currency', 'sellerInfo', 'paymentStatuses', 'siteName', 'appLogo', 'taxMode'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        set_time_limit(300);
        @ini_set('memory_limit', '512M');
        $invoice->load('items.service.customFields', 'customer', 'status');

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? 'toman';
        $sellerInfo = $this->sellerInfo();
        [$siteName, $appLogo] = $this->siteBrand($settings, $sellerInfo);
        $paymentStatuses = Status::where('type', 'payment')->orderBy('sort_order')->get();
        $printMode = $settings['services_print_mode'] ?? 'standard';
        $viewName = $printMode === 'official' ? 'services::invoices.print_official' : 'services::invoices.print';
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';

        $html = view($viewName, compact('invoice', 'currency', 'sellerInfo', 'paymentStatuses', 'siteName', 'appLogo', 'taxMode'))->render();
        $browsershot = Browsershot::html($html);

        if (PHP_OS_FAMILY === 'Windows') {
            $browsershot->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
                ->setNpmBinary('C:\\Program Files\\nodejs\\npm.cmd')
                ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        } else {
            $browsershot->noSandbox()
                ->setNodeModulePath(base_path('node_modules'))
                ->setChromePath('/usr/bin/google-chrome');
        }

        $pdf = $browsershot
            ->format('A4')
            ->margins(15, 10, 15, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        $filename = 'invoice-' . ($invoice->invoice_number ?: $invoice->proforma_invoice_number) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_mode' => 'required|in:cash,installment',
            'payment_method' => 'nullable|in:online,transfer,pos,installment',
            'gateway' => 'nullable|in:zarinpal,zibal,behpardakht',
            'installment_down_payment' => 'nullable|integer|min:0',
            'installment_steps' => 'nullable|integer|min:1|max:60',
            'installment_interest_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $mode = $request->payment_mode;

        if ($mode === 'installment') {
            $invoice->update([
                'payment_mode' => 'installment',
                'payment_method' => 'installment',
                'installment_down_payment' => $request->installment_down_payment ?? 0,
                'installment_steps' => $request->installment_steps ?? 1,
                'installment_interest_rate' => $request->installment_interest_rate ?? 0,
            ]);

            return back()->with('success', 'برنامه اقساطی با موفقیت ثبت شد.');
        }

        $method = $request->payment_method ?? 'transfer';

        if ($method === 'online') {
            return back()->with('error', 'درگاه اینترنتی هنوز پیاده‌سازی نشده.');
        }

        $invoice->update([
            'payment_mode' => 'cash',
            'payment_method' => $method,
            'paid_amount' => $invoice->total,
        ]);

        $invoice->updatePaymentStatus();

        return back()->with('success', 'پرداخت با موفقیت ثبت شد.');
    }

    public function verify(Request $request, string $gateway)
    {
        return redirect()->route('services.invoices.index');
    }

    public function convertToInvoice(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->invoice_number) {
            return back()->with('error', 'این پیش‌فاکتور قبلاً به فاکتور تبدیل شده است.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $invoiceAuto = !empty($settings['services_invoice_auto_numbering']) || !empty($settings['services_invoice_auto']);
        $invoiceNumber = $request->invoice_number;

        if ($invoiceAuto && !$invoiceNumber) {
            $invoiceNumber = Invoice::generateNumber();
        } elseif (!$invoiceNumber) {
            return back()->with('error', 'شماره فاکتور ارائه نشده و شماره‌گذاری خودکار نیز غیرفعال است.');
        }

        if (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            return back()->with('error', 'این شماره فاکتور قبلاً استفاده شده است.');
        }

        $invoice->invoice_number = $invoiceNumber;
        $invoice->converted_at = now();

        $status = Status::where('attributes->converts_to_invoice', true)->first();
        if ($status) {
            $invoice->status_id = $status->id;
        }

        $invoice->save();
        $this->syncOrdersForInvoice($invoice);

        return redirect()
            ->route('services.invoices.show', $invoice)
            ->with('success', 'پیش‌فاکتور با موفقیت به فاکتور تبدیل شد.');
    }

    private function buildItems(array $items, string $taxMode = 'invoice', bool $taxApplyCustomFields = false): array
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $itemsTotal = 0;
        $itemsTaxTotal = 0;
        $prepared = [];
        $serviceIds = collect($items)->pluck('service_id')->filter()->unique()->values();

        $services = $serviceIds->isNotEmpty()
            ? Service::with('customFields')->whereIn('id', $serviceIds)->get()->keyBy('id')
            : collect();

        foreach ($items as $item) {
            $qty = (float)$item['quantity'];
            $price = (int)$item['unit_price'];
            $discount = (int)($item['discount'] ?? 0);
            $billingPeriod = $item['billing_period'] ?? null;

            $customFieldsValues = $item['custom_fields'] ?? [];
            $customFieldsPrices = $item['custom_fields_prices'] ?? [];
            $customFieldsDiscounts = $item['custom_fields_discounts'] ?? [];
            $customFieldsTaxes = $item['custom_fields_taxes'] ?? [];

            $customFieldsUnitPrice = 0;
            $customFieldsDiscount = 0;
            $customFieldsTaxTotal = 0;

            if (!empty($item['service_id']) && $services->has((int)$item['service_id'])) {
                $service = $services->get((int)$item['service_id']);

                foreach ($service->customFields as $field) {
                    if (!$field->has_pricing) {
                        continue;
                    }

                    $val = $customFieldsValues[$field->id] ?? null;

                    $isSelected = match ($field->type) {
                        'checkbox' => in_array($val, [true, '1', 1], true),
                        'multiselect' => is_array($val) && count($val) > 0,
                        default => ($val !== null && $val !== ''),
                    };

                    if (!$isSelected) {
                        continue;
                    }

                    if (isset($customFieldsPrices[$field->id])) {
                        $amount = (float)$customFieldsPrices[$field->id];
                    } else {
                        $amount = $field->pricing_type === 'percentage'
                            ? $price * ((float)($field->pricing_amount ?? 0) / 100)
                            : (float)($field->pricing_amount ?? 0);
                    }

                    $fieldDiscount = (int)($customFieldsDiscounts[$field->id] ?? 0);

                    $customFieldsUnitPrice += $amount;
                    $customFieldsDiscount += $fieldDiscount;

                    // ── مالیات مستقل فیلد سفارشی ──
                    if ($taxMode === 'item' && $taxApplyCustomFields) {
                        $cfTaxPercent = (float)($customFieldsTaxes[$field->id] ?? 0);
                        $cfBase = max(0, ($amount * $qty) - $fieldDiscount);
                        $customFieldsTaxTotal += $cfBase * ($cfTaxPercent / 100);
                    }
                }
            }

            $rowGross = ($price + $customFieldsUnitPrice) * $qty;
            $rowDiscount = $discount + $customFieldsDiscount;
            $rowBase = max(0, $rowGross - $rowDiscount);

            $subtotal += $rowGross;
            $totalDiscount += $rowDiscount;
            $itemsTotal += $rowBase;

            $rowTaxPercent = 0;
            $rowTaxAmount = 0;

            if ($taxMode === 'item') {
                $rowTaxPercent = (float)($item['tax_percent'] ?? 0);

                // مالیات ردیف فقط روی مبلغ پایه سرویس (بدون فیلدهای سفارشی)
                $rowTaxableBase = max(0, ($price * $qty) - $discount);
                $rowTaxAmount = $rowTaxableBase * ($rowTaxPercent / 100);

                // اضافه کردن مالیات فیلدهای سفارشی
                $itemsTaxTotal += $rowTaxAmount + $customFieldsTaxTotal;
            }

            $prepared[] = [
                'service_id' => $item['service_id'] ?? null,
                'custom_service_name' => $item['custom_service_name'] ?? null,
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'عدد',
                'quantity' => $qty,
                'unit_price' => $price,
                'discount' => $discount,
                'tax_percent' => $rowTaxPercent,
                'tax_amount' => (int)round($rowTaxAmount + $customFieldsTaxTotal),
                'total' => (int)round($rowBase),
                'meta' => [
                    'billing_period' => $billingPeriod,
                    'custom_fields' => $customFieldsValues,
                    'custom_fields_prices' => $customFieldsPrices,
                    'custom_fields_discounts' => $customFieldsDiscounts,
                    'custom_fields_taxes' => $customFieldsTaxes,
                ],
            ];
        }

        return [$prepared, $subtotal, $totalDiscount, $itemsTotal, $itemsTaxTotal];
    }
    private function computeExtraDiscount(array $data, float $itemsTotal): int
    {
        $type = $data['extra_discount_type'] ?? 'amount';
        $value = (float)($data['extra_discount_value'] ?? 0);

        if ($value <= 0 || $itemsTotal <= 0) {
            return 0;
        }

        $discount = $type === 'percent'
            ? $itemsTotal * ($value / 100)
            : $value;

        return (int)round(min(max(0, $discount), $itemsTotal));
    }

    private function applyInvoiceTax(float $itemsTotal, mixed $taxPercent, int $extraDiscount = 0, string $taxMode = 'invoice', float $itemsTaxTotal = 0): array
    {
        $taxableAmount = max(0, $itemsTotal - $extraDiscount);

        if ($taxMode === 'item') {
            $totalTax = $itemsTaxTotal;
        } else {
            $taxPercent = (float)$taxPercent;
            $totalTax = $taxableAmount * ($taxPercent / 100);
        }

        $grandTotal = $taxableAmount + $totalTax;

        return [$totalTax, $grandTotal];
    }

    private function buildInvoiceData(
        array $data,
        int   $userId,
        float $subtotal,
        float $totalDiscount,
        float $totalTax,
        float $grandTotal,
        bool  $isProforma = false
    ): array
    {
        $invoiceNumber = $isProforma ? null : ($data['invoice_number'] ?? Invoice::generateNumber());
        $proformaInvoiceNumber = $isProforma ? ($data['proforma_invoice_number'] ?? Invoice::generateProformaNumber()) : null;

        $defaultStatus = \Modules\Services\App\Http\Models\Status::where('type', 'invoice')
            ->where('is_default', 1)
            ->first();
        $statusId = $defaultStatus ? $defaultStatus->id : null;

        return [
            'status_id' => $statusId,
            'invoice_number' => $invoiceNumber,
            'proforma_invoice_number' => $proformaInvoiceNumber,
            'customer_id' => $data['customer_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_email' => $data['client_email'] ?? null,
            'created_by' => $userId,
            'issue_date' => $data['issue_date'] ?? now(),
            'due_date' => $data['due_date'] ?? null,
            'subtotal' => (int)round($subtotal),
            'discount_amount' => $totalDiscount,
            'tax_percent' => (float)($data['tax_percent'] ?? 0),
            'tax_amount' => (int)round($totalTax),
            'total' => (int)round($grandTotal),
            'notes' => $data['notes'] ?? null,
            'payment_mode' => $data['payment_mode'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_gateway' => $data['gateway'] ?? null,
            'installment_down_payment' => $data['installment_down_payment'] ?? 0,
            'installment_steps' => $data['installment_steps'] ?? 0,
            'installment_interest_rate' => $data['installment_interest_rate'] ?? 0,
            'installment_option_id' => $data['installment_option_id'] ?? null,
            'installment_option_title' => $data['installment_option_title'] ?? null,
            'installment_start_date' => $data['installment_start_date'] ?? null,
            'installment_schedule' => isset($data['installment_schedule'])
                ? json_decode($data['installment_schedule'], true)
                : null,
        ];
    }

    private function sellerInfo(): array
    {
        $s = Setting::all()->pluck('value', 'key');

        $pick = function (array $keys) use ($s) {
            foreach ($keys as $key) {
                if (!empty($s[$key])) {
                    return $s[$key];
                }
            }
            return null;
        };

        $customFieldsRaw = $pick(['identity_custom_fields', 'seller_custom_fields']);
        $customFields = [];
        if ($customFieldsRaw) {
            $decoded = json_decode($customFieldsRaw, true);
            if (is_array($decoded)) {
                $customFields = array_values(array_filter(
                    $decoded,
                    fn ($field) => !empty($field['value'] ?? null)
                ));
            }
        }

        return [
            'name' => $pick(['identity_name', 'seller_name', 'company_name']) ?? '',
            'economic_number' => $pick([
                    'identity_economic_code',
                    'identity_economic_number',
                    'seller_economic_number',
                    'economic_number',
                ]) ?? '',
            'national_id' => $pick(['identity_national_id', 'seller_national_id', 'national_id']) ?? '',
            'registration_number' => $pick(['identity_registration_number', 'seller_registration_number', 'registration_number']) ?? '',
            'phone_fax' => $pick(['identity_phone_fax', 'seller_phone_fax', 'phone_fax']) ?? '',
            'address' => $pick([
                    'identity_full_address',
                    'identity_address',
                    'seller_address',
                    'address',
                ]) ?? '',
            'stamp_signature_image' => $pick(['identity_seal_signature', 'seller_stamp_signature', 'stamp_signature_image']),
            'custom_fields' => $customFields,
        ];
    }
    private function siteBrand(array $settings, array $sellerInfo): array
    {
        $pick = function (array $keys) use ($settings) {
            foreach ($keys as $key) {
                if (!empty($settings[$key])) {
                    return $settings[$key];
                }
            }
            return null;
        };

        $siteName = $pick(['identity_site_name', 'site_name', 'app_name', 'identity_name'])
            ?: ($sellerInfo['name'] ?: 'فاکتور');

        $appLogo = $pick(['identity_logo', 'site_logo', 'app_logo', 'company_logo']);

        return [$siteName, $appLogo];
    }

    private function installmentSettings(array $settings): array
    {
        $types = $settings['installment_types'] ?? '[]';
        $types = is_string($types) ? json_decode($types, true) : $types;
        if (!is_array($types)) $types = [];

        $dueDaysRaw = $settings['installment_due_days'] ?? '[]';
        $dueDays = is_string($dueDaysRaw) ? json_decode($dueDaysRaw, true) : $dueDaysRaw;
        if (!is_array($dueDays)) $dueDays = [];
        $dueDays = array_values(array_map('intval', $dueDays));

        $mode = strtolower(trim((string)($settings['installment_rounding_mode'] ?? 'none')));
        if (!in_array($mode, ['none', 'up', 'down'], true)) $mode = 'none';

        $factor = (int)($settings['installment_rounding_factor'] ?? 1000);

        return compact('types', 'dueDays', 'mode', 'factor');
    }

    private function activePaymentMethods(array $settings): array
    {
        $methods = [];

        if (!empty($settings['zarinpal_active']) || !empty($settings['zibal_active']) || !empty($settings['behpardakht_active'])) {
            $methods[] = 'online';
        }
        if (!empty($settings['transfer_active'])) {
            $methods[] = 'transfer';
        }
        if (!empty($settings['pos_active'])) {
            $methods[] = 'pos';
        }
        if (!empty($settings['installment_active'])) {
            $methods[] = 'installment';
        }

        if (empty($methods)) {
            $methods = ['online', 'transfer', 'pos'];
        }

        return $methods;
    }

    private function syncOrdersForInvoice(Invoice $invoice, array $preparedItems = [])
    {
        if (!$invoice->invoice_number) return;

        if (empty($preparedItems)) {
            $preparedItems = $invoice->items->map(function ($item) {
                return [
                    'service_id' => $item->service_id,
                    'custom_service_name' => $item->custom_service_name,
                    'total' => max(0, ($item->unit_price * $item->quantity) - $item->discount),
                    'meta' => is_string($item->meta) ? json_decode($item->meta, true) : ($item->meta ?? []),
                ];
            })->toArray();
        }

        $existingOrders = Order::where('invoice_id', $invoice->id)->orderBy('id')->get();

        $orderStatus = Status::where('type', 'order')->where('name', 'در انتظار')->first()
            ?? Status::where('type', 'order')->first();

        foreach ($preparedItems as $index => $item) {
            $serviceId = $item['service_id'] ?? null;
            $service = $serviceId ? Service::find($serviceId) : null;

            // نام سرویس یا برای سرویس‌های دستی
            $customName = !empty($item['custom_service_name'])
                ? $item['custom_service_name']
                : ($service?->name ?? 'ردیف دستی');

            $renewalPrice = 0;
            $renewalDate = null;
            $billingCycle = $item['meta']['billing_period'] ?? null;

            if ($service && $service->billing_type === 'recurring' && $billingCycle) {
                $renewalPrice = $service->renewal_prices[$billingCycle] ?? 0;
                $issueDate = Carbon::parse($invoice->issue_date);
                switch ($billingCycle) {
                    case 'monthly':     $renewalDate = (clone $issueDate)->addMonth(); break;
                    case 'quarterly':   $renewalDate = (clone $issueDate)->addMonths(3); break;
                    case 'semi_annual': $renewalDate = (clone $issueDate)->addMonths(6); break;
                    case 'annual':      $renewalDate = (clone $issueDate)->addYear(); break;
                }
            }

            $orderData = [
                'order_number' => 'ORD-' . $invoice->id . '-' . ($index + 1),
                'invoice_id' => $invoice->id,
                'service_id' => $serviceId,
                'customer_id' => $invoice->customer_id,
                'created_by' => $invoice->created_by,
                'client_name' => $invoice->client_name,
                'client_phone' => $invoice->client_phone,
                'client_email' => $invoice->client_email,
                'issue_date' => $invoice->issue_date,
                'renewal_date' => $renewalDate,
                'billing_cycle' => $billingCycle,
                'first_payment_amount' => $item['total'],
                'total_amount' => $item['total'],
                'renewal_price' => $renewalPrice,
                'renewal_price_type' => 'auto',
                'notes' => $customName,
            ];

            $order = $existingOrders->get($index);

            if ($order) {
                $order->update($orderData);
            } else {
                $orderData['status_id'] = $orderStatus?->id ?? $invoice->status_id;
                $newOrder = Order::create($orderData);

                if (class_exists(WorkflowEngine::class)) {
                    try {
                        app(WorkflowEngine::class)->start('order_created', 'ORDER', $newOrder->id, [
                            'invoice_id' => $invoice->id,
                            'service_id' => $serviceId,
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('[Workflows] Error starting order_created workflow: ' . $e->getMessage());
                    }
                }
            }
        }

        if ($existingOrders->count() > count($preparedItems)) {
            for ($i = count($preparedItems); $i < $existingOrders->count(); $i++) {
                $existingOrders[$i]->delete();
            }
        }
    }
}
