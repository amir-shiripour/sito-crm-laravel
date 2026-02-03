@extends('layouts.user')

@section('content')
    <div class="space-y-5" x-data="statementFilter()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">صورت وضعیت</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">گزارش نوبت‌ها به تفکیک پزشک و سایر نقش‌ها</p>
            </div>

            @if($appointments !== null && !$appointments->isEmpty())
                <button type="button" @click="openPrintModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    چاپ PDF
                </button>
            @endif
        </div>

        @includeIf('partials.jalali-date-picker')

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('user.booking.statement.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- Provider Search with Alpine.js --}}
                <div class="relative" @click.outside="closeResults('provider')">
                    <label for="provider_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">پزشک (ارائه‌دهنده)</label>

                    <div class="relative">
                        <input type="text"
                               id="provider_search"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                               placeholder="جستجو نام یا موبایل..."
                               x-model="searches['provider']"
                               @input.debounce.300ms="fetchProviders()"
                               @focus="showResults['provider'] = true"
                               autocomplete="off">

                        <input type="hidden" name="provider_id" :value="selectedIds['provider']">

                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <button type="button"
                                x-show="selectedIds['provider']"
                                @click="clearSelection('provider')"
                                class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown Results --}}
                    <div x-show="showResults['provider'] && (users['provider'].length > 0 || loading['provider'])"
                         x-transition.opacity
                         class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                        <template x-if="loading['provider']">
                            <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                در حال جستجو...
                            </div>
                        </template>

                        <template x-for="provider in users['provider']" :key="provider.id">
                            <div @click="selectUser('provider', provider)"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium block truncate" x-text="provider.name"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="provider.mobile"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ شروع</label>
                    <input type="text" name="start_date" id="start_date" data-jdp class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $startDateLocal }}" autocomplete="off">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاریخ پایان</label>
                    <input type="text" name="end_date" id="end_date" data-jdp class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $endDateLocal }}" autocomplete="off">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                        نمایش گزارش
                    </button>
                </div>
            </form>
        </div>

        @if($appointments !== null)
            @if($appointments->isEmpty())
                <div class="flex items-center gap-3 rounded-2xl border border-blue-200 dark:border-blue-700/70 bg-blue-50 dark:bg-blue-900/40 text-blue-800 dark:text-blue-100 px-4 py-3 shadow-sm">
                    <span class="text-xl">ℹ️</span>
                    <span class="text-sm">هیچ نوبتی در این بازه زمانی یافت نشد.</span>
                </div>
            @else
                @if($firstAppointmentTime && $lastAppointmentTime)
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 rounded-2xl p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-indigo-900 dark:text-indigo-100">بازه زمانی نوبت‌ها</div>
                                <div class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                                    از ساعت <span class="font-bold font-mono">{{ $firstAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }}</span>
                                    تا ساعت <span class="font-bold font-mono">{{ $lastAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @foreach($appointments as $categoryName => $categoryAppointments)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-4">
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $categoryName }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full whitespace-nowrap text-sm text-right">
                                <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ساعت شروع</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام بیمار</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شماره پرونده</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نوع درمان</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شرح درمان</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">وضعیت</th>
                                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($categoryAppointments as $appointment)
                                        @php
                                            $statusMap = [
                                                'CONFIRMED' => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
                                                'PENDING_PAYMENT' => ['label' => 'در انتظار پرداخت', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
                                                'CANCELED_BY_CLIENT' => ['label' => 'لغو توسط بیمار', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                                'CANCELED_BY_ADMIN' => ['label' => 'لغو توسط ادمین', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
                                                'NO_SHOW' => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
                                                'DONE' => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
                                            ];
                                            $statusMeta = $statusMap[$appointment->status] ?? ['label' => $appointment->status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];
                                        @endphp
                                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">
                                                {{ $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if($appointment->client)
                                                    <a href="{{ route('clients.show', $appointment->client->id) }}" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                        {{ $appointment->client->full_name }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-200">{{ $appointment->client?->case_number ?? '-' }}</td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if($appointment->unit_count)
                                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">({{ $appointment->unit_count }} واحد)</span>
                                                @endif
                                                {{ $appointment->service?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                @if(!empty($appointment->processed_form_response))
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-3 items-center">
                                                        @foreach($appointment->processed_form_response as $item)
                                                            @if(!empty($item['value']))
                                                                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">
                                                                    @if(!empty($item['icon']))
                                                                        <span class="text-gray-500 dark:text-gray-400" style="width: 24px; height: 24px;" title="{{ $item['label'] }}">{!! $item['icon'] !!}</span>
                                                                    @else
                                                                        <span class="font-medium">{{ $item['label'] }}:</span>
                                                                    @endif
                                                                    <span>{{ is_array($item['value']) ? implode(' / ', $item['value']) : str_replace(',', ' / ', $item['value']) }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                                    {{ $statusMeta['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <div class="flex items-center gap-2 justify-end">
                                                    <a href="{{ route('user.booking.appointments.edit', $appointment->id) }}" class="px-3 py-1.5 text-xs rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-200 dark:hover:bg-indigo-500/30 transition" title="ویرایش">
                                                        ویرایش
                                                    </a>

                                                    <form action="{{ route('user.booking.appointments.destroy', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این نوبت اطمینان دارید؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-500/20 dark:text-rose-200 dark:hover:bg-rose-500/30 transition" title="حذف">
                                                            حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

        {{-- Print Modal (Manual Implementation to avoid Livewire dependency) --}}
        <div x-show="isPrintModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isPrintModalOpen = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-right w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">دریافت خروجی PDF</h3>
                                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    <p class="mb-4">
                                        لطفاً برای نقش‌های زیر کاربر مورد نظر را انتخاب کنید تا در خروجی PDF نمایش داده شود.
                                    </p>

                                    <form id="printForm" action="{{ route('user.booking.statement.print') }}" method="GET" target="_blank">
                                        <input type="hidden" name="provider_id" :value="selectedIds['provider']">
                                        <input type="hidden" name="start_date" value="{{ $startDateLocal }}">
                                        <input type="hidden" name="end_date" value="{{ $endDateLocal }}">

                                        @foreach($statementRoles as $role)
                                            <div class="mb-4 relative" @click.outside="closeResults('{{ $role->id }}')">
                                                <label for="modal_role_{{ $role->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $role->display_name ?? $role->name }}</label>

                                                <div class="relative">
                                                    <input type="text"
                                                           id="modal_role_{{ $role->id }}"
                                                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                                                           placeholder="جستجو..."
                                                           x-model="searches['{{ $role->id }}']"
                                                           @input.debounce.300ms="fetchUsers('{{ $role->id }}')"
                                                           @focus="showResults['{{ $role->id }}'] = true"
                                                           autocomplete="off">

                                                    <input type="hidden" name="role_{{ $role->id }}" :value="selectedIds['{{ $role->id }}']">

                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>

                                                    <button type="button"
                                                            x-show="selectedIds['{{ $role->id }}']"
                                                            @click="clearSelection('{{ $role->id }}')"
                                                            class="absolute inset-y-0 left-8 pl-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Dropdown Results --}}
                                                <div x-show="showResults['{{ $role->id }}'] && (users['{{ $role->id }}'].length > 0 || loading['{{ $role->id }}'])"
                                                     x-transition.opacity
                                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">

                                                    <template x-if="loading['{{ $role->id }}']">
                                                        <div class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-center">
                                                            در حال جستجو...
                                                        </div>
                                                    </template>

                                                    <template x-for="user in users['{{ $role->id }}']" :key="user.id">
                                                        <div @click="selectUser('{{ $role->id }}', user)"
                                                             class="cursor-pointer select-none relative py-2 pl-3 pr-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100 transition-colors">
                                                            <div class="flex items-center justify-between">
                                                                <span class="font-medium block truncate" x-text="user.name"></span>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="user.mobile"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        @endforeach
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitPrint()" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">دریافت PDF</button>
                        <button type="button" @click="isPrintModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">انصراف</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch();
            }
        });

        function statementFilter() {
            return {
                searches: {},
                selectedIds: {},
                users: {},
                showResults: {},
                loading: {},
                isPrintModalOpen: false,

                init() {
                    // Initialize provider search
                    this.searches['provider'] = '';
                    this.selectedIds['provider'] = '';
                    this.users['provider'] = [];
                    this.showResults['provider'] = false;
                    this.loading['provider'] = false;

                    // Pre-fill provider if selected
                    @if($selectedProviderId)
                        @php
                            $selectedProvider = \App\Models\User::find($selectedProviderId);
                        @endphp
                        @if($selectedProvider)
                            this.searches['provider'] = '{{ $selectedProvider->name }}';
                            this.selectedIds['provider'] = '{{ $selectedProvider->id }}';
                        @endif
                    @endif

                    // Initialize data for each role
                    @foreach($statementRoles as $role)
                        this.searches['{{ $role->id }}'] = '';
                        this.selectedIds['{{ $role->id }}'] = '';
                        this.users['{{ $role->id }}'] = [];
                        this.showResults['{{ $role->id }}'] = false;
                        this.loading['{{ $role->id }}'] = false;

                        // Pre-fill if selected
                        @if(isset($selectedUsers[$role->id]))
                            this.searches['{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->name }}';
                            this.selectedIds['{{ $role->id }}'] = '{{ $selectedUsers[$role->id]->id }}';
                        @endif
                    @endforeach
                },

                async fetchProviders() {
                    if (!this.searches['provider']) {
                        this.users['provider'] = [];
                        return;
                    }

                    this.loading['provider'] = true;
                    try {
                        const response = await fetch(`{{ route('user.booking.statement.search-providers') }}?q=${this.searches['provider']}`);
                        const data = await response.json();
                        this.users['provider'] = data.data;
                        this.showResults['provider'] = true;
                    } catch (error) {
                        console.error('Error fetching providers:', error);
                    } finally {
                        this.loading['provider'] = false;
                    }
                },

                async fetchUsers(roleId) {
                    if (!this.searches[roleId]) {
                        this.users[roleId] = [];
                        return;
                    }

                    this.loading[roleId] = true;
                    try {
                        const response = await fetch(`{{ route('user.booking.statement.search-users') }}?q=${this.searches[roleId]}&role_id=${roleId}`);
                        const data = await response.json();
                        this.users[roleId] = data.data;
                        this.showResults[roleId] = true;
                    } catch (error) {
                        console.error('Error fetching users:', error);
                    } finally {
                        this.loading[roleId] = false;
                    }
                },

                selectUser(key, user) {
                    this.selectedIds[key] = user.id;
                    this.searches[key] = user.name;
                    this.showResults[key] = false;
                },

                clearSelection(key) {
                    this.selectedIds[key] = '';
                    this.searches[key] = '';
                    this.users[key] = [];
                },

                closeResults(key) {
                    this.showResults[key] = false;
                },

                openPrintModal() {
                    this.isPrintModalOpen = true;
                },

                submitPrint() {
                    // Basic validation removed to allow optional selection
                    document.getElementById('printForm').submit();
                    this.isPrintModalOpen = false;
                }
            }
        }
    </script>
@endsection
