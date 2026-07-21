<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\MarketAttribute; // 💡 NEW
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryManager extends Component
{
    use \Livewire\WithFileUploads;

    public $category_id, $name, $parent_id, $brand_id, $code_offset, $is_active = true;
    public $icon, $existing_icon;
    public $has_parent_brand = false; // جهت غیر فعال کردن ویرایش برند در صورت داشتن والد با برند
    public $parentOptions = []; // برای همگام‌سازی سریع و زنده لیست کشویی والد با ویژگی‌های برند

    // Deletion Modal State Properties
    public $confirmingDeletion = false;
    public $deleteTargetId = null;
    public $deleteTargetName = '';
    public $deleteProductCount = 0;
    public $deleteSubCategoryCount = 0;
    public $deleteActionType = 'move'; // 'move' or 'delete_all'
    public $deleteMoveToCategoryId = '';
    public $deleteConfirmName = '';
    public $deleteMoveOptions = [];

    // آرایه‌های فرم‌ساز
    public $target_attributes = []; // فیلدهای عمومی (مثل وزن، ابعاد)
    public $variant_fields = [];    // محورهای تنوع (مثل رنگ، حافظه، گارانتی) - حالا ID ویژگی‌ها را ذخیره می‌کند

    public $isFormOpen = false;

    public function updatedBrandId($value)
    {
        // با تغییر برند به صورت دستی، اگر والد انتخاب شده‌ای وجود دارد که برندش متفاوت است، والد ریست شود
        if ($this->parent_id) {
            $parent = Category::find($this->parent_id);
            if ($parent) {
                $parentBrandId = $parent->brand_id ? (string)$parent->brand_id : '';
                $currentBrandId = $value ? (string)$value : '';
                if ($parentBrandId !== $currentBrandId) {
                    $this->parent_id = null;
                    $this->has_parent_brand = false;
                }
            }
        }
    }

    public function updatedParentId($value)
    {
        if ($value) {
            $parent = Category::find($value);
            if ($parent) {
                if ($parent->brand_id) {
                    $this->brand_id = $parent->brand_id;
                    $this->has_parent_brand = true;
                } else {
                    $this->has_parent_brand = false;
                }
                return;
            }
        }
        $this->has_parent_brand = false;
    }

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        $this->has_parent_brand = false;
        if ($id) {
            $cat = Category::findOrFail($id);
            $this->category_id = $cat->id;
            $this->name = $cat->name;
            $this->parent_id = $cat->parent_id;
            $this->brand_id = $cat->brand_id;
            $this->code_offset = $cat->code_offset;
            $this->existing_icon = $cat->icon;
            $this->is_active = $cat->is_active;

            // دریافت آرایه‌ها از دیتابیس (اطمینان از ساختار آرایه‌ای)
            $this->target_attributes = is_array($cat->target_attributes) ? $cat->target_attributes : [];
            $this->variant_fields = is_array($cat->variant_fields) ? $cat->variant_fields : [];

            if ($cat->parent_id) {
                $parent = Category::find($cat->parent_id);
                if ($parent && $parent->brand_id) {
                    $this->has_parent_brand = true;
                }
            }
        } else {
            $this->reset(['category_id', 'name', 'parent_id', 'brand_id', 'is_active', 'target_attributes', 'variant_fields', 'icon', 'existing_icon']);

            // تولید خودکار کد آفست جدید
            $lastOffset = Category::max('code_offset') ?? 0;
            $this->code_offset = $lastOffset + 100000;
        }
        $this->isFormOpen = true;
    }

    // ==========================================
    // مدیریت ویژگی‌های عمومی (Target Attributes)
    // ==========================================
    public function addAttribute() {
        $this->target_attributes[] = '';
    }

    public function removeAttribute($index) {
        unset($this->target_attributes[$index]);
        $this->target_attributes = array_values($this->target_attributes);
    }

    // ==========================================
    // مدیریت محورهای تنوع (Variant Axes)
    // ==========================================
    public function addVariantField() {
        $this->variant_fields[] = '';
    }

    public function removeVariantField($index) {
        unset($this->variant_fields[$index]);
        $this->variant_fields = array_values($this->variant_fields);
    }

    public function closeForm() {
        $this->isFormOpen = false;
        $this->reset(['icon']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code_offset' => 'required|integer|unique:market_categories,code_offset,' . $this->category_id,
            'brand_id' => 'nullable|integer|exists:market_brands,id',
            // بررسی مقادیر آرایه‌ها
            'target_attributes.*' => 'nullable|string|max:50',
            // 💡 NEW: حالا این فیلدها آیدی از دیتابیس هستند نه متن
            'variant_fields.*' => 'nullable|integer',
            'icon' => 'nullable|image|max:2048',
        ]);

        // پاکسازی اینپوت‌های خالی از آرایه‌ها تا دیتابیس کثیف نشود
        $cleanAttributes = array_values(array_filter($this->target_attributes));
        $cleanVariantFields = array_values(array_filter($this->variant_fields));

        $category = Category::updateOrCreate(
            ['id' => $this->category_id],
            [
                'name' => $this->name,
                'slug' => $this->category_id ? Category::find($this->category_id)->slug : Str::slug($this->name) . '-' . rand(10,99),
                'parent_id' => $this->parent_id ?: null,
                'brand_id' => $this->brand_id ?: null,
                'code_offset' => $this->code_offset,
                'target_attributes' => $cleanAttributes,
                'variant_fields' => $cleanVariantFields,
                'is_active' => $this->is_active,
            ]
        );

        if ($this->icon) {
            if ($category->icon) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->icon);
            }
            $path = $this->icon->store('categories', 'public');
            $category->update(['icon' => $path]);
        }

        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $categoryIds = $this->getDescendantCategoryIds($id);
        
        // Count products linked to this category or its subcategories
        $this->deleteProductCount = \Modules\Market\Entities\MasterProduct::whereIn('category_id', $categoryIds)->count();
        $this->deleteSubCategoryCount = count($categoryIds) - 1;

        if ($this->deleteProductCount > 0 || $this->deleteSubCategoryCount > 0) {
            // Setup deletion confirmation state
            $this->deleteTargetId = $id;
            $this->deleteTargetName = $category->name;
            $this->deleteActionType = 'move';
            $this->deleteMoveToCategoryId = '';
            $this->deleteConfirmName = '';
            $this->confirmingDeletion = true;

            // Load move to options (all categories excluding descendants)
            $allCats = Category::orderBy('code_offset')->get();
            $this->deleteMoveOptions = $this->buildMoveOptions($allCats, $categoryIds);
        } else {
            // Delete directly since it is empty
            $category->delete();
            $this->dispatch('notify', type: 'success', text: 'دسته‌بندی با موفقیت حذف شد.');
        }
    }

    public function confirmDelete()
    {
        if (!$this->deleteTargetId) return;

        $category = Category::findOrFail($this->deleteTargetId);
        $categoryIds = $this->getDescendantCategoryIds($this->deleteTargetId);

        try {
            DB::beginTransaction();

            if ($this->deleteActionType === 'move') {
                $this->validate([
                    'deleteMoveToCategoryId' => 'required|exists:market_categories,id|not_in:' . implode(',', $categoryIds),
                ], [
                    'deleteMoveToCategoryId.required' => 'انتخاب دسته‌بندی مقصد الزامی است.',
                    'deleteMoveToCategoryId.exists' => 'دسته‌بندی مقصد نامعتبر است.',
                    'deleteMoveToCategoryId.not_in' => 'دسته‌بندی مقصد نمی‌تواند از زیرمجموعه‌های دسته در حال حذف باشد.',
                ]);

                // 1. Move all products in these categories to the new category
                \Modules\Market\Entities\MasterProduct::whereIn('category_id', $categoryIds)
                    ->update(['category_id' => $this->deleteMoveToCategoryId]);

                // 2. Delete all subcategories and the category itself
                Category::whereIn('id', $categoryIds)->delete();

                $this->dispatch('notify', type: 'success', text: 'محصولات با موفقیت انتقال یافته و دسته‌بندی حذف شد.');
            } else if ($this->deleteActionType === 'delete_all') {
                // Must confirm by typing the exact name
                if (trim($this->deleteConfirmName) !== trim($category->name)) {
                    $this->addError('deleteConfirmName', 'نام وارد شده با نام دسته‌بندی مطابقت ندارد.');
                    DB::rollBack();
                    return;
                }

                // 1. Delete all products in these categories first
                $products = \Modules\Market\Entities\MasterProduct::whereIn('category_id', $categoryIds)->get();
                foreach ($products as $prod) {
                    $prod->delete();
                }

                // 2. Delete all subcategories and the category itself
                Category::whereIn('id', $categoryIds)->delete();

                $this->dispatch('notify', type: 'success', text: 'دسته‌بندی و تمامی محصولات مرتبط با آن با موفقیت حذف شدند.');
            }

            DB::commit();
            $this->closeDeleteModal();
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', text: 'خطا در عملیات حذف: ' . $e->getMessage());
        }
    }

    public function closeDeleteModal()
    {
        $this->confirmingDeletion = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
        $this->deleteProductCount = 0;
        $this->deleteSubCategoryCount = 0;
        $this->deleteActionType = 'move';
        $this->deleteMoveToCategoryId = '';
        $this->deleteConfirmName = '';
        $this->deleteMoveOptions = [];
    }

    protected function getDescendantCategoryIds($categoryId)
    {
        $ids = [$categoryId];
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getDescendantCategoryIds($childId));
        }
        return $ids;
    }

    private function buildMoveOptions($categories, $excludedIds, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);
        
        foreach ($filtered as $cat) {
            if (!in_array($cat->id, $excludedIds)) {
                $options[] = [
                    'id' => $cat->id,
                    'name' => str_repeat('— ', $depth) . $cat->name,
                ];
                $options = array_merge($options, $this->buildMoveOptions($categories, $excludedIds, $cat->id, $depth + 1));
            }
        }
        
        return $options;
    }

    public function render()
    {
        // دریافت درختی دسته‌ها (تا 3 سطح) برای نمایش در لیست پایین صفحه
        $categoriesTree = Category::whereNull('parent_id')
            ->with(['brand', 'children' => function($q) {
                $q->with(['brand', 'children' => function($q2) {
                    $q2->with('brand');
                }]);
            }])
            ->orderBy('code_offset')
            ->get();

        // دریافت تمام دسته‌ها به صورت فلت بر اساس برند انتخاب شده برای لیست کشویی "دسته والد"
        $parentCategoriesQuery = Category::orderBy('code_offset');
        if ($this->brand_id) {
            $parentCategoriesQuery->where('brand_id', $this->brand_id);
        } else {
            $parentCategoriesQuery->whereNull('brand_id');
        }
        $allCategories = $parentCategoriesQuery->get();

        // مقداردهی گزینه‌های والد با استفاده از ساختار درختی بازگشتی بدون محدودیت سطح
        $this->parentOptions = array_merge(
            [['value' => '', 'label' => '-- دسته اصلی (بدون والد) --', 'depth' => 0, 'isSub' => false]],
            $this->buildParentOptions($allCategories)
        );

        // 💡 NEW: دریافت تمام ویژگی‌های سراسری سیستم برای نمایش در سلکت‌باکس
        $globalAttributes = MarketAttribute::all();

        // دریافت تمام برندهای فعال برای انتساب به دسته‌ها
        $brands = \Modules\Market\Entities\Brand::where('is_active', true)->get();

        return view('market::livewire.admin.category-manager', [
            'categoriesTree' => $categoriesTree,
            'parentCategories' => $allCategories,
            'globalAttributes' => $globalAttributes,
            'brands' => $brands,
        ]);
    }

    private function buildParentOptions($categories, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);
        
        foreach ($filtered as $cat) {
            // برای جلوگیری از انتخاب خود دسته و تمامی فرزندان آن به عنوان والد خود
            if ($cat->id !== $this->category_id) {
                $options[] = [
                    'value' => (string)$cat->id,
                    'label' => $cat->name,
                    'depth' => $depth,
                    'isSub' => $depth > 0
                ];
                $options = array_merge($options, $this->buildParentOptions($categories, $cat->id, $depth + 1));
            }
        }
        
        return $options;
    }
}
