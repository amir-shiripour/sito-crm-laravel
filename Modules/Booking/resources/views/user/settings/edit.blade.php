@extends('layouts.user')

@section('title', 'تنظیمات نوبت‌دهی')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    $globalExceptions = \Modules\Booking\Entities\BookingAvailabilityException::query()
        ->where('scope_type', \Modules\Booking\Entities\BookingAvailabilityException::SCOPE_GLOBAL)
        ->whereNull('scope_id')
        ->get();
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ activeTab: '{{ request('tab', 'general') }}' }" x-init="$watch('activeTab', val => document.getElementById('active-tab-input').value = val)">
        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    تنظیمات سیستم نوبت‌دهی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-2xl leading-relaxed">
                    مدیریت تنظیمات عمومی، طرح درمان و برنامه زمانی
                </p>
            </div>

            <a href="{{ route('user.booking.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                بازگشت به داشبورد
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('success') }}
                @if(request('tab'))
                    <span class="text-xs bg-emerald-100 dark:bg-emerald-800/40 px-2 py-0.5 rounded-lg">
                @if(request('tab') === 'cure')طرح درمان@elseif(request('tab') === 'schedule')برنامه زمانی@elseif(request('tab') === 'appearance')تنظیمات ظاهری@elseif(request('tab') === 'ads')تبلیغات و بنرها@elseعمومی@endif
            </span>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <ul class="list-disc list-inside space-y-1 mt-1.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @includeIf('partials.jalali-date-picker')

        <form method="POST" action="{{ route('user.booking.settings.update') }}" enctype="multipart/form-data" class="space-y-8 pb-24" novalidate>
            <input type="hidden" name="_active_tab" id="active-tab-input" value="{{ request('tab', 'general') }}">
            @csrf
            <div class="border-b border-gray-200 dark:border-gray-700 mb-8">
                <div class="flex gap-1 text-sm font-semibold overflow-x-auto scrollbar-thin">
                    <button type="button" @click="activeTab = 'general'"
                            :class="activeTab === 'general'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        تنظیمات عمومی
                    </button>

                    <button type="button" @click="activeTab = 'cure'"
                            :class="activeTab === 'cure'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        تنظیمات طرح درمان
                    </button>

                    <button type="button" @click="activeTab = 'schedule'"
                            :class="activeTab === 'schedule'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        برنامه زمانی
                    </button>

                    <button type="button" @click="activeTab = 'appearance'"
                            :class="activeTab === 'appearance'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        تنظیمات ظاهری
                    </button>

                    <button type="button" @click="activeTab = 'ads'"
                            :class="activeTab === 'ads'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        تبلیغات و بنرها
                    </button>

                    <button type="button" @click="activeTab = 'holidays'"
                            :class="activeTab === 'holidays'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        تعطیلات عمومی سیستم
                    </button>

                    <button type="button" @click="activeTab = 'sync'"
                            :class="activeTab === 'sync'
                                ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent'"
                            class="pb-4 px-5 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        هماهنگ‌سازی سرویس‌ها
                    </button>
                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  GENERAL TAB                              --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ستون چپ --}}
                <div class="lg:col-span-2 space-y-8">

                    {{-- کارت ۱: تنظیمات پایه و مالی --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات پایه و مالی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی واحد پول و نحوه نمایش</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">واحد پول سیستم</label>
                                <div class="relative">
                                    <select name="currency_unit" class="{{ $selectClass }}">
                                        <option value="IRR" @selected(old('currency_unit', $settings->currency_unit)==='IRR')>ریال (IRR)</option>
                                        <option value="IRT" @selected(old('currency_unit', $settings->currency_unit)==='IRT')>تومان (IRT)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">وضعیت رزرو آنلاین</label>
                                <div class="relative">
                                    <select name="global_online_booking_enabled" class="{{ $selectClass }}">
                                        <option value="1" @selected((string)old('global_online_booking_enabled', $settings->global_online_booking_enabled)==='1')>فعال</option>
                                        <option value="0" @selected((string)old('global_online_booking_enabled', $settings->global_online_booking_enabled)==='0')>غیرفعال</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">استراحت پیش‌فرض قبل نوبت (دقیقه)</label>
                                <input type="number" name="default_buffer_before_minutes" min="0" max="240" class="{{ $inputClass }} text-center" value="{{ old('default_buffer_before_minutes', $settings->default_buffer_before_minutes ?? 0) }}" placeholder="0">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">استراحت پیش‌فرض بعد نوبت (دقیقه)</label>
                                <input type="number" name="default_buffer_after_minutes" min="0" max="240" class="{{ $inputClass }} text-center" value="{{ old('default_buffer_after_minutes', $settings->default_buffer_after_minutes ?? 0) }}" placeholder="0">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">جریان ثبت نوبت (اپراتور)</label>
                                <select name="operator_appointment_flow" class="{{ $selectClass }}">
                                    <option value="PROVIDER_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='PROVIDER_FIRST')>ابتدا ارائه‌دهنده</option>
                                    <option value="SERVICE_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='SERVICE_FIRST')>ابتدا سرویس</option>
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">جریان ثبت نوبت (کاربر)</label>
                                <select name="user_appointment_flow" class="{{ $selectClass }}">
                                    <option value="PROVIDER_FIRST" @selected(old('user_appointment_flow', $settings->user_appointment_flow ?? 'SERVICE_FIRST')==='PROVIDER_FIRST')>ابتدا ارائه‌دهنده</option>
                                    <option value="SERVICE_FIRST" @selected(old('user_appointment_flow', $settings->user_appointment_flow ?? 'SERVICE_FIRST')==='SERVICE_FIRST')>ابتدا سرویس</option>
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">ثبت زمان ورود/خروج</label>
                                <select name="allow_appointment_entry_exit_times" class="{{ $selectClass }}">
                                    <option value="1" @selected((string)old('allow_appointment_entry_exit_times', $settings->allow_appointment_entry_exit_times)==='1')>فعال</option>
                                    <option value="0" @selected((string)old('allow_appointment_entry_exit_times', $settings->allow_appointment_entry_exit_times)==='0')>غیرفعال</option>
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">امکان تغییر به زمان‌بندی دستی (هنگام ثبت نوبت)</label>
                                <select name="allow_manual_time_override" class="{{ $selectClass }}">
                                    <option value="1" @selected((string)old('allow_manual_time_override', $settings->allow_manual_time_override)==='1')>فعال (نمایش دکمه سوییچ)</option>
                                    <option value="0" @selected((string)old('allow_manual_time_override', $settings->allow_manual_time_override)==='0')>غیرفعال</option>
                                </select>
                                <p class="text-[11px] text-gray-400 mt-2">در صورت فعال بودن، اپراتور می‌تواند در هنگام ثبت نوبت اسلاتی، حالت زمان را به وارد کردن دستی ساعت تغییر دهد.</p>
                            </div>
                        </div>
                    </div>

                    {{-- کارت ۱.۵: ارزش افزوده (مالیات) --}}
                    <div class="{{ $cardClass }}" x-data="taxSettings">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">ارزش افزوده (مالیات)</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">وضعیت ارزش افزوده</label>
                                    <div class="relative">
                                        <select name="tax_enabled" x-model="taxEnabled" class="{{ $selectClass }}">
                                            <option value="1" @selected(old('tax_enabled', $settings->tax_enabled ?? false) == true)>فعال (محاسبه روی مبلغ)</option>
                                            <option value="0" @selected(old('tax_enabled', $settings->tax_enabled ?? false) == false)>غیرفعال</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">در صورت فعال بودن، مبلغ ارزش افزوده به هزینه نهایی افزوده می‌شود.</p>
                                </div>
                            </div>

                            <div x-show="taxEnabled == '1'" x-collapse class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <label class="{{ $labelClass }}">نوع محاسبه</label>
                                    <div class="relative">
                                        <select name="tax_type" x-model="taxType" class="{{ $selectClass }}" onchange="setTimeout(() => { document.querySelector('[name=tax_amount_display]').dispatchEvent(new Event('input')) }, 50)">
                                            <option value="PERCENT" @selected(old('tax_type', $settings->tax_type ?? 'PERCENT') === 'PERCENT')>درصدی (%)</option>
                                            <option value="FIXED" @selected(old('tax_type', $settings->tax_type ?? 'PERCENT') === 'FIXED')>مبلغ ثابت</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">مقدار ارزش افزوده</label>
                                    <div class="relative">
                                        <input type="hidden" name="tax_amount" id="tax_amount" value="{{ old('tax_amount', $settings->tax_amount ?? '') }}">
                                        <input type="text" name="tax_amount_display" class="{{ $inputClass }} text-center dir-ltr pl-14"
                                               value="{{ old('tax_amount', $settings->tax_amount ?? '') ? (old('tax_type', $settings->tax_type ?? 'PERCENT') === 'FIXED' ? number_format(old('tax_amount', $settings->tax_amount ?? '')) : old('tax_amount', $settings->tax_amount ?? '')) : '' }}"
                                               @input="formatPriceOrPercent($event)">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-500 text-xs font-bold">
                                            <span x-show="taxType === 'PERCENT'">%</span>
                                            <span x-show="taxType === 'FIXED'" x-cloak>مبلغ</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2" x-show="taxType === 'FIXED'" x-cloak>بر اساس واحد پول سیستم محاسبه می‌شود.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت ۲: دسترسی‌ها و نقش‌ها --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">دسترسی‌ها و نقش‌ها</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مدیریت نقش‌های ارائه‌دهنده خدمت و دسترسی‌ها</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-8">

                            <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-5 flex flex-col md:flex-row gap-5 items-start md:items-center">
                                <div class="flex-1">
                                    <label class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        اجازه ساخت سرویس توسط نقش‌ها
                                    </label>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                                        با فعال‌سازی این گزینه، نقش‌های انتخاب شده در پایین، علاوه بر اینکه به عنوان <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ config('booking.labels.provider') }}</span> شناخته می‌شوند، دسترسی لازم برای <span class="font-bold text-indigo-600 dark:text-indigo-400">ایجاد و ویرایش سرویس‌های خود</span> را نیز خواهند داشت.
                                    </p>
                                </div>
                                <div class="w-full md:w-48 shrink-0">
                                    <div class="relative">
                                        <select name="allow_role_service_creation" class="{{ $selectClass }} border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500/30">
                                            <option value="1" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='1')>بله (مجاز است)</option>
                                            <option value="0" @selected((string)old('allow_role_service_creation', (int)$settings->allow_role_service_creation)==='0')>خیر (محدود شود)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-indigo-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="{{ $labelClass }} flex justify-between">
                                        <span>نقش‌های {{ config('booking.labels.provider') }}</span>
                                        <span class="text-[10px] font-normal text-gray-400">چند انتخابی</span>
                                    </label>
                                    <div class="relative">
                                        <select name="allowed_roles[]" multiple class="{{ $inputClass }} h-48 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                                            @php
                                                $selectedRoles = old('allowed_roles', $settings->allowed_roles ?? []);
                                                if (is_string($selectedRoles)) {
                                                    $decoded = json_decode($selectedRoles, true);
                                                    $selectedRoles = is_array($decoded) ? $decoded : [];
                                                }
                                            @endphp
                                            @foreach($roles ?? [] as $role)
                                                <option value="{{ $role->id }}" {{ in_array($role->id, $selectedRoles) ? 'selected' : '' }} class="py-1 px-2 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer">
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute bottom-2 left-3 text-[10px] text-gray-400 bg-white/80 dark:bg-gray-800/80 px-2 py-0.5 rounded backdrop-blur-sm">
                                            Ctrl/Cmd + Click
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }} flex justify-between">
                                        <span>نقش‌های مجاز در صورت وضعیت</span>
                                        <span class="text-[10px] font-normal text-gray-400">چند انتخابی</span>
                                    </label>
                                    <div class="relative">
                                        <select name="statement_roles[]" multiple class="{{ $inputClass }} h-48 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                                            @php
                                                $selectedStatementRoles = old('statement_roles', $settings->statement_roles ?? []);
                                                if (is_string($selectedStatementRoles)) {
                                                    $decoded = json_decode($selectedStatementRoles, true);
                                                    $selectedStatementRoles = is_array($decoded) ? $decoded : [];
                                                }
                                            @endphp
                                            @foreach($roles ?? [] as $role)
                                                <option value="{{ $role->id }}" {{ in_array($role->id, $selectedStatementRoles) ? 'selected' : '' }} class="py-1 px-2 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer">
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute bottom-2 left-3 text-[10px] text-gray-400 bg-white/80 dark:bg-gray-800/80 px-2 py-0.5 rounded backdrop-blur-sm">
                                            Ctrl/Cmd + Click
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت ۳: مدیریت وضعیت‌های نوبت --}}
                    @php
                        $appointmentStatusesList = old('appointment_statuses', $settings->appointment_statuses ?? []);
                        if (empty($appointmentStatusesList) || !is_array($appointmentStatusesList)) {
                            $appointmentStatusesList = \Modules\Booking\Entities\BookingSetting::defaultAppointmentStatuses();
                        }
                    @endphp
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت وضعیت‌های نوبت</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی وضعیت‌های نوبت، پالت رنگ و فعال‌سازی در ثبت نوبت مرحله‌ای و برنامه زمانی</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="space-y-3">
                                @foreach($appointmentStatusesList as $idx => $st)
                                    @php
                                        $stId = $st['id'] ?? '';
                                        $stName = $st['name'] ?? $stId;
                                        $stColor = $st['color'] ?? '#6b7280';
                                        $stOrder = $st['order'] ?? ($idx + 1);
                                        $stepEnabled = isset($st['step_booking_enabled']) ? (bool)$st['step_booking_enabled'] : true;
                                        $schedEnabled = isset($st['schedule_booking_enabled']) ? (bool)$st['schedule_booking_enabled'] : true;
                                    @endphp
                                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 transition hover:border-indigo-200 dark:hover:border-indigo-800"
                                         x-data="{
                                             color: '{{ $stColor }}',
                                             name: '{{ addslashes($stName) }}',
                                             stepBooking: {{ $stepEnabled ? 'true' : 'false' }},
                                             scheduleBooking: {{ $schedEnabled ? 'true' : 'false' }}
                                         }">
                                        <input type="hidden" name="appointment_statuses[{{ $idx }}][id]" value="{{ $stId }}">
                                        <input type="hidden" name="appointment_statuses[{{ $idx }}][order]" value="{{ $stOrder }}">

                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                            {{-- شناسه، نام و رنگ وضعیت --}}
                                            <div class="flex flex-wrap items-center gap-3 flex-1">
                                                {{-- پیش‌نمایش نشان (Badge) --}}
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition shadow-sm min-w-[110px] justify-center"
                                                     :style="`background-color: ${color}15; color: ${color}; border-color: ${color}35;`">
                                                    <span class="w-2 h-2 rounded-full shrink-0" :style="`background-color: ${color}`"></span>
                                                    <span x-text="name || '{{ $stId }}'"></span>
                                                </div>

                                                {{-- انتخابگر رنگ --}}
                                                <div class="flex items-center gap-1.5">
                                                    <input type="color"
                                                           name="appointment_statuses[{{ $idx }}][color]"
                                                           x-model="color"
                                                           class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer shrink-0 p-0.5">
                                                    <input type="text"
                                                           x-model="color"
                                                           class="{{ $inputClass }} text-xs text-center font-mono py-1.5 px-2 w-24 shrink-0"
                                                           placeholder="#000000">
                                                </div>

                                                {{-- نام نمایشی --}}
                                                <div class="flex-1 min-w-[140px]">
                                                    <input type="text"
                                                           name="appointment_statuses[{{ $idx }}][name]"
                                                           x-model="name"
                                                           class="{{ $inputClass }} text-xs py-1.5 font-medium"
                                                           placeholder="نام وضعیت">
                                                </div>
                                            </div>

                                            {{-- سوییچ‌ها / گزینه‌ها --}}
                                            <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-200 dark:border-gray-700 shrink-0">
                                                {{-- گزینه ۱: فعال برای ثبت نوبت مرحله ایی --}}
                                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                                    <input type="hidden" name="appointment_statuses[{{ $idx }}][step_booking_enabled]" value="0">
                                                    <div class="relative inline-flex items-center shrink-0">
                                                        <input type="checkbox"
                                                               name="appointment_statuses[{{ $idx }}][step_booking_enabled]"
                                                               value="1"
                                                               x-model="stepBooking"
                                                               @checked($stepEnabled)
                                                               class="sr-only peer">
                                                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                        فعال برای ثبت نوبت مرحله ایی
                                                    </span>
                                                </label>

                                                {{-- گزینه ۲: فعال برای برنامه زمانی نوبت دهی --}}
                                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                                    <input type="hidden" name="appointment_statuses[{{ $idx }}][schedule_booking_enabled]" value="0">
                                                    <div class="relative inline-flex items-center shrink-0">
                                                        <input type="checkbox"
                                                               name="appointment_statuses[{{ $idx }}][schedule_booking_enabled]"
                                                               value="1"
                                                               x-model="scheduleBooking"
                                                               @checked($schedEnabled)
                                                               class="sr-only peer">
                                                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                        فعال برای برنامه زمانی نوبت دهی
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ستون راست (کوچکتر) --}}
                <div class="space-y-8">

                    {{-- کارت ۴: تنظیمات ظرفیت و زمان --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">پیش‌فرض‌های زمان</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مقادیر پیش‌فرض برای اسلات‌ها</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="{{ $labelClass }}">مدت هر اسلات (دقیقه)</label>
                                <div class="relative">
                                    <input type="number" name="default_slot_duration_minutes" class="{{ $inputClass }} text-center dir-ltr pl-10"
                                           value="{{ old('default_slot_duration_minutes', $settings->default_slot_duration_minutes) }}" required>
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs">min</div>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">ظرفیت هر اسلات</label>
                                <div class="relative">
                                    <input type="number" name="default_capacity_per_slot" class="{{ $inputClass }} text-center dir-ltr pl-10"
                                           value="{{ old('default_capacity_per_slot', $settings->default_capacity_per_slot) }}" required>
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs">نفر</div>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">ظرفیت کل روز (اختیاری)</label>
                                <div class="relative">
                                    <input type="number" name="default_capacity_per_day" class="{{ $inputClass }} text-center dir-ltr pl-10"
                                           value="{{ old('default_capacity_per_day', $settings->default_capacity_per_day) }}">
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs">نفر</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت: مدیریت صف انتظار (Waitlist Queue) --}}
                    <div class="{{ $cardClass }}" x-data="{ queueEnabled: {{ old('queue_enabled', $settings->queue_enabled ?? false) ? 'true' : 'false' }} }">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت صف انتظار</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی صف انتظار نوبت‌ها</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="flex items-center justify-between cursor-pointer select-none">
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white block">فعال‌سازی صف انتظار</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">امکان قرار دادن بیماران در صف نوبت</span>
                                    </div>
                                    <input type="hidden" name="queue_enabled" value="0">
                                    <div class="relative inline-flex items-center shrink-0 mr-3">
                                        <input type="checkbox"
                                               name="queue_enabled"
                                               value="1"
                                               x-model="queueEnabled"
                                               @checked(old('queue_enabled', $settings->queue_enabled ?? false))
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600"></div>
                                    </div>
                                </label>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2 leading-relaxed">
                                    با فعال بودن این گزینه، در فرم‌ساز ماژول پرونده‌ها فیلد سیستمی «صف انتظار نوبت» فعال شده و کاربران می‌توانند مراجعین را در صف عمومی یا صف اختصاصی سرویس‌ها ثبت کنند.
                                </p>
                            </div>

                            <div x-show="queueEnabled" x-collapse>
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <label class="{{ $labelClass }}">حداکثر ظرفیت صف (اختیاری)</label>
                                    <div class="relative">
                                        <input type="number" name="queue_max_size" min="1" max="100000" class="{{ $inputClass }} text-center dir-ltr pl-10"
                                               value="{{ old('queue_max_size', $settings->queue_max_size) }}" placeholder="نامحدود (خالی)">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs">نفر</div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">در صورت خالی بودن، صف بدون محدودیت ظرفیت خواهد بود.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت ۵: تنظیمات Scope --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">محدوده دسترسی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین سطح دسترسی (Scopes)</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            @foreach([
                                'category_management_scope' => 'مدیریت دسته‌ها',
                                'form_management_scope' => 'مدیریت فرم‌ها',
                                'service_category_selection_scope' => 'انتخاب دسته در سرویس',
                                'service_form_selection_scope' => 'انتخاب فرم در سرویس'
                            ] as $field => $label)
                                <div>
                                    <label class="{{ $labelClass }}">{{ $label }}</label>
                                    <div class="relative">
                                        <select name="{{ $field }}" class="{{ $selectClass }}">
                                            <option value="ALL" @selected(old($field, $settings->$field)==='ALL')>همه (Global)</option>
                                            <option value="OWN" @selected(old($field, $settings->$field)==='OWN')>شخصی (Own)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="sticky top-6 z-10">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            ذخیره تمام تغییرات
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-3">
                            تغییرات بلافاصله اعمال خواهند شد.
                        </p>
                    </div>
                </div>
            </div>
            <div x-show="activeTab === 'cure'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    {{-- کارت: مدیریت وضعیت‌های طرح درمان --}}
                    <div class="{{ $cardClass }}" x-data="{
                        statuses: @js(old('cure_statuses', $settings->cure_statuses ?? [])),
                        roles: @js(($roles ?? collect())->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->toArray()),
                        addStatus() {
                            const id = 'status_' + Date.now();
                            this.statuses.push({
                                id: id,
                                name: 'وضعیت جدید',
                                color: '#6b7280',
                                order: this.statuses.length + 1,
                                allowed_roles: [],
                                allowed_from: []
                            });
                        },
                        removeStatus(index) {
                            this.statuses.splice(index, 1);
                        }
                    }">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            </div>
                            <div class="flex-1 flex justify-between items-center">
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت وضعیت‌های طرح درمان</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی مراحل طرح درمان، نقش‌های مجاز و وابستگی‌های انتقال</p>
                                </div>
                                <button type="button" @click="addStatus()" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors text-[11px] font-semibold flex items-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    افزودن وضعیت جدید
                                </button>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="space-y-4">
                                <template x-for="(status, index) in statuses" :key="status.id">
                                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/10 space-y-4 relative">
                                        <!-- Delete Button -->
                                        <button type="button" @click="removeStatus(index)" class="absolute top-4 left-4 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-all" x-show="!['draft', 'confirmed'].includes(status.id)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <!-- ID -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">شناسه وضعیت</label>
                                                <input type="text" :name="'cure_statuses['+index+'][id]'" x-model="status.id" class="{{ $inputClass }} text-xs font-mono py-1.5" :readonly="['draft', 'confirmed'].includes(status.id)" required>
                                            </div>
                                            
                                            <!-- Name -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">نام وضعیت</label>
                                                <input type="text" :name="'cure_statuses['+index+'][name]'" x-model="status.name" class="{{ $inputClass }} text-xs py-1.5" required>
                                            </div>
                                            
                                            <!-- Color -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">رنگ وضعیت</label>
                                                <div class="flex gap-2">
                                                    <input type="color" :name="'cure_statuses['+index+'][color]'" x-model="status.color" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 cursor-pointer shrink-0">
                                                    <input type="text" x-model="status.color" class="{{ $inputClass }} text-xs text-center font-mono py-1.5">
                                                </div>
                                            </div>
                                            
                                            <!-- Order -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">ترتیب نمایش</label>
                                                <input type="number" :name="'cure_statuses['+index+'][order]'" x-model.number="status.order" class="{{ $inputClass }} text-xs text-center py-1.5">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Allowed Roles -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">نقش‌های مجاز برای ثبت/تغییر به این وضعیت (خالی = همه)</label>
                                                <template x-for="rId in status.allowed_roles" :key="rId">
                                                    <input type="hidden" :name="'cure_statuses['+index+'][allowed_roles][]'" :value="rId">
                                                </template>
                                                <div class="flex flex-wrap gap-1 p-1.5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 min-h-10">
                                                    <template x-for="role in roles" :key="role.id">
                                                        <button type="button" @click="
                                                            const rIdNum = Number(role.id);
                                                            if (!status.allowed_roles) status.allowed_roles = [];
                                                            if (status.allowed_roles.map(Number).includes(rIdNum)) {
                                                                status.allowed_roles = status.allowed_roles.map(Number).filter(r => r !== rIdNum);
                                                            } else {
                                                                status.allowed_roles.push(rIdNum);
                                                            }
                                                        " class="px-2 py-0.5 rounded-md text-[10px] font-semibold transition-all"
                                                        :class="status.allowed_roles && status.allowed_roles.map(Number).includes(Number(role.id)) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                            <span x-text="role.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            
                                            <!-- Allowed From -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 mb-1">تغییر وضعیت فقط از (وابستگی)</label>
                                                <template x-for="fId in status.allowed_from" :key="fId">
                                                    <input type="hidden" :name="'cure_statuses['+index+'][allowed_from][]'" :value="fId">
                                                </template>
                                                <div class="flex flex-wrap gap-1 p-1.5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 min-h-10">
                                                    <template x-for="otherSt in statuses.filter(s => s.id !== status.id)" :key="otherSt.id">
                                                        <button type="button" @click="
                                                            if (!status.allowed_from) status.allowed_from = [];
                                                            if (status.allowed_from.includes(otherSt.id)) {
                                                                status.allowed_from = status.allowed_from.filter(f => f !== otherSt.id);
                                                            } else {
                                                                status.allowed_from.push(otherSt.id);
                                                            }
                                                        " class="px-2 py-0.5 rounded-md text-[10px] font-semibold transition-all"
                                                        :class="status.allowed_from && status.allowed_from.includes(otherSt.id) ? 'bg-teal-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                            <span x-text="otherSt.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600 dark:text-violet-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات پایه طرح درمان</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی رفتار و وضعیت پیش‌فرض طرح‌ها</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">وضعیت پیش‌فرض طرح جدید</label>
                                    <div class="relative">
                                        <select name="cure_default_status" class="{{ $selectClass }}">
                                            <option value="draft" @selected(old('cure_default_status', $settings->cure_default_status ?? 'draft') === 'draft')>پیش‌نویس (الزام به ذخیره قبل از تأیید)</option>
                                            <option value="draft_direct" @selected(old('cure_default_status', $settings->cure_default_status ?? 'draft') === 'draft_direct')>پیش‌نویس (با امکان تأیید مستقیم)</option>
                                            <option value="confirmed" @selected(old('cure_default_status', $settings->cure_default_status ?? 'draft') === 'confirmed')>تأیید شده</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">وضعیتی که هنگام ایجاد طرح جدید به آن اختصاص می‌یابد.</p>
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">اجازه ویرایش طرح تأیید شده</label>
                                    <div class="relative">
                                        <select name="cure_allow_edit_confirmed" class="{{ $selectClass }}">
                                            <option value="1" @selected((int)old('cure_allow_edit_confirmed', $settings->cure_allow_edit_confirmed) === 1)>بله (مجاز است)</option>
                                            <option value="0" @selected((int)old('cure_allow_edit_confirmed', $settings->cure_allow_edit_confirmed) === 0)>خیر (محدود شود)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">در صورت غیرفعال بودن، طرح‌های تأیید شده فقط قابل مشاهده خواهند بود.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تخفیف و مالی طرح درمان</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مدیریت محدودیت‌های تخفیف و محاسبات مالی</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">اجازه اعمال تخفیف</label>
                                    <div class="relative">
                                        <select name="cure_allow_discount" class="{{ $selectClass }}">
                                            <option value="1" @selected((int)old('cure_allow_discount', $settings->cure_allow_discount) === 1)>فعال</option>
                                            <option value="0" @selected((int)old('cure_allow_discount', $settings->cure_allow_discount) === 0)>غیرفعال</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">امکان اعمال تخفیف روی طرح‌های درمان.</p>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">حداکثر درصد تخفیف</label>
                                    <div class="relative">
                                        <input type="number" name="cure_max_discount_percent" min="0" max="100"
                                               class="{{ $inputClass }} text-center dir-ltr pl-10"
                                               value="{{ old('cure_max_discount_percent', $settings->cure_max_discount_percent ?? 100) }}">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">بالاترین درصد تخفیف قابل اعمال. (۱۰۰ = بدون محدودیت)</p>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">نوع تخفیف مجاز</label>
                                    <div class="relative">
                                        <select name="cure_discount_type" class="{{ $selectClass }}">
                                            <option value="amount" @selected(old('cure_discount_type', $settings->cure_discount_type ?? 'amount') === 'amount')>مبلغ ثابت</option>
                                            <option value="percent" @selected(old('cure_discount_type', $settings->cure_discount_type ?? 'amount') === 'percent')>درصدی</option>
                                            <option value="both" @selected(old('cure_discount_type', $settings->cure_discount_type ?? 'amount') === 'both')>هر دو (مبلغ و درصد)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">محاسبه خودکار مالیات روی طرح</label>
                                    <div class="relative">
                                        <select name="cure_auto_tax" class="{{ $selectClass }}">
                                            <option value="1" @selected((int)old('cure_auto_tax', $settings->cure_auto_tax) === 1)>فعال (بر اساس تنظیمات مالیات)</option>
                                            <option value="0" @selected((int)old('cure_auto_tax', $settings->cure_auto_tax) === 0)>غیرفعال</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">در صورت فعال بودن، مالیات تنظیمات عمومی روی طرح نیز محاسبه می‌شود.</p>
                                </div>
                            </div>

                            {{-- Alert for discount --}}
                            <div class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-800/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-300">توجه</p>
                                    <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 leading-relaxed">
                                        محدودیت تخفیف فقط روی رابط کاربری اعمال می‌شود. برای امنیت بیشتر، حتماً سمت سرور نیز اعتبارسنجی شود.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">ضمانت و گارانتی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تنظیمات ضمانت پیش‌فرض برای آیتم‌های طرح</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">فعال‌سازی ضمانت</label>
                                    <div class="relative">
                                        <select name="cure_warranty_enabled" class="{{ $selectClass }}">
                                            <option value="1" @selected((int)old('cure_warranty_enabled', $settings->cure_warranty_enabled) === 1)>فعال</option>
                                            <option value="0" @selected((int)old('cure_warranty_enabled', $settings->cure_warranty_enabled) === 0)>غیرفعال</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">نمایش فیلد ضمانت هنگام ثبت آیتم طرح درمان.</p>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">مدت ضمانت پیش‌فرض (ماه)</label>
                                    <div class="relative">
                                        <input type="number" name="cure_default_warranty_months" min="0"
                                               class="{{ $inputClass }} text-center dir-ltr pl-14"
                                               value="{{ old('cure_default_warranty_months', $settings->cure_default_warranty_months ?? 6) }}">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">ماه</div>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2">مدت زمان پیش‌فرض ضمانت برای آیتم‌های جدید. (۰ = بدون ضمانت)</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="{{ $labelClass }}">متن ضمانت پیش‌فرض</label>
                                    <input type="text" name="cure_default_warranty_text"
                                           class="{{ $inputClass }}"
                                           value="{{ old('cure_default_warranty_text', $settings->cure_default_warranty_text ?? '') }}"
                                           placeholder="مثال: گارانتی تعویض رایگان تا ۶ ماه">
                                    <p class="text-[11px] text-gray-400 mt-2">متنی که به صورت پیش‌فرض در فیلد ضمانت نمایش داده می‌شود.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-8">
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">یادداشت پیش‌فرض</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">متن پیش‌فرض برای یادداشت طرح</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="{{ $labelClass }}">متن یادداشت پیش‌فرض</label>
                                <textarea name="cure_default_notes" rows="4"
                                          class="{{ $inputClass }} resize-none"
                                          placeholder="مثال: لطفاً قبل از شروع درمان تأییدیه بیمار دریافت شود.">{{ old('cure_default_notes', $settings->cure_default_notes ?? '') }}</textarea>
                                <p class="text-[11px] text-gray-400 mt-2">این متن به صورت پیش‌فرض در فیلد یادداشت طرح جدید نمایش داده می‌شود.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">الزام یادداشت</label>
                                <div class="relative">
                                    <select name="cure_require_notes" class="{{ $selectClass }}">
                                        <option value="0" @selected((int)old('cure_require_notes', $settings->cure_require_notes) === 0)>اختیاری</option>
                                        <option value="1" @selected((int)old('cure_require_notes', $settings->cure_require_notes) === 1)>الزامی</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت: تنظیمات نقشه دندانی --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-900/20 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">نقشه دندانی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تنظیمات رفتار نقشه دندانی</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="{{ $labelClass }}">سیستم شماره‌گذاری</label>
                                <div class="relative">
                                    <select name="cure_tooth_numbering_system" class="{{ $selectClass }}">
                                        <option value="universal" @selected(old('cure_tooth_numbering_system', $settings->cure_tooth_numbering_system ?? 'universal') === 'universal')>یونیورسال (Universal)</option>
                                        <option value="palmer" @selected(old('cure_tooth_numbering_system', $settings->cure_tooth_numbering_system ?? 'universal') === 'palmer')>پالمر (Palmer)</option>
                                        <option value="fdi" @selected(old('cure_tooth_numbering_system', $settings->cure_tooth_numbering_system ?? 'universal') === 'fdi')>FDI (دو رقمی)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">سیستم شماره‌گذاری دندان‌ها در نقشه.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">برجسته‌سازی خودکار دندان‌ها</label>
                                <div class="relative">
                                    <select name="cure_auto_highlight_teeth" class="{{ $selectClass }}">
                                        <option value="1" @selected((int)old('cure_auto_highlight_teeth', $settings->cure_auto_highlight_teeth) === 1)>فعال (دندان‌های طرح برجسته شوند)</option>
                                        <option value="0" @selected((int)old('cure_auto_highlight_teeth', $settings->cure_auto_highlight_teeth) === 0)>غیرفعال</option>
                                    </select>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">دندان‌هایی که در طرح ثبت شده‌اند با رنگ متفاوت نمایش داده شوند.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">نمایش فیلتر دندان‌های انتخاب شده</label>
                                <div class="relative">
                                    <select name="cure_show_tooth_filter" class="{{ $selectClass }}">
                                        <option value="1" @selected((int)old('cure_show_tooth_filter', $settings->cure_show_tooth_filter) === 1)>نمایش دکمه فیلتر</option>
                                        <option value="0" @selected((int)old('cure_show_tooth_filter', $settings->cure_show_tooth_filter) === 0)>مخفی</option>
                                    </select>
                                </div>
                            </div>

                             <div>
                                 <label class="{{ $labelClass }} flex justify-between">
                                     <span>دسته‌های مجاز در طرح درمان</span>
                                     <span class="text-[10px] font-normal text-gray-400">چند انتخابی</span>
                                 </label>
                                 <div x-data="{
                                     open: false,
                                     selected: (@js(old('cure_allowed_categories', $settings->cure_allowed_categories ?? [])) || []).map(Number),
                                     options: @js(($categories ?? collect())->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray()),
                                     search: '',
                                     toggle(id) {
                                         id = Number(id);
                                         if (this.selected.includes(id)) {
                                             this.selected = this.selected.filter(item => item !== id);
                                         } else {
                                             this.selected.push(id);
                                         }
                                     },
                                     isSelected(id) {
                                         return this.selected.includes(Number(id));
                                     },
                                     get selectedLabels() {
                                         if (!this.selected || this.selected.length === 0) return 'همه دسته‌ها (پیش‌فرض)';
                                         return this.options
                                             .filter(opt => this.selected.includes(Number(opt.id)))
                                             .map(opt => opt.name)
                                             .join('، ');
                                     },
                                     get filteredOptions() {
                                         if (!this.search) return this.options;
                                         return this.options.filter(opt => opt.name.toLowerCase().includes(this.search.toLowerCase()));
                                     }
                                 }" class="relative">
                                     <!-- Hidden inputs for form submission -->
                                     <template x-for="id in selected" :key="id">
                                         <input type="hidden" name="cure_allowed_categories[]" :value="id">
                                     </template>
                                     <!-- Fallback when none is selected to submit an empty array/value -->
                                     <input type="hidden" name="cure_allowed_categories[]" value="" x-show="selected.length === 0">

                                     <!-- Trigger Button -->
                                     <button type="button" @click="open = !open" @click.away="open = false"
                                             class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 text-right">
                                         <span class="truncate text-gray-700 dark:text-gray-300" x-text="selectedLabels"></span>
                                         <svg class="h-5 w-5 text-gray-400 mr-2 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                         </svg>
                                     </button>

                                     <!-- Dropdown Content -->
                                     <div x-show="open" x-cloak
                                          x-transition:enter="transition ease-out duration-100"
                                          x-transition:enter-start="transform opacity-0 scale-95"
                                          x-transition:enter-end="transform opacity-100 scale-100"
                                          x-transition:leave="transition ease-in duration-75"
                                          x-transition:leave-start="transform opacity-100 scale-100"
                                          x-transition:leave-end="transform opacity-0 scale-95"
                                          class="absolute z-50 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800 max-h-72 overflow-hidden flex flex-col">
                                         
                                         <!-- Search input -->
                                         <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                             <input type="text" x-model="search" placeholder="جستجوی دسته‌بندی..."
                                                    class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                         </div>

                                         <!-- Options list -->
                                         <div class="overflow-y-auto flex-1 max-h-48 p-1 scrollbar-thin">
                                             <template x-for="opt in filteredOptions" :key="opt.id">
                                                 <button type="button" @click="toggle(opt.id)"
                                                         class="w-full flex items-center justify-between px-3 py-2 text-right text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                                         :class="isSelected(opt.id) ? 'bg-indigo-50/50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                     <span x-text="opt.name"></span>
                                                     <span x-show="isSelected(opt.id)" class="text-indigo-600 dark:text-indigo-400">
                                                         <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                         </svg>
                                                     </span>
                                                 </button>
                                             </template>
                                             <div x-show="filteredOptions.length === 0" class="text-center text-gray-400 text-xs py-4">
                                                 هیچ دسته‌ای پیدا نشد.
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <p class="text-[11px] text-gray-400 mt-2">دسته‌هایی از سرویس که در طرح درمان مجاز هستند. اگر هیچ دسته‌ای انتخاب نشود، تمام سرویس‌ها در دسترس خواهند بود.</p>
                             </div>

                    {{-- کارت: نقش‌های قابل انتساب در طرح درمان --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">نقش‌های قابل انتساب</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">کاربران دارای چه نقش‌هایی در طرح درمان منتسب شوند</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="{{ $labelClass }} flex justify-between">
                                    <span>نقش‌های مجاز برای انتساب</span>
                                    <span class="text-[10px] font-normal text-gray-400">چند انتخابی</span>
                                </label>
                                <div x-data="{
                                    open: false,
                                    selected: (@js(old('cure_assignable_roles', $settings->cure_assignable_roles ?? [])) || []).map(Number),
                                    options: @js(($roles ?? collect())->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->toArray()),
                                    search: '',
                                    toggle(id) {
                                        id = Number(id);
                                        if (this.selected.includes(id)) {
                                            this.selected = this.selected.filter(item => item !== id);
                                        } else {
                                            this.selected.push(id);
                                        }
                                    },
                                    isSelected(id) {
                                        return this.selected.includes(Number(id));
                                    },
                                    get selectedLabels() {
                                        if (!this.selected || this.selected.length === 0) return 'هیچ نقشی انتخاب نشده';
                                        return this.options
                                            .filter(opt => this.selected.includes(Number(opt.id)))
                                            .map(opt => opt.name)
                                            .join('، ');
                                    },
                                    get filteredOptions() {
                                        if (!this.search) return this.options;
                                        return this.options.filter(opt => opt.name.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" class="relative">
                                    <!-- Hidden inputs for form submission -->
                                    <template x-for="id in selected" :key="id">
                                        <input type="hidden" name="cure_assignable_roles[]" :value="id">
                                    </template>
                                    <input type="hidden" name="cure_assignable_roles[]" value="" x-show="selected.length === 0">

                                    <!-- Trigger Button -->
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 text-right">
                                        <span class="truncate text-gray-700 dark:text-gray-300" x-text="selectedLabels"></span>
                                        <svg class="h-5 w-5 text-gray-400 mr-2 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Dropdown Content -->
                                    <div x-show="open" x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute z-50 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800 max-h-72 overflow-hidden flex flex-col">
                                        
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                            <input type="text" x-model="search" placeholder="جستجوی نقش..."
                                                   class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                        </div>

                                        <div class="overflow-y-auto flex-1 max-h-48 p-1 scrollbar-thin">
                                            <template x-for="opt in filteredOptions" :key="opt.id">
                                                <button type="button" @click="toggle(opt.id)"
                                                        class="w-full flex items-center justify-between px-3 py-2 text-right text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                                        :class="isSelected(opt.id) ? 'bg-indigo-50/50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span x-text="opt.name"></span>
                                                    <span x-show="isSelected(opt.id)" class="text-indigo-600 dark:text-indigo-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        </div>
                    </div>

                    {{-- دکمه ذخیره --}}
                    <div class="sticky top-6 z-10">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            ذخیره تنظیمات طرح درمان
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-3">
                            تغییرات بلافاصله اعمال خواهند شد.
                        </p>
                    </div>
                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  SCHEDULE TAB                             --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'schedule'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ستون چپ --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">برنامه زمانی سراسری</h2>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">Global Schedule</span>
                        </div>
                        <p class="text-xs text-gray-500 hidden sm:block">تنظیم ساعات کاری پیش‌فرض برای تمام روزها</p>
                    </div>

                    @php
                        $dayNames = [
                            0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه',
                            3 => 'سه‌شنبه', 4 => 'چهارشنبه', 5 => 'پنج‌شنبه', 6 => 'جمعه',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 gap-4">
                        @for($d = 0; $d <= 6; $d++)
                            @php
                                $r = $rules[$d] ?? null;
                                $isClosed = old('rules.'.$d.'.is_closed', ($r?->is_closed ?? false) ? '1' : '0');
                                $start    = old('rules.'.$d.'.work_start_local', $r?->work_start_local);
                                $end      = old('rules.'.$d.'.work_end_local', $r?->work_end_local);
                                $dur      = old('rules.'.$d.'.slot_duration_minutes', $r?->slot_duration_minutes);
                                $capSlot  = old('rules.'.$d.'.capacity_per_slot', $r?->capacity_per_slot);
                                $capDay   = old('rules.'.$d.'.capacity_per_day', $r?->capacity_per_day);
                                $bufBefore = old('rules.'.$d.'.buffer_before_minutes', $r?->buffer_before_minutes);
                                $bufAfter  = old('rules.'.$d.'.buffer_after_minutes', $r?->buffer_after_minutes);

                                $breaksArray = [];
                                if (old('rules.'.$d.'.breaks')) {
                                    $breaksArray = old('rules.'.$d.'.breaks');
                                } elseif ($r?->breaks_json) {
                                    $breaksArray = is_array($r->breaks_json) ? $r->breaks_json : json_decode($r->breaks_json, true);
                                }
                            @endphp

                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:border-indigo-300 dark:hover:border-indigo-700 shadow-sm group"
                                 x-data="{ isOpen: '{{ $isClosed }}' === '0' }">

                                {{-- هدر روز --}}
                                <div class="px-5 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between cursor-pointer" @click="isOpen = !isOpen; $refs.statusSelect.value = isOpen ? '0' : '1'">
                                    <div class="flex items-center gap-4">
                                        <span class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-sm transition-colors duration-300"
                                              :class="isOpen ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 shadow-sm' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                                            {{ $d + 1 }}
                                        </span>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $dayNames[$d] ?? ('Day '.$d) }}</h3>
                                            <span class="text-xs font-medium transition-colors duration-300" :class="isOpen ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500'">
                                                <span x-text="isOpen ? 'ساعات کاری فعال است' : 'روز تعطیل'"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3" @click.stop>
                                        <div class="relative">
                                            <select x-ref="statusSelect"
                                                    name="rules[{{ $d }}][is_closed]"
                                                    x-model="isOpen"
                                                    x-on:change="isOpen = $event.target.value === '0'"
                                                    class="h-9 pl-3 pr-8 rounded-lg border-gray-300 bg-white text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 cursor-pointer shadow-sm">
                                                <option value="0">باز (فعال)</option>
                                                <option value="1">تعطیل</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2 text-gray-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>
                                        </div>

                                        <div class="w-8 h-8 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center transition-colors cursor-pointer" @click="isOpen = !isOpen">
                                            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" :class="isOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- محتوای روز --}}
                                <div x-show="isOpen" x-collapse class="border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <div class="p-5 space-y-6">
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">شروع کار</label>
                                                <div class="relative">
                                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_start_local]"
                                                           class="{{ $inputClass }} text-center dir-ltr font-mono" value="{{ $start }}" placeholder="09:00">
                                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">پایان کار</label>
                                                <div class="relative">
                                                    <input type="text" data-jdp-only-time name="rules[{{ $d }}][work_end_local]"
                                                           class="{{ $inputClass }} text-center dir-ltr font-mono" value="{{ $end }}" placeholder="17:00">
                                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">مدت اسلات (دقیقه)</label>
                                                <input type="number" name="rules[{{ $d }}][slot_duration_minutes]"
                                                       class="{{ $inputClass }} text-center" value="{{ $dur }}" placeholder="{{ $settings->default_slot_duration_minutes }}">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">ظرفیت اسلات</label>
                                                <input type="number" name="rules[{{ $d }}][capacity_per_slot]"
                                                       class="{{ $inputClass }} text-center" value="{{ $capSlot }}" placeholder="{{ $settings->default_capacity_per_slot }}">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">ظرفیت کل روز</label>
                                                <input type="number" name="rules[{{ $d }}][capacity_per_day]"
                                                       class="{{ $inputClass }} text-center" value="{{ $capDay }}" placeholder="{{ $settings->default_capacity_per_day }}">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">استراحت قبل (دقیقه)</label>
                                                <input type="number" name="rules[{{ $d }}][buffer_before_minutes]"
                                                       class="{{ $inputClass }} text-center" value="{{ $bufBefore }}" placeholder="{{ $settings->default_buffer_before_minutes ?? 0 }}">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">استراحت بعد (دقیقه)</label>
                                                <input type="number" name="rules[{{ $d }}][buffer_after_minutes]"
                                                       class="{{ $inputClass }} text-center" value="{{ $bufAfter }}" placeholder="{{ $settings->default_buffer_after_minutes ?? 0 }}">
                                            </div>
                                        </div>

                                        {{-- استراحت‌ها --}}
                                        <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50">
                                            <div class="flex items-center justify-between mb-3">
                                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    زمان‌های استراحت (Breaks)
                                                </label>
                                                <button type="button" onclick="addBreak({{ $d }})"
                                                        class="text-[11px] px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-all shadow-sm flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                    افزودن استراحت
                                                </button>
                                            </div>

                                            <div id="breaks-{{ $d }}" class="space-y-2">
                                                @if(is_array($breaksArray) && count($breaksArray))
                                                    @foreach($breaksArray as $i => $br)
                                                        <div class="flex items-center gap-3 break-row bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm group/break">
                                                            <span class="text-xs font-medium text-gray-500 w-6 text-center">از</span>
                                                            <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][start_local]"
                                                                   class="w-24 h-9 rounded-lg border-gray-200 bg-gray-50 text-center text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200"
                                                                   value="{{ $br['start_local'] ?? '' }}" placeholder="شروع">
                                                            <span class="text-xs font-medium text-gray-500 w-6 text-center">تا</span>
                                                            <input type="text" data-jdp-only-time name="rules[{{ $d }}][breaks][{{ $i }}][end_local]"
                                                                   class="w-24 h-9 rounded-lg border-gray-200 bg-gray-50 text-center text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200"
                                                                   value="{{ $br['end_local'] ?? '' }}" placeholder="پایان">

                                                            <div class="flex-1"></div>

                                                            <button type="button" onclick="this.closest('.break-row').remove()"
                                                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20 transition-colors opacity-0 group-hover/break:opacity-100">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-xs text-gray-400 italic py-3 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg bg-white/50 dark:bg-gray-800/50">
                                                        هیچ زمان استراحتی تعریف نشده است
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- ستون راست --}}
                <div class="space-y-8">

                    {{-- کارت: راهنمای سریع --}}
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">راهنمای برنامه</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400">۱</span>
                                    </span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">روزهای کاری خود را فعال و ساعات شروع و پایان را تعیین کنید.</p>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400">۲</span>
                                    </span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">مدت هر اسلات و ظرفیت آن را بر اساس نیاز تنظیم کنید.</p>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400">۳</span>
                                    </span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">زمان‌های استراحت (ناهار و ...) را اضافه کنید تا در آن بازه نوبت ثبت نشود.</p>
                                </div>
                            </div>

                            <div class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl p-3">
                                <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                                    <span class="font-bold">نکته:</span> این تنظیمات به عنوان پیش‌فرض عمل می‌کنند. هر ارائه‌دهنده می‌تواند برنامه مستقل خود را داشته باشد.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- دکمه ذخیره --}}
                    <div class="sticky top-6 z-10">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            ذخیره برنامه زمانی
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-3">
                            تغییرات بلافاصله اعمال خواهند شد.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  APPEARANCE TAB                           --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'appearance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- ستون اصلی --}}
                <div class="lg:col-span-2 space-y-8">
                    <div class="{{ $cardClass }}">
                        <div class="{{ $headerClass }}">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات نمایش در داشبورد مشتریان</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">کنترل بخش‌ها و اطلاعات قابل مشاهده برای بیماران/مشتریان</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">

                            {{-- گزینه ۱: نمایش توضیحات سرویس --}}
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                <div class="space-y-1">
                                    <label class="text-sm font-bold text-gray-900 dark:text-white block">
                                        نمایش توضیحات سرویس در داشبورد مشتریان/بیماران
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        در صورت فعال بودن، توضیحات ثبتی سرویس در صفحه جزئیات نوبت مشتریان به آن‌ها نمایش داده می‌شود.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="show_service_description" value="0">
                                    <input type="checkbox" name="show_service_description" value="1" @checked(old('show_service_description', $settings->show_service_description ?? true)) class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            {{-- گزینه ۲: نمایش اطلاعات تکمیلی --}}
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                <div class="space-y-1">
                                    <label class="text-sm font-bold text-gray-900 dark:text-white block">
                                        نمایش اطلاعات تکمیلی در داشبورد مشتریان/بیماران
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        در صورت فعال بودن، پاسخ‌های فرم اختصاصی نوبت (اطلاعات تکمیلی) در پرتال کلاینت قابل مشاهده خواهد بود.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="show_supplementary_info" value="0">
                                    <input type="checkbox" name="show_supplementary_info" value="1" @checked(old('show_supplementary_info', $settings->show_supplementary_info ?? true)) class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            {{-- گزینه ۳: نمایش ارائه‌دهنده --}}
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                <div class="space-y-1">
                                    <label class="text-sm font-bold text-gray-900 dark:text-white block">
                                        نمایش ارائه‌دهنده در داشبورد مشتریان/بیماران
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        در صورت فعال بودن، نام و مشخصات {{ config('booking.labels.provider') }} در صفحه نوبت کلاینت نمایش داده می‌شود.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="show_provider_info" value="0">
                                    <input type="checkbox" name="show_provider_info" value="1" @checked(old('show_provider_info', $settings->show_provider_info ?? true)) class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ستون راست --}}
                <div class="space-y-8">
                    <div class="sticky top-6 z-10">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            ذخیره تنظیمات ظاهری
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-3">
                            تغییرات بلافاصله اعمال خواهند شد.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  HOLIDAYS TAB                             --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'holidays'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
                <x-booking::jalali-holiday-calendar
                    scopeType="GLOBAL"
                    :storeUrl="route('user.booking.settings.holidays.store')"
                    :batchUrl="route('user.booking.settings.holidays.batch')"
                    :exceptions="$globalExceptions"
                />
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  SYNC SERVICES TAB                        --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'sync'"
                 x-data="serviceSyncComponent()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="space-y-8">

                <input type="hidden" name="sync_groups" :value="JSON.stringify(groups)">

                {{-- راهنما --}}
                <div class="bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">راهنمای هماهنگ‌سازی سرویس‌ها</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            در این بخش می‌توانید سرویس‌هایی که از نظر زمان و ظرفیت هم‌پوشانی دارند را در یک ردیف هماهنگی قرار دهید.
                            هنگامی که برای یکی از سرویس‌های موجود در یک ردیف نوبت ثبت شود، همان اسلات زمانی برای سایر سرویس‌های هم‌ردیف مسدود می‌گردد.
                            سرویس‌هایی که در هیچ ردیفی قرار نگرفته‌اند روند عادی خود را خواهند داشت.
                        </p>
                    </div>
                </div>

                {{-- بخش ۱: لیست سرویس‌ها و وضعیت هماهنگی --}}
                <div class="{{ $cardClass }}">
                    <div class="{{ $headerClass }}">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">۱. سرویس‌ها</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشاهده وضعیت هماهنگی سرویس‌های فعال سیستم</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="service in services" :key="service.id">
                                <div class="p-4 rounded-xl border transition-all flex items-center justify-between gap-3"
                                     :class="getServiceGroupNames(service.id).length > 0
                                        ? 'border-indigo-200 bg-indigo-50/40 dark:bg-indigo-950/20 dark:border-indigo-800/40'
                                        : 'border-gray-200 bg-gray-50/50 dark:bg-gray-900/40 dark:border-gray-700'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-3 h-3 rounded-full flex-shrink-0"
                                             :class="getServiceGroupNames(service.id).length > 0 ? 'bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                        <div class="min-w-0">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate block" x-text="service.name"></span>
                                            <template x-if="getServiceGroupNames(service.id).length > 0">
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <template x-for="gName in getServiceGroupNames(service.id)" :key="gName">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300" x-text="gName"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="getServiceGroupNames(service.id).length === 0">
                                                <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 block">روند فعلی (بدون هماهنگی)</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- بخش ۲: ایجاد ردیف‌ها و قرار دادن سرویس‌ها --}}
                <div class="{{ $cardClass }}">
                    <div class="{{ $headerClass }} justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">۲. ردیف‌های هماهنگی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعریف ردیف و قرار دادن سرویس‌های هم‌نیازمند در هر ردیف</p>
                            </div>
                        </div>

                        <button type="button" @click="addGroup()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            افزودن ردیف جدید
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <template x-if="groups.length === 0">
                            <div class="text-center py-12 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50/50 dark:bg-gray-900/20">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">هیچ ردیف هماهنگی تعریف نشده است</p>
                                <p class="text-xs text-gray-400 mt-1">با کلیک روی دکمه «افزودن ردیف جدید»، سرویس‌ها را با یکدیگر هماهنگ کنید.</p>
                                <button type="button" @click="addGroup()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 transition-colors">
                                    + ایجاد اولین ردیف
                                </button>
                            </div>
                        </template>

                        <template x-for="(group, gIdx) in groups" :key="group.id">
                            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm space-y-4 relative group/row transition-all hover:border-indigo-300 dark:hover:border-indigo-700">
                                {{-- هدر ردیف --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                                    <div class="flex items-center gap-3 flex-1">
                                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-black" x-text="gIdx + 1"></span>
                                        <input type="text" x-model="group.name"
                                               class="text-sm font-bold text-gray-900 dark:text-white bg-transparent border-b border-dashed border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:outline-none px-1 py-0.5 w-48"
                                               placeholder="عنوان ردیف (مثلاً: هماهنگی ۱)">
                                    </div>

                                    <div class="flex items-center gap-3">
                                        {{-- فیلتر ارائه‌دهنده --}}
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">ارائه‌دهنده:</label>
                                            <select x-model="group.provider_user_id" class="text-xs rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 dark:text-gray-200 focus:ring-indigo-500">
                                                <option value="">همه ارائه‌دهندگان (سراسر سیستم)</option>
                                                @foreach($providers as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->email ? "({$p->email})" : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="button" @click="removeGroup(gIdx)"
                                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="حذف این ردیف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- انتخاب سرویس‌های این ردیف --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        سرویس‌های موجود در این ردیف:
                                        <span class="text-[11px] font-normal text-gray-400 mr-1">(هر سرویس می‌تواند تنها در یک ردیف فعال باشد)</span>
                                    </label>

                                    <div class="flex flex-wrap gap-2 min-h-[44px] p-3 rounded-xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/80">
                                        <template x-for="service in services" :key="service.id">
                                            <button type="button"
                                                    @click="toggleServiceInGroup(gIdx, service.id)"
                                                    :class="group.service_ids.includes(service.id)
                                                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 font-bold border-indigo-600'
                                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-300'"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs transition-all transform active:scale-95">
                                                <span x-text="service.name"></span>
                                                <template x-if="group.service_ids.includes(service.id)">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                </template>
                                                <template x-if="!group.service_ids.includes(service.id)">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                </template>
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="group.service_ids.length === 1">
                                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1.5 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            برای هماهنگ‌سازی حداقل ۲ سرویس باید انتخاب شوند.
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- دکمه ذخیره‌سازی --}}
                <div class="flex justify-end pt-4">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all transform active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        ذخیره هماهنگ‌سازی سرویس‌ها
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{--                  ADS & BANNERS TAB                        --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'ads'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-8" x-data="adsTabManager()">
                
                {{-- هدر بخش تبلیغات و اتصال به بهینه‌ساز هوشمند هسته --}}
                <div class="p-6 rounded-2xl bg-gradient-to-l from-indigo-500/10 via-purple-500/5 to-transparent border border-indigo-100 dark:border-indigo-900/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                مدیریت تبلیغات و بنرهای سیستم
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    بهینه‌ساز هوشمند WebP فعال
                                </span>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                تصاویر آپلود شده به صورت خودکار با موتور بهینه‌ساز هسته فشرده‌سازی شده و به فرمت پرسرعت WebP تبدیل می‌شوند.
                            </p>
                        </div>
                    </div>

                    @if(auth()->user() && auth()->user()->hasRole('super-admin'))
                        <a href="{{ url('/admin/optimizer-report') }}" target="_blank"
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700/60 transition-colors shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            مشاهده گزارش بهینه‌ساز
                        </a>
                    @endif
                </div>

                {{-- کارت ۱: بخش صفحه پزشکان --}}
                <div class="{{ $cardClass }}">
                    <div class="{{ $headerClass }} justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">۱. بنر اختصاصی صفحه پزشکان (Doctor Profile Banner)</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">نمایش بنر تبلیغاتی یا اطلاع‌رسانی در بالای هدر صفحه اختصاصی هر ارائه‌دهنده</p>
                            </div>
                        </div>

                        {{-- کلید فعال/غیرفعال‌سازی بنر صفحه پزشک --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold" :class="doctorBannerEnabled ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">
                                <span x-text="doctorBannerEnabled ? 'فعال' : 'غیرفعال'"></span>
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="ads_doctor_enabled" value="1" x-model="doctorBannerEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- فیلدهای آپلودر و تنظیمات --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                            {{-- آپلودر ۱: تصویر بنر اصلی (دسکتاپ) --}}
                            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/20 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تصویر بنر دسکتاپ (اصلی)</span>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        پیشنهاد: ۱۹۲۰×۴۰۰ px
                                    </span>
                                </div>

                                {{-- اینپوت فایل دائمی در DOM --}}
                                <input type="file" x-ref="desktopInput" name="ads_doctor_desktop_image" accept="image/jpeg,image/png,image/webp,image/jpg" class="hidden" @change="handleDesktopFile($event)">
                                <input type="hidden" name="delete_ads_doctor_desktop_image" :value="deleteDesktopImage ? '1' : '0'">

                                {{-- ناحیه پیش‌نمایش تصویر دسکتاپ --}}
                                <div x-show="desktopPreviewUrl && !deleteDesktopImage" class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shadow-inner">
                                    <img :src="desktopPreviewUrl" alt="پیش‌نمایش بنر دسکتاپ" class="w-full h-32 object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 backdrop-blur-xs">
                                        <button type="button" @click="$refs.desktopInput.click()" class="px-3 py-1.5 rounded-lg bg-white/90 hover:bg-white text-gray-800 text-xs font-bold cursor-pointer shadow-sm transition-transform active:scale-95">
                                            تغییر تصویر
                                        </button>
                                        <button type="button" @click="removeDesktopImage()" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition-transform active:scale-95">
                                            حذف تصویر
                                        </button>
                                    </div>
                                </div>

                                {{-- ناحیه درگ اند دراپ یا انتخاب فایل در صورت عدم وجود تصویر --}}
                                <div x-show="!desktopPreviewUrl || deleteDesktopImage" @click="$refs.desktopInput.click()" class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-xl p-6 flex flex-col items-center justify-center gap-2 cursor-pointer transition-colors group bg-white dark:bg-gray-800/60">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1-1m6-5l-1.5-1.5a2.25 2.25 0 00-3.182 0L12 12M9 3l-1.5 1.5a2.25 2.25 0 000 3.182L12 12m3-9l-1.5 1.5a2.25 2.25 0 000 3.182L12 12" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">کلیک برای انتخاب یا رها کردن تصویر بنر</span>
                                    <span class="text-[11px] text-gray-400">فرمت‌های مجاز: JPG, PNG, WEBP (حداکثر ۵ مگابایت)</span>
                                </div>
                            </div>

                            {{-- آپلودر ۲: تصویر بنر تفکیک‌شده موبایل (اختیاری) --}}
                            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/20 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تصویر اختصاصی موبایل (اختیاری)</span>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-50 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
                                        پیشنهاد: ۱۰۸۰×۶۰۰ px
                                    </span>
                                </div>

                                {{-- اینپوت فایل دائمی در DOM --}}
                                <input type="file" x-ref="mobileInput" name="ads_doctor_mobile_image" accept="image/jpeg,image/png,image/webp,image/jpg" class="hidden" @change="handleMobileFile($event)">
                                <input type="hidden" name="delete_ads_doctor_mobile_image" :value="deleteMobileImage ? '1' : '0'">

                                {{-- ناحیه پیش‌نمایش تصویر موبایل --}}
                                <div x-show="mobilePreviewUrl && !deleteMobileImage" class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shadow-inner">
                                    <img :src="mobilePreviewUrl" alt="پیش‌نمایش بنر موبایل" class="w-full h-32 object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 backdrop-blur-xs">
                                        <button type="button" @click="$refs.mobileInput.click()" class="px-3 py-1.5 rounded-lg bg-white/90 hover:bg-white text-gray-800 text-xs font-bold cursor-pointer shadow-sm transition-transform active:scale-95">
                                            تغییر تصویر
                                        </button>
                                        <button type="button" @click="removeMobileImage()" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition-transform active:scale-95">
                                            حذف تصویر
                                        </button>
                                    </div>
                                </div>

                                {{-- ناحیه انتخاب فایل موبایل در صورت عدم وجود تصویر --}}
                                <div x-show="!mobilePreviewUrl || deleteMobileImage" @click="$refs.mobileInput.click()" class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-400 rounded-xl p-6 flex flex-col items-center justify-center gap-2 cursor-pointer transition-colors group bg-white dark:bg-gray-800/60">
                                    <div class="w-10 h-10 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">بارگذاری تصویر تفکیک‌شده برای موبایل</span>
                                    <span class="text-[11px] text-gray-400">(در صورت خالی ماندن، تصویر دسکتاپ به صورت خودکار مقیاس داده می‌شود)</span>
                                </div>
                            </div>

                        </div>

                        {{-- فیلدهای لینک و سئو --}}
                        <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/20 space-y-4">
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                تنظیمات مقصد، لینک و دسترسی‌پذیری بنر
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">آدرس لینک بنر (URL)</label>
                                    <input type="text" name="ads_doctor_link" x-model="doctorBannerLink"
                                           class="{{ $inputClass }} text-left ltr font-mono"
                                           placeholder="https://example.com/promo یا /booking">
                                    <p class="text-[11px] text-gray-400 mt-1">در صورت تمایل به کلیک‌پذیر بودن بنر، آدرس اینترنتی یا مسیر داخلی را وارد کنید.</p>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">متن جایگزین بنر (Alt Text)</label>
                                    <input type="text" name="ads_doctor_alt_text" x-model="doctorBannerAlt"
                                           class="{{ $inputClass }}"
                                           placeholder="مثال: جشنواره خدمات تخصصی کلینیک">
                                    <p class="text-[11px] text-gray-400 mt-1">برای سئو و خوانایی مناسب برای موتورهای جستجو</p>
                                </div>
                            </div>

                            <div class="pt-2">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="ads_doctor_open_new_tab" value="1" x-model="doctorBannerOpenNewTab"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">باز شدن لینک در تب جدید مرورگر (target="_blank")</span>
                                </label>
                            </div>
                        </div>

                        {{-- بخش پیش‌نمایش زنده در پروفایل پزشک --}}
                        <div class="p-5 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50/30 dark:bg-indigo-950/20 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    پیش‌نمایش زنده هدر صفحه پزشک
                                </div>

                                <div class="flex items-center gap-1 bg-white dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700 text-xs">
                                    <button type="button" @click="previewViewport = 'desktop'"
                                            :class="previewViewport === 'desktop' ? 'bg-indigo-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                            class="px-3 py-1 rounded-lg transition-colors">
                                        نمای دسکتاپ 💻
                                    </button>
                                    <button type="button" @click="previewViewport = 'mobile'"
                                            :class="previewViewport === 'mobile' ? 'bg-indigo-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                            class="px-3 py-1 rounded-lg transition-colors">
                                        نمای موبایل 📱
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-900/5 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex justify-center">
                                <div :class="previewViewport === 'mobile' ? 'max-w-xs w-full' : 'w-full'" class="transition-all duration-300">
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-200/80 dark:border-gray-700/80 space-y-4">
                                        
                                        {{-- بنر در پیش‌نمایش --}}
                                        <template x-if="doctorBannerEnabled && (previewViewport === 'mobile' ? (mobilePreviewUrl || desktopPreviewUrl) : desktopPreviewUrl) && !deleteDesktopImage">
                                            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm relative group">
                                                <img :src="previewViewport === 'mobile' ? (mobilePreviewUrl && !deleteMobileImage ? mobilePreviewUrl : desktopPreviewUrl) : desktopPreviewUrl"
                                                     :alt="doctorBannerAlt || 'بنر تبلیغاتی'"
                                                     class="w-full h-auto max-h-32 object-cover">
                                                <template x-if="doctorBannerLink">
                                                    <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded bg-black/70 text-[10px] text-white font-mono" x-text="doctorBannerLink"></span>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="!doctorBannerEnabled || (!desktopPreviewUrl && !mobilePreviewUrl) || deleteDesktopImage">
                                            <div class="p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 text-center text-xs text-gray-400">
                                                (بنر در صفحه نمایش داده نمی‌شود - غیرفعال یا تصویری بارگذاری نشده است)
                                            </div>
                                        </template>

                                        {{-- ماک‌آپ مشخصات پزشک در زیر بنر --}}
                                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                                            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0">
                                                پزشک
                                            </div>
                                            <div>
                                                <div class="h-3 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-1.5"></div>
                                                <div class="h-2 w-20 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- دکمه ذخیره‌سازی تب تبلیغات --}}
                <div class="flex justify-end pt-4">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all transform active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        ذخیره تنظیمات تبلیغات و بنرها
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- اسکریپت‌ها --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('serviceSyncComponent', () => ({
                groups: (@json($syncGroups ?? [])).map(g => ({
                    ...g,
                    provider_user_id: (g.provider_user_id !== null && g.provider_user_id !== undefined && g.provider_user_id !== '') ? String(g.provider_user_id) : ''
                })),
                services: @json($services ?? []),
                providers: @json($providers ?? []),

                addGroup() {
                    const nextNum = this.groups.length + 1;
                    this.groups.push({
                        id: 'group_' + Date.now(),
                        name: 'هماهنگی ' + nextNum,
                        provider_user_id: '',
                        service_ids: []
                    });
                },

                removeGroup(index) {
                    if (confirm('آیا از حذف این ردیف هماهنگی اطمینان دارید؟')) {
                        this.groups.splice(index, 1);
                    }
                },

                toggleServiceInGroup(groupIndex, serviceId) {
                    const targetGroup = this.groups[groupIndex];
                    if (!targetGroup) return;

                    const isCurrentlyInTarget = targetGroup.service_ids.includes(serviceId);

                    if (isCurrentlyInTarget) {
                        // Remove from current group
                        targetGroup.service_ids = targetGroup.service_ids.filter(id => id !== serviceId);
                        return;
                    }

                    // Check if this service is already in another group
                    let existingGroupIndex = -1;
                    let existingGroupName = '';

                    for (let i = 0; i < this.groups.length; i++) {
                        if (i !== groupIndex && this.groups[i].service_ids.includes(serviceId)) {
                            existingGroupIndex = i;
                            existingGroupName = this.groups[i].name || ('ردیف ' + (i + 1));
                            break;
                        }
                    }

                    if (existingGroupIndex !== -1) {
                        const sObj = this.services.find(s => s.id === serviceId);
                        const sName = sObj ? sObj.name : 'این سرویس';
                        const proceed = confirm(`سرویس «${sName}» در حال حاضر در «${existingGroupName}» قرار دارد.\nآیا مایلید این سرویس از آن ردیف حذف شده و به «${targetGroup.name}» منتقل شود؟`);

                        if (!proceed) {
                            return; // User canceled
                        }

                        // Remove from existing group
                        this.groups[existingGroupIndex].service_ids = this.groups[existingGroupIndex].service_ids.filter(id => id !== serviceId);
                    }

                    // Add to target group
                    targetGroup.service_ids.push(serviceId);
                },

                getServiceGroupNames(serviceId) {
                    const names = [];
                    for (const group of this.groups) {
                        if (group.service_ids.includes(serviceId)) {
                            names.push(group.name);
                        }
                    }
                    return names;
                }
            }));

            Alpine.data('taxSettings', () => ({
                taxEnabled: '{{ (string)(old('tax_enabled', $settings->tax_enabled ?? false) ? '1' : '0') }}',
                taxType: '{{ old('tax_type', $settings->tax_type ?? 'PERCENT') }}',

                formatPriceOrPercent(event) {
                    let value = event.target.value;
                    let hiddenInput = document.getElementById('tax_amount');

                    if (this.taxType === 'FIXED') {
                        value = value.replace(/[^0-9]/g, '');
                        if (value !== '') {
                            hiddenInput.value = value;
                            event.target.value = parseInt(value).toLocaleString('en-US');
                        } else {
                            hiddenInput.value = '';
                            event.target.value = '';
                        }
                    } else {
                        hiddenInput.value = value;
                    }
                }
            }));

            Alpine.data('adsTabManager', () => ({
                doctorBannerEnabled: @json((bool) ($settings->ads['doctor_page']['enabled'] ?? false)),
                desktopPreviewUrl: @json($settings->doctor_banner_desktop_url),
                mobilePreviewUrl: @json($settings->doctor_banner_mobile_url),
                doctorBannerLink: @json($settings->doctor_banner_link ?? ''),
                doctorBannerOpenNewTab: @json((bool) ($settings->doctor_banner_open_new_tab ?? true)),
                doctorBannerAlt: @json($settings->doctor_banner_alt ?? ''),
                deleteDesktopImage: false,
                deleteMobileImage: false,
                previewViewport: 'desktop',

                handleDesktopFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.desktopPreviewUrl = URL.createObjectURL(file);
                        this.deleteDesktopImage = false;
                    }
                },

                removeDesktopImage() {
                    this.desktopPreviewUrl = null;
                    this.deleteDesktopImage = true;
                    if (this.$refs.desktopInput) {
                        this.$refs.desktopInput.value = '';
                    }
                },

                handleMobileFile(event) {
                    const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                    if (file) {
                        this.mobilePreviewUrl = URL.createObjectURL(file);
                        this.deleteMobileImage = false;
                    }
                },

                removeMobileImage() {
                    this.mobilePreviewUrl = null;
                    this.deleteMobileImage = true;
                    if (this.$refs.mobileInput) {
                        this.$refs.mobileInput.value = '';
                    }
                }
            }));
        })

        function addBreak(day) {
            const container = document.getElementById('breaks-' + day);
            const emptyMsg = container.querySelector('.italic');
            if(emptyMsg) emptyMsg.remove();

            const index = container.querySelectorAll('.break-row').length + Date.now();
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 break-row bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm group/break';

            row.innerHTML = `
                <span class="text-xs font-medium text-gray-500 w-6 text-center">از</span>
                <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][start_local]"
                       class="w-24 h-9 rounded-lg border-gray-200 bg-gray-50 text-center text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200"
                       placeholder="شروع">
                <span class="text-xs font-medium text-gray-500 w-6 text-center">تا</span>
                <input type="text" data-jdp-only-time name="rules[${day}][breaks][${index}][end_local]"
                       class="w-24 h-9 rounded-lg border-gray-200 bg-gray-50 text-center text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200"
                       placeholder="پایان">
                <div class="flex-1"></div>
                <button type="button" onclick="this.closest('.break-row').remove()"
                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            `;
            container.appendChild(row);

            if (window.jalaliDatepicker) {
                jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', hasSecond: false });
            }
        }
    </script>
@endsection
