<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\ProductQuestion;
use Livewire\Attributes\Url;

class QuestionManager extends Component
{
    use WithPagination;

    #[Url(except: 'pending')]
    public $filterStatus = 'pending';

    #[Url(except: '')]
    public $search = '';

    // Rejection Modal properties
    public $rejectingQuestionId = null;
    public $rejectionReason = '';

    // Admin replies array (keyed by question_id)
    public $replyTexts = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function approve($id)
    {
        $item = ProductQuestion::findOrFail($id);
        $item->update([
            'status' => 'approved',
            'rejection_reason' => null
        ]);
        
        $msg = $item->parent_id ? 'پاسخ با موفقیت تایید و منتشر شد.' : 'پرسش با موفقیت تایید و در سایت منتشر شد.';
        $this->dispatch('notify', type: 'success', text: $msg);
    }

    public function promptReject($id)
    {
        $this->rejectingQuestionId = $id;
        $this->rejectionReason = '';
    }

    public function cancelReject()
    {
        $this->rejectingQuestionId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5'
        ], [
            'rejectionReason.required' => 'لطفاً علت رد را بنویسید.',
            'rejectionReason.min' => 'علت رد باید حداقل ۵ کاراکتر باشد.'
        ]);

        $item = ProductQuestion::findOrFail($this->rejectingQuestionId);
        $item->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason
        ]);

        $msg = $item->parent_id ? 'پاسخ رد شد و دلیل آن ثبت گردید.' : 'پرسش رد شد و دلیل آن ثبت گردید.';
        $this->dispatch('notify', type: 'success', text: $msg);
        $this->cancelReject();
    }

    public function delete($id)
    {
        $item = ProductQuestion::findOrFail($id);
        $item->delete();
        
        $msg = $item->parent_id ? 'پاسخ با موفقیت حذف شد.' : 'پرسش و تمامی پاسخ‌های آن با موفقیت حذف شدند.';
        $this->dispatch('notify', type: 'success', text: $msg);
    }

    public function submitReply($questionId)
    {
        $this->validate([
            'replyTexts.' . $questionId => 'required|string|min:3|max:1000'
        ], [
            'replyTexts.' . $questionId . '.required' => 'لطفاً متن پاسخ خود را بنویسید.',
            'replyTexts.' . $questionId . '.min' => 'پاسخ باید حداقل ۳ کاراکتر باشد.',
            'replyTexts.' . $questionId . '.max' => 'پاسخ نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.'
        ]);

        $question = ProductQuestion::findOrFail($questionId);
        
        ProductQuestion::create([
            'parent_id' => $questionId,
            'master_product_id' => $question->master_product_id,
            'user_id' => auth()->id(),
            'text' => $this->replyTexts[$questionId],
            'status' => 'approved', // Admin replies are auto-approved
        ]);

        $this->replyTexts[$questionId] = '';
        $this->dispatch('notify', type: 'success', text: 'پاسخ شما با موفقیت ثبت و منتشر شد.');
    }

    public function render()
    {
        // Query only root questions (parent_id is null)
        $query = ProductQuestion::whereNull('parent_id')
            ->with(['masterProduct', 'client', 'replies.client', 'replies.vendor', 'replies.user'])
            ->latest();

        // Filter Questions:
        // - pending: Shows pending questions OR questions containing pending replies.
        if ($this->filterStatus === 'pending') {
            $query->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhereHas('replies', function ($sub) {
                        $sub->where('status', 'pending');
                    });
            });
        } elseif ($this->filterStatus === 'approved') {
            $query->where('status', 'approved');
        } elseif ($this->filterStatus === 'rejected') {
            $query->where('status', 'rejected');
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('text', 'like', $term)
                    ->orWhereHas('masterProduct', function ($sub) use ($term) {
                        $sub->where('title', 'like', $term)
                           ->orWhere('crm_code', 'like', $term);
                    })
                    ->orWhereHas('client', function ($sub) use ($term) {
                        $sub->where('full_name', 'like', $term)
                           ->orWhere('username', 'like', $term)
                           ->orWhere('phone', 'like', $term);
                    })
                    ->orWhereHas('replies', function ($sub) use ($term) {
                        $sub->where('text', 'like', $term);
                    });
            });
        }

        $questions = $query->paginate(15);

        return view('market::livewire.admin.question-manager', compact('questions'))
            ->layout('layouts.user');
    }
}
