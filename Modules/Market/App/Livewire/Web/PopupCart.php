<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Modules\Market\Entities\MarketSetting;

class PopupCart extends Component
{
    public $cart = [];
    public $totalPrice = 0;
    public $totalDiscount = 0;
    public $itemCount = 0;
    public $show = false;
    public $step = 'cart'; // 'cart' or 'checkout'
    public bool $showVendor;

    public $shipping_address = [
        'province' => '',
        'city' => '',
        'address' => '',
    ];

    protected $listeners = [
        'showCartPopup' => 'showPopup',
        'cartUpdated' => 'loadCart'
    ];

    public function mount()
    {
        $this->showVendor = (bool) MarketSetting::getValue('ui.show_vendor_on_product_page', true);
        $this->loadCart();
    }

    public function loadCart()
    {
        $this->cart = Session::get('market_cart', []);
        $this->calculateTotals();
    }

    public function showPopup()
    {
        $this->loadCart();
        $this->step = 'cart'; // Always reset to cart view on open
        $this->show = true;
    }

    public function hidePopup()
    {
        $this->show = false;
    }

    public function goToCheckout()
    {
        if (count($this->cart) > 0) {
            $this->step = 'checkout';
        }
    }

    public function backToCart()
    {
        $this->step = 'cart';
    }

    public function removeItem($cartKey)
    {
        // Dispatch to the main cart manager to handle logic
        $this->dispatch('removeFromCart', $cartKey);
    }

    public function updateQuantity($cartKey, $quantity)
    {
        // Dispatch to the main cart manager to handle logic
        $this->dispatch('updateQuantity', $cartKey, $quantity);
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
        return view('market::livewire.web.popup-cart');
    }
}
