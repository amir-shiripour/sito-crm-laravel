@php
    $inputClass = "w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white transition-all";
    $labelClass = "block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1";

    // رنگ‌ها و متن‌های وضعیت محصول
    $statusColors = [
        'published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        'pending_review' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700',
        'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800',
    ];
    $statusLabels = [
        'published' => 'تایید و منتشر شده',
        'pending_review' => 'در انتظار بررسی',
        'draft' => 'پیش‌نویس (غیرفعال)',
        'rejected' => 'رد شده',
    ];
@endphp

<div class="space-y-4">
    <div class="flex justify-end mb-4">
        <a href="{{ route('user.market.vendor.products.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            افزودن کالای جدید
        </a>
    </div>

    @forelse($masters as $master)
        <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300">

            {{-- ردیف اصلی محصول --}}
            <div @click="expanded = !expanded" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700">
                        @if($master->main_image)
                            <img src="{{ Storage::url($master->main_image) }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $master->title }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-mono bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded text-gray-500 dark:text-gray-400">{{ $master->crm_code }}</span>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                                {{ $master->variants->count() }} تنوع
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pr-16 sm:pr-0">
                    <a href="{{ route('user.market.vendor.products.create', ['master_id' => $master->id]) }}" @click.stop class="text-xs text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg font-bold transition-colors border border-indigo-100 dark:border-indigo-800">
                        + ویرایش/افزودن تنوع
                    </a>
                    <div class="p-1 text-gray-400 dark:text-gray-500 transform transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>

            {{-- لیست تنوع‌ها --}}
            <div x-show="expanded" x-collapse class="bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700">
                <div class="p-4 space-y-3">
                    @foreach($master->variants as $variant)
                        @php
                            $vp = $variant->vendorProducts->first();
                            if(!$vp) continue; // در صورت نداشتن دیتای فروشنده (نباید اتفاق بیفته طبق کدهای قبلی ولی برای امنیت بیشتر)

                            $attrs = $variant->variant_attributes ?? [];
                            $varName = empty($attrs) ? 'استاندارد' : implode(' | ', (array)$attrs);
                        @endphp

                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-3 rounded-xl shadow-sm transition-all">

                            @if($editingId !== $vp->id)
                                {{-- 💡 UX بازطراحی شده: جدا کردن قیمت‌ها و نمایش بسیار واضح و حرفه‌ای --}}
                                <div class="flex flex-col xl:flex-row items-center justify-between gap-4">
                                    <div class="flex-1 w-full xl:w-1/4 flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ $vp->stock > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $varName }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">{{ $variant->variant_code }}</span>
                                                {{-- 💡 نمایش وضعیت محصول --}}
                                                <span class="text-[9px] px-1.5 py-0.5 rounded border font-bold {{ $statusColors[$vp->status] ?? $statusColors['draft'] }}">
                                                    {{ $statusLabels[$vp->status] ?? 'نامشخص' }}
                                                </span>
                                                @if($vp->status == 'rejected' && $vp->rejection_reason)
                                                    <p class="text-[10px] text-rose-500 mt-2 bg-rose-50 dark:bg-rose-900/20 p-2 rounded border border-rose-100 dark:border-rose-800 w-full">
                                                        <span class="font-bold">دلیل رد:</span> {{ $vp->rejection_reason }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex-1 w-full xl:w-3/4 grid grid-cols-2 md:grid-cols-5 gap-4 items-center">

                                        <div class="flex flex-col border-r border-gray-100 dark:border-gray-700 pr-3">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">قیمت اصلی</span>
                                            <span class="font-bold text-gray-600 dark:text-gray-300 text-sm">{{ number_format($vp->price) }} <span class="font-normal text-[10px]">تومان</span></span>
                                        </div>

                                        <div class="flex flex-col border-r border-gray-100 dark:border-gray-700 pr-3">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">قیمت فروش (تخفیف)</span>
                                            <span class="font-bold text-rose-500 dark:text-rose-400 text-sm">{{ $vp->discount_price ? number_format($vp->discount_price) : '-' }}</span>
                                        </div>

                                        <div class="flex flex-col border-r border-gray-100 dark:border-gray-700 pr-3">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">موجودی انبار</span>
                                            <span class="font-bold {{ $vp->stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }} text-sm">{{ $vp->stock }} عدد</span>
                                        </div>

                                        <div class="flex flex-col border-r border-gray-100 dark:border-gray-700 pr-3">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">خرید (حداقل - حداکثر)</span>
                                            <span class="font-bold text-gray-600 dark:text-gray-300 text-sm">{{ $vp->min_purchase_qty }} - {{ $vp->max_purchase_qty ?: 'نامحدود' }}</span>
                                            @if($vp->cart_amount_step > 0 && $vp->purchase_step > 0)
                                                <span class="text-[9px] text-amber-600 dark:text-amber-400 mt-1 font-semibold block" title="محدودیت بر اساس مبلغ سبد خرید">
                                                    به ازای هر {{ number_format($vp->cart_amount_step) }} تومان: {{ $vp->purchase_step }} عدد
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-end gap-2 pr-3">
                                            <button wire:click="edit({{ $vp->id }})" class="p-1.5 bg-gray-50 dark:bg-gray-900 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 rounded-lg transition-colors border border-gray-200 dark:border-gray-700" title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="delete({{ $vp->id }})" onclick="confirm('حذف این تنوع از انبار؟') || event.stopImmediatePropagation()" class="p-1.5 bg-gray-50 dark:bg-gray-900 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-lg transition-colors border border-gray-200 dark:border-gray-700" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- ویرایش در لحظه (Inline Edit) --}}
                                <div class="bg-indigo-50/30 dark:bg-indigo-900/20 p-3 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                    <div class="mb-3 border-b border-indigo-100 dark:border-indigo-800 pb-2">
                                        <span class="text-sm font-bold text-indigo-900 dark:text-indigo-300">ویرایش سریع: {{ $varName }}</span>
                                    </div>

                                    {{-- 💡 آپدیت: محاسبه درصد و مدیریت زمان تخفیف در ویرایش سریع --}}
                                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                                        <div class="col-span-2 lg:col-span-1" x-data="{
                                            raw: @entangle('editForm.price'),
                                            formatted: '',
                                            init() { this.format(this.raw); this.$watch('raw', val => this.format(val)); },
                                            format(val) { this.formatted = val ? val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') : ''; },
                                            update() { this.raw = this.formatted.replace(/,/g, ''); }
                                        }">
                                            <label class="{{ $labelClass }}">قیمت (تومان)</label>
                                            <input type="text" x-model="formatted" @input="update()" class="{{ $inputClass }} font-mono dir-ltr text-center font-bold text-indigo-700 dark:text-indigo-400">
                                            @error('editForm.price') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="col-span-2 lg:col-span-3 grid grid-cols-2 gap-2 bg-rose-50/50 dark:bg-rose-900/10 p-2 rounded-xl border border-rose-100 dark:border-rose-800" x-data="{
                                            rawPrice: @entangle('editForm.price'),
                                            rawDiscount: @entangle('editForm.discount_price'),
                                            formattedDiscount: '',
                                            percent: '',
                                            init() {
                                                this.formatDiscount(this.rawDiscount);
                                                this.calcPercent();
                                                this.$watch('rawDiscount', val => {
                                                    this.formatDiscount(val);
                                                    this.calcPercent();
                                                 });
                                                this.$watch('rawPrice', () => this.calcPercent());
                                            },
                                            formatDiscount(val) {
                                                this.formattedDiscount = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                            },
                                            updateFromPrice() {
                                                this.rawDiscount = this.formattedDiscount.replace(/,/g, '');
                                                this.calcPercent();
                                            },
                                            updateFromPercent() {
                                                let p = parseFloat(this.percent);
                                                let pr = parseFloat(this.rawPrice);
                                                if (p > 0 && p <= 100 && pr > 0) {
                                                    let d = pr - (pr * (p / 100));
                                                    this.rawDiscount = d.toString();
                                                } else {
                                                    this.rawDiscount = '';
                                                }
                                            },
                                            calcPercent() {
                                                let pr = parseFloat(this.rawPrice);
                                                let d = parseFloat(this.rawDiscount);
                                                if (pr > 0 && d > 0 && d < pr) {
                                                    this.percent = Math.round(((pr - d) / pr) * 100);
                                                } else {
                                                    this.percent = '';
                                                }
                                            }
                                        }">
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">درصد تخفیف</label>
                                                <div class="relative">
                                                    <input type="number" x-model="percent" @input="updateFromPercent()" class="{{ $inputClass }} dir-ltr text-center !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="%">
                                                    <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-rose-400">٪</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-600 dark:!text-rose-400">قیمت با تخفیف</label>
                                                <input type="text" x-model="formattedDiscount" @input="updateFromPrice()" class="{{ $inputClass }} font-mono dir-ltr text-center text-rose-600 dark:text-rose-400 !border-rose-200 focus:!border-rose-500 focus:!ring-rose-500/20 dark:!border-rose-800" placeholder="بدون تخفیف">
                                            </div>
                                        </div>

                                        <div class="col-span-2 lg:col-span-1">
                                            <label class="{{ $labelClass }}">موجودی</label>
                                            <input type="number" wire:model="editForm.stock" class="{{ $inputClass }} text-center font-bold">
                                        </div>
                                    </div>

                                    {{-- محدودیت‌های خرید --}}
                                    <div class="mt-3 bg-gray-50/50 dark:bg-gray-900/10 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <div class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-2 border-b border-gray-100 dark:border-gray-700/50 pb-1">تنظیمات محدودیت خرید</div>
                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div>
                                                <label class="{{ $labelClass }}">حداقل سفارش</label>
                                                <input type="number" wire:model="editForm.min_purchase_qty" class="{{ $inputClass }} text-center">
                                                @error('editForm.min_purchase_qty') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">حداکثر سفارش</label>
                                                <input type="number" wire:model="editForm.max_purchase_qty" class="{{ $inputClass }} text-center" placeholder="نامحدود">
                                                @error('editForm.max_purchase_qty') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div x-data="{
                                                rawAmount: @entangle('editForm.cart_amount_step'),
                                                formattedAmount: '',
                                                init() {
                                                    this.formatAmount(this.rawAmount);
                                                    this.$watch('rawAmount', val => this.formatAmount(val));
                                                },
                                                formatAmount(val) {
                                                    this.formattedAmount = val ? val.toString().replace(/,/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
                                                },
                                                updateAmount() {
                                                    this.rawAmount = this.formattedAmount.replace(/,/g, '');
                                                }
                                            }">
                                                <label class="{{ $labelClass }}">مبنای مبلغ سبد خرید</label>
                                                <div class="relative">
                                                    <input type="text" x-model="formattedAmount" @input="updateAmount()" class="{{ $inputClass }} font-mono dir-ltr text-center" placeholder="مثلا 1,000,000">
                                                    <span class="absolute inset-y-0 right-3 flex items-center text-[9px] text-gray-400">تومان سبد</span>
                                                </div>
                                                @error('editForm.cart_amount_step') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">تعداد مجاز به ازای مبنا</label>
                                                <div class="relative">
                                                    <input type="number" wire:model="editForm.purchase_step" class="{{ $inputClass }} text-center" placeholder="مثلا 1">
                                                    <span class="absolute inset-y-0 right-3 flex items-center text-[9px] text-gray-400">عدد کالا</span>
                                                </div>
                                                @error('editForm.purchase_step') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- تنظیمات پیشرفته زمان و موجودی تخفیف در ویرایش سریع --}}
                                    <div x-data="{ hasDiscount: @entangle('editForm.discount_price') }" x-show="hasDiscount" x-collapse>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3 bg-rose-50/30 dark:bg-rose-900/5 p-3 rounded-xl border border-rose-100 dark:border-rose-800/50" wire:ignore>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">شروع تخفیف</label>
                                                <input type="text" data-jdp-with-time wire:model.defer="editForm.discount_start_date" class="{{ $inputClass }} !border-rose-200 dark:!border-rose-800">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">پایان تخفیف</label>
                                                <input type="text" data-jdp-with-time wire:model.defer="editForm.discount_end_date" class="{{ $inputClass }} !border-rose-200 dark:!border-rose-800">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">موجودی تخفیف</label>
                                                <input type="number" wire:model.defer="editForm.discount_stock" class="{{ $inputClass }} !border-rose-200 dark:!border-rose-800" placeholder="همه موجودی">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }} !text-rose-700 dark:!text-rose-400">محدودیت خرید</label>
                                                <input type="number" wire:model.defer="editForm.max_discount_purchase_qty" class="{{ $inputClass }} !border-rose-200 dark:!border-rose-800" placeholder="نامحدود">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-3 border-t border-indigo-100 dark:border-indigo-800 gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <input type="checkbox" wire:model="editForm.is_active" class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600">
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">درخواست انتشار محصول</span>
                                        </label>
                                        <div class="flex gap-2 w-full sm:w-auto">
                                            <button wire:click="cancelEdit" class="flex-1 sm:flex-none px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition-colors">انصراف</button>
                                            <button wire:click="saveEdit" class="flex-1 sm:flex-none px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">ذخیره و ارسال بررسی</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-base font-bold text-gray-700 dark:text-gray-300 mb-4">انبار شما خالی است!</p>
        </div>
    @endforelse

    @if($masters->hasPages())
        <div class="mt-4">{{ $masters->links() }}</div>
    @endif

    {{-- 💡 FIX: include به داخل div اصلی منتقل شد --}}
    @includeIf('partials.jalali-date-picker')
</div>
