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

    public function mount()
    {
        $this->isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);

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
    }

    private function calculateAllowedAxes()
    {
        $this->allowed_axes_options = [];
        $permissions = $this->selectedMasterProduct->variant_axes_permissions ?? [];
        if (empty($permissions)) return;
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
            $this->available_variants = $this->selectedMasterProduct->variants()->where('is_active', true)->get();

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

                $this->vendor_variants[$variant->id] = [
                    'display_name' => $variantName,
                    'sell_this' => $existing ? true : false,
                    'price' => $existing ? $existing->price : '',
                    'discount_price' => $existing ? $existing->discount_price : '',
                    'discount_start_date' => $existing && $existing->discount_start_date ? Jalalian::fromCarbon($existing->discount_start_date)->format('Y/m/d H:i') : '',
                    'discount_end_date' => $existing && $existing->discount_end_date ? Jalalian::fromCarbon($existing->discount_end_date)->format('Y/m/d H:i') : '',
                    'discount_stock' => $existing ? $existing->discount_stock : '',
                    'max_discount_purchase_qty' => $existing ? $existing->max_discount_purchase_qty : '',
                    'stock' => $stockValue, // Note: the blade file MUST use $isWmsActive to set this input as disabled/readonly
                    'reorder_point' => $existing ? $existing->reorder_point : 5,
                    'min_purchase' => $existing ? $existing->min_purchase_qty : 1,
                    'max_purchase' => $existing ? $existing->max_purchase_qty : '',
                    'is_active' => $existing ? in_array($existing->status, ['published', 'pending_review']) : true,
                    'is_custom' => false
                ];
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
                if (!$data['sell_this']) {
                    VendorProduct::where('vendor_id', $vendor->id)->where('product_variant_id', $variantId)->delete();
                    continue;
                }

                if (!empty($data['price'])) {
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

                    $payload = [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => !empty($data['discount_stock']) ? $data['discount_stock'] : null,
                        'max_discount_purchase_qty' => !empty($data['max_discount_purchase_qty']) ? $data['max_discount_purchase_qty'] : null,
                        'reorder_point' => $data['reorder_point'] ?? 5,
                        'min_purchase_qty' => $data['min_purchase'] ?: 1,
                        'max_purchase_qty' => !empty($data['max_purchase']) ? $data['max_purchase'] : null,
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

                if (!empty($customData['price'])) {
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

                    $variant = ProductVariant::firstOrCreate([
                        'master_product_id' => $this->master_product_id,
                        'variant_attributes' => json_encode($customData['attributes']),
                    ], [
                        'variant_code' => $this->selectedMasterProduct->crm_code . '-' . uniqid(),
                        'is_active' => true,
                    ]);

                    $payload = [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => !empty($customData['discount_stock']) ? $customData['discount_stock'] : null,
                        'max_discount_purchase_qty' => !empty($customData['max_discount_purchase_qty']) ? $customData['max_discount_purchase_qty'] : null,
                        'reorder_point' => $customData['reorder_point'] ?? 5,
                        'min_purchase_qty' => $customData['min_purchase'] ?: 1,
                        'max_purchase_qty' => !empty($customData['max_purchase']) ? $customData['max_purchase'] : null,
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

    public function render()
    {
        return view('market::livewire.vendor.product-form');
    }
}
