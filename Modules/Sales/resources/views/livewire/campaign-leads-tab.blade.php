@php
    $inputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-xs";
    $stats = $leadStats ?? [];
@endphp

<div class="space-y-5" dir="rtl">
    <!-- ==========================================
         1. TOP KPI SUMMARY CARDS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- کل لیدها -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">کل لیدها</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white block font-sans">
                    {{ $stats['total'] ?? 0 }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 block">مجموع لیدهای فعال</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <!-- در انتظار تماس -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">در انتظار تماس</span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block font-sans">
                    {{ $stats['pending'] ?? 0 }}
                </span>
                <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 block">نیازمند اقدام اولیه</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100/60 dark:border-amber-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- پیگیری شده و راغب -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">پیگیری شده و راغب</span>
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 block font-sans">
                    {{ $stats['contacted'] ?? 0 }}
                </span>
                <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 block">در مسیر صلاحیت‌سنجی</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100/60 dark:border-sky-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
        </div>

        <!-- تبدیل شده به پرونده -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">تبدیل به پرونده</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block font-sans">
                    {{ $stats['converted'] ?? 0 }}
                </span>
                <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block">فرصت‌های فروش ایجادشده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ==========================================
         2. TOOLBAR & FILTERS
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700/80 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- فیلتر وضعیت لید -->
            <select wire:model.live="filterStatus" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200 font-medium">
                <option value="all">همه وضعیت‌ها (لیدهای فعال)</option>
                <option value="pending">در انتظار تماس</option>
                <option value="contacted">تماس گرفته شده</option>
                <option value="responded">پاسخ‌داده‌شده / راغب</option>
                <option value="converted">تبدیل شده به پرونده</option>
                <option value="lost">رد شده / ناموفق</option>
            </select>

            <!-- فیلتر منبع لید -->
            <select wire:model.live="filterSource" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200 font-medium">
                <option value="all">همه منابع لید</option>
                <option value="campaign">کمپین‌های تبلیغاتی</option>
                <option value="crm">مشتریان مستقیم</option>
            </select>

            <!-- فیلتر کارشناس مسئول (مخصوص مدیران) -->
            @if($isManager)
                <select wire:model.live="filterAgent" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200 font-medium">
                    <option value="me">لیدهای من</option>
                    <option value="all">همه کارشناسان</option>
                    @foreach($salesAgents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <!-- نوار جستجو -->
        <div class="relative w-full md:w-72">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی نام یا شماره لید..." 
                class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3.5 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400 font-sans">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- ==========================================
         3. LEADS TABLE
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50/80 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-3.5 px-4">نام و مشخصات لید</th>
                        <th class="py-3.5 px-4">شماره تماس</th>
                        <th class="py-3.5 px-4">منبع لید</th>
                        @if($isManager)
                            <th class="py-3.5 px-4">کارشناس مسئول</th>
                        @endif
                        <th class="py-3.5 px-4">وضعیت لید</th>
                        <th class="py-3.5 px-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50 pointer-events-none" class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-900 dark:text-gray-100 transition-opacity duration-200 font-sans">
                    @forelse($leads as $lead)
                        @php
                            $isRowActive = ($selectedClientId && $lead->client_id === $selectedClientId);
                        @endphp
                        <tr class="transition-colors {{ $isRowActive ? 'bg-indigo-50/70 dark:bg-indigo-950/40 ring-1 ring-inset ring-indigo-500/30' : ($lead->is_converted ? 'bg-emerald-50/25 dark:bg-emerald-950/15 hover:bg-gray-50/80 dark:hover:bg-gray-900/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-900/30') }}">
                            <!-- نام و عنوان لید -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="openLeadDrawer('{{ $lead->type }}', {{ $lead->id }})" 
                                            title="مشاهده کشو ۳۶۰ درجه لید"
                                            class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 text-right transition-colors inline-flex items-center gap-1.5 group">
                                        <span>{{ $lead->name }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </button>
                                    @if(!empty($lead->client_status_label))
                                        <span class="text-[9px] bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 px-2 py-0.5 rounded-full font-medium border border-indigo-100 dark:border-indigo-900/40">
                                            {{ $lead->client_status_label }}
                                        </span>
                                    @elseif($lead->type === 'crm')
                                        <span class="text-[9px] bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 px-2 py-0.5 rounded-full font-medium border border-indigo-100 dark:border-indigo-900/40">
                                            مشتری مستقیم
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($lead->username))
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5 font-sans" dir="ltr">@<span>{{ $lead->username }}</span></span>
                                @endif
                            </td>

                            <!-- شماره تماس -->
                            <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300 font-sans" dir="ltr">
                                {{ $lead->phone ?: '—' }}
                            </td>

                            <!-- منبع لید -->
                            <td class="py-3.5 px-4">
                                @if($lead->type === 'campaign')
                                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 text-[10px] font-bold inline-flex items-center gap-1.5 border border-purple-100/60 dark:border-purple-900/40">
                                        <svg class="w-3 h-3 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                        </svg>
                                        <span>{{ $lead->source_label }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 text-[10px] font-bold inline-flex items-center gap-1.5 border border-indigo-100/60 dark:border-indigo-900/40">
                                        <svg class="w-3 h-3 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ $lead->source_label }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- کارشناس مسئول -->
                            @if($isManager)
                                <td class="py-3.5 px-4">
                                    <span class="bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-lg text-[10px] font-medium inline-block">
                                        {{ $lead->assignee_name }}
                                    </span>
                                </td>
                            @endif

                            <!-- وضعیت لید -->
                            <td class="py-3.5 px-4">
                                <select 
                                    wire:change="updateLeadStatus('{{ $lead->type }}', {{ $lead->id }}, $event.target.value)"
                                    class="text-[11px] font-bold rounded-lg border py-1 px-2.5 transition-colors focus:ring-1 focus:ring-indigo-500 font-sans cursor-pointer {{ 
                                        match($lead->status) {
                                            'pending' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                            'contacted' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60',
                                            'responded' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                            'converted' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                            'lost' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                                            default => 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                        }
                                    }}"
                                    {{ $lead->is_converted ? 'disabled' : '' }}
                                >
                                    <option value="pending" {{ $lead->status === 'pending' ? 'selected' : '' }}>در انتظار تماس</option>
                                    <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>تماس گرفته شده</option>
                                    <option value="responded" {{ $lead->status === 'responded' ? 'selected' : '' }}>پاسخ‌داده‌شده / راغب</option>
                                    <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }} disabled>تبدیل به پرونده</option>
                                    <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>رد شده / ناموفق</option>
                                </select>
                            </td>

                            <!-- عملیات -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- دکمه قبول لید کمپین در صورت عدم تخصیص -->
                                    @if($lead->type === 'campaign' && $lead->assigned_to === null)
                                        <button wire:click="claimContact({{ $lead->id }})" 
                                                wire:loading.attr="disabled"
                                                title="پذیرش لید"
                                                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/40 flex items-center gap-1 text-[11px] font-bold border border-indigo-100 dark:border-indigo-900/40 disabled:opacity-50">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>پذیرش لید</span>
                                        </button>
                                    @endif

                                    <!-- دکمه تبدیل سریع به پرونده فروش -->
                                    @if(!$lead->is_converted)
                                        <button wire:click="convertToDeal('{{ $lead->type }}', {{ $lead->id }})" 
                                                wire:loading.attr="disabled"
                                                title="تبدیل به پرونده فروش"
                                                class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/40 flex items-center gap-1 text-[11px] font-bold border border-emerald-100 dark:border-emerald-900/40 disabled:opacity-50">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span>تبدیل به پرونده</span>
                                        </button>
                                    @else
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[11px] bg-emerald-50 dark:bg-emerald-950/30 px-2.5 py-1 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            پرونده فعال
                                        </span>
                                    @endif

                                    <!-- تماس مستقیم VoIP -->
                                    @if($lead->phone)
                                        <button wire:click="initiateVoipCall('{{ $lead->phone }}', {{ $lead->client_id ?? 'null' }}, {{ $lead->type === 'campaign' ? $lead->id : 'null' }})" 
                                                wire:loading.attr="disabled"
                                                title="تماس صوتی VoIP"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-100 dark:border-blue-900/40 disabled:opacity-50">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- فعال‌سازی در میز کار فروش -->
                                    <button wire:click="activateContactInCockpit('{{ $lead->type }}', {{ $lead->id }})" 
                                            wire:loading.attr="disabled"
                                            title="{{ $isRowActive ? 'مشتری در میز کار فعال است' : 'انتخاب مشتری در میز کار' }}"
                                            class="p-1.5 rounded-xl transition-colors flex items-center gap-1 text-[11px] font-bold disabled:opacity-50 border {{ $isRowActive ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border-indigo-100 dark:border-indigo-900/40' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span class="hidden sm:inline">{{ $isRowActive ? 'فعال در میز کار' : 'فعال‌سازی' }}</span>
                                    </button>

                                    <!-- باز کردن کشو ۳۶۰ درجه جزئیات لید -->
                                    <button wire:click="openLeadDrawer('{{ $lead->type }}', {{ $lead->id }})" 
                                            wire:loading.attr="disabled"
                                            title="باز کردن کشو ۳۶۰ درجه جزئیات لید"
                                            class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors p-1.5 bg-gray-100 dark:bg-gray-700/60 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-600/50">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isManager ? 6 : 5 }}" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">هیچ لیدی با فیلترهای انتخابی یافت نشد.</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">می‌توانید فیلتر وضعیت یا کادر جستجو را تغییر دهید.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($leads->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
