<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\VendorProduct;

class VendorProductReview extends Component
{
    use WithPagination;

    public $filterStatus = 'pending_review';

    // متغیرهای مودال رد محصول
    public $rejectingProductId = null;
    public $rejectionReason = '';

    public function setFilter($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function approve($id)
    {
        $product = VendorProduct::findOrFail($id);
        $product->update([
            'status' => 'published',
            'rejection_reason' => null // پاک کردن دلیل رد قبلی در صورت وجود
        ]);
        $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت تایید و منتشر شد.');
    }

    // باز کردن مدال برای گرفتن دلیل رد
    public function promptReject($id)
    {
        $this->rejectingProductId = $id;
        $this->rejectionReason = ''; // ریست کردن متن
    }

    // لغو رد کردن
    public function cancelReject()
    {
        $this->rejectingProductId = null;
        $this->rejectionReason = '';
    }

    // ثبت نهایی رد محصول
    public function confirmReject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5'
        ], [
            'rejectionReason.required' => 'لطفاً دلیل رد محصول را بنویسید.',
            'rejectionReason.min' => 'دلیل رد محصول باید حداقل ۵ کاراکتر باشد.'
        ]);

        $product = VendorProduct::findOrFail($this->rejectingProductId);
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason
        ]);

        $this->dispatch('notify', type: 'success', text: 'محصول رد شد و دلیل آن برای فروشنده ثبت گردید.');

        $this->rejectingProductId = null;
        $this->rejectionReason = '';
    }

    public function render()
    {
        // 💡 رفع ارور: اضافه کردن variant.masterProduct چون masterProduct به تنهایی در VendorProduct تعریف مستقیم در دیتابیس ندارد
        $products = VendorProduct::with(['variant.masterProduct', 'vendor.user'])
            ->where('status', $this->filterStatus)
            ->latest()
            ->paginate(15);

        return view('market::livewire.admin.vendor-product-review', compact('products'));
    }
}
