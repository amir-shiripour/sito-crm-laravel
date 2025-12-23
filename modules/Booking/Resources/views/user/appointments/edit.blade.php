@extends('layouts.user')

@php
    /** @var \Modules\Booking\Entities\Appointment $appointment */
    $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
    $displayTz = config('booking.timezones.display_default', $scheduleTz);

    $startSchedule = $appointment->start_at_utc?->copy()->timezone($scheduleTz);
    $endSchedule = $appointment->end_at_utc?->copy()->timezone($scheduleTz);

    $initialPayload = [
        'provider_id' => $appointment->provider_user_id,
        'service_id' => $appointment->service_id,
        'client_id' => $appointment->client_id,
        'date_local' => $startSchedule?->format('Y-m-d'),
        'start_at_utc' => $appointment->start_at_utc?->toIso8601String(),
        'end_at_utc' => $appointment->end_at_utc?->toIso8601String(),
        'manual_start_time' => $startSchedule?->format('H:i'),
        'manual_end_time' => $endSchedule?->format('H:i'),
        'appointment_form_values' => $appointment->appointment_form_response_json ?? [],
    ];

    $entryTime = old('entry_time_local', $appointment->entry_at_utc?->copy()->timezone($displayTz)->format('H:i'));
    $exitTime = old('exit_time_local', $appointment->exit_at_utc?->copy()->timezone($displayTz)->format('H:i'));
@endphp

@section('content')
    <div class="space-y-5" data-initial='@json($initialPayload)'
         x-data="operatorWizard({ initial: null })" x-init="
    const raw = $el.dataset.initial;
    try { initial = raw ? JSON.parse(raw) : null } catch(e) { initial = null }
    init();
  ">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش نوبت (مرحله‌ای)</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش سرویس، ارائه‌دهنده، اسلات و وضعیت نوبت</p>
            </div>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
               href="{{ route('user.booking.appointments.show', $appointment) }}">
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

        <form method="POST" action="{{ route('user.booking.appointments.update', $appointment) }}"
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

            <div
                class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 px-3 py-2">
                <div class="text-sm text-gray-700 dark:text-gray-200 font-semibold flex items-center gap-2">
                    <span>مرحله</span>
                    <span
                        class="px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 text-xs"
                        x-text="step"></span>
                    <span class="text-gray-500 dark:text-gray-400">از</span>
                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 text-xs"
                          x-text="totalSteps"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
                            @click="prev()" :disabled="step===1">قبلی</button>

                    <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm shadow-sm"
                            @click="next()" x-show="step < totalSteps && step > 2">بعدی</button>
                </div>
            </div>

            {{-- STEP 1 --}}
            <div x-show="step===1" class="space-y-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    @php $flowValue = $settings->operator_appointment_flow ?? 'PROVIDER_FIRST'; @endphp
                    حالت انتخاب: <span
                        class="font-semibold">{{ $flowValue === 'SERVICE_FIRST' ? 'اول سرویس' : 'اول ارائه‌دهنده' }}</span>
                </div>

                <template x-if="flow==='PROVIDER_FIRST'">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب ارائه‌دهنده</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو ارائه‌دهنده..." x-model="providerSearch"
                                   @input.debounce.300ms="fetchProviders()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="providerLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت ارائه‌دهنده‌ها...</div>
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
                            بعد از انتخاب ارائه‌دهنده، به صورت خودکار به مرحله بعد می‌روید.
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب سرویس</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو سرویس..." x-model="serviceSearch"
                                   @input.debounce.300ms="fetchAllActiveServices()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="serviceLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت سرویس‌ها...</div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="s in services" :key="s.id">
                                <button type="button" class="text-right border rounded-xl p-3 transition"
                                        :class="String(serviceId)===String(s.id)
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                        @click="selectService(s, true)">
                                    <div class="font-semibold text-sm" x-text="s.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="s.category_name"
                                         x-text="s.category_name"></div>
                                </button>
                            </template>
                        </div>

                        <template x-if="!serviceLoading && (!services || services.length===0)">
                            <div class="text-xs text-amber-600">موردی یافت نشد.</div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- STEP 2 --}}
            <div x-show="step===2" class="space-y-3">
                <template x-if="flow==='PROVIDER_FIRST'">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب سرویس</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو سرویس..." x-model="serviceSearch"
                                   @input.debounce.300ms="fetchServicesForProvider()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="serviceLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت سرویس‌ها...</div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="group in groupedServices" :key="group.key">
                                <div class="space-y-2">
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="group.title"></div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <template x-for="s in group.items" :key="s.id">
                                            <button type="button" class="text-right border rounded-xl p-3 transition"
                                                    :class="String(serviceId)===String(s.id)
                                                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                                    @click="selectService(s, true)">
                                                <div class="font-semibold text-sm" x-text="s.name"></div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="s.category_name"
                                                     x-text="s.category_name"></div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">انتخاب ارائه‌دهنده</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="جستجو ارائه‌دهنده..." x-model="providerSearch"
                                   @input.debounce.300ms="fetchProviders()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="providerLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت ارائه‌دهنده‌ها...</div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="p in providers" :key="p.id">
                                <button type="button" class="text-right border rounded-xl p-3 transition"
                                        :class="String(providerId)===String(p.id)
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                        @click="selectProvider(p, true)">
                                    <div class="font-semibold text-sm" x-text="p.name"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- STEP 3: Calendar month --}}
            <div x-show="step===3" class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">انتخاب روز</div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="px-2 py-1 border rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70"
                                @click="prevMonth()">ماه قبل</button>
                        <div class="text-sm text-gray-700 dark:text-gray-100" x-text="monthLabel"></div>
                        <button type="button"
                                class="px-2 py-1 border rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70"
                                @click="nextMonth()">ماه بعد</button>
                    </div>
                </div>

                <template x-if="calendarLoading">
                    <div class="text-xs text-gray-500 dark:text-gray-400">در حال بارگذاری تقویم...</div>
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

                <div class="text-xs text-gray-500 dark:text-gray-400">
                    بعد از انتخاب روز، در مرحله بعد اسلات‌ها نمایش داده می‌شوند.
                </div>
            </div>

            {{-- STEP 4: Slots --}}
            <div x-show="step===4" class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">انتخاب اسلات زمانی</div>
                    <button type="button"
                            class="text-xs px-3 py-1 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70"
                            @click="fetchSlots()" x-show="!isCustomScheduleEnabled()">بروزرسانی</button>
                </div>

                <template x-if="slotsLoading && !isCustomScheduleEnabled()">
                    <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت اسلات‌ها...</div>
                </template>

                <template x-if="slotsError && !isCustomScheduleEnabled()">
                    <div class="text-xs text-red-600" x-text="slotsError"></div>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2"
                     x-show="slots.length && !slotsLoading && !isCustomScheduleEnabled()">
                    <template x-for="slot in slots" :key="slot.start_at_utc">
                        <button type="button"
                                class="border rounded px-2 py-2 text-xs text-center hover:bg-indigo-50 dark:hover:bg-indigo-950/40"
                                :class="selectedSlotKey === slot.start_at_utc ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-200' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800'"
                                @click="selectSlot(slot)">
                            <div class="font-semibold" x-text="formatTime(slot.start_at_view)"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                ظرفیت: <span x-text="slotCapacityDisplay(slot)"></span>
                            </div>
                        </button>
                    </template>
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

            {{-- STEP 5: Appointment Form --}}
            <div x-show="step===5 && hasAppointmentForm" class="space-y-3">
                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">فرم اطلاعات نوبت</div>

                <template x-if="selectedService && selectedService.appointment_form_id">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400" x-show="!appointmentFormSchema">
                            در حال دریافت فرم...
                        </div>

                        <template
                            x-if="appointmentFormSchema && appointmentFormSchema.fields && appointmentFormSchema.fields.length">
                            <div class="space-y-3">
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
                    <div class="text-xs text-gray-500 dark:text-gray-400">برای این سرویس فرم اختصاصی تعریف نشده است.</div>
                </template>
            </div>

            {{-- STEP 6: Client --}}
            <div x-show="step===clientStep" class="space-y-3">
                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">انتخاب مشتری و وضعیت</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <input type="text"
                               class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                               placeholder="جستجو مشتری (نام/موبایل/ایمیل)" x-model="clientSearch"
                               @input.debounce.300ms="fetchClients()">
                        <select
                            class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 mt-2 text-sm dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                            x-model="clientId">
                            <option value="">انتخاب کنید</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="`${c.full_name} (${c.phone || '-'})`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-200">وضعیت نوبت</label>
                        <select name="status"
                                class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                required>
                            @php
                                $statusOptions = [
                                    \Modules\Booking\Entities\Appointment::STATUS_DRAFT => 'پیش‌نویس',
                                    \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT => 'در انتظار پرداخت',
                                    \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED => 'تایید شده',
                                    \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN => 'لغو شده (ادمین)',
                                    \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT => 'لغو شده (مشتری)',
                                    \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW => 'عدم حضور',
                                    \Modules\Booking\Entities\Appointment::STATUS_DONE => 'انجام شده',
                                    \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED => 'جابجا شده',
                                ];
                            @endphp
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $appointment->status)===$value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if($settings->allow_appointment_entry_exit_times)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1 text-gray-700 dark:text-gray-200">زمان ورود</label>
                            <input type="text" name="entry_time_local" value="{{ $entryTime }}" data-jdp-only-time
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            @error('entry_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1 text-gray-700 dark:text-gray-200">زمان خروج</label>
                            <input type="text" name="exit_time_local" value="{{ $exitTime }}" data-jdp-only-time
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            @error('exit_time_local')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="pt-2">
                    <label class="block text-sm mb-1 text-gray-700 dark:text-gray-200">یادداشت</label>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        ذخیره نهایی فقط زمانی ممکن است که سرویس/ارائه‌دهنده/روز/اسلات/مشتری کامل باشند.
                    </div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">
                        ذخیره تغییرات
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
                flow: @json($flowValue ?? 'PROVIDER_FIRST'),
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
                initialFormValues: options.initial?.appointment_form_values || {},
                presetDateLocal: options.initial?.date_local || '',
                presetStartAtUtc: options.initial?.start_at_utc || '',
                presetEndAtUtc: options.initial?.end_at_utc || '',
                presetManualStartTime: options.initial?.manual_start_time || '',
                presetManualEndTime: options.initial?.manual_end_time || '',
                clients: [],
                clientSearch: '',
                clientId: '',
                isSubmitting: false,

                weekDays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                hasAppointmentForm: false,

                get totalSteps() {
                    return this.hasAppointmentForm ? 6 : 5;
                },

                get clientStep() {
                    return this.hasAppointmentForm ? 6 : 5;
                },

                async init() {
                    const now = new Date();
                    this.calendarYear = now.getFullYear();
                    this.calendarMonth = now.getMonth() + 1;

                    const preset = options.initial || {};
                    if (preset.date_local) {
                        const parts = preset.date_local.split('-');
                        if (parts.length === 3) {
                            this.calendarYear = Number(parts[0]);
                            this.calendarMonth = Number(parts[1]);
                        }
                    }

                    this.providerId = preset.provider_id ? String(preset.provider_id) : '';
                    this.serviceId = preset.service_id ? String(preset.service_id) : '';
                    this.clientId = preset.client_id ? String(preset.client_id) : '';
                    this.dateLocal = this.presetDateLocal;
                    this.manualStartTime = this.presetManualStartTime;
                    this.manualEndTime = this.presetManualEndTime;
                    this.selectedSlotKey = this.presetStartAtUtc;
                    if (this.$refs.startUtcInput && this.presetStartAtUtc) this.$refs.startUtcInput.value = this.presetStartAtUtc;
                    if (this.$refs.endUtcInput && this.presetEndAtUtc) this.$refs.endUtcInput.value = this.presetEndAtUtc;

                    if (this.flow === 'PROVIDER_FIRST') {
                        await this.fetchProviders();
                        if (this.providerId) {
                            const provider = this.providers.find(p => String(p.id) === String(this.providerId)) || {id: this.providerId, name: 'ارائه‌دهنده'};
                            await this.selectProvider(provider, false);
                            if (this.serviceId) {
                                const service = this.services.find(s => String(s.id) === String(this.serviceId)) || {id: this.serviceId, name: 'سرویس'};
                                await this.selectService(service, false);
                            }
                        }
                    } else {
                        await this.fetchAllActiveServices();
                        if (this.serviceId) {
                            const service = this.services.find(s => String(s.id) === String(this.serviceId)) || {id: this.serviceId, name: 'سرویس'};
                            await this.selectService(service, false);
                        }
                        if (this.providerId) {
                            if (!this.providers.length) {
                                await this.fetchProviders();
                            }
                        }
                    }

                    await this.applyPresetSchedule();
                    this.fetchClients();
                },

                async applyPresetSchedule() {
                    if (this.serviceId && this.providerId) {
                        if (this.presetDateLocal) {
                            this.dateLocal = this.presetDateLocal;
                        }
                        if (this.presetManualStartTime) {
                            this.manualStartTime = this.presetManualStartTime;
                        }
                        if (this.presetManualEndTime) {
                            this.manualEndTime = this.presetManualEndTime;
                        }
                        await this.loadCalendar();
                        if (this.dateLocal && !this.isCustomScheduleEnabled()) {
                            await this.fetchSlots();
                            if (this.presetStartAtUtc) {
                                this.selectedSlotKey = this.presetStartAtUtc;
                                if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = this.presetStartAtUtc;
                                if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = this.presetEndAtUtc;
                                const found = this.slots.find(s => s.start_at_utc === this.presetStartAtUtc);
                                if (found) {
                                    this.selectSlot(found);
                                }
                            }
                        }
                    }
                },

                // ---------------- providers/services/categories ----------------

                get groupedServices() {
                    const items = Array.isArray(this.services) ? this.services : [];

                    const getCatName = (s) => {
                        if (!s) return '';
                        if (typeof s.category_name === 'string' && s.category_name.trim()) return s.category_name
                            .trim();
                        if (typeof s.categoryTitle === 'string' && s.categoryTitle.trim()) return s.categoryTitle
                            .trim();
                        if (typeof s.category === 'string' && s.category.trim()) return s.category.trim();
                        if (s.category && typeof s.category.name === 'string' && s.category.name.trim()) return s
                            .category.name.trim();
                        if (s.category_obj && typeof s.category_obj.name === 'string' && s.category_obj.name.trim())
                            return s.category_obj.name.trim();
                        const cid = s.category_id ?? s.categoryId ?? null;
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
                        q: this.providerSearch || '',
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
                    const params = new URLSearchParams({
                        provider_id: this.providerId || ''
                    });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.categories') }}?` + params
                        .toString());
                    const json = await res.json();
                    this.categories = json.data || [];
                },

                async fetchServicesForProvider() {
                    this.serviceLoading = true;
                    try {
                        const params = new URLSearchParams({
                            provider_id: this.providerId || '',
                            category_id: this.categoryId || '',
                            q: this.serviceSearch || ''
                        });
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
                    try {
                        const params = new URLSearchParams({
                            q: this.serviceSearch || '',
                            category_id: this.categoryId || ''
                        });
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
                    if (autoGo && this.step === 1 && this.flow === 'PROVIDER_FIRST') {
                        await this.next();
                    }
                    if (autoGo && this.step === 2 && this.flow === 'SERVICE_FIRST') {
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
                    if (autoGo && this.step === 1 && this.flow === 'SERVICE_FIRST') {
                        await this.next();
                    }
                    if (autoGo && this.step === 2 && this.flow === 'PROVIDER_FIRST') {
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

                    if (this.initialFormValues && typeof this.initialFormValues === 'object') {
                        for (const field of schema.fields) {
                            if (Object.prototype.hasOwnProperty.call(this.initialFormValues, field.name)) {
                                this.appointmentFormValues[field.name] = this.initialFormValues[field.name];
                            }
                        }
                    }
                },

                // ---------------- calendar ----------------

                get monthLabel() {
                    const d = new Date(this.calendarYear, this.calendarMonth - 1, 1);
                    return d.toLocaleDateString('fa-IR-u-ca-persian', {
                        year: 'numeric',
                        month: 'long'
                    });
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

                dayBtnClass(day) {
                    if (!day) return '';
                    if (this.dateLocal === day.local_date) {
                        return 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-200';
                    }
                    if (day.is_closed) {
                        return 'border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/60 text-gray-400';
                    }
                    if (!day.has_available_slots) {
                        return 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500';
                    }
                    return 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/70';
                },

                toPersianDayNumber(localDate) {
                    const d = new Date(localDate + 'T00:00:00');
                    return d.toLocaleDateString('fa-IR-u-ca-persian', {
                        day: 'numeric'
                    });
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

                async fetchSlots() {
                    if (!this.serviceId || !this.providerId || !this.dateLocal) return;
                    this.slotsLoading = true;
                    this.slotsError = '';
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
                        const json = await res.json();
                        this.slots = json.data || [];
                    } catch (e) {
                        this.slotsError = 'خطا در دریافت اسلات‌ها';
                        this.slots = [];
                    } finally {
                        this.slotsLoading = false;
                    }
                },

                formatTime(timeStr) {
                    if (!timeStr) return '';
                    const parts = String(timeStr).split(' ');
                    return parts[1] || parts[0] || '';
                },

                selectDay(day) {
                    if (!day) return;
                    this.dateLocal = day.local_date;
                    this.manualStartTime = '';
                    this.manualEndTime = '';
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                    this.fetchSlots();
                    if (this.step === 3) this.step = 4;
                },

                selectSlot(slot) {
                    this.selectedSlotKey = slot.start_at_utc;
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = slot.start_at_utc;
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = slot.end_at_utc;
                },

                clearSlotSelection() {
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                isCustomScheduleEnabled() {
                    return Boolean(this.selectedService && this.selectedService.custom_schedule_enabled);
                },

                async fetchClients() {
                    const params = new URLSearchParams({
                        q: this.clientSearch || ''
                    });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.clients') }}?` + params.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    this.clients = json.data || [];
                },

                next() {
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

                handleSubmit() {
                    if (this.isSubmitting) return;
                    if (!this.serviceId || !this.providerId) return alert('سرویس/ارائه‌دهنده ناقص است.');
                    if (!this.dateLocal) return alert('روز انتخاب نشده است.');
                    if (this.isCustomScheduleEnabled()) {
                        if (!this.manualStartTime || !this.manualEndTime) {
                            return alert('لطفاً ساعت شروع و پایان را وارد کنید.');
                        }
                    } else if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) {
                        return alert('لطفاً یک اسلات انتخاب کنید.');
                    }
                    if (!this.clientId) return alert('لطفاً مشتری را انتخاب کنید.');

                    this.prepareAppointmentFormJson();

                    this.isSubmitting = true;
                    this.$refs.form.submit();
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
