@extends('layouts.user')

@section('title', 'تنظیمات انبارداری و مدیریت اموال')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Header --}}
    <div class="pb-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-gray-200">تنظیمات ماژول</span>
        </div>

        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            تنظیمات ماژول انبارداری
        </h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            پیکربندی فعال‌سازی انواع انبار و مدیریت اتصال به ماژول فروشگاه (Market)
        </p>
    </div>

    @include('warehouse::partials.flash_messages')

    <form action="{{ route('admin.warehouse.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: Warehouse Types Toggle (Flat, Standard Panel Card) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    انواع انبار فعال در سامانه
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    با غیرفعال‌سازی هر بخش، منوی آن در سایدبار مخفی می‌شود و اطلاعات پیشین کاملاً محفوظ می‌ماند.
                </p>
            </div>

            <div class="space-y-3 pt-1">
                {{-- Inventory Warehouses Toggle --}}
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80">
                    <div>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">انبار کالا / موجودی (Inventory)</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">انبار مرکزی موجودی، انبارک‌های شعب و پیوند با فروشگاه</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="inventory_enabled" value="1" {{ $inventoryEnabled ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                {{-- Asset Management Toggle --}}
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80">
                    <div>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">مدیریت اموال سازمانی (Asset Management)</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">انبار مرکزی اموال، طبقات، اثاثیه و تجهیزات دفتری</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="assets_enabled" value="1" {{ $assetsEnabled ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 2: Market Module Integration (Flat, Standard Panel Card) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        یکپارچگی با فروشگاه (Market)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        نمایش انبارهای فعال فروشگاه به‌عنوان زیرمجموعه انبار موجودی کالا
                    </p>
                </div>

                {{-- Status Pills --}}
                <div class="flex items-center gap-2">
                    @if($isMarketInstalled)
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/50">
                            فروشگاه: فعال
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            فروشگاه: نصب نیست
                        </span>
                    @endif

                    @if($isMarketWmsEnabled)
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/50">
                            WMS: فعال
                        </span>
                    @elseif($isMarketInstalled)
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/50">
                            WMS: غیرفعال
                        </span>
                    @endif
                </div>
            </div>

            @if($isMarketInstalled && $isMarketWmsEnabled)
                {{-- Toggle Master Integration --}}
                <div class="flex items-center justify-between p-4 rounded-xl border border-amber-200/80 dark:border-amber-900/50 bg-amber-50/30 dark:bg-amber-950/20">
                    <div>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">اتصال انبارهای فروشگاه به انبار موجودی</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">انبارهای فعال مارکت را به صورت نمایشی تحت انبار مرکزی موجودی نشان بده.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="market_integration_enabled" value="1" {{ $marketIntegrationEnabled ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-600"></div>
                    </label>
                </div>

                {{-- Warehouses Selection Checklist --}}
                @if($availableMarketWarehouses->count() > 0)
                    <div class="space-y-2 pt-1">
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                            انتخاب انبارهای متصل:
                        </label>

                        <div class="space-y-1.5 max-h-60 overflow-y-auto">
                            @foreach($availableMarketWarehouses as $mw)
                                <label class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-800/80 hover:bg-gray-50 dark:hover:bg-gray-700/60 border border-gray-200 dark:border-gray-700 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox"
                                               name="linked_market_warehouses[]"
                                               value="{{ $mw['id'] }}"
                                               {{ $mw['is_linked'] ? 'checked' : '' }}
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $mw['name'] }}</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-sans mr-1">({{ $mw['code'] }})</span>
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500 mr-2">«{{ $mw['vendor_name'] }}»</span>
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $mw['is_active'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        {{ $mw['is_active'] ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-xs text-gray-500 dark:text-gray-400">
                    @if(!$isMarketInstalled)
                        ماژول فروشگاه (Market) در این سامانه نصب نشده است.
                    @else
                        سیستم انبارداری (WMS) در تنظیمات ماژول فروشگاه غیرفعال است.
                    @endif
                </div>
            @endif
        </div>

        {{-- Action Buttons (Panel Standard) --}}
        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('admin.warehouse.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                بازگشت
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                ذخیره تنظیمات
            </button>
        </div>
    </form>

</div>
@endsection
