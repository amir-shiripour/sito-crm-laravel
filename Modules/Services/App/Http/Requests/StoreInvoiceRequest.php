<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Services\WarehouseStockService;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\WarehouseStock;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Settings\Entities\Setting;
use Nwidart\Modules\Facades\Module;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    protected function prepareForValidation(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $invoiceAuto = !empty($settings['services_invoice_auto_numbering']) || !empty($settings['services_invoice_auto']);
        $proformaAuto = !empty($settings['services_proforma_invoice_auto']);

        if ($this->input('invoice_type') === 'invoice' && $invoiceAuto) {
            $invNum = $this->input('invoice_number');
            $invoiceId = $this->route('invoice')?->id;
            if (!$invNum || Invoice::where('invoice_number', $invNum)->when($invoiceId, fn($q) => $q->where('id', '!=', $invoiceId))->exists()) {
                $this->merge(['invoice_number' => Invoice::generateNumber()]);
            }
        }

        if ($this->input('invoice_type') === 'proforma' && $proformaAuto) {
            $proNum = $this->input('proforma_invoice_number');
            $invoiceId = $this->route('invoice')?->id;
            if (!$proNum || Invoice::where('proforma_invoice_number', $proNum)->when($invoiceId, fn($q) => $q->where('id', '!=', $invoiceId))->exists()) {
                $this->merge(['proforma_invoice_number' => Invoice::generateProformaNumber()]);
            }
        }
    }

    public function rules(): array
    {
        $invoiceId = $this->route('invoice')?->id;

        $rules = [
            'invoice_type' => 'required|string|in:invoice,proforma',
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:service_invoices,invoice_number' . ($invoiceId ? ",{$invoiceId}" : ''),
            ],
            'proforma_invoice_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:service_invoices,proforma_invoice_number' . ($invoiceId ? ",{$invoiceId}" : ''),
            ],
            'issue_date' => 'required|string',
            'due_date' => 'nullable|string',
            'customer_id' => 'required|integer|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_selected_fields' => 'nullable|array',
            'client_selected_fields.*' => 'nullable',
            'status_id' => 'nullable|exists:services_statuses,id',
            'notes' => 'nullable|string',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'extra_discount_type' => 'nullable|in:amount,percent',
            'extra_discount_value' => 'nullable|numeric|min:0',
            'debt_from_invoice_ids' => 'nullable|string',
            'merged_from_invoice_ids' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.product_id' => 'nullable',
            'items.*.product_variant_id' => 'nullable',
            'items.*.custom_service_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount' => 'nullable|integer|min:0',
            'items.*.billing_period' => 'nullable|string|in:monthly,quarterly,semi_annual,annual',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.mode' => 'nullable|string',
            'items.*._isDebt' => 'nullable',
            'items.*.debt_invoice_ids' => 'nullable|string',

            'items.*.custom_fields' => 'nullable|array',
            'items.*.custom_fields_old' => 'nullable|array',
            'items.*.custom_fields_old.*' => 'nullable',
            'items.*.custom_fields.*' => 'nullable',
            'items.*.custom_fields_quantities' => 'nullable|array',
            'items.*.custom_fields_quantities.*' => 'nullable',
            'items.*.custom_fields_prices' => 'nullable|array',
            'items.*.custom_fields_prices.*' => 'nullable',
            'items.*.custom_fields_discounts' => 'nullable|array',
            'items.*.custom_fields_discounts.*' => 'nullable',
            'items.*.custom_fields_taxes' => 'nullable|array',
            'items.*.custom_fields_taxes.*' => 'nullable',

            'payment_mode' => 'nullable|in:cash,installment',
            'payment_method' => 'nullable|in:online,transfer,pos,installment,cod',
            'gateway' => 'nullable|in:zarinpal,zibal,behpardakht',
            'installment_down_payment' => 'nullable|integer|min:0',
            'installment_steps' => 'nullable|integer|min:0|max:60',
            'installment_interest_rate' => 'nullable|numeric|min:0|max:100',
            'installment_option_id' => 'nullable|string',
            'installment_option_title' => 'nullable|string',
            'installment_due_day' => 'nullable|integer|min:1|max:31',
            'installment_start_date' => 'nullable|string',
            'installment_schedule' => 'nullable|string',
        ];

        if ($this->input('payment_mode') === 'installment') {
            $rules['installment_steps'] = 'required|integer|min:1|max:60';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isMarketEnabled = Module::has('Market')
                && Module::isEnabled('Market')
                && class_exists(Order::class);

            foreach ($this->input('items', []) as $index => $item) {
                if (!empty($item['product_id']) || !empty($item['product_variant_id'])) {
                    if (!$isMarketEnabled) {
                        $validator->errors()->add("items.{$index}.product_id", 'ماژول فروشگاه غیرفعال است و امکان ثبت محصول فروشگاهی وجود ندارد.');
                        continue;
                    }

                    $requestedQty = (float)($item['quantity'] ?? 1);
                    $variantId = $item['product_variant_id'] ?? null;
                    $masterId = $item['product_id'] ?? null;
                    $availableStock = 0;
                    $title = !empty($item['custom_service_name']) ? $item['custom_service_name'] : 'محصول فروشگاه';

                    if ($variantId && class_exists(ProductVariant::class)) {
                        $variant = ProductVariant::with('vendorProducts')->find($variantId);
                        if ($variant) {
                            $isWmsActive = class_exists(MarketSetting::class)
                                && (bool)MarketSetting::getValue('wms.enabled', false);

                            if ($isWmsActive && class_exists(WarehouseStockService::class) && class_exists(WarehouseStock::class)) {
                                $stockField = app(WarehouseStockService::class)->getStockDeductionStrategy() === 'separated' ? 'online_stock' : 'physical_stock';
                                $stocks = WarehouseStock::where('product_variant_id', $variantId)
                                    ->whereHas('warehouse', function ($q) {
                                        $q->where('is_active', true);
                                    })
                                    ->get();
                                $availableStock = (int)$stocks->sum(function ($s) use ($stockField) {
                                    return max(0, $s->{$stockField} - $s->reserved_stock);
                                });
                            } else {
                                if ($variant->vendorProducts && $variant->vendorProducts->count() > 0) {
                                    $availableStock = (int)$variant->vendorProducts->where('status', 'published')->sum('stock');
                                } else {
                                    $availableStock = (int)($variant->stock ?? 0);
                                }
                            }
                        }
                    } elseif ($masterId && class_exists(MasterProduct::class)) {
                        $master = MasterProduct::with('variants.vendorProducts')->find($masterId);
                        if ($master) {
                            $availableStock = (int)($master->price_info['total_stock'] ?? 0);
                        }
                    }

                    if ($requestedQty > $availableStock) {
                        $validator->errors()->add("items.{$index}.quantity", "تعداد درخواستی برای «{$title}» بیش از موجودی انبار است (موجودی فعلی: {$availableStock} عدد).");
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'invoice_number' => 'شماره فاکتور',
            'proforma_invoice_number' => 'شماره پیش فاکتور',
            'customer_id' => 'مشتری',
            'issue_date' => 'تاریخ صدور',
            'due_date' => 'تاریخ سررسید',
        ];
    }
}
