@extends('layouts.user')

@php
    $title = 'قیمت‌گذاری ملک';
    // فرض بر وجود کلاس PropertySetting یا دریافت از کنترلر
    $currency = \Modules\Properties\Entities\PropertySetting::get('currency', 'toman');
    $currencyLabel = $currency == 'toman' ? 'تومان' : 'ریال';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 dir-ltr text-center font-mono placeholder-gray-400 dark:placeholder-gray-600";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="priceFormatter()">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                    قیمت‌گذاری ملک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مرحله ۲: تعیین شرایط مالی و قیمت برای "{{ $property->title }}"</p>
            </div>

            {{-- نوار پیشرفت --}}
            <div class="hidden sm:flex items-center gap-1">
                <span class="h-1 w-8 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-8 rounded-full bg-indigo-500"></span>
                <span class="h-1 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></span>
                <span class="h-1 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 sm:p-8">
            <form action="{{ route('user.properties.pricing.update', $property) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                {{-- نمایش نوع معامله --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg text-blue-600 dark:text-blue-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-800 dark:text-blue-200 font-medium">نوع معامله: <span class="font-bold">{{ match($property->listing_type) { 'sale' => 'فروش', 'rent' => 'رهن و اجاره', 'presale' => 'پیش‌فروش', default => $property->listing_type } }}</span></p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">لطفاً قیمت‌ها را به <span class="font-bold underline">{{ $currencyLabel }}</span> وارد کنید.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    @if($property->listing_type == 'sale')
                        {{-- فروش --}}
                        <div>
                            <label class="{{ $labelClass }}">قیمت کل اعلامی</label>
                            <div class="relative">
                                <input type="text" name="price" @input="formatPrice" class="{{ $inputClass }}" required value="{{ old('price', number_format($property->price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">قیمت کف (حداقل)</label>
                            <div class="relative">
                                <input type="text" name="min_price" @input="formatPrice" class="{{ $inputClass }}" value="{{ old('min_price', number_format($property->min_price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1.5 text-right">این قیمت محرمانه است و فقط برای شما نمایش داده می‌شود.</p>
                        </div>

                    @elseif($property->listing_type == 'rent')
                        {{-- رهن و اجاره --}}
                        <div>
                            <label class="{{ $labelClass }}">مبلغ رهن (و یا ودیعه)</label>
                            <div class="relative">
                                <input type="text" name="deposit_price" @input="formatPrice" class="{{ $inputClass }}" required value="{{ old('deposit_price', number_format($property->deposit_price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">اجاره ماهیانه</label>
                            <div class="relative">
                                <input type="text" name="rent_price" @input="formatPrice" class="{{ $inputClass }}" required value="{{ old('rent_price', number_format($property->rent_price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>

                    @elseif($property->listing_type == 'presale')
                        {{-- پیش‌فروش --}}
                        <div class="md:col-span-2">
                            <label class="{{ $labelClass }}">قیمت کل اعلامی</label>
                            <div class="relative">
                                <input type="text" name="price" @input="formatPrice" class="{{ $inputClass }}" required value="{{ old('price', number_format($property->price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">مبلغ پیش‌پرداخت</label>
                            <div class="relative">
                                <input type="text" name="advance_price" @input="formatPrice" class="{{ $inputClass }}" required value="{{ old('advance_price', number_format($property->advance_price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">قیمت کف (حداقل)</label>
                            <div class="relative">
                                <input type="text" name="min_price" @input="formatPrice" class="{{ $inputClass }}" value="{{ old('min_price', number_format($property->min_price)) }}" placeholder="0">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-gray-400">{{ $currencyLabel }}</div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- آپشن قابل تبدیل (فقط برای رهن و اجاره) --}}
                @if($property->listing_type == 'rent')
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex flex-col gap-4">
                            <label class="inline-flex items-center gap-3 cursor-pointer group w-fit">
                                <div class="relative">
                                    <input type="checkbox" name="is_convertible" value="1" x-model="isConvertible" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">قابل تبدیل به رهن/اجاره دیگر</span>
                            </label>

                            <div x-show="isConvertible" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                <label class="{{ $labelClass }}">شرایط تبدیل</label>
                                <input type="text" name="convertible_with" class="{{ $inputClass }} !text-right !dir-rtl" placeholder="مثلاً: تا ۲۰۰ میلیون رهن قابل تبدیل است..." value="{{ old('convertible_with', $property->convertible_with) }}">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- دکمه‌های عملیات --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('user.properties.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        مرحله قبل
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        ذخیره و ادامه (اطلاعات تکمیلی)
                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function priceFormatter() {
            return {
                isConvertible: {{ old('is_convertible', $property->is_convertible) ? 'true' : 'false' }},

                formatPrice(event) {
                    // حذف کاراکترهای غیر عددی
                    let value = event.target.value.replace(/,/g, '').replace(/[^\d]/g, '');

                    if (value !== '') {
                        // فرمت سه رقم سه رقم
                        event.target.value = parseInt(value).toLocaleString('en-US');
                    } else {
                        event.target.value = '';
                    }
                }
            }
        }
    </script>
@endsection
