<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-[100] animate-in fade-in duration-300"
>
    <!-- Overlay -->
    <div x-on:click="show = false" class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"></div>

    <!-- Modal -->
    <div class="absolute bottom-0 left-0 right-0 w-full max-w-lg mx-auto bg-white dark:bg-gray-900 rounded-t-3xl shadow-2xl flex flex-col transform transition-all duration-300 sm:relative sm:bottom-auto sm:left-auto sm:right-auto sm:mt-10 sm:rounded-3xl md:max-w-lg h-[85vh] sm:h-auto"
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95">

        <!-- Header -->
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                سبد خرید ({{ $itemCount }} کالا)
            </h2>
            <button x-on:click="show = false" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="space-y-6">
                @if(count($cart) > 0)
                    @foreach ($cart as $key => $item)
                        <div class="flex items-start gap-4 border-b border-gray-100 dark:border-gray-800 pb-6 last:border-b-0 last:pb-0">
                            <!-- Image -->
                            <a href="{{ route('market.public.product.show', ['slug' => $item['slug'], 'variant' => $item['variant_id']]) }}" class="flex-shrink-0">
                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['title'] }}" class="w-24 h-24 rounded-xl object-contain bg-gray-50 dark:bg-gray-800 p-2">
                            </a>

                            <!-- Info & Actions -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('market.public.product.show', ['slug' => $item['slug'], 'variant' => $item['variant_id']]) }}" class="text-sm font-bold text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-2 mb-2">
                                    {{ $item['title'] }}
                                </a>

                                @if(!empty($item['full_attributes']))
                                    <div class="space-y-2 mt-2">
                                        @foreach($item['full_attributes'] as $attribute)
                                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-medium">{{ $attribute['key'] }}:</span>
                                                @if($attribute['type'] === 'color')
                                                    <div class="w-4 h-4 rounded-full shadow-inner border border-gray-200 dark:border-gray-700" style="background-color: {{ $attribute['meta_value'] ?? '#ccc' }}"></div>
                                                @elseif($attribute['type'] === 'image' && $attribute['meta_value'])
                                                    <img src="{{ Storage::url($attribute['meta_value']) }}" class="w-5 h-5 rounded-md object-cover border border-gray-200 dark:border-gray-700">
                                                @endif
                                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $attribute['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($showVendor && $item['vendor_name'])
                                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        <span>{{ $item['vendor_name'] }}</span>
                                    </div>
                                @endif

                                @if (!empty($item['cart_amount_step']) && !empty($item['purchase_step']))
                                    <div class="mt-2.5 flex items-center gap-1.5 bg-amber-50 dark:bg-amber-900/10 px-3 py-1.5 rounded-lg border border-amber-100 dark:border-amber-800/20 text-[10px] font-bold text-amber-700 dark:text-amber-400">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span>به ازای هر {{ number_format($item['cart_amount_step']) }} تومان خرید، امکان خرید {{ $item['purchase_step'] }} عدد وجود دارد.</span>
                                    </div>
                                @endif

                                <div class="flex items-end justify-between mt-3">
                                    <div class="flex flex-col items-start">
                                        @if($item['discount_amount'] > 0 && $item['original_price'] > 0)
                                            <div class="flex items-center gap-2">
                                                <span class="px-1.5 py-0.5 bg-rose-500 text-white text-[9px] font-bold rounded-md">{{ round(($item['discount_amount'] / $item['original_price']) * 100) }}%</span>
                                                <span class="text-xs text-gray-400 line-through">{{ number_format($item['original_price'] * $item['quantity']) }}</span>
                                            </div>
                                        @endif
                                        <div class="text-base font-black text-gray-900 dark:text-white mt-1">
                                            {{ number_format($item['price'] * $item['quantity']) }} <span class="text-xs font-medium">تومان</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <button type="button" wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="px-2.5 py-1.5 text-gray-500 hover:text-indigo-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        </button>
                                        <input type="text" value="{{ $item['quantity'] }}" class="w-8 text-center bg-transparent border-0 text-sm font-bold text-gray-900 dark:text-white focus:ring-0 p-0" readonly>
                                        <button type="button" wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="px-2.5 py-1.5 text-gray-500 hover:text-rose-600 transition-colors">
                                            @if($item['quantity'] > 1)
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-10">
                         <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">سبد خرید شما خالی است</h3>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="p-5 border-t border-gray-100 dark:border-gray-800 flex-shrink-0">
            @if($totalDiscount > 0)
                <div class="flex justify-between items-center mb-4 bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 px-4 py-3 rounded-xl">
                    <span class="text-sm font-bold">سود شما از این خرید</span>
                    <span class="text-sm font-black">{{ number_format($totalDiscount) }} تومان</span>
                </div>
            @endif
            <div class="flex justify-between items-center mb-6">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">مبلغ قابل پرداخت</span>
                <span class="text-xl font-black text-gray-900 dark:text-white">{{ number_format($totalPrice) }} تومان</span>
            </div>

            <button
                type="button"
                x-on:click="show = false; $dispatch('open-checkout-modal')"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 active:scale-95 transition-all text-center block @if(count($cart) == 0) opacity-50 pointer-events-none @endif">
                تکمیل فرآیند خرید
            </button>
        </div>
    </div>
</div>
