@extends('layouts.user')
@php
    $fixedProviderPayload = (isset($fixedProvider) && $fixedProvider)
    ? ['id' => $fixedProvider->id, 'name' => $fixedProvider->name]
    : null;
    $clientLabel = config('clients.labels.singular', 'مشتری');

    $statusOptions = [
        \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
        \Modules\Booking\Entities\Appointment::STATUS_PENDING => 'در انتظار تایید',
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'تایید شده',
    ];
@endphp

@section('content')
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #3b82f6 !important;
            stroke: #2563eb !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(37, 99, 235, 0.45));
        }
        .dark .tooth-selected {
            fill: #1d4ed8 !important;
            stroke: #3b82f6 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1;
        }
        .dark .tooth-unselected {
            fill: #334155 !important;
            stroke: #475569;
        }
        .tooth-unselected:hover {
            fill: #f8fafc !important;
            stroke: #3b82f6;
        }
        .dark .tooth-unselected:hover {
            fill: #1e293b !important;
            stroke: #60a5fa;
        }
    </style>
    <div class="space-y-5" data-fixed-provider='@json($fixedProviderPayload)'
         x-data="operatorWizard({ fixedProvider: null })" x-init="
    const raw = $el.dataset.fixedProvider;
    try { fixedProvider = raw ? JSON.parse(raw) : null } catch(e) { fixedProvider = null }
    init();
  ">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ثبت نوبت (مرحله‌ای)</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">جریان اپراتوری با انتخاب {{ config('booking.labels.service', 'سرویس') }}/{{ config('booking.labels.provider') }} و اسلات
                </p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.appointments.index') }}">
                بازگشت
            </a>
        </div>

        @if(session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="flex flex-col gap-1 rounded-2xl border border-rose-200 dark:border-rose-700/70 bg-rose-50 dark:bg-rose-900/40 text-rose-800 dark:text-rose-100 px-4 py-3 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl">⚠️</span>
                    <span class="text-sm font-bold">خطا در ثبت نوبت:</span>
                </div>
                <ul class="list-disc list-inside text-xs pr-8 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @includeIf('partials.jalali-date-picker')

        {{-- Main Wizard Container (Changed from FORM to DIV to avoid nesting issues with client widget) --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-5">

            {{-- The Actual Form for Submission (Hidden) --}}
            <form method="POST" action="{{ route('user.booking.appointments.store') }}" x-ref="realForm" class="hidden">
                @csrf
                <input type="hidden" name="service_id" x-model="serviceId">
                <input type="hidden" name="provider_user_id" x-model="providerId">
                <input type="hidden" name="client_id" x-model="clientId">
                <input type="hidden" name="waitlist_id" x-model="waitlistId">
                <input type="hidden" name="start_at_utc" x-ref="startUtcInput">
                <input type="hidden" name="end_at_utc" x-ref="endUtcInput">
                <input type="hidden" name="date_local" x-model="dateLocal">
                <input type="hidden" name="start_time_local" x-model="manualStartTime">
                <input type="hidden" name="end_time_local" x-model="manualEndTime">
                <input type="hidden" name="appointment_form_response_json" x-ref="formJsonInput">
                <input type="hidden" name="notes" x-model="notes">
                <input type="hidden" name="status" x-model="status">
            </form>

            {{-- Stepper Component --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 md:p-6 overflow-x-hidden">
                <div class="relative">
                    {{-- Progress Line --}}
                    <div class="absolute top-5 md:top-6 right-0 left-0 h-0.5 bg-gray-200 dark:bg-gray-700 hidden md:block">
                        <div class="h-full bg-indigo-600 dark:bg-indigo-500 transition-all duration-500 ease-out"
                             :style="`width: ${((step - 1) / (totalSteps - 1)) * 100}%`"></div>
                    </div>

                    {{-- Steps --}}
                    <div class="relative flex justify-between items-start gap-1 md:gap-0">
                        <template x-for="(stepLabel, index) in stepLabels" :key="stepLabel.num">
                            <div class="flex flex-col items-center flex-1" style="min-width: 0;">
                                {{-- Step Circle --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-full border-2 transition-all duration-300"
                                         :class="{
                                         'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/50 scale-110 ring-4 ring-indigo-200 dark:ring-indigo-900/50': isStepActive(stepLabel.num),
                                         'bg-emerald-500 border-emerald-500 text-white': isStepCompleted(stepLabel.num),
                                         'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500': isStepUpcoming(stepLabel.num)
                                     }">
                                        <template x-if="isStepCompleted(stepLabel.num)">
                                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isStepCompleted(stepLabel.num)">
                                            <span class="text-base md:text-lg" x-text="stepLabel.icon"></span>
                                        </template>
                                    </div>
                                    {{-- Step Number Badge --}}
                                    <div class="mt-1.5 md:mt-2 px-1.5 md:px-2 py-0.5 rounded-full text-[10px] md:text-xs font-semibold transition-all"
                                         :class="{
                                         'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300': isStepActive(stepLabel.num),
                                         'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300': isStepCompleted(stepLabel.num),
                                         'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400': isStepUpcoming(stepLabel.num)
                                     }" x-text="stepLabel.num"></div>
                                </div>

                                {{-- Step Label --}}
                                <div class="mt-2 md:mt-3 text-center px-0.5" style="max-width: 100px;">
                                    <div class="text-[10px] md:text-xs font-semibold transition-colors leading-tight"
                                         :class="{
                                         'text-indigo-700 dark:text-indigo-300': isStepActive(stepLabel.num),
                                         'text-emerald-700 dark:text-emerald-300': isStepCompleted(stepLabel.num),
                                         'text-gray-500 dark:text-gray-400': isStepUpcoming(stepLabel.num)
                                     }" x-text="stepLabel.title"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div
                class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 px-4 py-3">
                <button type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="prev()" :disabled="step===1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    قبلی
                </button>

                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                    <span x-text="step"></span> از <span x-text="totalSteps"></span>
                </div>

                {{-- در مراحل ۲ و ۳ انتخاب به صورت کارت انجام می‌شود و بعد از انتخاب اتومات مرحله بعدی می‌رویم --}}
                <button type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="next()" x-show="step < totalSteps && (step === 1 || step > 3)" :disabled="step >= totalSteps">
                    بعدی
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <div x-show="step >= totalSteps" class="w-20"></div>
            </div>

            {{-- STEP 1: Client --}}
            <div x-show="step===1" class="space-y-4">
                <div class="bg-gradient-to-l from-indigo-50 via-purple-50 to-blue-50 dark:from-indigo-900/20 dark:via-purple-900/10 dark:to-blue-900/20 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            👤
                        </div>
                        <div>
                            <div class="font-bold text-base text-gray-900 dark:text-gray-100">انتخاب {{ $clientLabel }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $clientLabel }} مورد نظر را از طریق جستجو، صف انتظار یا ثبت جدید انتخاب کنید.</div>
                        </div>
                    </div>
                    <div x-show="clientId" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200 text-xs font-bold border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ $clientLabel }} انتخاب شد</span>
                    </div>
                </div>

                {{-- Selected Client Card (Shown when client is selected) --}}
                <div x-show="clientId" class="bg-emerald-50/90 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700/70 rounded-2xl p-4 shadow-sm animate-in fade-in">
                    <div class="flex items-center justify-between gap-4 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-base shadow-sm">
                                <span x-text="selectedClientName ? selectedClientName.charAt(0) : '👤'"></span>
                            </div>
                            <div>
                                <div class="font-black text-sm text-emerald-950 dark:text-emerald-100 flex items-center flex-wrap gap-2">
                                    <span x-text="selectedClientName"></span>
                                    <template x-if="waitlistId && selectedWaitlistObject">
                                        <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                            ⚡ تخصیص از صف انتظار (نفر <span x-text="selectedWaitlistObject.global_queue_rank || selectedWaitlistObject.queue_rank || 1"></span> کلینیک)
                                            <template x-if="selectedWaitlistObject.duration_minutes">
                                                <span x-text="`• ${selectedWaitlistObject.duration_minutes} دقیقه`"></span>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 mt-1">
                                    <span x-show="selectedClientPhone" x-text="`📞 ${selectedClientPhone}`"></span>
                                    <span x-show="selectedClientEmail" x-text="`✉️ ${selectedClientEmail}`"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <button type="button"
                                    class="px-3.5 py-2 rounded-xl bg-emerald-200/80 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-100 text-xs font-bold hover:bg-emerald-300 dark:hover:bg-emerald-800 transition shadow-2xs"
                                    @click="clearSelectedClient()">
                                تغییر {{ $clientLabel }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Client Selection Modes (Shown when no client is selected) --}}
                <div x-show="!clientId" class="space-y-4">
                    {{-- Segmented Mode Switcher --}}
                    <div class="grid gap-1 p-1 bg-gray-100 dark:bg-gray-800/80 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs font-bold"
                         :class="isQueueEnabled ? 'grid-cols-3' : 'grid-cols-2'">
                        <button type="button"
                                @click="clientTab = 'search'"
                                class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-2"
                                :class="clientTab === 'search' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span>جستجوی {{ $clientLabel }}</span>
                        </button>

                        <template x-if="isQueueEnabled">
                            <button type="button"
                                    @click="clientTab = 'waitlist'"
                                    class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-2"
                                    :class="clientTab === 'waitlist' ? 'bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>صف انتظار</span>
                                <span x-show="waitlist.length > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                      :class="clientTab === 'waitlist' ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                      x-text="waitlist.length"></span>
                            </button>
                        </template>

                        <button type="button"
                                @click="clientTab = 'new'"
                                class="py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-2"
                                :class="clientTab === 'new' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>{{ $clientLabel }} جدید</span>
                        </button>
                    </div>

                    {{-- Tab 1: Search Existing Client --}}
                    <div x-show="clientTab === 'search'" class="space-y-3">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-3">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    جستجو بر اساس نام، شماره تماس، کد ملی یا شماره پرونده
                                </span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       class="w-full border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 rounded-xl p-3 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition font-medium"
                                       placeholder="نام، شماره تماس، کد ملی یا شماره پرونده {{ $clientLabel }} را وارد کنید..."
                                       x-model="clientSearch"
                                       @input.debounce.300ms="fetchClients()"
                                       @focus="clientSearchFocused = true"
                                       @blur="setTimeout(() => clientSearchFocused = false, 200)">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Search Results --}}
                            <div class="mt-3" x-show="clients.length > 0">
                                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 flex items-center justify-between">
                                    <span x-show="clientSearch">نتایج جستجو (<span x-text="clients.length"></span> مورد)</span>
                                    <span x-show="!clientSearch">۳ {{ $clientLabel }} اخیر</span>
                                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400">برای انتخاب روی کارت کلیک کنید</span>
                                </div>
                                <div class="max-h-72 overflow-y-auto space-y-2 border border-gray-100 dark:border-gray-700/80 rounded-xl p-2 bg-gray-50/50 dark:bg-gray-900/30">
                                    <template x-for="c in clients" :key="c.id">
                                        <button type="button"
                                                class="w-full text-right border-2 rounded-xl p-3 transition-all duration-150 hover:shadow-md cursor-pointer flex items-center justify-between gap-3"
                                                :class="String(clientId) === String(c.id)
                                                    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-900 dark:text-indigo-100 shadow-sm'
                                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:border-indigo-300 dark:hover:border-indigo-600'"
                                                @click="selectClient(c)">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-sm flex-none">
                                                    <span x-text="c.full_name ? c.full_name.charAt(0) : '👤'"></span>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-sm text-gray-900 dark:text-white" x-text="c.full_name"></div>
                                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        <span x-show="c.phone" x-text="`📞 ${c.phone}`"></span>
                                                        <span x-show="c.national_code" x-text="`🆔 کد ملی: ${c.national_code}`"></span>
                                                        <span x-show="c.case_number" x-text="`📋 پرونده: ${c.case_number}`"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                                انتخاب ↵
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-3" x-show="clientSearch && clients.length === 0 && !clientLoading">
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4 text-center">
                                    <div class="text-sm font-bold text-amber-800 dark:text-amber-200">{{ $clientLabel }} با این مشخصات یافت نشد</div>
                                </div>
                            </div>

                            <template x-if="clientLoading">
                                <div class="mt-3 flex items-center justify-center py-6">
                                    <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 text-xs font-bold">
                                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>در حال جستجو...</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Tab 2: Select from Waitlist Queue (Shown if Queue is enabled) --}}
                    <template x-if="isQueueEnabled">
                        <div x-show="clientTab === 'waitlist'" class="space-y-3">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-3">
                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">مراجعین در صف انتظار</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">اولویت‌بندی خودکار بر اساس موقعیت در صف انتظار</p>
                                    </div>
                                    <button type="button" @click="fetchWaitlist()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="به‌روزرسانی صف">
                                        <svg class="w-4 h-4" :class="waitlistLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </div>

                                <template x-if="waitlistLoading">
                                    <div class="flex items-center justify-center py-8">
                                        <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 text-xs font-bold">
                                            <svg class="animate-spin h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>در حال بارگذاری صف انتظار...</span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!waitlistLoading && waitlist.length > 0">
                                    <div class="space-y-2.5 max-h-80 overflow-y-auto p-1 border border-gray-100 dark:border-gray-700/80 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                        <template x-for="item in waitlist" :key="item.id">
                                            <div @click="selectWaitlistEntry(item)"
                                                 class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition-all duration-150 cursor-pointer space-y-2">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <template x-if="(item.global_queue_rank || item.queue_rank) === 1">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-black text-xs shadow-2xs">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                                نفر ۱ کلینیک
                                                            </span>
                                                        </template>
                                                        <template x-if="(item.global_queue_rank || item.queue_rank) !== 1">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-xs">
                                                                نفر <span x-text="item.global_queue_rank || item.queue_rank"></span> کلینیک
                                                            </span>
                                                        </template>
                                                        <template x-if="item.service_id">
                                                            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">
                                                                (نفر <span x-text="item.service_queue_rank || 1"></span> در سرویس)
                                                            </span>
                                                        </template>

                                                        <span class="font-black text-sm text-gray-900 dark:text-white" x-text="item.client_name"></span>
                                                        <span x-show="item.client_phone" class="text-xs text-gray-500 dark:text-gray-400 dir-ltr" x-text="`(${item.client_phone})`"></span>
                                                    </div>

                                                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100 transition">
                                                        تخصیص نوبت ↵
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-1.5 border-t border-gray-100 dark:border-gray-700/60 flex-wrap gap-y-1">
                                                    <div class="flex items-center gap-3 flex-wrap">
                                                        <span x-text="`{{ config('booking.labels.service', 'سرویس') }}: ${item.service_name || 'عمومی'}`"></span>
                                                        <span x-show="item.provider_name" x-text="`• {{ config('booking.labels.provider') }}: ${item.provider_name}`"></span>
                                                        <span x-show="item.preferred_date" x-text="`• تاریخ ترجیحی: ${item.preferred_date}`"></span>
                                                        <span x-show="item.duration_minutes" x-text="`• مدت زمان: ${item.duration_minutes} دقیقه`"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!waitlistLoading && waitlist.length === 0">
                                    <div class="p-8 bg-gray-50/50 dark:bg-gray-900/30 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl text-center space-y-2">
                                        <div class="text-3xl">⏳</div>
                                        <div class="text-sm font-bold text-gray-700 dark:text-gray-300">صف انتظار خالی است</div>
                                        <div class="text-xs text-gray-400">در حال حاضر مراجعی در صف انتظار فعال ثبت نشده است.</div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Tab 3: Quick Create New Client --}}
                    <div x-show="clientTab === 'new'" class="space-y-3">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                ثبت سریع {{ $clientLabel }} جدید
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-700/80 pt-3">
                                @includeIf('clients::widgets.client-quick-create')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-3 flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-xs text-blue-800 dark:text-blue-200 font-medium">
                        بعد از انتخاب {{ $clientLabel }}، می‌توانید با دکمه «بعدی» به مرحله بعد بروید.
                    </div>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div x-show="step===2" class="space-y-4">
                <template x-if="flow==='PROVIDER_FIRST' && !fixedProvider">
                    <div class="space-y-4">
                        <div
                            class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <span class="text-xl">👨‍⚕️</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-base text-gray-800 dark:text-gray-100">انتخاب {{ config('booking.labels.provider') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ config('booking.labels.provider') }} مورد نظر را انتخاب
                                        کنید</div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                جستجو {{ config('booking.labels.provider') }}
                            </span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-3 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="نام {{ config('booking.labels.provider') }} را وارد کنید..." x-model="providerSearch"
                                       @input.debounce.300ms="fetchProviders()">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <template x-if="providerLoading">
                            <div class="flex items-center justify-center py-8">
                                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                         viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-sm">در حال دریافت {{ config('booking.labels.providers') }}...</span>
                                </div>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-show="!providerLoading">
                            <template x-for="p in providers" :key="p.id">
                                <button type="button"
                                        class="group relative text-right border-2 rounded-xl p-4 transition-all duration-200 hover:shadow-lg"
                                        :class="String(providerId)===String(p.id)
                                    ? 'border-indigo-600 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/40 dark:to-indigo-950/40 text-indigo-900 dark:text-indigo-100 shadow-md ring-2 ring-indigo-200 dark:ring-indigo-800'
                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md'"
                                        @click="selectProvider(p, true)">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="font-semibold text-sm mb-1 flex items-center flex-wrap gap-1.5">
                                                <span x-text="p.name"></span>
                                                <span x-show="providers && providers.length === 1" class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">
                                                    تنها ارائه‌دهنده (انتخاب خودکار)
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400" x-show="p.subtitle"
                                                 x-text="p.subtitle"></div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                             :class="String(providerId)===String(p.id)
                                             ? 'bg-indigo-600 text-white'
                                             : 'bg-gray-100 dark:bg-gray-700 text-gray-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                 x-show="String(providerId)===String(p.id)">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-xs" x-show="String(providerId)!==String(p.id)">👨‍⚕️</span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <template x-if="!providerLoading && (!providers || providers.length===0)">
                            <div
                                class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-4 text-center">
                                <div class="text-sm text-amber-800 dark:text-amber-200">موردی یافت نشد</div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="flow==='PROVIDER_FIRST' && fixedProvider">
                    <div class="space-y-4">
                        <div
                            class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <span class="text-xl">👨‍⚕️</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-base text-gray-800 dark:text-gray-100">{{ config('booking.labels.provider') }}
                                        انتخاب‌شده</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ config('booking.labels.provider') }} به صورت خودکار انتخاب
                                        شده است</div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border-2 border-indigo-300 dark:border-indigo-700 p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xl">
                                    <span>👨‍⚕️</span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base text-gray-800 dark:text-gray-100"
                                         x-text="fixedProvider.name"></div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">به صورت خودکار انتخاب شد
                                    </div>
                                </div>
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">دسته‌بندی</label>
                            <select
                                class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                x-model="categoryId" @change="fetchServicesForServiceFirst()">
                                <option value="">همه</option>
                                <template x-for="c in categories" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب {{ config('booking.labels.service', 'سرویس') }}</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو سرویس..." x-model="serviceSearch"
                                   @input.debounce.300ms="fetchServicesForServiceFirst()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="serviceLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت {{ config('booking.labels.services', 'سرویس‌ها') }}...</div>
                        </template>

                        <div class="space-y-4" x-show="!serviceLoading">
                            <template x-for="grp in groupedServices" :key="grp.key">
                                <div class="space-y-2">
                                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="grp.title">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <template x-for="s in grp.items" :key="s.id">
                                            <button type="button" class="text-right border rounded-xl p-3 transition"
                                                    :class="String(serviceId)===String(s.id)
                                                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                                    @click="selectService(s, true)">
                                                <div class="font-semibold text-sm" x-text="s.name"></div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400"
                                                     x-show="s.duration_min || s.price"
                                                     x-text="[s.duration_min ? (s.duration_min + ' دقیقه') : null, s.price ? (s.price + ' تومان') : null].filter(Boolean).join(' • ')">
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="!serviceLoading && (!services || services.length===0)">
                            <div class="text-xs text-amber-600">موردی یافت نشد.</div>
                        </template>

                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            بعد از انتخاب {{ config('booking.labels.service', 'سرویس') }}، به صورت خودکار به مرحله بعد می‌روید.
                        </div>
                    </div>
                </template>
            </div>

            {{-- STEP 3 --}}
            <div x-show="step===3" class="space-y-3">
                <template x-if="flow==='PROVIDER_FIRST'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm mb-1 dark:text-gray-200">دسته‌بندی</label>
                            <select
                                class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                x-model="categoryId" @change="fetchServicesForProvider()">
                                <option value="">همه</option>
                                <template x-for="c in categories" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm mb-1 dark:text-gray-200">انتخاب {{ config('booking.labels.service', 'سرویس') }}</label>
                            <div class="relative">
                                <input type="text"
                                       class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="جستجو سرویس..." x-model="serviceSearch"
                                       @input.debounce.300ms="fetchServicesForProvider()">
                                <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                            </div>

                            <template x-if="serviceLoading">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">در حال دریافت {{ config('booking.labels.services', 'سرویس‌ها') }}...</div>
                            </template>

                            <div class="space-y-4 mt-2" x-show="!serviceLoading">
                                <template x-for="grp in groupedServices" :key="grp.key">
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold text-gray-600 dark:text-gray-300"
                                             x-text="grp.title"></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                            <template x-for="s in grp.items" :key="s.id">
                                                <button type="button" class="text-right border rounded-xl p-3 transition"
                                                        :class="String(serviceId)===String(s.id)
                                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                                        @click="selectService(s, true)">
                                                    <div class="font-semibold text-sm" x-text="s.name"></div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400"
                                                         x-show="s.duration_min || s.price"
                                                         x-text="[s.duration_min ? (s.duration_min + ' دقیقه') : null, s.price ? (s.price + ' تومان') : null].filter(Boolean).join(' • ')">
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="!serviceLoading && (!services || services.length===0)">
                                <div class="text-xs text-amber-600 mt-2">موردی یافت نشد.</div>
                            </template>

                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">فقط {{ config('booking.labels.services', 'سرویس‌ها') }}یی که برای این
                                {{ config('booking.labels.provider') }} فعال هستند نمایش داده می‌شود. بعد از انتخاب، به صورت خودکار به مرحله بعد
                                می‌روید.</div>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST' && !fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب {{ config('booking.labels.provider') }} (برای
                            {{ config('booking.labels.service', 'سرویس') }} انتخابی)</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو {{ config('booking.labels.provider') }}..." x-model="providerSearch"
                                   @input.debounce.300ms="fetchProviders()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="providerLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت {{ config('booking.labels.providers') }}...</div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="p in providers" :key="p.id">
                                <button type="button" class="text-right border rounded-xl p-3 transition"
                                        :class="String(providerId)===String(p.id)
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                        @click="selectProvider(p, true)">
                                    <div class="font-semibold text-sm" x-text="p.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="p.subtitle"
                                         x-text="p.subtitle"></div>
                                </button>
                            </template>
                        </div>

                        <template x-if="!providerLoading && (!providers || providers.length===0)">
                            <div class="text-xs text-amber-600">موردی یافت نشد.</div>
                        </template>

                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            بعد از انتخاب {{ config('booking.labels.provider') }}، به صورت خودکار به مرحله بعد می‌روید.
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST' && fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">{{ config('booking.labels.provider') }} انتخاب‌شده</label>
                        <div
                            class="border rounded-xl p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200">
                            <div class="font-semibold text-sm" x-text="fixedProvider.name"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">به صورت خودکار انتخاب شد.</div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            {{ config('booking.labels.provider') }} قابل تغییر نیست.
                        </div>
                    </div>
                </template>
            </div>

            {{-- STEP 4: Calendar month --}}
            <div x-show="step===4" class="space-y-4">
                <div
                    class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xl">📅</span>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-base text-gray-800 dark:text-gray-100">انتخاب روز</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">روز مورد نظر برای نوبت را انتخاب کنید
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Waitlist Preferences Notice (Step 4) --}}
                <template x-if="selectedWaitlistObject && (selectedWaitlistObject.preferred_date || selectedWaitlistObject.duration_minutes)">
                    <div class="p-4 bg-gradient-to-r from-amber-50 via-amber-50/80 to-indigo-50/40 dark:from-amber-950/30 dark:via-amber-950/20 dark:to-indigo-950/20 border border-amber-200/90 dark:border-amber-800/70 rounded-2xl flex items-start sm:items-center justify-between gap-3 text-xs shadow-2xs">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 dark:bg-amber-500/25 text-amber-700 dark:text-amber-300 flex items-center justify-center text-lg font-bold shrink-0 mt-0.5 sm:mt-0">
                                📌
                            </div>
                            <div class="space-y-1 sm:space-y-0.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-amber-950 dark:text-amber-200 text-xs sm:text-sm">ترجیحات ثبت‌شده مراجع در صف انتظار:</span>
                                    <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold" x-text="selectedWaitlistObject.client_name"></span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-700 dark:text-gray-300 flex-wrap">
                                    <template x-if="selectedWaitlistObject.preferred_date">
                                        <span class="inline-flex items-center gap-1.5 bg-white/80 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-amber-200/60 dark:border-amber-800/50">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">تاریخ ترجیحی:</span>
                                            <strong class="text-amber-700 dark:text-amber-300 font-black text-xs" x-text="selectedWaitlistObject.preferred_date"></strong>
                                        </span>
                                    </template>
                                    <template x-if="selectedWaitlistObject.duration_minutes">
                                        <span class="inline-flex items-center gap-1.5 bg-white/80 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-indigo-200/60 dark:border-indigo-800/50">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">مدت زمان مورد نیاز:</span>
                                            <strong class="text-indigo-600 dark:text-indigo-400 font-black text-xs" x-text="selectedWaitlistObject.duration_minutes + ' دقیقه'"></strong>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <template x-if="selectedWaitlistObject.preferred_date">
                            <div class="text-[11px] text-amber-800 dark:text-amber-400 font-medium hidden md:block text-left shrink-0">
                                (روز ترجیحی با نشان طلایی در تقویم مشخص شده است)
                            </div>
                        </template>
                    </div>
                </template>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    @click="prevMonth()">

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </button>
                            <div class="text-base font-semibold text-gray-800 dark:text-gray-100 px-4" x-text="monthLabel">
                            </div>
                            <button type="button"
                                    class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    @click="nextMonth()">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <template x-if="calendarLoading">
                        <div class="flex items-center justify-center py-8">
                            <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-sm">در حال بارگذاری تقویم...</span>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-7 gap-2 text-xs" dir="rtl">
                        <template x-for="w in weekDays" :key="w">
                            <div class="text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 py-1">
                                <span x-text="w"></span>
                            </div>
                        </template>

                        <template x-for="cell in calendarCells" :key="cell.key">
                            <div>
                                <template x-if="cell.is_placeholder">
                                    <div class="h-[52px] border border-transparent"></div>
                                </template>

                                <template x-if="!cell.is_placeholder">
                                    <button type="button" class="w-full h-[52px] border rounded-lg p-2 text-center relative group"
                                            :class="dayBtnClass(cell.day)" @click="selectDay(cell.day)"
                                            @contextmenu.prevent="openHistoryModal(cell.day.local_date)"
                                            :disabled="cell.day.is_closed || !cell.day.has_available_slots">
                                        <div class="font-semibold" x-text="toPersianDayNumber(cell.day.local_date)"></div>
                                        <div class="text-[10px] mt-1" x-show="cell.day.is_closed">تعطیل</div>
                                        <div class="text-[10px] mt-1"
                                             x-show="!cell.day.is_closed && !cell.day.has_available_slots">پر</div>
                                        <template x-if="isPreferredDay(cell.day.local_date)">
                                            <span class="absolute top-1 left-1 flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500" title="روز ترجیحی مراجع در صف"></span>
                                            </span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-lg p-3 mt-4 flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-xs text-blue-800 dark:text-blue-200">
                            با کلیک راست روی هر روز، می‌توانید تاریخچه نوبت‌های آن روز را مشاهده کنید.
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 5: Slots --}}
            <div x-show="step===5" class="space-y-4">
                <div
                    class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                <span class="text-xl">⏰</span>
                            </div>
                            <div>
                                <div class="font-semibold text-base text-gray-800 dark:text-gray-100">انتخاب زمان</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">زمان مورد نظر برای نوبت را انتخاب کنید
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <template x-if="allowManualTimeOverride && !(selectedService && selectedService.custom_schedule_enabled)">
                                <div class="inline-flex items-center p-1 bg-gray-200/70 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-300/60 dark:border-gray-700/60 shadow-inner">
                                    <button type="button" 
                                            @click="if(manualModeActive) { manualModeActive = false; fetchSlots(); }"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all duration-200 font-semibold focus:outline-none"
                                            :class="!manualModeActive ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/60 dark:border-gray-600/60' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>اسلات‌های زمان</span>
                                    </button>
                                    <button type="button" 
                                            @click="manualModeActive = true"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all duration-200 font-semibold focus:outline-none"
                                            :class="manualModeActive ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/60 dark:border-gray-600/60' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>تنظیم دستی</span>
                                    </button>
                                </div>
                            </template>
                            <button type="button"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    @click="fetchSlots()" x-show="!isCustomScheduleEnabled()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                بروزرسانی
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Waitlist Time Preferences Notice (Step 5) --}}
                <template x-if="selectedWaitlistObject && (selectedWaitlistObject.duration_minutes || selectedWaitlistObject.preferred_date)">
                    <div class="p-4 bg-gradient-to-r from-indigo-50 via-indigo-50/80 to-amber-50/40 dark:from-indigo-950/30 dark:via-indigo-950/20 dark:to-amber-950/20 border border-indigo-200/90 dark:border-indigo-800/70 rounded-2xl flex items-start sm:items-center justify-between gap-3 text-xs shadow-2xs">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 dark:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-lg font-bold shrink-0 mt-0.5 sm:mt-0">
                                ⏱️
                            </div>
                            <div class="space-y-1 sm:space-y-0.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-indigo-950 dark:text-indigo-200 text-xs sm:text-sm">ترجیحات زمانی مراجع در صف انتظار:</span>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300 text-[10px] font-bold" x-text="selectedWaitlistObject.client_name"></span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-700 dark:text-gray-300 flex-wrap">
                                    <template x-if="selectedWaitlistObject.duration_minutes">
                                        <span class="inline-flex items-center gap-1.5 bg-white/80 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-indigo-200/60 dark:border-indigo-800/50">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">مدت زمان مورد نیاز:</span>
                                            <strong class="text-indigo-700 dark:text-indigo-300 font-black text-xs" x-text="selectedWaitlistObject.duration_minutes + ' دقیقه'"></strong>
                                        </span>
                                    </template>
                                    <template x-if="selectedWaitlistObject.preferred_date">
                                        <span class="inline-flex items-center gap-1.5 bg-white/80 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-amber-200/60 dark:border-amber-800/50">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">تاریخ ترجیحی در صف:</span>
                                            <strong class="text-amber-700 dark:text-amber-300 font-black text-xs" x-text="selectedWaitlistObject.preferred_date"></strong>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <template x-if="selectedWaitlistObject.duration_minutes">
                            <div class="text-[11px] text-indigo-800 dark:text-indigo-400 font-medium hidden md:block text-left shrink-0">
                                (مدت زمان به صورت خودکار برای محاسبه اعمال شده است)
                            </div>
                        </template>
                    </div>
                </template>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                    <template x-if="slotsLoading && !isCustomScheduleEnabled()">
                        <div class="flex items-center justify-center py-8">
                            <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-sm">در حال دریافت اسلات‌ها...</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="slotsError && !isCustomScheduleEnabled()">
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-lg p-4 text-center">
                            <div class="text-sm text-red-800 dark:text-red-200" x-text="slotsError"></div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3"
                         x-show="slots.length && !slotsLoading && !isCustomScheduleEnabled()">
                        <template x-for="slot in slots" :key="slot.start_at_utc">
                            <button type="button"
                                    :disabled="isSlotDisabled(slot)"
                                    class="group relative border-2 rounded-xl p-3 text-center transition-all duration-200"
                                    :class="selectedSlotKey === slot.start_at_utc
                                        ? 'border-indigo-600 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/40 dark:to-indigo-950/40 text-indigo-900 dark:text-indigo-100 shadow-md ring-2 ring-indigo-200 dark:ring-indigo-800'
                                        : (isSlotDisabled(slot)
                                            ? 'border-gray-200 dark:border-gray-800 text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800/40 cursor-not-allowed opacity-60'
                                            : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md')"
                                    @click="selectSlot(slot)">
                                <div class="font-bold text-sm mb-1" x-text="formatTime(slot.start_at_view)"></div>
                                <div class="text-[10px]" :class="isSlotDisabled(slot) ? 'text-red-500 font-semibold dark:text-red-400' : 'text-gray-500 dark:text-gray-400'">
                                    <template x-if="slot.sync_blocked">
                                        <span>تکمیل ظرفیت (هماهنگ‌شده)</span>
                                    </template>
                                    <template x-if="!slot.sync_blocked && isSlotDisabled(slot)">
                                        <span>تکمیل ظرفیت</span>
                                    </template>
                                    <template x-if="!isSlotDisabled(slot)">
                                        <span>ظرفیت: <span class="font-semibold" x-text="slotCapacityDisplay(slot)"></span></span>
                                    </template>
                                </div>
                                <div class="absolute top-2 left-2" x-show="selectedSlotKey === slot.start_at_utc">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <template
                    x-if="!slotsLoading && dateLocal && slots.length === 0 && !slotsError && !isCustomScheduleEnabled()">
                    <div class="text-xs text-amber-600 dark:text-amber-300">اسلات خالی برای این روز یافت نشد.</div>
                </template>

                <div x-show="isCustomScheduleEnabled()" class="space-y-2">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        برای این سرویس زمان‌بندی سفارشی فعال است؛ ساعت شروع و پایان را به صورت دستی وارد کنید.
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-gray-300">ساعت شروع</label>
                            <input type="text" data-jdp-only-time
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                   x-model="manualStartTime" @input="calculateEndTime()">
                        </div>
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-gray-300">مدت (دقیقه)</label>
                            <input type="number" min="1"
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                   x-model="manualDuration" @input="calculateEndTime()" placeholder="مثلاً 30">
                        </div>
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-gray-300">ساعت پایان</label>
                            <input type="text" data-jdp-only-time
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                   x-model="manualEndTime" @input="clearSlotSelection()">
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 6: Appointment Form --}}
            <div x-show="step===6 && hasAppointmentForm" class="space-y-4">
                <div
                    class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xl">📝</span>
                        </div>
                        <div>
                            <div class="font-semibold text-base text-gray-800 dark:text-gray-100">فرم اطلاعات نوبت</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">لطفاً اطلاعات اضافی را تکمیل کنید</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">

                    <template x-if="selectedService && selectedService.appointment_form_id">
                        <div class="space-y-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-show="!appointmentFormSchema">
                                در حال دریافت فرم...
                            </div>

                            <template
                                x-if="appointmentFormSchema && appointmentFormSchema.fields && appointmentFormSchema.fields.length">
                                <div class="space-y-4">
                                    <template x-for="field in appointmentFormSchema.fields" :key="field.name">
                                        <div class="space-y-1">
                                            <label class="block text-xs text-gray-600 dark:text-gray-300"
                                                   x-text="field.label"></label>

                                            <template x-if="field.type === 'textarea'">
                                            <textarea
                                                class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                                :placeholder="field.placeholder || ''" :required="field.required"
                                                x-model="appointmentFormValues[field.name]"></textarea>
                                            </template>

                                            <template x-if="field.type === 'select'">
                                                <select
                                                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                                    :required="field.required" x-model="appointmentFormValues[field.name]">
                                                    <option value="">انتخاب کنید</option>
                                                    <template x-for="opt in (field.options || [])" :key="opt">
                                                        <option :value="opt" x-text="opt"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'radio'">
                                                <div class="flex flex-wrap gap-3">
                                                    <template x-for="opt in (field.options || [])" :key="opt">
                                                        <label
                                                            class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                            <input type="radio" :name="`form_${field.name}`" :value="opt"
                                                                   :required="field.required"
                                                                   x-model="appointmentFormValues[field.name]">
                                                            <span x-text="opt"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="field.type === 'checkbox'">
                                                <div class="flex flex-wrap gap-3">
                                                    <template x-for="opt in (field.options || [])" :key="opt">
                                                        <label
                                                            class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                            <input type="checkbox" :value="opt"
                                                                   x-model="appointmentFormValues[field.name]">
                                                            <span x-text="opt"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="field.type === 'tooth_number'">
                                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mx-auto">
                                                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-3">
                                                        <div class="flex items-center gap-2">
                                                            <span class="w-2 h-5 rounded-full bg-rose-500 shrink-0"></span>
                                                            <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm">نقشه دندانی</h2>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" @click="selectJaw('upper')"
                                                                    :class="preset==='upper' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک بالا</button>
                                                            <button type="button" @click="selectJaw('lower')"
                                                                    :class="preset==='lower' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک پایین</button>
                                                            <button type="button" @click="selectAllTeeth()"
                                                                    :class="preset==='all' ? 'bg-violet-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">همه</button>
                                                            <button type="button" @click="resetTeeth()"
                                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600
                                                                           hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 transition-all">
                                                                پاک‌سازی
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="px-4 pt-4 pb-1 relative">
                                                        <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur
                                                                    px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                                                            <span class="text-[10px] text-gray-400 uppercase font-bold block">انتخاب</span>
                                                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                                                  x-text="appointmentFormValues[field.name]?.length || 0"></span>
                                                        </div>
                                                        <x-booking::dental-chart/>
                                                    </div>
                                                    <div class="px-5 py-3.5 flex items-center gap-3 min-h-14 border-t border-gray-150 dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                                                        <template x-if="appointmentFormValues[field.name] && appointmentFormValues[field.name].length > 0">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌های انتخابی:</span>
                                                                <div class="inline-grid grid-cols-2 select-none">
                                                                    <!-- Row 1: UR | UL -->
                                                                    <!-- UR -->
                                                                    <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                                        <template x-for="t in getQuadrantTeeth(appointmentFormValues[field.name], 'UR')" :key="t">
                                                                            <div role="button" @click="toggle(t)"
                                                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                                                 :class="[getQuadrantClasses(t)]"
                                                                                 x-text="getToothLabel(t).num">
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                    <!-- UL -->
                                                                    <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                                        <template x-for="t in getQuadrantTeeth(appointmentFormValues[field.name], 'UL')" :key="t">
                                                                            <div role="button" @click="toggle(t)"
                                                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                                                 :class="[getQuadrantClasses(t)]"
                                                                                 x-text="getToothLabel(t).num">
                                                                            </div>
                                                                        </template>
                                                                    </div>

                                                                    <!-- Row 2: LR | LL -->
                                                                    <!-- LR -->
                                                                    <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                                                        <template x-for="t in getQuadrantTeeth(appointmentFormValues[field.name], 'LR')" :key="t">
                                                                            <div role="button" @click="toggle(t)"
                                                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                                                 :class="[getQuadrantClasses(t)]"
                                                                                 x-text="getToothLabel(t).num">
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                    <!-- LL -->
                                                                    <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                                                        <template x-for="t in getQuadrantTeeth(appointmentFormValues[field.name], 'LL')" :key="t">
                                                                            <div role="button" @click="toggle(t)"
                                                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                                                 :class="[getQuadrantClasses(t)]"
                                                                                 x-text="getToothLabel(t).num">
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="!appointmentFormValues[field.name] || appointmentFormValues[field.name].length === 0">
                                                            <span class="text-xs text-gray-400 dark:text-gray-500 self-center">
                                                                روی دندان کلیک کنید تا انتخاب شود
                                                            </span>
                                                        </template>
                                                    </div>
                                                    {{-- Client Treatment Plan Helper --}}
                                                    <template x-if="Object.keys(clientToothPlans || {}).length > 0">
                                                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-indigo-50/30 dark:bg-indigo-950/10 space-y-3">
                                                            <div class="flex items-center gap-2 text-indigo-850 dark:text-indigo-300">
                                                                <span class="text-sm">💡</span>
                                                                <span class="text-xs font-bold">طرح درمان‌های فعال کلاینت (برای انتخاب سریع کلیک کنید):</span>
                                                            </div>
                                                            <div class="flex flex-wrap gap-2">
                                                                <template x-for="(treatments, toothId) in clientToothPlans" :key="toothId">
                                                                    <button type="button"
                                                                            @click="toggle(Number(toothId))"
                                                                            :class="(appointmentFormValues[field.name] || []).includes(Number(toothId)) ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300 dark:ring-indigo-800' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                                            class="inline-flex flex-col items-start p-2.5 rounded-xl text-right transition-all hover:scale-[1.02] active:scale-[0.98] min-w-[140px] border border-transparent shadow-sm">
                                                                        <div class="flex items-center justify-between w-full">
                                                                            <span class="text-xs font-black" x-text="`دندان ${getToothLabel(Number(toothId)).num} (${getToothLabel(Number(toothId)).pos})`"></span>
                                                                            <span class="w-2 h-2 rounded-full" :class="treatments[0].status === 'confirmed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                                                        </div>
                                                                        <div class="mt-1 text-[10px] opacity-90 line-clamp-1 text-gray-500 dark:text-gray-400" :class="(appointmentFormValues[field.name] || []).includes(Number(toothId)) ? '!text-indigo-100' : ''" x-text="treatments.map(t => t.service_name).join('، ')"></div>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="field.type === 'select-user-by-role'">
                                                <select
                                                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                                    :required="field.required"
                                                    :multiple="field.multiple"
                                                    x-model="appointmentFormValues[field.name]">
                                                    <option value="" x-show="!field.multiple">انتخاب کنید</option>
                                                    <template x-for="u in (field.user_options || [])" :key="u.id">
                                                        <option :value="u.id" x-text="u.name"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="!['textarea','select','radio','checkbox','tooth_number','select-user-by-role'].includes(field.type)">
                                                <input
                                                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                                    :type="field.type || 'text'" :placeholder="field.placeholder || ''"
                                                    :required="field.required" x-model="appointmentFormValues[field.name]">
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!selectedService || !selectedService.appointment_form_id">
                        <div class="text-xs text-gray-500 dark:text-gray-400">برای این سرویس فرم اختصاصی تعریف نشده است.
                        </div>
                    </template>
                </div>
            </div>

            {{-- Final Step: Notes and Submit (shown at last step) --}}
            <div x-show="step === (hasAppointmentForm ? 7 : 6)" class="space-y-4">
                <div
                    class="bg-gradient-to-l from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                            <span class="text-xl">✓</span>
                        </div>
                        <div>
                            <div class="font-semibold text-base text-gray-800 dark:text-gray-100">ثبت نهایی نوبت</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">لطفاً وضعیت و یادداشت را بررسی کرده و نوبت را
                                ثبت کنید</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                وضعیت نوبت
                            </span>
                        </label>
                        <select x-model="status" class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-3 text-sm dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            یادداشت (اختیاری)
                        </span>
                        </label>
                        <textarea name="notes" rows="4"
                                  class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-3 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                  placeholder="یادداشت یا توضیحات اضافی را اینجا وارد کنید..." x-model="notes"></textarea>
                    </div>
                </div>

                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">آماده ثبت نوبت</div>
                        <div class="text-xs text-blue-700 dark:text-blue-300">اطمینان حاصل کنید که تمام اطلاعات به درستی
                            وارد شده‌اند.</div>
                    </div>
                    <button type="button"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2"
                            @click.stop="handleSubmit($event)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        ثبت نوبت
                    </button>
                </div>
            </div>

            {{-- History Modal --}}
            <div x-show="showHistoryModal" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]"
                     @click.outside="closeHistoryModal">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            تاریخچه نوبت‌های <span x-text="toPersianDayNumber(historyModalDate)"></span>
                        </h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="closeHistoryModal">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-4 overflow-y-auto flex-1">
                        <template x-if="historyModalLoading">
                            <div class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="animate-spin h-8 w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>در حال دریافت اطلاعات...</span>
                            </div>
                        </template>

                        <template x-if="!historyModalLoading">
                            <div class="space-y-4">
                                {{-- Suggested Time Alert --}}
                                <div x-show="historyModalSuggestedTime" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-3 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">اولین زمان خالی پیشنهادی</div>
                                        <div class="text-sm font-bold text-emerald-800 dark:text-emerald-200" x-text="historyModalSuggestedTime"></div>
                                    </div>
                                </div>

                                {{-- Stats Section --}}
                                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg border border-blue-100 dark:border-blue-800">
                                        <div class="font-bold text-blue-700 dark:text-blue-300 text-lg" x-text="historyModalStats.total || 0"></div>
                                        <div class="text-blue-600 dark:text-blue-400">کل</div>
                                    </div>
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 p-2 rounded-lg border border-emerald-100 dark:border-emerald-800">
                                        <div class="font-bold text-emerald-700 dark:text-emerald-300 text-lg" x-text="historyModalStats.confirmed || 0"></div>
                                        <div class="text-emerald-600 dark:text-emerald-400">قطعی</div>
                                    </div>
                                    <div class="bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg border border-amber-100 dark:border-amber-800">
                                        <div class="font-bold text-amber-700 dark:text-amber-300 text-lg" x-text="historyModalStats.pending || 0"></div>
                                        <div class="text-amber-600 dark:text-amber-400">انتظار</div>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900/20 p-2 rounded-lg border border-red-100 dark:border-red-800">
                                        <div class="font-bold text-red-700 dark:text-red-300 text-lg" x-text="historyModalStats.canceled || 0"></div>
                                        <div class="text-red-600 dark:text-red-400">لغو</div>
                                    </div>
                                </div>

                                {{-- List Section --}}
                                <template x-if="historyModalItems.length === 0">
                                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-6">
                                        <div class="text-4xl mb-2">📭</div>
                                        <div>هیچ نوبتی برای این روز ثبت نشده است.</div>
                                    </div>
                                </template>

                                <div class="space-y-3" x-show="historyModalItems.length > 0">
                                    <template x-for="item in historyModalItems" :key="item.id">
                                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                                                    <span x-text="item.start_time"></span>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="item.client_name"></div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        مدت: <span x-text="item.duration_minutes"></span> دقیقه • پایان: <span x-text="item.end_time"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="px-2 py-1 rounded-lg text-xs font-medium"
                                                      :class="{
                                                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': ['pending', 'pending_payment'].includes(item.status),
                                                          'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': ['confirmed', 'done'].includes(item.status),
                                                          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': ['canceled_by_admin', 'canceled_by_client', 'no_show'].includes(item.status),
                                                          'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': ['draft', 'rescheduled'].includes(item.status)
                                                      }"
                                                      x-text="item.status_label"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end">
                        <button type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                @click="closeHistoryModal">
                            بستن
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-time]',
                    hasSecond: false
                });
            }
        });

        function operatorWizard(options = {}) {
            return {
                flow: @json($flow ?? 'PROVIDER_FIRST'),
                step: 1,

                defaultSlotCapacity: @json($settings -> default_capacity_per_slot ?? 1),
                fixedProvider: options.fixedProvider || null,
                providerId: '',
                serviceId: '',

                categoryId: '',
                dateLocal: '',
                manualStartTime: '',
                manualEndTime: '',
                manualDuration: '',
                notes: '',
                status: 'CONFIRMED',

                manualModeActive: false,
                allowManualTimeOverride: {{ \Modules\Booking\Entities\BookingSetting::current()->allow_manual_time_override ? 'true' : 'false' }},

                providers: [],
                services: [],
                categories: [],

                providerLoading: false,
                serviceLoading: false,

                providerSearch: '',
                serviceSearch: '',

                calendarYear: null,
                calendarMonth: null,
                calendarDays: [],
                calendarLoading: false,

                slots: [],
                slotsLoading: false,
                slotsError: '',
                selectedSlotKey: '',
                selectedService: null,

                appointmentFormSchema: null,
                appointmentFormValues: {},
                isQueueEnabled: @json($isQueueEnabled ?? false),
                clientTab: 'search',
                waitlist: [],
                waitlistLoading: false,
                waitlistId: '',
                selectedWaitlistObject: null,
                clients: [],
                clientSearch: '',
                clientId: '',
                selectedClientObject: null,
                clientLoading: false,
                clientSearchFocused: false,
                isSubmitting: false,
                clientToothPlans: {},

                // Modal State
                showHistoryModal: false,
                historyModalDate: '',
                historyModalItems: [],
                historyModalStats: {},
                historyModalSuggestedTime: null,
                historyModalLoading: false,

                weekDays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                persianMonths: [
                    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                ],
                hasAppointmentForm: false,

                get totalSteps() {
                    return this.hasAppointmentForm ? 7 : 6;
                },

                get clientStep() {
                    return 1;
                },

                get finalStep() {
                    return this.hasAppointmentForm ? 7 : 6;
                },

                get stepLabels() {
                    const labels = [{
                        num: 1,
                        title: 'انتخاب {{ $clientLabel }}',
                        icon: '👤'
                    },
                        {
                            num: 2,
                            title: this.flow === 'PROVIDER_FIRST' ? 'انتخاب {{ config('booking.labels.provider') }}' : 'انتخاب {{ config('booking.labels.service', 'سرویس') }}',
                            icon: this.flow === 'PROVIDER_FIRST' ? '👨‍⚕️' : '🛎️'
                        },
                        {
                            num: 3,
                            title: this.flow === 'PROVIDER_FIRST' ? 'انتخاب {{ config('booking.labels.service', 'سرویس') }}' : 'انتخاب {{ config('booking.labels.provider') }}',
                            icon: this.flow === 'PROVIDER_FIRST' ? '🛎️' : '👨‍⚕️'
                        },
                        {
                            num: 4,
                            title: 'انتخاب روز',
                            icon: '📅'
                        },
                        {
                            num: 5,
                            title: 'انتخاب زمان',
                            icon: '⏰'
                        },
                    ];

                    if (this.hasAppointmentForm) {
                        labels.push({
                            num: 6,
                            title: 'فرم اطلاعات',
                            icon: '📝'
                        });
                    }

                    const finalStepNum = this.hasAppointmentForm ? 7 : 6;
                    labels.push({
                        num: finalStepNum,
                        title: 'ثبت نهایی',
                        icon: '✓'
                    });

                    return labels;
                },

                isStepCompleted(stepNum) {
                    return this.step > stepNum;
                },

                isStepActive(stepNum) {
                    return this.step === stepNum;
                },

                isStepUpcoming(stepNum) {
                    return this.step < stepNum;
                },

                init() {
                    const now = new Date();
                    const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
                        year: 'numeric',
                        month: 'numeric'
                    });
                    const parts = formatter.formatToParts(now);
                    const y = parts.find(p => p.type === 'year').value;
                    const m = parts.find(p => p.type === 'month').value;

                    // Convert Persian digits to English digits if necessary
                    this.calendarYear = parseInt(y.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)));
                    this.calendarMonth = parseInt(m.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)));

                    // Initialize fixedProvider if available
                    if (this.fixedProvider) {
                        this.providerId = String(this.fixedProvider.id || '');
                        this.providers = [this.fixedProvider];
                        this.onProviderSelected();
                    } else if (this.flow === 'PROVIDER_FIRST') {
                        // Will fetch providers when user reaches step 2
                    } else {
                        // Will fetch services when user reaches step 2
                    }

                    // Check URL params for preselection
                    const urlParams = new URLSearchParams(window.location.search);
                    const qClientId = urlParams.get('client_id');
                    const qWaitlistId = urlParams.get('waitlist_id');
                    if (qClientId) {
                        this.clientId = String(qClientId);
                    }
                    if (qWaitlistId) {
                        this.waitlistId = String(qWaitlistId);
                    }

                    // Start with client selection (step 1)
                    this.step = 1;
                    this.fetchClients();

                    if (this.isQueueEnabled) {
                        this.fetchWaitlist().then(() => {
                            if (this.waitlistId) {
                                const entry = this.waitlist.find(w => String(w.id) === String(this.waitlistId));
                                if (entry) {
                                    this.selectWaitlistEntry(entry);
                                }
                            }
                        });
                    }

                    window.addEventListener('client-quick-saved', (e) => {
                        const newId = e?.detail?.clientId;
                        if (!newId) return;

                        // 1. Clear search so we get the latest list (including the new one)
                        this.clientSearch = '';

                        // 2. Fetch and Select
                        this.fetchClients().then(() => {
                            this.clientId = String(newId);
                            this.waitlistId = '';
                            this.selectedWaitlistObject = null;
                            const newClient = this.clients.find(c => String(c.id) === String(newId));
                            if (newClient) {
                                this.selectedClientObject = newClient;
                            }
                        });
                    });

                    this.$watch('manualStartTime', () => this.calculateEndTime());
                    this.$watch('clientId', () => this.fetchClientPlans());
                    if (this.clientId) {
                        this.fetchClientPlans();
                    }
                },

                // ---------------- providers/services/categories ----------------

                get groupedServices() {
                    const items = Array.isArray(this.services) ? this.services : [];

                    const getCatName = (s) => {
                        if (!s) return '';
                        if (typeof s.category_name === 'string' && s.category_name.trim()) return s.category_name
                            .trim();
                        if (s.category && typeof s.category.name === 'string' && s.category.name.trim()) return s
                            .category.name.trim();
                        const cid = s.category_id ?? null;
                        if (cid && Array.isArray(this.categories) && this.categories.length) {
                            const found = this.categories.find(c => String(c.id) === String(cid));
                            if (found && typeof found.name === 'string' && found.name.trim()) return found.name.trim();
                        }
                        return '';
                    };

                    const groups = new Map();
                    for (const s of items) {
                        const cat = getCatName(s);
                        const key = cat ? `cat:${cat}` : 'cat:__none__';
                        if (!groups.has(key)) groups.set(key, {
                            key,
                            title: cat || 'بدون دسته',
                            items: []
                        });
                        groups.get(key).items.push(s);
                    }

                    const arr = Array.from(groups.values());
                    arr.sort((a, b) => {
                        if (a.key === 'cat:__none__') return 1;
                        if (b.key === 'cat:__none__') return -1;
                        return (a.title || '').localeCompare(b.title || '', 'fa');
                    });
                    for (const g of arr) {
                        g.items.sort((x, y) => (x.name || '').localeCompare(y.name || '', 'fa'));
                    }
                    return arr;
                },
                slotCapacityDisplay(slot) {
                    if (!slot) return this.defaultSlotCapacity;
                    if (slot.remaining_capacity !== null && slot.remaining_capacity !== undefined) {
                        return slot.remaining_capacity;
                    }
                    if (slot.capacity_per_slot !== null && slot.capacity_per_slot !== undefined && Number(slot
                        .capacity_per_slot) > 0) {
                        return slot.capacity_per_slot;
                    }
                    return this.defaultSlotCapacity;
                },

                async fetchProviders(autoSelect = true) {
                    this.providerLoading = true;
                    const params = new URLSearchParams({
                        q: this.providerSearch || ''
                    });
                    if (this.flow === 'SERVICE_FIRST' && this.serviceId) {
                        params.set('service_id', this.serviceId);
                    }

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.providers') }}?` + params
                            .toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.providers = json.data || [];

                        // انتخاب خودکار در صورت وجود تنها ۱ ارائه‌دهنده
                        if (autoSelect && this.providers.length === 1 && !this.providerId) {
                            await this.selectProvider(this.providers[0], false);
                        }
                    } finally {
                        this.providerLoading = false;
                    }
                },

                async fetchCategories() {
                    if (!this.providerId) return;
                    const params = new URLSearchParams({
                        provider_id: this.providerId
                    });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.categories') }}?` + params
                        .toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    this.categories = json.data || [];
                },

                async fetchServicesForProvider() {
                    if (!this.providerId) return;
                    this.serviceLoading = true;
                    const params = new URLSearchParams({
                        provider_id: this.providerId,
                        q: this.serviceSearch || '',
                    });
                    if (this.categoryId) params.set('category_id', this.categoryId);

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.services') }}?` + params
                            .toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.services = json.data || [];
                    } finally {
                        this.serviceLoading = false;
                    }
                },

                async fetchAllActiveServices() {
                    this.serviceLoading = true;
                    const params = new URLSearchParams({
                        q: this.serviceSearch || ''
                    });
                    if (this.categoryId) params.set('category_id', this.categoryId);

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.all-services') }}?` + params
                            .toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.services = json.data || [];
                        this.syncCategoriesFromServices();
                    } finally {
                        this.serviceLoading = false;
                    }
                },

                async fetchServicesForServiceFirst() {
                    await this.fetchAllActiveServices();
                },

                async selectProvider(p, autoGo = false) {
                    this.providerId = String(p?.id ?? '');
                    await this.onProviderSelected();
                    if (autoGo && this.step === 2 && this.flow === 'PROVIDER_FIRST') {
                        if (this.serviceId) {
                            await this.onServiceSelected();
                            await this.loadCalendar();
                            this.step = 4;
                            return;
                        }
                        await this.next();
                    }
                    if (autoGo && this.step === 3 && this.flow === 'SERVICE_FIRST') {
                        await this.next();
                    }
                },

                async onProviderSelected() {
                    if (this.flow === 'PROVIDER_FIRST') {
                        const waitlistServiceId = this.selectedWaitlistObject?.service_id ? String(this.selectedWaitlistObject.service_id) : (this.serviceId ? String(this.serviceId) : '');
                        this.categoryId = '';
                        this.serviceId = waitlistServiceId;
                        this.services = [];
                        this.categories = [];
                        this.resetCalendarAndSlots();

                        await this.fetchCategories();
                        await this.fetchServicesForProvider();

                        if (this.serviceId) {
                            await this.onServiceSelected();
                        }
                        return;
                    }
                    this.resetCalendarAndSlots();
                },

                async selectService(s, autoGo = false) {
                    this.serviceId = String(s?.id ?? '');
                    await this.onServiceSelected();
                    if (autoGo && this.step === 2 && this.flow === 'SERVICE_FIRST') {
                        if (this.fixedProvider && this.fixedProvider.id && !this.providerId) {
                            this.providerId = String(this.fixedProvider.id);
                        }
                        await this.next();
                        // اگر تنها ۱ ارائه‌دهنده انتخاب گردید، مستقیماً به مرحله تقویم برود
                        if (this.providerId && this.step === 3) {
                            await this.next();
                        }
                    }
                    if (autoGo && this.step === 3 && this.flow === 'PROVIDER_FIRST') {
                        await this.next();
                    }
                },

                async onServiceSelected() {
                    this.selectedService = this.services.find(s => String(s.id) === String(this.serviceId)) || null;
                    this.resetCalendarAndSlots();
                    this.resetAppointmentForm();

                    if (this.selectedService && this.selectedService.payment_mode === 'REQUIRED') {
                        this.status = 'PENDING_PAYMENT';
                    } else {
                        this.status = 'CONFIRMED';
                    }

                    if (this.flow === 'SERVICE_FIRST') {
                        await this.fetchProviders();
                    }

                    this.hasAppointmentForm = Boolean(this.selectedService && this.selectedService.appointment_form_id);
                    if (this.hasAppointmentForm) {
                        await this.fetchAppointmentForm(this.selectedService.appointment_form_id);
                    }
                },

                syncCategoriesFromServices() {
                    if (this.flow !== 'SERVICE_FIRST') return;
                    const map = new Map();
                    for (const s of (this.services || [])) {
                        const id = s.category_id ?? null;
                        const name = s.category_name ?? null;
                        if (!id || !name) continue;
                        if (!map.has(String(id))) {
                            map.set(String(id), {
                                id,
                                name
                            });
                        }
                    }
                    this.categories = Array.from(map.values()).sort((a, b) => (a.name || '').localeCompare(b.name || '', 'fa'));
                },

                resetCalendarAndSlots() {
                    this.dateLocal = '';
                    this.calendarDays = [];
                    this.slots = [];
                    this.selectedSlotKey = '';
                    this.manualStartTime = '';
                    this.manualEndTime = '';
                    this.manualDuration = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                resetAppointmentForm() {
                    this.appointmentFormSchema = null;
                    this.appointmentFormValues = {};
                    this.hasAppointmentForm = false;
                },

                preset: 'none',
                upperJawIds: [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                lowerJawIds: [15,16,17,18,19,20,21,22,23,24,25,26,27,28],

                selectJaw(type) {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    if (this.preset === type) { this.resetTeeth(); return; }
                    this.preset = type;
                    this.appointmentFormValues[fieldName] = type === 'upper' ? [...this.upperJawIds] : [...this.lowerJawIds];
                },

                selectAllTeeth() {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    if (this.preset === 'all') { this.resetTeeth(); return; }
                    this.preset = 'all';
                    this.appointmentFormValues[fieldName] = [...this.upperJawIds, ...this.lowerJawIds];
                },

                resetTeeth() {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    this.appointmentFormValues[fieldName] = [];
                    this.preset = 'none';
                },

                getToothLabel(id) {
                    const mapping = {
                        1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                        5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                        8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                        12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                        15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                        19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                        22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                        26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                    };
                    return mapping[id] ?? { num: id, pos: 'UR' };
                },

                getQuadrantClasses(id) {
                    const tooth = this.getToothLabel(id);
                    switch(tooth.pos) {
                        case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        default:   return '';
                    }
                },

                getQuadrantTeeth(teethArray, pos) {
                    return (teethArray || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).sort((a,b) => a - b);
                },

                get groupedTeeth() {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    const vals = this.appointmentFormValues[fieldName] || [];
                    const sorted = [...vals].sort((a, b) => {
                        const posOrder = { 'UR': 1, 'UL': 2, 'LR': 3, 'LL': 4 };
                        return posOrder[this.getToothLabel(a).pos] - posOrder[this.getToothLabel(b).pos];
                    });
                    const groups = { 'UR': [], 'UL': [], 'LR': [], 'LL': [] };
                    sorted.forEach(t => groups[this.getToothLabel(t).pos].push(t));
                    return Object.entries(groups).filter(([key, val]) => val.length > 0);
                },

                toggle(toothId) {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    if (!Array.isArray(this.appointmentFormValues[fieldName])) {
                        this.appointmentFormValues[fieldName] = [];
                    }
                    const idx = this.appointmentFormValues[fieldName].indexOf(toothId);
                    if (idx > -1) {
                        this.appointmentFormValues[fieldName].splice(idx, 1);
                    } else {
                        this.appointmentFormValues[fieldName].push(toothId);
                    }
                    this.preset = 'none';
                },

                is(toothId) {
                    const fieldName = this.appointmentFormSchema?.fields?.find(f => f.type === 'tooth_number')?.name || 'tooth_numbers';
                    const vals = this.appointmentFormValues[fieldName];
                    const isSelected = Array.isArray(vals) && vals.includes(toothId);
                    let cls = isSelected ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                    if (this.clientToothPlans && this.clientToothPlans[toothId]) {
                        cls += ' !stroke-emerald-500 !stroke-[2.5px]';
                    }
                    return cls;
                },

                async fetchAppointmentForm(formId) {
                    this.appointmentFormSchema = null;
                    this.appointmentFormValues = {};
                    if (!formId) return;

                    const params = new URLSearchParams({
                        form_id: formId
                    });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.form') }}?` + params.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    const schema = json.data?.schema_json || null;
                    if (!schema || !Array.isArray(schema.fields)) {
                        this.appointmentFormSchema = {
                            fields: []
                        };
                        return;
                    }

                    this.appointmentFormSchema = schema;
                    const authUserId = @json(auth()->id());
                    for (const field of schema.fields) {
                        if (field.type === 'checkbox' || field.type === 'tooth_number' || (field.type === 'select-user-by-role' && field.multiple)) {
                            this.appointmentFormValues[field.name] = [];
                        } else {
                            this.appointmentFormValues[field.name] = '';
                        }

                        if (field.type === 'select-user-by-role' && field.lock_current_if_role && authUserId && Array.isArray(field.user_options)) {
                            const isMatch = field.user_options.some(u => Number(u.id) === Number(authUserId));
                            if (isMatch) {
                                this.appointmentFormValues[field.name] = field.multiple ? [authUserId] : authUserId;
                            }
                        }
                    }

                    if (this.selectedWaitlistObject?.appointment_form_response_json && typeof this.selectedWaitlistObject.appointment_form_response_json === 'object') {
                        for (const [k, v] of Object.entries(this.selectedWaitlistObject.appointment_form_response_json)) {
                            this.appointmentFormValues[k] = v;
                        }
                    }
                },

                // ---------------- calendar ----------------

                get monthLabel() {
                    const monthName = this.persianMonths[this.calendarMonth - 1] || 'نامشخص';
                    return `${monthName} ${this.calendarYear}`;
                },

                get calendarCells() {
                    const days = this.calendarDays || [];
                    if (!days.length) return [];

                    const first = days[0].local_date;
                    const firstDate = new Date(first + 'T00:00:00');
                    const persianWeekdayIndex = (firstDate.getDay() + 1) % 7;

                    const cells = [];
                    for (let i = 0; i < persianWeekdayIndex; i++) {
                        cells.push({
                            key: `ph-${i}`,
                            is_placeholder: true
                        });
                    }

                    for (const d of days) {
                        cells.push({
                            key: d.local_date,
                            is_placeholder: false,
                            day: d
                        });
                    }

                    while (cells.length % 7 !== 0) {
                        cells.push({
                            key: `ph-end-${cells.length}`,
                            is_placeholder: true
                        });
                    }

                    return cells;
                },
                async loadCalendar() {
                    if (!this.serviceId || !this.providerId) return;

                    this.calendarLoading = true;
                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        year: this.calendarYear,
                        month: this.calendarMonth
                    });

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.calendar') }}?` + params
                            .toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.calendarDays = json.data || [];
                    } finally {
                        this.calendarLoading = false;
                    }
                },

                prevMonth() {
                    this.calendarMonth--;
                    if (this.calendarMonth < 1) {
                        this.calendarMonth = 12;
                        this.calendarYear--;
                    }
                    this.loadCalendar();
                },

                nextMonth() {
                    this.calendarMonth++;
                    if (this.calendarMonth > 12) {
                        this.calendarMonth = 1;
                        this.calendarYear++;
                    }
                    this.loadCalendar();
                },

                async selectDay(day) {
                    if (day.is_closed || !day.has_available_slots) return;
                    this.dateLocal = day.local_date;
                    this.slots = [];
                    this.selectedSlotKey = '';
                    this.manualStartTime = '';
                    this.manualEndTime = '';
                    if (this.selectedWaitlistObject?.duration_minutes) {
                        this.manualDuration = this.selectedWaitlistObject.duration_minutes;
                    } else if (this.selectedService?.duration_min) {
                        this.manualDuration = this.selectedService.duration_min;
                    } else {
                        this.manualDuration = '';
                    }
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                    if (this.step === 4) {
                        await this.next();
                    }
                },

                isPreferredDay(localDate) {
                    if (!this.selectedWaitlistObject || !this.selectedWaitlistObject.preferred_date || !localDate) return false;
                    try {
                        const dd = new Date(localDate + 'T00:00:00');
                        const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        });
                        const parts = formatter.formatToParts(dd);
                        const y = parts.find(p => p.type === 'year').value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                        const m = parts.find(p => p.type === 'month').value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                        const d = parts.find(p => p.type === 'day').value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                        const formattedJalali = `${y}/${m.padStart(2, '0')}/${d.padStart(2, '0')}`;
                        
                        const prefClean = String(this.selectedWaitlistObject.preferred_date).replace(/[-\\]/g, '/');
                        return formattedJalali === prefClean || `${y}/${parseInt(m)}/${parseInt(d)}` === prefClean;
                    } catch(e) {
                        return false;
                    }
                },

                dayBtnClass(d) {
                    const isSelected = this.dateLocal && this.dateLocal === d.local_date;
                    const isPref = this.isPreferredDay(d.local_date);

                    if (!d.is_closed && !d.has_available_slots)
                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    if (d.is_closed)
                        return 'bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700';
                    if (isSelected)
                        return 'bg-indigo-50 text-indigo-700 border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-500 shadow-sm';
                    if (d.capacity_per_day !== null && d.remaining_day_capacity === 0)
                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    if (isPref)
                        return 'bg-amber-50/60 dark:bg-amber-950/20 text-gray-800 dark:text-gray-100 border-amber-400 dark:border-amber-500 ring-2 ring-amber-300/40 hover:bg-amber-50 dark:hover:bg-amber-900/30';
                    return 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/70';
                },

                toPersianDayNumber(localDate) {
                    const dd = new Date(localDate + 'T00:00:00');
                    return dd.toLocaleDateString('fa-IR-u-ca-persian', {
                        day: 'numeric'
                    });
                },

                // ---------------- history ----------------
                async openHistoryModal(localDate) {
                    this.historyModalDate = localDate;
                    this.showHistoryModal = true;
                    this.historyModalItems = [];
                    this.historyModalStats = {};
                    this.historyModalSuggestedTime = null;
                    this.historyModalLoading = true;

                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        date_local: localDate
                    });

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.history') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        this.historyModalItems = json.data || [];
                        this.historyModalStats = json.stats || {};
                        this.historyModalSuggestedTime = json.suggested_time || null;
                    } catch (e) {
                        console.error('Failed to fetch history', e);
                    } finally {
                        this.historyModalLoading = false;
                    }
                },

                closeHistoryModal() {
                    this.showHistoryModal = false;
                },

                // ---------------- slots ----------------

                async fetchSlots() {
                    if (!this.serviceId || !this.providerId || !this.dateLocal) return;

                    this.slotsLoading = true;
                    this.slotsError = '';
                    this.slots = [];

                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        provider_id: this.providerId,
                        from_local_date: this.dateLocal,
                        to_local_date: this.dateLocal,
                        viewer_timezone: @json(config('booking.timezones.display_default')),
                    });

                    try {
                        const res = await fetch('/api/booking/availability/slots?' + params.toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!res.ok) throw new Error('خطا در دریافت اسلات‌ها (کد ' + res.status + ')');
                        const json = await res.json();
                        this.slots = json.data || [];
                    } catch (e) {
                        this.slotsError = e.message || 'خطای ناشناخته در دریافت اسلات‌ها';
                    } finally {
                        this.slotsLoading = false;
                    }
                },

                isSlotDisabled(slot) {
                    if (!slot) return false;
                    if (slot.sync_blocked) return true;
                    if (slot.remaining_capacity !== null && slot.remaining_capacity !== undefined && Number(slot.remaining_capacity) <= 0) {
                        return true;
                    }
                    return false;
                },

                async selectSlot(slot) {
                    if (this.isSlotDisabled(slot)) return;
                    this.selectedSlotKey = slot.start_at_utc;
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = slot.start_at_utc;
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = slot.end_at_utc;
                    if (this.step === 5) {
                        await this.next();
                    }
                },

                clearSlotSelection() {
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                calculateEndTime() {
                    this.clearSlotSelection();
                    if (!this.manualStartTime || !this.manualDuration) return;

                    const parts = this.manualStartTime.split(':');
                    if (parts.length !== 2) return;

                    const h = parseInt(parts[0]);
                    const m = parseInt(parts[1]);
                    const dur = parseInt(this.manualDuration);

                    if (isNaN(h) || isNaN(m) || isNaN(dur)) return;

                    let totalMinutes = h * 60 + m + dur;
                    totalMinutes = totalMinutes % 1440;

                    const endH = Math.floor(totalMinutes / 60);
                    const endM = totalMinutes % 60;

                    this.manualEndTime = `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;
                },

                isCustomScheduleEnabled() {
                    const serviceForced = Boolean(this.selectedService && this.selectedService.custom_schedule_enabled);
                    return serviceForced || this.manualModeActive;
                },

                formatTime(isoString) {
                    const d = new Date(isoString);
                    return d.toLocaleTimeString('fa-IR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                // ---------------- clients ----------------

                get selectedClient() {
                    if (!this.clientId) return null;
                    // Return stored client object if available
                    if (this.selectedClientObject && String(this.selectedClientObject.id) === String(this.clientId)) {
                        return this.selectedClientObject;
                    }
                    // Try to find in current clients array
                    if (this.clients && this.clients.length > 0) {
                        const found = this.clients.find(c => String(c.id) === String(this.clientId));
                        if (found) {
                            this.selectedClientObject = found;
                            return found;
                        }
                    }
                    return null;
                },

                get selectedClientName() {
                    const client = this.selectedClient;
                    return client ? client.full_name : '';
                },

                get selectedClientPhone() {
                    const client = this.selectedClient;
                    if (!client) return '';
                    const parts = [];
                    if (client.phone) parts.push(client.phone);
                    if (client.national_code) parts.push(`کد ملی: ${client.national_code}`);
                    if (client.case_number) parts.push(`پرونده: ${client.case_number}`);
                    return parts.join(' • ');
                },

                get selectedClientEmail() {
                    const client = this.selectedClient;
                    return client && client.email ? client.email : '';
                },

                selectClient(client) {
                    this.clientId = String(client.id);
                    this.selectedClientObject = client;
                    this.waitlistId = '';
                    this.selectedWaitlistObject = null;
                    // Keep the selected client in the list if not already there
                    if (!this.clients.find(c => String(c.id) === String(client.id))) {
                        this.clients = [client, ...this.clients];
                    }
                    this.clientSearch = '';
                },

                async selectWaitlistEntry(item) {
                    this.waitlistId = String(item.id);
                    this.selectedWaitlistObject = item;
                    this.clientId = String(item.client_id);
                    this.selectedClientObject = {
                        id: item.client_id,
                        full_name: item.client_name,
                        phone: item.client_phone,
                        national_code: item.client_national_code,
                        case_number: item.client_case_number,
                        email: item.client_email,
                    };
                    if (item.service_id) {
                        this.serviceId = String(item.service_id);
                    }
                    if (item.provider_user_id) {
                        this.providerId = String(item.provider_user_id);
                    }
                    if (item.duration_minutes) {
                        this.manualDuration = item.duration_minutes;
                    }
                    if (item.appointment_form_response_json && typeof item.appointment_form_response_json === 'object') {
                        this.appointmentFormValues = { ...item.appointment_form_response_json };
                    }
                    if (item.notes && !this.notes) {
                        this.notes = item.notes;
                    }

                    if (this.flow === 'PROVIDER_FIRST') {
                        if (this.providerId) {
                            await this.fetchCategories();
                            await this.fetchServicesForProvider();
                        }
                    } else if (this.flow === 'SERVICE_FIRST') {
                        await this.fetchAllActiveServices();
                        if (this.serviceId) {
                            await this.fetchProviders(false);
                        }
                    }

                    if (this.serviceId) {
                        await this.onServiceSelected();
                    }
                },

                clearSelectedClient() {
                    this.clientId = '';
                    this.selectedClientObject = null;
                    this.waitlistId = '';
                    this.selectedWaitlistObject = null;
                    this.clientSearch = '';
                    this.fetchClients();
                },

                async fetchWaitlist() {
                    if (!this.isQueueEnabled) return;
                    this.waitlistLoading = true;
                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.waitlist') }}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.waitlist = json.data || [];
                    } catch(e) {
                        console.error('Failed to fetch waitlist:', e);
                    } finally {
                        this.waitlistLoading = false;
                    }
                },

                async fetchClients() {
                    this.clientLoading = true;
                    const params = new URLSearchParams({
                        q: this.clientSearch || ''
                    });
                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.clients') }}?` + params
                            .toString(), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        this.clients = json.data || [];
                        // If we have a selected clientId, make sure it's in the list
                        if (this.clientId && !this.clients.find(c => String(c.id) === String(this.clientId))) {
                            // Client not in search results, but that's ok - we'll show it anyway via selectedClient
                        }
                    } finally {
                        this.clientLoading = false;
                    }
                },

                // ---------------- wizard navigation ----------------

                async next() {
                    if (this.step === 1) {
                        if (!this.clientId) return alert('لطفاً {{ $clientLabel }} را انتخاب کنید.');
                        // Load providers/services when moving from step 1
                        if (this.flow === 'PROVIDER_FIRST') {
                            if (!this.fixedProvider) {
                                await this.fetchProviders(true);
                            }
                            if (this.providerId) {
                                if (!this.services || this.services.length === 0) {
                                    await this.fetchCategories();
                                    await this.fetchServicesForProvider();
                                }
                                if (this.serviceId) {
                                    await this.onServiceSelected();
                                    await this.loadCalendar();
                                    this.step = 4;
                                    return;
                                }
                                this.step = 3;
                                return;
                            }
                        } else if (this.flow === 'SERVICE_FIRST') {
                            await this.fetchAllActiveServices();
                            if (this.serviceId) {
                                await this.onServiceSelected();
                                if (this.providerId) {
                                    await this.loadCalendar();
                                    this.step = 4;
                                    return;
                                }
                                this.step = 3;
                                return;
                            }
                        }
                    }

                    if (this.step === 2) {
                        if (this.flow === 'PROVIDER_FIRST') {
                            if (!this.providerId) return alert('لطفاً {{ config('booking.labels.provider') }} را انتخاب کنید.');
                            if (!this.services || this.services.length === 0) {
                                await this.fetchCategories();
                                await this.fetchServicesForProvider();
                            }
                            if (this.serviceId) {
                                await this.onServiceSelected();
                                await this.loadCalendar();
                                this.step = 4;
                                return;
                            }
                        }
                        if (this.flow === 'SERVICE_FIRST') {
                            if (!this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                            if (!this.providers || this.providers.length === 0) {
                                await this.fetchProviders(false);
                            }
                            if (this.providerId) {
                                await this.loadCalendar();
                                this.step = 4;
                                return;
                            }
                        }
                    }

                    if (this.step === 3) {
                        if (!this.providerId) return alert('لطفاً {{ config('booking.labels.provider') }} را انتخاب کنید.');
                        if (!this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                        // Ensure services are loaded if not already
                        if (this.flow === 'PROVIDER_FIRST' && (!this.services || this.services.length === 0)) {
                            await this.onProviderSelected();
                        }
                        await this.loadCalendar();
                    }

                    if (this.step === 4) {
                        if (!this.dateLocal) return alert('لطفاً یک روز قابل رزرو انتخاب کنید.');
                        if (!this.isCustomScheduleEnabled()) {
                            await this.fetchSlots();
                        }
                    }

                    if (this.step === 5) {
                        if (this.isCustomScheduleEnabled()) {
                            if (!this.manualStartTime || !this.manualEndTime) {
                                return alert('لطفاً ساعت شروع و پایان را وارد کنید.');
                            }
                        } else if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) {
                            return alert('لطفاً یک اسلات زمانی را انتخاب کنید.');
                        }

                        // Prepare appointment form if needed before moving to form step
                        if (this.hasAppointmentForm) {
                            this.prepareAppointmentFormJson();
                        }
                    }

                    if (this.step < this.totalSteps) {
                        this.step++;
                    }
                },

                prev() {
                    if (this.step > 1) this.step--;
                },

                async initCalendarIfReady() {
                    if (this.serviceId && this.providerId) {
                        await this.loadCalendar();
                    }
                },

                handleSubmit(e) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    console.log('handleSubmit called', {
                        isSubmitting: this.isSubmitting,
                        serviceId: this.serviceId,
                        providerId: this.providerId,
                        dateLocal: this.dateLocal,
                        clientId: this.clientId,
                        step: this.step,
                        finalStep: this.finalStep
                    });

                    if (this.isSubmitting) {
                        console.log('Already submitting, returning');
                        return false;
                    }

                    if (!this.serviceId || !this.providerId) {
                        alert('سرویس/{{ config('booking.labels.provider') }} ناقص است.');
                        return false;
                    }
                    if (!this.dateLocal) {
                        alert('روز انتخاب نشده است.');
                        return false;
                    }
                    if (this.isCustomScheduleEnabled()) {
                        if (!this.manualStartTime || !this.manualEndTime) {
                            alert('لطفاً ساعت شروع و پایان را وارد کنید.');
                            return false;
                        }
                    } else {
                        if (!this.$refs.startUtcInput || !this.$refs.startUtcInput.value || !this.$refs.endUtcInput || !this
                            .$refs.endUtcInput.value) {
                            alert('لطفاً یک اسلات زمانی را انتخاب کنید.');
                            return false;
                        }
                    }
                    if (!this.clientId) {
                        alert('لطفاً {{ $clientLabel }} را انتخاب کنید.');
                        return false;
                    }

                    console.log('All validations passed, preparing form...');
                    this.prepareAppointmentFormJson();
                    this.isSubmitting = true;

                    if (this.$refs.realForm) {
                        console.log('Submitting form...');
                        this.$refs.realForm.submit();
                    } else {
                        console.error('Form reference not found');
                        this.isSubmitting = false;
                        alert('خطا: فرم یافت نشد. لطفاً صفحه را رفرش کنید.');
                    }

                    return false;
                },

                prepareAppointmentFormJson() {
                    if (this.selectedService && this.selectedService.appointment_form_id && this.appointmentFormSchema) {
                        this.$refs.formJsonInput.value = JSON.stringify(this.appointmentFormValues || {});
                    } else {
                        this.$refs.formJsonInput.value = '';
                    }
                },

                async fetchClientPlans() {
                    if (!this.clientId) {
                        this.clientToothPlans = {};
                        return;
                    }
                    try {
                        const res = await fetch(`/user/booking/cure/client-plans/${this.clientId}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        if (json.success) {
                            this.clientToothPlans = json.tooth_treatments || {};
                        } else {
                            this.clientToothPlans = {};
                        }
                    } catch(e) {
                        console.error('Failed to fetch client plans:', e);
                        this.clientToothPlans = {};
                    }
                },
            }
        }
    </script>
@endsection
