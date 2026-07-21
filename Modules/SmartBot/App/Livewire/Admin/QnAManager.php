<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\SmartBot\App\Models\BotQuestion;
use Modules\SmartBot\App\Models\BotAnswer;

class QnAManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = 'all';

    // Form fields
    public ?int $editingQuestionId = null;
    public string $question_text = '';
    public string $keywords = '';
    public string $category_field = 'general';
    public int $priority = 0;
    public bool $is_active = true;

    // Answer fields
    public string $answer_text = '';
    public string $answer_type = 'text'; // text, product_list
    public array $selected_product_ids = [];
    public bool $show_add_to_cart = true;

    public bool $isOpen = false;

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

    public function getProductsProperty(): array
    {
        if (class_exists('Modules\Market\Entities\MasterProduct')) {
            return \Modules\Market\Entities\MasterProduct::select('id', 'title')
                ->where('status', 'active')
                ->get()
                ->toArray();
        }
        return [];
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $question = BotQuestion::with('answers')->findOrFail($id);
            $this->editingQuestionId = $question->id;
            $this->question_text = $question->question_text;
            $this->keywords = implode(', ', $question->keywords ?? []);
            $this->category_field = $question->category;
            $this->priority = $question->priority;
            $this->is_active = (bool) $question->is_active;

            $defaultAnswer = $question->defaultAnswer();
            if ($defaultAnswer) {
                $this->answer_text = $defaultAnswer->answer_text;
                $this->answer_type = $defaultAnswer->answer_type;
                $this->selected_product_ids = $defaultAnswer->entity_ids ?? [];
                $this->show_add_to_cart = (bool) $defaultAnswer->show_add_to_cart;
            }
        }

        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingQuestionId = null;
        $this->question_text = '';
        $this->keywords = '';
        $this->category_field = 'general';
        $this->priority = 0;
        $this->is_active = true;
        $this->answer_text = '';
        $this->answer_type = 'text';
        $this->selected_product_ids = [];
        $this->show_add_to_cart = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate([
            'question_text' => 'required|string|min:3',
            'keywords' => 'nullable|string',
            'category_field' => 'required|string',
            'priority' => 'required|integer|min:0',
            'answer_text' => 'required|string',
            'answer_type' => 'required|string|in:text,product_list',
            'selected_product_ids' => 'required_if:answer_type,product_list|array',
        ]);

        $keywordsArray = array_filter(array_map('trim', explode(',', $this->keywords)));

        \DB::transaction(function () use ($keywordsArray) {
            $question = BotQuestion::updateOrCreate(
                ['id' => $this->editingQuestionId],
                [
                    'question_text' => $this->question_text,
                    'keywords' => $keywordsArray,
                    'category' => $this->category_field,
                    'priority' => $this->priority,
                    'is_active' => $this->is_active,
                    'created_by' => auth()->id(),
                ]
            );

            // Update default answer
            BotAnswer::updateOrCreate(
                ['question_id' => $question->id, 'is_default' => true],
                [
                    'answer_text' => $this->answer_text,
                    'answer_type' => $this->answer_type,
                    'entity_type' => $this->answer_type === 'product_list' ? 'market_product' : null,
                    'entity_ids' => $this->answer_type === 'product_list' ? $this->selected_product_ids : null,
                    'show_add_to_cart' => $this->show_add_to_cart,
                ]
            );
        });

        $this->dispatch('notify', type: 'success', text: 'سوال و جواب با موفقیت ذخیره شد.');
        $this->closeModal();
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
