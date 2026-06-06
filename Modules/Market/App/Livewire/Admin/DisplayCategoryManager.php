<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\DisplayCategory;
use Illuminate\Support\Str;

class DisplayCategoryManager extends Component
{
    public $category_id, $name, $parent_id, $is_active = true;
    public $parentOptions = [];
    public $isFormOpen = false;

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
            $this->is_active = $cat->is_active;
        } else {
            $this->reset(['category_id', 'name', 'parent_id', 'is_active']);
        }
        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:market_display_categories,id',
        ]);

        DisplayCategory::updateOrCreate(
            ['id' => $this->category_id],
            [
                'name' => $this->name,
                'slug' => $this->category_id ? DisplayCategory::find($this->category_id)->slug : Str::slug($this->name) . '-' . rand(10, 99),
                'parent_id' => $this->parent_id ?: null,
                'is_active' => $this->is_active,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی مجزا با موفقیت ذخیره شد.');
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

        return view('market::livewire.admin.display-category-manager', [
            'categoriesTree' => $categoriesTree,
            'parentCategories' => $allCategories,
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
