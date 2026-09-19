<div x-data="{ 
        isDrawerOpen: @entangle('isDrawerOpen').live 
    }" 
    @keydown.window="
        if ($event.altKey && $event.key === 'c') { $event.preventDefault(); $wire.switchTab('calls'); }
        if ($event.altKey && $event.key === 't') { $event.preventDefault(); $wire.switchTab('tasks'); }
        if ($event.altKey && $event.key === 's') { $event.preventDefault(); document.getElementById('global-search').focus(); }
        if ($event.altKey && $event.key === 'n') { $event.preventDefault(); $wire.initiateCall(); }
        if ($event.key === 'Escape') {
            if (isDrawerOpen) { $wire.closeDrawer(); }
            $wire.clearGlobalSearch();
        }
    " 
    class="flex flex-col h-[calc(100vh-theme(spacing.16))] overflow-hidden bg-[#F8FAFC] dark:bg-[#0F172A] relative font-sans text-right" dir="rtl">

    <!-- ==========================================
         1. SMART DYNAMIC HEADER (Top Navigation)
         ========================================== -->
    <header class="flex-shrink-0 px-4 md:px-8 py-4 z-20 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        
        <!-- Left Section (Global Search) -->
        <div class="w-full xl:w-auto xl:min-w-[320px] relative group order-2 xl:order-1" @click.outside="$wire.clearGlobalSearch()">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="global-search" wire:model.live.debounce.300ms="globalSearch" 
                   class="block w-full pr-12 pl-16 py-3 bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm text-gray-900 dark:text-white placeholder-gray-400 transition-all shadow-sm focus:shadow-md outline-none" 
                   placeholder="جستجوی سریع مشتری یا معامله..." dir="rtl">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <div wire:loading wire:target="globalSearch">
                    <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="globalSearch">
                    <kbd class="hidden sm:inline-flex items-center gap-0.5 px-2 py-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-[10px] font-mono font-bold text-gray-400 shadow-[0_2px_0_rgba(0,0,0,0.05)] dark:shadow-none">
                        Alt+S
                    </kbd>
                </div>
            </div>

            <!-- Global Search Results Dropdown -->
            @if(!empty($searchResults))
                <div class="absolute top-full mt-2 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 divide-y divide-gray-100 dark:divide-gray-700/60 animate-fade-in" dir="rtl">
                    @foreach($searchResults as $res)
                        <button wire:click="selectSearchResult('{{ $res['type'] }}', {{ $res['id'] }})"
                                class="w-full text-right px-4 py-3 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/30 transition-colors flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 flex items-center justify-center flex-shrink-0 text-gray-500 group-hover:text-indigo-600 dark:text-gray-300 dark:group-hover:text-indigo-400">
                                    @if($res['type'] === 'deal')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @endif
                                </div>
                                <div class="truncate">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate">{{ $res['title'] }}</div>
                                    <div class="text-[10px] text-gray-400 truncate mt-0.5" dir="ltr">{{ $res['subtitle'] }}</div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex-shrink-0">
                                {{ $res['badge'] }}
                            </span>
                        </button>
                    @endforeach
                    <div class="p-2 text-center bg-gray-50/50 dark:bg-gray-900/30">
                        <button wire:click="clearGlobalSearch" class="text-[10px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">بستن نتایج</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Center Section (Panel-aligned Segmented Control) -->
        <div class="flex-1 flex justify-center order-1 xl:order-2">
            <nav class="inline-flex items-center bg-gray-100/80 dark:bg-gray-800/80 p-1.5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 shadow-inner max-w-full overflow-x-auto scrollbar-hide gap-1">
                @php
                    $navTabs = [
                        ['id' => 'today', 'label' => 'امروز من', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['id' => 'deals', 'label' => 'پرونده‌ها', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['id' => 'calls', 'label' => 'مرکز تماس', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                        ['id' => 'tasks', 'label' => 'پیگیری‌ها', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['id' => 'campaign_leads', 'label' => 'لیدها', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                        ['id' => 'goals', 'label' => 'اهداف', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2'],
                    ];
                @endphp

                @foreach($navTabs as $tab)
                    @php
                        $isActive = ($activeTab === $tab['id']);
                    @endphp
                    <button wire:click="switchTab('{{ $tab['id'] }}')" 
                            wire:loading.attr="disabled"
                            class="relative px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-2 whitespace-nowrap {{ $isActive ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-xs border border-gray-200/50 dark:border-gray-700/60' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40' }}">
                        <span wire:loading.remove wire:target="switchTab('{{ $tab['id'] }}')">
                            <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="switchTab('{{ $tab['id'] }}')">
                            <svg class="w-4 h-4 shrink-0 animate-spin text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </span>
                        <span>{{ $tab['label'] }}</span>
                        @if($tab['id'] === 'tasks' && ($stats['pending_tasks'] ?? 0) > 0)
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                {{ $stats['pending_tasks'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Right Section (User & KPIs) -->
        <div class="hidden xl:flex items-center gap-6 order-3 justify-end xl:min-w-[320px]">
            
            <div class="flex items-center gap-3 bg-white dark:bg-gray-800/80 px-4 py-2 rounded-[1.25rem] border border-gray-100 dark:border-gray-700/80 shadow-sm transition-all hover:shadow-md">
                <!-- Won Revenue -->
                <div class="text-left" dir="ltr">
                    <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right">فروش ماه</span>
                    <span class="block text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ number_format((float) ($stats['won_revenue'] ?? 0)) }} <span class="text-[9px] font-normal text-gray-500">ریال</span>
                    </span>
                </div>
                <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>
                <!-- Calls Stats -->
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">تماس امروز</span>
                    <span class="block text-sm font-black text-gray-900 dark:text-white tabular-nums">
                        {{ $stats['calls_today'] ?? 0 }} <span class="text-[9px] font-normal text-gray-500">مورد</span>
                    </span>
                </div>
            </div>
        </div>

    </header>

    <!-- ==========================================
         2. MAIN WORKSPACE AREA (Canvas)
         ========================================== -->
    <main class="flex-1 overflow-auto p-4 md:px-8 md:pb-8 relative z-10 scrollbar-hide">
        
        <div class="h-full flex flex-col" dir="rtl">
            @if($selectedClient)
                <!-- ACTIVE CLIENT FOCUS BAR -->
                <div class="mb-5 bg-white dark:bg-gray-800/95 border border-indigo-100 dark:border-indigo-900/40 rounded-2xl p-4 shadow-sm backdrop-blur-md flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all animate-fade-in" dir="rtl">
                    <!-- Client Identity & Info -->
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100/80 dark:border-indigo-800/50 flex items-center justify-center font-black text-lg text-indigo-600 dark:text-indigo-400 shadow-sm flex-shrink-0">
                            {{ mb_substr($selectedClient->full_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">مشتری فعال میز کار:</span>
                                <button wire:click="openDrawer" class="text-sm font-black text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $selectedClient->full_name }}
                                </button>
                                @if($selectedClient->status)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold" 
                                          style="background-color: {{ ($selectedClient->status->color ?? '#6366f1') . '15' }}; color: {{ $selectedClient->status->color ?? '#6366f1' }}; border: 1px solid {{ ($selectedClient->status->color ?? '#6366f1') . '30' }}">
                                        {{ $selectedClient->status->label ?? $selectedClient->status->name }}
                                    </span>
                                @endif
                                @if($selectedDeal)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        پرونده: {{ $selectedDeal->title }} ({{ $selectedDeal->stage?->name ?? 'مرحله اول' }})
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-[11px] text-gray-500 dark:text-gray-400 flex-wrap">
                                @if($selectedClient->phone)
                                    <span class="inline-flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-300" dir="ltr">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $selectedClient->phone }}
                                    </span>
                                @endif
                                @if($selectedClient->case_number)
                                    <span>شماره پرونده: <span class="font-bold tabular-nums">{{ $selectedClient->case_number }}</span></span>
                                @endif
                                @if($selectedClient->users->isNotEmpty())
                                    <span>کارشناس: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $selectedClient->users->pluck('name')->join('، ') }}</span></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Toolbar -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Direct Call -->
                        @if($selectedClient->phone)
                            <button wire:click="initiateCall" title="برقراری تماس VoIP" class="px-3 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors flex items-center gap-1.5 text-xs font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>تماس</span>
                            </button>
                        @endif

                        <!-- Add Task / Follow-up -->
                        <button wire:click="$dispatch('openCreateFollowupModal')" title="ثبت وظیفه / پیگیری" class="px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors flex items-center gap-1.5 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>پیگیری جدید</span>
                        </button>

                        <!-- Deal button -->
                        <button wire:click="switchTab('deals')" title="مشاهده یا ثبت معامله" class="px-3 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors flex items-center gap-1.5 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>معاملات</span>
                        </button>

                        <!-- Toggle Drawer -->
                        <button wire:click="toggleDrawer" title="باز / بستن کشوی سوابق" class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-1.5 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                            <span>کشوی سوابق</span>
                        </button>

                        <!-- Clear Active Client (Focus Exit) -->
                        <button wire:click="clearActiveClient" title="خروج از حالت کلاینت فعال (مشاهده کل کارها)" class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if($activeTab === 'deals')
                @livewire('sales::deal-tab', ['selectedDealId' => $selectedDealId, 'selectedClientId' => $selectedClientId], key('deal-tab-'.($selectedClientId ?: 'none').'-'.($selectedDealId ?: 'none')))
            @elseif($activeTab === 'calls')
                @livewire('sales::call-center-tab', ['selectedClientId' => $selectedClientId], key('call-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'tasks')
                @livewire('sales::task-tab', ['selectedClientId' => $selectedClientId], key('task-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'today')
                @livewire('sales::today-tab', ['selectedClientId' => $selectedClientId], key('today-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'campaign_leads')
                @livewire('sales::campaign-leads-tab', ['selectedClientId' => $selectedClientId], key('camp-leads-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'goals')
                @livewire('sales::cockpit-goal-manager', [], key('goals-tab'))
            @endif
        </div>
    </main>

    <!-- ==========================================
         3. SLIDE-OVER DETAIL DRAWER (Left Side)
         ========================================== -->
    <template x-teleport="body">
        <div x-show="isDrawerOpen" class="fixed inset-0 z-[100]" role="dialog" aria-modal="true" style="display: none;">
            <!-- Backdrop Overlay (covers the entire screen including the header) -->
            <div x-show="isDrawerOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-950/60 dark:bg-black/75 z-[100] backdrop-blur-sm" 
                 @click="$wire.closeDrawer()"></div>

            <!-- The Drawer Panel -->
            <div x-data="{ drawerTab: 'summary' }" 
                 @client-changed.window="drawerTab = 'summary'"
                 class="fixed inset-y-0 left-0 w-full sm:w-[420px] lg:w-[480px] bg-white dark:bg-gray-900 shadow-[-20px_0_50px_rgba(0,0,0,0.25)] dark:shadow-[-20px_0_50px_rgba(0,0,0,0.7)] z-[101] border-r border-gray-200/50 dark:border-gray-800/80 transform transition-transform duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] flex flex-col"
                 :class="isDrawerOpen ? 'translate-x-0' : '-translate-x-full'" dir="rtl">
        
        @if($selectedClient)
            <!-- Drawer Header (Sticky) -->
            <div class="flex-shrink-0 px-6 py-5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800/80 sticky top-0 z-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 border border-indigo-100/50 dark:border-indigo-900/50 flex items-center justify-center font-black text-xl text-indigo-600 dark:text-indigo-400 shadow-sm">
                        @if($selectedDeal)
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @else
                            {{ mb_substr($selectedClient->full_name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight">
                            {{ $selectedDeal ? $selectedDeal->title : $selectedClient->full_name }}
                        </h2>
                        <div class="flex flex-col gap-0.5 mt-1">
                            @if($selectedDeal)
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-semibold">
                                    مشتری: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $selectedClient->full_name }}</span>
                                </span>
                            @endif
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">پرونده: <span class="tabular-nums font-bold">{{ $selectedClient->case_number ?: $selectedClient->id }}</span></span>
                                @if($selectedDeal && $selectedDeal->stage)
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                    <span class="text-[10px] font-bold flex items-center gap-1" style="color: {{ $selectedDeal->stage->color }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $selectedDeal->stage->color }}"></span>
                                        {{ $selectedDeal->stage->name }}
                                    </span>
                                @elseif($selectedClient->status)
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                    <span class="text-[10px] font-bold flex items-center gap-1" style="color: {{ $selectedClient->status->color }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $selectedClient->status->color }}"></span>
                                        {{ $selectedClient->status->label }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Close Button -->
                <div class="flex items-center gap-1">
                    <button wire:click="closeDrawer" title="بستن کشو (کلاینت در میز کار فعال می‌ماند)" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Internal Sub-Tabs Navigation -->
            <div class="flex-shrink-0 px-6 py-2 bg-gray-50/50 dark:bg-gray-800/20 border-b border-gray-100 dark:border-gray-800/80 flex gap-1">
                <button @click="drawerTab = 'summary'" 
                        :class="drawerTab === 'summary' ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-black' : 'text-gray-500 dark:text-gray-400 font-semibold hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-800/30'"
                        class="flex-1 py-2 text-[11px] rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    خلاصه پرونده
                </button>
                <button @click="drawerTab = 'notes'" 
                        :class="drawerTab === 'notes' ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-black' : 'text-gray-500 dark:text-gray-400 font-semibold hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-800/30'"
                        class="flex-1 py-2 text-[11px] rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    یادداشت و پیامک
                </button>
                <button @click="drawerTab = 'activity'" 
                        :class="drawerTab === 'activity' ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 font-black' : 'text-gray-500 dark:text-gray-400 font-semibold hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-800/30'"
                        class="flex-1 py-2 text-[11px] rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    فید تعاملات
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6 scrollbar-hide">
                
                <!-- TAB 1: SUMMARY -->
                <div x-show="drawerTab === 'summary'" class="space-y-6 animate-fade-in">
                    
                    <!-- Quick Actions Grid -->
                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="initiateCall" class="flex flex-col items-center justify-center py-4 bg-blue-50/50 hover:bg-blue-500 text-blue-600 hover:text-white dark:bg-blue-950/20 dark:hover:bg-blue-600 dark:text-blue-400 dark:hover:text-white rounded-2xl transition-all transform active:scale-95 border border-blue-100/30 dark:border-blue-900/30 shadow-xs hover:shadow-md">
                            <svg class="w-6 h-6 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-[10px] font-black">تماس سیستم</span>
                        </button>
                        <button wire:click="switchTab('calls')" class="flex flex-col items-center justify-center py-4 bg-emerald-50/50 hover:bg-emerald-500 text-emerald-600 hover:text-white dark:bg-emerald-950/20 dark:hover:bg-emerald-600 dark:text-emerald-400 dark:hover:text-white rounded-2xl transition-all transform active:scale-95 border border-emerald-100/30 dark:border-emerald-900/30 shadow-xs hover:shadow-md">
                            <svg class="w-6 h-6 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px] font-black">ثبت مکالمه</span>
                        </button>
                        <button wire:click="switchTab('tasks')" class="flex flex-col items-center justify-center py-4 bg-purple-50/50 hover:bg-purple-500 text-purple-600 hover:text-white dark:bg-purple-950/20 dark:hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white rounded-2xl transition-all transform active:scale-95 border border-purple-100/30 dark:border-purple-900/30 shadow-xs hover:shadow-md">
                            <svg class="w-6 h-6 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px] font-black">ایجاد تسک</span>
                        </button>
                    </div>

                    <!-- Deal Details Card (if selected) -->
                    @if($selectedDeal)
                        <div class="bg-indigo-50/30 dark:bg-indigo-950/10 rounded-[2rem] border border-indigo-100/30 dark:border-indigo-900/30 p-4 space-y-4 shadow-2xs">
                            <h3 class="text-xs font-black text-indigo-900 dark:text-indigo-400">اطلاعات پرونده فروش</h3>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="bg-white dark:bg-gray-900 p-3 rounded-xl border border-gray-100 dark:border-gray-800 shadow-3xs">
                                    <span class="block text-[10px] text-gray-400 font-bold mb-1">مبلغ پیش‌بینی‌شده</span>
                                    <span class="font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        {{ number_format($selectedDeal->expected_revenue) }} <span class="text-[9px] font-normal text-gray-500">ریال</span>
                                    </span>
                                </div>
                                <div class="bg-white dark:bg-gray-900 p-3 rounded-xl border border-gray-100 dark:border-gray-800 shadow-3xs">
                                    <span class="block text-[10px] text-gray-400 font-bold mb-1">تاریخ بسته‌شدن</span>
                                    <span class="font-extrabold text-gray-700 dark:text-gray-300 tabular-nums">
                                        {{ $selectedDeal->expected_close_date ? $selectedDeal->expected_close_date->format('Y/m/d') : 'تعیین نشده' }}
                                    </span>
                                </div>
                            </div>
                            @if($selectedDeal->description)
                                <div class="bg-white dark:bg-gray-900 p-3 rounded-xl border border-gray-100 dark:border-gray-800 shadow-3xs">
                                    <span class="block text-[10px] text-gray-400 font-bold mb-1">توضیحات پرونده</span>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $selectedDeal->description }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Contact Details Card -->
                    <div class="bg-gray-50 dark:bg-gray-800/40 rounded-[2rem] border border-gray-100 dark:border-gray-800 p-4 space-y-4">
                        <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">جزییات پرونده مخاطب</h3>
                        
                        <!-- Phone Field -->
                        <div class="flex items-center justify-between bg-white dark:bg-gray-900 px-4 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-500 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-400">تلفن همراه</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums select-all" dir="ltr">{{ $selectedClient->phone }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $selectedClient->phone }}')" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Email Field -->
                        @if($selectedClient->email)
                            <div class="flex items-center justify-between bg-white dark:bg-gray-900 px-4 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">نشانی ایمیل</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-gray-900 dark:text-white select-all">{{ $selectedClient->email }}</span>
                                    <button onclick="navigator.clipboard.writeText('{{ $selectedClient->email }}')" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB 2: NOTES & SMS -->
                <div x-show="drawerTab === 'notes'" class="space-y-6 animate-fade-in">
                    <!-- Quick Note -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-700 dark:text-gray-300">یادداشت سریع پرونده</label>
                        <div class="relative group">
                            <textarea wire:model="quickNote" wire:blur="saveQuickNote" rows="6" 
                                      class="w-full text-sm rounded-[20px] border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 p-4 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all resize-none shadow-inner" 
                                      placeholder="نکات کلیدی مکالمه و پیگیری را بنویسید (با کلیک خارج از کادر ذخیره می‌شود)..."></textarea>
                            <div class="absolute bottom-3 left-3 opacity-0 group-focus-within:opacity-100 transition-opacity">
                                <span class="text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 px-2.5 py-1 rounded-lg font-bold animate-pulse">در حال نوشتن...</span>
                            </div>
                        </div>
                    </div>

                    <!-- SMS Panel -->
                    @if(class_exists('\Modules\Sms\Services\SmsManager'))
                        <div class="bg-gray-50 dark:bg-gray-800/40 rounded-[2rem] border border-gray-100 dark:border-gray-800 p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">ارسال پیامک سریع</h3>
                                <button wire:click="$toggle('showSmsPanel')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                    {{ $showSmsPanel ? 'بستن پنل' : 'باز کردن پنل' }}
                                </button>
                            </div>
                            
                            @if($showSmsPanel)
                                <div class="space-y-3 animate-fade-in">
                                    <textarea wire:model="smsText" rows="3" class="w-full text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all resize-none shadow-inner" placeholder="متن پیامک خود را تایپ کنید..."></textarea>
                                    <div class="flex justify-end">
                                        <button wire:click="sendQuickSms" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all transform active:scale-95">ارسال پیامک</button>
                                    </div>
                                    @error('smsText') <span class="text-[10px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- TAB 3: ACTIVITY FEED -->
                <div x-show="drawerTab === 'activity'" class="space-y-6 animate-fade-in">
                    
                    <div class="space-y-0 relative before:absolute before:inset-y-0 before:right-[15px] before:w-[2px] before:bg-gray-100 dark:before:bg-gray-800/80 pb-4">
                        
                        <!-- Reminders -->
                        @if($activeReminders->count() > 0)
                            @foreach($activeReminders as $reminder)
                                <div class="relative pr-12 pb-6 group">
                                    <div class="absolute right-0 top-0 w-8 h-8 rounded-full bg-white dark:bg-gray-900 border-2 border-amber-400 dark:border-amber-500 flex items-center justify-center z-10 shadow-sm group-hover:scale-110 transition-transform">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                                    </div>
                                    <div class="bg-amber-50/50 dark:bg-amber-900/10 rounded-2xl p-4 border border-amber-100 dark:border-amber-900/30">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="font-extrabold text-xs text-amber-900 dark:text-amber-400">یادآوری فعال</span>
                                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-500 tabular-nums" dir="ltr">{{ $reminder->remind_at->format('Y/m/d H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">{{ $reminder->message }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Call logs -->
                        @foreach($lastCalls as $call)
                            @php
                                $isSuccess = in_array($call->status, ['done', 'answered']);
                                $isPlanned = $call->status === 'planned';
                                $isNoAnswer = $call->status === 'no_answer';
                                $isBusy = $call->status === 'busy';
                                $isCancelled = in_array($call->status, ['cancelled', 'canceled']);

                                if ($isSuccess) {
                                    $iconBorder = 'border-emerald-400 dark:border-emerald-500 text-emerald-500';
                                    $badgeClass = 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400';
                                    $dotClass = 'bg-emerald-500';
                                    $badgeLabel = 'مکالمه موفق';
                                } elseif ($isPlanned) {
                                    $iconBorder = 'border-blue-400 dark:border-blue-500 text-blue-500';
                                    $badgeClass = 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400';
                                    $dotClass = 'bg-blue-500';
                                    $badgeLabel = 'در انتظار تماس';
                                } elseif ($isNoAnswer) {
                                    $iconBorder = 'border-rose-400 dark:border-rose-500 text-rose-500';
                                    $badgeClass = 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400';
                                    $dotClass = 'bg-rose-500';
                                    $badgeLabel = 'بدون پاسخ';
                                } elseif ($isBusy) {
                                    $iconBorder = 'border-amber-400 dark:border-amber-500 text-amber-500';
                                    $badgeClass = 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400';
                                    $dotClass = 'bg-amber-500';
                                    $badgeLabel = 'اشغال';
                                } elseif ($isCancelled) {
                                    $iconBorder = 'border-gray-400 dark:border-gray-500 text-gray-500';
                                    $badgeClass = 'bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300';
                                    $dotClass = 'bg-gray-500';
                                    $badgeLabel = 'لغو شده';
                                } else {
                                    $iconBorder = 'border-red-400 dark:border-red-500 text-red-500';
                                    $badgeClass = 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400';
                                    $dotClass = 'bg-red-500';
                                    $badgeLabel = 'ناموفق';
                                }
                            @endphp
                            <div class="relative pr-12 pb-6 group">
                                <div class="absolute right-0 top-0 w-8 h-8 rounded-full bg-white dark:bg-gray-900 border-2 {{ $iconBorder }} flex items-center justify-center z-10 shadow-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl p-4 border border-gray-100 dark:border-gray-800/80">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-extrabold text-xs text-gray-900 dark:text-white">
                                            تماس {{ $call->direction === 'inbound' ? 'ورودی' : 'خروجی' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 tabular-nums dir-ltr">
                                            {{ $call->call_date ? \Morilog\Jalali\Jalalian::fromDateTime($call->call_date)->format('Y/m/d') : '-' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-2">{{ $call->reason ?: 'بدون موضوع' }}</p>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $badgeClass }} text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $call->result ?: $badgeLabel }}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Tasks -->
                        @foreach($pendingFollowups as $task)
                            <div class="relative pr-12 pb-6 group">
                                <div class="absolute right-0 top-0 w-8 h-8 rounded-full bg-white dark:bg-gray-900 border-2 border-purple-400 dark:border-purple-500 flex items-center justify-center z-10 shadow-sm group-hover:scale-110 transition-transform text-purple-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="bg-purple-50/20 dark:bg-purple-950/10 rounded-2xl p-4 border border-purple-100/30 dark:border-purple-900/30">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-extrabold text-xs text-purple-900 dark:text-purple-400">پیگیری و تسک</span>
                                        <span class="text-[10px] font-bold text-purple-600 dark:text-purple-500 tabular-nums" dir="ltr">{{ $task->due_at ? $task->due_at->format('Y/m/d') : 'بدون سررسید' }}</span>
                                    </div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">{{ $task->title }}</p>
                                </div>
                            </div>
                        @endforeach

                        @if($lastCalls->count() === 0 && $pendingFollowups->count() === 0 && $activeReminders->count() === 0)
                            <div class="pr-10 py-4">
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center border border-dashed border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-400 dark:text-gray-500 text-xs font-semibold">تاریخچه تعاملاتی برای این مخاطب ثبت نشده است.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Bottom padding space for scrolling -->
                <div class="h-8"></div>
            </div>
        @endif
    </div>
        </div>
    </template>

    <!-- Script wrapper for VoIP -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('voip-initiate', (e) => {
                console.log('[VoIP] Outbound Call Initialized:', e.detail);
                if (typeof window.voipDialOut === 'function') {
                    window.voipDialOut(e.detail.phone);
                }
            });
        });
    </script>
</div>
