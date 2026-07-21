<div class="smartbot-root font-sans" dir="rtl">
    <style>
        .smartbot-fade-in {
            animation: smartbotFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes smartbotFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .prose-ai p { margin-bottom: 0.85em; line-height: 1.8; }
        .prose-ai p:last-child { margin-bottom: 0; }
        .prose-ai strong { color: inherit; font-weight: 700; }

        /* انیمیشن پالس برای گوی هوش مصنوعی در حالت خالی */
        .ai-orb-glow {
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.65), rgba(139, 92, 246, 0.35), transparent 70%);
            animation: orbPulse 4s ease-in-out infinite alternate;
        }
        @keyframes orbPulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 1; }
        }
    </style>

    <!-- 1. STANDALONE PAGE VIEW (Gemini/ChatGPT Full Screen Style) -->
    @if($isStandalone && $isWidgetOpen)
        <!--
            نکته کلیدی: استفاده از min-h-screen و w-full بدون استایل باکس
            اسکرول صفحه روی خود مرورگر (window) اتفاق می‌افتد.
        -->
        <div
            class="w-full min-h-screen flex flex-col relative"
            x-data="{
                inputValue: @entangle('userMessage'),
                themeOpen: false,
                activeTheme: 'auto',
                inputHeight: 56,
                bottomHeight: 180,
                init() {
                    this.scrollToBottom();
                    window.addEventListener('chatScrollToBottom', () => this.scrollToBottom());
                    
                    let stored = localStorage.getItem('theme');
                    if (stored !== 'light' && stored !== 'dark' && stored !== 'auto') {
                        stored = 'auto';
                    }
                    this.activeTheme = stored;
                    this.applyTheme(stored);

                    // Setup ResizeObserver to track bottomBar height changes
                    if (this.$refs.bottomBar) {
                        const observer = new ResizeObserver(() => {
                            this.updateBottomHeight();
                        });
                        observer.observe(this.$refs.bottomBar);
                    }
                },
                applyTheme(theme) {
                    if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.classList.add('light');
                    }
                },
                updateBottomHeight() {
                    const el = this.$refs.bottomBar;
                    if (el) {
                        this.bottomHeight = el.offsetHeight;
                    }
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                    });
                },
                submitForm() {
                    const val = this.$refs.inputField.value.trim();
                    if (val && !this.$wire.isThinking) {
                        this.$wire.sendMessage();
                        if (this.$refs.inputField) {
                            this.$refs.inputField.style.height = '56px';
                            this.inputHeight = 56;
                        }
                    }
                }
            }"
        >
            <!-- هدر شیک و یکپارچه شیشه‌ای بالای صفحه -->
            <header class="fixed top-0 left-0 w-full bg-white/95 dark:bg-[#0a0a0d]/92 backdrop-blur-xl border-b border-zinc-200/70 dark:border-indigo-500/15 py-3 px-3 sm:px-4 md:px-6 flex items-center justify-between z-30 shadow-[0_2px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_2px_24px_rgba(99,102,241,0.08)] transition-all duration-300">
                <!-- نوار رنگی گرادینت زیر هدر -->
                <div class="absolute bottom-0 left-0 w-full h-[1.5px] bg-gradient-to-l from-transparent via-indigo-400/40 to-transparent dark:via-indigo-400/25 pointer-events-none"></div>
                <!-- سمت راست: لوگوی بات و دکمه بازگشت -->
                <div class="flex items-center gap-1.5 sm:gap-3">
                    <!-- دکمه بازگشت -->
                    <a href="{{ route('client.dashboard') }}" class="p-1.5 sm:p-2 rounded-full text-zinc-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/80 dark:hover:bg-indigo-500/10 transition-all" title="بازگشت">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    
                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700/60"></div>

                    <!-- هویت دستیار هوشمند -->
                    <div class="flex items-center gap-2">
                        <div class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/25 dark:shadow-indigo-500/30 ring-1 ring-indigo-400/20 dark:ring-indigo-400/30">
                            <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                            <!-- چراغ سبز آنلاین بودن -->
                            <span class="absolute -bottom-0.5 -left-0.5 w-2 w-2 sm:w-2.5 sm:h-2.5 bg-green-500 border-2 border-white dark:border-[#0a0a0d] rounded-full shadow-sm shadow-green-400/50"></span>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-[11px] sm:text-xs font-bold text-zinc-900 dark:text-zinc-50 leading-tight">{{ $botName }}</span>
                            <span class="text-[9px] sm:text-[10px] text-green-600 dark:text-green-400 font-semibold tracking-wide leading-none mt-0.5">
                                <span class="inline sm:hidden">آنلاین</span>
                                <span class="hidden sm:inline">آنلاین · پاسخگوی هوشمند</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- سمت چپ: پروفایل، تم سوئیتشر، گفتگوی جدید -->
                <div class="flex items-center gap-1.5 sm:gap-3">
                    <!-- انتخاب تم (سه حالته: لایت، دارک، اتوماتیک) -->
                    <div class="relative">
                        <button 
                            @click="themeOpen = !themeOpen" 
                            class="p-1.5 sm:p-2 rounded-xl text-zinc-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/80 dark:hover:bg-indigo-500/10 transition-all flex items-center justify-center"
                            title="تغییر تم"
                        >
                            <!-- آیکون لایت -->
                            <svg x-show="activeTheme === 'light'" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <!-- آیکون دارک -->
                            <svg x-show="activeTheme === 'dark'" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                            <!-- آیکون اتوماتیک -->
                            <svg x-show="activeTheme === 'auto'" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </button>

                        <!-- منوی بازشو تم -->
                        <div 
                            x-show="themeOpen" 
                            @click.outside="themeOpen = false" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 transform -translate-y-2"
                            class="absolute left-0 mt-2.5 w-40 rounded-2xl bg-white dark:bg-[#111115] backdrop-blur-xl border border-zinc-200/70 dark:border-indigo-500/20 shadow-[0_8px_32px_rgba(0,0,0,0.12)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.5),0_0_0_1px_rgba(99,102,241,0.08)] py-1.5 z-40 overflow-hidden"
                            style="display: none;"
                        >
                            <!-- گزینه خودکار -->
                            <button 
                                @click="
                                    activeTheme = 'auto'; 
                                    localStorage.removeItem('theme'); 
                                    applyTheme('auto');
                                    themeOpen = false;
                                " 
                                class="w-full text-right px-3.5 py-2.5 text-xs flex items-center justify-between transition-all duration-150 rounded-xl mx-auto group"
                                :class="activeTheme === 'auto' ? 'text-indigo-600 dark:text-indigo-300 font-bold bg-indigo-50 dark:bg-indigo-500/15' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100/80 dark:hover:bg-indigo-500/8 hover:text-indigo-600 dark:hover:text-indigo-300'"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                    خودکار
                                </span>
                                <span x-show="activeTheme === 'auto'" class="w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400 flex-shrink-0"></span>
                            </button>
                            <!-- گزینه روشن -->
                            <button 
                                @click="
                                    activeTheme = 'light'; 
                                    localStorage.setItem('theme', 'light'); 
                                    applyTheme('light');
                                    themeOpen = false;
                                " 
                                class="w-full text-right px-3.5 py-2.5 text-xs flex items-center justify-between transition-all duration-150 rounded-xl mx-auto group"
                                :class="activeTheme === 'light' ? 'text-indigo-600 dark:text-indigo-300 font-bold bg-indigo-50 dark:bg-indigo-500/15' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100/80 dark:hover:bg-indigo-500/8 hover:text-indigo-600 dark:hover:text-indigo-300'"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M2 12h2"/><path d="M20 12h2"/></svg>
                                    روشن
                                </span>
                                <span x-show="activeTheme === 'light'" class="w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400 flex-shrink-0"></span>
                            </button>
                            <!-- گزینه تاریک -->
                            <button 
                                @click="
                                    activeTheme = 'dark'; 
                                    localStorage.setItem('theme', 'dark'); 
                                    applyTheme('dark');
                                    themeOpen = false;
                                " 
                                class="w-full text-right px-3.5 py-2.5 text-xs flex items-center justify-between transition-all duration-150 rounded-xl mx-auto group"
                                :class="activeTheme === 'dark' ? 'text-indigo-600 dark:text-indigo-300 font-bold bg-indigo-50 dark:bg-indigo-500/15' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100/80 dark:hover:bg-indigo-500/8 hover:text-indigo-600 dark:hover:text-indigo-300'"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                                    تاریک
                                </span>
                                <span x-show="activeTheme === 'dark'" class="w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400 flex-shrink-0"></span>
                            </button>
                        </div>
                    </div>

                    <!-- سبد خرید -->
                    @php
                        $isMarketModuleActive = false;
                        try {
                            if (class_exists(\App\Models\Module::class)) {
                                $isMarketModuleActive = \App\Models\Module::where('slug', 'market')
                                    ->where('installed', true)
                                    ->where('active', true)
                                    ->exists();
                            }
                        } catch (\Throwable $e) {}
                    @endphp

                    @if($isMarketModuleActive)
                        <button 
                            x-on:click="$dispatch('showCartPopup')"
                            class="relative p-1.5 sm:p-2 rounded-xl text-zinc-550 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-150 dark:hover:bg-zinc-800/80 transition-all flex items-center justify-center cursor-pointer"
                            title="سبد خرید"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"/>
                                <circle cx="19" cy="21" r="1"/>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                            </svg>
                            @if($cartItemCount > 0)
                                <span class="absolute -top-1 -left-1 bg-rose-500 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full flex items-center justify-center min-w-[16px] h-[16px] animate-pulse">
                                    {{ $cartItemCount }}
                                </span>
                            @endif
                        </button>

                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700/60"></div>
                    @endif

                    <!-- شروع مجدد گفتگو -->
                    <button 
                        wire:click="resetSession" 
                        class="flex items-center gap-1 px-2 py-1.5 sm:px-3 sm:py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-[11px] sm:text-xs font-bold text-indigo-600 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-400/25 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white hover:border-indigo-600 dark:hover:border-indigo-500 transition-all duration-200 shadow-sm dark:shadow-indigo-500/10"
                        title="شروع مجدد گفتگو"
                        wire:loading.attr="disabled"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin-hover"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                        <span class="hidden md:inline">گفتگوی جدید</span>
                    </button>

                    <!-- جداکننده -->
                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700/60"></div>

                    <!-- پروفایل کاربر -->
                    <div class="flex items-center gap-2 select-none">
                        @auth('client')
                            @php
                                $clientName = auth('client')->user()->full_name;
                                $initial = mb_substr($clientName, 0, 1, 'utf-8');
                            @endphp
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-[11px] sm:text-xs font-bold flex items-center justify-center shadow-md shadow-indigo-500/25 dark:shadow-indigo-500/20 ring-2 ring-white dark:ring-[#0a0a0d]">
                                {{ $initial }}
                            </div>
                            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-200 hidden md:inline">{{ $clientName }}</span>
                        @else
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-550 dark:text-zinc-400 text-xs font-bold flex items-center justify-center border border-zinc-200/60 dark:border-zinc-700/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 hidden md:inline">مهمان</span>
                        @endauth
                    </div>
                </div>
            </header>

            <div 
                class="flex-1 w-full max-w-3xl mx-auto px-4 md:px-6 pt-24 z-10 flex flex-col gap-8"
                :style="'padding-bottom: ' + bottomHeight + 'px'"
            >

                <!-- CASE A: ONLY WELCOME STATE (SayHalo Inspiration) -->
                @if(count($messages) <= 1)
                    <div class="flex-1 flex flex-col items-center justify-center min-h-[50vh] smartbot-fade-in">

                        <!-- AI Orb / Logo -->
                        <div class="relative w-24 h-24 flex items-center justify-center mb-8">
                            <div class="absolute inset-0 ai-orb-glow rounded-full"></div>
                            <div class="relative w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl shadow-xl flex items-center justify-center rotate-3 hover:rotate-6 transition-transform duration-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                            </div>
                        </div>

                        <!-- Welcome Text -->
                        <div class="text-center space-y-3 mb-12">
                            <h2 class="text-xl md:text-2xl font-medium text-zinc-500 dark:text-zinc-300 tracking-tight">
                                سلام {{ auth('client')->check() ? auth('client')->user()->full_name : 'کاربر گرامی' }}،
                            </h2>
                            <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-zinc-900 dark:text-white">
                                امروز چطور می‌توانم راهنماییتان کنم؟
                            </h1>
                        </div>

                        <!-- Suggestions Cards (Horizontal scroll or Grid) -->
                        @if(!empty($suggestions))
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                                @foreach(array_slice($suggestions, 0, 3) as $index => $sug)
                                    @php
                                        $icons = [
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 16-4-4-4 4"/><path d="M17 21v-9"/><path d="m3 8 4-4 4 4"/><path d="M7 3v9"/></svg>',
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
                                        ];
                                        $iconColors = [
                                            'from-indigo-500 to-violet-500',
                                            'from-sky-500 to-indigo-500',
                                            'from-purple-500 to-pink-500',
                                        ];
                                    @endphp
                                    <button
                                        wire:click="sendMessage('{{ addslashes($sug) }}')"
                                        class="group text-right p-5 rounded-3xl bg-white dark:bg-zinc-900/85 border border-zinc-200/80 dark:border-zinc-800/70 hover:bg-zinc-50/80 dark:hover:bg-zinc-800/80 hover:border-indigo-400/50 dark:hover:border-indigo-500/40 shadow-[0_4px_24px_rgba(0,0,0,0.06)] dark:shadow-[0_4px_24px_rgba(0,0,0,0.3)] hover:shadow-[0_8px_32px_rgba(99,102,241,0.10)] dark:hover:shadow-[0_8px_32px_rgba(99,102,241,0.18)] hover:-translate-y-0.5 transition-all duration-300 cursor-pointer"
                                    >
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br {{ $iconColors[$index % count($iconColors)] }} flex items-center justify-center text-white mb-4 shadow-md group-hover:scale-110 transition-transform duration-300">
                                            {!! $icons[$index % count($icons)] !!}
                                        </div>
                                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors duration-200">{{ $sug }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- CASE B: SCROLLABLE CHAT MESSAGE HISTORY -->
                @else
                    @foreach($messages as $msg)
                        @if($msg['role'] === 'user')
                            <!-- سوال کاربر: ترازبندی در راست (RTL) با استایل شیشه‌ای ملایم ایندیگو -->
                            <div class="flex justify-start w-full smartbot-fade-in group">
                                <div class="bg-indigo-500/5 dark:bg-indigo-500/10 backdrop-blur-md border border-indigo-200/50 dark:border-indigo-500/35 text-indigo-900 dark:text-indigo-200 px-6 py-3.5 rounded-3xl text-[15px] leading-relaxed max-w-[85%] md:max-w-[75%] font-semibold shadow-[0_8px_30px_rgba(99,102,241,0.03)] dark:shadow-[0_8px_30px_rgba(99,102,241,0.1)]">
                                    {!! nl2br(e($msg['content'])) !!}
                                </div>
                            </div>
                        @else
                            <!-- پاسخ دستیار: ترازبندی در راست (RTL)، بدون حباب، ظاهر مستند‌گونه شبیه ChatGPT -->
                            <div class="flex justify-start w-full gap-4 md:gap-6 smartbot-fade-in">
                                <!-- آواتار هوش مصنوعی -->
                                <div class="flex-shrink-0 mt-1 w-8 h-8 md:w-9 md:h-9 rounded-full border border-indigo-100 dark:border-indigo-500/30 bg-gradient-to-tr from-indigo-50 dark:from-indigo-950/50 to-purple-50 dark:to-purple-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                </div>

                                <!-- متن پیام و محصولات -->
                                <div class="flex-1 min-w-0 pt-1.5 pb-2">
                                    <div class="text-[15px] leading-[1.8] text-zinc-800 dark:text-zinc-200 prose-ai">
                                        {!! nl2br(e($msg['content'])) !!}
                                    </div>

                                    <!-- لیست محصولات پیشنهادی -->
                                    @if(!empty($msg['products']))
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 max-w-3xl">
                                            @foreach($msg['products'] as $product)
                                                <div class="flex bg-white dark:bg-zinc-900/90 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800/75 rounded-2xl overflow-hidden hover:bg-zinc-50 dark:hover:bg-zinc-800/90 shadow-[0_8px_30px_rgba(99,102,241,0.02)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.25)] hover:shadow-[0_16px_40px_rgba(99,102,241,0.06)] dark:hover:shadow-[0_16px_40px_rgba(0,0,0,0.35)] hover:-translate-y-1 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-300 p-3.5 gap-4 items-center">
                                                     <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" class="block flex-shrink-0 group relative overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 p-2 w-20 h-20 md:w-24 md:h-24 flex items-center justify-center">
                                                         @if(!empty($product['image']))
                                                             <img src="{{ $product['image'] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" alt="{{ $product['title'] }}" />
                                                         @else
                                                             <div class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                                                                 <svg class="w-8 h-8 opacity-30 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                 </svg>
                                                             </div>
                                                         @endif
                                                     </a>
                                                     <div class="flex-1 flex flex-col justify-between min-w-0 py-0.5">
                                                         <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" class="block group">
                                                             <h4 class="text-xs md:text-sm font-bold text-zinc-800 dark:text-zinc-100 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-relaxed min-h-[2.5rem]">{{ $product['title'] }}</h4>
                                                         </a>
                                                         @if(!empty($product['variant_name']) || !empty($product['has_variations']))
                                                             <div class="flex flex-wrap gap-1.5 mt-1.5 min-h-[1.5rem]">
                                                                 @if(!empty($product['variant_name']))
                                                                     <span class="inline-flex items-center px-2 py-1 rounded-lg bg-zinc-100/90 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[11px] font-bold border border-zinc-200/50 dark:border-zinc-700/50 leading-none">
                                                                         {{ $product['variant_name'] }}
                                                                     </span>
                                                                 @endif
                                                                 @if(!empty($product['has_variations']))
                                                                     <span class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 text-[11px] font-bold border border-indigo-100/60 dark:border-indigo-900/30 leading-none">
                                                                         دارای تنوع
                                                                     </span>
                                                                 @endif
                                                             </div>
                                                         @else
                                                             <div class="mt-1.5 min-h-[1.5rem]"></div>
                                                         @endif

                                                         <div class="mt-3">
                                                             @if($product['discount_percent'] > 0)
                                                                 <div class="flex items-center gap-1.5 mb-0.5">
                                                                     <span class="text-[10px] text-zinc-400 dark:text-zinc-550 line-through">
                                                                         {{ $product['formatted_original_price'] }}
                                                                     </span>
                                                                     <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-500 text-white leading-none">
                                                                         {{ $product['discount_percent'] }}%
                                                                     </span>
                                                                 </div>
                                                                 <span class="text-xs md:text-sm font-extrabold text-rose-500 dark:text-rose-450 truncate">
                                                                     {{ $product['formatted_price'] }}
                                                                 </span>
                                                             @else
                                                                 <span class="text-xs md:text-sm font-extrabold text-zinc-800 dark:text-zinc-200 truncate">
                                                                     {{ $product['formatted_price'] }}
                                                                 </span>
                                                             @endif
                                                         </div>

                                                         <div class="mt-3 flex justify-end">
                                                             @if(!empty($product['has_variations']))
                                                                 <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" 
                                                                    class="inline-flex items-center justify-center gap-1.5 px-4 h-10 text-xs font-bold text-white rounded-xl whitespace-nowrap transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10 active:scale-95 hover:brightness-95" 
                                                                    style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px -2px {{ $primaryColor }}40;">
                                                                     <span>مشاهده و خرید</span>
                                                                     <svg class="w-3.5 h-3.5 transform -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                         <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                                     </svg>
                                                                 </a>
                                                             @elseif($product['has_stock'] && $product['variant_id'] && $product['vendor_product_id'])
                                                                 <div class="w-full max-w-[120px]">
                                                                     @livewire('market::web.add-to-cart-button', [
                                                                  'variantId' => $product['variant_id'],
                                                                  'vendorProductId' => $product['vendor_product_id'],
                                                                  't' => [
                                                                      'style' => 'background-color: ' . $primaryColor . ';',
                                                                      'shadow_color' => $primaryColor,
                                                                  ]
                                                              ], key('cart-btn-' . $product['id'] . '-' . $loop->index . '-' . ($msg['id'] ?? 0)))
                                                          </div>
                                                      @else
                                                          <span class="inline-flex items-center justify-center px-4 h-10 text-xs font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl whitespace-nowrap">ناموجود</span>
                                                      @endif
                                                      </div>
                                                  </div>
                                              </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- انیمیشن در حال تایپ -->
                    @if($isThinking)
                        <div class="flex justify-start w-full gap-4 md:gap-6 smartbot-fade-in" wire:key="thinking" x-init="$wire.processMessage()">
                            <div class="flex-shrink-0 mt-1 w-8 h-8 md:w-9 md:h-9 rounded-full border border-zinc-200 dark:border-zinc-805 bg-white dark:bg-[#151515] flex items-center justify-center text-zinc-400 shadow-sm">
                                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0 pt-3">
                                <span class="text-sm text-zinc-500 dark:text-zinc-450 font-medium">هوش مصنوعی در حال پردازش است...</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- نوار شناور و ثابت پایین صفحه برای دریافت متن -->
            <div x-ref="bottomBar" class="fixed bottom-0 left-0 w-full z-20 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-[#0a0a0d] dark:via-[#0a0a0d]/97 dark:to-transparent pt-12 pb-6">
                <div class="max-w-3xl mx-auto w-full px-4 flex flex-col gap-3">

                    <!-- پیشنهادات حین چت -->
                    @if(!empty($suggestions) && count($messages) > 1)
                        @if($allowCustomTyping)
                            <!-- اسکرولر افقی جمع و جور در صورت فعال بودن تایپ متنی -->
                            <div class="flex overflow-x-auto smartbot-scrollbar pb-1 gap-2 justify-start px-2">
                                @foreach($suggestions as $sug)
                                    <button
                                        wire:click="sendMessage('{{ addslashes($sug) }}')"
                                        class="whitespace-nowrap flex-shrink-0 px-4 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-900/90 border border-zinc-200/80 dark:border-zinc-700/60 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-500/15 hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all duration-200 shadow-sm"
                                    >
                                        {{ $sug }}
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <!-- پنل ویژه و شیک سوالات آماده در صورت غیرفعال بودن تایپ متنی -->
                            <div class="w-full bg-white dark:bg-zinc-900/90 border border-zinc-200/80 dark:border-zinc-800/70 rounded-[28px] p-5 shadow-[0_4px_24px_rgba(0,0,0,0.08)] dark:shadow-[0_4px_24px_rgba(0,0,0,0.4)] flex flex-col gap-4 smartbot-fade-in">
                                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                    <div class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-200">موضوعات پیشنهادی برای ادامه گفتگو</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($suggestions as $sug)
                                        <button
                                            wire:click="sendMessage('{{ addslashes($sug) }}')"
                                            class="text-right px-4 py-3 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-50/80 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 border border-zinc-200/60 dark:border-zinc-700/60 hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:text-indigo-600 dark:hover:text-indigo-300 rounded-2xl transition-all duration-200 flex items-center justify-between group"
                                        >
                                            <span class="truncate ml-2">{{ $sug }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex-shrink-0 rtl:rotate-180 text-indigo-500"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- فیلد ورودی متن مدرن و یکپارچه با تم -->
                    @if($allowCustomTyping)
                        <div 
                            class="relative bg-white dark:bg-zinc-900/90 border border-zinc-200/80 dark:border-zinc-700/60 rounded-[24px] p-2 flex shadow-[0_4px_20px_rgba(0,0,0,0.08)] dark:shadow-[0_4px_20px_rgba(0,0,0,0.35)] focus-within:ring-2 focus-within:ring-indigo-500/30 dark:focus-within:ring-indigo-400/25 focus-within:border-indigo-400/60 dark:focus-within:border-indigo-500/50 focus-within:shadow-[0_4px_24px_rgba(99,102,241,0.12)] dark:focus-within:shadow-[0_4px_24px_rgba(99,102,241,0.15)] transition-all duration-300"
                            :class="inputHeight > 56 ? 'items-end' : 'items-center'"
                        >

                            <!-- دکمه گیره (اختیاری) -->
                            <button type="button" class="p-2.5 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            </button>

                            <textarea
                                x-model="inputValue"
                                x-ref="inputField"
                                placeholder="از دستیار هوشمند بپرسید..."
                                class="flex-1 bg-transparent border-none focus:ring-0 text-zinc-900 dark:text-zinc-50 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 resize-none py-3 px-2 text-[15px] leading-relaxed outline-none"
                                style="height: 56px;"
                                x-on:keydown.enter.prevent="if (!e.shiftKey) { submitForm(); }"
                                @input="
                                    $refs.inputField.style.height = '56px'; 
                                    let newH = Math.min($refs.inputField.scrollHeight, 200);
                                    $refs.inputField.style.height = newH + 'px';
                                    inputHeight = newH;
                                "
                                :disabled="$wire.isThinking"
                            ></textarea>

                            <!-- دکمه ارسال -->
                            <button
                                @click="submitForm()"
                                class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center transition-all duration-200 ml-1"
                                :class="inputValue ? 'bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white shadow-md shadow-indigo-500/30 hover:shadow-indigo-500/40 hover:scale-105 active:scale-95' : 'bg-zinc-100/80 dark:bg-zinc-800/60 text-zinc-400 dark:text-zinc-600'"
                                :disabled="$wire.isThinking || !inputValue"
                            >
                                <template x-if="$wire.isThinking">
                                    <svg class="animate-spin w-4 h-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!$wire.isThinking">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </template>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- 2. STANDARD COMPACT FLOATING CHAT PANEL -->
    @if(!$isStandalone && $isWidgetOpen)
        <div
            class="fixed bottom-24 left-6 w-[380px] h-[600px] z-50 bg-white dark:bg-[#0c0c0e] border border-zinc-200 dark:border-zinc-800/80 rounded-3xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300"
            x-data="{
                inputValue: @entangle('userMessage'),
                init() {
                    this.scrollToBottom();
                    window.addEventListener('chatScrollToBottom', () => this.scrollToBottom());
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.chatBody;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
                submitForm() {
                    const val = this.$refs.inputField.value.trim();
                    if (val && !this.$wire.isThinking) {
                        this.$wire.sendMessage();
                    }
                }
            }"
            x-init="init()"
        >
            <!-- Header -->
            <div class="px-5 py-4 border-b border-zinc-150 dark:border-zinc-800/80 flex items-center justify-between bg-white/90 dark:bg-[#0c0c0e]/90 backdrop-blur-md z-20 absolute top-0 w-full">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100 dark:border-indigo-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white tracking-tight">{{ $botName }}</h3>
                        <p class="text-[10px] text-zinc-500">پاسخگوی هوشمند</p>
                    </div>
                </div>

                <button wire:click="toggleWidget" class="p-1.5 rounded-full text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <!-- Messages List -->
            <div class="flex-grow overflow-y-auto pt-20 p-4 space-y-6 bg-zinc-50/50 dark:bg-[#0f0f12]" x-ref="chatBody">
                @if(count($messages) <= 1)
                    <div class="text-center py-10">
                        <div class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-900 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 20.94c1.5 0 2.75 1.06 4 1.06 3 0 6-8 6-12.22A4.91 4.91 0 0 0 17 5c-2.22 0-4 1.44-5 2-1-.56-2.78-2-5-2a4.9 4.9 0 0 0-5 4.78C2 14 5 22 8 22c1.25 0 2.5-1.06 4-1.06Z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200 mb-1">سلام!</h4>
                        <p class="text-xs text-zinc-500">چگونه می‌توانم راهنماییتان کنم؟</p>
                    </div>
                @endif

                @foreach($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }} w-full smartbot-fade-in">
                        @if($msg['role'] === 'user')
                            <div class="max-w-[85%] bg-indigo-600 text-white px-4 py-2.5 rounded-2xl rounded-tr-sm text-[13px] leading-relaxed shadow-sm">
                                {!! nl2br(e($msg['content'])) !!}
                            </div>
                        @else
                            <div class="flex gap-2 w-full">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-indigo-600 mt-1">
                                    <svg xmlns="http://www.w3.org/2050/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                </div>
                                <div class="flex-1 text-[13px] text-zinc-800 dark:text-zinc-200 leading-relaxed pt-1">
                                    {!! nl2br(e($msg['content'])) !!}

                                    <!-- Products in Widget -->
                                    @if(!empty($msg['products']))
                                        <div class="grid grid-cols-1 gap-2.5 mt-3">
                                            @foreach($msg['products'] as $product)
                                                <div class="flex p-2.5 bg-white dark:bg-zinc-900/90 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800/75 rounded-2xl overflow-hidden hover:bg-zinc-50 dark:hover:bg-zinc-800/90 shadow-[0_6px_20px_rgba(99,102,241,0.02)] dark:shadow-[0_6px_20px_rgba(0,0,0,0.2)] hover:shadow-[0_12px_30px_rgba(99,102,241,0.05)] dark:hover:shadow-[0_12px_30px_rgba(0,0,0,0.28)] hover:-translate-y-1 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-300 items-center gap-3">
                                                    <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" class="block flex-shrink-0 group overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 p-1.5 w-14 h-14 flex items-center justify-center">
                                                        @if(!empty($product['image']))
                                                            <img src="{{ $product['image'] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" alt="" />
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                                                                <svg class="w-6 h-6 opacity-30 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </a>
                                                    <div class="flex-1 min-w-0 pr-1 py-0.5 flex flex-col justify-between h-full">
                                                        <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" class="block group">
                                                            <h4 class="text-xs font-bold truncate text-zinc-850 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-normal">{{ $product['title'] }}</h4>
                                                        </a>
                                                        @if(!empty($product['variant_name']) || !empty($product['has_variations']))
                                                            <div class="flex flex-wrap gap-1 mt-1">
                                                                @if(!empty($product['variant_name']))
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-zinc-100/90 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px] font-bold border border-zinc-200/50 dark:border-zinc-700/50 leading-none">
                                                                        {{ $product['variant_name'] }}
                                                                    </span>
                                                                @endif
                                                                @if(!empty($product['has_variations']))
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 text-[10px] font-bold border border-indigo-100/60 dark:border-indigo-900/30 leading-none">
                                                                        دارای تنوع
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <div class="mt-2">
                                                            @if($product['discount_percent'] > 0)
                                                                <div class="flex items-center gap-1.5 mb-0.5">
                                                                    <span class="text-[8px] text-zinc-400 line-through">
                                                                        {{ $product['formatted_original_price'] }}
                                                                    </span>
                                                                    <span class="inline-flex items-center px-0.5 py-0.5 rounded text-[7px] font-black bg-rose-500 text-white leading-none">
                                                                        {{ $product['discount_percent'] }}%
                                                                    </span>
                                                                </div>
                                                                <span class="text-[11px] font-extrabold text-rose-500 dark:text-rose-455 truncate">
                                                                    {{ $product['formatted_price'] }}
                                                                </span>
                                                            @else
                                                                <span class="text-[11px] font-extrabold text-zinc-800 dark:text-zinc-200 truncate">
                                                                    {{ $product['formatted_price'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2.5 flex justify-end">
                                                            @if(!empty($product['has_variations']))
                                                                <a href="{{ $product['slug'] ? route('market.public.product.show', array_filter(['slug' => $product['slug'], 'variant' => $product['variant_id'] ?? null])) : '#' }}" 
                                                                   class="inline-flex items-center justify-center gap-1 px-3 h-8.5 text-[10px] font-bold text-white rounded-lg whitespace-nowrap transition-all duration-300 hover:shadow-lg active:scale-95" 
                                                                   style="background-color: {{ $primaryColor }}; box-shadow: 0 2px 8px -2px {{ $primaryColor }}30;">
                                                                    <span>مشاهده</span>
                                                                    <svg class="w-3.5 h-3.5 transform -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                                    </svg>
                                                                </a>
                                                            @elseif($product['has_stock'] && $product['variant_id'] && $product['vendor_product_id'])
                                                                <div class="w-full max-w-[100px]">
                                                                    @livewire('market::web.add-to-cart-button', [
                                                                        'variantId' => $product['variant_id'],
                                                                        'vendorProductId' => $product['vendor_product_id'],
                                                                        't' => [
                                                                            'style' => 'background-color: ' . $primaryColor . ';',
                                                                            'shadow_color' => $primaryColor,
                                                                        ]
                                                                    ], key('cart-btn-widget-' . $product['id'] . '-' . $loop->index . '-' . ($msg['id'] ?? 0)))
                                                                </div>
                                                            @else
                                                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl whitespace-nowrap">ناموجود</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Compact Input -->
            <div class="p-3 bg-white dark:bg-[#0c0c0e] border-t border-zinc-150 dark:border-zinc-800/80">
                @if($allowCustomTyping)
                    <div class="relative bg-zinc-100 dark:bg-zinc-900 rounded-2xl p-1 flex items-end">
                        <textarea
                            x-model="inputValue"
                            x-ref="inputField"
                            placeholder="پیام شما..."
                            class="flex-1 bg-transparent border-none focus:ring-0 text-zinc-800 dark:text-zinc-100 resize-none py-2.5 px-3 text-[13px] outline-none"
                            style="height: 40px;"
                            x-on:keydown.enter.prevent="if (!e.shiftKey) { submitForm(); }"
                        ></textarea>

                        <button
                            @click="submitForm()"
                            class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 transition-all"
                            :class="inputValue ? 'bg-indigo-600 text-white' : 'text-zinc-400'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="rtl:rotate-180"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                @else
                    <div class="py-2.5 text-center text-xs text-zinc-450 dark:text-zinc-550 select-none font-medium">
                        💬 قابلیت ارسال پیام غیرفعال است.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- 3. FLOATING ACTION BUTTON (Widget closed) -->
    @if(!$isStandalone && !$isWidgetOpen)
        <button
            wire:click="toggleWidget"
            class="fixed bottom-6 left-6 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-white hover:scale-105 active:scale-95 transition-transform duration-300 z-50 group"
            style="background: {{ $primaryColor ?? '#4f46e5' }}; box-shadow: 0 10px 25px -5px {{ $primaryColor ?? '#4f46e5' }}80;"
        >
            <svg class="w-6 h-6 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
        </button>
    @endif
</div>
