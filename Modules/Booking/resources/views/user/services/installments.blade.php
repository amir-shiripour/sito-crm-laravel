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
                $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
                $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
                $helperClass = "text-[10px] text-gray-400 mt-1";
            @endphp

            @php
                $tabs = is_array($customPrices)
                    ? ($customPrices['tabs'] ?? $customPrices)
                    : [];
            @endphp

            @empty($tabs)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 12H4"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">هیچ آیتمی در بخش "قیمت‌گذاری پیشرفته" ثبت نشده است.</p>
                    <a href="{{ route('user.booking.services.custom-prices', $service->id) }}" class="text-indigo-600 text-sm mt-2 inline-block hover:underline">رفتن به قیمت‌گذاری پیشرفته</a>
                </div>
            @endempty

            @foreach($tabs as $tIdx => $tab)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-l from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-xs font-black">
                                {{ $tIdx + 1 }}
                            </span>
                            تب: {{ $tab['title'] ?? 'بدون عنوان' }}
                        </h2>
                    </div>

                    <div class="p-6 space-y-8">
                        @foreach($tab['sections'] as $sIdx => $section)
                            <div x-data="{ active: {{ ($savedSettings[$tIdx]['sections'][$sIdx]['is_active'] ?? false) ? 'true' : 'false' }}, applyToAll: false }" class="relative p-5 rounded-xl border border-dashed transition-all duration-300 {{ ($savedSettings[$tIdx]['sections'][$sIdx]['is_active'] ?? false) ? 'border-indigo-300 bg-indigo-50/30 dark:border-indigo-800 dark:bg-indigo-900/10' : 'border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/40' }}">

                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                        {{ $section['title'] ?? 'عنوان بدون نام' }}
                                        <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 rounded-full uppercase">{{ $section['type'] ?? '' }}</span>
                                    </h3>

                                    <!-- Toggle Switch برای فعال‌سازی -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][is_active]" value="0">
                                        <input type="checkbox" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][is_active]" value="1" x-model="active" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-600 peer-checked:bg-indigo-600"></div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">فعال‌سازی قسطی</span>
                                    </label>
                                </div>

                                <!-- تنظیمات وقتی فعال باشد نمایش داده می‌شود -->
                                <div x-show="active" x-transition.opacity.duration.300ms class="space-y-6 mt-4">

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <label class="{{ $labelClass }}">حداکثر تعداد اقساط</label>
                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][max_months]" class="{{ $inputClass }}" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['max_months'] ?? 3 }}" min="1" max="60">
                                            <p class="{{ $helperClass }}">مثلاً ۳ یا ۶ یا ۱۲ قسط</p>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">درصد پیش‌پرداخت (%)</label>
                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][down_payment_percent]" class="{{ $inputClass }}" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['down_payment_percent'] ?? 30 }}" min="0" max="100">
                                            <p class="{{ $helperClass }}>حداقل ۱۰٪ پیشنهاد می‌شود</p>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">کارمزد قسط (%)</label>
                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][fee_percent]" class="{{ $inputClass }}" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['fee_percent'] ?? 0 }}" min="0" step="0.1">
                                            <p class="{{ $helperClass }}">درصد سود یا کارمزد بانکی</p>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">نوع بازپرداخت</label>
                                            <select name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][payment_cycle]" class="{{ $inputClass }}">
                                                <option value="monthly" {{ ($savedSettings[$tIdx]['sections'][$sIdx]['payment_cycle'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>ماهانه</option>
                                                <option value="weekly" {{ ($savedSettings[$tIdx]['sections'][$sIdx]['payment_cycle'] ?? 'monthly') === 'weekly' ? 'selected' : '' }}>هفتگی</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="{{ $labelClass }}">مهلت پرداخت هر قسط (روز)</label>
                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][grace_period_days]" class="{{ $inputClass }}" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['grace_period_days'] ?? 3 }}" min="0">
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">جریمه دیرکرد روزانه (%)</label>
                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][late_fee_percent]" class="{{ $inputClass }}" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['late_fee_percent'] ?? 0 }}" min="0" step="0.01">
                                        </div>
                                    </div>

                                    @if(!empty($section['brands']))
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5">
                                            <div class="flex items-center justify-between mb-4">
                                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">تنظیمات اختصاصی برندها / گزینه‌ها:</p>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" x-model="applyToAll" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                                    <span class="text-xs text-indigo-600 font-medium">اعمال تنظیمات بالا برای همه</span>
                                                </label>
                                            </div>

                                            @foreach($section['brands'] as $bIdx => $brand)
                                                <div x-data="{ price: {{ $brand['price'] ?? 0 }}, dp: {{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['down_payment_percent'] ?? $savedSettings[$tIdx]['sections'][$sIdx]['down_payment_percent'] ?? 30 }} }" class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 mb-3 border border-gray-100 dark:border-gray-700 shadow-sm">
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 block truncate">
                                                            {{ $brand['name'] ?? 'گزینه ' . ($bIdx + 1) }}
                                                        </span>
                                                        <span class="text-[11px] text-gray-400 block mt-0.5">قیمت پایه: {{ number_format($brand['price'] ?? 0) }} تومان</span>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                                                        <div class="w-20">
                                                            <label class="text-[10px] text-gray-500 block mb-1">اقساط</label>
                                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][max_months]" class="{{ $inputClass }} text-center py-1.5 text-xs" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['max_months'] ?? '' }}" placeholder="پیش‌فرض" :disabled="applyToAll">
                                                        </div>
                                                        <div class="w-24 relative">
                                                            <label class="text-[10px] text-gray-500 block mb-1">پیش‌پرداخت %</label>
                                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][down_payment_percent]" x-model="dp" class="{{ $inputClass }} text-center py-1.5 text-xs" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['down_payment_percent'] ?? '' }}" placeholder="پیش‌فرض" :disabled="applyToAll">
                                                            <span class="absolute left-2 top-7 text-[9px] text-gray-400 font-mono" x-text="Math.round(price * dp / 100).toLocaleString() + ' ت'"></span>
                                                        </div>
                                                        <div class="w-20">
                                                            <label class="text-[10px] text-gray-500 block mb-1">کارمزد %</label>
                                                            <input type="number" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][fee_percent]" class="{{ $inputClass }} text-center py-1.5 text-xs" value="{{ $savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['fee_percent'] ?? '' }}" placeholder="پیش‌فرض" :disabled="applyToAll">
                                                        </div>
                                                        <div>
                                                            <label class="flex items-center gap-1.5 mt-4 sm:mt-5 cursor-pointer">
                                                                <input type="hidden" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][excluded]" value="0">
                                                                <input type="checkbox" name="installments[{{ $tIdx }}][sections][{{ $sIdx }}][brands][{{ $bIdx }}][excluded]" value="1" class="rounded border-red-300 text-red-500 shadow-sm" {{ ($savedSettings[$tIdx]['sections'][$sIdx]['brands'][$bIdx]['excluded'] ?? false) ? 'checked' : '' }}>
                                                                <span class="text-[10px] text-red-500 font-bold whitespace-nowrap">خارج از قسطی</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(!empty($tabs))
                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="px-10 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        ذخیره تنظیمات قسطی
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
