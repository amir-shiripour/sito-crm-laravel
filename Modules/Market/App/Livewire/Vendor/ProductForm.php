<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\WarehouseStock;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class ProductForm extends Component
{
    public $searchQuery = '';
    public $master_product_id = '';
    public $selectedMasterProduct = null;

    public $available_variants = [];
    public $vendor_variants = [];

    public $vendor_custom_variants = [];
    public $allow_custom_variants = false;
    public $allowed_axes_options = [];

    public bool $isWmsActive = false;
    public bool $isStandardOnly = false;
    public bool $vendorCanManagePrices = true;

    public string $activeTab = 'vendor';
    public bool $isAdminPricingAllowed = false;
    public array $catalogPrices = [];
    public string $catalogPricingSearch = '';

    // Batch Edit Properties
    public $batchPrice = '';
    public $batchDiscountPercent = '';
    public $batchDiscountPrice = '';
    public $batchStock = '';
    public $batchMinPurchase = '';
    public $batchMaxPurchase = '';
    public $batchCartAmountStep = '';
    public $batchPurchaseStep = '';
    public $batchDiscountStart = '';
    public $batchDiscountEnd = '';
    public $batchDiscountStock = '';
    public $batchMaxDiscountQty = '';

    public function mount()
    {
        $this->isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);
        $this->vendorCanManagePrices = (bool) MarketSetting::getValue('vendors.vendor_can_manage_prices', true);

        $user = auth()->user();
        $storeType = MarketSetting::getValue('system.store_type', 'multi');
        $this->isAdminPricingAllowed = $user && $user->hasAnyRole(['super-admin', 'admin'])
            && $storeType === 'multi'
            && !$this->vendorCanManagePrices;

        if (request()->has('master_id')) {
            $this->selectProduct(request()->query('master_id'));
        }
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchQuery) < 2) return collect();

        return MasterProduct::where('status', 'active')
            ->where(function($q) {
                $q->where('title', 'like', "%{$this->searchQuery}%")
                    ->orWhere('crm_code', 'like', "%{$this->searchQuery}%");
            })
            ->with('category', 'brand')
            ->take(10)
            ->get();
    }

    public function selectProduct($id)
    {
        $this->master_product_id = $id;
        $this->selectedMasterProduct = MasterProduct::with('variants', 'category', 'brand')->find($id);
        $this->searchQuery = '';

        $storeType = MarketSetting::getValue('system.store_type', 'multi');
        $vendorCanCreateVariants = (bool) MarketSetting::getValue('vendors.vendor_can_create_variants', false);

        $this->allow_custom_variants = ($storeType === 'multi' && $vendorCanCreateVariants);

        if ($this->allow_custom_variants) {
            $this->calculateAllowedAxes();
        }

        $this->loadVariants();
    }

    public function clearSelection()
    {
        $this->master_product_id = '';
        $this->selectedMasterProduct = null;
        $this->vendor_variants = [];
        $this->vendor_custom_variants = [];
        $this->allow_custom_variants = false;
        $this->allowed_axes_options = [];
        $this->catalogPrices = [];
        $this->activeTab = 'vendor';
        $this->catalogPricingSearch = '';
    }

    private function calculateAllowedAxes()
    {
        $this->allowed_axes_options = [];
        $permissions = $this->selectedMasterProduct->variant_axes_permissions ?? [];
        if (empty($permissions)) {
            // FALLBACK: If permissions are empty, allow all values of the category's variant fields
            $category = $this->selectedMasterProduct->category;
            $variantFieldIds = $category && is_array($category->variant_fields) ? $category->variant_fields : [];
            if (empty($variantFieldIds)) return;
            $attributes = \Modules\Market\Entities\MarketAttribute::with('values')->whereIn('id', $variantFieldIds)->get();
            foreach ($attributes as $attr) {
                $this->allowed_axes_options[$attr->name] = $attr->values->pluck('value')->toArray();
            }
            return;
        }
        $category = $this->selectedMasterProduct->category;
        $variantFieldIds = is_array($category->variant_fields) ? $category->variant_fields : [];
        if (empty($variantFieldIds)) return;
        $attributes = \Modules\Market\Entities\MarketAttribute::with('values')->whereIn('id', $variantFieldIds)->get();
        foreach ($attributes as $attr) {
            $attrName = $attr->name;
            if (isset($permissions[$attrName])) {
                $allowedVals = $permissions[$attrName];
                if (in_array('هر ' . $attrName, $allowedVals)) {
                    $this->allowed_axes_options[$attrName] = $attr->values->pluck('value')->toArray();
                } else {
                    $this->allowed_axes_options[$attrName] = $allowedVals;
                }
            }
        }
    }

    private function loadVariants()
    {
        $this->vendor_variants = [];
        $vendorId = auth()->user()->marketVendor->id;

        if ($this->selectedMasterProduct) {
            $category = $this->selectedMasterProduct->category;
            $variantFieldIds = $category && is_array($category->variant_fields) ? $category->variant_fields : [];

            $rawVariants = $this->selectedMasterProduct->variants()->where('is_active', true)->get();
            // Filter out wildcard variants (e.g. "هر رنگ") from the standard checklist
            $this->available_variants = $rawVariants->filter(function($variant) {
                $attributes = $variant->variant_attributes ?? [];
                foreach ($attributes as $key => $val) {
                    if (is_string($val) && $val === 'هر ' . $key) {
                        return false;
                    }
                }
                return true;
            });

            $this->isStandardOnly = ($this->available_variants->count() === 1 && empty($variantFieldIds) && empty($this->selectedMasterProduct->variant_axes_permissions));

            foreach ($this->available_variants as $variant) {
                $attributes = $variant->variant_attributes ?? [];
                $variantName = empty($attributes) ? 'تنوع استاندارد' : implode(' | ', (array)$attributes);

                $existing = VendorProduct::where('vendor_id', $vendorId)->where('product_variant_id', $variant->id)->first();

                $stockValue = 0;

                // if WMS is active, dynamically calculate stock from warehouses to ensure frontend has the latest source of truth.
                if ($this->isWmsActive) {
                    $wmsOnlineStock = WarehouseStock::where('product_variant_id', $variant->id)
                        ->whereHas('warehouse', function($q) use ($vendorId) {
                            $q->where('vendor_id', $vendorId)
                              ->where('is_active', true);
                        })
                        ->sum('online_stock');

                    // Automatically heal/sync the legacy database field if a discrepancy exists
                    if ($existing && $existing->stock != $wmsOnlineStock) {
                        $existing->update(['stock' => $wmsOnlineStock]);
                    }
                    $stockValue = $wmsOnlineStock;
                } else {
                    $stockValue = $existing ? $existing->stock : 0;
                }

                $resolvedPrice = $this->vendorCanManagePrices ? ($existing ? $existing->price : '') : ($this->resolveCatalogPrice((array)$attributes) ?? '');
                $this->vendor_variants[$variant->id] = [
                    'display_name' => $variantName,
                    'sell_this' => ($this->isStandardOnly || $existing) ? true : false,
                    'price' => $resolvedPrice ? number_format($resolvedPrice) : '',
                    'discount_price' => $this->vendorCanManagePrices ? ($existing ? $existing->discount_price : '') : '',
                    'discount_start_date' => $existing && $existing->discount_start_date ? Jalalian::fromCarbon($existing->discount_start_date)->format('Y/m/d H:i') : '',
                    'discount_end_date' => $existing && $existing->discount_end_date ? Jalalian::fromCarbon($existing->discount_end_date)->format('Y/m/d H:i') : '',
                    'discount_stock' => $existing ? $existing->discount_stock : '',
                    'max_discount_purchase_qty' => $existing ? $existing->max_discount_purchase_qty : '',
                    'stock' => $stockValue, // Note: the blade file MUST use $isWmsActive to set this input as disabled/readonly
                    'reorder_point' => $existing ? $existing->reorder_point : 5,
                    'min_purchase' => $existing ? $existing->min_purchase_qty : 1,
                    'max_purchase' => $existing ? $existing->max_purchase_qty : '',
                    'cart_amount_step' => $existing && $existing->cart_amount_step ? number_format($existing->cart_amount_step) : '',
                    'purchase_step' => $existing ? $existing->purchase_step : '',
                    'is_active' => $existing ? in_array($existing->status, ['published', 'pending_review']) : true,
                    'is_custom' => false
                ];
            }

            if ($this->isAdminPricingAllowed) {
                $this->catalogPrices = [];
                foreach ($rawVariants as $variant) {
                    $attributes = $variant->variant_attributes ?? [];
                    $variantName = empty($attributes) || (count($attributes) === 1 && isset($attributes['name']) && $attributes['name'] === 'استاندارد')
                        ? 'تنوع استاندارد'
                        : implode(' | ', (array)$attributes);

                    $this->catalogPrices[$variant->id] = [
                        'variant_code' => $variant->variant_code,
                        'display_name' => $variantName,
                        'attributes' => $attributes,
                        'price' => $variant->price ? number_format($variant->price) : '',
                        'discount_price' => $variant->discount_price ? number_format($variant->discount_price) : '',
                        'discount_start_date' => $variant->discount_start_date ? Jalalian::fromCarbon($variant->discount_start_date)->format('Y/m/d H:i') : '',
                        'discount_end_date' => $variant->discount_end_date ? Jalalian::fromCarbon($variant->discount_end_date)->format('Y/m/d H:i') : '',
                        'discount_stock' => $variant->discount_stock ?? '',
                        'max_discount_purchase_qty' => $variant->max_discount_purchase_qty ?? '',
                        'min_purchase' => $variant->min_purchase_qty ?? 1,
                        'max_purchase' => $variant->max_purchase_qty ?? '',
                        'cart_amount_step' => $variant->cart_amount_step ? number_format($variant->cart_amount_step) : '',
                        'purchase_step' => $variant->purchase_step ?? '',
                    ];
                }
            }

            if ($this->allow_custom_variants && empty($this->vendor_variants) && !empty($this->allowed_axes_options)) {
                $this->addCustomVariant();
            }
        }
    }

    public function addCustomVariant()
    {
        $initialAttributes = [];
        foreach ($this->allowed_axes_options as $axisName => $options) {
            $initialAttributes[$axisName] = '';
        }
        $this->vendor_custom_variants[] = [
            'id' => null,
            'attributes' => $initialAttributes,
            'sell_this' => true,
            'price' => '',
            'discount_price' => '',
            'discount_start_date' => '',
            'discount_end_date' => '',
            'discount_stock' => '',
            'max_discount_purchase_qty' => '',
            'stock' => 0,
            'reorder_point' => 5,
            'min_purchase' => 1,
            'max_purchase' => '',
            'cart_amount_step' => '',
            'purchase_step' => '',
            'is_active' => true,
        ];
    }

    public function removeCustomVariant($index)
    {
        unset($this->vendor_custom_variants[$index]);
        $this->vendor_custom_variants = array_values($this->vendor_custom_variants);
    }

    public function save()
    {
        $vendor = auth()->user()->marketVendor;
        $savedCount = 0;
        $storeType = MarketSetting::getValue('system.store_type', 'multi');
        $defaultStatus = ($storeType === 'single') ? 'published' : 'pending_review';

        DB::beginTransaction();
        try {
            // Save standard variants
            foreach ($this->vendor_variants as $variantId => $data) {
                $variantObj = ProductVariant::find($variantId);
                $catalogPrice = $variantObj ? $variantObj->price : null;
                $hasPrice = $this->vendorCanManagePrices ? !empty($data['price']) : ($catalogPrice > 0);

                if (!$data['sell_this'] || ($this->isStandardOnly && !$hasPrice)) {
                    VendorProduct::where('vendor_id', $vendor->id)->where('product_variant_id', $variantId)->delete();
                    continue;
                }

                if ($hasPrice) {
                    if (!$this->vendorCanManagePrices) {
                        $cleanPrice = $variantObj->price;
                        $cleanDiscount = $variantObj->discount_price;
                        $startDate = $variantObj->discount_start_date;
                        $endDate = $variantObj->discount_end_date;
                        $discountStock = $variantObj->discount_stock;
                        $maxDiscountQty = $variantObj->max_discount_purchase_qty;
                        $reorderPoint = $variantObj->reorder_point;
                        $minPurchase = $variantObj->min_purchase_qty;
                        $maxPurchase = $variantObj->max_purchase_qty;
                        $cartStep = $variantObj->cart_amount_step;
                        $purchaseStep = $variantObj->purchase_step;
                    } else {
                        $cleanPrice = str_replace(',', '', $data['price']);
                        $cleanDiscount = str_replace(',', '', $data['discount_price']);

                        $startDate = null;
                        if (!empty($data['discount_start_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_start_date'])) {
                            $startDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_start_date'])->toCarbon();
                        }

                        $endDate = null;
                        if (!empty($data['discount_end_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_end_date'])) {
                            $endDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_end_date'])->toCarbon();
                        }

                        $discountStock = !empty($data['discount_stock']) ? $data['discount_stock'] : null;
                        $maxDiscountQty = !empty($data['max_discount_purchase_qty']) ? $data['max_discount_purchase_qty'] : null;
                        $reorderPoint = $data['reorder_point'] ?? 5;
                        $minPurchase = $data['min_purchase'] ?: 1;
                        $maxPurchase = !empty($data['max_purchase']) ? $data['max_purchase'] : null;
                        $cartStep = !empty($data['cart_amount_step']) ? str_replace(',', '', $data['cart_amount_step']) : null;
                        $purchaseStep = !empty($data['purchase_step']) ? $data['purchase_step'] : null;
                    }

                    $payload = [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => $discountStock,
                        'max_discount_purchase_qty' => $maxDiscountQty,
                        'reorder_point' => $reorderPoint,
                        'min_purchase_qty' => $minPurchase,
                        'max_purchase_qty' => $maxPurchase,
                        'cart_amount_step' => $cartStep,
                        'purchase_step' => $purchaseStep,
                        'status' => $data['is_active'] ? $defaultStatus : 'draft',
                    ];

                    // Determine stock strategy based on WMS status.
                    // If WMS is active, ignore user input entirely and fetch from WMS, avoiding zeroing out the stock accidentally.
                    if ($this->isWmsActive) {
                        $payload['stock'] = WarehouseStock::where('product_variant_id', $variantId)
                            ->whereHas('warehouse', function($q) use ($vendor) {
                                $q->where('vendor_id', $vendor->id)
                                  ->where('is_active', true);
                            })
                            ->sum('online_stock');
                    } else {
                        $payload['stock'] = $data['stock'] ?: 0;
                    }

                    VendorProduct::updateOrCreate(
                        ['vendor_id' => $vendor->id, 'product_variant_id' => $variantId],
                        $payload
                    );
                    $savedCount++;
                }
            }

            // Save custom variants
            foreach ($this->vendor_custom_variants as $customData) {
                if (!$customData['sell_this']) continue;

                $hasCustomPrice = $this->vendorCanManagePrices ? !empty($customData['price']) : true;

                if ($hasCustomPrice) {
                    $variant = ProductVariant::where('master_product_id', $this->master_product_id)
                        ->whereJsonContains('variant_attributes', $customData['attributes'])
                        ->first();

                    if (!$variant) {
                        $variant = ProductVariant::create([
                            'master_product_id' => $this->master_product_id,
                            'variant_code' => $this->selectedMasterProduct->crm_code . '-' . uniqid(),
                            'variant_attributes' => $customData['attributes'],
                            'is_active' => true,
                        ]);
                    }

                    if (!$this->vendorCanManagePrices) {
                        $cleanPrice = $variant->price;
                        $cleanDiscount = $variant->discount_price;
                        $startDate = $variant->discount_start_date;
                        $endDate = $variant->discount_end_date;
                        $discountStock = $variant->discount_stock;
                        $maxDiscountQty = $variant->max_discount_purchase_qty;
                        $reorderPoint = $variant->reorder_point;
                        $minPurchase = $variant->min_purchase_qty;
                        $maxPurchase = $variant->max_purchase_qty;
                        $cartStep = $variant->cart_amount_step;
                        $purchaseStep = $variant->purchase_step;
                    } else {
                        $cleanPrice = str_replace(',', '', $customData['price']);
                        $cleanDiscount = str_replace(',', '', $customData['discount_price']);

                        $startDate = null;
                        if (!empty($customData['discount_start_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $customData['discount_start_date'])) {
                            $startDate = Jalalian::fromFormat('Y/m/d H:i', $customData['discount_start_date'])->toCarbon();
                        }

                        $endDate = null;
                        if (!empty($customData['discount_end_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $customData['discount_end_date'])) {
                            $endDate = Jalalian::fromFormat('Y/m/d H:i', $customData['discount_end_date'])->toCarbon();
                        }

                        $discountStock = !empty($customData['discount_stock']) ? $customData['discount_stock'] : null;
                        $maxDiscountQty = !empty($customData['max_discount_purchase_qty']) ? $customData['max_discount_purchase_qty'] : null;
                        $reorderPoint = $customData['reorder_point'] ?? 5;
                        $minPurchase = $customData['min_purchase'] ?: 1;
                        $maxPurchase = !empty($customData['max_purchase']) ? $customData['max_purchase'] : null;
                        $cartStep = !empty($customData['cart_amount_step']) ? str_replace(',', '', $customData['cart_amount_step']) : null;
                        $purchaseStep = !empty($customData['purchase_step']) ? $customData['purchase_step'] : null;
                    }

                    $payload = [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => $discountStock,
                        'max_discount_purchase_qty' => $maxDiscountQty,
                        'reorder_point' => $reorderPoint,
                        'min_purchase_qty' => $minPurchase,
                        'max_purchase_qty' => $maxPurchase,
                        'cart_amount_step' => $cartStep,
                        'purchase_step' => $purchaseStep,
                        'status' => $customData['is_active'] ? $defaultStatus : 'draft',
                    ];

                    if ($this->isWmsActive) {
                        // For a newly created custom variant in a WMS-enabled environment,
                        // stock is strictly managed by warehouses. Start at 0.
                        $payload['stock'] = 0;
                    } else {
                        $payload['stock'] = $customData['stock'] ?: 0;
                    }

                    VendorProduct::updateOrCreate(
                        ['vendor_id' => $vendor->id, 'product_variant_id' => $variant->id],
                        $payload
                    );
                    $savedCount++;
                }
            }

            DB::commit();

            $this->dispatch('notify', type: 'success', text: "تغییرات شما با موفقیت ذخیره شد. ({$savedCount} تنوع)");
            return redirect()->route('user.market.vendor.products.index');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطایی در هنگام ذخیره رخ داد: ' . $e->getMessage());
        }
    }

    public function toggleAllVariants($state)
    {
        foreach ($this->vendor_variants as $key => $variant) {
            $this->vendor_variants[$key]['sell_this'] = (bool) $state;
        }
        $this->vendor_variants = $this->vendor_variants; // Force reactivity
    }

    public function applyBatchSettings()
    {
        $cleanPrice = str_replace(',', '', $this->batchPrice);
        $cleanDiscountPrice = str_replace(',', '', $this->batchDiscountPrice);
        $cleanCartAmountStep = str_replace(',', '', $this->batchCartAmountStep);

        // Apply to standard variants
        foreach ($this->vendor_variants as $variantId => $data) {
            if ($data['sell_this']) {
                if ($cleanPrice !== '') {
                    $this->vendor_variants[$variantId]['price'] = $cleanPrice;
                }
                
                if ($this->batchDiscountPercent !== '') {
                    $pr = $cleanPrice !== '' ? (float)$cleanPrice : (float)str_replace(',', '', $data['price']);
                    if ($pr > 0) {
                        $p = (float)$this->batchDiscountPercent;
                        $d = $pr - ($pr * ($p / 100));
                        $this->vendor_variants[$variantId]['discount_price'] = round($d);
                    }
                } elseif ($cleanDiscountPrice !== '') {
                    $this->vendor_variants[$variantId]['discount_price'] = $cleanDiscountPrice;
                }

                if ($this->batchStock !== '' && !$this->isWmsActive) {
                    $this->vendor_variants[$variantId]['stock'] = $this->batchStock;
                }

                if ($this->batchMinPurchase !== '') {
                    $this->vendor_variants[$variantId]['min_purchase'] = $this->batchMinPurchase;
                }

                if ($this->batchMaxPurchase !== '') {
                    $this->vendor_variants[$variantId]['max_purchase'] = $this->batchMaxPurchase;
                }

                if ($cleanCartAmountStep !== '') {
                    $this->vendor_variants[$variantId]['cart_amount_step'] = $cleanCartAmountStep;
                }

                if ($this->batchPurchaseStep !== '') {
                    $this->vendor_variants[$variantId]['purchase_step'] = $this->batchPurchaseStep;
                }

                if ($this->batchDiscountStart !== '') {
                    $this->vendor_variants[$variantId]['discount_start_date'] = $this->batchDiscountStart;
                }

                if ($this->batchDiscountEnd !== '') {
                    $this->vendor_variants[$variantId]['discount_end_date'] = $this->batchDiscountEnd;
                }

                if ($this->batchDiscountStock !== '') {
                    $this->vendor_variants[$variantId]['discount_stock'] = $this->batchDiscountStock;
                }

                if ($this->batchMaxDiscountQty !== '') {
                    $this->vendor_variants[$variantId]['max_discount_purchase_qty'] = $this->batchMaxDiscountQty;
                }
            }
        }

        // Apply to custom variants
        foreach ($this->vendor_custom_variants as $index => $data) {
            if ($data['sell_this']) {
                if ($cleanPrice !== '') {
                    $this->vendor_custom_variants[$index]['price'] = $cleanPrice;
                }

                if ($this->batchDiscountPercent !== '') {
                    $pr = $cleanPrice !== '' ? (float)$cleanPrice : (float)str_replace(',', '', $data['price']);
                    if ($pr > 0) {
                        $p = (float)$this->batchDiscountPercent;
                        $d = $pr - ($pr * ($p / 100));
                        $this->vendor_custom_variants[$index]['discount_price'] = round($d);
                    }
                } elseif ($cleanDiscountPrice !== '') {
                    $this->vendor_custom_variants[$index]['discount_price'] = $cleanDiscountPrice;
                }

                if ($this->batchStock !== '' && !$this->isWmsActive) {
                    $this->vendor_custom_variants[$index]['stock'] = $this->batchStock;
                }

                if ($this->batchMinPurchase !== '') {
                    $this->vendor_custom_variants[$index]['min_purchase'] = $this->batchMinPurchase;
                }

                if ($this->batchMaxPurchase !== '') {
                    $this->vendor_custom_variants[$index]['max_purchase'] = $this->batchMaxPurchase;
                }

                if ($cleanCartAmountStep !== '') {
                    $this->vendor_custom_variants[$index]['cart_amount_step'] = $cleanCartAmountStep;
                }

                if ($this->batchPurchaseStep !== '') {
                    $this->vendor_custom_variants[$index]['purchase_step'] = $this->batchPurchaseStep;
                }

                if ($this->batchDiscountStart !== '') {
                    $this->vendor_custom_variants[$index]['discount_start_date'] = $this->batchDiscountStart;
                }

                if ($this->batchDiscountEnd !== '') {
                    $this->vendor_custom_variants[$index]['discount_end_date'] = $this->batchDiscountEnd;
                }

                if ($this->batchDiscountStock !== '') {
                    $this->vendor_custom_variants[$index]['discount_stock'] = $this->batchDiscountStock;
                }

                if ($this->batchMaxDiscountQty !== '') {
                    $this->vendor_custom_variants[$index]['max_discount_purchase_qty'] = $this->batchMaxDiscountQty;
                }
            }
        }

        // Reset batch properties after applying
        $this->batchPrice = '';
        $this->batchDiscountPercent = '';
        $this->batchDiscountPrice = '';
        $this->batchStock = '';
        $this->batchMinPurchase = '';
        $this->batchMaxPurchase = '';
        $this->batchCartAmountStep = '';
        $this->batchPurchaseStep = '';
        $this->batchDiscountStart = '';
        $this->batchDiscountEnd = '';
        $this->batchDiscountStock = '';
        $this->batchMaxDiscountQty = '';

        // Explicitly reassign arrays to force Livewire to detect deep updates
        $this->vendor_variants = $this->vendor_variants;
        $this->vendor_custom_variants = $this->vendor_custom_variants;

        $this->dispatch('notify', type: 'success', text: 'تنظیمات گروهی با موفقیت بر روی تمام تنوع‌های فعال اعمال شد.');
    }

    public function updated($name, $value)
    {
        if (str_starts_with($name, 'vendor_custom_variants.') && str_contains($name, '.attributes.')) {
            $parts = explode('.', $name);
            $index = $parts[1] ?? null;
            if ($index !== null && isset($this->vendor_custom_variants[$index])) {
                $customAttrs = $this->vendor_custom_variants[$index]['attributes'] ?? [];
                if (!$this->vendorCanManagePrices) {
                    $resolvedPrice = $this->resolveCatalogPrice($customAttrs);
                    $this->vendor_custom_variants[$index]['price'] = $resolvedPrice ? number_format($resolvedPrice) : '';
                }
            }
        }
    }

    private function resolveCatalogPrice(array $vendorAttrs)
    {
        if (!$this->selectedMasterProduct) return null;

        $masterVariants = $this->selectedMasterProduct->variants()->where('is_active', true)->get();

        $bestMatch = null;
        $bestScore = -1;

        foreach ($masterVariants as $var) {
            $varAttrs = $var->variant_attributes ?? [];
            $score = 0;
            $mismatch = false;

            foreach ($varAttrs as $key => $masterVal) {
                if (!isset($vendorAttrs[$key])) {
                    $mismatch = true;
                    break;
                }
                $vendorVal = $vendorAttrs[$key];
                if ($vendorVal === $masterVal) {
                    $score += 10;
                } elseif (is_string($masterVal) && $masterVal === 'هر ' . $key) {
                    $score += 1;
                } else {
                    $mismatch = true;
                    break;
                }
            }

            if (!$mismatch && $score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $var;
            }
        }

        return $bestMatch ? $bestMatch->price : null;
    }

    public function getFilteredCatalogPricesProperty()
    {
        if (empty($this->catalogPrices)) return [];

        $search = trim($this->catalogPricingSearch);
        if ($search === '') {
            return $this->catalogPrices;
        }

        return array_filter($this->catalogPrices, function($item) use ($search) {
            return str_contains($item['display_name'], $search) 
                || str_contains($item['variant_code'], $search)
                || str_contains(str_replace(',', '', $item['price'] ?? ''), $search);
        });
    }

    public function saveCatalogPrices()
    {
        if (!$this->isAdminPricingAllowed) {
            abort(403, 'شما اجازه قیمت‌گذاری کاتالوگ را ندارید.');
        }

        foreach ($this->catalogPrices as $variantId => $data) {
            $cleanPrice = isset($data['price']) && $data['price'] !== '' ? (int)str_replace(',', '', $data['price']) : null;
            $cleanDiscount = isset($data['discount_price']) && $data['discount_price'] !== '' ? (int)str_replace(',', '', $data['discount_price']) : null;

            $startDate = null;
            if (!empty($data['discount_start_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_start_date'])) {
                $startDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_start_date'])->toCarbon();
            }

            $endDate = null;
            if (!empty($data['discount_end_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_end_date'])) {
                $endDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_end_date'])->toCarbon();
            }

            $cleanCartStep = isset($data['cart_amount_step']) && $data['cart_amount_step'] !== '' ? (int)str_replace(',', '', $data['cart_amount_step']) : null;

            $payload = [
                'price' => $cleanPrice,
                'discount_price' => $cleanDiscount,
                'discount_start_date' => $startDate,
                'discount_end_date' => $endDate,
                'discount_stock' => $data['discount_stock'] !== '' ? (int)$data['discount_stock'] : null,
                'max_discount_purchase_qty' => $data['max_discount_purchase_qty'] !== '' ? (int)$data['max_discount_purchase_qty'] : null,
                'reorder_point' => $data['reorder_point'] !== '' ? (int)$data['reorder_point'] : 5,
                'min_purchase_qty' => $data['min_purchase'] !== '' ? (int)$data['min_purchase'] : 1,
                'max_purchase_qty' => $data['max_purchase'] !== '' ? (int)$data['max_purchase'] : null,
                'cart_amount_step' => $cleanCartStep,
                'purchase_step' => $data['purchase_step'] !== '' ? (int)$data['purchase_step'] : null,
            ];

            $variant = ProductVariant::where('id', $variantId)
                ->where('master_product_id', $this->master_product_id)
                ->first();

            if ($variant) {
                $variant->update($payload);

                // Also update any existing VendorProduct records for this variant
                \Modules\Market\Entities\VendorProduct::where('product_variant_id', $variantId)
                    ->update([
                        'price' => $cleanPrice,
                        'discount_price' => $cleanDiscount,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => $payload['discount_stock'],
                        'max_discount_purchase_qty' => $payload['max_discount_purchase_qty'],
                        'min_purchase_qty' => $payload['min_purchase_qty'],
                        'max_purchase_qty' => $payload['max_purchase_qty'],
                        'cart_amount_step' => $payload['cart_amount_step'],
                        'purchase_step' => $payload['purchase_step'],
                    ]);
            }
        }

        $this->dispatch('notify', type: 'success', text: 'قیمت‌های مرجع و تنظیمات تجاری کاتالوگ با موفقیت بروزرسانی و همگام‌سازی شدند.');
        $this->loadVariants();
    }

    public function render()
    {
        return view('market::livewire.vendor.product-form');
    }
}
