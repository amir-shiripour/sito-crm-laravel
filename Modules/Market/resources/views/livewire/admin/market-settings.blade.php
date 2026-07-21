@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center gap-3";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
    $isSuperAdmin = auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('admin');
@endphp

<div x-data="{ tab: '{{ $isSuperAdmin ? 'system' : 'general' }}' }" class="space-y-6 pb-24">

    {{-- منوی تب‌ها --}}
    <div class="flex items-center gap-2 overflow-x-auto bg-white dark:bg-gray-800 p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm scrollbar-hide">

        @if($isSuperAdmin)
            <button @click="tab = 'system'" :class="tab === 'system' ? 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                مدیریت کلان (موتور سیستم)
            </button>
        @endif

        <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            عمومی و کاتالوگ
        </button>

        <button @click="tab = 'ui'" :class="tab === 'ui' ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            تنظیمات نمایشی (UI)
        </button>

        @if($store_type === 'multi' || auth()->user()->can('market.vendors.manage') || auth()->user()->can('market.vendors.view'))
            <button @click="tab = 'vendors'" :class="tab === 'vendors' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="animate-in fade-in flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                سیاست فروشندگان
            </button>
        @endif

        <button @click="tab = 'tax'" :class="tab === 'tax' ? 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" /></svg>
            مالیات (Taxes)
        </button>

        <button @click="tab = 'orders'" :class="tab === 'orders' ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            سفارشات و پرداخت
        </button>

        <button @click="tab = 'checkout'" :class="tab === 'checkout' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
            فرم تسویه حساب
        </button>

        <button @click="tab = 'finance'" :class="tab === 'finance' ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            مالی و تسویه حساب
        </button>

        <button @click="tab = 'map'" :class="tab === 'map' ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-400 font-medium'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-all whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            تنظیمات نقشه
        </button>

    </div>

    {{-- محتوای تب‌ها --}}
    <div class="grid grid-cols-1 gap-6">

        {{-- 0. تب سیستم (Super Admin Only) --}}
        @if($isSuperAdmin)
            <div x-show="tab === 'system'" x-cloak class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات هسته مارکت (Core Engine)</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">معماری، ماژول‌های انبارداری و بازاریابی را پیکربندی کنید.</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">

                    {{-- نوع نمایش و معماری فروشگاه --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- نوع فروشگاه --}}
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 p-5 rounded-2xl">
                            <label class="{{ $labelClass }} text-indigo-900 dark:text-indigo-300 mb-3">معماری فروشگاه (Store Architecture)</label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="flex items-center gap-3 cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex-1 hover:border-indigo-500 transition-colors">
                                    <input type="radio" wire:model.live="store_type" value="single" class="text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-bold dark:text-white">تک فروشگاهی (Single Vendor)</span>
                                        <span class="block text-[11px] text-gray-500 mt-1">فروشگاه اختصاصی شما. پنل فروشندگان و احراز هویت‌ها غیرفعال می‌شود.</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex-1 hover:border-indigo-500 transition-colors">
                                    <input type="radio" wire:model.live="store_type" value="multi" class="text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-bold dark:text-white">مارکت‌پلیس (Multi Vendor)</span>
                                        <span class="block text-[11px] text-gray-500 mt-1">مدیریت ده‌ها فروشنده، احراز هویت (KYC)، ثبت‌نام و سیستم کمیسیون.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- نوع نمایش فروشگاه --}}
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 p-5 rounded-2xl">
                            <label class="{{ $labelClass }} text-emerald-900 dark:text-emerald-300 mb-3">نوع نمایش فروشگاه (Store Display Type)</label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="flex items-center gap-3 cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex-1 hover:border-emerald-500 transition-colors">
                                    <input type="radio" wire:model.defer="store_display_type" value="by_vendor" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-sm font-bold dark:text-white">بر اساس فروشگاه/فروشندگان</span>
                                        <span class="block text-[11px] text-gray-500 mt-1">نمایش صفحه اول و لیست‌ها بر اساس فروشگاه‌ها (مانند اسنپ‌فود)</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex-1 hover:border-emerald-500 transition-colors">
                                    <input type="radio" wire:model.defer="store_display_type" value="by_product" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-sm font-bold dark:text-white">بر اساس محصول</span>
                                        <span class="block text-[11px] text-gray-500 mt-1">نمایش مستقیم محصولات در فروشگاه (مانند دیجی‌کالا)</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- ماژول‌های عملیاتی --}}
                        <div class="space-y-4 border border-gray-100 dark:border-gray-700 p-5 rounded-2xl bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">عملیات و لجستیک</h3>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.live="wms_enabled" class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">فعال‌سازی سیستم انبارداری (WMS) مجزا</span>
                                    <span class="text-[10px] text-gray-500 mt-1">مدیریت حواله، قفسه‌بندی و انبارگردانی پیشرفته به سیستم اضافه شود.</span>
                                </div>
                            </label>

                            @if($wms_enabled)
                                <div class="pl-8 animate-in fade-in">
                                    <label for="stock_deduction_strategy" class="{{ $labelClass }}">استراتژی کسر موجودی</label>
                                    <select id="stock_deduction_strategy" wire:model.defer="stock_deduction_strategy" class="{{ $inputClass }}">
                                        <option value="combined">ترکیبی (موجودی کل انبارهای فیزیکی و آنلاین)</option>
                                        <option value="separated">مجزا (فقط موجودی انبارهای نوع آنلاین)</option>
                                    </select>
                                </div>
                            @endif

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="enable_reports" class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">سیستم گزارشات کلان (Analytics)</span>
                                    <span class="text-[10px] text-gray-500 mt-1">ایجاد داشبورد نمودارها و خروجی‌های پیشرفته فروش.</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="separate_category_enabled" class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">سیستم دسته‌بندی مجزا</span>
                                    <span class="text-[10px] text-gray-500 mt-1">فعال‌سازی سیستم دسته‌بندی مستقل جهت کنترل و نحوه‌ی نمایش محصولات و صفحات فروشگاه به کاربر.</span>
                                </div>
                            </label>
                        </div>


                        {{-- ماژول‌های مارکتینگ --}}
                        <div class="space-y-4 border border-gray-100 dark:border-gray-700 p-5 rounded-2xl bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">مارکتینگ و فروش</h3>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.live="enable_coupons" class="{{ $checkboxClass }}">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">فعال‌سازی کدهای تخفیف</span>
                            </label>

                            @if($enable_coupons)
                                <label class="flex items-center gap-3 cursor-pointer mr-6 animate-in fade-in">
                                    <input type="checkbox" wire:model.defer="sequential_discounts" class="{{ $checkboxClass }}">
                                    <span class="text-[11px] font-medium text-gray-600 dark:text-gray-400">محاسبه متوالی (اعمال تخفیف دوم روی قیمت تخفیف خورده)</span>
                                </label>
                            @endif

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="enable_wallet" class="{{ $checkboxClass }}">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">کیف پول کاربری (امتیاز/شارژ)</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.defer="enable_affiliate" class="{{ $checkboxClass }}">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">سیستم همکاری در فروش (Affiliate)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        {{-- تنظیم پیشوند کالا --}}
                        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl">
                            <label class="{{ $labelClass }}">پیش‌وند کدهای محصول (Product Prefix)</label>
                            <div class="relative">
                                <input type="text" wire:model.defer="system_product_prefix" class="{{ $inputClass }} font-mono uppercase text-left dir-ltr pl-4" placeholder="SIT">
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-2">
                                این حروف در ابتدای کد هوشمند (Smart SKU) تمام محصولات مرجع قرار می‌گیرد. توصیه می‌شود حداکثر 3 الی 4 حرف انگلیسی باشد.
                            </p>
                            @error('system_product_prefix') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 1. تب عمومی --}}
        <div x-show="tab === 'general'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات عمومی و کاتالوگ</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی واحد پول، نمایش محصولات و مناطق مجاز</p>
                </div>
            </div>
            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- بلوک وضعیت و کاتالوگ --}}
                    <div class="space-y-4 border-l border-gray-100 dark:border-gray-700 pl-6">
                        <div>
                            <label class="{{ $labelClass }}">وضعیت فعالیت فروشگاه</label>
                            <select wire:model.defer="is_market_active" class="{{ $inputClass }}">
                                <option value="1">فعال (کاربران می‌توانند خرید کنند)</option>
                                <option value="0">غیرفعال (Maintenance / توقف موقت فروش)</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="hide_out_of_stock" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">مخفی کردن محصولات ناموجود از کاتالوگ و جستجو</span>
                        </label>

                        <div class="mt-4 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <label class="{{ $labelClass }}">نحوه نمایش محصولات متغیر (تنوع کالا) در فروشگاه</label>
                            <select wire:model.defer="variant_display_mode" class="{{ $inputClass }}">
                                <option value="grouped">گروه‌بندی شده (یک کارت محصول + ذکر تعداد تنوع)</option>
                                <option value="separated">مجزا (هر رنگ/سایز یک کارت مجزا در فروشگاه باشد)</option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-2 leading-relaxed">
                                <strong>گروه‌بندی شده:</strong> مشابه دیجی‌کالا. کارت تمیزتر است و کاربر داخل صفحه محصول رنگ را انتخاب می‌کند.<br>
                                <strong>مجزا:</strong> مشابه فروشگاه‌های لباس یا دیجی‌استایل. باعث می‌شود فروشگاه پربارتر به نظر برسد.
                            </p>
                        </div>

                        <div class="mt-4 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <label class="{{ $labelClass }}">مسیر هدایت پس از ثبت کاتالوگ (مختص تک فروشگاهی)</label>
                            <select wire:model.defer="single_vendor_redirect_after_save" class="{{ $inputClass }}">
                                <option value="catalog">لیست کاتالوگ محصولات (پیش‌فرض)</option>
                                <option value="pricing">صفحه قیمت‌گذاری و موجودی همان کالا (پیشنهادی)</option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-2 leading-relaxed">
                                تعیین می‌کند که پس از ذخیره نهایی کاتالوگ کالا در حالت تک فروشگاهی، به کدام صفحه هدایت شوید.
                            </p>
                        </div>

                    </div>

                    {{-- بلوک مالی --}}
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">واحد پول پیش‌فرض</label>
                                <select wire:model.defer="currency" class="{{ $inputClass }}">
                                    <option value="toman">تومان (Toman)</option>
                                    <option value="rial">ریال (IRR)</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">موقعیت نماد پول</label>
                                <select wire:model.defer="currency_position" class="{{ $inputClass }}">
                                    <option value="right">راست (100تومان)</option>
                                    <option value="right_space">راست با فاصله (100 تومان)</option>
                                    <option value="left">چپ (تومان100)</option>
                                    <option value="left_space">چپ با فاصله (تومان 100)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- مکان‌های فروش --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">مکان‌های مجاز فروش (آدرس مشتری)</label>
                        <select wire:model.live="selling_location" class="{{ $inputClass }}">
                            <option value="all">فروش به تمام مناطق (پشتیبانی سراسری)</option>
                            <option value="specific">فروش فقط به مناطق خاص (تعیین شده)</option>
                            <option value="exception">فروش به همه مناطق، به جز...</option>
                        </select>
                    </div>

                    @if($selling_location !== 'all')
                        <div class="animate-in fade-in duration-300">
                            <label class="{{ $labelClass }}">انتخاب مناطق مجاز / غیرمجاز</label>
                            <select wire:model.defer="specific_locations" multiple class="{{ $inputClass }} h-24 scrollbar-thin">
                                @foreach($locationsList as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-gray-400 mt-1 block">با نگه داشتن دکمه Ctrl چند مورد را انتخاب کنید.</span>
                        </div>
                    @endif
                </div>

                {{-- زمان کاری --}}
                <div>
                    <label class="{{ $labelClass }}">ساعات کاری پشتیبانی و ارسال</label>
                    <textarea wire:model.defer="business_days" rows="2" class="{{ $inputClass }}" placeholder="مثال: شنبه تا چهارشنبه از ساعت 9 الی 17 - پنجشنبه 9 الی 13"></textarea>
                    <p class="text-xs text-gray-500 mt-1">این متن به عنوان اطلاعات زمان پاسخ‌گویی در سایت نمایش داده می‌شود.</p>
                </div>
            </div>
        </div>

        {{-- 💡 2.5 تب جدید: تنظیمات نمایشی (UI/UX) --}}
        <div x-show="tab === 'ui'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات نمایشی و رابط کاربری (UI)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مدیریت رفتار گرافیکی کارت‌ها و صفحات محصول</p>
                </div>
            </div>
            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- تنظیمات کارت محصول --}}
                    <div class="space-y-4 border border-gray-100 dark:border-gray-700 p-5 rounded-2xl bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">تنظیمات کارت‌های فروشگاه</h3>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ui_show_category_on_card" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">نمایش نام دسته‌بندی روی کارت محصول</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ui_show_brand_on_card" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">نمایش نام برند روی کارت محصول</span>
                        </label>

                        <div class="pt-3">
                            <label class="{{ $labelClass }}">طراحی (استایل) کارت محصولات</label>
                            <select wire:model.defer="ui_product_card_style" class="{{ $inputClass }}">
                                <option value="modern">مدرن (دارای هاور افکت‌های پیشرفته و بلور)</option>
                                <option value="classic">کلاسیک (ساده و مینیمال - مناسب فروشگاه‌های سنگین)</option>
                                <option value="minimal">مینیمال (بدون حاشیه و سایه‌های سنگین)</option>
                            </select>
                        </div>
                    </div>

                    {{-- تنظیمات صفحه محصول تکی --}}
                    <div class="space-y-4 border border-gray-100 dark:border-gray-700 p-5 rounded-2xl bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">تنظیمات صفحه داخلی محصول</h3>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ui_show_brand_on_product_page" class="{{ $checkboxClass }}">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">نمایش نام برند در صفحه محصول</span>
                                <span class="text-[10px] text-gray-500 mt-1">نمایش یا عدم نمایش نام برند در بالای عنوان کالا.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ui_show_vendor_on_product_page" class="{{ $checkboxClass }}">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">نمایش بخش "فروشنده" در باکس خرید</span>
                                <span class="text-[10px] text-gray-500 mt-1">اگر سایت تک‌فروشگاهی است، پیشنهاد می‌شود این گزینه را خاموش کنید.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="ui_show_stock_warning" class="{{ $checkboxClass }}">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">نمایش هشدار موجودی کم (پایین‌تر از ۳ عدد)</span>
                                <span class="text-[10px] text-gray-500 mt-1">برای ایجاد حس فوریت (FOMO) در خریدار مناسب است.</span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- 3. تب مالیات --}}
        <div x-show="tab === 'tax'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">سیستم مالیات (Taxes)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی قوانین ارزش افزوده برای محصولات و حمل و نقل</p>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                    <input type="checkbox" wire:model.live="enable_taxes" class="{{ $checkboxClass }}">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-200">فعال‌سازی سیستم محاسبات مالیات و ارزش افزوده</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">با فعال شدن، تب مالیات در ثبت محصول اضافه شده و روی سبد خرید اعمال می‌شود.</span>
                    </div>
                </label>

                @if($enable_taxes)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-5 bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700 rounded-xl animate-in fade-in slide-in-from-top-4">
                        <div>
                            <label class="{{ $labelClass }}">نرخ پیش‌فرض مالیات (%)</label>
                            <input type="number" wire:model.defer="default_tax_rate" class="{{ $inputClass }} text-center font-mono dir-ltr" placeholder="9">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">وارد کردن قیمت در محصولات</label>
                            <select wire:model.defer="prices_include_tax" class="{{ $inputClass }}">
                                <option value="0">قیمت بدون مالیات وارد می‌شود (مالیات هنگام پرداخت افزوده می‌شود)</option>
                                <option value="1">قیمتی که وارد می‌شود شامل مالیات است</option>
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">محاسبه مالیات بر اساس</label>
                            <select wire:model.defer="tax_calculation_based_on" class="{{ $inputClass }}">
                                <option value="customer_shipping">آدرس تحویل مشتری (استاندارد)</option>
                                <option value="store_base">آدرس مبدأ فروشگاه / انبار</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4. تب فروشندگان (فقط Multi Vendor) --}}
        @if($store_type === 'multi' || auth()->user()->can('market.vendors.manage') || auth()->user()->can('market.vendors.view'))
            <div x-show="tab === 'vendors'" x-cloak class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">سیاست‌ها و دسترسی فروشندگان</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تنظیمات تایید محصول، حریم خصوصی کاربر و کمیسیون</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">

                    {{-- دسترسی‌های عملیاتی --}}
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="auto_approve_vendors" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">تایید خودکار ثبت‌نام فروشندگان (بدون نیاز به تایید ادمین)</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="products_require_approval" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">محصولات جدید/ویرایش شده فروشنده، قبل از انتشار نیاز به تایید ادمین دارد.</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="vendor_can_view_customer_info" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">حریم خصوصی: فروشنده مجاز است شماره موبایل و ایمیل مشتری را در فاکتور ببیند.</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="vendor_can_create_variants" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">تنوع محصولات: فروشنده مجاز است برای محصولات خود تنوع (رنگ، سایز و...) ایجاد کند.</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="vendor_can_create_catalog" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">ایجاد کاتالوگ: فروشنده در صورت داشتن مجوز لازم، اجازه ایجاد کاتالوگ (محصولات مرجع) را داشته باشد.</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="vendor_can_manage_prices" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">مدیریت قیمت: فروشنده اجازه تعیین و تغییر قیمت را داشته باشد. (در غیر این صورت، قیمت توسط ادمین در کاتالوگ کالا مشخص می‌شود)</span>
                        </label>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-700">

                    {{-- قوانین مالی و وضعیت کاتالوگ فروشنده --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">وضعیت پیش‌فرض کاتالوگ کارهای فروشنده</label>
                            <select wire:model.defer="vendor_catalog_default_status" class="{{ $inputClass }}">
                                <option value="draft">پیش‌نویس (نیاز به بررسی و فعال‌سازی توسط ادمین)</option>
                                <option value="active">فعال (انتشار مستقیم و خودکار)</option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">محصولات مرجعی که فروشنده ایجاد می‌کند به صورت پیش‌فرض در این وضعیت قرار می‌گیرند.</p>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">کمیسیون پایه سیستم (%)</label>
                            <div class="relative">
                                <input type="number" wire:model.defer="default_commission_rate" class="{{ $inputClass }} dir-ltr text-center font-mono">
                                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 text-xs">%</div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">این درصد روی تمام فروشندگان اعمال می‌شود مگر اینکه برای یک فروشنده خاص درصد متفاوتی ست شود.</p>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">حداکثر تعداد آدرس/انبار برای هر فروشنده</label>
                            <input type="number" wire:model.defer="max_vendor_addresses" class="{{ $inputClass }} dir-ltr text-center font-mono">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 5. تب سفارشات --}}
        <div x-show="tab === 'orders'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">ثبت سفارش و پرداخت (Checkout)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مدیریت لغو خودکار، مرجوعی‌ها و فرمت فاکتور</p>
                </div>
            </div>
            <div class="p-6 space-y-6">

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                    <input type="checkbox" wire:model.defer="allow_guest_checkout" class="{{ $checkboxClass }}">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-200">خرید به عنوان مهمان (Guest Checkout)</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">اجازه ثبت سفارش به کاربرانی که هنوز در سایت عضو نشده‌اند.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                    <input type="checkbox" wire:model.defer="enable_geolocation_ordering" class="{{ $checkboxClass }}">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-200">فعال کردن سفارش کلاینت بر اساس موقعیت مکانی</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">در صورت فعال بودن، موقعیت مکانی (آدرس) کلاینت دریافت شده و فیلتر/ثبت سفارشات بر اساس آن انجام می‌شود.</span>
                    </div>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">حداقل مبلغ کل سبد خرید برای ثبت سفارش</label>
                        <div class="relative">
                            <input type="text" wire:model.defer="min_order_amount" class="{{ $inputClass }} dir-ltr text-center font-mono">
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400 text-xs">تومان / ریال</div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">عدد 0 به معنای عدم محدودیت است.</p>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">پیش‌وند شماره فاکتور (Invoice Prefix)</label>
                        <input type="text" wire:model.defer="invoice_prefix" class="{{ $inputClass }} dir-ltr text-center font-mono" placeholder="INV-">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">لغو خودکار سفارشات پرداخت نشده (ساعت)</label>
                        <div class="relative">
                            <input type="number" wire:model.defer="auto_cancel_unpaid_orders_hours" class="{{ $inputClass }} dir-ltr text-center font-mono">
                            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 text-xs">ساعت</div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">موجودی محصول پس از این زمان آزاد می‌شود.</p>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">مهلت ضمانت بازگشت کالا / مرجوعی (روز)</label>
                        <div class="relative">
                            <input type="number" wire:model.defer="return_policy_days" class="{{ $inputClass }} dir-ltr text-center font-mono">
                            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 text-xs">روز</div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">وضعیت پیش‌فرض سفارش بر اساس روش‌های پرداخت</h3>
                    <p class="text-xs text-gray-500">برای هر یک از روش‌های پرداخت فعال در تنظیمات سیستم، وضعیت اولیه پرداخت و تحویل فاکتور را مشخص کنید.</p>
                    
                    @if(empty($activePaymentMethods))
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 text-amber-600 dark:text-amber-400 text-xs">
                            هیچ روش پرداختی در تنظیمات کلی سیستم فعال نیست.
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($activePaymentMethods as $methodKey => $methodName)
                                <div class="p-4 rounded-xl border border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/10 flex flex-col md:flex-row md:items-center gap-4">
                                    <div class="md:w-1/4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        {{ $methodName }}
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                                        @if(in_array($methodKey, ['zarinpal', 'zibal', 'behpardakht']))
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">وضعیت پرداخت (پس از تراکنش موفق)</label>
                                                <div class="px-4 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-sm font-bold border border-emerald-100 dark:border-emerald-900/20">
                                                    پرداخت شده (paid)
                                                </div>
                                            </div>
                                        @else
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">وضعیت پرداخت</label>
                                                <select wire:model.defer="payment_method_statuses.{{ $methodKey }}.payment_status" class="{{ $inputClass }}">
                                                    <option value="unpaid">در انتظار پرداخت (unpaid)</option>
                                                    <option value="paid">پرداخت شده (paid)</option>
                                                    <option value="failed">ناموفق (failed)</option>
                                                </select>
                                            </div>
                                        @endif
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">وضعیت تحویل / ارسال</label>
                                            <select wire:model.defer="payment_method_statuses.{{ $methodKey }}.delivery_status" class="{{ $inputClass }}">
                                                <option value="pending">در انتظار تایید (pending)</option>
                                                <option value="processing">در حال پردازش (processing)</option>
                                                <option value="shipped">ارسال شده (shipped)</option>
                                                <option value="delivered">تحویل داده شده (delivered)</option>
                                                <option value="canceled">لغو شده (canceled)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 7. تب تسویه حساب --}}
        <div x-show="tab === 'checkout'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">فرم تسویه حساب و سینک اطلاعات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مدیریت فرم‌های پرداخت و همگام‌سازی با پروفایل مشتری</p>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="checkout_auto_fill_from_client" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">پیش‌نمایش خودکار اطلاعات مشتری در فرم</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <input type="checkbox" wire:model.defer="checkout_sync_require_approval" class="{{ $checkboxClass }}">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">تغییرات اطلاعات مشتری نیاز به تایید مدیر دارد</span>
                        </label>
                    </div>
                    <div>
                        <label for="checkout_default_form_key" class="{{ $labelClass }}">فرم پیش‌فرض تسویه حساب</label>
                        <select id="checkout_default_form_key" wire:model.defer="checkout_default_form_key" class="{{ $inputClass }}">
                            <option value="">-- انتخاب کنید --</option>
                            @if(isset($checkoutForms))
                                @foreach($checkoutForms as $form)
                                    <option value="{{ $form->key }}">{{ $form->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                        <input type="checkbox" wire:model.defer="checkout_allow_product_override" class="{{ $checkboxClass }}">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">اجازه دادن به محصولات برای داشتن فرم تسویه حساب اختصاصی</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                        <input type="checkbox" wire:model.defer="checkout_allow_category_override" class="{{ $checkboxClass }}">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">اجازه دادن به دسته‌بندی‌ها برای داشتن فرم تسویه حساب اختصاصی</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 6. تب مالی --}}
        <div x-show="tab === 'finance'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-green-600 dark:text-green-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">درگاه‌ها و تسویه‌حساب (Withdrawals)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی درگاه پرداخت و قوانین برداشت وجه برای فروشندگان</p>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div>
                        <label class="{{ $labelClass }}">درگاه پرداخت پیش‌فرض سایت</label>
                        <select wire:model.defer="default_payment_gateway" class="{{ $inputClass }} appearance-none">
                            <option value="zarinpal">زرین‌پال (ZarinPal)</option>
                            <option value="saman">بانک سامان (Saman Kish)</option>
                            <option value="mellat">بانک ملت (Behpardakht)</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">حداقل مبلغ درخواست تسویه حساب</label>
                        <div class="relative">
                            <input type="text" wire:model.defer="min_withdrawal_amount" class="{{ $inputClass }} dir-ltr text-center font-mono">
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400 text-xs">تومان/ریال</div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">زمان‌بندی تسویه با فروشندگان</label>
                        <select wire:model.defer="withdrawal_schedule" class="{{ $inputClass }} appearance-none">
                            <option value="on_demand">به درخواست فروشنده (آزاد)</option>
                            <option value="weekly">به صورت هفتگی (اتوماتیک)</option>
                            <option value="monthly">به صورت ماهانه (اتوماتیک)</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- 7. تب تنظیمات نقشه --}}
        <div x-show="tab === 'map'" x-cloak class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات نقشه و آدرس‌ها</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی منبع دریافت نقشه و مختصات (Neshan / Map.ir)</p>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">منبع نقشه و وب‌سرویس</label>
                        <select wire:model.live="map_provider" class="{{ $inputClass }} appearance-none">
                            <option value="neshan">Neshan.org (نقشه نشان)</option>
                            <option value="map_ir">Map.ir (نقشه مپ)</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">کلید دسترسی API (API Key)</label>
                        <input type="text" wire:model.defer="map_api_key" class="{{ $inputClass }} dir-ltr text-left font-mono" placeholder="مثال: web.1234567890abcdef...">
                        @error('map_api_key') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- دکمه ذخیره شناور --}}
    <div class="fixed bottom-6 left-0 right-0 z-40 flex justify-center pointer-events-none px-4">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl pointer-events-auto max-w-sm w-full flex justify-between items-center gap-4 animate-in slide-in-from-bottom-6">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-2 hidden sm:inline">تغییرات را ذخیره کنید</span>
            <button wire:click="save" wire:loading.attr="disabled"
                    class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره تمام تنظیمات
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    در حال پردازش...
                </span>
            </button>
        </div>
    </div>
</div>
