<div x-data="{ 
        isDrawerOpen: @entangle('selectedClientId').live 
    }" 
    @keydown.window="
        if ($event.altKey && $event.key === 'c') { $event.preventDefault(); $wire.switchTab('calls'); }
        if ($event.altKey && $event.key === 't') { $event.preventDefault(); $wire.switchTab('tasks'); }
        if ($event.altKey && $event.key === 's') { $event.preventDefault(); document.getElementById('global-search').focus(); }
        if ($event.altKey && $event.key === 'n') { $event.preventDefault(); $wire.initiateCall(); }
        if ($event.key === 'Escape' && isDrawerOpen) { $wire.clearSelection(); }
    " 
    class="flex flex-col h-[calc(100vh-theme(spacing.16))] overflow-hidden bg-[#F8FAFC] dark:bg-[#0F172A] relative font-sans text-right" dir="rtl">

    <!-- ==========================================
         1. SMART DYNAMIC HEADER (Top Navigation)
         ========================================== -->
    <header class="flex-shrink-0 px-4 md:px-8 py-4 z-20 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        
        <!-- Left Section (Global Search) -->
        <div class="w-full xl:w-auto xl:min-w-[320px] relative group order-2 xl:order-1">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="global-search" wire:model.live.debounce.300ms="globalSearch" 
                   class="block w-full pr-12 pl-16 py-3 bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-sm text-gray-900 dark:text-white placeholder-gray-400 transition-all shadow-sm focus:shadow-md outline-none" 
                   placeholder="جستجوی سریع در میزکار..." dir="rtl">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <kbd class="hidden sm:inline-flex items-center gap-0.5 px-2 py-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-[10px] font-mono font-bold text-gray-400 shadow-[0_2px_0_rgba(0,0,0,0.05)] dark:shadow-none">
                    Alt+S
                </kbd>
            </div>
        </div>

        <!-- Center Section (Floating Glass Segmented Control) -->
        <div class="flex-1 flex justify-center order-1 xl:order-2">
            <nav class="inline-flex items-center bg-white/70 dark:bg-gray-800/50 backdrop-blur-xl border border-white/40 dark:border-gray-700/50 p-1.5 rounded-[1.5rem] shadow-sm overflow-x-auto scrollbar-hide max-w-full">
                
                <!-- Today Feed -->
                <button wire:click="switchTab('today')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'today' ? 'text-indigo-700 dark:text-indigo-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'today')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'today' ? 'text-indigo-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    امروز من
                </button>

                <!-- Customers -->
                <button wire:click="switchTab('customers')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'customers' ? 'text-blue-700 dark:text-blue-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'customers')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'customers' ? 'text-blue-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    مشتریان
                </button>

                <!-- Calls -->
                <button wire:click="switchTab('calls')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'calls' ? 'text-amber-700 dark:text-amber-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'calls')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'calls' ? 'text-amber-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    مرکز تماس
                </button>

                <!-- Tasks -->
                <button wire:click="switchTab('tasks')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'tasks' ? 'text-rose-700 dark:text-rose-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'tasks')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-rose-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'tasks' ? 'text-rose-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    تسک‌ها
                    @if(($stats['pending_tasks'] ?? 0) > 0)
                        <span class="absolute top-1.5 left-2 w-2 h-2 bg-rose-500 rounded-full shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                    @endif
                </button>

                <!-- Campaigns -->
                <button wire:click="switchTab('campaign_leads')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'campaign_leads' ? 'text-emerald-700 dark:text-emerald-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'campaign_leads')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'campaign_leads' ? 'text-emerald-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    سرنخ‌های من
                </button>

                <!-- Goals -->
                <button wire:click="switchTab('goals')" 
                        class="relative px-5 py-2.5 rounded-[1.25rem] text-sm font-bold transition-all duration-300 flex items-center gap-2 whitespace-nowrap {{ $activeTab == 'goals' ? 'text-purple-700 dark:text-purple-300 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/50 dark:hover:bg-gray-700/30' }}">
                    @if($activeTab == 'goals')
                        <div class="absolute inset-0 bg-white dark:bg-gray-800 rounded-[1.25rem] shadow-[0_2px_8px_rgba(0,0,0,0.04)] -z-10 transition-all"></div>
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-purple-500 rounded-full"></div>
                    @endif
                    <svg class="w-4 h-4 {{ $activeTab == 'goals' ? 'text-purple-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    اهداف
                </button>
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
        
        <!-- Loading Indicator overlay -->
        <div wire:loading.delay class="absolute inset-0 bg-white/40 dark:bg-[#0F172A]/40 z-50 flex flex-col items-center justify-center backdrop-blur-sm transition-all duration-300 rounded-[2rem]">
            <div class="flex items-center gap-3 bg-white/90 dark:bg-gray-800/90 px-6 py-4 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 backdrop-blur-md">
                <div class="w-5 h-5 border-2 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">لطفاً صبر کنید...</span>
            </div>
        </div>

        <div class="h-full flex flex-col" dir="rtl">
            @if($activeTab === 'customers')
                @livewire('sales::customer-tab', ['selectedClientId' => $selectedClientId], key('cust-tab'))
            @elseif($activeTab === 'calls')
                @livewire('sales::call-center-tab', ['selectedClientId' => $selectedClientId], key('call-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'tasks')
                @livewire('sales::task-tab', ['selectedClientId' => $selectedClientId], key('task-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'today')
                @livewire('sales::today-tab', ['selectedClientId' => $selectedClientId], key('today-tab-'.($selectedClientId ?: 'none')))
            @elseif($activeTab === 'campaign_leads')
                @livewire('sales::campaign-leads-tab', [], key('camp-leads-tab'))
            @elseif($activeTab === 'goals')
                @livewire('sales::cockpit-goal-manager', [], key('goals-tab'))
            @endif
        </div>
    </main>

    <!-- ==========================================
         3. SLIDE-OVER DETAIL DRAWER (Left Side)
         ========================================== -->
    <!-- Backdrop Overlay -->
    <div x-show="isDrawerOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/40 dark:bg-black/60 z-30 backdrop-blur-sm" 
         @click="$wire.clearSelection()"></div>

    <!-- The Drawer Panel -->
    <div x-data="{ drawerTab: 'summary' }" 
         @client-changed.window="drawerTab = 'summary'"
         class="fixed inset-y-0 left-0 w-full sm:w-[420px] lg:w-[480px] bg-white dark:bg-gray-900 shadow-[-20px_0_40px_rgba(0,0,0,0.08)] dark:shadow-[-20px_0_40px_rgba(0,0,0,0.5)] z-40 border-r border-gray-200/50 dark:border-gray-800/80 transform transition-transform duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] flex flex-col"
         :class="isDrawerOpen ? 'translate-x-0' : '-translate-x-full'" dir="rtl">
        
        @if($selectedClient)
            <!-- Drawer Header (Sticky) -->
            <div class="flex-shrink-0 px-6 py-5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800/80 sticky top-0 z-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 border border-indigo-100/50 dark:border-indigo-900/50 flex items-center justify-center font-black text-xl text-indigo-600 dark:text-indigo-400 shadow-sm">
                        {{ mb_substr($selectedClient->full_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight">{{ $selectedClient->full_name }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">پرونده: <span class="tabular-nums font-bold">{{ $selectedClient->case_number ?: $selectedClient->id }}</span></span>
                            @if($selectedClient->status)
                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                <span class="text-[10px] font-bold flex items-center gap-1" style="color: {{ $selectedClient->status->color }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $selectedClient->status->color }}"></span>
                                    {{ $selectedClient->status->label }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Close Button -->
                <button wire:click="clearSelection" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
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
                            <div class="relative pr-12 pb-6 group">
                                <div class="absolute right-0 top-0 w-8 h-8 rounded-full bg-white dark:bg-gray-900 border-2 {{ $call->status === 'done' ? 'border-emerald-400 dark:border-emerald-500 text-emerald-500' : 'border-blue-400 dark:border-blue-500 text-blue-500' }} flex items-center justify-center z-10 shadow-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl p-4 border border-gray-100 dark:border-gray-800/80">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-extrabold text-xs text-gray-900 dark:text-white">
                                            تماس {{ $call->direction === 'inbound' ? 'ورودی' : 'خروجی' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 tabular-nums" dir="ltr">{{ $call->call_date->format('Y/m/d') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-2">{{ $call->reason }}</p>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $call->status === 'done' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' }} text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $call->status === 'done' ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                        {{ $call->result ?: ($call->status === 'done' ? 'مکالمه موفق' : 'در انتظار تماس') }}
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
                </section>
                
                <!-- Bottom padding space for scrolling -->
                <div class="h-8"></div>
            </div>
        @endif
    </div>

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
