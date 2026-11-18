<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Models\Section;
use Modules\FieldService\App\Models\ServiceGroup;

class ServiceGroupsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $sectionFilter = 'all';

    // Modal state: Create & Edit
    public bool $showModal = false;
    public ?int $editingGroupId = null;

    public ?int $sectionId = null;
    public string $name = '';
    public string $slug = '';
    public string $icon = '📂';
    public int $sortOrder = 0;
    public bool $isActive = true;
    public string $description = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;
    public ?int $deletingGroupId = null;
    public ?string $deletingGroupName = null;
    public int $deletingGroupServicesCount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSectionFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingGroupId',
            'name',
            'slug',
            'description',
        ]);
        $this->sectionId = $this->sectionFilter !== 'all'
            ? (int) $this->sectionFilter
            : Section::orderBy('sort_order')->orderBy('id')->value('id');
        $this->icon = '📂';
        $this->sortOrder = (ServiceGroup::max('sort_order') ?? 0) + 1;
        $this->isActive = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $group = ServiceGroup::findOrFail($id);
        $this->editingGroupId = $group->id;
        $this->sectionId = $group->section_id;
        $this->name = $group->name;
        $this->slug = $group->slug;
        $this->icon = $group->icon ?? '📂';
        $this->sortOrder = $group->sort_order;
        $this->isActive = (bool) $group->is_active;
        $this->description = $group->description ?? '';

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedName(string $value): void
    {
        if (empty($this->slug) || ! $this->editingGroupId) {
            $this->slug = $this->generateUniqueSlug($value, $this->sectionId, $this->editingGroupId);
        }
    }

    public function updatedSectionId(): void
    {
        if (! $this->editingGroupId && ! empty(trim($this->name))) {
            $this->slug = $this->generateUniqueSlug($this->name, $this->sectionId, $this->editingGroupId);
        }
    }

    protected function generateUniqueSlug(string $name, ?int $sectionId = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-', null);
        if ($base === '') {
            $base = 'group';
        }

        $exists = fn (string $candidate): bool => ServiceGroup::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if (! $exists($base)) {
            return $base;
        }

        if ($sectionId) {
            $sectionSlug = Section::whereKey($sectionId)->value('slug');
            if ($sectionSlug) {
                $withSection = Str::limit("{$sectionSlug}-{$base}", 100, '');
                if (! $exists($withSection)) {
                    return $withSection;
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

    public function saveGroup(): void
    {
        if (empty(trim($this->slug)) && ! empty(trim($this->name))) {
            $this->slug = $this->generateUniqueSlug($this->name, $this->sectionId, $this->editingGroupId);
        } else {
            $this->slug = Str::slug($this->slug, '-', null);
        }

        $uniqueRule = 'unique:field_service_service_groups,slug';
        if ($this->editingGroupId) {
            $uniqueRule .= ',' . $this->editingGroupId;
        }

        $this->validate([
            'sectionId'   => 'required|integer|exists:field_service_sections,id',
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:100|' . $uniqueRule,
            'icon'        => 'nullable|string|max:20',
            'sortOrder'   => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ], [
            'sectionId.required' => 'انتخاب بخش مرتبط الزامی است.',
            'sectionId.exists'   => 'بخش انتخاب‌شده معتبر نیست.',
            'name.required'      => 'وارد کردن عنوان دسته الزامی است.',
            'slug.required'      => 'وارد کردن نامک الزامی است.',
            'slug.unique'        => 'این نامک قبلاً ثبت شده است. لطفاً نامک یکتایی وارد کنید.',
            'sortOrder.required' => 'اولویت مرتب‌سازی الزامی است.',
        ]);

        $data = [
            'section_id'  => $this->sectionId,
            'name'        => trim($this->name),
            'slug'        => Str::slug($this->slug, '-', null),
            'icon'        => $this->icon ?: '📂',
            'sort_order'  => $this->sortOrder,
            'is_active'   => $this->isActive,
            'description' => $this->description ?: null,
        ];

        if ($this->editingGroupId) {
            $group = ServiceGroup::findOrFail($this->editingGroupId);
            $group->update($data);
            session()->flash('message', "دسته «{$group->name}» با موفقیت ویرایش شد.");
        } else {
            $newGroup = ServiceGroup::create($data);
            session()->flash('message', "دسته جدید «{$newGroup->name}» با موفقیت افزوده شد.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $group = ServiceGroup::findOrFail($id);
        $group->is_active = ! $group->is_active;
        $group->save();

        $statusText = $group->is_active ? 'فعال' : 'غیرفعال';
        session()->flash('message', "وضعیت دسته «{$group->name}» به {$statusText} تغییر یافت.");
    }

    public function confirmDelete(int $id): void
    {
        $group = ServiceGroup::withCount('services')->findOrFail($id);
        $this->deletingGroupId = $group->id;
        $this->deletingGroupName = $group->name;
        $this->deletingGroupServicesCount = $group->services_count;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingGroupId = null;
        $this->deletingGroupName = null;
        $this->deletingGroupServicesCount = 0;
    }

    public function deleteGroup(): void
    {
        if (! $this->deletingGroupId) {
            return;
        }

        $group = ServiceGroup::withCount('services')->findOrFail($this->deletingGroupId);

        if ($group->services_count > 0) {
            session()->flash('error', "امکان حذف دسته «{$group->name}» وجود ندارد زیرا {$group->services_count} خدمت زیرمجموعه آن ثبت شده است. ابتدا خدمات را منتقل کنید یا این دسته را غیرفعال فرمایید.");
            $this->showDeleteModal = false;
            return;
        }

        $groupName = $group->name;
        $group->delete();

        session()->flash('message', "دسته «{$groupName}» با موفقیت حذف گردید.");
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $query = ServiceGroup::with('section')->withCount('services');

        if (! empty(trim($this->search))) {
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
            $query->where('section_id', (int) $this->sectionFilter);
        }

        $groups = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        $sections = Section::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return view('fieldservice::livewire.admin.service-groups-manager', [
            'groups'      => $groups,
            'sections'    => $sections,
            'totalCount'  => ServiceGroup::count(),
            'activeCount' => ServiceGroup::where('is_active', true)->count(),
        ]);
    }
}
