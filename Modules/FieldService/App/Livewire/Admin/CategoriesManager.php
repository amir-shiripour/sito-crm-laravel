<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Models\Category;

class CategoriesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $applianceFilter = 'all';

    // Modal state: Create & Edit
    public bool $showModal = false;
    public ?int $editingCategoryId = null;

    public string $name = '';
    public string $slug = '';
    public string $icon = '🛠️';
    public string $applianceType = 'other';
    public ?string $baseLaborFeeToman = '';
    public int $warrantyDays = 90;
    public int $sortOrder = 0;
    public bool $isActive = true;
    public string $description = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;
    public ?int $deletingCategoryId = null;
    public ?string $deletingCategoryName = null;
    public int $deletingCategoryTechniciansCount = 0;
    public int $deletingCategoryOrdersCount = 0;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingApplianceFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset([
            'editingCategoryId',
            'name',
            'slug',
            'icon',
            'applianceType',
            'baseLaborFeeToman',
            'description',
        ]);
        $this->icon = '🛠️';
        $this->applianceType = 'other';
        $this->baseLaborFeeToman = '300,000';
        $this->warrantyDays = 90;
        $this->sortOrder = (Category::max('sort_order') ?? 0) + 1;
        $this->isActive = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $cat = Category::findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->name = $cat->name;
        $this->slug = $cat->slug;
        $this->icon = $cat->icon ?? '🛠️';
        $this->applianceType = $cat->appliance_type instanceof ApplianceType ? $cat->appliance_type->value : (string) $cat->appliance_type;
        $this->baseLaborFeeToman = number_format($cat->base_labor_fee / 10);
        $this->warrantyDays = $cat->warranty_days;
        $this->sortOrder = $cat->sort_order;
        $this->isActive = (bool) $cat->is_active;
        $this->description = $cat->description ?? '';

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function updatedName($value)
    {
        if (empty($this->slug) || !$this->editingCategoryId) {
            $this->slug = Str::slug($value, '-', null);
        }
    }

    public function saveCategory()
    {
        $uniqueRule = 'unique:field_service_categories,slug';
        if ($this->editingCategoryId) {
            $uniqueRule .= ',' . $this->editingCategoryId;
        }

        $this->validate([
            'name'          => 'required|string|max:100',
            'slug'          => 'required|string|max:100|' . $uniqueRule,
            'icon'          => 'nullable|string|max:20',
            'applianceType' => 'required|string',
            'warrantyDays'  => 'required|integer|min:0|max:3650',
            'sortOrder'     => 'required|integer|min:0',
            'description'   => 'nullable|string|max:1000',
        ], [
            'name.required'      => 'وارد کردن عنوان خدمت الزامی است.',
            'slug.required'      => 'وارد کردن نامک الزامی است.',
            'slug.unique'        => 'این نامک قبلاً ثبت شده است. لطفاً یک نامک یکتا وارد کنید.',
            'warrantyDays.min'   => 'مدت گارانتی نمی‌تواند منفی باشد.',
            'sortOrder.required' => 'اولویت مرتب‌سازی الزامی است.',
        ]);

        // Convert cleaned Toman to Rials for database storage
        $cleanToman = (float) preg_replace('/[^0-9]/', '', (string) $this->baseLaborFeeToman);
        $baseLaborFeeRials = $cleanToman * 10;

        $data = [
            'name'           => $this->name,
            'slug'           => Str::slug($this->slug, '-', null),
            'icon'           => $this->icon ?: '🛠️',
            'appliance_type' => $this->applianceType,
            'base_labor_fee' => $baseLaborFeeRials,
            'warranty_days'  => $this->warrantyDays,
            'sort_order'     => $this->sortOrder,
            'is_active'      => $this->isActive,
            'description'    => $this->description,
        ];

        if ($this->editingCategoryId) {
            $cat = Category::findOrFail($this->editingCategoryId);
            $cat->update($data);
            session()->flash('message', "خدمت «{$cat->name}» با موفقیت ویرایش شد.");
        } else {
            $newCat = Category::create($data);
            session()->flash('message', "خدمت جدید «{$newCat->name}» با موفقیت افزوده شد.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id)
    {
        $cat = Category::findOrFail($id);
        $cat->is_active = !$cat->is_active;
        $cat->save();

        $statusText = $cat->is_active ? 'فعال' : 'غیرفعال';
        session()->flash('message', "وضعیت خدمت «{$cat->name}» به {$statusText} تغییر یافت.");
    }

    public function confirmDelete(int $id)
    {
        $cat = Category::withCount(['technicians', 'orders'])->findOrFail($id);
        $this->deletingCategoryId = $cat->id;
        $this->deletingCategoryName = $cat->name;
        $this->deletingCategoryTechniciansCount = $cat->technicians_count;
        $this->deletingCategoryOrdersCount = $cat->orders_count;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingCategoryId = null;
        $this->deletingCategoryName = null;
    }

    public function deleteCategory()
    {
        if (!$this->deletingCategoryId) {
            return;
        }

        $cat = Category::findOrFail($this->deletingCategoryId);

        // Check if there are existing orders attached to this category
        if ($cat->orders()->exists()) {
            session()->flash('error', "امکان حذف خدمت «{$cat->name}» وجود ندارد زیرا {$cat->orders()->count()} سفارش با این خدمت ثبت شده است. می‌توانید آن را غیرفعال فرمایید.");
            $this->showDeleteModal = false;
            return;
        }

        $catName = $cat->name;
        $cat->technicians()->detach();
        $cat->delete();

        session()->flash('message', "خدمت «{$catName}» با موفقیت حذف گردید.");
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $query = Category::withCount(['technicians', 'orders']);

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->applianceFilter !== 'all') {
            $query->where('appliance_type', $this->applianceFilter);
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        $applianceTypes = ApplianceType::cases();

        return view('fieldservice::livewire.admin.categories-manager', [
            'categories'     => $categories,
            'applianceTypes' => $applianceTypes,
            'totalCount'     => Category::count(),
            'activeCount'    => Category::where('is_active', true)->count(),
        ]);
    }
}
