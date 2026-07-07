<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ContentForge\App\Models\ContentComment;
use Modules\ContentForge\App\Enums\CommentStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public string $statusFilter = 'all'; // all, pending, approved, spam
    public string $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $this->authorize('content.comments.approve');
        $comment = ContentComment::findOrFail($id);
        $comment->update(['status' => CommentStatus::Approved]);
        
        // Update post comment count
        $comment->post->increment('comment_count');
        
        session()->flash('success', 'دیدگاه با موفقیت تایید شد.');
    }

    public function reject(int $id): void
    {
        $this->authorize('content.comments.manage');
        $comment = ContentComment::findOrFail($id);
        
        if ($comment->status === CommentStatus::Approved) {
            $comment->post->decrement('comment_count');
        }

        $comment->update(['status' => CommentStatus::Pending]);
        session()->flash('success', 'وضعیت دیدگاه به در انتظار تغییر یافت.');
    }

    public function markSpam(int $id): void
    {
        $this->authorize('content.comments.manage');
        $comment = ContentComment::findOrFail($id);

        if ($comment->status === CommentStatus::Approved) {
            $comment->post->decrement('comment_count');
        }

        $comment->update(['status' => CommentStatus::Spam]);
        session()->flash('success', 'دیدگاه به عنوان هرزنامه علامت‌گذاری شد.');
    }

    public function delete(int $id): void
    {
        $this->authorize('content.comments.manage');
        $comment = ContentComment::findOrFail($id);

        if ($comment->status === CommentStatus::Approved) {
            $comment->post->decrement('comment_count');
        }

        $comment->delete();
        session()->flash('success', 'دیدگاه حذف شد.');
    }

    public function render()
    {
        $query = ContentComment::with(['post', 'user'])
            ->latest();

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('body', 'like', '%' . $this->search . '%')
                  ->orWhere('author_name', 'like', '%' . $this->search . '%')
                  ->orWhere('author_email', 'like', '%' . $this->search . '%');
            });
        }

        $comments = $query->paginate(15);

        return view('contentforge::livewire.admin.comment-manager', compact('comments'))
            ->layout('layouts.user');
    }
}
