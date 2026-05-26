<div class="flex items-center justify-end">
    @if ($quantityInCart > 0)
        {{-- Quantity Management View - Compact Version --}}
        <div class="flex items-center justify-evenly h-11 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all duration-300">
            <button wire:click="increment" wire:loading.attr="disabled" wire:target="increment" class="px-3 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            </button>

            <div wire:loading wire:target="increment, decrement" class="animate-spin">
                <svg class="h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <span class="text-base font-bold text-gray-900 dark:text-white" wire:loading.remove wire:target="increment, decrement">
                {{ $quantityInCart }}
            </span>

            <button wire:click="decrement" wire:loading.attr="disabled" wire:target="decrement" class="px-3 text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" /></svg>
            </button>
        </div>
    @else
        {{-- Add to Cart Button View --}}
        <button wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="w-11 h-11 rounded-2xl {{ $t['bg'] ?? 'bg-indigo-600' }} text-white flex items-center justify-center transition-all duration-300 flex-shrink-0 {{ $t['bg_hover'] ?? 'hover:bg-indigo-700' }} hover:shadow-lg {{ $t['shadow'] ?? 'hover:shadow-indigo-500/40' }} transform hover:scale-105 relative z-20 cursor-pointer" :class="{'shadow-lg shadow-orange-500/30': $wire.loading}">
            <div wire:loading.remove wire:target="addToCart">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <div wire:loading wire:target="addToCart">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </button>
    @endif
</div>
