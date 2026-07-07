<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Web;

use Livewire\Component;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentComment;
use Modules\ContentForge\App\Enums\CommentStatus;
use Modules\ContentForge\Entities\ContentSetting;

class CommentForm extends Component
{
    public ContentPost $post;
    public ?int $parentId = null;

    public string $body = '';
    public string $authorName = '';
    public string $authorEmail = '';

    protected function rules(): array
    {
        $rules = [
            'body' => 'required|string|min:5|max:1000',
        ];

        if (!auth()->check()) {
            $rules['authorName'] = 'required|string|max:100';
            $rules['authorEmail'] = 'required|email|max:100';
        }

        return $rules;
    }

    protected $validationAttributes = [
        'body'        => 'متن دیدگاه',
        'authorName'  => 'نام شما',
        'authorEmail' => 'ایمیل شما',
    ];

    public function submit(): void
    {
        $this->validate();

        $autoApprove = ContentSetting::getValue('general.auto_approve_comments', 'false') === 'true';

        ContentComment::create([
            'post_id'      => $this->post->id,
            'parent_id'    => $this->parentId,
            'user_id'      => auth()->id(),
            'author_name'  => auth()->check() ? auth()->user()->name : $this->authorName,
            'author_email' => auth()->check() ? auth()->user()->email : $this->authorEmail,
            'body'         => $this->body,
            'status'       => $autoApprove ? CommentStatus::Approved : CommentStatus::Pending,
            'ip_address'   => request()->ip(),
        ]);

        if ($autoApprove) {
            $this->post->increment('comment_count');
            $this->dispatch('commentAdded');
            session()->flash('success', 'دیدگاه شما با موفقیت ثبت و منتشر شد.');
        } else {
            session()->flash('success', 'دیدگاه شما ثبت شد و پس از تایید مدیریت نمایش داده خواهد شد.');
        }

        $this->reset(['body', 'authorName', 'authorEmail']);
    }

    public function render()
    {
        return view('contentforge::livewire.web.comment-form');
    }
}
