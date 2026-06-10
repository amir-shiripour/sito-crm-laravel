<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\ProductReview;

use Livewire\Attributes\Url;

class ReviewManager extends Component
{
    use WithPagination;

    #[Url(except: 'pending')]
    public $filterStatus = 'pending';

    #[Url(except: '')]
    public $search = '';

    // Rejection Modal properties
    public $rejectingReviewId = null;
    public $rejectionReason = '';

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
        $review = ProductReview::findOrFail($id);
        $review->update([
            'status' => 'approved',
            'rejection_reason' => null
        ]);
        $this->dispatch('notify', type: 'success', text: 'دیدگاه با موفقیت تایید و در سایت منتشر شد.');
    }

    public function promptReject($id)
    {
        $this->rejectingReviewId = $id;
        $this->rejectionReason = '';
    }

    public function cancelReject()
    {
        $this->rejectingReviewId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5'
        ], [
            'rejectionReason.required' => 'لطفاً علت رد دیدگاه را بنویسید.',
            'rejectionReason.min' => 'علت رد دیدگاه باید حداقل ۵ کاراکتر باشد.'
        ]);

        $review = ProductReview::findOrFail($this->rejectingReviewId);
        $review->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason
        ]);

        $this->dispatch('notify', type: 'success', text: 'دیدگاه رد شد و دلیل آن ثبت گردید.');
        $this->cancelReject();
    }

    public function delete($id)
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();
        $this->dispatch('notify', type: 'success', text: 'دیدگاه با موفقیت حذف شد.');
    }

    public function render()
    {
        $query = ProductReview::with(['masterProduct', 'client'])
            ->latest();

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('comment', 'like', $term)
                    ->orWhereHas('masterProduct', function ($sub) use ($term) {
                        $sub->where('title', 'like', $term)
                           ->orWhere('crm_code', 'like', $term);
                    })
                    ->orWhereHas('client', function ($sub) use ($term) {
                        $sub->where('full_name', 'like', $term)
                           ->orWhere('username', 'like', $term)
                           ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $reviews = $query->paginate(15);

        return view('market::livewire.admin.review-manager', compact('reviews'))
            ->layout('layouts.user');
    }
}
