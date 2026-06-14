<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class AddToCartButton extends Component
{
    public $variantId;
    public $vendorProductId;
    public $quantityInCart = 0;
    public array $t = []; // Property to accept the theme array

    protected $listeners = ['cartUpdated' => 'checkCart'];

    public bool $hasCartLimit = false;
    public ?string $productSlug = null;

    public function mount($variantId, $vendorProductId, $t = [])
    {
        $this->variantId = $variantId;
        $this->vendorProductId = $vendorProductId;
        $this->t = $t;

        $vp = \Modules\Market\Entities\VendorProduct::find($vendorProductId);
        if ($vp) {
            if ($vp->cart_amount_step > 0 && $vp->purchase_step > 0) {
                $this->hasCartLimit = true;
            }
            $variant = \Modules\Market\Entities\ProductVariant::with('masterProduct')->find($variantId);
            if ($variant && $variant->masterProduct) {
                $this->productSlug = $variant->masterProduct->slug;
            }
        }

        $this->checkCart();
    }

    public function checkCart()
    {
        $cart = Session::get('market_cart', []);
        $cartKey = $this->variantId . '_' . $this->vendorProductId;

        if (isset($cart[$cartKey])) {
            $this->quantityInCart = $cart[$cartKey]['quantity'];
        } else {
            $this->quantityInCart = 0;
        }
    }

    public function addToCart()
    {
        $this->dispatch('addToCart', variantId: $this->variantId, vendorProductId: $this->vendorProductId, quantity: 1);
    }

    public function increment()
    {
        $this->dispatch('updateQuantity', cartKey: $this->getCartKey(), newQuantity: $this->quantityInCart + 1);
    }

    public function decrement()
    {
        if ($this->quantityInCart > 0) {
            $this->dispatch('updateQuantity', cartKey: $this->getCartKey(), newQuantity: $this->quantityInCart - 1);
        }
    }

    private function getCartKey()
    {
        return $this->variantId . '_' . $this->vendorProductId;
    }

    public function render()
    {
        return view('market::livewire.web.add-to-cart-button');
    }
}
