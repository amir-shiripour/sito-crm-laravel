<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\DisplayCategory;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\MarketAttribute;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\App\Services\ProductService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUploadTrait;
use Illuminate\Validation\Rule;

class MasterProductForm extends Component
{
    use WithFileUploads, FileUploadTrait;

    public ?MasterProduct $product = null;

    public $storeType;
    public $vendorCanCreateVariants;
    public bool $vendorCanManagePrices = true;
    public bool $isVendor = false;
    public $currentStep = 1;

    public $catOptions = [];
    public $displayCategoryOptions = [];
    public $selectedDisplayCategories = [];
    public bool $separate_category_enabled = false;

    // فیلدهای محصول
    public $title = '', $slug = '', $brand_id = '', $category_id = '', $status = 'active';
    public $crm_code = 'اتوماتیک';
    public $gtin = '';
    public $barcode = '';
    public $short_description = '';
    public $description = '';
    public $single_sell = false;
    public $enable_reviews = true;
    public $enable_questions = true;
    public $weight = '';
    public $length = '';
    public $width = '';
    public $height = '';
    public $shipping_class = 'standard';

    public $categoryFields = [];
    public $dynamicAttributes = [];

    public $main_image;
    public $existing_main_image;
    public $gallery_images = [];
    public $existing_gallery = [];

    // تنوع‌ها
    public $variants = [];
    public $variantAxes = [];
    public $selectedAxisValues = [];

    public function mount(?MasterProduct $product = null)
    {
        $this->product = $product ?? new MasterProduct();
        $this->storeType = MarketSetting::getValue('system.store_type', 'multi');
        $this->vendorCanCreateVariants = (bool) MarketSetting::getValue('vendors.vendor_can_create_variants', false);
        $user = auth()->user();
        $this->isVendor = !$user->hasAnyRole(['super-admin', 'admin']);
        $this->vendorCanManagePrices = (bool) MarketSetting::getValue('vendors.vendor_can_manage_prices', true);

        if ($this->product->exists) {
            // 💡 FIX 1: Explicitly assign properties to avoid filling image properties with strings
            $this->title = $this->product->title;
            $this->slug = $this->product->slug;
            $this->brand_id = $this->product->brand_id;
            $this->category_id = $this->product->category_id;
            $this->status = $this->product->status;
            $this->crm_code = $this->product->crm_code;
            $this->gtin = $this->product->gtin;
            $this->barcode = $this->product->barcode;
            $this->short_description = $this->product->short_description;
            $this->description = $this->product->description;
            $this->single_sell = (bool) $this->product->single_sell;
            $this->enable_reviews = (bool) $this->product->enable_reviews;
            $this->enable_questions = $this->product->exists ? (bool) $this->product->enable_questions : true;
            $this->weight = $this->product->weight;
            $this->length = $this->product->length;
            $this->width = $this->product->width;
            $this->height = $this->product->height;
            $this->shipping_class = $this->product->shipping_class ?? 'standard';

            $this->dynamicAttributes = $this->product->attributes ?? [];
            $this->existing_main_image = $this->product->main_image;
            $this->existing_gallery = $this->product->gallery_images ?? [];

            $this->loadCategoryFields($this->category_id);

            foreach ($this->product->variants as $var) {
                $this->variants[] = [
                    'id' => $var->id,
                    'values' => $var->variant_attributes ?? [],
                    'price' => $var->price ? number_format($var->price) : '',
                    'is_active' => (bool)$var->is_active,
                ];
            }

            $permissions = $this->product->variant_axes_permissions ?? [];
            foreach ($this->variantAxes as $axis) {
                $axisName = $axis['name'];
                $this->selectedAxisValues[$axisName] = $permissions[$axisName] ?? [];
            }
            if (empty($permissions)) {
                $this->parseExistingVariantsToSelectedAxes();
            }
        } else {
            $this->clearAllVariants();
            if ($this->isVendor) {
                $this->status = MarketSetting::getValue('vendors.vendor_catalog_default_status', 'draft');
            }
            // خودکارسازی انتخاب برند در صورتی که فقط یک برند فعال وجود داشته باشد
            $activeBrands = Brand::where('is_active', true)->get();
            if ($activeBrands->count() === 1) {
                $this->brand_id = (string)$activeBrands->first()->id;
            }
        }

        // بررسی سیستم دسته‌بندی مجزا
        $this->separate_category_enabled = (bool) MarketSetting::getValue('system.separate_category_enabled', false);
        if ($this->separate_category_enabled) {
            $allDisplayCats = DisplayCategory::where('is_active', true)->orderBy('name')->get();
            $this->displayCategoryOptions = $this->buildDisplayCategoryOptions($allDisplayCats);
            $this->selectedDisplayCategories = $this->product->exists 
                ? $this->product->displayCategories()->pluck('display_category_id')->map(fn($id) => (string)$id)->toArray() 
                : [];
        }
    }

    public function updatedCategoryId($id) {
        $this->loadCategoryFields($id);
        $this->generateCode();
        if (!$this->product->exists) {
            $this->clearAllVariants();
        }
    }

    public function updatedBrandId($value)
    {
        $this->generateCode();
        if ($value && $this->category_id) {
            $cat = Category::find($this->category_id);
            if ($cat && (string)$cat->brand_id !== (string)$value) {
                $this->category_id = '';
                $this->categoryFields = [];
                $this->variantAxes = [];
                $this->selectedAxisValues = [];
            }
        }
    }

    public function setStep($step)
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        } elseif ($step > $this->currentStep) {
            $this->validateStep($this->currentStep);
            $this->currentStep = $step;
        }
    }

    public function nextStep()
    {
        $this->validateStep($this->currentStep);
        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    private function validateStep($step)
    {
        $rules = [
            1 => [
                'title' => 'required|string|max:255',
                'slug' => ['required', 'string', 'max:255', Rule::unique('market_master_products', 'slug')->ignore($this->product->id)],
                'brand_id' => 'required',
                'category_id' => 'required',
            ],
            2 => ['status' => 'required|in:draft,active,archived'],
            3 => [
                'weight' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
            ],
        ];
        if (isset($rules[$step])) {
            $this->validate($rules[$step]);
        }
    }

    private function loadCategoryFields($categoryId) {
        if (!$categoryId) return;
        $category = Category::find($categoryId);
        $this->categoryFields = $category->target_attributes ?? [];
        $variantFieldIds = $category->variant_fields ?? [];

        $this->variantAxes = [];
        $this->selectedAxisValues = [];

        if (!empty($variantFieldIds)) {
            $attributes = MarketAttribute::with('values')->whereIn('id', $variantFieldIds)->get();
            foreach ($attributes as $attr) {
                $vals = $attr->values->toArray();
                array_unshift($vals, ['id' => 'any', 'value' => 'هر ' . $attr->name, 'meta_value' => 'any']);
                $this->variantAxes[] = ['id' => $attr->id, 'name' => $attr->name, 'type' => $attr->type, 'values' => $vals];
                $this->selectedAxisValues[$attr->name] = [];
            }
        }
    }

    private function generateCode() {
        if ($this->brand_id && $this->category_id && !$this->product->exists) {
            $this->crm_code = (new ProductService())->generateCrmCode($this->brand_id, $this->category_id);
        }
    }

    public function removeVariant($index) {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    private function parseExistingVariantsToSelectedAxes() {
        if (empty($this->variantAxes) || empty($this->variants)) return;
        foreach ($this->variantAxes as $axis) {
            $axisName = $axis['name'];
            $foundValues = array_unique(array_column(array_column($this->variants, 'values'), $axisName));
            if (empty($foundValues) || in_array('هر ' . $axisName, $foundValues)) {
                $this->selectedAxisValues[$axisName] = ['هر ' . $axisName];
            } else {
                $this->selectedAxisValues[$axisName] = array_values($foundValues);
            }
        }
    }

    public function clearAllVariants() {
        $this->variants = [];
    }

    private function expandVariantValues(array $values)
    {
        $expanded = [$values];

        foreach ($this->variantAxes as $axis) {
            $axisName = $axis['name'];
            $axisValues = [];
            foreach ($axis['values'] as $opt) {
                if ($opt['id'] !== 'any') {
                    $axisValues[] = $opt['value'];
                }
            }

            if (empty($axisValues)) {
                continue;
            }

            $nextExpanded = [];
            foreach ($expanded as $item) {
                if (isset($item[$axisName]) && is_string($item[$axisName]) && str_starts_with($item[$axisName], 'هر ')) {
                    // Expand this axis
                    foreach ($axisValues as $val) {
                        $newItem = $item;
                        $newItem[$axisName] = $val;
                        $nextExpanded[] = $newItem;
                    }
                } else {
                    $nextExpanded[] = $item;
                }
            }
            $expanded = $nextExpanded;
        }

        return $expanded;
    }

    public function generateAllCombinations()
    {
        if (empty($this->variantAxes)) return;

        $axesValuesToCombine = [];
        foreach ($this->variantAxes as $axis) {
            $axisName = $axis['name'];
            $selectedForAxis = $this->selectedAxisValues[$axisName] ?? [];

            if (empty($selectedForAxis)) {
                $this->dispatch('notify', type: 'error', text: "برای ساخت ترکیب، باید برای محور '{$axisName}' حداقل یک مقدار انتخاب کنید.");
                return;
            }

            if ($this->storeType === 'single') {
                $resolvedValues = [];
                foreach ($selectedForAxis as $val) {
                    if (is_string($val) && str_starts_with($val, 'هر ')) {
                        // Expand "any X" to all concrete values of this axis
                        foreach ($axis['values'] as $opt) {
                            if ($opt['id'] !== 'any') {
                                $resolvedValues[] = $opt['value'];
                            }
                        }
                    } else {
                        $resolvedValues[] = $val;
                    }
                }
                $axesValuesToCombine[$axisName] = $resolvedValues;
            } else {
                $axesValuesToCombine[$axisName] = $selectedForAxis;
            }
        }

        // اگر به فروشنده اجازه داده شده و می‌تواند قیمت‌گذاری کند، ترکیبی نساز، فقط انتخاب‌ها را نگه دار
        if ($this->storeType === 'multi' && $this->vendorCanCreateVariants && $this->vendorCanManagePrices) {
            $this->dispatch('notify', type: 'info', text: 'گزینه‌های مجاز برای فروشنده مشخص شد. نیازی به ساخت ترکیب در این مرحله نیست.');
            return;
        }

        $combinations = [[]];
        foreach ($axesValuesToCombine as $key => $values) {
            $append = [];
            foreach ($combinations as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }
            $combinations = $append;
        }

        $existingDbVariants = collect($this->variants)->filter(fn($v) => !empty($v['id']))->values()->toArray();
        $this->variants = $existingDbVariants;

        foreach ($combinations as $combo) {
            $exists = collect($this->variants)->contains(fn($v) => $v['values'] == $combo);
            if (!$exists) {
                $this->variants[] = ['id' => null, 'values' => $combo, 'price' => '', 'is_active' => true];
            }
        }
        $this->dispatch('notify', type: 'success', text: 'ترکیبات بر اساس انتخاب‌های شما با موفقیت ایجاد شدند.');
    }

    public function removeExistingGalleryImage($index) {
        if (isset($this->existing_gallery[$index])) {
            Storage::disk('public')->delete($this->existing_gallery[$index]);
            unset($this->existing_gallery[$index]);
            $this->existing_gallery = array_values($this->existing_gallery);
            $this->product->gallery_images = $this->existing_gallery;
            $this->product->save();
        }
    }

    public function removeNewGalleryImage($index) {
        unset($this->gallery_images[$index]);
        $this->gallery_images = array_values($this->gallery_images);
    }

    public function save()
    {
        if ($this->isVendor) {
            $vendorCanCreate = (bool) MarketSetting::getValue('vendors.vendor_can_create_catalog', false);
            if (!$vendorCanCreate) {
                abort(403, 'شما اجازه ثبت یا تغییر کاتالوگ محصولات را ندارید.');
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('market_master_products', 'slug')->ignore($this->product->id)],
            'brand_id' => 'required', 'category_id' => 'required', 'status' => 'required|in:draft,active,archived',
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('market_master_products', 'barcode')->ignore($this->product->id)],
            'gtin' => 'nullable|string|max:255',
        ];
        if ($this->main_image && !is_string($this->main_image)) {
            $rules['main_image'] = 'image|max:5120';
        }
        if ($this->gallery_images) {
            $rules['gallery_images.*'] = 'image|max:5120';
        }
        $this->validate($rules);


        if (!$this->product->exists) $this->generateCode();

        $imagePath = $this->existing_main_image;
        if ($this->main_image && !is_string($this->main_image)) {
            if ($this->existing_main_image) Storage::disk('public')->delete($this->existing_main_image);
            $imagePath = $this->uploadFile($this->main_image, 'products/masters', 'public');
        }

        $finalGallery = $this->existing_gallery;
        if (!empty($this->gallery_images)) {
            foreach ($this->gallery_images as $img) {
                if ($img && !is_string($img)) {
                    $finalGallery[] = $this->uploadFile($img, 'products/gallery', 'public');
                }
            }
        }

        $this->product->fill([
            'title' => $this->title, 'slug' => $this->slug, 'brand_id' => $this->brand_id, 'category_id' => $this->category_id,
            'crm_code' => $this->crm_code, 'gtin' => $this->gtin, 'barcode' => $this->barcode, 'short_description' => $this->short_description,
            'description' => $this->description, 'single_sell' => $this->single_sell, 'enable_reviews' => $this->enable_reviews,
            'enable_questions' => $this->enable_questions,
            'weight' => empty($this->weight) ? null : $this->weight, 'length' => empty($this->length) ? null : $this->length,
            'width' => empty($this->width) ? null : $this->width, 'height' => empty($this->height) ? null : $this->height,
            'attributes' => $this->dynamicAttributes, 'status' => $this->status,
            'main_image' => $imagePath,
            'gallery_images' => $finalGallery,
            'variant_axes_permissions' => ($this->storeType === 'multi' && $this->vendorCanCreateVariants && !empty($this->selectedAxisValues)) ? $this->selectedAxisValues : null,
        ])->save();

        if ($this->separate_category_enabled) {
            $this->product->displayCategories()->sync($this->selectedDisplayCategories);
        }

        $maxVariantSerial = ProductVariant::where('master_product_id', $this->product->id)->count();
        $keptVariantIds = [];

        if (empty($this->variantAxes) && empty($this->variants)) {
            $this->variants[] = ['id' => null, 'values' => ['name' => 'استاندارد'], 'is_active' => true];
        }

        // اگر به فروشنده اجازه داده شده و می‌تواند قیمت‌گذاری کند، هیچ تنوعی در این مرحله نساز (مگر اینکه قیمت‌گذاری فروشندگان غیرفعال باشد و ادمین باید ترکیب‌ها و قیمت‌ها را تعریف کند)
        if (!($this->storeType === 'multi' && $this->vendorCanCreateVariants && $this->vendorCanManagePrices)) {
            $expandedVariants = [];
            foreach ($this->variants as $var) {
                if ($this->storeType === 'single') {
                    $expandedVals = $this->expandVariantValues($var['values'] ?? []);
                    foreach ($expandedVals as $ev) {
                        $expandedVariants[] = [
                            'id' => null,
                            'values' => $ev,
                            'price' => $var['price'] ?? '',
                            'is_active' => $var['is_active'] ?? true,
                        ];
                    }
                } else {
                    $expandedVariants[] = $var;
                }
            }

            foreach ($expandedVariants as $var) {
                // Find if a variant with these exact attributes already exists for this master product to reuse its ID
                $existingVariant = null;
                if (!empty($var['values'])) {
                    $existingVariant = ProductVariant::where('master_product_id', $this->product->id)
                        ->whereJsonContains('variant_attributes', $var['values'])
                        ->first();
                }

                $vCode = $existingVariant ? $existingVariant->variant_code : ($var['id'] ? ProductVariant::find($var['id'])->variant_code : $this->product->crm_code . '-' . str_pad(++$maxVariantSerial, 2, '0', STR_PAD_LEFT));
                $variantValues = $var['values'] ?? (empty($this->variantAxes) ? ['name' => 'استاندارد'] : []);
                $cleanPrice = isset($var['price']) && $var['price'] !== '' ? str_replace(',', '', $var['price']) : null;

                $savedVariant = ProductVariant::updateOrCreate(
                    ['id' => $existingVariant ? $existingVariant->id : $var['id']],
                    [
                        'master_product_id' => $this->product->id, 
                        'variant_code' => $vCode, 
                        'variant_attributes' => $variantValues, 
                        'price' => $cleanPrice,
                        'is_active' => $var['is_active'] ?? true
                    ]
                );
                $keptVariantIds[] = $savedVariant->id;
            }
        }

        if ($this->product->exists) {
            ProductVariant::where('master_product_id', $this->product->id)->whereNotIn('id', $keptVariantIds)->delete();
        }

        $this->dispatch('notify', type: 'success', text: 'محصول و تنوع‌های انتخابی با موفقیت در کاتالوگ ثبت شد.');
        return redirect()->route('user.market.master-products.index');
    }

    public function render()
    {
        $categoriesQuery = Category::where('is_active', true)->orderBy('code_offset');
        if ($this->brand_id) {
            $categoriesQuery->where('brand_id', $this->brand_id);
        } else {
            $categoriesQuery->whereNull('brand_id');
        }
        $categories = $categoriesQuery->get();

        $this->catOptions = array_merge(
            [['value' => '', 'label' => 'انتخاب دسته...', 'depth' => 0, 'isSub' => false]],
            $this->buildCategoryOptions($categories)
        );

        return view('market::livewire.admin.master-product-form', [
            'brands' => Brand::where('is_active', true)->get(),
        ]);
    }

    private function buildCategoryOptions($categories, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);

        foreach ($filtered as $cat) {
            $options[] = [
                'value' => (string)$cat->id,
                'label' => $cat->name,
                'depth' => $depth,
                'isSub' => $depth > 0
            ];
            $options = array_merge($options, $this->buildCategoryOptions($categories, $cat->id, $depth + 1));
        }

        return $options;
    }

    private function buildDisplayCategoryOptions($categories, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);

        foreach ($filtered as $cat) {
            $options[] = [
                'value' => (string)$cat->id,
                'label' => $cat->name,
                'depth' => $depth,
                'isSub' => $depth > 0,
                'parent_id' => $cat->parent_id ? (string)$cat->parent_id : null
            ];
            $options = array_merge($options, $this->buildDisplayCategoryOptions($categories, $cat->id, $depth + 1));
        }

        return $options;
    }
}
