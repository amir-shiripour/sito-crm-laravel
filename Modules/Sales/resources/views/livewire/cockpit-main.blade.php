<div x-data="{}" @keydown.window="
    if ($event.altKey && $event.key === 'c') { $event.preventDefault(); $wire.switchTab('calls'); }
    if ($event.altKey && $event.key === 't') { $event.preventDefault(); $wire.switchTab('tasks'); }
    if ($event.altKey && $event.key === 's') { $event.preventDefault(); document.getElementById('global-search').focus(); }
    if ($event.altKey && $event.key === 'n') { $event.preventDefault(); $wire.initiateCall(); }
" class="w-full">
    
    <!-- 5 Stats Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-5" dir="rtl">
        <!-- Card 1: Total Customers -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">مشتریان کل</span>
                    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['total_clients'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Calls Today -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">تماس‌های امروز</span>
                    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['calls_today'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Answered Calls Today -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">تماس موفق امروز</span>
                    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['answered_today'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Active Tasks -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">تسک‌های باز من</span>
                    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['pending_tasks'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Card 5: Overdue Tasks -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">معوق سررسید گذشته</span>
                    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['overdue_tasks'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Progress Bar -->
    @if(isset($goalProgress) && $goalProgress['has_goal'])
        <div class="mb-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800/80 dark:to-gray-900/80 rounded-2xl p-4 border border-indigo-100 dark:border-gray-700 shadow-sm" dir="rtl">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-2 gap-2">
                <div class="flex items-center gap-2">
                    <span class="p-1 bg-indigo-600 text-white rounded-lg text-xs font-bold">🎯 هدف</span>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                        {{ $goalProgress['label'] }} ({{ $goalProgress['period'] === 'daily' ? 'روزانه' : ($goalProgress['period'] === 'weekly' ? 'هفتگی' : 'ماهانه') }})
                    </span>
                </div>
                <span class="text-sm font-black text-indigo-700 dark:text-indigo-400">
                    {{ $goalProgress['current'] }} / {{ $goalProgress['target'] }} ({{ $goalProgress['percent'] }}%)
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3.5 overflow-hidden">
                <div class="bg-indigo-600 h-3.5 rounded-full transition-all duration-700" style="width: {{ $goalProgress['percent'] }}%"></div>
            </div>
        </div>
    @endif

    <!-- Global Search Bar -->
    <div class="mb-5" dir="rtl">
        <div class="relative rounded-2xl shadow-sm">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="h-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" id="global-search" wire:model.live.debounce.300ms="globalSearch" class="block w-full pr-11 pl-4 py-3 border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right" placeholder="جستجو در کل مشتریان، تماس‌ها و تسک‌ها (Alt+S)..." dir="rtl">
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Left Column: Master Tabs -->
        <div class="w-full lg:w-3/5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden relative shadow-sm">
            
            <!-- Skeleton/Wire Loading Loader -->
            <div wire:loading.delay class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-50 flex flex-col items-center justify-center rounded-2xl backdrop-blur-[1px]">
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-6 py-4 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                    <div class="w-6 h-6 border-3 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">در حال به روز رسانی...</span>
                </div>
            </div>

            <!-- Dashboard Tab Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50" dir="rtl">
                <ul class="flex flex-wrap -mb-px text-sm font-bold text-center text-gray-500 dark:text-gray-400 px-4 pt-2">
                    <li class="me-2">
                        <button wire:click="switchTab('customers')" class="inline-flex items-center gap-2 p-4 border-b-2 rounded-t-xl transition-all duration-200 {{ $activeTab == 'customers' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent hover:text-gray-700 hover:bg-white/60 dark:hover:bg-gray-700/50 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            👥 مشتریان
                        </button>
                    </li>
                    <li class="me-2">
                        <button wire:click="switchTab('calls')" class="inline-flex items-center gap-2 p-4 border-b-2 rounded-t-xl transition-all duration-200 {{ $activeTab == 'calls' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent hover:text-gray-700 hover:bg-white/60 dark:hover:bg-gray-700/50 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            📞 تماس‌ها
                        </button>
                    </li>
                    <li class="me-2">
                        <button wire:click="switchTab('tasks')" class="inline-flex items-center gap-2 p-4 border-b-2 rounded-t-xl transition-all duration-200 {{ $activeTab == 'tasks' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent hover:text-gray-700 hover:bg-white/60 dark:hover:bg-gray-700/50 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            ✅ تسک‌ها
                        </button>
                    </li>
                    <li class="me-2">
                        <button wire:click="switchTab('today')" class="inline-flex items-center gap-2 p-4 border-b-2 rounded-t-xl transition-all duration-200 {{ $activeTab == 'today' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent hover:text-gray-700 hover:bg-white/60 dark:hover:bg-gray-700/50 dark:hover:text-gray-300' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            📅 امروز من
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Tab Panels Content -->
            <div class="p-5 min-h-[550px]" dir="rtl">
                @if($activeTab === 'customers')
                    @livewire('sales::customer-tab', ['selectedClientId' => $selectedClientId], key('cust-tab'))
                @elseif($activeTab === 'calls')
                    @livewire('sales::call-center-tab', ['selectedClientId' => $selectedClientId], key('call-tab-'.($selectedClientId ?: 'none')))
                @elseif($activeTab === 'tasks')
                    @livewire('sales::task-tab', ['selectedClientId' => $selectedClientId], key('task-tab-'.($selectedClientId ?: 'none')))
                @elseif($activeTab === 'today')
                    @livewire('sales::today-tab', ['selectedClientId' => $selectedClientId], key('today-tab-'.($selectedClientId ?: 'none')))
                @endif
            </div>
        </div>

        <!-- Right Column: Detail Panel -->
        <div class="w-full lg:w-2/5" dir="rtl">
            <div class="sticky top-5 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 min-h-[550px] flex flex-col">
                @if($selectedClient)
                    <!-- Client Header info -->
                    <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-black text-xl shadow-sm">
                                {{ mb_substr($selectedClient->full_name, 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $selectedClient->full_name }}</h3>
                                    @if($selectedClient->status)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: {{ $selectedClient->status->color }}15; color: {{ $selectedClient->status->color }}">
                                            {{ $selectedClient->status->label }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-1">پرونده: {{ $selectedClient->case_number ?: $selectedClient->id }}</p>
                            </div>
                        </div>
                        <button wire:click="clearSelection" class="text-gray-400 hover:text-red-500 transition-colors p-1 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg" title="بستن پرونده">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Client Detail Actions Hub -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <!-- VoIP Call button -->
                        <button wire:click="initiateCall" class="flex flex-col items-center justify-center py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-xl transition-all font-bold">
                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-[10px]">تماس VoIP</span>
                        </button>
                        
                        <!-- Log Call / Register Call button -->
                        <button wire:click="switchTab('calls')" class="flex flex-col items-center justify-center py-2.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-xl transition-all font-bold">
                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px]">ثبت تماس</span>
                        </button>

                        <!-- SMS panel button -->
                        @if(class_exists('\Modules\Sms\Services\SmsManager'))
                            <button wire:click="$toggle('showSmsPanel')" class="flex flex-col items-center justify-center py-2.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-xl transition-all font-bold">
                                <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span class="text-[10px]">ارسال پیامک</span>
                            </button>
                        @else
                            <button disabled class="opacity-50 cursor-not-allowed flex flex-col items-center justify-center py-2.5 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-xl transition-all font-bold">
                                <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="text-[10px]">پیامک غیرفعال</span>
                            </button>
                        @endif

                        <!-- Add Task button -->
                        <button wire:click="switchTab('tasks')" class="flex flex-col items-center justify-center py-2.5 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-xl transition-all font-bold">
                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px]">ثبت تسک</span>
                        </button>
                    </div>

                    <!-- SMS Quick Panel -->
                    @if($showSmsPanel)
                        <div class="mb-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900 rounded-2xl p-3">
                            <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-400 mb-2">ارسال پیامک سریع</h4>
                            <div class="flex gap-2">
                                <input type="text" wire:model="smsText" class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-xl text-xs" placeholder="متن پیامک...">
                                <button wire:click="sendQuickSms" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold">ارسال</button>
                            </div>
                            @error('smsText') <span class="text-[10px] text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Client General Fields -->
                    <div class="bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-2xl p-3 mb-4 text-xs space-y-2">
                        <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-400">شماره تماس:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200" dir="ltr">{{ $selectedClient->phone }}</span>
                        </div>
                        @if($selectedClient->email)
                            <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-400">پست الکترونیکی:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedClient->email }}</span>
                            </div>
                        @endif
                        @if($selectedClient->national_code)
                            <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-400">کد ملی:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $selectedClient->national_code }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Inline Quick Note (Auto-save on blur or save button) -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                یادداشت اپراتور (ثبت خودکار)
                            </label>
                            <button wire:click="saveQuickNote" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold hover:underline">ذخیره دستی</button>
                        </div>
                        <textarea wire:model="quickNote" wire:blur="saveQuickNote" rows="3" class="w-full text-xs rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-right" placeholder="یادداشت‌های پرونده را در اینجا بنویسید..."></textarea>
                    </div>

                    <!-- Tabs/Accordions for Client History -->
                    <div class="flex-grow space-y-4">
                        <!-- Section: Active Reminders -->
                        @if($activeReminders->count() > 0)
                            <div class="bg-amber-50/40 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900 rounded-2xl p-3">
                                <h4 class="text-xs font-bold text-amber-800 dark:text-amber-400 mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    یادآوری‌های فعال مشتری
                                </h4>
                                <div class="space-y-1.5">
                                    @foreach($activeReminders as $reminder)
                                        <div class="flex items-center justify-between text-[11px] bg-white dark:bg-gray-800 p-2 rounded-xl border border-amber-100/50 dark:border-amber-900/50">
                                            <span class="text-gray-700 dark:text-gray-300 font-semibold">{{ $reminder->message }}</span>
                                            <span class="text-gray-400 text-[10px]" dir="ltr">{{ $reminder->remind_at->format('Y-m-d H:i') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Section: Recent Timeline / Actions history -->
                        <div class="border border-gray-100 dark:border-gray-700 rounded-2xl p-3">
                            <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                ۵ رویداد اخیر این مشتری
                            </h4>
                            <div class="space-y-3 relative before:absolute before:inset-y-0 before:right-2.5 before:w-0.5 before:bg-gray-100 dark:before:bg-gray-700 pr-1">
                                <!-- Planned / Done Calls Timeline -->
                                @foreach($lastCalls as $call)
                                    <div class="relative pr-6">
                                        <div class="absolute right-0 top-1 w-5.5 h-5.5 rounded-full flex items-center justify-center text-[10px] {{ $call->status === 'done' ? 'bg-emerald-500 text-white' : ($call->status === 'planned' ? 'bg-blue-500 text-white' : 'bg-red-500 text-white') }} border-2 border-white dark:border-gray-800">
                                            📞
                                        </div>
                                        <div class="text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-gray-800 dark:text-gray-200">
                                                    تماس {{ $call->direction === 'inbound' ? 'ورودی' : 'خروجی' }} ({{ $call->status === 'done' ? 'موفق' : 'برنامه‌ریزی‌شده' }})
                                                </span>
                                                <span class="text-[10px] text-gray-400" dir="ltr">{{ $call->call_date->format('Y-m-d') }}</span>
                                            </div>
                                            <p class="text-gray-500 mt-0.5">{{ $call->reason }} - {{ $call->result ?: 'بدون نتیجه' }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Tasks/Followups Timeline -->
                                @foreach($pendingFollowups as $task)
                                    <div class="relative pr-6">
                                        <div class="absolute right-0 top-1 w-5.5 h-5.5 rounded-full bg-purple-500 text-white flex items-center justify-center text-[10px] border-2 border-white dark:border-gray-800">
                                            ✓
                                        </div>
                                        <div class="text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-gray-800 dark:text-gray-200">پیگیری (تسک باز)</span>
                                                <span class="text-[10px] text-gray-400" dir="ltr">{{ $task->due_at ? $task->due_at->format('Y-m-d') : '' }}</span>
                                            </div>
                                            <p class="text-gray-500 mt-0.5">{{ $task->title }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                @if($lastCalls->count() === 0 && $pendingFollowups->count() === 0)
                                    <p class="text-xs text-gray-400 text-center py-2">هیچ رویداد اخیر یا وظیفه بازی برای این مشتری ثبت نشده است.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State details -->
                    <div class="flex-grow flex flex-col items-center justify-center text-center p-6" dir="rtl">
                        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4 shadow-inner">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">پرونده‌ای انتخاب نشده است</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 max-w-[240px] leading-relaxed">برای مدیریت پرونده و ثبت پیگیری یا تماس، از لیست سمت راست یک مشتری را انتخاب کنید.</p>
                        
                        <!-- Mini widget goals helper when client is not selected -->
                        <div class="mt-8 w-full border-t border-dashed border-gray-200 dark:border-gray-700 pt-6">
                            @livewire('sales::cockpit-goal-manager')
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Script wrapper for keyboard shortcuts & VoIP integration -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // VoIP call dispatcher event
            document.addEventListener('voip-initiate', (e) => {
                console.log('[VoIP] Dialing outbound call details:', e.detail);
                if (typeof window.voipDialOut === 'function') {
                    window.voipDialOut(e.detail.phone);
                } else {
                    alert('سیستم VoIP در حال حاضر آماده متصل شدن است. شماره‌گیری آزمایشی: ' + e.detail.phone);
                }
            });
        });
    </script>
</div>
