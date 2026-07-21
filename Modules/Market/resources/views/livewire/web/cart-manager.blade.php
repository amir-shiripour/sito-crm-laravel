<div class="animate-in fade-in slide-in-from-bottom-8 duration-1000">
    @if (count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Cart Items Column --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">اقلام سفارش ({{ $itemCount }} کالا)</h2>
                <div class="space-y-6">
                    @foreach ($cart as $key => $item)
                        <div class="flex items-start gap-4 border-b border-gray-100 dark:border-gray-800 pb-6 last:border-b-0 last:pb-0">
                             <!-- Image -->
                            <a href="{{ route('market.public.product.show', ['slug' => $item['slug'], 'variant' => $item['variant_id']]) }}" class="flex-shrink-0">
                                 @if(!empty($item['image']))
                                     <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['title'] }}" class="w-28 h-28 rounded-2xl object-contain bg-gray-50 dark:bg-gray-800 p-2">
                                 @else
                                     <div class="w-28 h-28 rounded-2xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center p-2 text-gray-400">
                                         <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                         </svg>
                                     </div>
                                 @endif
                            </a>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('market.public.product.show', ['slug' => $item['slug'], 'variant' => $item['variant_id']]) }}" class="text-base font-bold text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-2 mb-2">
                                    {{ $item['title'] }}
                                </a>

                                @if(!empty($item['full_attributes']))
                                    <div class="space-y-2.5 mt-3">
                                        @foreach($item['full_attributes'] as $attribute)
                                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-medium">{{ $attribute['key'] }}:</span>
                                                @if($attribute['type'] === 'color')
                                                    <div class="w-5 h-5 rounded-full shadow-inner border-2 border-white dark:border-gray-800" style="background-color: {{ $attribute['meta_value'] ?? '#ccc' }}"></div>
                                                @elseif($attribute['type'] === 'image' && $attribute['meta_value'])
                                                    <img src="{{ Storage::url($attribute['meta_value']) }}" class="w-6 h-6 rounded-md object-cover border-2 border-white dark:border-gray-800">
                                                @endif
                                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $attribute['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($showVendor && $item['vendor_name'])
                                    <div class="flex items-center gap-2 mt-4 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        <span class="font-medium">{{ $item['vendor_name'] }}</span>
                                    </div>
                                @endif

                                @if (!empty($item['cart_amount_step']) && !empty($item['purchase_step']))
                                    <div class="mt-3 flex items-center gap-2 bg-amber-50 dark:bg-amber-900/10 px-3.5 py-2.5 rounded-xl border border-amber-100 dark:border-amber-800/20 text-xs font-bold text-amber-700 dark:text-amber-400 max-w-max">
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span>به ازای هر {{ number_format($item['cart_amount_step']) }} تومان خرید، امکان خرید {{ $item['purchase_step'] }} عدد از این محصول وجود دارد.</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col items-end justify-between h-28">
                                <div class="flex flex-col items-end">
                                    @if($item['discount_amount'] > 0 && $item['original_price'] > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-md">{{ round(($item['discount_amount'] / $item['original_price']) * 100) }}%</span>
                                            <span class="text-sm text-gray-400 line-through">{{ number_format($item['original_price'] * $item['quantity']) }}</span>
                                        </div>
                                    @endif
                                    <div class="text-lg font-black text-gray-900 dark:text-white mt-1">
                                        {{ number_format($item['price'] * $item['quantity']) }} <span class="text-xs font-medium">تومان</span>
                                    </div>
                                </div>
                                <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-xl">
                                    <button type="button" wire:click="updateItemQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="px-3 py-2 text-gray-500 hover:text-indigo-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                    </button>
                                    <input type="text" value="{{ $item['quantity'] }}" class="w-10 text-center bg-transparent border-0 text-sm font-bold text-gray-900 dark:text-white focus:ring-0" readonly>
                                    <button type="button" wire:click="updateItemQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="px-3 py-2 text-gray-500 hover:text-rose-600 transition-colors">
                                        @if($item['quantity'] > 1)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Summary and Checkout Column --}}
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">خلاصه سفارش</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm font-medium text-gray-600 dark:text-gray-300">
                            <span>مبلغ کل کالاها</span>
                            <span>{{ number_format($totalPrice + $totalDiscount) }} تومان</span>
                        </div>
                        @if($totalDiscount > 0)
                        <div class="flex justify-between items-center text-sm font-medium text-rose-600 dark:text-rose-400">
                            <span>سود شما از خرید</span>
                            <span>({{ number_format($totalDiscount) }}) تومان</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center text-sm font-medium text-gray-600 dark:text-gray-300">
                            <span>هزینه ارسال</span>
                            <span class="text-teal-600 dark:text-teal-400">وابسته به آدرس</span>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-800 my-4"></div>
                        <div class="flex justify-between items-center text-lg font-black text-gray-900 dark:text-white">
                            <span>مبلغ قابل پرداخت</span>
                            <span>{{ number_format($totalPrice) }} تومان</span>
                        </div>
                    </div>

                    <a href="{{ route('market.checkout.index') }}"
                       class="mt-8 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/30 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>ادامه و تسویه حساب</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center bg-white dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm py-20 px-4">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">سبد خرید شما خالی است</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">برای شروع خرید، به صفحه فروشگاه مراجعه کنید.</p>
            <a href="{{ route('market.public.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-500/30 active:scale-95 transition-all">
                بازگشت به فروشگاه
            </a>
        </div>
    @endif
</div>
