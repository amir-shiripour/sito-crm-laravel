<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Web;

use Livewire\Component;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentComment;
use Modules\ContentForge\App\Enums\CommentStatus;

class CommentList extends Component
{
    public ContentPost $post;

    protected $listeners = ['commentAdded' => '$refresh'];

    public function render()
    {
        // Load parent comments that are approved
        $comments = ContentComment::where('post_id', $this->post->id)
            ->whereNull('parent_id')
            ->where('status', CommentStatus::Approved)
            ->with(['replies' => function($q) {
                $q->where('status', CommentStatus::Approved);
            }, 'user'])
            ->latest()
            ->get();

        return view('contentforge::livewire.web.comment-list', compact('comments'));
    }
}
