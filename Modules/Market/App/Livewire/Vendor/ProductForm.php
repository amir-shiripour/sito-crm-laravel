<?php

namespace Modules\Market\App\Livewire\Vendor;

use Livewire\Component;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\MarketSetting;
use Morilog\Jalali\Jalalian;

class ProductForm extends Component
{
    public $searchQuery = '';
    public $master_product_id = '';
    public $selectedMasterProduct = null;

    public $available_variants = [];
    public $vendor_variants = [];

    // برای حالت ساخت تنوع توسط فروشنده
    public $vendor_custom_variants = [];
    public $allow_custom_variants = false;

    // نگهداری گزینه‌های مجاز برای انتخاب فروشنده
    public $allowed_axes_options = [];

    public function mount()
    {
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
            $this->available_variants = $this->selectedMasterProduct->variants->where('is_active', true);

            foreach ($this->available_variants as $variant) {
                $attributes = $variant->variant_attributes ?? [];
                $variantName = empty($attributes) ? 'تنوع استاندارد' : implode(' | ', (array)$attributes);

                $existing = VendorProduct::where('vendor_id', $vendorId)->where('product_variant_id', $variant->id)->first();

                $this->vendor_variants[$variant->id] = [
                    'display_name' => $variantName,
                    'sell_this' => $existing ? true : false,
                    'price' => $existing ? $existing->price : '',
                    'discount_price' => $existing ? $existing->discount_price : '',
                    // 💡 FIX: فرمت کردن تاریخ و زمان میلادی به شمسی برای نمایش
                    'discount_start_date' => $existing && $existing->discount_start_date ? Jalalian::fromCarbon($existing->discount_start_date)->format('Y/m/d H:i') : '',
                    'discount_end_date' => $existing && $existing->discount_end_date ? Jalalian::fromCarbon($existing->discount_end_date)->format('Y/m/d H:i') : '',
                    'discount_stock' => $existing ? $existing->discount_stock : '',
                    'max_discount_purchase_qty' => $existing ? $existing->max_discount_purchase_qty : '',
                    'stock' => $existing ? $existing->stock : 0,
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

        foreach ($this->vendor_variants as $variantId => $data) {

            if (!$data['sell_this']) {
                VendorProduct::where('vendor_id', $vendor->id)->where('product_variant_id', $variantId)->delete();
                continue;
            }

            if (!empty($data['price'])) {
                $cleanPrice = str_replace(',', '', $data['price']);
                $cleanDiscount = str_replace(',', '', $data['discount_price']);

                // 💡 FIX: تبدیل تاریخ و زمان شمسی به میلادی
                $startDate = null;
                if (!empty($data['discount_start_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_start_date'])) {
                    $startDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_start_date'])->toCarbon();
                }

                $endDate = null;
                if (!empty($data['discount_end_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_end_date'])) {
                    $endDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_end_date'])->toCarbon();
                }

                VendorProduct::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'product_variant_id' => $variantId,
                    ],
                    [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => !empty($data['discount_stock']) ? $data['discount_stock'] : null,
                        'max_discount_purchase_qty' => !empty($data['max_discount_purchase_qty']) ? $data['max_discount_purchase_qty'] : null,
                        'stock' => $data['stock'] ?: 0,
                        'reorder_point' => $data['reorder_point'] ?? 5,
                        'min_purchase_qty' => $data['min_purchase'] ?: 1,
                        'max_purchase_qty' => !empty($data['max_purchase']) ? $data['max_purchase'] : null,
                        'status' => $data['is_active'] ? $defaultStatus : 'draft',
                    ]
                );
                $savedCount++;
            }
        }

        if ($this->allow_custom_variants) {
            foreach ($this->vendor_custom_variants as $data) {
                if (!$data['sell_this'] || empty($data['price'])) continue;

                $hasEmptyAttr = false;
                foreach ($data['attributes'] as $val) {
                    if (empty($val)) $hasEmptyAttr = true;
                }
                if ($hasEmptyAttr) continue;

                $cleanPrice = str_replace(',', '', $data['price']);
                $cleanDiscount = str_replace(',', '', $data['discount_price']);

                $sortedAttributes = $data['attributes'];
                ksort($sortedAttributes);

                $attrHash = md5(json_encode($sortedAttributes));
                $vCode = $this->selectedMasterProduct->crm_code . '-V' . substr($attrHash, 0, 8);

                $productVariant = ProductVariant::firstOrCreate(
                    [
                        'master_product_id' => $this->selectedMasterProduct->id,
                        'variant_code' => $vCode,
                    ],
                    [
                        'variant_attributes' => $sortedAttributes,
                        'is_active' => true
                    ]
                );

                // 💡 FIX: تبدیل تاریخ و زمان شمسی به میلادی
                $startDate = null;
                if (!empty($data['discount_start_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_start_date'])) {
                    $startDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_start_date'])->toCarbon();
                }

                $endDate = null;
                if (!empty($data['discount_end_date']) && preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $data['discount_end_date'])) {
                    $endDate = Jalalian::fromFormat('Y/m/d H:i', $data['discount_end_date'])->toCarbon();
                }

                VendorProduct::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'product_variant_id' => $productVariant->id,
                    ],
                    [
                        'price' => $cleanPrice,
                        'discount_price' => !empty($cleanDiscount) ? $cleanDiscount : null,
                        'discount_start_date' => $startDate,
                        'discount_end_date' => $endDate,
                        'discount_stock' => !empty($data['discount_stock']) ? $data['discount_stock'] : null,
                        'max_discount_purchase_qty' => !empty($data['max_discount_purchase_qty']) ? $data['max_discount_purchase_qty'] : null,
                        'stock' => $data['stock'] ?: 0,
                        'reorder_point' => $data['reorder_point'] ?? 5,
                        'min_purchase_qty' => $data['min_purchase'] ?: 1,
                        'max_purchase_qty' => !empty($data['max_purchase']) ? $data['max_purchase'] : null,
                        'status' => $data['is_active'] ? $defaultStatus : 'draft',
                    ]
                );
                $savedCount++;
            }
        }

        $this->dispatch('notify', type: 'success', text: "تغییرات انبار شما با موفقیت ذخیره شد. ({$savedCount} تنوع)");
        return redirect()->route('user.market.vendor.products.index');
    }

    public function render()
    {
        return view('market::livewire.vendor.product-form');
    }
}
