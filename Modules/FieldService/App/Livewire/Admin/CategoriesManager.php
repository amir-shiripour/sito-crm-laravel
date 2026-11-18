<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\Section;
use Modules\FieldService\App\Models\ServiceGroup;

class CategoriesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $sectionFilter = 'all';
    public string $groupFilter = 'all';

    // Bulk group assignment
    public array $selectedIds = [];
    public ?string $bulkGroupId = '';

    // Modal state: Create & Edit
    public bool $showModal = false;
    public ?int $editingCategoryId = null;

    public ?int $selectedSectionId = null;
    public ?int $groupId = null;
    public string $name = '';
    public string $slug = '';
    public string $icon = '🛠️';
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

    public function updatingSectionFilter()
    {
        $this->groupFilter = 'all';
        $this->resetPage();
    }

    public function updatingGroupFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectedSectionId()
    {
        $this->groupId = null;
    }

    public function assignSelectedToGroup(): void
    {
        $ids = array_values(array_filter(array_map('intval', $this->selectedIds)));
        if (empty($ids)) {
            return;
        }

        if ($this->bulkGroupId === '' || $this->bulkGroupId === null) {
            Category::whereIn('id', $ids)->update(['section_id' => null, 'group_id' => null]);
            session()->flash('message', count($ids) . ' خدمت به حالت «بدون بخش و دسته» منتقل شدند.');
        } else {
            $group = ServiceGroup::with('section')->findOrFail((int) $this->bulkGroupId);
            Category::whereIn('id', $ids)->update([
                'section_id' => $group->section_id,
                'group_id'   => $group->id,
            ]);
            $sectionName = $group->section?->name ? $group->section->name . ' › ' : '';
            session()->flash('message', count($ids) . " خدمت به دسته «{$sectionName}{$group->name}» منتقل شدند.");
        }

        $this->selectedIds = [];
        $this->bulkGroupId = '';
    }

    public function openCreateModal()
    {
        $this->reset([
            'editingCategoryId',
            'selectedSectionId',
            'groupId',
            'name',
            'slug',
            'icon',
            'baseLaborFeeToman',
            'description',
        ]);
        if ($this->sectionFilter !== 'all') {
            $this->selectedSectionId = (int) $this->sectionFilter;
        }
        if ($this->groupFilter !== 'all' && $this->groupFilter !== 'none') {
            $grp = ServiceGroup::find((int) $this->groupFilter);
            if ($grp) {
                $this->selectedSectionId = $grp->section_id;
                $this->groupId = $grp->id;
            }
        }
        $this->icon = '🛠️';
        $this->baseLaborFeeToman = '300,000';
        $this->warrantyDays = 90;
        $this->sortOrder = (Category::max('sort_order') ?? 0) + 1;
        $this->isActive = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $cat = Category::with('group')->findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->groupId = $cat->group_id;
        $this->selectedSectionId = $cat->section_id ?: $cat->group?->section_id;
        $this->name = $cat->name;
        $this->slug = $cat->slug;
        $this->icon = $cat->icon ?? '🛠️';
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
            $this->slug = $this->generateUniqueSlug((string) $value, $this->groupId, $this->editingCategoryId);
        }
    }

    public function updatedGroupId()
    {
        if (!$this->editingCategoryId && !empty(trim($this->name))) {
            $this->slug = $this->generateUniqueSlug($this->name, $this->groupId, $this->editingCategoryId);
        }
    }

    protected function generateUniqueSlug(string $name, ?int $groupId = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-', null);
        if ($base === '') {
            $base = 'service';
        }

        $exists = fn (string $candidate): bool => Category::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if (!$exists($base)) {
            return $base;
        }

        if ($groupId) {
            $groupSlug = ServiceGroup::whereKey($groupId)->value('slug');
            if ($groupSlug) {
                $withGroup = Str::limit("{$groupSlug}-{$base}", 100, '');
                if (!$exists($withGroup)) {
                    return $withGroup;
                }
            }
        }

        $counter = 2;
        do {
            $suffix = "-{$counter}";
            $candidate = Str::limit($base, 100 - strlen($suffix), '') . $suffix;
            $counter++;
        } while ($exists($candidate));

        return $candidate;
    }

    public function saveCategory()
    {
        $this->selectedSectionId = $this->selectedSectionId ?: null;
        $this->groupId = $this->groupId ?: null;

        if (empty(trim($this->slug)) && !empty(trim($this->name))) {
            $this->slug = $this->generateUniqueSlug($this->name, $this->groupId, $this->editingCategoryId);
        } else {
            $this->slug = Str::slug($this->slug, '-', null);
        }

        $uniqueRule = 'unique:field_service_categories,slug';
        if ($this->editingCategoryId) {
            $uniqueRule .= ',' . $this->editingCategoryId;
        }

        $this->validate([
            'selectedSectionId' => 'nullable|integer|exists:field_service_sections,id',
            'groupId'           => 'nullable|integer|exists:field_service_service_groups,id',
            'name'              => 'required|string|max:100',
            'slug'              => 'required|string|max:100|' . $uniqueRule,
            'icon'              => 'nullable|string|max:20',
            'warrantyDays'      => 'required|integer|min:0|max:3650',
            'sortOrder'         => 'required|integer|min:0',
            'description'       => 'nullable|string|max:1000',
        ], [
            'selectedSectionId.exists' => 'بخش انتخاب‌شده معتبر نیست.',
            'groupId.exists'           => 'دسته انتخاب‌شده معتبر نیست.',
            'name.required'            => 'وارد کردن عنوان خدمت الزامی است.',
            'slug.required'            => 'وارد کردن نامک الزامی است.',
            'slug.unique'              => 'این نامک قبلاً ثبت شده است. لطفاً یک نامک یکتا وارد کنید.',
            'warrantyDays.min'         => 'مدت گارانتی نمی‌تواند منفی باشد.',
            'sortOrder.required'       => 'اولویت مرتب‌سازی الزامی است.',
        ]);

        // Keep section_id consistent with group_id if a group is selected
        $resolvedSectionId = $this->selectedSectionId;
        if ($this->groupId) {
            $resolvedSectionId = ServiceGroup::whereKey($this->groupId)->value('section_id') ?? $this->selectedSectionId;
        }

        // Convert cleaned Toman (including Persian/Arabic digits) to Rials for database storage
        $normalizedToman = strtr((string) $this->baseLaborFeeToman, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        $cleanToman = (float) preg_replace('/[^0-9]/', '', $normalizedToman);
        $baseLaborFeeRials = $cleanToman * 10;

        $data = [
            'section_id'     => $resolvedSectionId ?: null,
            'group_id'       => $this->groupId ?: null,
            'name'           => $this->name,
            'slug'           => Str::slug($this->slug, '-', null),
            'icon'           => $this->icon ?: '🛠️',
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
        $query = Category::with(['section', 'group.section'])->withCount(['technicians', 'orders']);

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

        if ($this->sectionFilter !== 'all') {
            $sectionId = (int) $this->sectionFilter;
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhereHas('group', fn ($gq) => $gq->where('section_id', $sectionId));
            });
        }

        if ($this->groupFilter === 'none') {
            $query->whereNull('group_id');
        } elseif ($this->groupFilter !== 'all') {
            $query->where('group_id', (int) $this->groupFilter);
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        $sections = Section::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $allGroups = ServiceGroup::with('section')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $filterGroups = $this->sectionFilter !== 'all'
            ? $allGroups->where('section_id', (int) $this->sectionFilter)
            : $allGroups;

        $modalGroups = $this->selectedSectionId
            ? $allGroups->where('section_id', (int) $this->selectedSectionId)
            : collect();

        return view('fieldservice::livewire.admin.categories-manager', [
            'categories'     => $categories,
            'sections'       => $sections,
            'allGroups'      => $allGroups,
            'filterGroups'   => $filterGroups,
            'modalGroups'    => $modalGroups,
            'totalCount'     => Category::count(),
            'activeCount'    => Category::where('is_active', true)->count(),
        ]);
    }
}
