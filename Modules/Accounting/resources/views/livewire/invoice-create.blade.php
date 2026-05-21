@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

<form wire:submit.prevent="save">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

        {{-- کارت ۱: اطلاعات اصلی فاکتور --}}
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات اصلی صورت‌حساب</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">انتخاب مشتری، شماره و تاریخ‌ها</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-3" wire:ignore>
                    <label for="client_id" class="{{ $labelClass }}">مشتری</label>
                    <select id="client_id">
                        <option value="">انتخاب کنید...</option>
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="invoice_number" class="{{ $labelClass }}">شماره فاکتور</label>
                    <input type="text" id="invoice_number" wire:model.lazy="invoice_number" class="{{ $inputClass }} dir-ltr text-left" @if($isNumberingLocked) disabled @endif>
                    @error('invoice_number') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور</label>
                    <input type="text" id="issue_date" data-jdp wire:model.lazy="issue_date" class="{{ $inputClass }} dir-ltr text-center" @change="$wire.set('issue_date', $el.value)">
                    @error('issue_date') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="{{ $labelClass }}">تاریخ سررسید</label>
                    <input type="text" id="due_date" data-jdp wire:model.lazy="due_date" class="{{ $inputClass }} dir-ltr text-center" @change="$wire.set('due_date', $el.value)">
                    @error('due_date') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- کارت ۲: ردیف‌های فاکتور --}}
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                </div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">اقلام صورت‌حساب</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-12 gap-4 items-start" wire:key="item-{{ $index }}">
                            <div class="col-span-12 md:col-span-5">
                                <label for="item-desc-{{$index}}" class="{{ $labelClass }}">شرح کالا یا خدمات</label>
                                <input type="text" id="item-desc-{{$index}}" wire:model.lazy="items.{{$index}}.description" class="{{ $inputClass }}" placeholder="مثال: طراحی وبسایت">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label for="item-qty-{{$index}}" class="{{ $labelClass }}">تعداد/مقدار</label>
                                <input type="text" id="item-qty-{{$index}}" wire:model.lazy="items.{{$index}}.quantity" class="{{ $inputClass }} dir-ltr text-left" @input="$el.value = toEnglishDigits($el.value)">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label for="item-unit-{{$index}}" class="{{ $labelClass }}">واحد</label>
                                <select id="item-unit-{{$index}}" wire:model.lazy="items.{{$index}}.unit" class="{{ $selectClass }}">
                                    @foreach($availableUnits as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label for="item-price-{{$index}}" class="{{ $labelClass }}">مبلغ واحد</label>
                                <input type="text" id="item-price-{{$index}}" class="{{ $inputClass }} dir-ltr text-left"
                                       x-data="{ value: @entangle('items.'.$index.'.unit_price') }"
                                       x-init="$el.value = formatNumber(value)"
                                       @input="value = unformatNumber(toEnglishDigits($event.target.value))"
                                       @blur="$el.value = formatNumber(value)">
                            </div>
                            <div class="col-span-12 md:col-span-1 flex items-end justify-end h-full">
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    <button type="button" wire:click="addItem" class="inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        افزودن ردیف جدید
                    </button>
                </div>
            </div>
        </div>

        {{-- کارت ۳: خلاصه مالی و یادداشت --}}
        <div class="{{ $cardClass }}">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label for="discount" class="{{ $labelClass }}">تخفیف</label>
                        <input type="text" id="discount" class="{{ $inputClass }} dir-ltr text-left"
                               x-data="{ value: @entangle('discount') }"
                               x-init="$el.value = formatNumber(value)"
                               @input="value = unformatNumber(toEnglishDigits($event.target.value))"
                               @blur="$el.value = formatNumber(value)">
                        @error('discount') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="tax_rate" class="{{ $labelClass }}">مالیات</label>
                        <div class="relative">
                            <input type="text" id="tax_rate" wire:model.lazy="tax_rate" class="{{ $inputClass }} dir-ltr text-left" @if($isTaxLocked) disabled @endif @input="$el.value = toEnglishDigits($el.value)">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        @error('tax_rate') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label for="notes" class="{{ $labelClass }}">یادداشت‌ها</label>
                    <textarea id="notes" wire:model.lazy="notes" rows="5" class="{{ $inputClass }}" placeholder="توضیحات اضافی یا شرایط پرداخت..."></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- کارت ۴: خلاصه مالی --}}
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">خلاصه مالی</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">جمع کل اقلام:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr">{{ number_format($this->subtotal()) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">تخفیف:</span>
                    <span class="font-bold text-red-600 dark:text-red-400 dir-ltr">- {{ number_format((float)$this->discount) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">مالیات ({{ $this->tax_rate }}%):</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 dir-ltr">+ {{ number_format($this->taxAmount()) }}</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center text-lg">
                    <span class="font-bold text-gray-900 dark:text-white">مبلغ نهایی:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr">{{ number_format($this->grandTotal()) }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- نوار شناور ذخیره --}}
    <div class="sticky bottom-4 z-40 max-w-5xl mx-auto">
        <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                ذخیره و صدور صورت‌حساب
                <div wire:loading wire:target="save" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            </button>
            <div class="min-h-[2rem] flex items-center">
                <div x-data="{ show: false, text: '' }"
                     @notify.window="show = true; text = $event.detail[0]?.text || 'صورت‌حساب با موفقیت ثبت شد.'; setTimeout(() => show = false, 3000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="text-sm font-medium text-green-600"
                     style="display: none;">
                    <p x-text="text"></p>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    @includeIf('partials.jalali-date-picker')
    {{-- TomSelect CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    {{-- TomSelect JS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    @php
        $tomSelectOptions = $clients->map(function($client) {
            return [
                'id' => $client->id,
                'text' => ($client->full_name ?: $client->username) . ' (' . ($client->national_code ?? 'N/A') . ')',
                'national_code' => $client->national_code,
            ];
        })->values();
    @endphp

    <script>
        function toEnglishDigits(str) {
            if (!str) return '';
            return String(str)
                .replace(/[\u0660-\u0669]/g, c => c.charCodeAt(0) - 0x0660)
                .replace(/[\u06F0-\u06F9]/g, c => c.charCodeAt(0) - 0x06F0);
        }

        function formatNumber(value) {
            if (!value) return '';
            let num = String(value).replace(/[^0-9.]/g, '');
            let parts = num.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }

        function unformatNumber(value) {
            if (!value) return '';
            return String(value).replace(/,/g, '');
        }

        document.addEventListener('livewire:navigated', () => {
            // Initialize TomSelect for client_id
            let tomSelect = new TomSelect('#client_id', {
                sortField: { field: "text", direction: "asc" },
                valueField: 'id',
                labelField: 'text',
                searchField: ['text', 'national_code'],
                options: @json($tomSelectOptions),
                onChange: (value) => {
                    @this.set('client_id', value);
                }
            });
        });
    </script>
@endpush
