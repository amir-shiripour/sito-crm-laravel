<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CartCounter extends Component
{
    public $itemCount = 0;
    public $isBottomNav = false;

    protected $listeners = ['cartUpdated' => 'updateCount'];

    public function mount($isBottomNav = false)
    {
        $this->isBottomNav = $isBottomNav;
        $this->updateCount();
    }

    public function updateCount()
    {
        $cart = Session::get('market_cart', []);
        $this->itemCount = array_sum(array_column($cart, 'quantity'));
    }

    public function render()
    {
        return <<<'HTML'
        @if($isBottomNav)
            @php $isActive = request()->routeIs('market.public.cart*') || request()->routeIs('checkout*'); @endphp
            <button onclick="window.Livewire.dispatch('showCartPopup')" class="w-full h-full flex flex-col items-center justify-center relative transition-transform duration-300 {{ $isActive ? '-translate-y-0.5' : '' }}">
                @if($isActive)
                    <div class="absolute top-3 w-10 h-10 bg-[#3F7D20] opacity-15 dark:opacity-20 blur-xl rounded-full"></div>
                    <span class="absolute top-0 w-8 h-1 bg-[#3F7D20] rounded-b-full"></span>
                @endif
                <div class="relative z-10 flex flex-col items-center justify-center">
                    @if($isActive)
                        <svg class="w-6 h-6 mb-1 text-[#3F7D20] dark:text-[#5cba2f] transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M15.949 3.684l1.104 3.316h1.947a3 3 0 0 1 2.962 3.477l-1.252 7.131a4 4 0 0 1 -3.954 3.392h-9.512a3.994 3.994 0 0 1 -3.95 -3.371l-1.258 -7.173a3 3 0 0 1 2.964 -3.456h1.945l1.105 -3.316a1 1 0 0 1 1.898 .632l-.895 2.684h5.893l-.895 -2.684a1 1 0 1 1 1.898 -.632m-3.949 7.316a3 3 0 0 0 -2.995 2.824l-.005 .176a3 3 0 1 0 3 -3" />
                        </svg>
                    @else
                        <svg class="w-6 h-6 mb-1 text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M5.001 8h13.999a2 2 0 0 1 1.977 2.304l-1.255 7.152a3 3 0 0 1 -2.966 2.544h-9.512a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304" />
                            <path d="M17 10l-2 -6" />
                            <path d="M7 10l2 -6" />
                        </svg>
                    @endif
                    @if($itemCount > 0)
                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#3F7D20] text-white text-[10px] font-bold shadow-md">
                            {{ $itemCount }}
                        </span>
                    @endif
                    <span class="text-[10px] font-bold {{ $isActive ? 'text-[#3F7D20] dark:text-[#5cba2f]' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200' }} transition-colors duration-300">سبد خرید</span>
                </div>
            </button>
        @else
            <button onclick="window.Livewire.dispatch('showCartPopup')" class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                    <path d="M5.001 8h13.999a2 2 0 0 1 1.977 2.304l-1.255 7.152a3 3 0 0 1 -2.966 2.544h-9.512a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304" />
                    <path d="M17 10l-2 -6" />
                    <path d="M7 10l2 -6" />
                </svg>
                @if($itemCount > 0)
                    <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#3F7D20] text-white text-[10px] font-bold shadow-md">
                        {{ $itemCount }}
                    </span>
                @endif
            </button>
        @endif
        HTML;
    }
}
