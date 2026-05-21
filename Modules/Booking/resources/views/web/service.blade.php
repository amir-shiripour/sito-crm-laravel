@extends('layouts.web')

@section('title', $service->name)

@push('styles')
    <style>
        /* Smooth transitions for step content */
        .step-content { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        #loading-overlay { backdrop-filter: blur(4px); }
    </style>
@endpush

@section('content')
    <div class="max-w-5xl mx-auto px-6 w-full space-y-8">

        {{-- Global Loading Overlay --}}
        <div id="loading-overlay" class="fixed inset-0 bg-white/60 dark:bg-gray-950/80 z-[100] hidden items-center justify-center transition-all duration-300">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col items-center gap-5 animate-in zoom-in duration-300">
                <svg class="animate-spin w-12 h-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-base font-bold text-gray-700 dark:text-gray-300">در حال پردازش اطلاعات...</span>
            </div>
        </div>

        {{-- Header Navigation --}}
        <div class="flex items-center gap-4 mb-2 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <a href="{{ route('booking.public.index') }}"
               class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 shadow-sm hover:shadow-md hover:translate-x-1 group">
                <svg class="w-5 h-5 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-1 tracking-tight">رزرو: {{ $service->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">لطفاً مراحل زیر را به دقت تکمیل نمایید</p>
            </div>
        </div>

        {{-- Service Info Card --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-xl shadow-gray-200/20 dark:shadow-none overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-1000 delay-100">
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shadow-inner flex-shrink-0 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-1">هزینه نهایی رزرو</div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white">
                            {{ number_format($service->final_price ?? $service->base_price) }} <span class="text-base font-normal text-gray-500 dark:text-gray-400">{{ ($settings->currency_unit ?? 'IRT') === 'IRR' ? 'ریال' : 'تومان' }}</span>
                        </div>
                        @if($settings->tax_enabled && ($service->final_price > $service->base_price))
                            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1.5">
                                (شامل {{ number_format($service->final_price - $service->base_price) }} {{ ($settings->currency_unit ?? 'IRT') === 'IRR' ? 'ریال' : 'تومان' }} مالیات بر ارزش افزوده)
                            </div>
                        @endif
                    </div>
                    @if($service->description)
                        <div class="w-full md:w-auto md:max-w-sm text-sm text-gray-600 dark:text-gray-400 leading-relaxed md:border-r-2 md:border-gray-100 md:dark:border-gray-800 md:pr-6 mt-4 md:mt-0">
                            {{ $service->description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Error Summary Messages --}}
        <div id="error-summary-container" class="{{ $errors->any() ? '' : 'hidden' }} rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/50 p-5 flex items-start gap-4 shadow-sm animate-in fade-in zoom-in duration-300">
            <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="text-sm font-bold text-rose-700 dark:text-rose-300 pt-1.5">
                <ul class="list-disc list-inside space-y-1" id="error-summary-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Booking Form Wrapper --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-gray-200/20 dark:shadow-none overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-200">
            <div class="p-6 md:p-10">
                @if(!$settings->global_online_booking_enabled)
                    <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 p-6 flex flex-col items-center text-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-lg font-bold text-rose-700 dark:text-rose-300">رزرو آنلاین در حال حاضر غیرفعال است.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('booking.public.book', $service) }}" class="space-y-10" id="online-booking-form">
                        @csrf
                        <input type="hidden" name="start_at_utc" id="start_at_utc" value="{{ old('start_at_utc') }}">
                        <input type="hidden" name="end_at_utc" id="end_at_utc" value="{{ old('end_at_utc') }}">
                        <input type="hidden" name="date_local" id="date_local" value="{{ old('date_local') }}">

                        @php
                            $engine = app(\Modules\Booking\Services\BookingEngine::class);
                            $isOnlineEnabled = false;
                            if ($service->serviceProviders->where('is_active', true)->count() > 0) {
                                $firstProvider = $service->serviceProviders->where('is_active', true)->first();
                                $isOnlineEnabled = $engine->isOnlineBookingEnabled($service->id, $firstProvider->provider_user_id);
                            }

                            $onlineFields = [];
                            if($service->appointmentForm && is_array($service->appointmentForm->schema_json) && $isOnlineEnabled) {
                                $onlineFields = array_filter($service->appointmentForm->schema_json['fields'] ?? [], function($field) {
                                    return !empty($field['name']) && !empty($field['collect_from_online']);
                                });
                            }
                            $hasForm = count($onlineFields) > 0;
                            $totalSteps = $hasForm ? 5 : 4;
                            $lastStep = $totalSteps;

                            $errorStep = 1;
                            if ($errors->any()) {
                                if ($errors->has('provider_user_id')) $errorStep = 1;
                                elseif ($errors->has('date_local')) $errorStep = 2;
                                elseif ($errors->has('start_at_utc') || $errors->has('end_at_utc')) $errorStep = 3;
                                elseif ($hasForm && $errors->has('form_data.*')) $errorStep = 4;
                                else $errorStep = $lastStep;
                            }
                        @endphp

                        {{-- Progress Steps UI --}}
                        <div class="mb-12" dir="rtl">
                            <div class="flex items-center justify-between w-full relative">
                                {{-- Step 1 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="1" onclick="showStep(1)">
                                    <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <span class="step-label mt-3 text-sm font-bold transition-all duration-300">ارائه‌دهنده</span>
                                </div>

                                <div class="flex-1 h-1.5 mx-2 md:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="1" data-to="2">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 2 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="2" onclick="if(validateStep(1, false)) showStep(2)">
                                    <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <span class="step-label mt-3 text-sm font-bold transition-all duration-300">تاریخ</span>
                                </div>

                                <div class="flex-1 h-1.5 mx-2 md:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="2" data-to="3">
                                    <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                </div>

                                {{-- Step 3 --}}
                                <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="3" onclick="if(validateStep(2, false)) showStep(3)">
                                    <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <span class="step-label mt-3 text-sm font-bold transition-all duration-300">زمان</span>
                                </div>

                                @if($hasForm)
                                    <div class="flex-1 h-1.5 mx-2 md:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="3" data-to="4">
                                        <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                    </div>

                                    {{-- Step 4 --}}
                                    <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="4" onclick="if(validateStep(3, false)) showStep(4)">
                                        <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        <span class="step-label mt-3 text-sm font-bold transition-all duration-300">فرم</span>
                                    </div>
                                    <div class="flex-1 h-1.5 mx-2 md:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="4" data-to="5">
                                        <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                    </div>
                                    {{-- Step 5 --}}
                                    <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="5" onclick="if(validateStep(4, false)) showStep(5)">
                                        <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <span class="step-label mt-3 text-sm font-bold transition-all duration-300">اطلاعات</span>
                                    </div>
                                @else
                                    <div class="flex-1 h-1.5 mx-2 md:mx-4 rounded-full bg-gray-100 dark:bg-gray-800 relative overflow-hidden step-connector" data-from="3" data-to="4">
                                        <div class="absolute top-0 right-0 h-full bg-emerald-500 transition-all duration-500 w-0"></div>
                                    </div>
                                    {{-- Step 4 (Client Info) --}}
                                    <div class="flex flex-col items-center relative z-10 step-item cursor-pointer group" data-step="4" onclick="if(validateStep(3, false)) showStep(4)">
                                        <div class="step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm bg-white dark:bg-gray-800">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <span class="step-label mt-3 text-sm font-bold transition-all duration-300">اطلاعات</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Step 1: Provider Selection --}}
                        <div class="step-content" data-step="1">
                            <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl p-6 md:p-8 border border-gray-200/60 dark:border-gray-700/50">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <h2 class="text-xl font-black">۱</h2>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">انتخاب ارائه‌دهنده</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">متخصص یا شخص مورد نظر خود را برای این سرویس انتخاب کنید</p>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <select name="provider_user_id" id="provider_user_id" class="w-full h-14 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-base text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-bold cursor-pointer shadow-sm">
                                        <option value="">-- لطفاً انتخاب کنید --</option>
                                        @foreach($service->serviceProviders->where('is_active', true) as $sp)
                                            <option value="{{ $sp->provider_user_id }}" @selected(old('provider_user_id') == $sp->provider_user_id)>
                                                {{ optional($sp->provider)->name ?? optional($sp->provider)->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Date Selection --}}
                        <div class="step-content hidden" data-step="2">
                            <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl p-6 md:p-8 border border-gray-200/60 dark:border-gray-700/50">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <h2 class="text-xl font-black">۲</h2>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">انتخاب تاریخ تقویم</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">روزهای فعال با رنگ روشن مشخص شده‌اند</p>
                                    </div>
                                </div>

                                <div class="max-w-md mx-auto">
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl p-2 shadow-sm border border-gray-200 dark:border-gray-700 mb-4">
                                        <button type="button" id="calendar-prev" class="p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                        <div class="text-lg font-black text-indigo-600 dark:text-indigo-400" id="calendar-label"></div>
                                        <button type="button" id="calendar-next" class="p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                    </div>
                                    <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 md:p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                                        <div class="grid grid-cols-7 gap-2 mb-4 text-center text-xs font-bold text-gray-400" dir="rtl" id="calendar-grid"></div>
                                        <div class="text-sm text-rose-600 dark:text-rose-400 text-center font-bold" id="calendar-error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Time Slot Selection --}}
                        <div class="step-content hidden" data-step="3">
                            <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl p-6 md:p-8 border border-gray-200/60 dark:border-gray-700/50">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <h2 class="text-xl font-black">۳</h2>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">انتخاب زمان (ساعت)</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">زمان‌های خالی برای تاریخ انتخاب شده</p>
                                    </div>
                                    <div class="hidden sm:flex text-sm font-bold text-indigo-600 bg-indigo-100 px-3 py-1.5 rounded-lg" id="slot-selected">اسلاتی انتخاب نشده</div>
                                </div>

                                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3" id="slots-container"></div>

                                    <div class="py-10 text-center text-gray-400" id="slots-empty">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <p class="font-bold">ابتدا تاریخ را از مرحله قبل انتخاب کنید</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 4: Dynamic Form Fields --}}
                        @if($hasForm)
                            <div class="step-content hidden" data-step="4">
                                <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl p-6 md:p-8 border border-gray-200/60 dark:border-gray-700/50">
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                                            <h2 class="text-xl font-black">۴</h2>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">تکمیل فرم اطلاعات</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">موارد درخواستی سرویس را با دقت پر کنید</p>
                                        </div>
                                    </div>
                                    <div class="space-y-6">
                                        @foreach($onlineFields as $field)
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                                    {{ $field['label'] ?? $field['name'] }}
                                                    @if(!empty($field['required'])) <span class="text-rose-500 text-lg">*</span> @endif
                                                </label>
                                                @if(($field['type'] ?? 'text') === 'textarea')
                                                    <textarea name="form_data[{{ $field['name'] }}]" id="form_data_{{ $field['name'] }}" rows="4"
                                                              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all resize-none shadow-sm"
                                                              placeholder="{{ $field['placeholder'] ?? '' }}">{{ old('form_data.'.$field['name']) }}</textarea>
                                                @elseif(($field['type'] ?? 'text') === 'select')
                                                    <select name="form_data[{{ $field['name'] }}]" id="form_data_{{ $field['name'] }}"
                                                            class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-base text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all font-medium shadow-sm cursor-pointer">
                                                        <option value="">انتخاب کنید...</option>
                                                        @foreach($field['options'] ?? [] as $opt)
                                                            <option value="{{ $opt }}" @selected(old('form_data.'.$field['name'])==$opt)>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="{{ $field['type'] ?? 'text' }}" name="form_data[{{ $field['name'] }}]" id="form_data_{{ $field['name'] }}"
                                                           value="{{ old('form_data.'.$field['name']) }}"
                                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                                           class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-base text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step Last: Client Authentication --}}
                        @php $client = auth('client')->user(); @endphp
                        <div class="step-content hidden" data-step="{{ $lastStep }}">
                            <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl p-6 md:p-8 border border-gray-200/60 dark:border-gray-700/50">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <h2 class="text-xl font-black">{{ $lastStep }}</h2>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">تایید نهایی و مشخصات</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشخصات رزرو کننده را برای ثبت در سیستم تایید کنید</p>
                                    </div>
                                </div>

                                @if($client)
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                                        <div class="flex items-center gap-4">
                                            <div class="w-14 h-14 rounded-full bg-emerald-200 dark:bg-emerald-800/50 flex items-center justify-center text-emerald-700 dark:text-emerald-300 shrink-0">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                            <div>
                                                <div class="text-lg font-black text-emerald-900 dark:text-emerald-100 mb-1">{{ $client->full_name }}</div>
                                                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400 dir-ltr text-right">{{ $client->phone ?? 'بدون شماره' }}</div>
                                            </div>
                                        </div>
                                        <div class="px-4 py-2 bg-white dark:bg-gray-900 rounded-xl text-xs font-bold text-emerald-600 border border-emerald-100">شما با موفقیت وارد شده‌اید</div>
                                    </div>
                                @else
                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                                                <input type="text" name="full_name" id="client_full_name" value="{{ old('full_name') }}" autocomplete="name"
                                                       class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm" placeholder="مثال: علی محمدی">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">شماره تماس (موبایل) <span class="text-rose-500">*</span></label>
                                                <input type="tel" name="phone" id="client_phone" value="{{ old('phone') }}" autocomplete="tel"
                                                       class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm dir-ltr font-bold text-left" placeholder="09123456789">
                                            </div>
                                        </div>

                                        @if($clientMode === 'both')
                                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">روش ورود به سیستم <span class="text-rose-500">*</span></label>
                                                <div class="flex flex-wrap gap-6">
                                                    <label class="flex items-center gap-3 cursor-pointer group">
                                                        <input type="radio" name="login_type" value="password" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-600" {{ (old('login_type', $defaultLogin) === 'password') ? 'checked' : '' }} onchange="toggleLoginFields('password')">
                                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">استفاده از رمز عبور</span>
                                                    </label>
                                                    <label class="flex items-center gap-3 cursor-pointer group">
                                                        <input type="radio" name="login_type" value="otp" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-600" {{ (old('login_type', $defaultLogin) === 'otp') ? 'checked' : '' }} onchange="toggleLoginFields('otp')">
                                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">ارسال کد پیامکی (OTP)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <input type="hidden" name="login_type" value="{{ $clientMode }}">
                                        @endif

                                        {{-- Password Section --}}
                                        <div id="password-field-container" class="bg-indigo-50/50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 {{ ($clientMode === 'password' || ($clientMode === 'both' && old('login_type', $defaultLogin) === 'password')) ? 'block' : 'hidden' }}">
                                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">رمز عبور <span class="text-rose-500">*</span></label>
                                            <input type="password" name="password" autocomplete="new-password" id="client_password"
                                                   class="w-full h-12 md:max-w-md rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm dir-ltr text-left">
                                            <p class="text-xs font-medium text-gray-500 mt-2 flex items-center gap-1"><svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> اگر قبلاً ثبت‌نام نکرده‌اید، اکانت شما به صورت خودکار با این رمز ایجاد می‌شود.</p>
                                        </div>

                                        {{-- OTP Section --}}
                                        <div id="otp-fields-container" class="bg-indigo-50/50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 {{ ($clientMode === 'otp' || ($clientMode === 'both' && old('login_type', $defaultLogin) === 'otp')) ? 'block' : 'hidden' }}">
                                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">کد تایید پیامک شده <span class="text-rose-500">*</span></label>
                                            <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-start">
                                                <input type="text" name="otp_code" id="otp_code" value="{{ old('otp_code') }}"
                                                       class="w-full sm:w-48 h-14 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 text-center text-xl tracking-[0.5em] font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm dir-ltr"
                                                       placeholder="----" maxlength="10">
                                                <button type="button" id="send-otp-btn" class="h-14 px-6 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-500/20 whitespace-nowrap">
                                                    ارسال کد تایید
                                                </button>
                                            </div>

                                            <div id="otp-timer-container" class="hidden mt-4 text-sm font-bold text-gray-600 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 inline-flex items-center gap-2">
                                                <svg class="w-4 h-4 text-indigo-500 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                امکان ارسال مجدد در: <span id="otp-timer" class="text-indigo-600 dir-ltr inline-block w-12 text-center">00:00</span>
                                            </div>
                                            <div id="otp-message" class="hidden mt-3 text-sm font-bold rounded-lg p-3"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Navigation Buttons --}}
                        <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-800 mt-8">
                            <button type="button" id="prev-step-btn" class="hidden px-6 h-14 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-all shadow-sm">
                                مرحله قبل
                            </button>
                            <div class="flex-1"></div>
                            <button type="button" id="next-step-btn" class="px-8 h-14 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:scale-105 flex items-center gap-2">
                                مرحله بعدی
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            </button>
                            <button type="submit" id="submit-btn" class="hidden px-8 h-14 rounded-xl bg-emerald-600 text-white font-black hover:bg-emerald-700 shadow-xl shadow-emerald-600/30 transition-all hover:scale-105 flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                ثبت نهایی و پرداخت
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('online-booking-form');
            if (!form) return;

            const providerSelect = document.getElementById('provider_user_id');
            const slotsContainer = document.getElementById('slots-container');
            const slotsEmpty = document.getElementById('slots-empty');
            const startInput = document.getElementById('start_at_utc');
            const endInput = document.getElementById('end_at_utc');
            const slotSelected = document.getElementById('slot-selected');
            const dateInput = document.getElementById('date_local');
            const calendarGrid = document.getElementById('calendar-grid');
            const calendarLabel = document.getElementById('calendar-label');
            const calendarPrev = document.getElementById('calendar-prev');
            const calendarNext = document.getElementById('calendar-next');
            const calendarError = document.getElementById('calendar-error');
            const nextStepBtn = document.getElementById('next-step-btn');
            const prevStepBtn = document.getElementById('prev-step-btn');
            const submitBtn = document.getElementById('submit-btn');

            const sendOtpBtn = document.getElementById('send-otp-btn');
            const clientPhone = document.getElementById('client_phone');
            const clientFullName = document.getElementById('client_full_name');
            const otpTimerContainer = document.getElementById('otp-timer-container');
            const otpTimer = document.getElementById('otp-timer');
            const otpMessage = document.getElementById('otp-message');

            const weekDays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
            const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

            let calendarYear = {!! json_encode($currentJalali['year'] ?? date('Y')) !!};
            let calendarMonth = {!! json_encode($currentJalali['month'] ?? date('n')) !!};
            let calendarDays = [];

            let currentStep = {!! json_encode($errorStep ?? 1) !!};
            const totalSteps = {!! json_encode($hasForm ? 5 : 4) !!};
            const hasForm = {!! json_encode($hasForm) !!};
            const clientMode = {!! json_encode($clientMode) !!};
            const defaultLogin = {!! json_encode($defaultLogin) !!};
            const isAuthenticated = {!! json_encode(auth('client')->check()) !!};

            window.toggleLoginFields = function(type) {
                const passContainer = document.getElementById('password-field-container');
                const otpContainer = document.getElementById('otp-fields-container');
                if (passContainer && otpContainer) {
                    if (type === 'password') {
                        passContainer.classList.remove('hidden');
                        otpContainer.classList.add('hidden');
                    } else {
                        passContainer.classList.add('hidden');
                        otpContainer.classList.remove('hidden');
                    }
                }
            };

            window.showStep = function(step) {
                currentStep = step;
                document.querySelectorAll('.step-content').forEach(el => {
                    el.classList.add('hidden'); el.style.opacity = '0';
                });

                document.querySelectorAll('.step-item').forEach((el) => {
                    const stepNum = parseInt(el.getAttribute('data-step'));
                    const circle = el.querySelector('.step-circle');
                    const label = el.querySelector('.step-label');

                    circle.className = 'step-circle w-14 h-14 rounded-2xl flex items-center justify-center border-2 transition-all duration-300 shadow-sm font-black text-lg';
                    label.className = 'step-label mt-3 text-sm font-bold transition-all duration-300';

                    if (stepNum < step) {
                        circle.classList.add('border-emerald-500', 'bg-emerald-500', 'text-white');
                        label.classList.add('text-emerald-600', 'dark:text-emerald-400');
                        circle.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>`;
                    } else if (stepNum === step) {
                        circle.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white', 'ring-4', 'ring-indigo-100', 'dark:ring-indigo-900/30');
                        label.classList.add('text-indigo-600', 'dark:text-indigo-400');
                        circle.innerHTML = stepNum;
                    } else {
                        circle.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-400', 'dark:text-gray-500');
                        label.classList.add('text-gray-400', 'dark:text-gray-500');
                        circle.innerHTML = stepNum;
                    }
                });

                document.querySelectorAll('.step-connector').forEach((el) => {
                    const from = parseInt(el.getAttribute('data-from'));
                    const bar = el.querySelector('div');
                    if (from < step) bar.style.width = '100%';
                    else bar.style.width = '0%';
                });

                const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
                if (stepContent) {
                    stepContent.classList.remove('hidden');
                    setTimeout(() => stepContent.style.opacity = '1', 10);
                }
                updateButtons();

                if(!event || event.type !== 'click') {
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    const errSummary = document.getElementById('error-summary-container');
                    if(errSummary) {
                        errSummary.classList.add('hidden');
                        document.getElementById('error-summary-list').innerHTML = '';
                    }
                }
            };

            let otpInterval;
            function startOtpTimer(seconds) {
                if (otpInterval) clearInterval(otpInterval);
                sendOtpBtn.disabled = true;
                sendOtpBtn.classList.add('opacity-50', 'cursor-not-allowed');
                otpTimerContainer.classList.remove('hidden');
                let remain = seconds;
                updateTimerDisplay(remain);
                otpInterval = setInterval(() => {
                    remain--;
                    if (remain <= 0) {
                        clearInterval(otpInterval);
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        otpTimerContainer.classList.add('hidden');
                    } else {
                        updateTimerDisplay(remain);
                    }
                }, 1000);
            }

            function updateTimerDisplay(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                otpTimer.textContent = `${m}:${s}`;
            }

            function showMessage(msg, type = 'error') {
                otpMessage.textContent = msg;
                otpMessage.className = 'mt-3 text-sm font-bold rounded-lg p-3 border block';
                if (type === 'error') {
                    otpMessage.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200', 'dark:bg-rose-900/30', 'dark:border-rose-800', 'dark:text-rose-400');
                } else {
                    otpMessage.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'dark:bg-emerald-900/30', 'dark:border-emerald-800', 'dark:text-emerald-400');
                }
            }

            if (sendOtpBtn) {
                sendOtpBtn.addEventListener('click', async () => {
                    const phone = clientPhone.value.trim();
                    const fullName = clientFullName.value.trim();
                    otpMessage.classList.add('hidden');

                    if (!fullName) { showMessage('لطفاً نام و نام خانوادگی را وارد کنید.', 'error'); clientFullName.focus(); return; }
                    if (!phone) { showMessage('لطفاً شماره تماس را وارد کنید.', 'error'); clientPhone.focus(); return; }

                    sendOtpBtn.disabled = true;
                    const originalText = sendOtpBtn.textContent;
                    sendOtpBtn.innerHTML = `<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

                    try {
                        const res = await fetch(`{{ route('booking.public.send_otp') }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                            body: JSON.stringify({ phone: phone, full_name: fullName })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showMessage(data.message || 'کد تایید پیامک شد.', 'success');
                            document.getElementById('otp_code').focus();
                            startOtpTimer(data.resend_in || 60);
                        } else {
                            showMessage(data.message || 'خطا در ارسال کد.', 'error');
                            if (data.resend_in) startOtpTimer(data.resend_in); else sendOtpBtn.disabled = false;
                        }
                    } catch (error) {
                        showMessage('خطای شبکه. بررسی اتصال.', 'error'); sendOtpBtn.disabled = false;
                    } finally {
                        if(!sendOtpBtn.disabled || otpTimerContainer.classList.contains('hidden') === false) {} else sendOtpBtn.disabled = false;
                        if(sendOtpBtn.innerHTML.includes('svg')) sendOtpBtn.textContent = originalText;
                    }
                });
            }

            function updateButtons() {
                if (currentStep === 1) prevStepBtn.classList.add('hidden'); else prevStepBtn.classList.remove('hidden');
                if (currentStep === totalSteps) { nextStepBtn.classList.add('hidden'); submitBtn.classList.remove('hidden'); }
                else { nextStepBtn.classList.remove('hidden'); submitBtn.classList.add('hidden'); }
            }

            function showErrorSummary(errors) {
                const container = document.getElementById('error-summary-container');
                const list = document.getElementById('error-summary-list');
                list.innerHTML = '';
                errors.forEach(err => {
                    const li = document.createElement('li'); li.textContent = err; list.appendChild(li);
                });
                container.classList.remove('hidden');
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            window.validateStep = function(step, showErrors = true) {
                let errors = []; let isValid = true;
                if (step === 1 && !providerSelect?.value) { errors.push('لطفاً یک ارائه‌دهنده را انتخاب کنید.'); isValid = false; }
                if (step === 2 && !dateInput?.value) { errors.push('لطفاً تاریخ را انتخاب کنید.'); isValid = false; }
                if (step === 3 && (!startInput?.value || !endInput?.value)) { errors.push('لطفاً زمان موجود را انتخاب کنید.'); isValid = false; }
                if (hasForm && step === 4) {
                    let formValid = true;
                        @if($hasForm) @foreach($onlineFields as $field) @if(!empty($field['required']))
                    {
                        const fieldEl = document.getElementById('form_data_{{ $field['name'] }}');
                        if(fieldEl && !fieldEl.value.trim()) { errors.push('فیلد "{{ $field['label'] ?? $field['name'] }}" الزامی است.'); formValid = false; }
                    }
                    @endif @endforeach @endif
                    if(!formValid) isValid = false;
                }
                if (!isValid && showErrors) showErrorSummary(errors);
                return isValid;
            }

            nextStepBtn?.addEventListener('click', () => { if (window.validateStep(currentStep)) { currentStep++; window.showStep(currentStep); } });
            prevStepBtn?.addEventListener('click', () => { if (currentStep > 1) { currentStep--; window.showStep(currentStep); } });

            form.addEventListener('submit', function(e) {
                let errors = [];
                for(let i=1; i<= (hasForm?4:3); i++){
                    if(!window.validateStep(i)) { e.preventDefault(); window.showStep(i); return; }
                }
                if(!isAuthenticated) {
                    const fullName = document.getElementById('client_full_name')?.value;
                    const phone = document.getElementById('client_phone')?.value;
                    let activeLoginType = clientMode === 'both' ? (document.querySelector('input[name="login_type"]:checked')?.value || defaultLogin) : clientMode;

                    if (!fullName) errors.push('وارد کردن نام الزامی است.');
                    if (!phone) errors.push('شماره تماس الزامی است.');
                    if (activeLoginType === 'password' && !document.getElementById('client_password')?.value) errors.push('رمز عبور الزامی است.');
                    if (activeLoginType === 'otp' && !document.getElementById('otp_code')?.value) errors.push('کد پیامکی الزامی است.');

                    if (errors.length > 0) { e.preventDefault(); showErrorSummary(errors); window.showStep(totalSteps); return; }
                }
                document.getElementById('loading-overlay').classList.remove('hidden');
                document.getElementById('loading-overlay').classList.add('flex');
                document.body.style.overflow = 'hidden';
            });

            // Calendar Render
            const clearSlots = (message = 'تاریخ را انتخاب کنید') => {
                if (slotsContainer) slotsContainer.innerHTML = '';
                if (slotsEmpty) {
                    slotsEmpty.innerHTML = message.includes('در حال') ? `<svg class="animate-spin w-8 h-8 mx-auto mb-3 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="font-bold">${message}</p>` : `<svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p class="font-bold">${message}</p>`;
                    slotsEmpty.classList.remove('hidden');
                }
                if (startInput) startInput.value = ''; if (endInput) endInput.value = '';
                if (slotSelected) { slotSelected.textContent = 'اسلاتی انتخاب نشده'; slotSelected.classList.replace('bg-emerald-100', 'bg-indigo-100'); slotSelected.classList.replace('text-emerald-700', 'text-indigo-600'); }
            };

            const renderCalendar = () => {
                if (!calendarGrid) return;
                calendarGrid.innerHTML = '';
                if (calendarLabel) calendarLabel.textContent = `${persianMonths[calendarMonth - 1] || ''} ${calendarYear}`;

                weekDays.forEach((w) => {
                    const div = document.createElement('div'); div.className = 'py-2 border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400'; div.textContent = w; calendarGrid.appendChild(div);
                });

                if (!calendarDays.length) return;
                const firstDate = new Date(calendarDays[0].local_date + 'T00:00:00');
                for (let i = 0; i < (firstDate.getDay() + 1) % 7; i++) calendarGrid.appendChild(document.createElement('div'));

                calendarDays.forEach((day) => {
                    const cell = document.createElement('button'); cell.type = 'button';
                    const isSel = dateInput?.value === day.local_date;
                    const isDis = day.is_closed || !day.has_available_slots;

                    cell.className = 'w-full aspect-square rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-300 ' +
                        (isSel ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 scale-105 ring-2 ring-indigo-200 dark:ring-indigo-900' :
                            isDis ? 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed' :
                                'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 hover:text-indigo-600 border border-gray-100 dark:border-gray-600');

                    cell.disabled = isDis;
                    cell.textContent = new Date(day.local_date + 'T00:00:00').toLocaleDateString('fa-IR-u-ca-persian', {day: 'numeric'});

                    if (!isDis) cell.addEventListener('click', () => { if (dateInput) dateInput.value = day.local_date; fetchSlots(); renderCalendar(); });
                    calendarGrid.appendChild(cell);
                });
            };

            const fetchCalendar = async () => {
                if (!providerSelect?.value) return;
                if (calendarError) calendarError.textContent = '';
                if (calendarGrid) calendarGrid.style.opacity = '0.5';
                try {
                    const res = await fetch(`{{ route('booking.public.calendar', $service) }}?` + new URLSearchParams({provider_user_id: providerSelect.value, year: calendarYear, month: calendarMonth}), {headers: {'Accept': 'application/json'}});
                    calendarDays = (await res.json()).data || []; renderCalendar();
                } catch (e) { if (calendarError) calendarError.textContent = 'خطا در ارتباط با سرور.'; }
                if (calendarGrid) calendarGrid.style.opacity = '1';
            };

            const renderSlots = (slots) => {
                if (!slotsContainer) return; slotsContainer.innerHTML = '';
                if (!slots.length) { clearSlots('زمان خالی برای این روز یافت نشد.'); return; }
                if (slotsEmpty) slotsEmpty.classList.add('hidden');

                slots.forEach((slot) => {
                    const btn = document.createElement('button'); btn.type = 'button';
                    btn.className = 'py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all';
                    btn.textContent = slot.start_time || slot.start_at_view.split(' ')[1].substring(0, 5);

                    btn.addEventListener('click', () => {
                        startInput.value = slot.start_at_utc; endInput.value = slot.end_at_utc;
                        if (slotSelected) {
                            slotSelected.textContent = `${btn.textContent} الی ${slot.end_time || slot.end_at_view.split(' ')[1].substring(0,5)}`;
                            slotSelected.classList.replace('bg-indigo-100', 'bg-emerald-100'); slotSelected.classList.replace('text-indigo-600', 'text-emerald-700');
                        }
                        Array.from(slotsContainer.children).forEach(b => {
                            b.className = 'py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all';
                        });
                        btn.className = 'py-3 rounded-xl border-transparent bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 font-bold text-sm transform scale-105 transition-all ring-2 ring-indigo-200 dark:ring-indigo-900';
                    });
                    slotsContainer.appendChild(btn);
                });
            };

            const fetchSlots = async () => {
                if (!providerSelect?.value || !dateInput?.value) return clearSlots();
                clearSlots('در حال یافتن زمان‌های خالی...');
                if (slotsContainer) slotsContainer.style.opacity = '0.5';
                try {
                    const res = await fetch(`{{ route('booking.public.slots', $service) }}?` + new URLSearchParams({provider_user_id: providerSelect.value, date_local: dateInput.value}), {headers: {'Accept': 'application/json'}});
                    renderSlots((await res.json()).data || []);
                } catch (e) { clearSlots('خطا در دریافت اسلات‌ها.'); }
                if (slotsContainer) slotsContainer.style.opacity = '1';
            };

            providerSelect?.addEventListener('change', () => { dateInput.value = ''; clearSlots(); fetchCalendar(); });
            calendarPrev?.addEventListener('click', () => { calendarMonth--; if (calendarMonth<1) {calendarMonth=12; calendarYear--;} fetchCalendar(); });
            calendarNext?.addEventListener('click', () => { calendarMonth++; if (calendarMonth>12) {calendarMonth=1; calendarYear++;} fetchCalendar(); });

            window.showStep(currentStep);
            fetchCalendar();
            if (dateInput?.value) fetchSlots();
        });
    </script>
@endpush
