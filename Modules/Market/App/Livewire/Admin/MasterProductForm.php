<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\App\Services\ProductService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MasterProductForm extends Component
{
    use WithFileUploads;

    public ?MasterProduct $product = null;

    public $title = '', $slug = '', $brand_id = '', $category_id = '', $description = '', $status = 'draft';
    public $crm_code = 'اتوماتیک';

    // فیلدهای داینامیک
    public $categoryFields = [];
    public $dynamicAttributes = [];

    // فایل‌ها
    public $main_image;
    public $existing_main_image;
    public $gallery_images = [];
    public $existing_gallery = [];

    // تنوع‌ها (Variants)
    public $variants = [];
    public $variantAxes = [];

    public function mount(?MasterProduct $product = null)
    {
        $this->product = $product ?? new MasterProduct();

        if ($this->product->exists) {
            $this->title = $this->product->title;
            $this->brand_id = $this->product->brand_id;
            $this->category_id = $this->product->category_id;
            $this->description = $this->product->description;
            $this->status = $this->product->status;
            $this->crm_code = $this->product->crm_code;

            $this->dynamicAttributes = $this->product->attributes ?? [];
            $this->loadCategoryFields($this->category_id);

            $this->existing_main_image = $this->product->main_image;
            $this->existing_gallery = $this->product->gallery_images ?? [];

            // 💡 اصلاح لود تنوع‌ها برای ساختار جدید (Values)
            foreach ($this->product->variants as $var) {
                $this->variants[] = [
                    'id' => $var->id,
                    'values' => $var->variant_attributes ?? [],
                    'is_active' => $var->is_active
                ];
            }
        } else {
            // ساختار خام برای محصول جدید
            $this->variants[] = ['id' => null, 'values' => [], 'is_active' => true];
        }
    }

    public function updatedBrandId() { $this->generateCode(); }

    public function updatedCategoryId($id) {
        $this->loadCategoryFields($id);
        $this->generateCode();
    }

    private function loadCategoryFields($categoryId) {
        if (!$categoryId) return;
        $category = Category::find($categoryId);
        $this->categoryFields = $category->target_attributes ?? [];
        $this->variantAxes = $category->variant_fields ?? [];
    }

    private function generateCode() {
        if ($this->brand_id && $this->category_id && !$this->product->exists) {
            $service = new ProductService();
            $this->crm_code = $service->generateCrmCode($this->brand_id, $this->category_id);
        }
    }

    public function addVariant() {
        $this->variants[] = ['id' => null, 'values' => [], 'is_active' => true];
    }

    public function removeVariant($index) {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
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
        // 💡 اصلاح اعتبارسنجی (برداشتن گیر اضافی روی نام)
        $this->validate([
            'title' => 'required|string|max:255',
            'brand_id' => 'required',
            'category_id' => 'required',
            'status' => 'required|in:draft,active,archived',
            'main_image' => 'nullable|image|max:5120',
            'gallery_images.*' => 'nullable|image|max:5120',
        ]);

        if (!$this->product->exists) {
            $this->generateCode();
        }

        $imagePath = $this->existing_main_image;
        if ($this->main_image) {
            if ($this->existing_main_image) Storage::disk('public')->delete($this->existing_main_image);
            $imagePath = $this->main_image->store('products/masters', 'public');
        }

        $finalGallery = $this->existing_gallery;
        if (!empty($this->gallery_images)) {
            foreach ($this->gallery_images as $img) {
                $finalGallery[] = $img->store('products/gallery', 'public');
            }
        }

        $this->product->fill([
            'title' => $this->title,
            'slug' => $this->product->exists ? $this->product->slug : Str::slug($this->title) . '-' . rand(1000,9999),
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'crm_code' => $this->crm_code,
            'description' => $this->description,
            'main_image' => $imagePath,
            'gallery_images' => $finalGallery,
            'attributes' => $this->dynamicAttributes,
            'status' => $this->status,
        ])->save();

        $maxVariantSerial = 0;
        if ($this->product->exists) {
            $existingVariants = ProductVariant::where('master_product_id', $this->product->id)->get();
            foreach ($existingVariants as $ev) {
                $parts = explode('-', $ev->variant_code);
                $lastPart = end($parts);
                if (is_numeric($lastPart) && (int)$lastPart > $maxVariantSerial) {
                    $maxVariantSerial = (int)$lastPart;
                }
            }
        }

        // ذخیره تنوع‌ها
        foreach ($this->variants as $var) {
            if (empty($var['id'])) {
                $maxVariantSerial++;
                $vCode = $this->product->crm_code . '-' . str_pad($maxVariantSerial, 2, '0', STR_PAD_LEFT);
            } else {
                $vCode = ProductVariant::find($var['id'])->variant_code;
            }

            // 💡 هندل کردن کالاهای بدون تنوع (مثلاً یک کتاب که سایز و رنگ نداره)
            $variantValues = $var['values'] ?? [];
            if (empty($this->variantAxes)) {
                $variantValues = ['name' => 'استاندارد'];
            }

            ProductVariant::updateOrCreate(
                ['id' => $var['id']],
                [
                    'master_product_id' => $this->product->id,
                    'variant_code' => $vCode,
                    'variant_attributes' => $variantValues,
                    'is_active' => $var['is_active'] ?? true
                ]
            );
        }

        $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت در کاتالوگ ثبت شد.');
        return redirect()->route('user.market.master-products.index');
    }

    public function render()
    {
        return view('market::livewire.admin.master-product-form', [
            'brands' => Brand::where('is_active', true)->get(),
            'parentCategories' => Category::whereNull('parent_id')->with('children')->get()
        ]);
    }
}
