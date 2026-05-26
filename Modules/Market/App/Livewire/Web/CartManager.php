<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Modules\Market\Entities\MarketAttribute;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\WarehouseStock;
use Modules\Market\Entities\MarketSetting;
use Illuminate\Support\Facades\Session;

class CartManager extends Component
{
    public $cart = [];
    public $totalPrice = 0;
    public $totalDiscount = 0;
    public $itemCount = 0;

    public bool $isFullPage = false;
    public bool $showVendor;

    public $shipping_address = [
        'province' => '',
        'city' => '',
        'address' => '',
        'zip_code' => ''
    ];

    protected $listeners = [
        'addToCart' => 'addItemToCart',
        'removeFromCart' => 'removeItemFromCart',
        'updateQuantity' => 'updateItemQuantity',
        'cartUpdated' => 'loadCart'
    ];

    public function mount($isFullPage = false)
    {
        $this->isFullPage = $isFullPage;
        $this->showVendor = (bool) MarketSetting::getValue('ui.show_vendor_on_product_page', true);
        $this->loadCart();
    }

    public function loadCart()
    {
        $sessionCart = Session::get('market_cart', []);
        $refreshedCart = [];
        $attributeDictionary = MarketAttribute::with('values')->get();

        foreach ($sessionCart as $key => $item) {
            $variant = ProductVariant::with('masterProduct')->find($item['variant_id']);
            $vp = VendorProduct::with('vendor')->find($item['vendor_product_id']);

            if ($variant && $vp) {
                $finalPrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;
                $discountAmount = $vp->discount_price > 0 ? ($vp->price - $vp->discount_price) : 0;

                $fullAttributes = [];
                if (is_array($variant->variant_attributes)) {
                    foreach ($variant->variant_attributes as $attrKey => $attrValue) {
                        $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                        $dictVal = $dictAttr ? $dictAttr->values->firstWhere('value', $attrValue) : null;
                        $fullAttributes[] = [
                            'key' => $attrKey,
                            'value' => $attrValue,
                            'type' => $dictAttr->type ?? 'select',
                            'meta_value' => $dictVal->meta_value ?? null,
                        ];
                    }
                }

                // تضمین جفت شدن دقیق تمام کلیدهای ریلیشنال و لجستیک در آرایه سبد خرید سشن
                $refreshedCart[$key] = [
                    'variant_id' => (int) $variant->id,
                    'vendor_product_id' => (int) $vp->id,
                    'vendor_id' => $vp->vendor_id, // تزریق قطعی آیدی فروشنده برای لایه مالی و WMS
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $finalPrice,
                    'original_price' => (float) $vp->price,
                    'discount_amount' => (float) $discountAmount,
                    'title' => $variant->masterProduct->title,
                    'image' => $variant->masterProduct->main_image,
                    'slug' => $variant->masterProduct->slug,
                    'full_attributes' => $fullAttributes,
                    'vendor_name' => $this->showVendor ? $vp->vendor->store_name : null,
                    'vendor_logo' => $this->showVendor ? $vp->vendor->logo : null,
                    'attributes' => $variant->variant_attributes,
                ];
            }
        }

        $this->cart = $refreshedCart;
        Session::put('market_cart', $this->cart);
        $this->calculateTotals();
    }

    public function addItemToCart($variantId, $vendorProductId = null, $quantity = 1)
    {
        $variant = ProductVariant::with('masterProduct')->find($variantId);
        if (!$variant) {
            $this->dispatch('notify', type: 'error', text: 'محصول یافت نشد.');
            return;
        }

        $vp = null;
        if ($vendorProductId) {
            $vp = VendorProduct::with('vendor')->find($vendorProductId);
        } else {
            $vp = VendorProduct::with('vendor')->where('product_variant_id', $variantId)
                ->where('status', 'published')
                ->where('stock', '>', 0)
                ->orderBy('price', 'asc')
                ->first();
        }

        if (!$vp) {
            $this->dispatch('notify', type: 'error', text: 'این محصول در حال حاضر موجود نیست.');
            return;
        }

        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);
        $availableStock = 0;

        if ($isWmsActive) {
            $availableStock = WarehouseStock::where('product_variant_id', $variantId)
                ->whereHas('warehouse', function($q) use ($vp) {
                    $q->where('vendor_id', $vp->vendor_id)->where('is_active', true);
                })->sum('online_stock');
        } else {
            $availableStock = $vp->stock;
        }

        $cartKey = $variantId . '_' . $vp->id;
        $currentCartQty = isset($this->cart[$cartKey]) ? $this->cart[$cartKey]['quantity'] : 0;
        $requestedQty = $currentCartQty + $quantity;

        if ($availableStock < $requestedQty) {
            $this->dispatch('notify', type: 'error', text: "موجودی کافی نیست. حداکثر {$availableStock} عدد قابل سفارش است.");
            return;
        }

        $finalPrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;
        $discountAmount = $vp->discount_price > 0 ? ($vp->price - $vp->discount_price) : 0;

        // ذخیره فونداسیون اولیه دیتا همراه با فیلد حیاتی vendor_id مستقیم در زمان درج اولیه
        $this->cart[$cartKey] = [
            'variant_id' => (int) $variantId,
            'vendor_product_id' => (int) $vp->id,
            'vendor_id' => $vp->vendor_id, // پایداری مطلق آیدی فروشنده در هسته اولیه سشن
            'quantity' => $requestedQty,
            'price' => (float) $finalPrice,
            'original_price' => (float) $vp->price,
            'discount_amount' => (float) $discountAmount,
            'title' => $variant->masterProduct->title,
            'image' => $variant->masterProduct->main_image,
            'slug' => $variant->masterProduct->slug,
            'attributes' => $variant->variant_attributes,
        ];

        Session::put('market_cart', $this->cart);
        $this->loadCart(); // ریلود متمرکز کلیدها برای یکپارچگی نهایی

        $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت به سبد خرید افزوده شد.');
        $this->dispatch('cartUpdated', ['cart' => $this->cart]);
        $this->dispatch('showCartPopup');
    }

    public function removeItemFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            Session::put('market_cart', $this->cart);
            $this->loadCart();
            $this->dispatch('notify', type: 'success', text: 'محصول از سبد خرید حذف شد.');
            $this->dispatch('cartUpdated', ['cart' => $this->cart]);
        }
    }

    public function updateItemQuantity($cartKey, $newQuantity)
    {
        if (!isset($this->cart[$cartKey])) return;

        $newQuantity = (int)$newQuantity;
        if ($newQuantity <= 0) {
            $this->removeItemFromCart($cartKey);
            return;
        }

        $item = $this->cart[$cartKey];
        $vp = VendorProduct::find($item['vendor_product_id']);
        if (!$vp) {
            $this->removeItemFromCart($cartKey);
            return;
        }

        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);
        $availableStock = 0;

        if ($isWmsActive) {
            $availableStock = WarehouseStock::where('product_variant_id', $item['variant_id'])
                ->whereHas('warehouse', function($q) use ($vp) {
                    $q->where('vendor_id', $vp->vendor_id)->where('is_active', true);
                })->sum('online_stock');
        } else {
            $availableStock = $vp->stock;
        }

        if ($availableStock < $newQuantity) {
            $this->dispatch('notify', type: 'error', text: "موجودی کافی نیست. حداکثر {$availableStock} عدد قابل سفارش است.");
            $this->cart[$cartKey]['quantity'] = $availableStock;
        } else {
            $this->cart[$cartKey]['quantity'] = $newQuantity;
        }

        Session::put('market_cart', $this->cart);
        $this->loadCart();
        $this->dispatch('cartUpdated', ['cart' => $this->cart]);
    }

    private function calculateTotals()
    {
        $this->totalPrice = 0;
        $this->totalDiscount = 0;
        $this->itemCount = 0;

        foreach ($this->cart as $item) {
            $this->totalPrice += ($item['price'] * $item['quantity']);
            $this->totalDiscount += ($item['discount_amount'] * $item['quantity']);
            $this->itemCount += $item['quantity'];
        }
    }

    public function render()
    {
        if ($this->isFullPage) {
            return view('market::livewire.web.cart-manager');
        }
        return '<div></div>';
    }
}
