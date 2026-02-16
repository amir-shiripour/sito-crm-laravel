@extends('layouts.user')

@section('title', 'تنظیمات نوبت‌دهی')

@php
    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

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
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    در این بخش می‌توانید پیکربندی‌های اصلی سیستم نوبت‌دهی، شامل واحد پول، قوانین رزرو، نقش‌های کاربری و برنامه زمانی هفتگی را مدیریت کنید.
                </p>
            </div>

            <div class="flex items-center gap-3">
                 <a href="{{ route('user.booking.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    بازگشت به داشبورد
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex items-start gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
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

        <form method="POST" action="{{ route('user.booking.settings.update') }}" class="space-y-8 pb-24">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ستون چپ (بزرگتر) --}}
                <div class="lg:col-span-2 space-y-8">

                    {{-- کارت ۱: تنظیمات عمومی --}}
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
                                <p class="text-[11px] text-gray-400 mt-2">واحد پولی که در تمام بخش‌های رزرو نمایش داده می‌شود.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">وضعیت رزرو آنلاین</label>
                                <div class="relative">
                                    <select name="global_online_booking_enabled" class="{{ $selectClass }}">
                                        <option value="1" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='1')>فعال (پذیرش رزرو)</option>
                                        <option value="0" @selected((string)old('global_online_booking_enabled', (int)$settings->global_online_booking_enabled)==='0')>غیرفعال (توقف موقت)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">در صورت غیرفعال کردن، هیچ رزرو جدیدی ثبت نخواهد شد.</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">جریان ثبت نوبت (توسط اپراتور)</label>
                                <div class="relative">
                                    <select name="operator_appointment_flow" class="{{ $selectClass }}">
                                        <option value="PROVIDER_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='PROVIDER_FIRST')>
                                            ابتدا انتخاب {{ config('booking.labels.provider') }}
                                        </option>
                                        <option value="SERVICE_FIRST" @selected(old('operator_appointment_flow', $settings->operator_appointment_flow)==='SERVICE_FIRST')>
                                            ابتدا انتخاب سرویس
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">ثبت زمان ورود/خروج</label>
                                <div class="relative">
                                    <select name="allow_appointment_entry_exit_times" class="{{ $selectClass }}">
                                        <option value="1" @selected((string)old('allow_appointment_entry_exit_times', (int)$settings->allow_appointment_entry_exit_times)==='1')>فعال (نمایش فیلدها)</option>
                                        <option value="0" @selected((string)old('allow_appointment_entry_exit_times', (int)$settings->allow_appointment_entry_exit_times)==='0')>غیرفعال (مخفی کردن)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">
                                    امکان ثبت ساعت دقیق ورود و خروج مشتری در جزئیات نوبت.
                                </p>
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
                                <div class="w-full md:w-48 flex-shrink-0">
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

                    {{-- کارت ۳: برنامه زمانی --}}
                    <div class="space-y-4">
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
                                            {{-- سوئیچ تعطیلی --}}
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
                                            {{-- ردیف اول: زمان و تنظیمات --}}
                                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
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
                                            </div>

                                            {{-- ردیف دوم: استراحت‌ها --}}
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

                    {{-- دکمه ذخیره --}}
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
        </form>
    </div>

    {{-- اسکریپت‌ها --}}
    <script>
        function addBreak(day) {
            const container = document.getElementById('breaks-' + day);
            // حذف پیام "بدون زمان استراحت" اگر وجود دارد
            const emptyMsg = container.querySelector('.italic');
            if(emptyMsg) emptyMsg.remove();

            const index = container.querySelectorAll('.break-row').length + Date.now(); // برای یونیک بودن ایندکس در جاوااسکریپت
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 break-row bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm group/break animate-in fade-in slide-in-from-right-2';

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

            // Re-init Jalali Datepicker if available
            if (window.jalaliDatepicker) {
                jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', hasSecond: false });
            }
        }
    </script>
@endsection
