<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Models\Section;

class SectionsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // Modal state: Create & Edit
    public bool $showModal = false;
    public ?int $editingSectionId = null;

    public string $name = '';
    public string $slug = '';
    public string $icon = '🧩';
    public int $sortOrder = 0;
    public bool $isActive = true;
    public string $description = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;
    public ?int $deletingSectionId = null;
    public ?string $deletingSectionName = null;
    public int $deletingSectionGroupsCount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingSectionId',
            'name',
            'slug',
            'description',
        ]);
        $this->icon = '🧩';
        $this->sortOrder = (Section::max('sort_order') ?? 0) + 1;
        $this->isActive = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $section = Section::findOrFail($id);
        $this->editingSectionId = $section->id;
        $this->name = $section->name;
        $this->slug = $section->slug;
        $this->icon = $section->icon ?? '🧩';
        $this->sortOrder = $section->sort_order;
        $this->isActive = (bool) $section->is_active;
        $this->description = $section->description ?? '';

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedName(string $value): void
    {
        if (empty($this->slug) || ! $this->editingSectionId) {
            $this->slug = Str::slug($value, '-', null);
        }
    }

    public function saveSection(): void
    {
        $uniqueRule = 'unique:field_service_sections,slug';
        if ($this->editingSectionId) {
            $uniqueRule .= ',' . $this->editingSectionId;
        }

        $this->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:100|' . $uniqueRule,
            'icon'        => 'nullable|string|max:20',
            'sortOrder'   => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required'      => 'وارد کردن عنوان بخش الزامی است.',
            'slug.required'      => 'وارد کردن نامک الزامی است.',
            'slug.unique'        => 'این نامک قبلاً ثبت شده است. لطفاً نامک یکتایی وارد کنید.',
            'sortOrder.required' => 'اولویت مرتب‌سازی الزامی است.',
        ]);

        $data = [
            'name'        => trim($this->name),
            'slug'        => Str::slug($this->slug, '-', null),
            'icon'        => $this->icon ?: '🧩',
            'sort_order'  => $this->sortOrder,
            'is_active'   => $this->isActive,
            'description' => $this->description ?: null,
        ];

        if ($this->editingSectionId) {
            $section = Section::findOrFail($this->editingSectionId);
            $section->update($data);
            session()->flash('message', "بخش «{$section->name}» با موفقیت ویرایش شد.");
        } else {
            $newSection = Section::create($data);
            session()->flash('message', "بخش جدید «{$newSection->name}» با موفقیت افزوده شد.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $section = Section::findOrFail($id);
        $section->is_active = ! $section->is_active;
        $section->save();

        $statusText = $section->is_active ? 'فعال' : 'غیرفعال';
        session()->flash('message', "وضعیت بخش «{$section->name}» به {$statusText} تغییر یافت.");
    }

    public function confirmDelete(int $id): void
    {
        $section = Section::withCount('groups')->findOrFail($id);
        $this->deletingSectionId = $section->id;
        $this->deletingSectionName = $section->name;
        $this->deletingSectionGroupsCount = $section->groups_count;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingSectionId = null;
        $this->deletingSectionName = null;
        $this->deletingSectionGroupsCount = 0;
    }

    public function deleteSection(): void
    {
        if (! $this->deletingSectionId) {
            return;
        }

        $section = Section::withCount('groups')->findOrFail($this->deletingSectionId);

        if ($section->groups_count > 0) {
            session()->flash('error', "امکان حذف بخش «{$section->name}» وجود ندارد زیرا {$section->groups_count} دسته زیرمجموعه آن ثبت شده است. می‌توانید آن را غیرفعال فرمایید.");
            $this->showDeleteModal = false;
            return;
        }

        $sectionName = $section->name;
        $section->delete();

        session()->flash('message', "بخش «{$sectionName}» با موفقیت حذف گردید.");
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $query = Section::withCount('groups');

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

        $sections = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        return view('fieldservice::livewire.admin.sections-manager', [
            'sections'    => $sections,
            'totalCount'  => Section::count(),
            'activeCount' => Section::where('is_active', true)->count(),
        ]);
    }
}
