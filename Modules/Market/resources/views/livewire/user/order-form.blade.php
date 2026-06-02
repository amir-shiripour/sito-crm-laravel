<div class="space-y-6">
    <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-right" dir="rtl">

        {{-- Left Section: Items, Customer Selection, Dynamic Metadata --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Client Selection Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        انتخاب مشتری
                    </h3>
                    <button type="button" wire:click="$set('showQuickClientModal', true)" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        + مشتری جدید سریع
                    </button>
                </div>

                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchClient" wire:input="searchClients"
                           placeholder="جستجوی مشتری بر اساس نام، موبایل یا ایمیل..."
                           class="w-full px-4 py-3 rounded-2xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">

                    @if($clientsList->isNotEmpty() && $searchClient !== '')
                        <div class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($clientsList as $c)
                                <button type="button" wire:click="$set('clientId', {{ $c->id }}); $set('searchClient', '{{ addslashes($c->full_name) }}'); $set('clientsList', [])"
                                        class="w-full text-right px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors flex justify-between items-center text-sm">
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $c->full_name }}</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 mr-2">({{ $c->phone }})</span>
                                    </div>
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($clientId)
                    @php
                        $selectedClient = \Modules\Clients\Entities\Client::find($clientId);
                    @endphp
                    @if($selectedClient)
                        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 flex flex-col md:flex-row justify-between gap-4 text-sm">
                            <div class="space-y-1">
                                <p class="text-gray-900 dark:text-white"><span class="text-gray-400 font-medium">خریدار:</span> <strong class="font-extrabold">{{ $selectedClient->full_name }}</strong></p>
                                <p class="text-gray-600 dark:text-gray-300"><span class="text-gray-400 font-medium">شماره موبایل:</span> <span class="font-mono">{{ $selectedClient->phone }}</span></p>
                            </div>
                            <div class="space-y-1 text-md-left">
                                <p class="text-gray-600 dark:text-gray-300"><span class="text-gray-400 font-medium">ایمیل:</span> {{ $selectedClient->email ?: 'ثبت نشده' }}</p>
                                <p class="text-gray-600 dark:text-gray-300"><span class="text-gray-400 font-medium">کد ملی:</span> <span class="font-mono">{{ $selectedClient->national_code ?: 'ثبت نشده' }}</span></p>
                            </div>
                        </div>
                    @endif
                @endif
                @error('clientId') <span class="text-xs text-rose-600 font-bold block">{{ $message }}</span> @enderror
            </div>

            {{-- Checkout Form Template & Dynamic Fields Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    قالب و داده‌های فرم تسویه حساب
                </h3>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">قالب فرم تسویه حساب</label>
                    <select wire:model.live="formId" class="w-full px-4 py-3 rounded-2xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                        <option value="">-- انتخاب قالب فرم --</option>
                        @foreach($checkoutFormsList as $f)
                            <option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
                        @endforeach
                    </select>
                    @error('formId') <span class="text-xs text-rose-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                @if(!empty($checkoutFields))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                        @foreach($checkoutFields as $field)
                            @php
                                $fieldId = $field['id'];
                                $label = $field['label'] ?? $field['name'] ?? $fieldId;
                                $required = !empty($field['required']);
                                $type = $field['type'] ?? 'text';
                            @endphp
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">
                                    {{ $label }}
                                    @if($required) <span class="text-rose-500">*</span> @endif
                                </label>

                                @if($type === 'select-province-city')
                                    <div class="grid grid-cols-2 gap-2">
                                        <select wire:model.live="shipping_address.province" class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                                            <option value="">استان</option>
                                            @foreach($provinces as $p)
                                                <option value="{{ $p }}">{{ $p }}</option>
                                            @endforeach
                                        </select>
                                        <select wire:model.live="shipping_address.city" class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                                            <option value="">شهر</option>
                                            @foreach($cities as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($type === 'textarea')
                                    <textarea wire:model.defer="formData.{{ $fieldId }}" rows="2" class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                @else
                                    <input type="{{ $type }}" wire:model.defer="formData.{{ $fieldId }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                @endif

                                @error('formData.' . $fieldId) <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Products & Items Selector Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    محصولات سفارش
                </h3>

                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchProduct" wire:input="searchProducts"
                           placeholder="جستجوی محصول جهت افزودن به سفارش..."
                           class="w-full px-4 py-3 rounded-2xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">

                    @if(!empty($productsList) && $searchProduct !== '')
                        <div class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($productsList as $p)
                                <button type="button" wire:click="addItem({{ $p['id'] }}); $set('searchProduct', ''); $set('productsList', [])"
                                        class="w-full text-right px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors flex justify-between items-center text-sm">
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $p['title'] }}</span>
                                        @if(!empty($p['variant_name']))
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 mr-2 font-medium">تنوع: {{ $p['variant_name'] }} (کد: {{ $p['variant_code'] }})</span>
                                        @endif
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                                            فروشنده: {{ $p['vendor_name'] }} | موجودی: {{ $p['stock'] }} عدد
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($p['price']) }} تومان</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($items))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            <thead>
                                <tr class="text-gray-400 text-xs">
                                    <th class="py-2 text-right">عنوان محصول</th>
                                    <th class="py-2 text-center">قیمت واحد</th>
                                    <th class="py-2 text-center">تعداد</th>
                                    <th class="py-2 text-center">قیمت کل</th>
                                    <th class="py-2 text-left">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($items as $idx => $item)
                                    <tr class="text-gray-900 dark:text-white">
                                        <td class="py-3 font-semibold text-xs">
                                            <div>{{ $item['title'] }}</div>
                                            @if(!empty($item['variant_name']))
                                                <div class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5 font-medium">تنوع: {{ $item['variant_name'] }} (کد: {{ $item['variant_code'] }})</div>
                                            @endif
                                            @if(!empty($item['vendor_name']))
                                                <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">فروشنده: {{ $item['vendor_name'] }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center font-mono text-xs">{{ number_format($item['price']) }}</td>
                                        <td class="py-3 text-center">
                                            <div class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-1">
                                                <button type="button" wire:click="incrementItem({{ $idx }})" class="p-0.5 text-gray-500 hover:text-indigo-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                                <span class="px-2 font-bold text-xs">{{ $item['quantity'] }}</span>
                                                <button type="button" wire:click="decrementItem({{ $idx }})" class="p-0.5 text-gray-500 hover:text-indigo-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($item['price'] * $item['quantity']) }}
                                        </td>
                                        <td class="py-3 text-left">
                                            <button type="button" wire:click="removeItem({{ $idx }})" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-gray-400 dark:text-gray-500 text-xs">
                        هیچ محصولی به سفارش اضافه نشده است.
                    </div>
                @endif
                @error('items') <span class="text-xs text-rose-600 font-bold block">{{ $message }}</span> @enderror
            </div>

            {{-- Shipping Address Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    مشخصات تحویل و آدرس گیرنده
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">استان <span class="text-rose-500">*</span></label>
                        <select wire:model.live="shipping_address.province" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                            <option value="">-- انتخاب استان --</option>
                            @foreach($provinces as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('shipping_address.province') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">شهر <span class="text-rose-500">*</span></label>
                        <select wire:model.defer="shipping_address.city" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                            <option value="">-- انتخاب شهر --</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                        @error('shipping_address.city') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">آدرس دقیق پستی</label>
                        <textarea wire:model.defer="shipping_address.address" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        @error('shipping_address.address') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">نام گیرنده (در صورت تفاوت با خریدار)</label>
                        <input type="text" wire:model.defer="shipping_address.recipient_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        @error('shipping_address.recipient_name') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">موبایل گیرنده</label>
                        <input type="text" wire:model.defer="shipping_address.recipient_mobile" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                        @error('shipping_address.recipient_mobile') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Section: Totals, Actions, Gateway and Status Configuration --}}
        <div class="space-y-6">

            {{-- Summary & Totals Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">خلاصه مالی فاکتور</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">مجموع اقلام:</span>
                        <span class="font-bold text-gray-900 dark:text-white font-mono">{{ number_format($this->subtotal) }} تومان</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 dark:border-gray-700/50 pt-3 text-base font-extrabold text-gray-900 dark:text-white">
                        <span>مبلغ کل فاکتور:</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-mono">{{ number_format($this->subtotal) }} تومان</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-md transition-all hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        {{ $isEdit ? 'به‌روزرسانی و ثبت سفارش' : 'ثبت و ذخیره نهایی سفارش' }}
                    </button>
                    <a href="{{ route('user.market.orders.index') }}" class="w-full block text-center mt-3 py-3 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-2xl transition-colors hover:bg-gray-200 dark:hover:bg-gray-600">
                        انصراف و بازگشت
                    </a>
                </div>
            </div>

            {{-- Settings & Gateway Settings --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">تنظیمات پرداخت و ارسال</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">روش پرداخت</label>
                        <select wire:model.defer="payment_method" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                            <option value="zibal">زیبال (Zibal)</option>
                            <option value="zarinpal">زرین‌پال (Zarinpal)</option>
                            <option value="pos">پرداخت در محل (کارتخوان)</option>
                            <option value="transfer">کارت به کارت / واریز فیش</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">وضعیت پرداخت</label>
                        <select wire:model.defer="payment_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                            <option value="unpaid">در انتظار پرداخت (Unpaid)</option>
                            <option value="paid">پرداخت شده (Paid)</option>
                            <option value="failed">پرداخت ناموفق (Failed)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">وضعیت تحویل</label>
                        <select wire:model.defer="delivery_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs">
                            <option value="processing">در حال پردازش (Processing)</option>
                            <option value="shipped">ارسال شده (Shipped)</option>
                            <option value="delivered">تحویل داده شده (Delivered)</option>
                            <option value="canceled">لغوشده (Canceled)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Inline Quick Customer Creation Modal --}}
    @if($showQuickClientModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/80 backdrop-blur-sm transition-all duration-300" dir="rtl">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 max-w-md w-full overflow-hidden text-right animate-in fade-in zoom-in duration-200">
                <div class="bg-indigo-600 p-6 text-white flex justify-between items-center">
                    <h3 class="font-extrabold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        ثبت سریع مشتری جدید
                    </h3>
                    <button type="button" wire:click="$set('showQuickClientModal', false)" class="text-white hover:text-indigo-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createQuickClient" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="newClient.full_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        @error('newClient.full_name') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">شماره موبایل <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="newClient.phone" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                        @error('newClient.phone') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">ایمیل</label>
                        <input type="email" wire:model.defer="newClient.email" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                        @error('newClient.email') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400">کد ملی</label>
                        <input type="text" wire:model.defer="newClient.national_code" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                        @error('newClient.national_code') <span class="text-xs text-rose-600 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" wire:click="$set('showQuickClientModal', false)" class="py-2 px-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-xl text-xs">
                            انصراف
                        </button>
                        <button type="submit" class="py-2 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md">
                            ثبت مشتری
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
