<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\SmartBot\App\Models\BotQuestion;

class QnAManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->can('smartbot.manage')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        BotQuestion::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'سوال و جواب با موفقیت حذف شد.');
    }

    public function toggleStatus(int $id): void
    {
        $question = BotQuestion::findOrFail($id);
        $question->update(['is_active' => !$question->is_active]);
        $this->dispatch('notify', type: 'success', text: 'وضعیت سوال تغییر کرد.');
    }

    public function render()
    {
        $query = BotQuestion::query()->with('answers');

        if ($this->search) {
            $query->where('question_text', 'like', '%' . $this->search . '%')
                ->orWhere('keywords', 'like', '%' . $this->search . '%');
        }

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        $questions = $query->orderBy('priority', 'desc')->orderBy('id', 'desc')->paginate(10);
        $categories = BotQuestion::select('category')->distinct()->pluck('category');

        return view('smartbot::livewire.admin.qna-manager', [
            'questions' => $questions,
            'categories' => $categories,
        ])->layout('layouts.user', ['title' => 'مدیریت سوال و جواب دستیار هوشمند']);
    }
}
