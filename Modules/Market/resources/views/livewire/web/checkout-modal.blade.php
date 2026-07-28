<div
    x-data="{ isOpen: @entangle('isOpen'), showNewAddressModal: @entangle('showNewAddressModal') }"
    x-show="isOpen"
    x-on:keydown.escape.window="if (!showNewAddressModal) isOpen = false"
    x-on:checkout-modal-opened.window="document.body.classList.add('overflow-y-hidden')"
    x-on:checkout-modal-closed.window="document.body.classList.remove('overflow-y-hidden')"
    class="fixed inset-0 z-[150] animate-in fade-in duration-300"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    @includeIf('partials.jalali-date-picker')

    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-indigo-500";
        $labelClass = "block text-sm font-bold text-gray-800 dark:text-gray-200 mb-2";
    @endphp

    <!-- Backdrop -->
    <div x-show="isOpen"
         x-on:click="if (!showNewAddressModal) isOpen = false"
         class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity">
    </div>

    <!-- Modal Panel -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         class="absolute bottom-0 left-0 right-0 w-full mx-auto bg-white dark:bg-gray-900 rounded-t-3xl shadow-2xl flex flex-col transform transition-all duration-300 sm:relative sm:bottom-auto sm:left-auto sm:right-auto sm:mt-10 sm:rounded-3xl sm:max-w-2xl md:max-w-2xl h-[90vh] sm:h-auto overflow-hidden text-right"
         @click.outside="if (!showNewAddressModal) isOpen = false">

        <form wire:submit.prevent="submit" class="flex flex-col h-full sm:h-auto overflow-hidden">
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                    تکمیل اطلاعات و تسویه حساب
                </h3>
                <button type="button" @click="isOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <span class="sr-only">بستن</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6 flex-1 overflow-y-auto custom-scrollbar">
                    @if($errors->any())
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800/50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 mr-2">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">
                                        خطا در اطلاعات ورودی
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                        <p>لطفا خطاهای فرم را بررسی و اصلاح کنید.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($form)
                        @foreach($groupedSchema as $group)
                            @php
                                $addressSelectorRendered = false;
                            @endphp
                            <div class="space-y-6" wire:key="group-{{ $group['id'] }}">
                                <h3 class="text-md font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">{{ $group['name'] }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    @foreach($group['fields'] as $field)
                                        @php
                                            $isAddressField = ($field['type'] === 'select-province-city' ||
                                                               $field['type'] === 'postal-code' ||
                                                               $field['id'] === 'address' ||
                                                               str_contains($field['id'], 'address') ||
                                                               str_contains($field['id'], 'postal') ||
                                                               str_contains($field['id'], 'postcode'));
                                        @endphp

                                        @if(Auth::guard('client')->check() && $isAddressField)
                                            @if(!$addressSelectorRendered)
                                                @php $addressSelectorRendered = true; @endphp
                                                {{-- Render the Address Selector in place of the first address field --}}
                                                <div class="sm:col-span-2 space-y-4" wire:key="modal-address-selector-block-{{ $group['id'] }}">
                                                    <div class="bg-gray-50 dark:bg-gray-800/45 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 space-y-4">
                                                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                </svg>
                                                                نشانی تحویل سفارش
                                                            </h3>
                                                            
                                                            <button type="button" wire:click="openNewAddressModal" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-bold flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                                </svg>
                                                                ثبت آدرس جدید
                                                            </button>
                                                        </div>

                                                        @if(count($addresses) > 0)
                                                            @php
                                                                $selectedAddress = collect($addresses)->firstWhere('id', $selectedAddressId) ?? $addresses[0];
                                                            @endphp
                                                            
                                                            <div class="relative bg-white dark:bg-gray-800/60 border border-indigo-500/20 rounded-xl p-4 shadow-sm">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $selectedAddress->title }}</span>
                                                                        @if($selectedAddress->is_default)
                                                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-md">پیش‌فرض</span>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    {{-- Dropdown selector to change address --}}
                                                                    <div x-data="{ open: false }" class="relative">
                                                                        <button type="button" @click="open = !open" class="text-[11px] text-gray-500 hover:text-indigo-700 dark:text-indigo-400 font-bold flex items-center gap-1 select-none">
                                                                            تغییر آدرس
                                                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                            </svg>
                                                                        </button>
                                                                        
                                                                        <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 py-1.5 overflow-hidden" style="display: none;">
                                                                            @foreach($addresses as $addr)
                                                                                <button type="button" @click="open = false" wire:click="selectAddress({{ $addr->id }})" class="w-full text-right px-4 py-2.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-800 flex flex-col gap-1 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors">
                                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $addr->title }} {{ $addr->is_default ? '(پیش‌فرض)' : '' }}</span>
                                                                                    <span class="text-gray-500 dark:text-gray-400 truncate">{{ $addr->province }}، {{ $addr->city }}، {{ $addr->address }}</span>
                                                                                </button>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-semibold">
                                                                    {{ $selectedAddress->province }}، {{ $selectedAddress->city }}، {{ $selectedAddress->address }}
                                                                </p>
                                                                @if($selectedAddress->postal_code)
                                                                    <div class="text-[10px] text-gray-500 mt-2 font-mono">کد پستی: {{ $selectedAddress->postal_code }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="text-center py-6 space-y-3 bg-white dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">هنوز هیچ آدرسی ثبت نکرده‌اید.</p>
                                                                <button type="button" wire:click="openNewAddressModal" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
                                                                    ثبت اولین آدرس روی نقشه
                                                                </button>
                                                            </div>
                                                        @endif

                                                        {{-- Details Toggle --}}
                                                        @if(count($addresses) > 0)
                                                            <div class="flex justify-start">
                                                                <button type="button" wire:click="$toggle('showManualAddress')" class="text-[10px] text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold flex items-center gap-1 transition-colors">
                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                    </svg>
                                                                    {{ $showManualAddress ? 'پنهان کردن جزئیات آدرس' : 'مشاهده و ویرایش جزئیات آدرس به‌صورت دستی' }}
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Only render the dynamic fields when toggled manually --}}
                                            @if($showManualAddress)
                                                @php
                                                    $modelName = 'formData.' . $field['id'];
                                                    $htmlId = 'modal-form-field-' . str_replace('.', '_', $field['id']);
                                                    $widthClass = match($field['width'] ?? 'full') {
                                                        '1/2' => 'sm:col-span-1',
                                                        default => 'sm:col-span-2',
                                                    };
                                                    $currentValue = $formData[$field['id']] ?? '';
                                                @endphp
                                                @include('market::livewire.web.partials._checkout-form-fields')
                                            @endif
                                        @else
                                            {{-- Guest or non-address fields - Render normal dynamic inputs --}}
                                            @php
                                                $modelName = 'formData.' . $field['id'];
                                                $htmlId = 'modal-form-field-' . str_replace('.', '_', $field['id']);
                                                $widthClass = match($field['width'] ?? 'full') {
                                                    '1/2' => 'sm:col-span-1',
                                                    default => 'sm:col-span-2',
                                                };
                                                $currentValue = $formData[$field['id']] ?? '';
                                            @endphp
                                            @include('market::livewire.web.partials._checkout-form-fields')
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">فرم تسویه حساب یافت نشد</h3>
                            <p class="mt-1 text-sm text-gray-500">ممکن است فرم پیش‌فرض برای تسویه حساب تعریف نشده باشد.</p>
                        </div>
                    @endif

                    {{-- Shipping Selection --}}
                    @if(!empty($shippingMethods))
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">روش ارسال مرسوله</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($shippingMethods as $method)
                                    <label wire:key="modal-ship-{{ $method['id'] }}" class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-indigo-300 dark:hover:border-indigo-700 {{ $selectedShippingMethodId == $method['id'] ? 'bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/20' : 'bg-gray-50 dark:bg-gray-900/10 border-gray-200 dark:border-gray-700' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" wire:model.live="selectedShippingMethodId" value="{{ $method['id'] }}" class="text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ $method['cost'] > 0 ? number_format($method['cost']) . ' ' . $this->getCurrencyLabel() : 'رایگان' }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-400">
                                            @if($method['driver'] === 'post_api') استعلام مستقیم از پست
                                            @elseif($method['driver'] === 'tipax_api') استعلام مستقیم از تیپاکس
                                            @else محاسبه بر اساس وزن مرسوله @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedShippingMethodId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Delivery Slots --}}
                        @if(!empty($availableSlots))
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">انتخاب بازه زمانی تحویل</label>
                                <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50 rounded-xl">
                                    @foreach($availableSlots as $slot)
                                        <button type="button" 
                                                wire:key="modal-slot-{{ $slot['slot_id'] }}-{{ $slot['date'] }}"
                                                wire:click="selectSlot({{ $slot['slot_id'] }}, '{{ $slot['date'] }}')"
                                                class="flex-1 min-w-[140px] text-right p-3 rounded-xl border text-xs transition-all {{ $selectedSlotId == $slot['slot_id'] && $selectedDeliveryDate == $slot['date'] ? 'bg-indigo-600 border-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-indigo-300' }}">
                                            <div class="font-bold">{{ $slot['day_name'] }} ({{ $slot['jalali_date'] }})</div>
                                            <div class="text-[10px] mt-1 {{ $selectedSlotId == $slot['slot_id'] && $selectedDeliveryDate == $slot['date'] ? 'text-indigo-200' : 'text-gray-400' }}">
                                                ساعت: {{ $slot['start_time'] }} الی {{ $slot['end_time'] }}
                                            </div>
                                            <div class="text-[9px] mt-0.5 font-semibold {{ $selectedSlotId == $slot['slot_id'] && $selectedDeliveryDate == $slot['date'] ? 'text-indigo-100' : 'text-emerald-500' }}">
                                                ظرفیت باقی‌مانده: {{ $slot['remaining'] }}
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                @error('selectedSlotId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endif

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">روش پرداخت</label>
                        <div class="space-y-3">
                            @forelse($paymentMethods as $key => $details)
                                <div
                                    wire:key="payment-method-modal-{{$key}}"
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
                        @error('payment_method') <span class="text-xs text-red-550 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex-shrink-0">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex flex-col text-right">
                            <div class="flex items-baseline gap-1.5 text-[11px] text-gray-500">
                                <span>هزینه محصولات:</span>
                                <span class="font-bold">{{ number_format($totalAmount) }} تومان</span>
                                @if($shippingCost > 0)
                                    <span class="mr-1">+ هزینه ارسال:</span>
                                    <span class="font-bold text-gray-600 dark:text-gray-300">{{ number_format($shippingCost) }} تومان</span>
                                @endif
                            </div>
                            <div class="flex items-baseline mt-1">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-400">مبلغ قابل پرداخت</span>
                            </div>
                        </div>
                        <div class="text-lg font-black text-gray-900 dark:text-white">
                            {{ number_format($totalAmount + $shippingCost) }} <span class="text-xs font-medium">تومان</span>
                        </div>
                    </div>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="w-full inline-flex items-center justify-center py-3.5 border border-transparent text-base font-bold rounded-xl shadow-lg shadow-indigo-500/30 text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:ring-offset-gray-800 transition-all active:scale-95 disabled:opacity-50">
                        <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>پرداخت و ثبت نهایی</span>
                        <span wire:loading>در حال پردازش...</span>
                    </button>
                </div>
            </form>
        </div>

    {{-- Modal for New Address Quick Creation --}}
    @if($showNewAddressModal)
        <div wire:key="modal-new-address-modal" x-data="{ init() { document.body.classList.add('overflow-hidden'); }, destroy() { document.body.classList.remove('overflow-hidden'); } }" class="fixed inset-0 z-[200] flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none" style="backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4);">
            <div class="relative w-full max-w-2xl mx-auto my-6 px-4">
                <div class="relative flex flex-col w-full bg-white dark:bg-gray-900 border-0 rounded-3xl shadow-2xl outline-none focus:outline-none overflow-hidden max-h-[95vh] dark:border dark:border-gray-700">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-5 border-b border-solid border-gray-200 dark:border-gray-700 rounded-t-3xl">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">ثبت آدرس جدید روی نقشه</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">موقعیت آدرس خود را روی نقشه مشخص کرده و ذخیره کنید.</p>
                        </div>
                        <button type="button" wire:click="closeNewAddressModal" class="p-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-indigo-600 hover:border-indigo-500 transition-all">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 overflow-y-auto max-h-[60vh] space-y-6">
                        {{-- Autocomplete search box --}}
                        <div class="space-y-1 relative" x-data="{ showDropdown: true }">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">جستجوی آدرس / محله</label>
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchQuery" 
                                       @focus="showDropdown = true" 
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-gray-400 dark:placeholder-gray-500 pl-10 outline-none" 
                                       placeholder="مثال: تهران، ونک، ملاصدرا...">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-550">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            
                            {{-- Suggestions dropdown --}}
                            @if(!empty($searchQuery) && count($searchResults) > 0)
                                <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-40 overflow-y-auto py-1">
                                    @foreach($searchResults as $res)
                                        <button type="button" 
                                                wire:click="selectSearchResult({{ $res['lat'] }}, {{ $res['lng'] }}, @js($res['title']))"
                                                @click="showDropdown = false"
                                                class="w-full text-right px-4 py-2 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/30 transition-colors flex flex-col gap-0.5 border-b border-gray-100 last:border-0 dark:border-gray-700/50">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $res['title'] }}</span>
                                            @if(!empty($res['address']))
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $res['address'] }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Map Picker --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">انتخاب موقعیت روی نقشه</label>
                            <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div 
                                    x-data="{
                                        initNewAddressMap() {
                                            let map = null;
                                            let marker = null;
                                            const lat = $wire.newLat || 35.6892;
                                            const lng = $wire.newLng || 51.3890;
                                            const provider = @js($mapProvider);
                                            const apiKey = @js($mapApiKey);

                                            const setupEvents = (leafletMap) => {
                                                marker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
                                                
                                                leafletMap.on('click', (e) => {
                                                    const newLat = e.latlng.lat;
                                                    const newLng = e.latlng.lng;
                                                    marker.setLatLng([newLat, newLng]);
                                                    $wire.fetchNewAddressFromCoordinates(newLat, newLng);
                                                });

                                                marker.on('dragend', (e) => {
                                                    const newLat = marker.getLatLng().lat;
                                                    const newLng = marker.getLatLng().lng;
                                                    $wire.fetchNewAddressFromCoordinates(newLat, newLng);
                                                });

                                                window.addEventListener('mapMoveTo', (e) => {
                                                    const targetLat = e.detail.lat;
                                                    const targetLng = e.detail.lng;
                                                    if (map && marker) {
                                                        map.setView([targetLat, targetLng], 16);
                                                        marker.setLatLng([targetLat, targetLng]);
                                                    }
                                                });

                                                setTimeout(() => leafletMap.invalidateSize(), 250);
                                            };

                                            const initDefaultMap = () => {
                                                if (typeof L === 'undefined') return;
                                                map = L.map(this.$el).setView([lat, lng], 15);

                                                if (provider === 'map_ir' && apiKey) {
                                                    if (!L.TileLayer.WMS.Header) {
                                                        L.TileLayer.WMS.Header = L.TileLayer.WMS.extend({
                                                            initialize: function (url, options) {
                                                                const wmsOptions = Object.assign({}, options);
                                                                this.headers = wmsOptions.headers || {};
                                                                delete wmsOptions.headers;
                                                                L.TileLayer.WMS.prototype.initialize.call(this, url, wmsOptions);
                                                            },
                                                            createTile: function (coords, done) {
                                                                const url = this.getTileUrl(coords);
                                                                const img = document.createElement('img');

                                                                fetch(url, {
                                                                    method: 'GET',
                                                                    headers: this.headers,
                                                                    mode: 'cors'
                                                                })
                                                                .then(response => {
                                                                    if (!response.ok) throw new Error('Network response was not ok');
                                                                    return response.blob();
                                                                })
                                                                .then(blob => {
                                                                    const objectURL = URL.createObjectURL(blob);
                                                                    img.onload = () => {
                                                                        URL.revokeObjectURL(objectURL);
                                                                        done(null, img);
                                                                    };
                                                                    img.onerror = () => {
                                                                        done(new Error('Image load error'), img);
                                                                    };
                                                                    img.src = objectURL;
                                                                })
                                                                .catch(error => {
                                                                    done(error, img);
                                                                });

                                                                return img;
                                                            }
                                                        });

                                                        L.tileLayer.wms.header = function (url, options) {
                                                            return new L.TileLayer.WMS.Header(url, options);
                                                        };
                                                    }

                                                    L.tileLayer.wms.header('https://map.ir/shiveh', {
                                                        layers: 'Shiveh:Shiveh',
                                                        format: 'image/png',
                                                        transparent: true,
                                                        maxZoom: 18,
                                                        headers: { 'x-api-key': apiKey },
                                                        attribution: '&copy; Map.ir'
                                                    }).addTo(map);
                                                } else {
                                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                        maxZoom: 18,
                                                        attribution: '&copy; OpenStreetMap'
                                                    }).addTo(map);
                                                }
                                                setupEvents(map);
                                            };

                                             if (provider === 'neshan' && apiKey) {
                                                 const initNeshan = () => {
                                                     try {
                                                         map = new L.Map(this.$el, {
                                                             key: apiKey,
                                                             maptype: 'dreamy',
                                                             poi: true,
                                                             traffic: false,
                                                             center: [lat, lng],
                                                             zoom: 15
                                                         });
                                                         setupEvents(map);
                                                     } catch (err) {}
                                                 };

                                                 if (typeof L !== 'undefined' && L.Map && L.Map.prototype.addGoogleLayer) {
                                                     initNeshan();
                                                 } else {
                                                     const css = document.createElement('link');
                                                     css.href = 'https://static.neshan.org/sdk/leaflet/1.4.0/leaflet.css';
                                                     css.rel = 'stylesheet';
                                                     document.head.appendChild(css);
                                                     
                                                     const script = document.createElement('script');
                                                     script.src = 'https://static.neshan.org/sdk/leaflet/1.4.0/leaflet.js';
                                                     script.onload = initNeshan;
                                                     document.head.appendChild(script);
                                                 }
                                             } else {
                                                 if (typeof L !== 'undefined') {
                                                     initDefaultMap();
                                                 } else {
                                                     const css = document.createElement('link');
                                                     css.href = '{{ asset("vendor/leaflet/leaflet.css") }}';
                                                     css.rel = 'stylesheet';
                                                     document.head.appendChild(css);
                                                     
                                                     const script = document.createElement('script');
                                                     script.src = '{{ asset("vendor/leaflet/leaflet.js") }}';
                                                     script.onload = initDefaultMap;
                                                     document.head.appendChild(script);
                                                 }
                                             }
                                         }
                                     }"
                                     x-init="initNewAddressMap()"
                                     id="modal-new-address-map" 
                                     wire:ignore 
                                     class="w-full h-56 bg-gray-100 dark:bg-gray-900" 
                                     style="z-index: 10;">
                                </div>

                                {{-- GPS locator --}}
                                <button type="button" onclick="
                                    if (navigator.geolocation) {
                                        navigator.geolocation.getCurrentPosition(
                                            (position) => {
                                                const userLat = position.coords.latitude;
                                                const userLng = position.coords.longitude;
                                                window.dispatchEvent(new CustomEvent('mapMoveTo', { detail: { lat: userLat, lng: userLng } }));
                                                $wire.fetchNewAddressFromCoordinates(userLat, userLng);
                                            },
                                            (error) => {
                                                alert('خطا در دریافت موقعیت از GPS: ' + error.message);
                                            },
                                            { enableHighAccuracy: true, timeout: 8000 }
                                        );
                                    } else {
                                        alert('مرورگر شما از GPS پشتیبانی نمی کند.');
                                    }
                                " class="absolute bottom-10 left-2.5 z-[20] bg-white hover:bg-gray-100 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors flex items-center justify-center" title="موقعیت فعلی من (GPS)">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                        <circle cx="12" cy="12" r="8" stroke-width="2" />
                                        <path d="M12 2v2M12 20v2M2 12h2M20 12h2" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </button>

                                <div class="absolute bottom-2 right-2 z-[20] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-[9px] text-gray-600 dark:text-gray-300 font-medium select-none">
                                    برای تغییر مکان، نشانگر را بکشید یا روی نقشه کلیک کنید.
                                </div>
                            </div>
                        </div>

                        {{-- Details Inputs --}}
                        <div class="relative">
                            {{-- Modal Geocode Spinner Overlay --}}
                            <div wire:loading wire:target="fetchNewAddressFromCoordinates" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px] z-[40] flex items-center justify-center rounded-2xl transition-all">
                                <div class="flex flex-col items-center gap-2 bg-white dark:bg-gray-800 px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                                    <div class="w-5 h-5 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-[11px] font-bold text-gray-800 dark:text-gray-200">در حال دریافت نشانی روی نقشه...</span>
                                </div>
                            </div>
                            @php
                                $jsonPath = base_path('Modules/Clients/resources/data/iran-provinces-cities.json');
                                $provincesData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
                                $allProvinces = array_keys($provincesData);
                                $baseInputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none";
                                $labelClass = "block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200";
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                 x-data="{
                                     province: @entangle('newProvince'),
                                     city: @entangle('newCity'),
                                     provinces: @js($allProvinces),
                                     cities: [],
                                     provincesData: @js($provincesData),
                                     init() {
                                         if (this.province && this.provincesData[this.province]) {
                                             this.cities = this.provincesData[this.province];
                                         }
                                         this.$watch('province', value => {
                                             this.cities = (value && this.provincesData[value]) ? this.provincesData[value] : [];
                                             if (value && this.cities && !this.cities.includes(this.city)) {
                                                 this.city = '';
                                             }
                                         });
                                     }
                                 }">
                                <div>
                                    <label for="new_title" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">عنوان آدرس (خانه، محل کار و...)</label>
                                    <input type="text" id="new_title" wire:model.defer="newTitle" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-gray-400 dark:placeholder-gray-500 outline-none" placeholder="مثال: خانه">
                                    @error('newTitle') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="new_postal_code" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">کد پستی</label>
                                    <input type="text" id="new_postal_code" wire:model.defer="newPostalCode" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 font-mono text-center dir-ltr focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder-gray-400 dark:placeholder-gray-500 outline-none" placeholder="1234567890">
                                    @error('newPostalCode') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                {{-- Province Selector Dropdown --}}
                                <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                                    <label class="{{ $labelClass }}">استان</label>
                                    <div @click="open = !open" class="{{ $baseInputClass }} cursor-pointer flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800': open, 'bg-gray-50 dark:bg-gray-800': !open}">
                                        <span x-text="province || 'انتخاب استان...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !province}"></span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                    <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                        <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                        <template x-for="p in provinces.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="p">
                                            <div @click="province = p; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': province == p, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50': province != p}">
                                                <span x-text="p"></span>
                                                <svg x-show="province == p" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                    @error('newProvince') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                {{-- City Selector Dropdown --}}
                                <div x-data="{ open: false, search: '' }" @click.away="open = false" class="relative" wire:ignore>
                                    <label class="{{ $labelClass }}">شهر</label>
                                    <div @click="province ? open = !open : null" class="{{ $baseInputClass }} flex justify-between items-center transition-colors select-none" :class="{'ring-2 ring-indigo-500/20 border-indigo-500 dark:border-indigo-500 bg-white dark:bg-gray-800 cursor-pointer': open && province, 'bg-gray-50 dark:bg-gray-800 cursor-pointer': !open && province, 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-800/30': !province}">
                                        <span x-text="city || 'انتخاب شهر...'" class="block truncate" :class="{'text-gray-400 dark:text-gray-500': !city}"></span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180 text-indigo-500 dark:text-indigo-400': open && province}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                    <div x-show="open && province" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-2" style="display: none;">
                                        <input type="text" x-model="search" placeholder="جستجو..." class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-4 py-2 text-sm focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                                        <template x-for="c in cities.filter(item => item.toLowerCase().includes(search.toLowerCase()))" :key="c">
                                            <div @click="city = c; open = false; search = ''" class="px-4 py-2.5 cursor-pointer transition-all flex items-center gap-2 group" :class="{'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold': city == c, 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50': city != c}">
                                                <span x-text="c"></span>
                                                <svg x-show="city == c" class="w-4 h-4 mr-auto text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                    @error('newCity') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="new_address" class="block mb-1 text-[11px] font-bold text-gray-800 dark:text-gray-200">نشانی دقیق پستی</label>
                                    <textarea id="new_address" wire:model="newAddress" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 resize-y min-h-[50px]"></textarea>
                                    @error('newAddress') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end p-5 border-t border-solid border-gray-200 dark:border-gray-700 rounded-b-3xl gap-3">
                        <button type="button" wire:click="saveNewAddress" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
                            ثبت و استفاده از آدرس
                        </button>
                        <button type="button" wire:click="closeNewAddressModal" class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition-colors">
                            انصراف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Transfer Payment Modal (کارت به کارت / واریز بانکی) --}}
    @if($showTransferModal)
        <div class="fixed inset-0 z-[999] overflow-y-auto" dir="rtl">
            <div class="flex min-h-full items-center justify-center p-2.5 sm:p-4 text-center">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeTransferModal"></div>

                <div class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all w-full max-w-[98%] sm:max-w-xl md:max-w-2xl lg:max-w-3xl my-2 sm:my-6 border border-gray-200 dark:border-gray-700 flex flex-col max-h-[92vh]">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/50 to-white dark:from-gray-800 dark:to-gray-800 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">اطلاعات واریز بانکی (کارت به کارت)</h3>
                                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">لطفاً حساب مقصد را انتخاب کرده و اطلاعات واریز را ثبت نمایید</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeTransferModal" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-4 sm:p-6 space-y-5 sm:space-y-6 overflow-y-auto custom-scrollbar flex-1">

                        {{-- Bank Accounts Cards Carousel --}}
                        <div x-data="{
                                active: @entangle('selectedBankAccountIndex').live,
                                total: {{ count($bankAccounts) }},
                                copied: '',
                                scrollToActive() {
                                    this.$nextTick(() => {
                                        if (this.$refs.track && this.$refs.track.children && this.$refs.track.children[0] && this.$refs.track.children[0].children[this.active]) {
                                            this.$refs.track.children[0].children[this.active].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                                        }
                                    });
                                },
                                copyText(text, key) {
                                    if (!text) return;
                                    if (navigator.clipboard && window.isSecureContext) {
                                        navigator.clipboard.writeText(text).then(() => {
                                            this.copied = key;
                                            setTimeout(() => this.copied = '', 2000);
                                        }).catch(() => {
                                            this.fallbackCopy(text, key);
                                        });
                                    } else {
                                        this.fallbackCopy(text, key);
                                    }
                                },
                                fallbackCopy(text, key) {
                                    const el = document.createElement('textarea');
                                    el.value = text;
                                    el.setAttribute('readonly', '');
                                    el.style.position = 'absolute';
                                    el.style.left = '-9999px';
                                    document.body.appendChild(el);
                                    el.select();
                                    try {
                                        document.execCommand('copy');
                                        this.copied = key;
                                        setTimeout(() => this.copied = '', 2000);
                                    } catch (err) {}
                                    document.body.removeChild(el);
                                },
                                next() {
                                    this.active = (this.active + 1) % this.total;
                                    this.scrollToActive();
                                },
                                prev() {
                                    this.active = (this.active - 1 + this.total) % this.total;
                                    this.scrollToActive();
                                },
                                select(index) {
                                    this.active = index;
                                    this.scrollToActive();
                                }
                            }"
                            x-init="
                                if (active === null || active === undefined || active >= total) active = 0;
                                setTimeout(() => scrollToActive(), 150);
                                $watch('active', value => scrollToActive());
                            "
                            class="relative w-full rounded-2xl sm:rounded-3xl p-3.5 sm:p-5 bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-xs space-y-4">

                            {{-- Final Total Price Box --}}
                            @php
                                $payableTotal = ($totals['grand_total'] ?? 0) + ($shippingCost ?? 0);
                            @endphp
                            <div class="flex items-center justify-between gap-3 p-3.5 px-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 shadow-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">مبلغ قابل پرداخت:</span>
                                        <span class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                                            {{ number_format($payableTotal) }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">تومان</span>
                                    </div>
                                </div>

                                {{-- Icon-Only Copy Button for Raw Amount --}}
                                <button type="button" 
                                        @click="copyText('{{ $payableTotal }}', 'total')"
                                        title="کپی مبلغ خالص (بدون پسوند جهت همراه بانک)"
                                        class="p-2 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white transition-all shadow-xs active:scale-90 flex items-center justify-center shrink-0">
                                    <template x-if="copied === 'total'">
                                        <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="copied !== 'total'">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </template>
                                </button>
                            </div>

                            <!-- Carousel Header -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                    <label class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        انتخاب کارت / حساب مقصد <span class="text-rose-500">*</span>
                                    </label>
                                </div>

                                @if(count($bankAccounts) > 1)
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-full border border-gray-200 dark:border-gray-700">
                                            <span x-text="active + 1"></span> / {{ count($bankAccounts) }}
                                        </span>

                                        <button type="button" @click="prev()"
                                                class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all active:scale-95">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>

                                        <button type="button" @click="next()"
                                                class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all active:scale-95">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Peeking Carousel Track with Native Scroll-Snap --}}
                            <div class="relative w-full overflow-x-auto snap-x snap-mandatory py-2 no-scrollbar scroll-smooth"
                                 x-ref="track">
                                <div class="flex items-center gap-3.5 sm:gap-4 px-[5%] sm:px-[10%] min-w-full">
                                    @foreach($bankAccounts as $index => $acc)
                                        @php
                                            $cardGradients = [
                                                0 => 'from-slate-900 via-indigo-950 to-blue-950',
                                                1 => 'from-zinc-900 via-purple-950 to-slate-950',
                                                2 => 'from-slate-900 via-emerald-950 to-teal-950',
                                                3 => 'from-stone-900 via-rose-950 to-zinc-950',
                                            ];
                                            $bgGradient = $cardGradients[$index % 4];
                                            $ownerName = $acc['owner_name'] ?? ($acc['name'] ?? '');
                                            $bankName = $acc['bank_name'] ?? 'بانک';
                                            $cardNumber = !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '';
                                            $accountNumber = !empty($acc['account_number']) ? trim($acc['account_number']) : '';
                                            $ibanNumber = !empty($acc['iban']) ? trim($acc['iban']) : '';
                                        @endphp
                                        <div wire:key="bank-card-modal-slide-{{ $index }}" 
                                             @click="select({{ $index }})"
                                             class="snap-center shrink-0 w-[88%] sm:w-[350px] md:w-[380px] transition-all duration-300 select-none cursor-pointer"
                                             :class="active === {{ $index }} ? 'scale-100 opacity-100 z-20' : 'scale-95 opacity-50 hover:opacity-80 z-10'">
                                            
                                            <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex flex-col justify-between text-white select-none border-2 transition-all duration-300 min-h-[230px] sm:min-h-[245px] shadow-xl"
                                                 :class="active === {{ $index }} ? 'border-indigo-400 ring-4 ring-indigo-500/30 shadow-indigo-500/25' : 'border-transparent'">

                                                <!-- Background Gradient & Glow -->
                                                <div class="absolute inset-0 bg-gradient-to-br {{ $bgGradient }}"></div>
                                                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>

                                                {{-- Header: Account Owner Name --}}
                                                <div class="relative z-10 flex items-center justify-between border-b border-white/20 pb-2.5 mb-2">
                                                    <div>
                                                        <span class="text-[11px] sm:text-xs font-bold text-amber-200/90 block mb-0.5">صاحب حساب:</span>
                                                        <span class="text-sm sm:text-base md:text-lg font-black text-white tracking-wide drop-shadow-md">
                                                            {{ !empty($ownerName) ? $ownerName : $bankName }}
                                                        </span>
                                                    </div>

                                                    <!-- Selected Badge -->
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-[11px] sm:text-xs font-bold px-2.5 py-1 rounded-full transition-all"
                                                              :class="active === {{ $index }} ? 'bg-indigo-600 text-white shadow-md border border-indigo-400/50' : 'bg-white/20 text-white/80'">
                                                            <span x-text="active === {{ $index }} ? 'انتخاب‌شده' : 'کارت ' + ({{ $index }} + 1)"></span>
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Middle Section: All Numbers Left-Aligned with Orderly High-Legibility Boxes --}}
                                                <div class="relative z-10 space-y-2 my-1.5">
                                                    <!-- Card Number (Slightly Smaller Font Size) -->
                                                    @if(!empty($cardNumber))
                                                        <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-2 rounded-xl border border-white/20">
                                                            <span class="text-[11px] sm:text-xs font-bold text-amber-200/90 shrink-0">شماره کارت:</span>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs sm:text-sm font-extrabold tracking-wider sm:tracking-widest text-white block" style="direction: ltr; display: inline-block;">
                                                                    {{ implode(' - ', str_split($cardNumber, 4)) }}
                                                                </span>
                                                                <button type="button" 
                                                                        @click.stop="copyText('{{ $cardNumber }}', 'modal-card-{{ $index }}')"
                                                                        title="کپی شماره کارت"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'modal-card-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'modal-card-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                    </template>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- Account Number -->
                                                    @if(!empty($accountNumber))
                                                        <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-2 rounded-xl border border-white/20">
                                                            <span class="text-[11px] sm:text-xs font-bold text-amber-200/90 shrink-0">شماره حساب:</span>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs sm:text-sm font-bold text-white block" style="direction: ltr; display: inline-block;">
                                                                    {{ $accountNumber }}
                                                                </span>
                                                                <button type="button" 
                                                                        @click.stop="copyText('{{ $accountNumber }}', 'modal-account-{{ $index }}')"
                                                                        title="کپی شماره حساب"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'modal-account-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'modal-account-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                    </template>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- IBAN / Sheba -->
                                                    @if(!empty($ibanNumber))
                                                        <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-2 rounded-xl border border-white/20">
                                                            <span class="text-[11px] sm:text-xs font-bold text-amber-200/90 shrink-0">شماره شبا:</span>
                                                            <div class="flex items-center gap-2 overflow-hidden">
                                                                <span class="text-[11px] sm:text-xs font-bold text-white block truncate" style="direction: ltr; display: inline-block;">
                                                                    {{ $ibanNumber }}
                                                                </span>
                                                                <button type="button" 
                                                                        @click.stop="copyText('{{ $ibanNumber }}', 'modal-iban-{{ $index }}')"
                                                                        title="کپی شماره شبا"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'modal-iban-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'modal-iban-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                    </template>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Footer: Bank Name --}}
                                                <div class="relative z-10 flex items-center justify-between pt-2 border-t border-white/20 text-xs">
                                                    <span class="text-[11px] sm:text-xs font-bold text-amber-200/90">بانک صادرکننده:</span>
                                                    <span class="font-black text-amber-300 text-xs sm:text-sm drop-shadow-md">
                                                        {{ $bankName }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Pagination Dots -->
                            @if(count($bankAccounts) > 1)
                                <div class="flex items-center justify-center gap-1.5 pt-1">
                                    @foreach($bankAccounts as $index => $acc)
                                        <button type="button" @click="select({{ $index }})"
                                                class="h-2 rounded-full transition-all duration-300"
                                                :class="active === {{ $index }} ? 'w-6 bg-indigo-600 dark:bg-indigo-400' : 'w-2 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400'">
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @error('selectedBankAccountIndex') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Transfer Details Form --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                            <div>
                                <label for="transfer_sender_name_modal" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">نام واریزکننده <span class="text-rose-500">*</span></label>
                                <input type="text" id="transfer_sender_name_modal" wire:model.defer="transfer_sender_name" placeholder="مثال: علی محمدی" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none">
                                @error('transfer_sender_name') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="transfer_mobile_modal" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">شماره موبایل واریزکننده <span class="text-rose-500">*</span></label>
                                <input type="text" id="transfer_mobile_modal" wire:model.defer="transfer_mobile" placeholder="۰۹۱۲۳۴۵۶۷۸۹" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none dir-ltr text-right">
                                @error('transfer_mobile') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="transfer_ref_number_modal" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">شماره مرجع / پیگیری <span class="text-rose-500">*</span></label>
                                <input type="text" id="transfer_ref_number_modal" wire:model.defer="transfer_ref_number" placeholder="مثال: ۱۲۳۴۵۶۷۸" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none dir-ltr text-right">
                                @error('transfer_ref_number') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="transfer_payment_date_modal" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">تاریخ پرداخت (شمسی) <span class="text-rose-500">*</span></label>
                                <input type="text" id="transfer_payment_date_modal" data-jdp wire:model.live="transfer_payment_date" x-on:change="$wire.set('transfer_payment_date', $event.target.value)" placeholder="۱۴۰۳/۰۵/۰۵" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none text-center dir-ltr">
                                @error('transfer_payment_date') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Image Upload Zone (Optimized WebP Upload) --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">تصویر رسید پرداخت (اختیاری)</label>
                            <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-4 sm:p-5 text-center hover:border-indigo-500 transition-colors bg-gray-50/50 dark:bg-gray-900/30 cursor-pointer">
                                <input type="file" wire:model="transfer_receipt" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                @if($transfer_receipt)
                                    <div class="relative z-20 flex flex-col items-center gap-2">
                                        <img src="{{ $transfer_receipt->temporaryUrl() }}" class="max-h-36 rounded-xl object-contain shadow-md border border-gray-200 dark:border-gray-700" />
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            تصویر رسید بارگذاری شد (برای تغییر کلیک کنید)
                                        </span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-2 text-gray-500 dark:text-gray-400 py-2">
                                        <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب یا رهاسازی تصویر رسید</span>
                                        <span class="text-[10px] text-gray-400">فرمت‌های مجاز: JPG, PNG, WEBP (بهینه‌سازی خودکار)</span>
                                    </div>
                                @endif
                                <div wire:loading wire:target="transfer_receipt" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 z-30 flex items-center justify-center rounded-2xl">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        در حال پیش‌نمایش و اعتبارسنجی فایل...
                                    </span>
                                </div>
                            </div>
                            @error('transfer_receipt') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-2.5 sm:gap-3 p-4 sm:p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 rounded-b-2xl sm:rounded-b-3xl shrink-0">
                        <button type="button" wire:click="closeTransferModal" class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition text-center">
                            انصراف
                        </button>

                        <button type="button"
                                wire:click="confirmTransfer"
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-indigo-500/20 transition active:scale-95 disabled:opacity-50">
                            <svg wire:loading wire:target="confirmTransfer" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="confirmTransfer">تأیید اطلاعات و ثبت نهایی سفارش</span>
                            <span wire:loading wire:target="confirmTransfer">در حال پردازش سفارش...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
