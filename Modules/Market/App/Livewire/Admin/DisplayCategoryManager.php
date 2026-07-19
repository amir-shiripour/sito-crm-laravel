<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\DisplayCategory;
use Modules\Market\Entities\MasterProduct;
use Illuminate\Support\Str;

class DisplayCategoryManager extends Component
{
    use \Livewire\WithFileUploads;

    public $category_id, $name, $parent_id, $is_active = true;
    public $icon, $existing_icon;
    public $parentOptions = [];
    public $isFormOpen = false;

    // Product association states
    public $selectedProductIds = [];
    public $bulkBrandId = null;
    public $bulkCategoryId = null;
    public $searchQuery = '';

    public $brandOptions = [];
    public $categoryOptions = [];

    public function updatedBulkBrandId()
    {
        $this->bulkCategoryId = null;
    }

    public function mount()
    {
        if (! (bool) \Modules\Market\Entities\MarketSetting::getValue('system.separate_category_enabled', false)) {
            abort(403, 'سیستم دسته‌بندی مجزا فعال نیست.');
        }
    }

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        if ($id) {
            $cat = DisplayCategory::findOrFail($id);
            $this->category_id = $cat->id;
            $this->name = $cat->name;
            $this->parent_id = $cat->parent_id;
            $this->existing_icon = $cat->icon;
            $this->is_active = $cat->is_active;
            
            // Load associated products
            $this->selectedProductIds = $cat->masterProducts()->pluck('market_master_products.id')->toArray();
        } else {
            $this->reset(['category_id', 'name', 'parent_id', 'is_active', 'icon', 'existing_icon', 'selectedProductIds', 'bulkBrandId', 'bulkCategoryId', 'searchQuery']);
        }
        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->reset(['icon']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:market_display_categories,id',
            'icon' => 'nullable|image|max:2048',
        ]);

        $category = DisplayCategory::updateOrCreate(
            ['id' => $this->category_id],
            [
                'name' => $this->name,
                'slug' => $this->category_id ? DisplayCategory::find($this->category_id)->slug : Str::slug($this->name) . '-' . rand(10, 99),
                'parent_id' => $this->parent_id ?: null,
                'is_active' => $this->is_active,
            ]
        );

        if ($this->icon) {
            if ($category->icon) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->icon);
            }
            $path = $this->icon->store('display_categories', 'public');
            $category->update(['icon' => $path]);
        }

        // Sync products association
        $category->masterProducts()->sync($this->selectedProductIds);

        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی نمایشی با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function delete($id)
    {
        DisplayCategory::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی مجزا حذف شد.');
    }

    public function render()
    {
        // دریافت درختی دسته‌ها (تا 3 سطح) برای نمایش در لیست پایین صفحه
        $categoriesTree = DisplayCategory::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->with(['children']);
            }])
            ->orderBy('name')
            ->get();

        // دریافت تمام دسته‌ها به صورت فلت برای لیست کشویی "دسته والد"
        $allCategories = DisplayCategory::orderBy('name')->get();

        // مقداردهی گزینه‌های والد با استفاده از ساختار درختی بازگشتی
        $this->parentOptions = array_merge(
            [['value' => '', 'label' => '-- دسته اصلی (بدون والد) --', 'depth' => 0, 'isSub' => false]],
            $this->buildParentOptions($allCategories)
        );

        // Fetch brands and main categories for bulk selection
        $allBrands = \Modules\Market\Entities\Brand::orderBy('name')->get();
        $this->brandOptions = array_merge(
            [['value' => '', 'label' => 'همه برندها']],
            $allBrands->map(fn($b) => ['value' => (string)$b->id, 'label' => $b->name])->toArray()
        );

        $allCategoriesList = \Modules\Market\Entities\Category::orderBy('name')->get();
        if ($this->bulkBrandId) {
            $matchingCategoryIds = MasterProduct::where('brand_id', $this->bulkBrandId)
                ->pluck('category_id')
                ->filter()
                ->unique()
                ->toArray();
            
            $validIds = [];
            foreach ($matchingCategoryIds as $cid) {
                $validIds[$cid] = true;
                $curr = $allCategoriesList->firstWhere('id', $cid);
                while ($curr && $curr->parent_id) {
                    $validIds[$curr->parent_id] = true;
                    $curr = $allCategoriesList->firstWhere('id', $curr->parent_id);
                }
            }
            $allMainCategories = $allCategoriesList->whereIn('id', array_keys($validIds));
        } else {
            $allMainCategories = $allCategoriesList;
        }

        $this->categoryOptions = array_merge(
            [['value' => '', 'label' => 'همه دسته‌بندی‌ها', 'depth' => 0, 'isSub' => false]],
            $this->buildMainCategoryOptions($allMainCategories)
        );

        // Individual search results
        $searchResults = [];
        if (strlen(trim($this->searchQuery)) >= 2) {
            $searchResults = MasterProduct::query()
                ->where(function($q) {
                    $q->where('title', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('crm_code', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('barcode', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('gtin', 'like', '%' . $this->searchQuery . '%');
                })
                ->limit(8)
                ->get();
        }

        // Fetch currently selected products
        $selectedProducts = MasterProduct::whereIn('id', $this->selectedProductIds)
            ->with(['brand', 'category'])
            ->get();

        return view('market::livewire.admin.display-category-manager', [
            'categoriesTree' => $categoriesTree,
            'parentCategories' => $allCategories,
            'allBrands' => $allBrands,
            'allMainCategories' => $allMainCategories,
            'searchResults' => $searchResults,
            'selectedProducts' => $selectedProducts,
        ]);
    }

    public function addProduct($productId)
    {
        if (!in_array($productId, $this->selectedProductIds)) {
            $this->selectedProductIds[] = $productId;
        }
        $this->searchQuery = '';
    }

    public function removeProduct($productId)
    {
        $this->selectedProductIds = array_values(array_diff($this->selectedProductIds, [$productId]));
    }

    public function addBulkProducts()
    {
        if (empty($this->bulkBrandId) && empty($this->bulkCategoryId)) {
            $this->addError('bulk_selection', 'لطفاً حداقل یک برند یا دسته‌بندی انتخاب کنید.');
            return;
        }

        $query = MasterProduct::query();
        if ($this->bulkBrandId) {
            $query->where('brand_id', $this->bulkBrandId);
        }
        if ($this->bulkCategoryId) {
            $categoryIds = $this->getCategoryChildrenIds($this->bulkCategoryId);
            $query->whereIn('category_id', $categoryIds);
        }

        $ids = $query->pluck('id')->toArray();
        $this->selectedProductIds = array_unique(array_merge($this->selectedProductIds, $ids));

        $this->bulkBrandId = null;
        $this->bulkCategoryId = null;
        $this->dispatch('notify', type: 'success', text: 'محصولات گروهی با موفقیت به لیست اضافه شدند.');
    }

    private function getCategoryChildrenIds($categoryId)
    {
        $ids = [(int)$categoryId];
        $children = \Modules\Market\Entities\Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getCategoryChildrenIds($childId));
        }
        return array_unique($ids);
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

    private function buildMainCategoryOptions($categories, $parentId = null, $depth = 0)
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
            $options = array_merge($options, $this->buildMainCategoryOptions($categories, $cat->id, $depth + 1));
        }

        return $options;
    }
}
