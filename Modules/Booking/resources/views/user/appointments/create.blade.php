@extends('layouts.user')
@php
    $fixedProviderPayload = (isset($fixedProvider) && $fixedProvider)
    ? ['id' => $fixedProvider->id, 'name' => $fixedProvider->name]
    : null;
@endphp

@section('content')
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
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">جریان اپراتوری با انتخاب سرویس/{{ config('booking.labels.provider') }} و اسلات
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

        @includeIf('partials.jalali-date-picker')

        <form method="POST" action="{{ route('user.booking.appointments.store') }}"
              class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 space-y-5"
              x-ref="form" @submit.prevent="handleSubmit">
            @csrf

            <input type="hidden" name="service_id" x-model="serviceId">
            <input type="hidden" name="provider_user_id" x-model="providerId">
            <input type="hidden" name="client_id" x-model="clientId">
            <input type="hidden" name="start_at_utc" x-ref="startUtcInput">
            <input type="hidden" name="end_at_utc" x-ref="endUtcInput">
            <input type="hidden" name="date_local" x-model="dateLocal">
            <input type="hidden" name="start_time_local" x-model="manualStartTime">
            <input type="hidden" name="end_time_local" x-model="manualEndTime">
            <input type="hidden" name="appointment_form_response_json" x-ref="formJsonInput">

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
                <div
                    class="bg-gradient-to-l from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xl">👤</span>
                        </div>
                        <div>
                            <div class="font-semibold text-base text-gray-800 dark:text-gray-100">انتخاب مشتری</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">مشتری مورد نظر را انتخاب کنید</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 space-y-3">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                جستجو مشتری
                            </span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-3 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="نام، شماره تماس، کد ملی یا شماره پرونده..." x-model="clientSearch"
                                       @input.debounce.300ms="fetchClients()" @focus="clientSearchFocused = true"
                                       @blur="setTimeout(() => clientSearchFocused = false, 200)">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <div class="mt-3" x-show="clientSearch && clients.length > 0">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    نتایج جستجو (<span x-text="clients.length"></span> مورد)
                                </div>
                                <div
                                    class="max-h-64 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                                    <template x-for="c in clients" :key="c.id">
                                        <button type="button"
                                                class="w-full text-right border-2 rounded-xl p-3 transition-all duration-200 hover:shadow-md"
                                                :class="String(clientId) === String(c.id)
                                            ? 'border-indigo-600 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/40 dark:to-indigo-950/40 text-indigo-900 dark:text-indigo-100 shadow-md ring-2 ring-indigo-200 dark:ring-indigo-800'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:border-indigo-300 dark:hover:border-indigo-600'"
                                                @click="selectClient(c);">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1">
                                                    <div class="font-semibold text-sm mb-1" x-text="c.full_name"></div>
                                                    <div
                                                        class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span x-show="c.phone" x-text="`📞 ${c.phone}`"></span>
                                                        <span x-show="c.national_code"
                                                              x-text="`🆔 ${c.national_code}`"></span>
                                                        <span x-show="c.case_number" x-text="`📋 ${c.case_number}`"></span>
                                                        <span x-show="c.email" x-text="`✉ ${c.email}`"></span>
                                                    </div>
                                                </div>
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                                     :class="String(clientId) === String(c.id)
                                                     ? 'bg-indigo-600 text-white'
                                                     : 'bg-gray-100 dark:bg-gray-700 text-gray-400'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24" x-show="String(clientId) === String(c.id)">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-3" x-show="clientSearch && clients.length === 0 && !clientLoading">
                                <div
                                    class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-4 text-center">
                                    <div class="text-sm text-amber-800 dark:text-amber-200">مشتری یافت نشد</div>
                                </div>
                            </div>

                            <template x-if="clientLoading">
                                <div class="mt-3 flex items-center justify-center py-4">
                                    <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">در حال جستجو...</span>
                                    </div>
                                </div>
                            </template>

                            <div class="mt-3" x-show="clientId">
                                <div
                                    class="bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-300 dark:border-emerald-700 rounded-xl p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-sm text-emerald-900 dark:text-emerald-100"
                                                 x-text="selectedClientName"></div>
                                            <div class="text-xs text-emerald-700 dark:text-emerald-300 mt-1"
                                                 x-show="selectedClientPhone" x-text="selectedClientPhone"></div>
                                            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1"
                                                 x-show="selectedClientEmail" x-text="selectedClientEmail"></div>
                                        </div>
                                        <button type="button"
                                                class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                                                @click="clientId = ''; selectedClientObject = null; clientSearch = ''; fetchClients();">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            مشتری جدید
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            @includeIf('clients::widgets.client-quick-create')
                        </div>
                    </div>
                </div>

                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-lg p-3 flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-xs text-blue-800 dark:text-blue-200">
                        بعد از انتخاب مشتری، می‌توانید با دکمه "بعدی" به مرحله بعد بروید.
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
                                            <div class="font-semibold text-sm mb-1" x-text="p.name"></div>
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

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب سرویس</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو سرویس..." x-model="serviceSearch"
                                   @input.debounce.300ms="fetchServicesForServiceFirst()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="serviceLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت سرویس‌ها...</div>
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
                            بعد از انتخاب سرویس، به صورت خودکار به مرحله بعد می‌روید.
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
                            <label class="block text-sm mb-1 dark:text-gray-200">انتخاب سرویس</label>
                            <div class="relative">
                                <input type="text"
                                       class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="جستجو سرویس..." x-model="serviceSearch"
                                       @input.debounce.300ms="fetchServicesForProvider()">
                                <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                            </div>

                            <template x-if="serviceLoading">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">در حال دریافت سرویس‌ها...</div>
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

                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">فقط سرویس‌هایی که برای این
                                {{ config('booking.labels.provider') }} فعال هستند نمایش داده می‌شود. بعد از انتخاب، به صورت خودکار به مرحله بعد
                                می‌روید.</div>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST' && !fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب {{ config('booking.labels.provider') }} (برای
                            سرویس انتخابی)</label>
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
                                    <button type="button" class="w-full h-[52px] border rounded-lg p-2 text-center"
                                            :class="dayBtnClass(cell.day)" @click="selectDay(cell.day)"
                                            :disabled="cell.day.is_closed || !cell.day.has_available_slots">
                                        <div class="font-semibold" x-text="toPersianDayNumber(cell.day.local_date)"></div>
                                        <div class="text-[10px] mt-1" x-show="cell.day.is_closed">تعطیل</div>
                                        <div class="text-[10px] mt-1"
                                             x-show="!cell.day.is_closed && !cell.day.has_available_slots">پر</div>
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
                            بعد از انتخاب روز، در مرحله بعد اسلات‌های زمانی نمایش داده می‌شوند.
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
                                    class="group relative border-2 rounded-xl p-3 text-center transition-all duration-200 hover:shadow-lg"
                                    :class="selectedSlotKey === slot.start_at_utc
                                ? 'border-indigo-600 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/40 dark:to-indigo-950/40 text-indigo-900 dark:text-indigo-100 shadow-md ring-2 ring-indigo-200 dark:ring-indigo-800'
                                : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md'"
                                    @click="selectSlot(slot)">
                                <div class="font-bold text-sm mb-1" x-text="formatTime(slot.start_at_view)"></div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                    ظرفیت: <span class="font-semibold" x-text="slotCapacityDisplay(slot)"></span>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-gray-300">ساعت شروع</label>
                            <input type="text" data-jdp-only-time
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100"
                                   x-model="manualStartTime" @input="clearSlotSelection()">
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

                                            <template x-if="!['textarea','select','radio','checkbox'].includes(field.type)">
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
                            <div class="text-xs text-gray-500 dark:text-gray-400">لطفاً یادداشت را وارد کرده و نوبت را
                                ثبت کنید</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
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
                              placeholder="یادداشت یا توضیحات اضافی را اینجا وارد کنید..."></textarea>
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
        </form>
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
                clients: [],
                clientSearch: '',
                clientId: '',
                selectedClientObject: null,
                clientLoading: false,
                clientSearchFocused: false,
                isSubmitting: false,

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
                        title: 'انتخاب مشتری',
                        icon: '👤'
                    },
                        {
                            num: 2,
                            title: this.flow === 'PROVIDER_FIRST' ? 'انتخاب {{ config('booking.labels.provider') }}' : 'انتخاب سرویس',
                            icon: this.flow === 'PROVIDER_FIRST' ? '👨‍⚕️' : '🛎️'
                        },
                        {
                            num: 3,
                            title: this.flow === 'PROVIDER_FIRST' ? 'انتخاب سرویس' : 'انتخاب {{ config('booking.labels.provider') }}',
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

                    // Start with client selection (step 1)
                    this.step = 1;
                    this.fetchClients();

                    window.addEventListener('client-quick-saved', (e) => {
                        const newId = e?.detail?.clientId;
                        this.fetchClients().then(() => {
                            if (newId) {
                                this.clientId = String(newId);
                                // Find and store the new client object
                                const newClient = this.clients.find(c => String(c.id) === String(newId));
                                if (newClient) {
                                    this.selectedClientObject = newClient;
                                }
                            }
                        });
                    });
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

                async fetchProviders() {
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
                        await this.next();
                    }
                    if (autoGo && this.step === 3 && this.flow === 'SERVICE_FIRST') {
                        await this.next();
                    }
                },

                async onProviderSelected() {
                    if (this.flow === 'PROVIDER_FIRST') {
                        this.categoryId = '';
                        this.serviceId = '';
                        this.services = [];
                        this.categories = [];
                        this.resetCalendarAndSlots();

                        await this.fetchCategories();
                        await this.fetchServicesForProvider();
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
                        if (this.fixedProvider && this.step === 3) {
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
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                resetAppointmentForm() {
                    this.appointmentFormSchema = null;
                    this.appointmentFormValues = {};
                    this.hasAppointmentForm = false;
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
                    for (const field of schema.fields) {
                        if (field.type === 'checkbox') {
                            this.appointmentFormValues[field.name] = [];
                        } else {
                            this.appointmentFormValues[field.name] = '';
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
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                    if (this.step === 4) {
                        await this.next();
                    }
                },

                dayBtnClass(d) {
                    const isSelected = this.dateLocal && this.dateLocal === d.local_date;
                    if (!d.is_closed && !d.has_available_slots)
                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    if (d.is_closed)
                        return 'bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700';
                    if (isSelected)
                        return 'bg-indigo-50 text-indigo-700 border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-500';
                    if (d.capacity_per_day !== null && d.remaining_day_capacity === 0)
                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    return 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/70';
                },

                toPersianDayNumber(localDate) {
                    const dd = new Date(localDate + 'T00:00:00');
                    return dd.toLocaleDateString('fa-IR-u-ca-persian', {
                        day: 'numeric'
                    });
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

                async selectSlot(slot) {
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

                isCustomScheduleEnabled() {
                    return Boolean(this.selectedService && this.selectedService.custom_schedule_enabled);
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
                    // Keep the selected client in the list if not already there
                    if (!this.clients.find(c => String(c.id) === String(client.id))) {
                        this.clients = [client, ...this.clients];
                    }
                    this.clientSearch = '';
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
                        if (!this.clientId) return alert('لطفاً مشتری را انتخاب کنید.');
                        // Load providers/services when moving to step 2
                        if (this.flow === 'PROVIDER_FIRST' && !this.fixedProvider) {
                            await this.fetchProviders();
                        } else if (this.flow === 'SERVICE_FIRST') {
                            await this.fetchAllActiveServices();
                        }
                    }

                    if (this.step === 2) {
                        if (this.flow === 'PROVIDER_FIRST' && !this.providerId) {
                            return alert('لطفاً {{ config('booking.labels.provider') }} را انتخاب کنید.');
                        }
                        if (this.flow === 'SERVICE_FIRST' && !this.serviceId) {
                            return alert('لطفاً سرویس را انتخاب کنید.');
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
                        alert('لطفاً مشتری را انتخاب کنید.');
                        return false;
                    }

                    console.log('All validations passed, preparing form...');
                    this.prepareAppointmentFormJson();
                    this.isSubmitting = true;

                    if (this.$refs.form) {
                        console.log('Submitting form...');
                        this.$refs.form.submit();
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
            }
        }
    </script>
@endsection
