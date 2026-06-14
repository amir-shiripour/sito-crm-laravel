<div>
    <div class="mb-6 flex items-center gap-2">
        <button wire:click="setFilter('pending_review')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'pending_review' ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
            در انتظار بررسی
            <span class="inline-block px-1.5 py-0.5 mr-1 bg-white/60 dark:bg-gray-900/50 rounded-md text-[10px]">{{ $filterStatus === 'pending_review' ? $products->total() : '' }}</span>
        </button>
        <button wire:click="setFilter('published')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'published' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
            منتشر شده
        </button>
        <button wire:click="setFilter('rejected')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
            رد شده
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-gray-50 dark:bg-gray-900/80 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="p-4 w-10"></th>
                    <th class="p-4">فروشگاه</th>
                    <th class="p-4">محصول</th>
                    <th class="p-4">قیمت فعلی (تومان)</th>
                    <th class="p-4">موجودی</th>
                    <th class="p-4">وضعیت</th>
                    <th class="p-4">عملیات</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($products as $product)
                    @php
                        $master = $product->variant->masterProduct ?? null;
                        $attrs = $product->variant->variant_attributes ?? [];
                        $varName = empty($attrs) ? 'استاندارد' : implode(' | ', (array)$attrs);

                        // محاسبه درصد تخفیف
                        $discountPercent = 0;
                        if($product->discount_price && $product->price > 0) {
                            $discountPercent = round((($product->price - $product->discount_price) / $product->price) * 100);
                        }
                    @endphp

                    {{-- هر سطر حالا یک آبجکت Alpine است برای باز و بسته شدن --}}
                    <tbody x-data="{ expanded: false }" class="divide-y divide-gray-100 dark:divide-gray-700 border-transparent">

                    {{-- سطر خلاصه (مشاهده اولیه) --}}
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer group" @click="expanded = !expanded">
                        <td class="p-4 text-center">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 transform transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm border border-indigo-100 dark:border-indigo-500/20">
                                    {{ mb_substr($product->vendor->store_name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $product->vendor->store_name }}</span>
                                    <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400">{{ $product->vendor->user->mobile ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 overflow-hidden flex-shrink-0">
                                    @if($master && $master->main_image)
                                        <img src="{{ Storage::url($master->main_image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 line-clamp-1">{{ $master->title ?? 'نامشخص' }}</span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full shadow-sm {{ $product->stock > 0 ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-red-500 dark:bg-red-400' }}"></span>
                                        @forelse($attrs as $key => $val)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md text-gray-700 dark:text-gray-300 font-medium">
                                                        <span class="text-gray-400 dark:text-gray-500">{{ $key }}:</span> {{ $val }}
                                                    </span>
                                        @empty
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 px-1.5 py-0.5 bg-gray-50 dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-700">استاندارد</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                            {{ $product->discount_price ? number_format($product->discount_price) : number_format($product->price) }}
                                        </span>
                                @if($discountPercent > 0)
                                    <span class="bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20 text-[10px] font-bold px-1.5 py-0.5 rounded">
                                                {{ $discountPercent }}٪
                                            </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 text-sm font-bold {{ $product->stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $product->stock }} عدد
                        </td>
                        <td class="p-4">
                            @if($product->status == 'pending_review')
                                <span class="bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 text-[10px] px-2 py-1 rounded border border-amber-200 dark:border-amber-500/30 font-bold">در انتظار بررسی</span>
                            @elseif($product->status == 'published')
                                <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 text-[10px] px-2 py-1 rounded border border-emerald-200 dark:border-emerald-500/30 font-bold">منتشر شده</span>
                            @else
                                <span class="bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300 text-[10px] px-2 py-1 rounded border border-rose-200 dark:border-rose-500/30 font-bold">رد شده</span>
                            @endif
                        </td>
                        <td class="p-4">
                            {{-- دکمه‌های عملیات اصلی اینجا --}}
                            <div class="flex gap-2">
                                @if($product->status == 'pending_review' || $product->status == 'rejected')
                                    <button @click.stop wire:click="approve({{ $product->id }})" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-white border border-emerald-200 dark:border-emerald-500/30 rounded-lg transition-colors" title="تایید سریع">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                @endif

                                @if($product->status == 'pending_review' || $product->status == 'published')
                                    <button @click.stop wire:click="promptReject({{ $product->id }})" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500 dark:hover:text-white border border-rose-200 dark:border-rose-500/30 rounded-lg transition-colors" title="رد کردن">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- سطر جزئیات (بازشونده) - تم دارک بهبود یافته با عمق دهی --}}
                    <tr x-show="expanded">
                        <td colspan="7" class="p-0 border-t border-gray-100 dark:border-gray-700">
                            <div x-show="expanded" x-collapse>
                                <div class="p-6 bg-gray-50/80 dark:bg-gray-900/50 shadow-inner">

                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                                        {{-- باکس 1: اطلاعات مالی --}}
                                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/60 dark:border-gray-700 shadow-sm">
                                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-4 pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                اطلاعات مالی
                                            </h4>
                                            <div class="space-y-4">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">قیمت اصلی فروشنده:</span>
                                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-gray-700/50">{{ number_format($product->price) }} <span class="text-[10px] font-normal text-gray-500">تومان</span></span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">قیمت با تخفیف:</span>
                                                    <span class="text-sm font-bold {{ $product->discount_price ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20' : 'text-gray-500 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700/50' }} px-2.5 py-1 rounded-lg">{{ $product->discount_price ? number_format($product->discount_price) . ' تومان' : 'ندارد' }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">سود مشتری (تخفیف):</span>
                                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 px-2.5 py-1 rounded-lg">{{ $discountPercent }}٪</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- باکس 2: قوانین فروش و انبار --}}
                                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/60 dark:border-gray-700 shadow-sm">
                                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-4 pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                                موجودی و شرایط فروش
                                            </h4>
                                            <div class="space-y-4">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">موجودی ثبت شده:</span>
                                                    <span class="text-sm font-bold {{ $product->stock > 0 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20' : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20' }} px-2.5 py-1 rounded-lg">{{ $product->stock }} <span class="text-[10px] font-normal">عدد</span></span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">نقطه سفارش مجدد:</span>
                                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700/50 px-2.5 py-1 rounded-lg">{{ $product->reorder_point ?: 0 }} <span class="text-[10px] font-normal text-gray-500">عدد</span></span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">سبد خرید (حداقل/حداکثر):</span>
                                                    <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg font-mono">
                                                        <span>{{ $product->min_purchase_qty }}</span>
                                                        <span class="text-indigo-300 dark:text-indigo-600">/</span>
                                                        <span>{{ $product->max_purchase_qty ?: '∞' }}</span>
                                                    </div>
                                                </div>
                                                @if($product->cart_amount_step > 0 && $product->purchase_step > 0)
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">محدودیت ارزش سبد خرید:</span>
                                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 px-2.5 py-1 rounded-lg">
                                                            {{ $product->purchase_step }} عدد به ازای هر {{ number_format($product->cart_amount_step) }} تومان
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- باکس 3: مشخصات سیستمی و فنی --}}
                                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/60 dark:border-gray-700 shadow-sm">
                                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-4 pb-2 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-purple-500 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                                کدها و مشخصات کالا
                                            </h4>
                                            <div class="space-y-4">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">کد مرجع (CRM):</span>
                                                    <span class="text-xs font-mono font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700/50 px-2.5 py-1 rounded-lg">{{ $master->crm_code ?? '-' }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">کد تنوع (Variant):</span>
                                                    <span class="text-xs font-mono font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700/50 px-2.5 py-1 rounded-lg">{{ $product->variant->variant_code ?? '-' }}</span>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">ویژگی‌های این تنوع:</span>
                                                    <div class="flex flex-wrap gap-2">
                                                        @forelse($attrs as $key => $val)
                                                            <div class="flex items-center text-xs border border-gray-200 dark:border-gray-600 rounded-md overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                                                                <span class="bg-gray-50 dark:bg-gray-900 px-2 py-1.5 text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-600">{{ $key }}</span>
                                                                <span class="px-2 py-1.5 font-bold text-gray-800 dark:text-gray-200">{{ $val }}</span>
                                                            </div>
                                                        @empty
                                                            <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-md border border-gray-100 dark:border-gray-700">استاندارد (بدون ویژگی خاص)</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- پیام رد شدن با استایل دارک بهبود یافته --}}
                                    @if($product->rejection_reason)
                                        <div class="mt-4 p-3 bg-rose-50 dark:bg-rose-500/10 border-r-4 border-rose-500 dark:border-rose-500 rounded-l-lg flex gap-3 items-start">
                                            <svg class="w-5 h-5 text-rose-500 dark:text-rose-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            <div>
                                                <span class="block text-xs font-bold text-rose-800 dark:text-rose-300">دلیل رد شدن این تنوع:</span>
                                                <p class="text-sm text-gray-700 dark:text-gray-200 mt-1">{{ $product->rejection_reason }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- دکمه‌های بزرگ عملیات --}}
                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                                        @if($product->status == 'pending_review' || $product->status == 'published')
                                            <button @click.stop wire:click="promptReject({{ $product->id }})" class="px-5 py-2.5 bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 border border-gray-200 dark:border-gray-700 hover:bg-rose-50 dark:hover:bg-gray-700 hover:border-rose-200 dark:hover:border-gray-600 rounded-xl text-sm font-bold transition-all shadow-sm">
                                                رد کردن این قیمت/موجودی
                                            </button>
                                        @endif
                                        @if($product->status == 'pending_review' || $product->status == 'rejected')
                                            <button @click.stop wire:click="approve({{ $product->id }})" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-600/30 dark:shadow-none">
                                                تایید و انتشار نهایی در سایت
                                            </button>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-gray-500 dark:text-gray-400 text-sm font-bold bg-gray-50/50 dark:bg-gray-800/30">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            هیچ محصولی در وضعیت انتخابی یافت نشد.
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $products->links() }}
            </div>
        @endif

        {{-- مدال (Modal) رد محصول با بک‌گراند دارک مناسب --}}
        @if($rejectingProductId)
            <div class="absolute inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-lg overflow-hidden" @click.away="$wire.cancelReject()">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">رد کردن قیمت / تنوع</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">لطفاً دلیل رد شدن را بنویسید تا فروشنده بداند کدام بخش (موجودی، قیمت یا قوانین خرید) نیاز به اصلاح دارد.</p>
                    </div>
                    <div class="p-5">
                        <textarea wire:model="rejectionReason" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-sm focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:focus:border-rose-500 dark:text-white h-24 resize-none placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="مثال: قیمت وارد شده با عرف بازار مطابقت ندارد..."></textarea>
                        @error('rejectionReason') <span class="text-xs text-rose-500 dark:text-rose-400 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="p-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button wire:click="cancelReject" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            انصراف
                        </button>
                        <button wire:click="confirmReject" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white shadow-lg shadow-rose-600/30 dark:shadow-none transition-all">
                            ثبت نهایی و رد
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
