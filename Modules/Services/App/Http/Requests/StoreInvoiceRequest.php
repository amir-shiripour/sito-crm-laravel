<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Services\App\Http\Models\Invoice;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
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
            'status_id' => 'nullable|exists:services_statuses,id',
            'notes' => 'nullable|string',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'extra_discount_type' => 'nullable|in:amount,percent',
            'extra_discount_value' => 'nullable|numeric|min:0',

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

            'items.*.custom_fields' => 'nullable|array',
            'items.*.custom_fields.*' => 'nullable',
            'items.*.custom_fields_prices' => 'nullable|array',
            'items.*.custom_fields_prices.*' => 'nullable|numeric',
            'items.*.custom_fields_discounts' => 'nullable|array',
            'items.*.custom_fields_discounts.*' => 'nullable|numeric',

            // قانون اعتبارسنجی برای مالیات فیلدهای سفارشی
            'items.*.custom_fields_taxes' => 'nullable|array',
            'items.*.custom_fields_taxes.*' => 'nullable|numeric|min:0|max:100',

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
            $isMarketEnabled = \Nwidart\Modules\Facades\Module::has('Market')
                && \Nwidart\Modules\Facades\Module::isEnabled('Market')
                && class_exists(\Modules\Market\App\Models\Order::class);

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

                    if ($variantId && class_exists(\Modules\Market\Entities\ProductVariant::class)) {
                        $variant = \Modules\Market\Entities\ProductVariant::with('vendorProducts')->find($variantId);
                        if ($variant) {
                            $isWmsActive = class_exists(\Modules\Market\Entities\MarketSetting::class) 
                                && (bool) \Modules\Market\Entities\MarketSetting::getValue('wms.enabled', false);
                            
                            if ($isWmsActive && class_exists(\Modules\Market\App\Services\WarehouseStockService::class) && class_exists(\Modules\Market\Entities\WarehouseStock::class)) {
                                $stockField = app(\Modules\Market\App\Services\WarehouseStockService::class)->getStockDeductionStrategy() === 'separated' ? 'online_stock' : 'physical_stock';
                                $stocks = \Modules\Market\Entities\WarehouseStock::where('product_variant_id', $variantId)
                                    ->whereHas('warehouse', function($q) { $q->where('is_active', true); })
                                    ->get();
                                $availableStock = (int) $stocks->sum(function($s) use ($stockField) {
                                    return max(0, $s->{$stockField} - $s->reserved_stock);
                                });
                            } else {
                                if ($variant->vendorProducts && $variant->vendorProducts->count() > 0) {
                                    $availableStock = (int) $variant->vendorProducts->where('status', 'published')->sum('stock');
                                } else {
                                    $availableStock = (int) ($variant->stock ?? 0);
                                }
                            }
                        }
                    } elseif ($masterId && class_exists(\Modules\Market\Entities\MasterProduct::class)) {
                        $master = \Modules\Market\Entities\MasterProduct::with('variants.vendorProducts')->find($masterId);
                        if ($master) {
                            $availableStock = (int) ($master->price_info['total_stock'] ?? 0);
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
