<div
    x-data="{ isOpen: @entangle('isOpen') }"
    x-show="isOpen"
    x-on:keydown.escape.window="isOpen = false"
    x-on:checkout-modal-opened.window="document.body.classList.add('overflow-y-hidden')"
    x-on:checkout-modal-closed.window="document.body.classList.remove('overflow-y-hidden')"
    class="fixed inset-0 z-[150] overflow-y-auto"
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

    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        {{-- Backdrop --}}
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity"
             aria-hidden="true"
             @click="isOpen = false">
        </div>

        {{-- Modal Panel --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
             @click.outside="isOpen = false">

            <form wire:submit.prevent="submit">
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
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
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
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
                            <div class="space-y-6" wire:key="group-{{ $group['id'] }}">
                                <h3 class="text-md font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">{{ $group['name'] }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    @foreach($group['fields'] as $field)
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
                                    <label wire:key="modal-ship-{{ $method['id'] }}" class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-indigo-300 dark:hover:border-indigo-700 {{ $selectedShippingMethodId == $method['id'] ? 'bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/20' : 'bg-gray-50 dark:bg-gray-800/30 border-gray-200 dark:border-gray-700' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" wire:model.live="selectedShippingMethodId" value="{{ $method['id'] }}" class="text-indigo-600 focus:ring-indigo-500 dark:bg-gray-850 dark:border-gray-700">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ $method['cost'] > 0 ? number_format($method['cost']) . ' ' . $this->getCurrencyLabel() : 'رایگان' }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-400">
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
                        @error('payment_method') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl flex items-center justify-between">
                    <div class="flex flex-col text-right">
                        <div class="flex items-baseline gap-2 text-xs text-gray-500">
                            <span>هزینه محصولات:</span>
                            <span>{{ number_format($totalAmount) }} تومان</span>
                            @if($shippingCost > 0)
                                <span>+ هزینه ارسال:</span>
                                <span>{{ number_format($shippingCost) }} تومان</span>
                            @endif
                        </div>
                        <div class="flex items-baseline mt-1">
                            <span class="text-sm font-bold text-gray-600 dark:text-gray-400">قابل پرداخت:</span>
                            <span class="mr-2 text-lg font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($totalAmount + $shippingCost) }}</span>
                            <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">تومان</span>
                        </div>
                    </div>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:ring-offset-gray-800 transition-colors disabled:opacity-50">
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
    </div>
</div>
