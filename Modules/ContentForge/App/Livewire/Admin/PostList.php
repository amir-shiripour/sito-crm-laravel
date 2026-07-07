<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\App\Enums\PostType;
use Modules\ContentForge\App\Enums\PostStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostList extends Component
{
    use WithPagination, AuthorizesRequests;

    public string $type = 'post'; // post | page
    public string $statusFilter = 'all'; // all, draft, published, archived
    public ?int $entityFilter = null;
    public string $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'entityFilter' => ['except' => null],
        'search'       => ['except' => ''],
    ];

    public function mount(string $type = 'post'): void
    {
        $this->type = $type;
        $this->authorize('content.posts.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEntityFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $post = ContentPost::findOrFail($id);
        $this->authorize('content.posts.delete', $post);

        $post->delete();
        session()->flash('success', 'آیتم مورد نظر با موفقیت حذف شد.');
    }

    public function render()
    {
        $query = ContentPost::with(['author', 'category', 'entity'])
            ->where('type', $this->type)
            ->latest();

        // If not super-admin/admin, restrict to own posts if permission specifies it
        if (!auth()->user()->hasRole(['super-admin', 'admin']) && auth()->user()->can('content.posts.edit.own')) {
            $query->where('author_id', auth()->id());
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->entityFilter) {
            $query->where('entity_id', $this->entityFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        $posts = $query->paginate(15);
        $entities = ContentEntity::where('is_active', true)->get();

        return view('contentforge::livewire.admin.post-list', compact('posts', 'entities'))
            ->layout('layouts.user');
    }
}
