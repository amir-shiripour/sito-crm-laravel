<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\Category;
use Illuminate\Support\Str;

class CategoryManager extends Component
{
    public $category_id, $name, $parent_id, $code_offset, $is_active = true;

    // آرایه‌های فرم‌ساز
    public $target_attributes = []; // فیلدهای عمومی (مثل وزن، ابعاد)
    public $variant_fields = [];    // محورهای تنوع (مثل رنگ، حافظه، گارانتی)

    public $isFormOpen = false;

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        if ($id) {
            $cat = Category::findOrFail($id);
            $this->category_id = $cat->id;
            $this->name = $cat->name;
            $this->parent_id = $cat->parent_id;
            $this->code_offset = $cat->code_offset;
            $this->is_active = $cat->is_active;

            // دریافت آرایه‌ها از دیتابیس (اطمینان از ساختار آرایه‌ای)
            $this->target_attributes = is_array($cat->target_attributes) ? $cat->target_attributes : [];
            $this->variant_fields = is_array($cat->variant_fields) ? $cat->variant_fields : [];
        } else {
            $this->reset(['category_id', 'name', 'parent_id', 'is_active', 'target_attributes', 'variant_fields']);

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
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code_offset' => 'required|integer|unique:market_categories,code_offset,' . $this->category_id,
            // بررسی مقادیر آرایه‌ها
            'target_attributes.*' => 'nullable|string|max:50',
            'variant_fields.*' => 'nullable|string|max:50',
        ]);

        // پاکسازی اینپوت‌های خالی از آرایه‌ها تا دیتابیس کثیف نشود
        $cleanAttributes = array_values(array_filter($this->target_attributes));
        $cleanVariantFields = array_values(array_filter($this->variant_fields));

        Category::updateOrCreate(
            ['id' => $this->category_id],
            [
                'name' => $this->name,
                'slug' => $this->category_id ? Category::find($this->category_id)->slug : Str::slug($this->name) . '-' . rand(10,99),
                'parent_id' => $this->parent_id ?: null,
                'code_offset' => $this->code_offset,
                'target_attributes' => $cleanAttributes,
                'variant_fields' => $cleanVariantFields,
                'is_active' => $this->is_active,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'دسته‌بندی حذف شد.');
    }

    public function render()
    {
        // دریافت درختی دسته‌ها (تا 3 سطح) برای نمایش در لیست پایین صفحه
        $categoriesTree = Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->with('children');
            }])
            ->orderBy('code_offset')
            ->get();

        // دریافت تمام دسته‌ها به صورت فلت برای لیست کشویی "دسته والد"
        $allCategories = Category::orderBy('code_offset')->get();

        return view('market::livewire.admin.category-manager', [
            'categoriesTree' => $categoriesTree,
            'parentCategories' => $allCategories
        ]);
    }
}
