<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentRedirect;
use Modules\ContentForge\App\Models\ContentEntity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RedirectManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public string $fromUrl = '';
    public string $toUrl = '';
    public string $type = '301'; // 301 | 302
    public ?int $entityId = null;

    public ?int $editingRedirectId = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'fromUrl'  => 'required|string|max:255',
            'toUrl'    => 'required|string|max:255',
            'type'     => 'required|in:301,302',
            'entityId' => 'nullable|exists:content_entities,id',
        ];
    }

    public function mount(): void
    {
        $this->authorize('content.redirects.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'from_url'  => $this->fromUrl,
            'to_url'    => $this->toUrl,
            'type'      => $this->type,
            'entity_id' => $this->entityId,
        ];

        if ($this->editingRedirectId) {
            ContentRedirect::findOrFail($this->editingRedirectId)->update($data);
            session()->flash('success', 'ریدایرکت با موفقیت ویرایش شد.');
        } else {
            ContentRedirect::create($data);
            session()->flash('success', 'ریدایرکت جدید با موفقیت ایجاد شد.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $redirect = ContentRedirect::findOrFail($id);
        $this->editingRedirectId = $redirect->id;
        $this->fromUrl = $redirect->from_url;
        $this->toUrl = $redirect->to_url;
        $this->type = $redirect->type;
        $this->entityId = $redirect->entity_id;
    }

    public function delete(int $id): void
    {
        ContentRedirect::findOrFail($id)->delete();
        session()->flash('success', 'ریدایرکت حذف شد.');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingRedirectId', 'fromUrl', 'toUrl', 'type', 'entityId']);
    }

    public function render()
    {
        $entities = ContentEntity::where('is_active', true)->get();
        
        $query = ContentRedirect::latest();

        if (!empty($this->search)) {
            $query->where('from_url', 'like', '%' . $this->search . '%')
                  ->orWhere('to_url', 'like', '%' . $this->search . '%');
        }

        $redirects = $query->paginate(15);

        return view('contentforge::livewire.admin.redirect-manager', compact('entities', 'redirects'))
            ->layout('layouts.user');
    }
}
