<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\ServicePackage;
use Modules\Services\App\Http\Models\ServicePackageItem;
use Modules\Services\App\Http\Requests\StoreServicePackageRequest;
use Modules\Settings\Entities\Setting;
use Nwidart\Modules\Facades\Module;
use Modules\Market\Entities\MarketAttribute;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\App\Services\WarehouseStockService;
use Throwable;

class ServicePackageController extends Controller
{
    private function parsePrice($val)
    {
        if (empty($val)) return 0;
        if (is_numeric($val)) return floatval($val);

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $val = str_replace($persian, $english, (string)$val);
        $val = str_replace($arabic, $english, $val);

        $clean = preg_replace('/[^\d.]/', '', $val);

        return floatval($clean);
    }

    public function index(Request $request)
    {
        $packages = ServicePackage::withCount('items')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.index', compact('packages', 'currency'));
    }

    public function create()
    {
        $services = Service::active()->with('customFields')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';
        $marketModuleEnabled = $this->isMarketModuleEnabled();
        $products = $this->getProductsForPackage();
        $marketAttributes = $this->getMarketAttributesForPackage();

        return view('services::packages.create', compact('services', 'currency', 'marketModuleEnabled', 'products', 'marketAttributes'));
    }

    public function store(StoreServicePackageRequest $request)
    {
        DB::transaction(function () use ($request, &$package) {
            $itemsData = $request->input('items', []);
            $subtotal = 0;

            $processedItems = [];

            foreach ($itemsData as $item) {
                $qty = floatval($this->parsePrice($item['quantity'] ?? 1));
                if ($qty <= 0) $qty = 1;
                $unitPrice = intval($this->parsePrice($item['unit_price'] ?? 0));
                $discType = $item['discount_type'] ?? 'amount';
                $discVal = floatval($this->parsePrice($item['discount_value'] ?? 0));

                $rawCustomFields = $item['custom_fields'] ?? [];
                $customFields = [];
                if (is_array($rawCustomFields)) {
                    foreach ($rawCustomFields as $k => $v) {
                        if (is_array($v)) {
                            $cleanArr = array_values(array_filter($v, fn($x) => $x !== null && trim((string)$x) !== ''));
                            if (!empty($cleanArr)) {
                                $customFields[$k] = $cleanArr;
                            }
                        } elseif ($v !== null && trim((string)$v) !== '') {
                            $customFields[$k] = $v;
                        }
                    }
                }

                $rawCustomFieldsPrices = $item['custom_fields_prices'] ?? [];
                $rawCustomFieldsQuantities = $item['custom_fields_quantities'] ?? [];
                $customFieldsPrices = [];
                $customFieldsQuantities = [];

                foreach ($rawCustomFieldsPrices as $k => $v) {
                    if (is_array($v)) {
                        $customFieldsPrices[$k] = [];
                        foreach ($v as $subK => $subV) {
                            $customFieldsPrices[$k][$subK] = $this->parsePrice($subV);
                        }
                    } else {
                        $customFieldsPrices[$k] = $this->parsePrice($v);
                    }
                }

                foreach ($rawCustomFieldsQuantities as $k => $v) {
                    if (is_array($v)) {
                        $customFieldsQuantities[$k] = [];
                        foreach ($v as $subK => $subV) {
                            $parsedQ = floatval($this->parsePrice($subV));
                            $customFieldsQuantities[$k][$subK] = $parsedQ > 0 ? $parsedQ : 1;
                        }
                    } else {
                        $parsedQ = floatval($this->parsePrice($v));
                        $customFieldsQuantities[$k] = $parsedQ > 0 ? $parsedQ : 1;
                    }
                }

                $cfSubtotal = 0;
                if (!empty($item['service_id'])) {
                    $service = Service::with('customFields')->find($item['service_id']);
                    if ($service && !empty($service->customFields)) {
                        foreach ($service->customFields as $cf) {
                            $val = $customFields[$cf->id] ?? null;
                            $isSelected = false;
                            if ($val !== null && $val !== '') {
                                if ($cf->type === 'checkbox') {
                                    $isSelected = !empty($val);
                                } elseif ($cf->type === 'multiselect') {
                                    $isSelected = is_array($val) && count($val) > 0;
                                } elseif ($cf->type === 'number') {
                                    $numVal = floatval($this->parsePrice($val));
                                    $isSelected = ($numVal > 0);
                                    if ($isSelected) {
                                        $customFields[$cf->id] = $numVal;
                                        if (!isset($customFieldsQuantities[$cf->id]) || floatval($customFieldsQuantities[$cf->id]) <= 0) {
                                            $customFieldsQuantities[$cf->id] = $numVal;
                                        }
                                    }
                                } else {
                                    $isSelected = true;
                                }
                            }
                            if (!$isSelected) {
                                unset($customFieldsQuantities[$cf->id], $customFieldsPrices[$cf->id], $customFields[$cf->id]);
                                continue;
                            }
                            $customFieldsUseDefaultPrice = $item['custom_fields_use_default_price'] ?? [];
                            if ($cf->has_pricing && $isSelected) {
                                $useDef = !empty($customFieldsUseDefaultPrice[$cf->id]);
                                if ($cf->type === 'multiselect' && is_array($val)) {
                                    $cfPriceTotal = 0;
                                    foreach ($val as $opt) {
                                        $optPrice = is_array($customFieldsPrices[$cf->id] ?? null)
                                            ? ($customFieldsPrices[$cf->id][$opt] ?? null)
                                            : null;
                                        if ($optPrice === null || $optPrice === '') {
                                            $optPrice = $cf->getOptionPrice($opt, $unitPrice, $useDef);
                                        }
                                        $optQty = is_array($customFieldsQuantities[$cf->id] ?? null)
                                            ? (floatval($this->parsePrice($customFieldsQuantities[$cf->id][$opt] ?? 1)) ?: 1)
                                            : (floatval($this->parsePrice($customFieldsQuantities[$cf->id] ?? 1)) ?: 1);
                                        $cfPriceTotal += (floatval($optPrice) * $optQty);
                                    }
                                    $cfSubtotal += $cfPriceTotal;
                                } else {
                                    $cfPrice = 0;
                                    if (isset($customFieldsPrices[$cf->id]) && !is_array($customFieldsPrices[$cf->id]) && $customFieldsPrices[$cf->id] !== '') {
                                        $cfPrice = floatval($customFieldsPrices[$cf->id]);
                                    } else {
                                        if ($useDef) {
                                            $cfAmount = floatval($cf->pricing_amount ?? 0);
                                            $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                        } else {
                                            if (in_array($cf->type, ['select', 'radio'])) {
                                                $cfPrice = $cf->getOptionPrice($val, $unitPrice, false);
                                            } else {
                                                $cfAmount = floatval($cf->pricing_amount ?? 0);
                                                $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                            }
                                        }
                                        $customFieldsPrices[$cf->id] = $cfPrice;
                                    }
                                    $cfQty = floatval($this->parsePrice($customFieldsQuantities[$cf->id] ?? 1));
                                    if ($cf->type === 'number' && isset($customFields[$cf->id])) {
                                        $cfQty = floatval($this->parsePrice($customFields[$cf->id]));
                                    }
                                    if ($cfQty <= 0) $cfQty = 1;
                                    $customFieldsQuantities[$cf->id] = $cfQty;
                                    $cfSubtotal += ($cfPrice * $cfQty);
                                }
                            }
                        }
                    }
                }

                $rowSubtotal = ($qty * $unitPrice) + $cfSubtotal;
                if ($discType === 'percent') {
                    $discAmount = round(($rowSubtotal * min(100, max(0, $discVal))) / 100);
                } else {
                    $discAmount = min($rowSubtotal, $discVal);
                }

                $rowTotal = max(0, $rowSubtotal - $discAmount);
                $subtotal += $rowTotal;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'product_variant_id' => !empty($item['product_variant_id']) ? $item['product_variant_id'] : null,
                    'mode' => !empty($item['product_id']) || !empty($item['product_variant_id']) || (($item['mode'] ?? '') === 'product') ? 'product' : (!empty($item['service_id']) ? 'service' : 'manual'),
                    'custom_service_name' => $item['custom_service_name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'عدد',
                    'unit_price' => $unitPrice,
                    'discount_type' => $discType,
                    'discount_value' => $discVal,
                    'discount_amount' => $discAmount,
                    'billing_period' => $item['billing_period'] ?? null,
                    'custom_fields' => $customFields,
                    'custom_fields_prices' => $customFieldsPrices,
                    'custom_fields_quantities' => $customFieldsQuantities,
                    'custom_fields_use_default_price' => $customFieldsUseDefaultPrice ?? [],
                    'total_price' => $rowTotal,
                ];
            }

            $packageDiscType = $request->input('discount_type', 'amount');
            $packageDiscVal = floatval($this->parsePrice($request->input('discount_value', 0)));

            if ($packageDiscType === 'percent') {
                $packageDiscAmount = round(($subtotal * min(100, max(0, $packageDiscVal))) / 100);
            } else {
                $packageDiscAmount = min($subtotal, $packageDiscVal);
            }

            $finalPrice = max(0, $subtotal - $packageDiscAmount);

            $package = ServicePackage::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'total_amount' => $subtotal,
                'discount_type' => $packageDiscType,
                'discount_value' => $packageDiscVal,
                'final_price' => $finalPrice,
                'status' => $request->input('status', 'active'),
            ]);

            foreach ($processedItems as $pItem) {
                $package->items()->create($pItem);
            }
        });

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج با موفقیت تعریف شد.');
    }

    public function show(ServicePackage $package)
    {
        $package->load(['items.service.customFields']);
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.show', compact('package', 'currency'));
    }

    public function edit(ServicePackage $package)
    {
        $package->load(['items.service.customFields']);
        $services = Service::active()->with('customFields')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';
        $marketModuleEnabled = $this->isMarketModuleEnabled();
        $products = $this->getProductsForPackage();
        $marketAttributes = $this->getMarketAttributesForPackage();

        return view('services::packages.edit', compact('package', 'services', 'currency', 'marketModuleEnabled', 'products', 'marketAttributes'));
    }

    public function update(StoreServicePackageRequest $request, ServicePackage $package)
    {
        DB::transaction(function () use ($request, $package) {
            $itemsData = $request->input('items', []);
            $subtotal = 0;
            $processedItems = [];

            foreach ($itemsData as $item) {
                $qty = floatval($this->parsePrice($item['quantity'] ?? 1));
                if ($qty <= 0) $qty = 1;
                $unitPrice = intval($this->parsePrice($item['unit_price'] ?? 0));
                $discType = $item['discount_type'] ?? 'amount';
                $discVal = floatval($this->parsePrice($item['discount_value'] ?? 0));

                $rawCustomFields = $item['custom_fields'] ?? [];
                $customFields = [];
                if (is_array($rawCustomFields)) {
                    foreach ($rawCustomFields as $k => $v) {
                        if (is_array($v)) {
                            $cleanArr = array_values(array_filter($v, fn($x) => $x !== null && trim((string)$x) !== ''));
                            if (!empty($cleanArr)) {
                                $customFields[$k] = $cleanArr;
                            }
                        } elseif ($v !== null && trim((string)$v) !== '') {
                            $customFields[$k] = $v;
                        }
                    }
                }

                $rawCustomFieldsPrices = $item['custom_fields_prices'] ?? [];
                $rawCustomFieldsQuantities = $item['custom_fields_quantities'] ?? [];
                $customFieldsPrices = [];
                $customFieldsQuantities = [];

                foreach ($rawCustomFieldsPrices as $k => $v) {
                    if (is_array($v)) {
                        $customFieldsPrices[$k] = [];
                        foreach ($v as $subK => $subV) {
                            $customFieldsPrices[$k][$subK] = $this->parsePrice($subV);
                        }
                    } else {
                        $customFieldsPrices[$k] = $this->parsePrice($v);
                    }
                }

                foreach ($rawCustomFieldsQuantities as $k => $v) {
                    if (is_array($v)) {
                        $customFieldsQuantities[$k] = [];
                        foreach ($v as $subK => $subV) {
                            $parsedQ = floatval($this->parsePrice($subV));
                            $customFieldsQuantities[$k][$subK] = $parsedQ > 0 ? $parsedQ : 1;
                        }
                    } else {
                        $parsedQ = floatval($this->parsePrice($v));
                        $customFieldsQuantities[$k] = $parsedQ > 0 ? $parsedQ : 1;
                    }
                }

                $cfSubtotal = 0;
                if (!empty($item['service_id'])) {
                    $service = Service::with('customFields')->find($item['service_id']);
                    if ($service && !empty($service->customFields)) {
                        foreach ($service->customFields as $cf) {
                            $val = $customFields[$cf->id] ?? null;
                            $isSelected = false;
                            if ($val !== null && $val !== '') {
                                if ($cf->type === 'checkbox') {
                                    $isSelected = !empty($val);
                                } elseif ($cf->type === 'multiselect') {
                                    $isSelected = is_array($val) && count($val) > 0;
                                } elseif ($cf->type === 'number') {
                                    $numVal = floatval($this->parsePrice($val));
                                    $isSelected = ($numVal > 0);
                                    if ($isSelected) {
                                        $customFields[$cf->id] = $numVal;
                                        if (!isset($customFieldsQuantities[$cf->id]) || floatval($customFieldsQuantities[$cf->id]) <= 0) {
                                            $customFieldsQuantities[$cf->id] = $numVal;
                                        }
                                    }
                                } else {
                                    $isSelected = true;
                                }
                            }
                            if (!$isSelected) {
                                unset($customFieldsQuantities[$cf->id], $customFieldsPrices[$cf->id], $customFields[$cf->id]);
                                continue;
                            }
                            $customFieldsUseDefaultPrice = $item['custom_fields_use_default_price'] ?? [];
                            if ($cf->has_pricing && $isSelected) {
                                $useDef = !empty($customFieldsUseDefaultPrice[$cf->id]);
                                if ($cf->type === 'multiselect' && is_array($val)) {
                                    $cfPriceTotal = 0;
                                    foreach ($val as $opt) {
                                        $optPrice = is_array($customFieldsPrices[$cf->id] ?? null)
                                            ? ($customFieldsPrices[$cf->id][$opt] ?? null)
                                            : null;
                                        if ($optPrice === null || $optPrice === '') {
                                            $optPrice = $cf->getOptionPrice($opt, $unitPrice, $useDef);
                                        }
                                        $optQty = is_array($customFieldsQuantities[$cf->id] ?? null)
                                            ? (floatval($this->parsePrice($customFieldsQuantities[$cf->id][$opt] ?? 1)) ?: 1)
                                            : (floatval($this->parsePrice($customFieldsQuantities[$cf->id] ?? 1)) ?: 1);
                                        $cfPriceTotal += (floatval($optPrice) * $optQty);
                                    }
                                    $cfSubtotal += $cfPriceTotal;
                                } else {
                                    $cfPrice = 0;
                                    if (isset($customFieldsPrices[$cf->id]) && !is_array($customFieldsPrices[$cf->id]) && $customFieldsPrices[$cf->id] !== '') {
                                        $cfPrice = floatval($customFieldsPrices[$cf->id]);
                                    } else {
                                        if ($useDef) {
                                            $cfAmount = floatval($cf->pricing_amount ?? 0);
                                            $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                        } else {
                                            if (in_array($cf->type, ['select', 'radio'])) {
                                                $cfPrice = $cf->getOptionPrice($val, $unitPrice, false);
                                            } else {
                                                $cfAmount = floatval($cf->pricing_amount ?? 0);
                                                $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                            }
                                        }
                                        $customFieldsPrices[$cf->id] = $cfPrice;
                                    }
                                    $cfQty = floatval($this->parsePrice($customFieldsQuantities[$cf->id] ?? 1));
                                    if ($cf->type === 'number' && isset($customFields[$cf->id])) {
                                        $cfQty = floatval($this->parsePrice($customFields[$cf->id]));
                                    }
                                    if ($cfQty <= 0) $cfQty = 1;
                                    $customFieldsQuantities[$cf->id] = $cfQty;
                                    $cfSubtotal += ($cfPrice * $cfQty);
                                }
                            }
                        }
                    }
                }

                $rowSubtotal = ($qty * $unitPrice) + $cfSubtotal;
                if ($discType === 'percent') {
                    $discAmount = round(($rowSubtotal * min(100, max(0, $discVal))) / 100);
                } else {
                    $discAmount = min($rowSubtotal, $discVal);
                }

                $rowTotal = max(0, $rowSubtotal - $discAmount);
                $subtotal += $rowTotal;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'product_variant_id' => !empty($item['product_variant_id']) ? $item['product_variant_id'] : null,
                    'mode' => !empty($item['product_id']) || !empty($item['product_variant_id']) || (($item['mode'] ?? '') === 'product') ? 'product' : (!empty($item['service_id']) ? 'service' : 'manual'),
                    'custom_service_name' => $item['custom_service_name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'عدد',
                    'unit_price' => $unitPrice,
                    'discount_type' => $discType,
                    'discount_value' => $discVal,
                    'discount_amount' => $discAmount,
                    'billing_period' => $item['billing_period'] ?? null,
                    'custom_fields' => $customFields,
                    'custom_fields_prices' => $customFieldsPrices,
                    'custom_fields_quantities' => $customFieldsQuantities,
                    'custom_fields_use_default_price' => $customFieldsUseDefaultPrice ?? [],
                    'total_price' => $rowTotal,
                ];
            }

            $packageDiscType = $request->input('discount_type', 'amount');
            $packageDiscVal = floatval($this->parsePrice($request->input('discount_value', 0)));

            if ($packageDiscType === 'percent') {
                $packageDiscAmount = round(($subtotal * min(100, max(0, $packageDiscVal))) / 100);
            } else {
                $packageDiscAmount = min($subtotal, $packageDiscVal);
            }

            $finalPrice = max(0, $subtotal - $packageDiscAmount);

            $package->update([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'total_amount' => $subtotal,
                'discount_type' => $packageDiscType,
                'discount_value' => $packageDiscVal,
                'final_price' => $finalPrice,
                'status' => $request->input('status', 'active'),
            ]);

            $package->items()->delete();
            foreach ($processedItems as $pItem) {
                $package->items()->create($pItem);
            }
        });

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج با موفقیت به روزرسانی شد.');
    }

    public function destroy(ServicePackage $package)
    {
        $package->delete();

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج حذف شد.');
    }

    public function getPackagesJson()
    {
        $packages = ServicePackage::where('status', 'active')
            ->with(['items.service.customFields'])
            ->latest()
            ->get();

        return response()->json($packages);
    }

    private function isMarketModuleEnabled(): bool
    {
        return Module::has('Market') && Module::isEnabled('Market');
    }

    private function getMarketAttributesForPackage()
    {
        if ($this->isMarketModuleEnabled() && class_exists(MarketAttribute::class)) {
            try {
                return MarketAttribute::with('values')->orderBy('name')->get();
            } catch (Throwable $e) {
                Log::error('[ServicePackageController] Error loading market attributes: ' . $e->getMessage());
            }
        }
        return collect();
    }

    private function getProductsForPackage(): array
    {
        $products = [];
        if (!$this->isMarketModuleEnabled() || !class_exists(MasterProduct::class)) {
            return $products;
        }

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

                        $stock = 0;
                        $isWmsActive = class_exists(MarketSetting::class)
                            && (bool)MarketSetting::getValue('wms.enabled', false);

                        if ($isWmsActive && class_exists(WarehouseStockService::class) && class_exists(WarehouseStock::class)) {
                            $stockField = app(WarehouseStockService::class)->getStockDeductionStrategy() === 'separated' ? 'online_stock' : 'physical_stock';
                            $stocks = WarehouseStock::where('product_variant_id', $v->id)
                                ->whereHas('warehouse', function ($q) {
                                    $q->where('is_active', true);
                                })
                                ->get();
                            $stock = (int)$stocks->sum(function ($s) use ($stockField) {
                                return max(0, $s->{$stockField} - $s->reserved_stock);
                            });
                        } else {
                            if ($v->vendorProducts && $v->vendorProducts->count() > 0) {
                                $stock = (int)$v->vendorProducts->where('status', 'published')->sum('stock');
                            } else {
                                $stock = (int)($v->stock ?? 0);
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
                    $stock = (int)($priceInfo['total_stock'] ?? 0);

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
        } catch (Throwable $e) {
            Log::error('[ServicePackageController] Error loading market products: ' . $e->getMessage());
        }

        return $products;
    }
}
