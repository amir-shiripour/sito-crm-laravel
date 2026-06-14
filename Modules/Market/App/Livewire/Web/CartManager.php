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

                $itemAttributes = $item['attributes'] ?? $variant->variant_attributes;
                $fullAttributes = [];
                if (is_array($itemAttributes)) {
                    foreach ($itemAttributes as $attrKey => $attrValue) {
                        $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                        $dictVal = $dictAttr ? $dictAttr->values->firstWhere('value', $attrValue) : null;
                        
                        // مخفی کردن ویژگی استاندارد
                        if ($attrKey === 'name' && $attrValue === 'استاندارد') {
                            continue;
                        }

                        $fullAttributes[] = [
                            'key' => $attrKey,
                            'value' => $attrValue,
                            'type' => $dictAttr->type ?? 'select',
                            'meta_value' => $dictVal->meta_value ?? null,
                        ];
                    }
                }

                $attrString = '';
                if (is_array($itemAttributes)) {
                    $pairs = [];
                    foreach ($itemAttributes as $k => $v) {
                        if ($k && $v && $v !== 'استاندارد') {
                            $pairs[] = "$k: $v";
                        }
                    }
                    if (!empty($pairs)) {
                        $attrString = ' - ' . implode(' - ', $pairs);
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
                    'title' => $variant->masterProduct->title . $attrString,
                    'image' => $variant->masterProduct->main_image,
                    'slug' => $variant->masterProduct->slug,
                    'full_attributes' => $fullAttributes,
                    'vendor_name' => $this->showVendor ? $vp->vendor->store_name : null,
                    'vendor_logo' => $this->showVendor ? $vp->vendor->logo : null,
                    'attributes' => $itemAttributes,
                    'cart_amount_step' => $vp->cart_amount_step ? (float) $vp->cart_amount_step : 0.0,
                    'purchase_step' => $vp->purchase_step ? (int) $vp->purchase_step : 0,
                ];
            }
        }

        $this->cart = $refreshedCart;
        Session::put('market_cart', $this->cart);
        $this->calculateTotals();
    }

    public function addItemToCart($variantId, $vendorProductId = null, $quantity = 1, $selectedAttributes = [])
    {
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            if (!auth()->guard('client')->check()) {
                $this->dispatch('notify', type: 'error', text: 'برای افزودن محصول به سبد خرید، ابتدا باید وارد حساب کاربری خود شوید.');
                return redirect()->route('client.login');
            }
        }

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

        // بررسی و بازنویسی مقادیر "هر X" بر اساس ورودی selectedAttributes یا مقدار پیش‌فرض دیتابیس
        $resolvedAttributes = $variant->variant_attributes ?? [];
        $hasAnyAttribute = false;
        $suffixParts = [];
        if (is_array($resolvedAttributes)) {
            foreach ($resolvedAttributes as $k => $v) {
                if (is_string($v) && str_starts_with($v, 'هر ')) {
                    $hasAnyAttribute = true;
                    $val = $selectedAttributes[$k] ?? null;
                    if (!$val) {
                        $dictAttr = MarketAttribute::with('values')->where('name', $k)->first();
                        if ($dictAttr && isset($dictAttr->values[0])) {
                            $val = $dictAttr->values[0]->value;
                        } else {
                            $val = $v;
                        }
                    }
                    $resolvedAttributes[$k] = $val;
                    $suffixParts[] = $k . '=' . $val;
                }
            }
        }

        $cartKey = $variantId . '_' . $vp->id;
        if ($hasAnyAttribute && !empty($suffixParts)) {
            sort($suffixParts);
            $cartKey .= '_' . implode('&', $suffixParts);
        }

        $currentCartQty = isset($this->cart[$cartKey]) ? $this->cart[$cartKey]['quantity'] : 0;
        $requestedQty = $currentCartQty + $quantity;

        // Verify purchase limits
        list($ok, $errorMsg) = $this->validatePurchaseLimits($variantId, $vp->id, $requestedQty, $cartKey);
        if (!$ok) {
            $this->dispatch('notify', type: 'error', text: $errorMsg);
            return;
        }

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
            'attributes' => $resolvedAttributes,
            'cart_amount_step' => $vp->cart_amount_step ? (float) $vp->cart_amount_step : 0.0,
            'purchase_step' => $vp->purchase_step ? (int) $vp->purchase_step : 0,
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
            $prospectiveCart = $this->cart;
            unset($prospectiveCart[$cartKey]);

            list($ok, $errorMsg) = $this->validateEntireCart($prospectiveCart);
            if (!$ok) {
                $this->dispatch('notify', type: 'error', text: $errorMsg);
                return;
            }

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

        // Verify purchase limits
        list($ok, $errorMsg) = $this->validatePurchaseLimits($item['variant_id'], $vp->id, $newQuantity, $cartKey);
        if (!$ok) {
            $this->dispatch('notify', type: 'error', text: $errorMsg);
            $this->loadCart(); // Force refresh to original quantity
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
            $newQuantity = $availableStock;
        }

        // Check if updating this quantity causes any other item to violate its limits
        $prospectiveCart = $this->cart;
        $prospectiveCart[$cartKey]['quantity'] = $newQuantity;
        list($ok, $errorMsg) = $this->validateEntireCart($prospectiveCart);
        if (!$ok) {
            $this->dispatch('notify', type: 'error', text: $errorMsg);
            $this->loadCart(); // Force refresh
            return;
        }

        $this->cart[$cartKey]['quantity'] = $newQuantity;
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

    private function validatePurchaseLimits($variantId, $vendorProductId, $requestedQty, $cartKeyToIgnore = null)
    {
        $vp = VendorProduct::find($vendorProductId);
        if (!$vp) return [true, ''];

        // 1. Min purchase limit
        if ($vp->min_purchase_qty > 0 && $requestedQty < $vp->min_purchase_qty) {
            return [false, "حداقل تعداد قابل سفارش برای این محصول {$vp->min_purchase_qty} عدد است."];
        }

        // 2. Max purchase limit
        if ($vp->max_purchase_qty > 0 && $requestedQty > $vp->max_purchase_qty) {
            return [false, "حداکثر تعداد قابل سفارش برای این محصول {$vp->max_purchase_qty} عدد است."];
        }

        // 3. Cart total based limit
        if ($vp->cart_amount_step > 0 && $vp->purchase_step > 0) {
            // Calculate cart total excluding the current item's quantity to find out how much other items contribute
            $otherItemsTotal = 0;
            foreach ($this->cart as $key => $item) {
                if ($key !== $cartKeyToIgnore && (int)$item['variant_id'] !== (int)$variantId) {
                    $otherItemsTotal += ($item['price'] * $item['quantity']);
                }
            }
            
            $maxAllowedByCartValue = floor($otherItemsTotal / $vp->cart_amount_step) * $vp->purchase_step;

            if ($requestedQty > $maxAllowedByCartValue) {
                $formattedStep = number_format($vp->cart_amount_step);
                if ($maxAllowedByCartValue == 0) {
                    return [false, "برای خرید این محصول، مبلغ سبد خرید شما باید حداقل {$formattedStep} تومان باشد."];
                }
                return [false, "با توجه به مبلغ سبد خرید شما، حداکثر {$maxAllowedByCartValue} عدد از این محصول قابل سفارش است (به ازای هر {$formattedStep} تومان سبد خرید، {$vp->purchase_step} عدد)."];
            }
        }

        return [true, ''];
    }

    private function validateEntireCart($prospectiveCart)
    {
        foreach ($prospectiveCart as $key => $item) {
            $vp = VendorProduct::find($item['vendor_product_id']);
            if ($vp && $vp->cart_amount_step > 0 && $vp->purchase_step > 0) {
                $otherItemsTotalForThis = 0;
                foreach ($prospectiveCart as $k => $it) {
                    if ($k !== $key && (int)$it['variant_id'] !== (int)$item['variant_id']) {
                        $otherItemsTotalForThis += ($it['price'] * $it['quantity']);
                    }
                }
                $maxAllowed = floor($otherItemsTotalForThis / $vp->cart_amount_step) * $vp->purchase_step;
                if ($item['quantity'] > $maxAllowed) {
                    $formattedStep = number_format($vp->cart_amount_step);
                    $title = $item['title'] ?? 'محصول محدود شده';
                    if ($maxAllowed == 0) {
                        return [false, "به دلیل محدودیت خرید محصول «{$title}»، مبلغ سبد خرید شما باید حداقل {$formattedStep} تومان باشد. ابتدا تعداد آن را کاهش داده یا آن را حذف کنید."];
                    }
                    return [false, "با توجه به مبلغ سبد خرید، حداکثر {$maxAllowed} عدد از محصول «{$title}» قابل خرید است. ابتدا تعداد آن را به {$maxAllowed} عدد یا کمتر کاهش دهید."];
                }
            }
        }
        return [true, ''];
    }

    public function render()
    {
        if ($this->isFullPage) {
            return view('market::livewire.web.cart-manager');
        }
        return '<div></div>';
    }
}
