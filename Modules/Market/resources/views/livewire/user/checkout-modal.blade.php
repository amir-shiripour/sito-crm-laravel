@includeIf('partials.jalali-date-picker')

@if ($isFullPage)
    <div class="bg-white dark:bg-gray-800 rounded-2xl text-right shadow-xl">
        <form wire:submit.prevent="submit">
            {{-- Form Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    تکمیل اطلاعات و تسویه حساب
                </h3>
            </div>

            {{-- Form Body --}}
            <div class="p-6 space-y-6">
                @if($errors->has('submit') || $errors->has('stock'))
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 mr-2">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">
                                    @if($errors->has('stock'))
                                        خطای موجودی انبار
                                    @else
                                        خطا در ثبت سفارش
                                    @endif
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                    <p>{{ $errors->first('submit') ?: $errors->first('stock') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Customer Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام و نام خانوادگی</label>
                        <input type="text" wire:model.defer="name" id="name" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="مثال: علی محمدی">
                        @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">شماره موبایل</label>
                        <input type="text" wire:model.defer="mobile" id="mobile" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                        @error('mobile') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Location --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="province_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">استان</label>
                        <select wire:model.live="province_id" id="province_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">انتخاب کنید...</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('province_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">شهر</label>
                        <select wire:model.live="city_id" id="city_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @if(count($cities) == 0) disabled @endif>
                            <option value="">ابتدا استان را انتخاب کنید...</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">آدرس دقیق</label>
                    <textarea wire:model.defer="address" id="address" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="خیابان، کوچه، پلاک، واحد..."></textarea>
                    @error('address') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Shipping Selection --}}
                @if(!empty($shippingMethods))
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">روش ارسال مرسوله</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($shippingMethods as $method)
                                <label wire:key="full-ship-{{ $method['id'] }}" class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-indigo-300 dark:hover:border-indigo-700 {{ $selectedShippingMethodId == $method['id'] ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/20' : 'bg-gray-50/50 dark:bg-gray-900/10 border-gray-200 dark:border-gray-700' }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" wire:model.live="selectedShippingMethodId" value="{{ $method['id'] }}" class="text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700">
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
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">انتخاب بازه زمانی تحویل</label>
                            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50 rounded-xl">
                                @foreach($availableSlots as $slot)
                                    <button type="button" 
                                            wire:key="full-slot-{{ $slot['slot_id'] }}-{{ $slot['date'] }}"
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">روش پرداخت</label>
                    <div class="space-y-3">
                        @forelse($paymentGateways as $key => $details)
                            <label
                                wire:key="payment-method-{{$key}}"
                                class="relative flex items-start p-4 border rounded-lg cursor-pointer transition-all"
                                :class="{
                                    'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500': '{{ $payment_method }}' === '{{ $key }}',
                                    'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500': '{{ $payment_method }}' !== '{{ $key }}'
                                }"
                            >
                                <div class="flex items-center h-5">
                                    <input wire:model="payment_method" id="payment_method_{{ $key }}" name="payment_method" type="radio" value="{{ $key }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:bg-gray-600 dark:border-gray-500">
                                </div>
                                <div class="ml-3 mr-3 text-sm flex-grow">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $details['title'] }}</span>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ $details['description'] }}</p>
                                </div>
                                <div class="text-gray-400 dark:text-gray-500">
                                    {!! $details['icon'] !!}
                                </div>
                            </label>
                        @empty
                            <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                در حال حاضر هیچ روش پرداخت فعالی وجود ندارد.
                            </div>
                        @endforelse
                    </div>
                     @error('payment_method') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Form Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl flex items-center justify-between">
                <div class="flex flex-col text-right">
                    <div class="flex items-baseline gap-2 text-xs text-gray-500">
                        <span>هزینه محصولات:</span>
                        <span>{{ number_format($totalAmount) }} {{ $this->getCurrencyLabel() }}</span>
                        @if($shippingCost > 0)
                            <span>+ هزینه ارسال:</span>
                            <span>{{ number_format($shippingCost) }} {{ $this->getCurrencyLabel() }}</span>
                        @endif
                    </div>
                    <div class="flex items-baseline mt-1">
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400">قابل پرداخت:</span>
                        <span class="mr-2 text-lg font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($totalAmount + $shippingCost) }}</span>
                        <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">{{ $this->getCurrencyLabel() }}</span>
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
@else
    <div
        x-data="{ isOpen: @entangle('isOpen') }"
        x-show="isOpen"
        x-on:keydown.escape.window="isOpen = false"
        x-on:checkout-modal-opened.window="document.body.classList.add('overflow-y-hidden')"
        x-on:checkout-modal-closed.window="document.body.classList.remove('overflow-y-hidden')"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        style="display: none;"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            {{-- Backdrop --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity"
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
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
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
                        @if($errors->has('submit') || $errors->has('stock'))
                            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800/50">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 mr-2">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">
                                            @if($errors->has('stock'))
                                                خطای موجودی انبار
                                            @else
                                                خطا در ثبت سفارش
                                            @endif
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                            <p>{{ $errors->first('submit') ?: $errors->first('stock') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        {{-- Customer Info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام و نام خانوادگی</label>
                                <input type="text" wire:model.defer="name" id="name" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="مثال: علی محمدی">
                                @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">شماره موبایل</label>
                                <input type="text" wire:model.defer="mobile" id="mobile" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                                @error('mobile') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="province_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">استان</label>
                                <select wire:model.live="province_id" id="province_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                @error('province_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">شهر</label>
                                <select wire:model.live="city_id" id="city_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @if(count($cities) == 0) disabled @endif>
                                    <option value="">ابتدا استان را انتخاب کنید...</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @error('city_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">آدرس دقیق</label>
                            <textarea wire:model.defer="address" id="address" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="خیابان، کوچه، پلاک، واحد..."></textarea>
                            @error('address') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Shipping Selection (Modal) --}}
                        @if(!empty($shippingMethods))
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">روش ارسال مرسوله</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($shippingMethods as $method)
                                        <label wire:key="modal-ship-{{ $method['id'] }}" class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-indigo-300 dark:hover:border-indigo-700 {{ $selectedShippingMethodId == $method['id'] ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500/20' : 'bg-gray-50/50 dark:bg-gray-900/10 border-gray-200 dark:border-gray-700' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" wire:model.live="selectedShippingMethodId" value="{{ $method['id'] }}" class="text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700">
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

                            {{-- Delivery Slots (Modal) --}}
                            @if(!empty($availableSlots))
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">انتخاب بازه زمانی تحویل</label>
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">روش پرداخت</label>
                            <div class="space-y-3">
                                @forelse($paymentGateways as $key => $details)
                                    <label
                                        wire:key="payment-method-{{$key}}"
                                        class="relative flex items-start p-4 border rounded-lg cursor-pointer transition-all"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-600 ring-2 ring-indigo-500': '{{ $payment_method }}' === '{{ $key }}',
                                            'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500': '{{ $payment_method }}' !== '{{ $key }}'
                                        }"
                                    >
                                        <div class="flex items-center h-5">
                                            <input wire:model="payment_method" id="payment_method_{{ $key }}" name="payment_method" type="radio" value="{{ $key }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:bg-gray-600 dark:border-gray-500">
                                        </div>
                                        <div class="ml-3 mr-3 text-sm flex-grow">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $details['title'] }}</span>
                                            <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ $details['description'] }}</p>
                                        </div>
                                        <div class="text-gray-400 dark:text-gray-500">
                                            {!! $details['icon'] !!}
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        در حال حاضر هیچ روش پرداخت فعالی وجود ندارد.
                                    </div>
                                @endforelse
                            </div>
                             @error('payment_method') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl flex items-center justify-between">
                        <div class="flex flex-col text-right">
                            <div class="flex items-baseline gap-2 text-xs text-gray-500">
                                <span>هزینه محصولات:</span>
                                <span>{{ number_format($totalAmount) }} {{ $this->getCurrencyLabel() }}</span>
                                @if($shippingCost > 0)
                                    <span>+ هزینه ارسال:</span>
                                    <span>{{ number_format($shippingCost) }} {{ $this->getCurrencyLabel() }}</span>
                                @endif
                            </div>
                            <div class="flex items-baseline mt-1">
                                <span class="text-sm font-bold text-gray-600 dark:text-gray-400">قابل پرداخت:</span>
                                <span class="mr-2 text-lg font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($totalAmount + $shippingCost) }}</span>
                                <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">{{ $this->getCurrencyLabel() }}</span>
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
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ \Modules\Market\Entities\MarketSetting::getValue('general.currency') === 'rial' ? 'ریال' : 'تومان' }}</span>
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
                                        <div wire:key="user-bank-card-modal-slide-{{ $index }}" 
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
                                                                        @click.stop="copyText('{{ $cardNumber }}', 'user-card-{{ $index }}')"
                                                                        title="کپی شماره کارت"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'user-card-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'user-card-{{ $index }}'">
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
                                                                        @click.stop="copyText('{{ $accountNumber }}', 'user-account-{{ $index }}')"
                                                                        title="کپی شماره حساب"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'user-account-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'user-account-{{ $index }}'">
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
                                                                        @click.stop="copyText('{{ $ibanNumber }}', 'user-iban-{{ $index }}')"
                                                                        title="کپی شماره شبا"
                                                                        class="p-1.5 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                    <template x-if="copied === 'user-iban-{{ $index }}'">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    </template>
                                                                    <template x-if="copied !== 'user-iban-{{ $index }}'">
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
                                <label for="user_transfer_sender_name" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">نام واریزکننده <span class="text-rose-500">*</span></label>
                                <input type="text" id="user_transfer_sender_name" wire:model.defer="transfer_sender_name" placeholder="مثال: علی محمدی" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none">
                                @error('transfer_sender_name') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="user_transfer_mobile" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">شماره موبایل واریزکننده <span class="text-rose-500">*</span></label>
                                <input type="text" id="user_transfer_mobile" wire:model.defer="transfer_mobile" placeholder="۰۹۱۲۳۴۵۶۷۸۹" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none dir-ltr text-right">
                                @error('transfer_mobile') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="user_transfer_ref_number" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">شماره مرجع / پیگیری <span class="text-rose-500">*</span></label>
                                <input type="text" id="user_transfer_ref_number" wire:model.defer="transfer_ref_number" placeholder="مثال: ۱۲۳۴۵۶۷۸" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none dir-ltr text-right">
                                @error('transfer_ref_number') <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="user_transfer_payment_date" class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">تاریخ پرداخت (شمسی) <span class="text-rose-500">*</span></label>
                                <input type="text" id="user_transfer_payment_date" data-jdp wire:model.live="transfer_payment_date" x-on:change="$wire.set('transfer_payment_date', $event.target.value)" placeholder="۱۴۰۳/۰۵/۰۵" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none text-center dir-ltr">
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
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-indigo-500/20 transition active:scale-95 disabled:opacity-50">
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
@endif
