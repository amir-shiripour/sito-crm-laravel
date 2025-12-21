@extends('layouts.user')
@php
    $fixedProviderPayload = (isset($fixedProvider) && $fixedProvider)
      ? ['id' => $fixedProvider->id, 'name' => $fixedProvider->name]
      : null;
@endphp

@section('content')
    <div class="space-y-6"
         data-fixed-provider='@json($fixedProviderPayload)'
         x-data="operatorWizard({ fixedProvider: null })"
         x-init="
    const raw = $el.dataset.fixedProvider;
    try { fixedProvider = raw ? JSON.parse(raw) : null } catch(e) { fixedProvider = null }
    init();
  ">

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ثبت نوبت (مرحله‌ای)</h1>
            <a class="text-blue-600 dark:text-blue-400 hover:underline" href="{{ route('user.booking.appointments.index') }}">بازگشت</a>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded text-green-700 dark:text-green-200">{{ session('success') }}</div>
        @endif

        <form method="POST"
              action="{{ route('user.booking.appointments.store') }}"
              class="bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 p-4 space-y-4"
              x-ref="form"
              @submit.prevent="handleSubmit">
            @csrf

            <input type="hidden" name="service_id" x-model="serviceId">
            <input type="hidden" name="provider_user_id" x-model="providerId">
            <input type="hidden" name="client_id" x-model="clientId">
            <input type="hidden" name="start_at_utc" x-ref="startUtcInput">
            <input type="hidden" name="end_at_utc" x-ref="endUtcInput">
            <input type="hidden" name="appointment_form_response_json" x-ref="formJsonInput">

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    مرحله:
                    <span class="font-semibold" x-text="step"></span>
                    از
                    <span class="font-semibold">6</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            class="px-3 py-1 rounded border border-gray-300 dark:border-gray-700 dark:text-gray-100 disabled:opacity-50"
                            @click="prev()"
                            :disabled="step===1">قبلی</button>

                    {{-- در مراحل ۱ و ۲ انتخاب به صورت کارت انجام می‌شود و بعد از انتخاب اتومات مرحله بعدی می‌رویم --}}
                    <button type="button"
                            class="px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white"
                            @click="next()"
                            x-show="step<6 && step>2">بعدی</button>
                </div>
            </div>

            {{-- STEP 1 --}}
            <div x-show="step===1" class="space-y-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    @php $flowValue = $flow ?? 'PROVIDER_FIRST'; @endphp
                    حالت انتخاب: <span class="font-semibold">{{ $flowValue === 'SERVICE_FIRST' ? 'اول سرویس' : 'اول ارائه‌دهنده' }}</span>
                </div>

                <template x-if="flow==='PROVIDER_FIRST' && !fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">انتخاب ارائه‌دهنده</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                   placeholder="جستجو ارائه‌دهنده..."
                                   x-model="providerSearch"
                                   @input.debounce.300ms="fetchProviders()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="providerLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت ارائه‌دهنده‌ها...</div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="p in providers" :key="p.id">
                                <button type="button"
                                        class="text-right border rounded-xl p-3 transition"
                                        :class="String(providerId)===String(p.id)
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                        @click="selectProvider(p, true)">
                                    <div class="font-semibold text-sm" x-text="p.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="p.subtitle" x-text="p.subtitle"></div>
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

                <template x-if="flow==='PROVIDER_FIRST' && fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">ارائه‌دهنده انتخاب‌شده</label>
                        <div class="border rounded-xl p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200">
                            <div class="font-semibold text-sm" x-text="fixedProvider.name"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">به صورت خودکار انتخاب شد.</div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            ارائه‌دهنده قابل تغییر نیست.
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST'">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">انتخاب سرویس</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                   placeholder="جستجو سرویس..."
                                   x-model="serviceSearch"
                                   @input.debounce.300ms="fetchServicesForServiceFirst()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="serviceLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت سرویس‌ها...</div>
                        </template>

                        <div class="space-y-4" x-show="!serviceLoading">
                            <template x-for="grp in groupedServices" :key="grp.key">
                                <div class="space-y-2">
                                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="grp.title"></div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <template x-for="s in grp.items" :key="s.id">
                                            <button type="button"
                                                    class="text-right border rounded-xl p-3 transition"
                                                    :class="String(serviceId)===String(s.id)
                                                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                                    @click="selectService(s, true)">
                                                <div class="font-semibold text-sm" x-text="s.name"></div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="s.duration_min || s.price"
                                                     x-text="[s.duration_min ? (s.duration_min + ' دقیقه') : null, s.price ? (s.price + ' تومان') : null].filter(Boolean).join(' • ')"></div>
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

            {{-- STEP 2 --}}
            <div x-show="step===2" class="space-y-3">
                <template x-if="flow==='PROVIDER_FIRST'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm mb-1 dark:text-gray-200">دسته‌بندی</label>
                            <select class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100" x-model="categoryId" @change="fetchServicesForProvider()">
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
                                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                       placeholder="جستجو سرویس..."
                                       x-model="serviceSearch"
                                       @input.debounce.300ms="fetchServicesForProvider()">
                                <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                            </div>

                            <template x-if="serviceLoading">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">در حال دریافت سرویس‌ها...</div>
                            </template>

                            <div class="space-y-4 mt-2" x-show="!serviceLoading">
                                <template x-for="grp in groupedServices" :key="grp.key">
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="grp.title"></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                            <template x-for="s in grp.items" :key="s.id">
                                                <button type="button"
                                                        class="text-right border rounded-xl p-3 transition"
                                                        :class="String(serviceId)===String(s.id)
                                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                                        @click="selectService(s, true)">
                                                    <div class="font-semibold text-sm" x-text="s.name"></div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="s.duration_min || s.price"
                                                         x-text="[s.duration_min ? (s.duration_min + ' دقیقه') : null, s.price ? (s.price + ' تومان') : null].filter(Boolean).join(' • ')"></div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="!serviceLoading && (!services || services.length===0)">
                                <div class="text-xs text-amber-600 mt-2">موردی یافت نشد.</div>
                            </template>

                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">فقط سرویس‌هایی که برای این ارائه‌دهنده فعال هستند نمایش داده می‌شود. بعد از انتخاب، به صورت خودکار به مرحله بعد می‌روید.</div>
                        </div>
                    </div>
                </template>

                <template x-if="flow==='SERVICE_FIRST' && !fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">انتخاب ارائه‌دهنده (برای سرویس انتخابی)</label>
                        <div class="relative">
                            <input type="text"
                                   class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 pr-10 text-sm dark:text-gray-100 placeholder:text-gray-400"
                                   placeholder="جستجو ارائه‌دهنده..."
                                   x-model="providerSearch"
                                   @input.debounce.300ms="fetchProviders()">
                            <span class="absolute right-3 top-2.5 text-gray-400">🔎</span>
                        </div>

                        <template x-if="providerLoading">
                            <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت ارائه‌دهنده‌ها...</div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="p in providers" :key="p.id">
                                <button type="button"
                                        class="text-right border rounded-xl p-3 transition"
                                        :class="String(providerId)===String(p.id)
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/70'"
                                        @click="selectProvider(p, true)">
                                    <div class="font-semibold text-sm" x-text="p.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="p.subtitle" x-text="p.subtitle"></div>
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

                <template x-if="flow==='SERVICE_FIRST' && fixedProvider">
                    <div class="space-y-2">
                        <label class="block text-sm mb-1 dark:text-gray-200">ارائه‌دهنده انتخاب‌شده</label>
                        <div class="border rounded-xl p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200">
                            <div class="font-semibold text-sm" x-text="fixedProvider.name"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">به صورت خودکار انتخاب شد.</div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            ارائه‌دهنده قابل تغییر نیست.
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
                                <button type="button"
                                        class="w-full h-[52px] border rounded-lg p-2 text-center"
                                        :class="dayBtnClass(cell.day)"
                                        @click="selectDay(cell.day)"
                                        :disabled="cell.day.is_closed || !cell.day.has_available_slots">
                                    <div class="font-semibold" x-text="toPersianDayNumber(cell.day.local_date)"></div>
                                    <div class="text-[10px] mt-1" x-show="cell.day.is_closed">تعطیل</div>
                                    <div class="text-[10px] mt-1" x-show="!cell.day.is_closed && !cell.day.has_available_slots">پر</div>
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
                            @click="fetchSlots()">بروزرسانی</button>
                </div>

                <template x-if="slotsLoading">
                    <div class="text-xs text-gray-500 dark:text-gray-400">در حال دریافت اسلات‌ها...</div>
                </template>

                <template x-if="slotsError">
                    <div class="text-xs text-red-600" x-text="slotsError"></div>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2" x-show="slots.length && !slotsLoading">
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

                <template x-if="!slotsLoading && dateLocal && slots.length === 0 && !slotsError">
                    <div class="text-xs text-amber-600 dark:text-amber-300">اسلات خالی برای این روز یافت نشد.</div>
                </template>
            </div>

            {{-- STEP 5: Appointment Form --}}
            <div x-show="step===5" class="space-y-3">
                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">فرم اطلاعات نوبت</div>

                <template x-if="selectedService && selectedService.appointment_form_id">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            سرویس فرم دارد. (فعلاً پاسخ را به صورت JSON ذخیره می‌کنیم؛ اگر کامپوننت فرم‌ساز دارید، همینجا جایگزین می‌شود.)
                        </div>
                        <textarea class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-xs dark:text-gray-100 placeholder:text-gray-400" rows="6"
                                  placeholder='مثلاً: {"field1":"value"}'
                                  x-model="appointmentFormJson"></textarea>
                    </div>
                </template>

                <template x-if="!selectedService || !selectedService.appointment_form_id">
                    <div class="text-xs text-gray-500 dark:text-gray-400">برای این سرویس فرم اختصاصی تعریف نشده است.</div>
                </template>
            </div>

            {{-- STEP 6: Client --}}
            <div x-show="step===6" class="space-y-3">
                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">انتخاب مشتری</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <input type="text" class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400" placeholder="جستجو مشتری (نام/موبایل/ایمیل)"
                               x-model="clientSearch" @input.debounce.300ms="fetchClients()">
                        <select class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 mt-2 text-sm dark:text-gray-100" x-model="clientId">
                            <option value="">انتخاب کنید</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="`${c.full_name} (${c.phone || '-'})`"></option>
                            </template>
                        </select>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                        <div class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">ایجاد سریع مشتری</div>
                        <div class="space-y-2">
                            <input class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400" placeholder="نام کامل" x-model="quickClient.full_name">
                            <input class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400" placeholder="موبایل" x-model="quickClient.phone">
                            <input class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400" placeholder="ایمیل" x-model="quickClient.email">
                            <button type="button" class="px-3 py-1 rounded bg-emerald-600 text-white text-sm" @click="quickCreateClient()">
                                ثبت مشتری
                            </button>
                            <div class="text-xs text-red-600" x-show="quickClientError" x-text="quickClientError"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-sm mb-1 text-gray-700 dark:text-gray-200">یادداشت</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg p-2 text-sm dark:text-gray-100 placeholder:text-gray-400"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        ثبت نهایی فقط وقتی ممکن است که سرویس/ارائه‌دهنده/روز/اسلات/مشتری کامل باشند.
                    </div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">
                        ثبت نوبت
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function operatorWizard(options = {}) {
            return {
                flow: @json($flow ?? 'PROVIDER_FIRST'),
                step: 1,

                defaultSlotCapacity: @json($settings->default_capacity_per_slot ?? 1),
                fixedProvider: options.fixedProvider || null,
                providerId: '',
                serviceId: '',
                categoryId: '',
                dateLocal: '',

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

                appointmentFormJson: '',
                clients: [],
                clientSearch: '',
                clientId: '',

                quickClient: { full_name: '', phone: '', email: '' },
                quickClientError: '',
                weekDays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],

                init() {
                    const now = new Date();
                    this.calendarYear = now.getFullYear();
                    this.calendarMonth = now.getMonth() + 1;

                    if (this.fixedProvider) {
                        this.providerId = String(this.fixedProvider.id || '');
                        this.providers = [this.fixedProvider];
                        this.onProviderSelected().then(() => {
                            if (this.flow === 'PROVIDER_FIRST') {
                                this.step = 2;
                            }
                        });
                    } else if (this.flow === 'PROVIDER_FIRST') {
                        // شروع
                        this.fetchProviders();
                    } else {
                        // service first: اول لیست سرویس‌ها از روی provider_id نداریم؛ پس از API services با provider لازم است.
                        // در این حالت سرویس‌ها را با یک provider_id نمی‌گیریم؛
                        // برای سادگی، اینجا لیست سرویس‌ها را از providers فعال در سیستم نمی‌گیریم و فقط بعد از انتخاب سرویس،
                        // providers را fetch می‌کنیم.
                        // اگر خواستی، endpoint جدا برای "all active services" هم اضافه می‌کنیم.
                        this.fetchAllActiveServices();
                    }

                    this.fetchClients();
                },

                // ---------------- providers/services/categories ----------------

                get groupedServices() {
                    // سرویس‌ها را بر اساس دسته‌بندی گروه‌بندی کن.
                    // اگر دسته‌ای نبود: «بدون دسته»
                    const items = Array.isArray(this.services) ? this.services : [];

                    const getCatName = (s) => {
                        if (!s) return '';
                        // چند حالت رایج برگشتی API
                        if (typeof s.category_name === 'string' && s.category_name.trim()) return s.category_name.trim();
                        if (typeof s.categoryTitle === 'string' && s.categoryTitle.trim()) return s.categoryTitle.trim();
                        if (typeof s.category === 'string' && s.category.trim()) return s.category.trim();
                        if (s.category && typeof s.category.name === 'string' && s.category.name.trim()) return s.category.name.trim();
                        if (s.category_obj && typeof s.category_obj.name === 'string' && s.category_obj.name.trim()) return s.category_obj.name.trim();
                        // اگر فقط category_id داریم و لیست دسته‌ها موجود است
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
                        if (!groups.has(key)) groups.set(key, { key, title: cat || 'بدون دسته', items: [] });
                        groups.get(key).items.push(s);
                    }

                    // مرتب‌سازی: دسته‌ها الفبایی، «بدون دسته» آخر.
                    const arr = Array.from(groups.values());
                    arr.sort((a, b) => {
                        if (a.key === 'cat:__none__') return 1;
                        if (b.key === 'cat:__none__') return -1;
                        return (a.title || '').localeCompare(b.title || '', 'fa');
                    });
                    // مرتب‌سازی داخل هر گروه
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
                    if (slot.capacity_per_slot !== null && slot.capacity_per_slot !== undefined && Number(slot.capacity_per_slot) > 0) {
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
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.providers') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        this.providers = json.data || [];
                    } finally {
                        this.providerLoading = false;
                    }
                },

                async fetchCategories() {
                    if (!this.providerId) return;
                    const params = new URLSearchParams({ provider_id: this.providerId });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.categories') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
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
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.services') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        this.services = json.data || [];
                    } finally {
                        this.serviceLoading = false;
                    }
                },

                async fetchAllActiveServices() {
                    this.serviceLoading = true;
                    const params = new URLSearchParams({ q: this.serviceSearch || '' });

                    try {
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.all-services') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });

                        const json = await res.json();
                        this.services = json.data || [];
                    } finally {
                        this.serviceLoading = false;
                    }
                },

                async fetchServicesForServiceFirst() {
                    // همان fetchAllActiveServices (محدودیت بالا)
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
                    this.categoryId = '';

                    // نکته مهم:
                    // در SERVICE_FIRST نباید serviceId را reset کنیم
                    if (this.flow === 'PROVIDER_FIRST') {
                        this.serviceId = '';
                        this.services = [];
                        this.categories = [];
                        this.resetCalendarAndSlots();

                        await this.fetchCategories();
                        await this.fetchServicesForProvider();
                        return;
                    }

                    // SERVICE_FIRST
                    this.resetCalendarAndSlots();
                },

                async selectService(s, autoGo = false) {
                    this.serviceId = String(s?.id ?? '');
                    await this.onServiceSelected();
                    if (autoGo && this.step === 1 && this.flow === 'SERVICE_FIRST') {
                        if (this.fixedProvider && this.fixedProvider.id && !this.providerId) {
                            this.providerId = String(this.fixedProvider.id);
                        }
                        await this.next();
                        if (this.fixedProvider && this.step === 2) {
                            await this.next();
                        }
                    }
                    if (autoGo && this.step === 2 && this.flow === 'PROVIDER_FIRST') {
                        await this.next();
                    }
                },

                async onServiceSelected() {
                    this.selectedService = this.services.find(s => String(s.id) === String(this.serviceId)) || null;
                    this.resetCalendarAndSlots();

                    if (this.flow === 'SERVICE_FIRST') {
                        await this.fetchProviders(); // حالا providers برای این service
                    }
                },

                resetCalendarAndSlots() {
                    this.dateLocal = '';
                    this.calendarDays = [];
                    this.slots = [];
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                // ---------------- calendar ----------------

                get monthLabel() {
                    const d = new Date(this.calendarYear, this.calendarMonth - 1, 1);
                    return d.toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long' });
                },

                get calendarCells() {
                    // خروجی: placeholder + روزهای ماه
                    const days = this.calendarDays || [];
                    if (!days.length) return [];

                    const first = days[0].local_date; // YYYY-MM-DD
                    const firstDate = new Date(first + 'T00:00:00');

                    // JS getDay(): 0=Sun..6=Sat -> تبدیل به 0=Sat..6=Fri
                    const persianWeekdayIndex = (firstDate.getDay() + 1) % 7;

                    const cells = [];
                    for (let i = 0; i < persianWeekdayIndex; i++) {
                        cells.push({ key: `ph-${i}`, is_placeholder: true });
                    }

                    for (const d of days) {
                        cells.push({ key: d.local_date, is_placeholder: false, day: d });
                    }

                    // کامل شدن ردیف آخر
                    while (cells.length % 7 !== 0) {
                        cells.push({ key: `ph-end-${cells.length}`, is_placeholder: true });
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
                        const res = await fetch(`{{ route('user.booking.appointments.wizard.calendar') }}?` + params.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        this.calendarDays = json.data || [];
                    } finally {
                        this.calendarLoading = false;
                    }
                },

                prevMonth() {
                    this.calendarMonth--;
                    if (this.calendarMonth < 1) { this.calendarMonth = 12; this.calendarYear--; }
                    this.loadCalendar();
                },

                nextMonth() {
                    this.calendarMonth++;
                    if (this.calendarMonth > 12) { this.calendarMonth = 1; this.calendarYear++; }
                    this.loadCalendar();
                },

                selectDay(day) {
                    if (day.is_closed || !day.has_available_slots) return;
                    this.dateLocal = day.local_date;
                    this.slots = [];
                    this.selectedSlotKey = '';
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = '';
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = '';
                },

                dayBtnClass(d) {
                    const isSelected = this.dateLocal && this.dateLocal === d.local_date;
                    if (!d.is_closed && !d.has_available_slots) return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    if (d.is_closed) return 'bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700';
                    if (isSelected) return 'bg-indigo-50 text-indigo-700 border-indigo-500 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-500';
                    if (d.capacity_per_day !== null && d.remaining_day_capacity === 0) return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-800';
                    return 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/70';
                },

                toPersianDayNumber(localDate) {
                    const dd = new Date(localDate + 'T00:00:00');
                    return dd.toLocaleDateString('fa-IR-u-ca-persian', { day: 'numeric' });
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
                            headers: { 'Accept': 'application/json' }
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

                selectSlot(slot) {
                    this.selectedSlotKey = slot.start_at_utc;
                    if (this.$refs.startUtcInput) this.$refs.startUtcInput.value = slot.start_at_utc;
                    if (this.$refs.endUtcInput) this.$refs.endUtcInput.value = slot.end_at_utc;
                },

                formatTime(isoString) {
                    const d = new Date(isoString);
                    return d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
                },

                // ---------------- clients ----------------

                async fetchClients() {
                    const params = new URLSearchParams({ q: this.clientSearch || '' });
                    const res = await fetch(`{{ route('user.booking.appointments.wizard.clients') }}?` + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    this.clients = json.data || [];
                },

                async quickCreateClient() {
                    this.quickClientError = '';
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        const res = await fetch(`{{ route('user.clients.quick-store') }}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || ''
                            },
                            body: JSON.stringify(this.quickClient)
                        });

                        if (!res.ok) {
                            const j = await res.json().catch(() => null);
                            throw new Error(j?.message || 'خطا در ایجاد مشتری');
                        }

                        const j = await res.json();
                        const c = j.client;
                        this.clients.unshift(c);
                        this.clientId = String(c.id);

                        this.quickClient = { full_name: '', phone: '', email: '' };
                    } catch (e) {
                        this.quickClientError = e.message || 'خطای ناشناخته';
                    }
                },

                // ---------------- wizard navigation ----------------

                async next() {
                    // STEP 1 -> 2
                    if (this.step === 1) {
                        if (this.flow === 'PROVIDER_FIRST' && !this.providerId) return alert('لطفاً ارائه‌دهنده را انتخاب کنید.');
                        if (this.flow === 'SERVICE_FIRST' && !this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                    }

                    // STEP 2 -> 3 (قبل از ورود به تقویم، خود تقویم را لود کن)
                    if (this.step === 2) {
                        if (!this.serviceId) return alert('لطفاً سرویس را انتخاب کنید.');
                        if (!this.providerId) return alert('لطفاً ارائه‌دهنده را انتخاب کنید.');

                        await this.loadCalendar();

                        // انتخاب خودکار امروز (اگر موجود و قابل رزرو باشد)
                        if (!this.dateLocal) {
                            const today = new Date();
                            const iso = today.toISOString().slice(0, 10);

                            const found = this.calendarDays.find(x =>
                                x.local_date === iso &&
                                !x.is_closed &&
                                x.has_available_slots
                            );

                            if (found) this.selectDay(found);
                        }
                    }

                    // STEP 3 -> 4 (روز باید انتخاب شود، بعد slots را بگیر)
                    if (this.step === 3) {
                        if (!this.dateLocal) return alert('لطفاً یک روز قابل رزرو انتخاب کنید.');
                        await this.fetchSlots();
                    }

                    // STEP 4 -> 5 (اسلات باید انتخاب شود)
                    if (this.step === 4) {
                        if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) {
                            return alert('لطفاً یک اسلات زمانی را انتخاب کنید.');
                        }
                    }

                    // STEP 5 -> 6 (فرم json را آماده کن)
                    if (this.step === 5) {
                        if (this.selectedService && this.selectedService.appointment_form_id) {
                            this.$refs.formJsonInput.value = this.appointmentFormJson || '';
                        } else {
                            this.$refs.formJsonInput.value = '';
                        }
                    }

                    this.step++;
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
                    if (!this.serviceId || !this.providerId) return alert('سرویس/ارائه‌دهنده ناقص است.');
                    if (!this.dateLocal) return alert('روز انتخاب نشده است.');
                    if (!this.$refs.startUtcInput.value || !this.$refs.endUtcInput.value) return alert('لطفاً یک اسلات انتخاب کنید.');
                    if (!this.clientId) return alert('لطفاً مشتری را انتخاب کنید.');

                    // فرم JSON
                    if (this.selectedService && this.selectedService.appointment_form_id) {
                        this.$refs.formJsonInput.value = this.appointmentFormJson || '';
                    }

                    this.$refs.form.submit();
                }
            }
        }
    </script>
@endsection
