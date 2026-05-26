<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
    @includeIf('partials.jalali-date-picker')

    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-indigo-500";
        $labelClass = "block text-sm font-bold text-gray-800 dark:text-gray-200 mb-2";
    @endphp

    {{-- Main Content: Checkout Form --}}
    <div class="lg:col-span-7">
        <form wire:submit.prevent="submit" class="space-y-8">
            @foreach($groupedSchema as $group)
                <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm" wire:key="group-{{ $group['id'] }}">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $group['name'] }}</h2>
                        @if($loop->first)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اطلاعات خود را برای تکمیل سفارش وارد کنید.</p>
                        @endif
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($group['fields'] as $field)
                                @php
                                    $modelName = 'formData.' . $field['id'];
                                    $htmlId = 'form-field-' . str_replace('.', '_', $field['id']);
                                    $widthClass = match($field['width'] ?? 'full') {
                                        '1/2' => 'sm:col-span-1',
                                        default => 'sm:col-span-2',
                                    };
                                    $currentValue = $formData[$field['id']] ?? '';
                                @endphp

                                @include('market::livewire.web.partials._checkout-form-fields')
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Payment Method Selection Card --}}
            <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">روش پرداخت</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لطفاً روش پرداخت ترجیحی خود را انتخاب کنید.</p>
                </div>

                <div class="space-y-3">
                    @forelse($paymentMethods as $key => $details)
                        <div
                            wire:key="payment-method-page-{{$key}}"
                            wire:click="$set('payment_method', '{{ $key }}')"
                            class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all duration-300
                                {{ $payment_method === $key
                                    ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/50'
                                    : 'bg-gray-50 dark:bg-gray-800/30 border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                                }}"
                        >
                            <input type="radio" name="payment_method" value="{{ $key }}" class="sr-only" {{ $payment_method === $key ? 'checked' : '' }}>

                            <div class="flex-shrink-0 w-12 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                @if(!empty($details['logo']))
                                    <img src="{{ asset($details['logo']) }}" alt="{{ $details['title'] }}" class="max-h-6 max-w-10 object-contain">
                                @else
                                    {{-- اصلاح نهایی غلط املایی کلمه Letters که اس‌وی‌جی را می‌شکست --}}
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @endif
                            </div>

                            <div class="ml-3 mr-3 text-sm flex-grow">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $details['title'] }}</span>
                                @if($details['description'] ?? null)
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ $details['description'] }}</p>
                                @endif
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all {{ $payment_method === $key ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700' }}">
                                <div class="w-2 h-2 rounded-full bg-white dark:bg-gray-700 transition-transform {{ $payment_method === $key ? 'scale-100' : 'scale-0' }}"></div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            در حال حاضر هیچ روش پرداخت فعالی وجود ندارد.
                        </div>
                    @endforelse
                </div>
                @error('payment_method') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </form>
    </div>

    {{-- Sidebar: Order Summary --}}
    <div class="lg:col-span-5">
        <div class="sticky top-28">
            <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">خلاصه سفارش</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-4 max-h-72 overflow-y-auto pr-2 -ml-2">
                        @foreach($cartItems as $item)
                            <div class="flex items-center justify-between" wire:key="cart-item-{{ $item['variant_id'] }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                                        <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $item['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $item['quantity'] }} عدد</p>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ number_format($item['price'] * $item['quantity']) }} <span class="text-[10px] text-gray-500">تومان</span></p>
                                    @if($item['price'] < $item['base_price'])
                                        <p class="text-xs text-gray-400 line-through">{{ number_format($item['base_price'] * $item['quantity']) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-5 space-y-3">
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-600 dark:text-gray-400">جمع کل سبد خرید:</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($totals['subtotal']) }} <span class="text-xs">تومان</span></p>
                        </div>
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-600 dark:text-gray-400">تخفیف شما:</p>
                            <p class="font-medium text-red-500">-{{ number_format($totals['discount']) }} <span class="text-xs">تومان</span></p>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-2 border-t border-dashed border-gray-200 dark:border-gray-700 mt-3">
                            <p class="text-gray-900 dark:text-white">مبلغ قابل پرداخت:</p>
                            <p class="text-indigo-600 dark:text-indigo-400">{{ number_format($totals['grand_total']) }} <span class="text-xs">تومان</span></p>
                        </div>
                    </div>

                    <button wire:click="submit" wire:loading.attr="disabled"
                            class="w-full mt-4 px-6 py-4 rounded-xl bg-indigo-600 text-white font-bold text-base shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submit">
                            پرداخت و ثبت نهایی سفارش
                        </span>
                        <span wire:loading wire:target="submit">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>در حال پردازش...</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-xs text-gray-400 dark:text-gray-500">پرداخت امن از طریق درگاه‌های رسمی کشور</p>
            </div>
        </div>
    </div>
</div>
