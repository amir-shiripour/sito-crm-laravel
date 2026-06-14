<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductQuestion;
use Modules\Market\Entities\Vendor;
use Modules\Market\App\Models\OrderItem;

class ProductQuestions extends Component
{
    use WithPagination;

    public MasterProduct $product;
    public $t = [];

    // New Question Form
    public $questionText = '';
    public $showForm = false;

    // Reply Forms (keyed by question ID)
    public $replyTexts = [];
    public $showReplyForm = [];

    public function mount(MasterProduct $product, $t = [])
    {
        $this->product = $product;
        $this->t = $t ?: ['name' => 'indigo'];
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        $this->questionText = '';
    }

    public function toggleReplyForm($questionId)
    {
        $this->showReplyForm[$questionId] = !($this->showReplyForm[$questionId] ?? false);
        $this->replyTexts[$questionId] = '';
    }

    public function submitQuestion()
    {
        // Must be logged in (as client, vendor or admin)
        $clientId = auth()->guard('client')->id();
        $userId = auth()->id();
        
        if (!$clientId && !$userId) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت پرسش ابتدا باید وارد حساب کاربری خود شوید.');
            return;
        }

        $this->validate([
            'questionText' => 'required|string|min:5|max:1000',
        ], [
            'questionText.required' => 'لطفاً متن پرسش خود را بنویسید.',
            'questionText.min' => 'پرسش باید حداقل ۵ کاراکتر باشد.',
            'questionText.max' => 'پرسش نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        $vendorId = null;
        if ($userId) {
            $vendor = Vendor::where('user_id', $userId)->first();
            if ($vendor) {
                $vendorId = $vendor->id;
                $userId = null; // Associated to vendor
            }
        }

        // Questions submitted from web are always pending
        ProductQuestion::create([
            'master_product_id' => $this->product->id,
            'client_id' => $clientId,
            'vendor_id' => $vendorId,
            'user_id' => $userId,
            'text' => $this->questionText,
            'status' => 'pending',
        ]);

        $this->questionText = '';
        $this->showForm = false;

        session()->flash('message', 'پرسش شما با موفقیت ثبت شد و پس از بررسی و تایید مدیریت نمایش داده خواهد شد.');
    }

    public function submitReply($questionId)
    {
        $clientId = auth()->guard('client')->id();
        $userId = auth()->id();
        
        if (!$clientId && !$userId) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت پاسخ ابتدا باید وارد حساب کاربری خود شوید.');
            return;
        }

        $this->validate([
            'replyTexts.' . $questionId => 'required|string|min:3|max:1000'
        ], [
            'replyTexts.' . $questionId . '.required' => 'لطفاً متن پاسخ خود را بنویسید.',
            'replyTexts.' . $questionId . '.min' => 'پاسخ باید حداقل ۳ کاراکتر باشد.',
            'replyTexts.' . $questionId . '.max' => 'پاسخ نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.'
        ]);

        $vendorId = null;
        $status = 'pending';

        if ($userId) {
            $vendor = Vendor::where('user_id', $userId)->first();
            if ($vendor) {
                $vendorId = $vendor->id;
                $userId = null;
                $status = 'approved'; // Vendor replies are auto-approved
            } else {
                $status = 'approved'; // Admin replies are auto-approved
            }
        }

        ProductQuestion::create([
            'parent_id' => $questionId,
            'master_product_id' => $this->product->id,
            'client_id' => $clientId,
            'vendor_id' => $vendorId,
            'user_id' => $userId,
            'text' => $this->replyTexts[$questionId],
            'status' => $status,
        ]);

        $this->replyTexts[$questionId] = '';
        $this->showReplyForm[$questionId] = false;

        if ($status === 'approved') {
            session()->flash('message_' . $questionId, 'پاسخ شما با موفقیت ثبت و منتشر شد.');
        } else {
            session()->flash('message_' . $questionId, 'پاسخ شما با موفقیت ثبت شد و پس از بررسی و تایید مدیریت نمایش داده خواهد شد.');
        }
    }

    public function likeQuestion($questionId)
    {
        $question = ProductQuestion::find($questionId);
        if (!$question) return;

        $liked = session()->get('liked_questions', []);
        $disliked = session()->get('disliked_questions', []);

        if (in_array($questionId, $liked)) {
            $question->decrement('likes_count');
            $liked = array_diff($liked, [$questionId]);
        } else {
            if (in_array($questionId, $disliked)) {
                $question->decrement('dislikes_count');
                $disliked = array_diff($disliked, [$questionId]);
            }
            $question->increment('likes_count');
            $liked[] = $questionId;
        }

        session()->put('liked_questions', $liked);
        session()->put('disliked_questions', $disliked);
    }

    public function dislikeQuestion($questionId)
    {
        $question = ProductQuestion::find($questionId);
        if (!$question) return;

        $liked = session()->get('liked_questions', []);
        $disliked = session()->get('disliked_questions', []);

        if (in_array($questionId, $disliked)) {
            $question->decrement('dislikes_count');
            $disliked = array_diff($disliked, [$questionId]);
        } else {
            if (in_array($questionId, $liked)) {
                $question->decrement('likes_count');
                $liked = array_diff($liked, [$questionId]);
            }
            $question->increment('dislikes_count');
            $disliked[] = $questionId;
        }

        session()->put('liked_questions', $liked);
        session()->put('disliked_questions', $disliked);
    }

    public function hasPurchased($clientId)
    {
        if (!$clientId) return false;

        return OrderItem::whereHas('order', function ($query) use ($clientId) {
            $query->where('client_id', $clientId)
                ->whereHas('status', function ($q) {
                    $q->where('admin_label', 'تحویل نهایی به مشتری')
                      ->orWhere('client_label', 'تحویل نهایی به مشتری')
                      ->orWhere('system_type', 'completed');
                });
        })
        ->whereHas('vendorProduct', function ($q) {
            $q->whereHas('variant', function ($sub) {
                $sub->where('master_product_id', $this->product->id);
            });
        })
        ->exists();
    }

    public function render()
    {
        $questions = $this->product->questions()
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['client', 'vendor', 'user', 'approvedReplies.client', 'approvedReplies.vendor', 'approvedReplies.user'])
            ->latest()
            ->paginate(5);

        return view('market::livewire.web.product-questions', [
            'questions' => $questions,
        ]);
    }
}
