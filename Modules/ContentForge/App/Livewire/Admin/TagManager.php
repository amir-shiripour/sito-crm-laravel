<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentTag;
use Modules\ContentForge\App\Models\ContentEntity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TagManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public int $entityId;
    public string $name = '';
    public string $slug = '';

    public ?int $editingTagId = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'entityId' => 'required|exists:content_entities,id',
            'name'     => 'required|string|max:100',
            'slug'     => 'nullable|string|max:100',
        ];
    }

    public function mount(): void
    {
        $this->authorize('content.tags.manage');
        $defaultEntity = ContentEntity::where('is_default', true)->first();
        $this->entityId = $defaultEntity ? (int)$defaultEntity->id : 0;
        if (!$this->entityId) {
            $firstEntity = ContentEntity::first();
            $this->entityId = $firstEntity ? (int)$firstEntity->id : 0;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'entity_id' => $this->entityId,
            'name'      => $this->name,
            'slug'      => $this->slug ?: \Illuminate\Support\Str::slug($this->name, '-'),
        ];

        if ($this->editingTagId) {
            ContentTag::findOrFail($this->editingTagId)->update($data);
            session()->flash('success', 'برچسب با موفقیت ویرایش شد.');
        } else {
            ContentTag::create($data);
            session()->flash('success', 'برچسب جدید با موفقیت ایجاد شد.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $tag = ContentTag::findOrFail($id);
        $this->editingTagId = $tag->id;
        $this->entityId = $tag->entity_id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
    }

    public function delete(int $id): void
    {
        ContentTag::findOrFail($id)->delete();
        session()->flash('success', 'برچسب حذف شد.');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingTagId', 'name', 'slug']);
    }

    public function render()
    {
        $entities = ContentEntity::where('is_active', true)->get();
        
        $query = ContentTag::where('entity_id', $this->entityId)
            ->latest();

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
        }

        $tags = $query->paginate(15);

        return view('contentforge::livewire.admin.tag-manager', compact('entities', 'tags'))
            ->layout('layouts.user');
    }
}
