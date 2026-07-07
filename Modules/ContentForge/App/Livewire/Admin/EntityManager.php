<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentEntity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EntityManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public string $name = '';
    public string $slug = '';
    public ?string $moduleSource = null;
    public ?int $entityReferenceId = null;
    public ?string $themeKey = null;
    public bool $isActive = true;

    public ?int $editingEntityId = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name'              => 'required|string|max:100',
            'slug'              => 'required|string|max:100|unique:content_entities,slug,' . ($this->editingEntityId ?: 'NULL'),
            'moduleSource'      => 'nullable|string|max:100',
            'entityReferenceId' => 'nullable|integer',
            'themeKey'          => 'nullable|string|max:50',
            'isActive'          => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->authorize('content.entities.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'                => $this->name,
            'slug'                => $this->slug,
            'module_source'       => $this->moduleSource,
            'entity_reference_id' => $this->entityReferenceId,
            'theme_key'           => $this->themeKey,
            'is_active'           => $this->isActive,
        ];

        if ($this->editingEntityId) {
            ContentEntity::findOrFail($this->editingEntityId)->update($data);
            session()->flash('success', 'موجودیت با موفقیت ویرایش شد.');
        } else {
            ContentEntity::create($data);
            session()->flash('success', 'موجودیت جدید با موفقیت ایجاد شد.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $entity = ContentEntity::findOrFail($id);
        $this->editingEntityId = $entity->id;
        $this->name = $entity->name;
        $this->slug = $entity->slug;
        $this->moduleSource = $entity->module_source;
        $this->entityReferenceId = $entity->entity_reference_id ? (int)$entity->entity_reference_id : null;
        $this->themeKey = $entity->theme_key;
        $this->isActive = $entity->is_active;
    }

    public function delete(int $id): void
    {
        $entity = ContentEntity::findOrFail($id);
        if ($entity->is_default) {
            session()->flash('error', 'موجودیت پیش‌فرض قابل حذف نیست.');
            return;
        }

        $entity->delete();
        session()->flash('success', 'موجودیت حذف شد.');
        $this->resetForm();
    }

    public function makeDefault(int $id): void
    {
        ContentEntity::query()->update(['is_default' => false]);
        ContentEntity::findOrFail($id)->update(['is_default' => true]);
        session()->flash('success', 'موجودیت پیش‌فرض سیستم تغییر کرد.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingEntityId', 'name', 'slug', 'moduleSource', 'entityReferenceId', 'themeKey', 'isActive']);
    }

    public function render()
    {
        $query = ContentEntity::latest();

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
        }

        $entities = $query->paginate(15);

        return view('contentforge::livewire.admin.entity-manager', compact('entities'))
            ->layout('layouts.user');
    }
}
