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
                        <select wire:model.defer="city_id" id="city_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @if(count($cities) == 0) disabled @endif>
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
                <div class="flex items-baseline">
                    <span class="text-sm text-gray-600 dark:text-gray-400">مبلغ کل:</span>
                    <span class="mr-2 text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalAmount) }}</span>
                    <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">تومان</span>
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
                                <select wire:model.defer="city_id" id="city_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @if(count($cities) == 0) disabled @endif>
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
                        <div class="flex items-baseline">
                            <span class="text-sm text-gray-600 dark:text-gray-400">مبلغ کل:</span>
                            <span class="mr-2 text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalAmount) }}</span>
                            <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">تومان</span>
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
@endif
