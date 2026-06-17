@extends('layouts.user')

@section('title', 'تنظیمات پرداخت قسطی: ' . $service->name)

@section('content')
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">تنظیمات قسطی: {{ $service->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت شرایط پرداخت برای آیتم‌ها و برندهای قیمت‌گذاری پیشرفته</p>
            </div>
            <a href="{{ route('user.booking.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-all dark:bg-gray-700 dark:text-gray-200">
                بازگشت به لیست
            </a>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('user.booking.services.installments.update', $service->id) }}" method="POST" class="space-y-6">
            @csrf

            @php
                $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
                $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
            @endphp

            @empty($customPrices)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">هیچ آیتمی در بخش "قیمت‌گذاری پیشرفته" ثبت نشده است.</p>
                    <a href="{{ route('user.booking.services.custom-prices', $service->id) }}" class="text-indigo-600 text-sm mt-2 inline-block">رفتن به قیمت‌گذاری پیشرفته</a>
                </div>
            @endempty

            @foreach($customPrices as $tIdx => $tab)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-xs">{{ $tIdx + 1 }}</span>
                            تب: {{ $tab['title'] ?? 'بدون عنوان' }}
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        @foreach($tab['sections'] as $sIdx => $section)
                            <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                    {{ $section['title'] ?? 'عنوان بدون نام' }}
                                    <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 rounded-full">{{ $section['type'] ?? '' }}</span>
                                </h3>

                                <div class="mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][is_active]" value="1"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                            {{ ($savedSettings[$tIdx]['sections'][$sIdx]['is_active'] ?? false) ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال‌سازی قسطی برای این آیتم</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div>
                                        <label class="{{ $labelClass }}">حداکثر تعداد اقساط</label>
                                        <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][max_months]" class="{{ $inputClass }}"
                                               value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['max_months'] ?? 3 }}" min="1">
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">درصد پیش‌پرداخت (%)</label>
                                        <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][down_payment_percent]" class="{{ $inputClass }}"
                                               value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['down_payment_percent'] ?? 30 }}" min="0" max="100">
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">کارمزد قسط (%)</label>
                                        <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][fee_percent]" class="{{ $inputClass }}"
                                               value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['fee_percent'] ?? 0 }}" min="0">
                                    </div>
                                </div>

                                @if(!empty($section['brands']))
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">تنظیمات اختصاصی برندها / گزینه‌ها:</p>
                                        @foreach($section['brands'] as $bIdx => $brand)
                                            <div class="flex items-center gap-4 p-3 rounded-lg bg-white dark:bg-gray-800 mb-2 border border-gray-100 dark:border-gray-700">
                                                <div class="flex-1">
                                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                                        {{ $brand['name'] ?? 'گزینه ' . ($bIdx + 1) }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 mr-1">({{ number_format($brand['price'] ?? 0) }} تومان)</span>
                                                </div>
                                                <div class="w-24">
                                                    <label class="text-[10px] text-gray-500 block mb-1">اقساط</label>
                                                    <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][max_months]"
                                                           class="{{ $inputClass }} text-center py-1.5"
                                                           value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['max_months'] ?? '' }}"
                                                           placeholder="پیش‌فرض">
                                                </div>
                                                <div class="w-24">
                                                    <label class="text-[10px] text-gray-500 block mb-1">پیش‌پرداخت %</label>
                                                    <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][down_payment_percent]"
                                                           class="{{ $inputClass }} text-center py-1.5"
                                                           value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['down_payment_percent'] ?? '' }}"
                                                           placeholder="پیش‌فرض">
                                                </div>
                                                <div>
                                                    <label class="flex items-center gap-1 mt-3 cursor-pointer">
                                                        <input type="checkbox" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][excluded]" value="1"
                                                               class="rounded border-gray-300 text-red-500"
                                                            {{ ($savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['excluded'] ?? false) ? 'checked' : '' }}>
                                                        <span class="text-[10px] text-red-500 font-medium">خارج از قسطی</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(!empty($customPrices))
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-purple-600 text-white font-bold shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-purple-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات قسطی
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
