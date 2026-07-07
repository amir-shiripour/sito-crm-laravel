<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentShortLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShortLinkManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public string $search = '';
    public ?int $editingShortLinkId = null;
    public string $customCode = '';

    protected function rules(): array
    {
        return [
            'customCode' => 'nullable|string|max:50|alpha_dash|unique:content_short_links,custom_code,' . ($this->editingShortLinkId ?: 'NULL') . '|unique:content_short_links,code',
        ];
    }

    public function mount(): void
    {
        $this->authorize('content.shortlinks.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $link = ContentShortLink::findOrFail($id);
        $this->editingShortLinkId = $link->id;
        $this->customCode = $link->custom_code ?? '';
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingShortLinkId) {
            $link = ContentShortLink::findOrFail($this->editingShortLinkId);
            $link->update([
                'custom_code' => !empty($this->customCode) ? $this->customCode : null,
            ]);
            session()->flash('success', 'کد اختصاصی لینک کوتاه با موفقیت ثبت شد.');
        }

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingShortLinkId', 'customCode']);
    }

    public function render()
    {
        $query = ContentShortLink::with('post.entity')
            ->latest();

        if (!empty($this->search)) {
            $query->whereHas('post', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhere('code', 'like', '%' . $this->search . '%')
              ->orWhere('custom_code', 'like', '%' . $this->search . '%');
        }

        $links = $query->paginate(15);

        return view('contentforge::livewire.admin.short-link-manager', compact('links'))
            ->layout('layouts.user');
    }
}
