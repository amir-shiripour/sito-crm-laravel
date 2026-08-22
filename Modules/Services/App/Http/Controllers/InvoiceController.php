<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\MarketOrderStatus;
use Modules\Market\App\Services\StockService;
use Modules\Market\App\Services\WarehouseStockService;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\WarehouseStock;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Http\Requests\StoreInvoiceRequest;
use Modules\Settings\Entities\Setting;
use Nwidart\Modules\Facades\Module;
use Spatie\Browsershot\Browsershot;
use Morilog\Jalali\Jalalian;
use Modules\Services\App\Http\Models\Order;
use Carbon\Carbon;
use Modules\Workflows\Services\WorkflowEngine;
use Modules\Market\App\Models\Order as MarketOrder;
use Modules\Market\App\Models\OrderItem as MarketOrderItem;
use Modules\Market\Entities\VendorProduct;

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
            ->when($request->customer_id, fn($q, $v) => $q->where('customer_id', $v))
            ->latest();

        $proformas = $query->paginate(20)->withQueryString();

        $customers = Client::orderBy('full_name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::proformas.index', compact('proformas', 'customers', 'currency'));
    }


    public function createInvoice(Request $request)
    {
        return $this->buildCreateView('invoice');
    }


    public function createProforma(Request $request)
    {
        return $this->buildCreateView('proforma');
    }

    private function isMarketModuleEnabled(): bool
    {
        return Module::has('Market')
            && Module::isEnabled('Market')
            && class_exists(MarketOrder::class);
    }


    private function getMarketAttributesForInvoice()
    {
        return $this->isMarketModuleEnabled() && class_exists(\Modules\Market\Entities\MarketAttribute::class)
            ? \Modules\Market\Entities\MarketAttribute::with('values')->orderBy('name')->get()
            : collect();
    }

    private function buildCreateView(string $type)
    {
        $this->authorize('create', Invoice::class);

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? $settings['payment_currency'] ?? 'toman';
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

        $services = Service::active()->with('customFields')->orderBy('name')->get();
        $products = $this->getProductsForInvoice();

        $mergedItems = [];
        $mergedFromIds = '';
        if (request()->has('merge_invoices')) {
            $ids = array_filter(explode(',', request('merge_invoices')));
            $mergedFromIds = implode(',', $ids);
            $invoices = Invoice::with('items')->whereIn('id', $ids)->get();
            foreach ($invoices as $inv) {
                foreach ($inv->items as $item) {
                    $service = $services->firstWhere('id', $item->service_id);
                    $meta = is_array($item->meta) ? $item->meta : (json_decode($item->meta, true) ?? []);

                    $isProduct = !empty($meta['product_id']) || (isset($meta['type']) && $meta['type'] === 'product');
                    $mode = $item->service_id ? 'service' : ($isProduct ? 'product' : 'manual');

                    $packageGroupId = $meta['_packageGroupId'] ?? $meta['package_group_id'] ?? null;
                    $packageTitle = $meta['_packageTitle'] ?? $meta['package_title'] ?? null;

                    if (!$packageGroupId && !empty($meta['package_id'])) {
                        $pkg = \Modules\Services\App\Http\Models\ServicePackage::find($meta['package_id']);
                        $packageGroupId = 'pkg_' . $inv->id . '_' . $meta['package_id'];
                        $packageTitle = $pkg ? $pkg->name : 'پکیج خدمات';
                    }

                    if (!$packageGroupId) {
                        $packageGroupId = 'merged_inv_' . $inv->id;
                        $packageTitle = 'اقلام فاکتور شماره ' . ($inv->invoice_number ?? $inv->id);
                    }

                    $stock = null;
                    if ($isProduct && !empty($products)) {
                        $prodMatch = collect($products)->first(function($p) use ($meta) {
                            if (!empty($meta['product_variant_id'])) {
                                return (string)($p['variant_id'] ?? '') === (string)$meta['product_variant_id'];
                            }
                            if (!empty($meta['product_id'])) {
                                return (string)($p['master_id'] ?? $p['id'] ?? '') === (string)$meta['product_id'];
                            }
                            return false;
                        });
                        if ($prodMatch) {
                            $stock = isset($prodMatch['stock']) ? (int)$prodMatch['stock'] : null;
                        }
                    }

                    $customFieldsArray = [];
                    $serviceRawArray = null;
                    if ($service) {
                        $serviceRawArray = $service->toArray();
                        $customFieldsArray = $service->customFields
                            ->filter(function($f) { return $f->show_in_invoice === true || $f->show_in_invoice === 1 || $f->show_in_invoice === '1' || $f->show_in_invoice === null; })
                            ->values()
                            ->map(function($f) {
                                if (is_string($f->options)) {
                                    try { $f->options = json_decode($f->options, true); } catch (\Exception $e) { $f->options = []; }
                                }
                                if (!is_array($f->options)) $f->options = [];
                                return $f;
                            })
                            ->toArray();
                    }

                    $mergedItems[] = [
                        'id' => uniqid() . rand(1000, 9999),
                        'mode' => $mode,
                        'service_id'  => $item->service_id ? (string) $item->service_id : '',
                        'product_id'  => isset($meta['product_id']) ? (string) $meta['product_id'] : '',
                        'product_variant_id' => isset($meta['product_variant_id']) ? (string) $meta['product_variant_id'] : '',
                        'stock'       => $stock,
                        'service_raw' => $serviceRawArray,
                        'custom_service_name' => $service ? $service->name : ($item->custom_service_name ?: $item->description ?? ''),
                        '_showServiceDropdown' => false,
                        '_showProductDropdown' => false,
                        '_selectedGroup' => '',
                        'description' => $item->description,
                        'unit'        => $item->unit ?? 'عدد',
                        'quantity'    => (float) $item->quantity,
                        'unit_price'  => (float) $item->unit_price,
                        'discount'    => (float) $item->discount,
                        'billing_period' => $meta['billing_period'] ?? null,
                        '_priceUnlocked' => false,
                        'service_custom_fields' => $customFieldsArray,
                        'custom_field_values' => $meta['custom_fields'] ?? [],
                        '_showCustomFields' => false,
                        'custom_field_custom_prices' => $meta['custom_fields_prices'] ?? [],
                        'custom_field_custom_discounts' => $meta['custom_fields_discounts'] ?? [],
                        'custom_field_tax_percents' => $meta['custom_fields_taxes'] ?? [],
                        'tax_percent' => $item->tax_percent > 0 ? (float) $item->tax_percent : ($defaultTaxRate ?? 9),
                        '_taxUnlocked' => false,
                        '_isMerged' => true,
                        '_packageGroupId' => $packageGroupId,
                        '_packageTitle' => $packageTitle,
                    ];
                }
            }
        }

        return view('services::invoices.create', [
            'type' => $type,
            'services' => Service::active()->with('customFields')->orderBy('name')->get(),
            'products' => $this->getProductsForInvoice(),
            'marketAttributes' => $this->getMarketAttributesForInvoice(),
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
            'servicesRoundingMode' => $settings['services_rounding_mode'] ?? 'none',
            'servicesRoundingFactor' => (int)($settings['services_rounding_factor'] ?? 1000),
            'marketModuleEnabled' => $this->isMarketModuleEnabled(),
            'mergedItems' => $mergedItems,
            'mergedFromIds' => $mergedFromIds,
            'packages' => \Modules\Services\App\Http\Models\ServicePackage::where('status', 'active')->with('items.service.customFields')->get(),
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
        $totalTax = $this->applyInvoiceTax($subtotal, $data['tax_percent'] ?? 0, $taxMode, $itemsTaxTotal);
        $extraDiscount = $this->computeExtraDiscount($data, $subtotal, $totalTax, $totalDiscount);
        $totalDiscount += $extraDiscount;
        $grandTotal = max(0, $subtotal + $totalTax - $totalDiscount);

        if ($taxMode === 'item') {
            $data['tax_percent'] = 0;
        }

        $data['issue_date'] = $this->parseJalaliToGregorian($data['issue_date'] ?? null) ?? now();
        $data['due_date'] = $this->parseJalaliToGregorian($data['due_date'] ?? null);
        if (isset($data['installment_start_date'])) {
            $data['installment_start_date'] = $this->parseJalaliToGregorian($data['installment_start_date']);
        }

        [$roundedGrandTotal, $roundingMeta] = $this->applyRounding($grandTotal, $settings);
        $currency = $settings['currency'] ?? $settings['payment_currency'] ?? 'toman';

        $invoiceData = $this->buildInvoiceData(
            $data, $request->user()->id,
            $subtotal, $totalDiscount, $totalTax, $roundedGrandTotal,
            $isProforma, $roundingMeta, $currency
        );

        $invoice = null;

        DB::transaction(function () use (&$invoice, $invoiceData, $preparedItems, $data, $grandTotal, $isProforma, $request) {
            $invoice = Invoice::create($invoiceData);
            $invoice->items()->createMany($preparedItems);

            if ($request->filled('merged_from_invoice_ids') && !$isProforma) {
                $sourceInvoiceIds = explode(',', $request->merged_from_invoice_ids);
                $sourceInvoices = Invoice::whereIn('id', $sourceInvoiceIds)->get();

                // Transfer payments
                Payment::whereIn('invoice_id', $sourceInvoiceIds)
                       ->update(['invoice_id' => $invoice->id]);

                // Mark old invoices as merged
                $mergedStatus = Status::where('type', 'invoice')->where('name', 'ادغام شده')->first();
                foreach ($sourceInvoices as $sourceInv) {
                    $meta = is_array($sourceInv->meta) ? $sourceInv->meta : (json_decode($sourceInv->meta, true) ?? []);
                    if (isset($meta['is_merged_invoice'])) unset($meta['is_merged_invoice']);
                    $meta['was_merged_into'] = $invoice->id;
                    $sourceInv->update([
                        'status_id' => $mergedStatus?->id ?? $sourceInv->status_id,
                        'meta' => $meta,
                    ]);
                }

                // Update new invoice meta
                $newMeta = is_array($invoice->meta) ? $invoice->meta : (json_decode($invoice->meta, true) ?? []);
                $newMeta['is_merged_invoice'] = true;
                $newMeta['merged_from_invoice_ids'] = $sourceInvoiceIds;
                $invoice->update(['meta' => $newMeta]);
            }

            $invoice->updatePaymentStatus(false);

            if (!$isProforma) {
                $this->syncOrdersForInvoice($invoice, $preparedItems);
            }
        });

        $invoice->save();

        if (!$isProforma && Module::has('Accounting') && Module::isEnabled('Accounting')) {
            try {
                app(\Modules\Accounting\App\Services\AccountingEngine::class)->recordFromServiceInvoice($invoice);
            } catch (\Throwable $e) {
                Log::error('[AccountingEngine] Error recording service invoice on store: ' . $e->getMessage());
            }
        }

        if (class_exists(WorkflowEngine::class)) {
            try {
                app(WorkflowEngine::class)->start('invoice_created', 'INVOICE', $invoice->id, [
                    'is_proforma' => $isProforma,
                ]);

                if ($invoice->isPaid() && !$isProforma) {
                    app(WorkflowEngine::class)->start('invoice_paid', 'INVOICE', $invoice->id, [
                        'amount'      => 0,
                        'is_paid'     => true,
                        'is_overdue'  => false,
                        'remaining'   => 0,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[Workflows] Error starting invoice_created workflow: ' . $e->getMessage());
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

        if (!$invoice->isEditable()) {
            return redirect()->route('services.invoices.show', $invoice)
                ->with('error', 'این فاکتور غیرقابل ویرایش است (پرداخت‌شده، لغو شده یا قفل شده).');
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
            'products' => $this->getProductsForInvoice(),
            'marketAttributes' => $this->getMarketAttributesForInvoice(),
            'customers' => Client::orderBy('full_name')->get(),
            'currency' => $settings['currency'] ?? $settings['payment_currency'] ?? 'toman',
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
            'servicesRoundingMode' => $settings['services_rounding_mode'] ?? 'none',
            'servicesRoundingFactor' => (int)($settings['services_rounding_factor'] ?? 1000),
            'marketModuleEnabled' => $this->isMarketModuleEnabled(),
            'packages' => \Modules\Services\App\Http\Models\ServicePackage::where('status', 'active')->with('items.service.customFields')->get(),
        ]);
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return redirect()->route('services.invoices.show', $invoice)
                ->with('error', 'این فاکتور غیرقابل ویرایش است (پرداخت‌شده، لغو شده یا قفل شده).');
        }

        $data = $request->validated();

        $settings = Setting::pluck('value', 'key')->toArray();
        $taxMode = $settings['services_tax_mode'] ?? 'invoice';
        $taxApplyCustomFields = !empty($settings['services_tax_apply_custom_fields']);

        [$preparedItems, $subtotal, $totalDiscount, $itemsTotal, $itemsTaxTotal] = $this->buildItems($data['items'], $taxMode, $taxApplyCustomFields);
        $totalTax = $this->applyInvoiceTax($subtotal, $data['tax_percent'] ?? 0, $taxMode, $itemsTaxTotal);
        $extraDiscount = $this->computeExtraDiscount($data, $subtotal, $totalTax, $totalDiscount);
        $totalDiscount += $extraDiscount;
        $grandTotal = max(0, $subtotal + $totalTax - $totalDiscount);

        if ($taxMode === 'item') {
            $data['tax_percent'] = 0;
        }

        $data['issue_date'] = $this->parseJalaliToGregorian($data['issue_date'] ?? null) ?? $invoice->issue_date;
        $data['due_date'] = $this->parseJalaliToGregorian($data['due_date'] ?? null) ?? $invoice->due_date;

        [$roundedGrandTotal, $roundingMeta] = $this->applyRounding($grandTotal, $settings);

        $existingMeta = is_array($invoice->meta) ? $invoice->meta : (json_decode($invoice->meta, true) ?? []);
        if (!empty($roundingMeta)) {
            $existingMeta['rounding'] = $roundingMeta;
        }
        if (isset($data['client_selected_fields']) && is_array($data['client_selected_fields'])) {
            $cleanedSelectedFields = [];
            foreach ($data['client_selected_fields'] as $fid => $vals) {
                if (is_array($vals)) {
                    $cleanedSelectedFields[$fid] = array_values(array_filter(array_map('trim', $vals)));
                } elseif (is_string($vals) && trim($vals) !== '') {
                    $cleanedSelectedFields[$fid] = [trim($vals)];
                }
            }
            $existingMeta['client_selected_fields'] = $cleanedSelectedFields;
        }

        $statusId = $invoice->status_id;
        if (!$invoice->proforma_invoice_number && (int)round($roundedGrandTotal) <= 0) {
            $paidStatus = Status::where('name', 'پرداخت شده')->first()
                ?? Status::where('name', 'LIKE', '%پرداخت شده%')->first();
            if ($paidStatus) {
                $statusId = $paidStatus->id;
            }
        }

        $invoiceData = [
            'status_id' => $statusId,
            'currency' => $data['currency'] ?? ($settings['currency'] ?? $invoice->currency ?? 'toman'),
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
            'total' => (int)round($roundedGrandTotal),
            'notes' => $data['notes'] ?? null,
            'meta' => $existingMeta,
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

        if ($invoice->isMerged()) {
            return redirect()->route('services.invoices.show', $invoice)->with('error', 'امکان ثبت پرداخت برای فاکتور ادغام شده وجود ندارد.');
        }

        $invoice->load('items.service', 'customer', 'status');
        $settings = Setting::pluck('value', 'key')->toArray();
        $servicesCurrency = strtolower($invoice->currency ?? $settings['currency'] ?? 'toman');
        $paymentCurrency  = strtolower($settings['payment_currency'] ?? 'toman');

        $conversionFactor = 1.0;
        if (in_array($servicesCurrency, ['rial', 'irr', 'ریال']) && in_array($paymentCurrency, ['toman', 'tmn', 'تومان'])) {
            $conversionFactor = 0.1;
        } elseif (in_array($servicesCurrency, ['toman', 'tmn', 'تومان']) && in_array($paymentCurrency, ['rial', 'irr', 'ریال'])) {
            $conversionFactor = 10.0;
        }

        $customerCheques = [];
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            if ($invoice->customer_id) {
                $customerCheques = \Modules\Accounting\Entities\Cheque::where('client_id', $invoice->customer_id)
                    ->where('type', 'receivable')
                    ->where('status', 'pending')
                    ->get()
                    ->filter(function ($cheque) {
                        $hasRelatedInvoice = !empty($cheque->related_invoice_id);
                        $hasAttachedInvoices = $cheque->attachedInvoices()->exists();
                        $hasAttachedDocuments = $cheque->attachedDocuments()->exists();
                        $hasExpenseDocuments = $cheque->expenseDocuments()->exists();

                        $hasServicePayment = false;
                        if (Module::has('Services') && Module::isEnabled('Services')) {
                            $hasServicePayment = Payment::where('method', 'cheque-' . $cheque->id)
                                ->where('status', '!=', 'canceled')
                                ->exists();
                        }

                        return !$hasRelatedInvoice && !$hasAttachedInvoices && !$hasAttachedDocuments && !$hasExpenseDocuments && !$hasServicePayment;
                    })
                    ->values()
                    ->map(function ($cheque) use ($conversionFactor) {
                        $cheque->due_date_jalali = \Morilog\Jalali\Jalalian::fromCarbon($cheque->due_date)->format('Y/m/d');
                        $cheque->display_amount = $cheque->amount * $conversionFactor;
                        return $cheque;
                    });
            }
        }

        $customerWallet = null;
        if (Module::has('Wallet') && Module::isEnabled('Wallet')) {
            if ($invoice->customer_id) {
                $clientClass = (new \Modules\Clients\Entities\Client())->getMorphClass();
                $customerWallet = \Modules\Wallet\App\Models\Wallet::where('holder_type', $clientClass)
                    ->where('holder_id', $invoice->customer_id)
                    ->first();

                if (!$customerWallet && $invoice->customer) {
                    $customerWallet = \Modules\Wallet\App\Models\Wallet::where('holder_type', get_class($invoice->customer))
                        ->where('holder_id', $invoice->customer->id)
                        ->first();
                }
            }
        }

        return view('services::invoices.payment', [
            'invoice'          => $invoice,
            'currency'         => $paymentCurrency,
            'settings'         => $settings,
            'customerCheques'  => $customerCheques,
            'customerWallet'   => $customerWallet,
            'conversionFactor' => $conversionFactor,
        ]);
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->isMerged()) {
            return redirect()->route('services.invoices.show', $invoice)->with('error', 'امکان ثبت پرداخت برای فاکتور ادغام شده وجود ندارد.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $servicesCurrency = strtolower($invoice->currency ?? $settings['currency'] ?? 'toman');
        $paymentCurrency  = strtolower($settings['payment_currency'] ?? 'toman');

        $conversionFactor = 1.0;
        if (in_array($servicesCurrency, ['rial', 'irr', 'ریال']) && in_array($paymentCurrency, ['toman', 'tmn', 'تومان'])) {
            $conversionFactor = 0.1;
        } elseif (in_array($servicesCurrency, ['toman', 'tmn', 'تومان']) && in_array($paymentCurrency, ['rial', 'irr', 'ریال'])) {
            $conversionFactor = 10.0;
        }

        $paymentCurrencyLabel = in_array($paymentCurrency, ['rial', 'irr', 'ریال']) ? 'ریال' : 'تومان';

        $useWallet = $request->boolean('use_wallet');
        $chequeIds = array_filter(array_map('intval', (array)$request->input('cheque_ids', [])));

        $rawPaymentItems = (array)$request->input('payment_items', []);
        $paymentItems = [];

        foreach ($rawPaymentItems as $item) {
            if (is_array($item) && !empty($item['method'])) {
                $submittedAmt = (float)str_replace(',', '', (string)($item['amount'] ?? 0));
                if ($submittedAmt > 0) {
                    $amtInServicesCurrency = (int)round($submittedAmt / $conversionFactor);
                    if ($amtInServicesCurrency > 0) {
                        $paymentItems[] = [
                            'method'         => $item['method'],
                            'amount'         => $amtInServicesCurrency,
                            'transaction_id' => $item['transaction_id'] ?? null,
                            'gateway'        => $item['gateway'] ?? null,
                        ];
                    }
                }
            }
        }

        $singleMethod = $request->input('payment_method');
        $singleAmount = (float)str_replace(',', '', $request->input('amount', '0'));
        if (empty($paymentItems) && !empty($singleMethod) && $singleMethod !== 'wallet' && !str_starts_with($singleMethod, 'cheque-') && $singleAmount > 0) {
            $amtInServicesCurrency = (int)round($singleAmount / $conversionFactor);
            if ($amtInServicesCurrency > 0) {
                $paymentItems[] = [
                    'method'         => $singleMethod,
                    'amount'         => $amtInServicesCurrency,
                    'transaction_id' => $request->input('transaction_id'),
                    'gateway'        => $request->input('gateway'),
                ];
            }
        }

        if (!empty($singleMethod) && str_starts_with($singleMethod, 'cheque-')) {
            $cId = (int)str_replace('cheque-', '', $singleMethod);
            if ($cId && !in_array($cId, $chequeIds)) {
                $chequeIds[] = $cId;
            }
        }

        $hasCheques = !empty($chequeIds);
        $hasMethodPayments = !empty($paymentItems);

        if (!$useWallet && !$hasCheques && !$hasMethodPayments) {
            return back()->withInput()->with('error', 'لطفاً حداقل یک روش پرداخت (کیف پول، چک، یا پرداخت نقدی/آنلاین) را انتخاب فرمایید.');
        }

        $currentDue = $invoice->total - $invoice->calculatePaidAmount();
        if ($currentDue <= 0) {
            return back()->with('error', 'این فاکتور قبلاً به طور کامل تسویه شده است.');
        }

        // Calculate total amount requested across all methods in this form submission
        $reqWalletAmt = 0;
        if ($useWallet) {
            if ($request->filled('wallet_amount')) {
                $rawWalletAmt = (float)str_replace(',', '', $request->input('wallet_amount'));
                $reqWalletAmt = (int)round($rawWalletAmt / $conversionFactor);
            } else {
                $reqWalletAmt = (int)$currentDue;
            }
        }

        $chequesTotal = 0;
        if (!empty($chequeIds) && Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $chequesTotal = (int)\Modules\Accounting\Entities\Cheque::whereIn('id', $chequeIds)
                ->where('type', 'receivable')
                ->where('status', 'pending')
                ->sum('amount');
        }

        $directTotal = 0;
        foreach ($paymentItems as $item) {
            $directTotal += (int)($item['amount'] ?? 0);
        }

        $totalSubmittedInRequest = $reqWalletAmt + $chequesTotal + $directTotal;

        if ($totalSubmittedInRequest > ($currentDue + 10)) {
            $submittedDisplay = (int)round($totalSubmittedInRequest * $conversionFactor);
            $dueDisplay       = (int)round($currentDue * $conversionFactor);
            return back()->withInput()->with('error', 'مجموع پرداختی‌های انتخاب شده در این فرم (' . number_format($submittedDisplay) . ' ' . $paymentCurrencyLabel . ') بیشتر از مانده بدهی فاکتور (' . number_format($dueDisplay) . ' ' . $paymentCurrencyLabel . ') است.');
        }

        $checkSetting = true;
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $checkSetting = \Modules\Accounting\App\Models\AccountingSetting::get('general.check_cheque_due_dates', true);
        }

        $lastPayment = null;

        try {
            DB::transaction(function () use ($invoice, $request, $paymentItems, &$lastPayment, $checkSetting, $useWallet, $chequeIds) {
                $paidAt = now();
                if ($request->filled('paid_at')) {
                    try {
                        $paidAt = Jalalian::fromFormat('Y/m/d', $request->paid_at)->toCarbon();
                    } catch (\Exception $e) {}
                }

                $dueAmount = $invoice->total - $invoice->calculatePaidAmount();

                // 1. Process Wallet Payment if requested
                if ($useWallet && $dueAmount > 0) {
                    $customerWallet = null;
                    if (Module::has('Wallet') && Module::isEnabled('Wallet')) {
                        if ($invoice->customer_id) {
                            $clientClass = (new \Modules\Clients\Entities\Client())->getMorphClass();
                            $customerWallet = \Modules\Wallet\App\Models\Wallet::where('holder_type', $clientClass)
                                ->where('holder_id', $invoice->customer_id)
                                ->first();

                            if (!$customerWallet && $invoice->customer) {
                                $customerWallet = \Modules\Wallet\App\Models\Wallet::where('holder_type', get_class($invoice->customer))
                                    ->where('holder_id', $invoice->customer->id)
                                    ->first();
                            }
                        }
                    }

                    if (!$customerWallet || (float)$customerWallet->balance <= 0) {
                        throw new \Exception('کیف پول مشتری یافت نشد یا موجودی کافی ندارد.');
                    }

                    $walletBalance = (float)$customerWallet->balance;
                    $reqWalletAmt = $request->filled('wallet_amount')
                        ? (int)str_replace(',', '', $request->input('wallet_amount'))
                        : (int)$dueAmount;

                    if ($reqWalletAmt <= 0) {
                        $reqWalletAmt = (int)$dueAmount;
                    }

                    $walletAmountToUse = min($reqWalletAmt, $walletBalance, $dueAmount);

                    if ($walletAmountToUse > 0) {
                        $walletHolder = $customerWallet->holder ?? $invoice->customer;
                        if ($walletHolder) {
                            app(\Modules\Wallet\App\Services\WalletService::class)->withdraw(
                                holder: $walletHolder,
                                amount: $walletAmountToUse,
                                type: \Modules\Wallet\App\Enums\TransactionType::PAYMENT,
                                payable: $invoice,
                                description: 'پرداخت فاکتور خدمات #' . $invoice->invoice_number,
                                meta: ['invoice_id' => $invoice->id]
                            );

                            $walletPayment = $invoice->payments()->create([
                                'user_id'        => $request->user()->id,
                                'amount'         => $walletAmountToUse,
                                'method'         => 'wallet',
                                'gateway'        => null,
                                'paid_at'        => $paidAt,
                                'transaction_id' => 'WALLET-' . time(),
                                'notes'          => 'پرداخت از کیف پول مشتری برای فاکتور #' . $invoice->invoice_number,
                                'status'         => 'paid',
                            ]);

                            $lastPayment = $walletPayment;

                            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                                $engine = app(\Modules\Accounting\App\Services\AccountingEngine::class);
                                $engine->recordServicePayment($walletPayment);
                            }
                        }
                    }
                }

                // 2. Process Cheque Payments if provided
                $remainingDue = $invoice->total - $invoice->calculatePaidAmount();

                if (!empty($chequeIds) && $remainingDue > 0) {
                    if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        $cheques = \Modules\Accounting\Entities\Cheque::whereIn('id', $chequeIds)
                            ->where('type', 'receivable')
                            ->where('status', 'pending')
                            ->get();

                        foreach ($cheques as $cheque) {
                            if ($remainingDue <= 0) break;

                            $chequeStatus = $checkSetting ? 'pending' : 'paid';
                            $chequeAmountToPay = min($cheque->amount, $remainingDue);
                            $chequePaidAt = $cheque->due_date ? \Carbon\Carbon::parse($cheque->due_date) : $paidAt;

                            $chequePayment = $invoice->payments()->create([
                                'user_id'        => $request->user()->id,
                                'amount'         => $chequeAmountToPay,
                                'method'         => 'cheque-' . $cheque->id,
                                'gateway'        => null,
                                'paid_at'        => $chequePaidAt,
                                'transaction_id' => $cheque->cheque_number,
                                'notes'          => 'پرداخت با چک صیادی #' . $cheque->cheque_number . ' برای فاکتور #' . $invoice->invoice_number,
                                'status'         => $chequeStatus,
                            ]);

                            $lastPayment = $chequePayment;

                            app(\Modules\Accounting\App\Services\ChequeService::class)->attachToInvoice($cheque, $invoice->id);

                            $remainingDue -= $chequeAmountToPay;
                        }
                    }
                }

                // 3. Process Direct/Manual Payment Items
                // Continuously track remainingDue without resetting (since pending cheques are already counted in $remainingDue)

                foreach ($paymentItems as $item) {
                    if ($remainingDue <= 0) break;

                    $method = $item['method'] ?? null;
                    $itemAmt = (int)($item['amount'] ?? 0);

                    if (empty($method) || $itemAmt <= 0 || $method === 'wallet' || str_starts_with($method, 'cheque-')) {
                        continue;
                    }

                    $amountToPay = min($itemAmt, $remainingDue);

                    $payment = $invoice->payments()->create([
                        'user_id'        => $request->user()->id,
                        'amount'         => $amountToPay,
                        'method'         => $method,
                        'gateway'        => $item['gateway'] ?? null,
                        'paid_at'        => $paidAt,
                        'transaction_id' => $item['transaction_id'] ?? null,
                        'notes'          => 'پرداخت برای فاکتور #' . $invoice->invoice_number,
                        'status'         => 'paid',
                    ]);

                    $lastPayment = $payment;

                    if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        $engine = app(\Modules\Accounting\App\Services\AccountingEngine::class);
                        $engine->recordServicePayment($payment);
                    }

                    $remainingDue -= $amountToPay;
                }

                // 4. Update Invoice Payment Status
                $invoice->paid_amount = $invoice->calculatePaidAmount();

                $StatusModel = Status::class;
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

                if ($invoice->isPaid() && Module::has('Accounting') && Module::isEnabled('Accounting')) {
                    try {
                        app(\Modules\Accounting\App\Services\AccountingEngine::class)->recordFromServiceInvoice($invoice);
                    } catch (\Throwable $e) {
                        Log::error('[AccountingEngine] Error recording service invoice on payment: ' . $e->getMessage());
                    }
                }

                $invoice->save();
                $this->syncOrdersForInvoice($invoice);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطا در ثبت پرداخت: ' . $e->getMessage());
        }

        // شلیک رویدادهای فاکتور به جای پرداخت
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class) && $lastPayment) {
            try {
                $eventKey = $invoice->isPaid() ? 'invoice_paid' : 'invoice_unpaid';
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start($eventKey, 'INVOICE', $invoice->id, [
                    'amount'      => $lastPayment->amount,
                    'is_paid'     => $invoice->isPaid(),
                    'is_overdue'  => $invoice->isOverdue(),
                    'remaining'   => $invoice->remainingAmount(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[Workflows] Error starting workflow: ' . $e->getMessage());
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

        try {
            DB::transaction(function () use ($invoice, $payment) {
                $payment->update(['status' => 'canceled']);

                $invoice->paid_amount = $invoice->calculatePaidAmount();

                $StatusModel = Status::class;
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
                $this->syncOrdersForInvoice($invoice);

                if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                    $engine = app(\Modules\Accounting\App\Services\AccountingEngine::class);
                    $engine->cancelServicePayment($payment);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در لغو پرداخت: ' . $e->getMessage());
        }

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
                Log::error('[Workflows] Error starting workflow: ' . $e->getMessage());
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
            $this->syncOrdersForInvoice($invoice);
        }

        $invoice->save();
        $this->syncOrdersForInvoice($invoice);

        if ($invoice->invoice_number && Module::has('Accounting') && Module::isEnabled('Accounting')) {
            try {
                if (mb_strpos($status->name, 'لغو') !== false || mb_strpos($status->name, 'باطل') !== false) {
                    $engine = app(\Modules\Accounting\App\Services\AccountingEngine::class);
                    foreach ($invoice->payments as $payment) {
                        if ($payment->status !== 'canceled') {
                            $payment->update(['status' => 'canceled']);
                            $engine->cancelServicePayment($payment);
                        }
                    }
                    $engine->cancelServiceInvoice($invoice);
                } else {
                    app(\Modules\Accounting\App\Services\AccountingEngine::class)->recordFromServiceInvoice($invoice);
                }
            } catch (\Throwable $e) {
                Log::error('[AccountingEngine] Error recording service invoice on updateStatus: ' . $e->getMessage());
            }
        }

        if (class_exists(WorkflowEngine::class)) {
            try {
                app(WorkflowEngine::class)->start('invoice_status_changed', 'INVOICE', $invoice->id, [
                    'new_status_id'   => $status->id,
                    'new_status_name' => $status->name,
                ]);
            } catch (\Throwable $e) {
                Log::error('[Workflows] Error starting invoice_status_changed workflow: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'وضعیت فاکتور به‌روز شد.');
    }

    public function cancel(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

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

        DB::transaction(function () use ($invoice, $cancelledStatus) {
            $invoice->status_id = $cancelledStatus->id;

            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                try {
                    $engine = app(\Modules\Accounting\App\Services\AccountingEngine::class);
                    foreach ($invoice->payments as $payment) {
                        if ($payment->status !== 'canceled') {
                            $engine->cancelServicePayment($payment);
                        }
                    }
                    $engine->cancelServiceInvoice($invoice);
                } catch (\Throwable $e) {
                    Log::error('[AccountingEngine] Error cancelling service invoice in Accounting: ' . $e->getMessage());
                }
            }

            $invoice->payments()->update(['status' => 'canceled']);
            $invoice->paid_amount = 0;
            $invoice->save();
            $this->syncOrdersForInvoice($invoice);
        });

        if (class_exists(WorkflowEngine::class)) {
            try {
                $invoice->refresh();
                app(WorkflowEngine::class)->start('invoice_cancelled', 'INVOICE', $invoice->id, [
                    'cancelled_status_id'   => $cancelledStatus->id,
                    'cancelled_status_name' => $cancelledStatus->name,
                ]);
            } catch (\Throwable $e) {
                Log::error('[Workflows] Error starting invoice_cancelled workflow: ' . $e->getMessage());
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

        $this->removeMarketOrderIfExists($invoice);
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

        $invoice->load('items.service.customFields', 'customer', 'status', 'payments');

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
        $invoice->load('items.service.customFields', 'customer', 'status', 'payments');

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
        $this->syncOrdersForInvoice($invoice);

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

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            try {
                app(\Modules\Accounting\App\Services\AccountingEngine::class)->recordFromServiceInvoice($invoice);
            } catch (\Throwable $e) {
                Log::error('[AccountingEngine] Error recording service invoice on convertToInvoice: ' . $e->getMessage());
            }
        }

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
            $customFieldsOld = $item['custom_fields_old'] ?? [];

            foreach ($customFieldsValues as $key => $val) {
                if ($val instanceof UploadedFile || $val instanceof \Illuminate\Http\UploadedFile) {
                    $path = $val->store('invoice_custom_fields', 'public');
                    $customFieldsValues[$key] = $path;
                }
            }

            foreach ($customFieldsOld as $key => $oldVal) {
                if (!empty($oldVal) && empty($customFieldsValues[$key])) {
                    $customFieldsValues[$key] = $oldVal;
                }
            }

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

                    if ($taxMode === 'item' && $taxApplyCustomFields) {
                        $cfTaxPercent = (float)($customFieldsTaxes[$field->id] ?? 0);
                        $cfBase = $amount * $qty;
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
                $rowTaxableBase = $price * $qty;
                $rowTaxAmount = $rowTaxableBase * ($rowTaxPercent / 100);
                $itemsTaxTotal += $rowTaxAmount + $customFieldsTaxTotal;
            }

            $prepared[] = [
                'service_id' => $item['service_id'] ?? null,
                'custom_service_name' => $item['custom_service_name'] ?? null,
                'description' => $item['description'] ?? '',
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
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    '_packageGroupId' => $item['_packageGroupId'] ?? null,
                    '_packageTitle' => $item['_packageTitle'] ?? null,
                    '_isMerged' => $item['_isMerged'] ?? false,
                    'type' => (!empty($item['product_id']) || !empty($item['product_variant_id'])) ? 'product' : (!empty($item['service_id']) ? 'service' : 'manual'),
                ],
            ];
        }

        return [$prepared, $subtotal, $totalDiscount, $itemsTotal, $itemsTaxTotal];
    }

    private function getProductsForInvoice(): array
    {
        $products = [];
        if (!$this->isMarketModuleEnabled()) {
            return $products;
        }

        if (class_exists(MasterProduct::class)) {
            try {
                $masterProducts = MasterProduct::where('status', 'active')
                    ->with(['variants.vendorProducts', 'category.parent', 'brand'])
                    ->orderBy('title')
                    ->get();

                foreach ($masterProducts as $mp) {
                    if ($mp->variants && $mp->variants->count() > 0) {
                        foreach ($mp->variants as $v) {
                            $price = method_exists($v, 'getEffectivePrice') ? $v->getEffectivePrice() : ($v->selling_price ?? $v->price);

                            if (!$price || $price <= 0) {
                                $priceInfo = $mp->price_info ?? [];
                                $price = $priceInfo['min_price'] ?? $priceInfo['original_price'] ?? 0;
                            }

                            // Calculate available stock
                            $stock = 0;
                            $isWmsActive = class_exists(MarketSetting::class)
                                && (bool) MarketSetting::getValue('wms.enabled', false);

                            if ($isWmsActive && class_exists(WarehouseStockService::class) && class_exists(WarehouseStock::class)) {
                                $stockField = app(WarehouseStockService::class)->getStockDeductionStrategy() === 'separated' ? 'online_stock' : 'physical_stock';
                                $stocks = WarehouseStock::where('product_variant_id', $v->id)
                                    ->whereHas('warehouse', function($q) { $q->where('is_active', true); })
                                    ->get();
                                $stock = (int) $stocks->sum(function($s) use ($stockField) {
                                    return max(0, $s->{$stockField} - $s->reserved_stock);
                                });
                            } else {
                                if ($v->vendorProducts && $v->vendorProducts->count() > 0) {
                                    $stock = (int) $v->vendorProducts->where('status', 'published')->sum('stock');
                                } else {
                                    $stock = (int) ($v->stock ?? 0);
                                }
                            }

                            $variantName = isset($v->name) ? $v->name : '';
                            $fullTitle = $mp->title . ($variantName ? ' - ' . $variantName : '');

                            $searchText = $mp->title;
                            if (isset($v->variant_attributes) && is_array($v->variant_attributes)) {
                                foreach ($v->variant_attributes as $key => $value) {
                                    if ($key === 'name' && $value === 'استاندارد') continue;
                                    $searchText .= ' ' . $value;
                                }
                            }

                            $category = $mp->category;
                            if ($category && $category->parent_id) {
                                $group = $category->parent;
                                $subCategory = $category;
                            } else {
                                $group = $category;
                                $subCategory = null;
                            }

                            $groupId = $group ? $group->id : 0;
                            $groupName = $group ? $group->name : 'سایر گروه‌ها';
                            $categoryId = $subCategory ? $subCategory->id : 0;
                            $categoryName = $subCategory ? $subCategory->name : 'عمومی';

                            $products[] = [
                                'id' => $mp->id . '_' . $v->id,
                                'master_id' => $mp->id,
                                'variant_id' => $v->id,
                                'name' => $fullTitle,
                                'search_text' => $searchText,
                                'price' => (float)($price ?? 0),
                                'stock' => $stock,
                                'unit' => 'عدد',
                                'group_id' => $groupId,
                                'group_name' => $groupName,
                                'category_id' => $categoryId,
                                'category_name' => $categoryName,
                                'brand_id' => $mp->brand_id ?? 0,
                                'brand_name' => $mp->brand ? $mp->brand->name : 'بدون برند',
                                'master_title' => $mp->title,
                                'single_sell' => (bool)$mp->single_sell,
                                'attributes' => $v->variant_attributes ?? [],
                            ];
                        }
                    } else {
                        $priceInfo = $mp->price_info ?? [];
                        $price = $priceInfo['min_price'] ?? $priceInfo['original_price'] ?? 0;
                        $stock = (int) ($priceInfo['total_stock'] ?? 0);

                        $category = $mp->category;
                        if ($category && $category->parent_id) {
                            $group = $category->parent;
                            $subCategory = $category;
                        } else {
                            $group = $category;
                            $subCategory = null;
                        }

                        $groupId = $group ? $group->id : 0;
                        $groupName = $group ? $group->name : 'سایر گروه‌ها';
                        $categoryId = $subCategory ? $subCategory->id : 0;
                        $categoryName = $subCategory ? $subCategory->name : 'عمومی';

                        $products[] = [
                            'id' => (string)$mp->id,
                            'master_id' => $mp->id,
                            'variant_id' => null,
                            'name' => $mp->title,
                            'search_text' => $mp->title,
                            'price' => (float)($price ?? 0),
                            'stock' => $stock,
                            'unit' => 'عدد',
                            'group_id' => $groupId,
                            'group_name' => $groupName,
                            'category_id' => $categoryId,
                            'category_name' => $categoryName,
                            'brand_id' => $mp->brand_id ?? 0,
                            'brand_name' => $mp->brand ? $mp->brand->name : 'بدون برند',
                            'master_title' => $mp->title,
                            'attributes' => [],
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::error('[InvoiceController] Error loading market products: ' . $e->getMessage());
            }
        }
        return $products;
    }
    private function computeExtraDiscount(array $data, float $subtotal, float $totalTax = 0, int $itemsDiscount = 0): int
    {
        $type = $data['extra_discount_type'] ?? 'amount';
        $value = (float)($data['extra_discount_value'] ?? 0);

        $base = max(0, $subtotal + $totalTax - $itemsDiscount);

        if ($value <= 0 || $base <= 0) {
            return 0;
        }

        $discount = $type === 'percent'
            ? $base * ($value / 100)
            : $value;

        return (int)round(min(max(0, $discount), $base));
    }

    private function applyInvoiceTax(float $subtotal, mixed $taxPercent, string $taxMode = 'invoice', float $itemsTaxTotal = 0): float
    {
        if ($taxMode === 'item') {
            return $itemsTaxTotal;
        }
        $taxPercent = (float)$taxPercent;
        return $subtotal * ($taxPercent / 100);
    }

    private function applyRounding(float $grandTotal, array $settings): array
    {
        $mode = $settings['services_rounding_mode'] ?? 'none';
        $factor = (int)($settings['services_rounding_factor'] ?? 1000);

        $unrounded = (int)round($grandTotal);
        $finalTotal = $unrounded;
        $diff = 0;

        if ($mode === 'up' && $factor > 0) {
            $finalTotal = (int)(ceil($grandTotal / $factor) * $factor);
            $diff = $finalTotal - $unrounded;
        } elseif ($mode === 'down' && $factor > 0) {
            $finalTotal = (int)(floor($grandTotal / $factor) * $factor);
            $diff = $finalTotal - $unrounded;
        }

        $meta = [
            'mode' => $mode,
            'factor' => $factor,
            'original_total' => $unrounded,
            'diff' => $diff,
            'is_rounded' => ($mode !== 'none' && $factor > 0 && $diff !== 0),
        ];

        return [$finalTotal, $meta];
    }

    private function buildInvoiceData(
        array $data,
        int   $userId,
        float $subtotal,
        float $totalDiscount,
        float $totalTax,
        float $grandTotal,
        bool  $isProforma = false,
        array $roundingMeta = [],
        string $currency = 'toman'
    ): array
    {
        $invoiceNumber = $isProforma ? null : ($data['invoice_number'] ?? Invoice::generateNumber());
        $proformaInvoiceNumber = $isProforma ? ($data['proforma_invoice_number'] ?? Invoice::generateProformaNumber()) : null;

        $defaultStatus = Status::where('type', 'invoice')
            ->where('is_default', 1)
            ->first();
        $statusId = $defaultStatus ? $defaultStatus->id : null;

        if (!$isProforma && (int)round($grandTotal) <= 0) {
            $paidStatus = Status::where('name', 'پرداخت شده')->first()
                ?? Status::where('name', 'LIKE', '%پرداخت شده%')->first();
            if ($paidStatus) {
                $statusId = $paidStatus->id;
            }
        }

        $existingMeta = $data['meta'] ?? [];
        if (is_string($existingMeta)) {
            $existingMeta = json_decode($existingMeta, true) ?? [];
        }
        if (!is_array($existingMeta)) {
            $existingMeta = [];
        }
        if (!empty($roundingMeta)) {
            $existingMeta['rounding'] = $roundingMeta;
        }
        if (isset($data['client_selected_fields']) && is_array($data['client_selected_fields'])) {
            $cleanedSelectedFields = [];
            foreach ($data['client_selected_fields'] as $fid => $vals) {
                if (is_array($vals)) {
                    $cleanedSelectedFields[$fid] = array_values(array_filter(array_map('trim', $vals)));
                } elseif (is_string($vals) && trim($vals) !== '') {
                    $cleanedSelectedFields[$fid] = [trim($vals)];
                }
            }
            $existingMeta['client_selected_fields'] = $cleanedSelectedFields;
        }

        return [
            'status_id' => $statusId,
            'invoice_number' => $invoiceNumber,
            'proforma_invoice_number' => $proformaInvoiceNumber,
            'customer_id' => $data['customer_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_email' => $data['client_email'] ?? null,
            'created_by' => $userId,
            'currency' => $data['currency'] ?? $currency,
            'issue_date' => $data['issue_date'] ?? now(),
            'due_date' => $data['due_date'] ?? null,
            'subtotal' => (int)round($subtotal),
            'discount_amount' => $totalDiscount,
            'tax_percent' => (float)($data['tax_percent'] ?? 0),
            'tax_amount' => (int)round($totalTax),
            'total' => (int)round($grandTotal),
            'notes' => $data['notes'] ?? null,
            'meta' => $existingMeta,
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
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_percent' => $item->tax_percent,
                    'tax_amount' => $item->tax_amount,
                    'total' => max(0, ($item->unit_price * $item->quantity) - $item->discount),
                    'meta' => is_string($item->meta) ? json_decode($item->meta, true) : ($item->meta ?? []),
                ];
            })->toArray();
        }

        $serviceItems = [];
        $marketItems = [];

        foreach ($preparedItems as $index => $item) {
            $type = $item['meta']['type'] ?? (!empty($item['service_id']) ? 'service' : 'manual');
            if ($type === 'product') {
                $marketItems[$index] = $item;
            } else {
                $serviceItems[$index] = $item;
            }
        }

        if (!empty($serviceItems)) {
            $this->syncServiceOrders($invoice, $serviceItems);
        }

        if (!empty($marketItems) && $this->isMarketModuleEnabled()) {
            $this->syncMarketOrder($invoice, $marketItems);
        } elseif (empty($marketItems) && $this->isMarketModuleEnabled()) {
            $this->removeMarketOrderIfExists($invoice);
        }
    }


    private function syncServiceOrders(Invoice $invoice, array $serviceItems)
    {
        $existingOrders = Order::where('invoice_id', $invoice->id)->orderBy('id')->get();

        $orderStatus = Status::where('type', 'order')->where('name', 'در انتظار')->first()
            ?? Status::where('type', 'order')->first();

        $newIndexPosition = 0;
        $usedOrderIds = [];

        foreach ($serviceItems as $item) {
            $serviceId = $item['service_id'] ?? null;
            $service = $serviceId ? Service::find($serviceId) : null;

            $customName = !empty($item['custom_service_name'])
                ? $item['custom_service_name']
                : ($service?->name ?? 'ردیف دستی');

            $renewalPrice = 0;
            $renewalDate = null;
            $billingCycle = $item['meta']['billing_period'] ?? null;

            if ($service && $service->billing_type === 'recurring' && $billingCycle) {
                $renewalPrice = $service->renewal_prices[$billingCycle] ?? 0;
                $issueDate = $invoice->issue_date ? Carbon::parse($invoice->issue_date) : now();
                try {
                    $issueJalali = Jalalian::fromCarbon($issueDate);
                    switch ($billingCycle) {
                        case 'monthly':     $renewalJalali = $issueJalali->addMonths(1); break;
                        case 'quarterly':   $renewalJalali = $issueJalali->addMonths(3); break;
                        case 'semi_annual': $renewalJalali = $issueJalali->addMonths(6); break;
                        case 'annual':      $renewalJalali = $issueJalali->addYears(1); break;
                    }
                    $renewalDate = $renewalJalali->toCarbon()->format('Y-m-d');
                } catch (\Exception $e) {
                    switch ($billingCycle) {
                        case 'monthly':     $renewalDate = (clone $issueDate)->addMonth()->format('Y-m-d'); break;
                        case 'quarterly':   $renewalDate = (clone $issueDate)->addMonths(3)->format('Y-m-d'); break;
                        case 'semi_annual': $renewalDate = (clone $issueDate)->addMonths(6)->format('Y-m-d'); break;
                        case 'annual':      $renewalDate = (clone $issueDate)->addYear()->format('Y-m-d'); break;
                    }
                }
            }

            $orderData = [
                'order_number' => 'ORD-' . $invoice->id . '-' . ($newIndexPosition + 1),
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

            $order = $existingOrders->get($newIndexPosition);

            if ($order) {
                $order->update($orderData);
                $usedOrderIds[] = $order->id;
            } else {
                $orderData['status_id'] = $orderStatus?->id ?? $invoice->status_id;
                $newOrder = Order::create($orderData);
                $usedOrderIds[] = $newOrder->id;

                if (class_exists(WorkflowEngine::class)) {
                    try {
                        app(WorkflowEngine::class)->start('order_created', 'ORDER', $newOrder->id, [
                            'invoice_id' => $invoice->id,
                            'service_id' => $serviceId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('[Workflows] Error starting order_created workflow: ' . $e->getMessage());
                    }
                }
            }

            $newIndexPosition++;
        }

        $existingOrders->whereNotIn('id', $usedOrderIds)->each->delete();
    }


    private function syncMarketOrder(Invoice $invoice, array $marketItems)
    {
        $marketOrderModel = MarketOrder::class;
        $marketOrderItemModel = MarketOrderItem::class;
        $vendorProductModel = VendorProduct::class;

        if (!$this->isMarketModuleEnabled()) {
            return;
        }

        $totalItemsPrice = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        $invoiceSubtotal = (float) ($invoice->subtotal ?? 0);
        $invoiceTax = (float) ($invoice->tax_amount ?? 0);

        foreach ($marketItems as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            if ($qty < 1) $qty = 1;
            $unitPrice = (float) ($item['unit_price'] ?? ($item['total'] / max(1, $qty)));
            $itemGross = $unitPrice * $qty;
            $totalItemsPrice += (int) round($itemGross);

            $itemDiscount = (float) ($item['discount'] ?? 0);
            $discountAmount += $itemDiscount;

            if (isset($item['tax_amount']) && $item['tax_amount'] > 0) {
                $itemTax = (float) $item['tax_amount'];
            } elseif (isset($item['tax_percent']) && $item['tax_percent'] > 0) {
                $itemTax = $itemGross * ((float) $item['tax_percent'] / 100);
            } elseif ($invoiceTax > 0 && $invoiceSubtotal > 0) {
                $itemTax = ($invoiceTax / $invoiceSubtotal) * $itemGross;
            } else {
                $itemTax = 0;
            }
            $taxAmount += $itemTax;
        }

        $taxAmount = (float) round($taxAmount);
        $discountAmount = (float) round($discountAmount);
        $finalGrandTotal = max(0, $totalItemsPrice - $discountAmount + $taxAmount);

        $marker = 'INVOICE_SOURCE_ID:' . $invoice->id;
        $marketOrder = $marketOrderModel::where('source_invoice_id', $invoice->id)->first()
            ?? (method_exists($marketOrderModel, 'scopeWhereSourceInvoiceId') ? $marketOrderModel::whereSourceInvoiceId($invoice->id)->first() : null)
            ?? $marketOrderModel::where('customer_notes', 'like', "%{$marker}%")->first();

        $invStatus = $invoice->status_id ? Status::find($invoice->status_id) : ($invoice->status ?? null);
        $isInvoiceCanceled = false;
        $isInvoicePaid = false;

        if ($invStatus) {
            $statusName = mb_strtolower($invStatus->name ?? '');
            if (mb_strpos($statusName, 'لغو') !== false || mb_strpos($statusName, 'باطل') !== false || ($invStatus->type ?? '') === 'canceled') {
                $isInvoiceCanceled = true;
            } elseif (mb_strpos($statusName, 'پرداخت شده') !== false || mb_strpos($statusName, 'تکمیل') !== false) {
                $isInvoicePaid = true;
            }
        } else {
            if ($invoice->paid_amount >= $invoice->total && $invoice->total > 0) {
                $isInvoicePaid = true;
            }
        }

        $paymentStatus = 'unpaid';
        $marketOrderStatusId = null;

        if ($isInvoiceCanceled) {
            $paymentStatus = 'failed';
            $canceledStatus = MarketOrderStatus::where('system_type', 'canceled')->first()
                ?? MarketOrderStatus::where('admin_label', 'like', '%لغو%')->first();
            $marketOrderStatusId = $canceledStatus?->id;
        } elseif ($isInvoicePaid) {
            $paymentStatus = 'paid';
            $processingStatus = MarketOrderStatus::where('admin_label', 'like', '%پرداخت تایید شده%')->first()
                ?? MarketOrderStatus::where('system_type', 'processing')->first();
            $marketOrderStatusId = $processingStatus?->id;
        } else {
            $paymentStatus = 'unpaid';
            $defaultStatus = MarketOrderStatus::getDefaultStatus();
            $marketOrderStatusId = $defaultStatus?->id;
        }

        $orderData = [
            'source_invoice_id' => $invoice->id,
            'client_id' => $invoice->customer_id,
            'shipping_address_json' => [
                'name' => $invoice->client_name,
                'mobile' => $invoice->client_phone,
                'email' => $invoice->client_email,
            ],
            'payment_method' => $invoice->payment_method ?: 'transfer',
            'payment_status' => $paymentStatus,
            'paid_at' => $isInvoicePaid ? ($invoice->paid_at ?: now()) : null,
            'total_items_price' => $totalItemsPrice,
            'total_tax' => $taxAmount,
            'total_discount' => $discountAmount,
            'grand_total' => $finalGrandTotal,
            'market_order_status_id' => $marketOrderStatusId ?: MarketOrderStatus::getDefaultStatus()?->id,
            'customer_notes' => $invoice->notes ?? null,
        ];

        if ($marketOrder) {
            if (class_exists(StockService::class)) {
                try {
                    app(StockService::class)->releaseReservation($marketOrder);
                } catch (\Throwable $e) {
                    Log::error('Failed to release market stock before sync: ' . $e->getMessage());
                }
            }
            $marketOrder->update($orderData);
        } else {
            $marketOrder = $marketOrderModel::create($orderData);
        }

        if (method_exists($marketOrder, 'meta')) {
            $marketOrder->meta()->updateOrCreate(
                ['key' => 'source_invoice_id'],
                ['value' => (string) $invoice->id]
            );
        }

        $marketOrder->items()->delete();

        foreach ($marketItems as $item) {
            $variantId = $item['meta']['product_variant_id'] ?? null;
            $qty = (int) ($item['quantity'] ?? 1);
            if ($qty < 1) $qty = 1;
            $vendorProductId = null;
            $vendorId = null;

            if ($variantId && class_exists($vendorProductModel)) {
                $vendorProduct = $vendorProductModel::where('product_variant_id', $variantId)->first();
                if ($vendorProduct) {
                    $vendorProductId = $vendorProduct->id;
                    $vendorId = $vendorProduct->vendor_id;
                }
            }

            $unitPrice = (float) ($item['unit_price'] ?? ($item['total'] / max(1, $qty)));

            $marketOrderItemModel::create([
                'order_id' => $marketOrder->id,
                'vendor_product_id' => $vendorProductId,
                'vendor_id' => $vendorId,
                'product_title' => $item['custom_service_name'] ?? 'محصول فروشگاه',
                'quantity' => $qty,
                'unit_price' => (int) $unitPrice,
                'total_price' => (int) $item['total'],
            ]);

            if (!$isInvoiceCanceled && class_exists(StockService::class)) {
                try {
                    $stockService = app(StockService::class);
                    if ($vendorProductId) {
                        $vp = $vendorProductModel::find($vendorProductId);
                        if ($vp) {
                            $stockService->deduct($vp->product_variant_id, $qty, $vp->id, $unitPrice);
                        }
                    } elseif ($variantId) {
                        $stockService->deduct((int) $variantId, $qty, null, $unitPrice);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to deduct stock during market order sync: ' . $e->getMessage());
                }
            }
        }
    }


    private function removeMarketOrderIfExists(Invoice $invoice)
    {
        if (!$this->isMarketModuleEnabled()) return;
        $marketOrderModel = MarketOrder::class;

        $marker = 'INVOICE_SOURCE_ID:' . $invoice->id;
        $marketOrder = method_exists($marketOrderModel, 'scopeWhereSourceInvoiceId')
            ? $marketOrderModel::whereSourceInvoiceId($invoice->id)->first()
            : ($marketOrderModel::where('source_invoice_id', $invoice->id)->first() ?? $marketOrderModel::where('customer_notes', 'like', "%{$marker}%")->first());
        if ($marketOrder) {
            if (class_exists(StockService::class)) {
                try {
                    app(StockService::class)->releaseReservation($marketOrder);
                } catch (\Throwable $e) {
                    Log::error('Failed to release market stock on removeMarketOrderIfExists: ' . $e->getMessage());
                }
            }
            $marketOrder->items()->delete();
            $marketOrder->delete();
        }
    }
    private function parseJalaliToGregorian($dateStr) {
        if (empty($dateStr)) return null;

        $dateStr = substr((string)$dateStr, 0, 10);
        $dateNorm = str_replace('/', '-', $dateStr);
        if (str_starts_with($dateNorm, '13') || str_starts_with($dateNorm, '14')) {
            try {
                return Jalalian::fromFormat('Y-m-d', $dateNorm)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    return Carbon::parse($dateNorm)->format('Y-m-d');
                } catch (\Exception $e2) {
                    return null;
                }
            }
        }

        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}
