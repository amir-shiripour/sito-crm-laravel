<?php

namespace Modules\Market\App\Livewire\Client;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\ProductReview;
use Modules\Market\Entities\ProductQuestion;

class InteractionsManager extends Component
{
    use WithPagination;

    public $activeTab = 'reviews'; // 'reviews' or 'questions'

    // For editing
    public $editingReviewId = null;
    public $editReviewComment = '';
    public $editReviewRating = 5;

    public $editingQuestionId = null;
    public $editQuestionText = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->cancelEdit();
    }

    // --- Reviews ---
    public function startEditReview($id)
    {
        $review = ProductReview::where('client_id', auth('client')->id())->find($id);
        if ($review && in_array($review->status, ['pending', 'rejected'])) {
            $this->editingReviewId = $id;
            $this->editReviewComment = $review->comment;
            $this->editReviewRating = $review->rating;
        }
    }

    public function updateReview()
    {
        $this->validate([
            'editReviewComment' => 'required|string|min:5|max:1000',
            'editReviewRating' => 'required|integer|min:1|max:5',
        ], [], [
            'editReviewComment' => 'متن دیدگاه',
            'editReviewRating' => 'امتیاز',
        ]);

        $review = ProductReview::where('client_id', auth('client')->id())->find($this->editingReviewId);
        if ($review && in_array($review->status, ['pending', 'rejected'])) {
            $review->update([
                'comment' => $this->editReviewComment,
                'rating' => $this->editReviewRating,
                'status' => 'pending', // Revert to pending after edit
            ]);
            session()->flash('message', 'دیدگاه با موفقیت ویرایش شد و برای بررسی مجدد ارسال گردید.');
            $this->cancelEdit();
        }
    }

    public function deleteReview($id)
    {
        $review = ProductReview::where('client_id', auth('client')->id())->find($id);
        if ($review && $review->status === 'pending') {
            $review->delete();
            session()->flash('message', 'دیدگاه با موفقیت حذف شد.');
        }
    }

    // --- Questions ---
    public function startEditQuestion($id)
    {
        $question = ProductQuestion::where('client_id', auth('client')->id())->find($id);
        if ($question && in_array($question->status, ['pending', 'rejected'])) {
            $this->editingQuestionId = $id;
            $this->editQuestionText = $question->text;
        }
    }

    public function updateQuestion()
    {
        $this->validate([
            'editQuestionText' => 'required|string|min:5|max:1000',
        ], [], [
            'editQuestionText' => 'متن پرسش',
        ]);

        $question = ProductQuestion::where('client_id', auth('client')->id())->find($this->editingQuestionId);
        if ($question && in_array($question->status, ['pending', 'rejected'])) {
            $question->update([
                'text' => $this->editQuestionText,
                'status' => 'pending', // Revert to pending
            ]);
            session()->flash('message', 'پرسش با موفقیت ویرایش شد و برای بررسی مجدد ارسال گردید.');
            $this->cancelEdit();
        }
    }

    public function deleteQuestion($id)
    {
        $question = ProductQuestion::where('client_id', auth('client')->id())->find($id);
        if ($question && $question->status === 'pending') {
            $question->delete();
            session()->flash('message', 'پرسش با موفقیت حذف شد.');
        }
    }

    public function cancelEdit()
    {
        $this->editingReviewId = null;
        $this->editReviewComment = '';
        $this->editReviewRating = 5;

        $this->editingQuestionId = null;
        $this->editQuestionText = '';
    }

    public function render()
    {
        $clientId = auth('client')->id();

        $reviews = collect();
        $questions = collect();

        if ($this->activeTab === 'reviews') {
            $reviews = ProductReview::with(['masterProduct', 'vendorProduct.variant', 'vendorProduct.vendor'])
                ->where('client_id', $clientId)
                ->latest()
                ->paginate(10);
        } else {
            $questions = ProductQuestion::with(['masterProduct', 'replies.user', 'replies.vendor', 'replies.client'])
                ->where('client_id', $clientId)
                ->latest()
                ->paginate(10);
        }

        return view('market::livewire.client.interactions-manager', [
            'reviews' => $reviews,
            'questions' => $questions,
        ])->extends('clients::layouts.client')
          ->section('content');
    }
}
