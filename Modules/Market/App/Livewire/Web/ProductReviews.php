<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductReview;

class ProductReviews extends Component
{
    use WithPagination;

    public MasterProduct $product;

    // Form attributes
    public $rating = 0;
    public $comment = '';

    // Purchase-based review properties
    public $purchasedItems = [];
    public $isPurchaseBased = false;
    public $selectedVendorProductId = null;

    public function mount(MasterProduct $product)
    {
        $this->product = $product;

        if (auth()->guard('client')->check()) {
            $this->purchasedItems = \Modules\Market\App\Models\OrderItem::whereHas('order', function ($query) {
                $query->where('client_id', auth()->guard('client')->id())
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
            ->with(['vendorProduct.variant', 'vendorProduct.vendor'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'vendor_product_id' => $item->vendor_product_id,
                    'variant_attributes' => $item->vendorProduct->variant->variant_attributes,
                    'vendor_name' => $item->vendorProduct->vendor->store_name ?? 'فروشگاه نامشخص',
                ];
            })
            ->toArray();

            if (!empty($this->purchasedItems)) {
                $this->selectedVendorProductId = $this->purchasedItems[0]['vendor_product_id'];
            }
        }
    }

    public function submitReview()
    {
        if (!auth()->guard('client')->check()) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت دیدگاه ابتدا باید وارد حساب کاربری خود شوید.');
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
        ], [
            'rating.required' => 'لطفاً امتیاز خود را ثبت کنید.',
            'rating.min' => 'امتیاز انتخابی نامعتبر است.',
            'rating.max' => 'امتیاز انتخابی نامعتبر است.',
            'comment.required' => 'لطفاً متن دیدگاه خود را بنویسید.',
            'comment.min' => 'متن دیدگاه باید حداقل ۵ کاراکتر باشد.',
            'comment.max' => 'متن دیدگاه نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        $vendorProductId = null;
        if ($this->isPurchaseBased && $this->selectedVendorProductId) {
            $valid = collect($this->purchasedItems)->contains('vendor_product_id', $this->selectedVendorProductId);
            if ($valid) {
                $vendorProductId = $this->selectedVendorProductId;
            }
        }

        ProductReview::create([
            'master_product_id' => $this->product->id,
            'client_id' => auth()->guard('client')->id(),
            'vendor_product_id' => $vendorProductId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => 'pending',
        ]);

        $this->reset(['rating', 'comment', 'isPurchaseBased']);
        if (!empty($this->purchasedItems)) {
            $this->selectedVendorProductId = $this->purchasedItems[0]['vendor_product_id'];
        }

        session()->flash('message', 'دیدگاه شما با موفقیت ثبت شد و پس از بررسی و تایید مدیریت نمایش داده خواهد شد.');
    }

    public function render()
    {
        // دریافت دیدگاه‌های تایید شده برای محصول
        $reviews = $this->product->reviews()
            ->where('status', 'approved')
            ->with(['vendorProduct.variant', 'vendorProduct.vendor'])
            ->latest()
            ->paginate(5);

        // محاسبه آمار توزیع امتیازها
        $stats = [];
        $totalReviews = $this->product->reviews()->where('status', 'approved')->count();
        $averageRating = $this->product->reviews()->where('status', 'approved')->avg('rating') ?: 0;

        for ($i = 5; $i >= 1; $i--) {
            $count = $this->product->reviews()->where('status', 'approved')->where('rating', $i)->count();
            $stats[$i] = [
                'count' => $count,
                'percent' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0
            ];
        }

        $showVendor = \Modules\Market\Entities\MarketSetting::getValue('ui.show_vendor_on_product_page', true);
        $attributeDictionary = \Modules\Market\Entities\MarketAttribute::with('values')->get();

        return view('market::livewire.web.product-reviews', [
            'reviews' => $reviews,
            'stats' => $stats,
            'totalReviews' => $totalReviews,
            'averageRating' => round($averageRating, 1),
            'showVendor' => $showVendor,
            'attributeDictionary' => $attributeDictionary,
        ]);
    }
}
