<div class="smartbot-root font-sans" dir="rtl">
    <style wire:ignore>
        :root {
            --bot-primary: {{ $primaryColor }};
            --bot-primary-rgb: {{ $primaryColorRgb }};
        }

        /* Support custom inline SVG color adaptation */
        svg.custom-bot-icon {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
        svg.custom-bot-icon path,
        svg.custom-bot-icon rect,
        svg.custom-bot-icon circle,
        svg.custom-bot-icon ellipse,
        svg.custom-bot-icon polygon,
        svg.custom-bot-icon polyline {
            fill: currentColor !important;
        }
        svg.custom-bot-icon[stroke] path {
            stroke: currentColor !important;
        }

        /* Overwrite hardcoded indigo tailwind classes to match custom primaryColor */
        .bg-indigo-500, .bg-indigo-600 {
            background-color: var(--bot-primary) !important;
        }
        .text-indigo-500, .text-indigo-600, .text-indigo-650 {
            color: var(--bot-primary) !important;
        }
        .border-indigo-500, .border-indigo-600 {
            border-color: var(--bot-primary) !important;
        }
        .bg-indigo-50 {
            background-color: rgba(var(--bot-primary-rgb), 0.08) !important;
        }
        .border-indigo-200\/70 {
            border-color: rgba(var(--bot-primary-rgb), 0.3) !important;
        }
        .hover\:bg-indigo-50\/80:hover {
            background-color: rgba(var(--bot-primary-rgb), 0.08) !important;
        }
        .hover\:bg-indigo-600:hover {
            background-color: var(--bot-primary) !important;
            border-color: var(--bot-primary) !important;
        }
        .hover\:text-indigo-600:hover {
            color: var(--bot-primary) !important;
        }
        .hover\:border-indigo-600:hover {
            border-color: var(--bot-primary) !important;
        }
        
        /* Dark mode overrides */
        .dark .dark\:bg-indigo-500\/15 {
            background-color: rgba(var(--bot-primary-rgb), 0.15) !important;
        }
        .dark .dark\:text-indigo-300 {
            color: rgba(var(--bot-primary-rgb), 0.9) !important;
        }
        .dark .dark\:border-indigo-500\/15 {
            border-color: rgba(var(--bot-primary-rgb), 0.15) !important;
        }
        .dark .dark\:border-indigo-500\/20 {
            border-color: rgba(var(--bot-primary-rgb), 0.20) !important;
        }
        .dark .dark\:border-indigo-400\/25 {
            border-color: rgba(var(--bot-primary-rgb), 0.25) !important;
        }
        .dark .dark\:hover\:bg-indigo-500\/10:hover {
            background-color: rgba(var(--bot-primary-rgb), 0.1) !important;
        }
        .dark .dark\:hover\:bg-indigo-500\/8:hover {
            background-color: rgba(var(--bot-primary-rgb), 0.08) !important;
        }
        .dark .dark\:hover\:text-indigo-300:hover {
            color: rgba(var(--bot-primary-rgb), 0.9) !important;
        }
        .dark .dark\:hover\:bg-indigo-500:hover {
            background-color: var(--bot-primary) !important;
        }
        
        /* Interactive States and Gradients */
        .focus-within\:ring-indigo-500\/30:focus-within {
            --tw-ring-color: rgba(var(--bot-primary-rgb), 0.3) !important;
        }
        .focus-within\:border-indigo-500:focus-within {
            border-color: var(--bot-primary) !important;
        }
        .shadow-indigo-500\/25 {
            --tw-shadow-color: rgba(var(--bot-primary-rgb), 0.25) !important;
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow) !important;
        }
        .shadow-indigo-500\/30 {
            --tw-shadow-color: rgba(var(--bot-primary-rgb), 0.3) !important;
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow) !important;
        }
        .shadow-indigo-500\/10 {
            --tw-shadow-color: rgba(var(--bot-primary-rgb), 0.1) !important;
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow) !important;
        }
        .ring-indigo-400\/20 {
            --tw-ring-color: rgba(var(--bot-primary-rgb), 0.2) !important;
        }
        .ring-indigo-400\/30 {
            --tw-ring-color: rgba(var(--bot-primary-rgb), 0.3) !important;
        }
        
        .telegram-flash {
            animation: telegramFlash 1.5s ease-out !important;
            border-radius: 12px !important;
        }
        @keyframes telegramFlash {
            0% { background-color: rgba(var(--bot-primary-rgb), 0.28) !important; }
            100% { background-color: transparent !important; }
        }
        }
        
        .bg-gradient-to-tr.from-indigo-600 {
            --tw-gradient-from: var(--bot-primary) !important;
            --tw-gradient-to: rgba(var(--bot-primary-rgb), 0.5) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
        .bg-gradient-to-br.from-indigo-500 {
            --tw-gradient-from: var(--bot-primary) !important;
            --tw-gradient-to: rgba(var(--bot-primary-rgb), 0.5) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
        .via-indigo-400\/40 {
            --tw-gradient-stops: var(--tw-gradient-from), rgba(var(--bot-primary-rgb), 0.4), var(--tw-gradient-to) !important;
        }
        .dark .dark\:via-indigo-400\/25 {
            --tw-gradient-stops: var(--tw-gradient-from), rgba(var(--bot-primary-rgb), 0.25), var(--tw-gradient-to) !important;
        }

        .smartbot-fade-in {
            animation: smartbotFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes smartbotFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .smartbot-ai-msg {
            scroll-margin-top: 5.5rem;
        }
        .smartbot-cmp-ai-msg {
            scroll-margin-top: 4.5rem;
        }

        .prose-ai p { margin-bottom: 0.85em; line-height: 1.8; }
        .prose-ai p:last-child { margin-bottom: 0; }
        .prose-ai strong { color: inherit; font-weight: 700; }

        /* انیمیشن پالس برای گوی هوش مصنوعی در حالت خالی */
        .ai-orb-glow {
            background: radial-gradient(circle at 50% 50%, rgba(var(--bot-primary-rgb), 0.65), rgba(var(--bot-primary-rgb), 0.25), transparent 70%);
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
                inputValue: $wire.entangle('userMessage'),
                themeOpen: false,
                activeTheme: 'auto',
                inputHeight: 56,
                bottomHeight: 180,
                init() {
                    this.scrollToLatestResponse();
                    window.addEventListener('chatScrollToBottom', () => this.scrollToLatestResponse());
                    
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
                scrollToLatestResponse() {
                    this.$nextTick(() => {
                        const botMessages = document.querySelectorAll('.smartbot-ai-msg');
                        if (botMessages.length > 0) {
                            const latestMsg = botMessages[botMessages.length - 1];
                            latestMsg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                        }
                    });
                },
                submitForm() {
                    const val = this.$refs.inputField.value.trim();
                    if (val && !this.$wire.isThinking) {
                        this.$wire.sendMessage();
                        this.scrollToBottom();
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
                        <div class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/25 dark:shadow-indigo-500/30 ring-1 ring-indigo-400/20 dark:ring-indigo-400/30 overflow-hidden">
                            @if($botIconSvg)
                                {!! $botIconSvg !!}
                            @elseif($botIcon)
                                <img src="{{ $botIcon }}" class="w-full h-full object-cover" alt="{{ $botName }}" />
                            @else
                                <svg class="w-4 h-4 sm:w-[17px] sm:h-[17px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                            @endif
                            <!-- چراغ سبز آنلاین بودن -->
                            <span class="absolute -bottom-0.5 -left-0.5 w-2 w-2 sm:w-2.5 sm:h-2.5 bg-green-500 border-2 border-white dark:border-[#0a0a0d] rounded-full shadow-sm shadow-green-400/50 z-10"></span>
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

            @if($showAuthPanel)
                <div 
                    class="flex-1 w-full max-w-3xl mx-auto px-4 md:px-6 pt-24 z-10 flex flex-col gap-8"
                    style="padding-bottom: 40px"
                    wire:key="sa-auth-container"
                >
                    <!-- STANDALONE AUTH PANEL -->
                    <div class="flex-1 flex flex-col items-center justify-center min-h-[50vh] smartbot-fade-in py-12">
                        <div class="w-full max-w-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-xl">
                            <!-- Auth Header -->
                            <div class="text-center mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 mx-auto mb-3 border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                                    @if($botIconSvg)
                                        {!! $botIconSvg !!}
                                    @elseif($botIcon)
                                        <img src="{{ $botIcon }}" class="w-full h-full object-cover rounded-2xl" alt="{{ $botName }}" />
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">ورود / ثبت‌نام در {{ $botName }}</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">جهت استفاده از دستیار، ابتدا وارد شوید.</p>
                            </div>

                            @if($authError)
                                <div class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs font-semibold text-red-600 dark:text-red-400 leading-relaxed text-right">
                                    {{ $authError }}
                                </div>
                            @endif

                            @if($authSuccessMsg)
                                <div class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-600 dark:text-emerald-400 leading-relaxed text-right">
                                    {{ $authSuccessMsg }}
                                </div>
                            @endif

                            <!-- Dynamic Auth Step Container -->
                            <div wire:key="auth-sa-step-container-{{ $authStep }}">
                                <!-- Step 1: Identifier -->
                                @if($authStep === 'identifier')
                                    <form wire:submit.prevent="checkIdentifier" class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">{{ $usernameLabel }}</label>
                                            <input
                                                type="text"
                                                wire:model="authUsername"
                                                placeholder="ورود {{ $usernameLabel }}..."
                                                class="w-full px-4 py-3 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-right"
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            class="w-full py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                        >
                                            ادامه
                                        </button>
                                    </form>

                                <!-- Step 2: Password -->
                                @elseif($authStep === 'password')
                                    <form wire:submit.prevent="attemptLogin" class="space-y-4">
                                        <div class="flex items-center justify-between text-xs bg-zinc-100 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200/60 dark:border-zinc-700/50">
                                            <span class="font-semibold text-zinc-700 dark:text-zinc-300 truncate max-w-[240px]">{{ $authUsername }}</span>
                                            <button type="button" wire:click="resetAuthStep" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">تغییر</button>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">رمز عبور</label>
                                            <input
                                                type="password"
                                                wire:model="authPassword"
                                                placeholder="رمز عبور را وارد کنید..."
                                                class="w-full px-4 py-3 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-right"
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            class="w-full py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                        >
                                            ورود به حساب
                                        </button>

                                        @if($authMode === 'both' || $authMode === 'otp')
                                            <div class="text-center pt-2">
                                                <button type="button" wire:click="sendOtpCode" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    ورود با کد یک‌بار مصرف (OTP)
                                                </button>
                                            </div>
                                        @endif
                                    </form>

                                <!-- Step 3: OTP -->
                                @elseif($authStep === 'otp')
                                    <form wire:submit.prevent="verifyOtpCode" class="space-y-4">
                                        <div class="flex items-center justify-between text-xs bg-zinc-100 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200/60 dark:border-zinc-700/50">
                                            <span class="font-semibold text-zinc-700 dark:text-zinc-300 truncate max-w-[240px]">{{ !empty($pendingRegistrationData['phone']) ? $pendingRegistrationData['phone'] : $authUsername }}</span>
                                            <button type="button" wire:click="backFromOtp" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">تغییر</button>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">کد تأیید پیامک‌شده</label>
                                            <input
                                                type="text"
                                                wire:model="authOtp"
                                                placeholder="کد ۵ رقمی..."
                                                class="w-full px-4 py-3 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-center tracking-widest"
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            class="w-full py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                        >
                                            تأیید و ورود
                                        </button>

                                        <div class="text-center pt-1">
                                            <button type="button" wire:click="sendOtpCode" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                ارسال مجدد کد تأیید
                                            </button>
                                        </div>
                                    </form>

                                <!-- Step 4: Register -->
                                @elseif($authStep === 'register')
                                    <form wire:submit.prevent="attemptRegister" class="space-y-3 text-right">
                                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] font-semibold text-amber-700 dark:text-amber-300 leading-relaxed mb-3">
                                            حساب کاربری یافت نشد. جهت استفاده از دستیار، ثبت‌نام خود را تکمیل کنید.
                                        </div>

                                        @foreach($regFormFields as $field)
                                            @php
                                                $fid = $field['id'];
                                                if ($fid === 'username' || ($authMode === 'otp' && $fid === 'password')) continue;
                                                $isRequired = !empty($field['required']);
                                            @endphp
                                            <div>
                                                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                                    {{ $field['label'] ?? $fid }}
                                                    @if($isRequired)<span class="text-red-500">*</span>@endif
                                                </label>
                                                @if(($field['type'] ?? '') === 'textarea')
                                                    <textarea
                                                        wire:model="regInputs.{{ $fid }}"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                                    ></textarea>
                                                @elseif(($field['type'] ?? '') === 'select')
                                                    @php
                                                        $options = [];
                                                        if (!empty($field['options_json'])) {
                                                            $lines = array_filter(array_map('trim', explode("\n", $field['options_json'])));
                                                            foreach ($lines as $line) {
                                                                if (str_contains($line, ':')) {
                                                                    [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                                    $options[$okey] = $oval;
                                                                } else {
                                                                    $options[$line] = $line;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <select
                                                        wire:model="regInputs.{{ $fid }}"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                                    >
                                                        <option value="">انتخاب کنید...</option>
                                                        @foreach($options as $okey => $oval)
                                                            <option value="{{ $okey }}">{{ $oval }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif(($field['type'] ?? '') === 'password')
                                                    <input
                                                        type="password"
                                                        wire:model="regInputs.{{ $fid }}"
                                                        placeholder="حداقل ۶ کاراکتر"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                                    />
                                                @else
                                                    <input
                                                        type="{{ ($field['type'] ?? '') === 'email' ? 'email' : (($field['type'] ?? '') === 'number' ? 'number' : 'text') }}"
                                                        wire:model="regInputs.{{ $fid }}"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                                    />
                                                @endif
                                            </div>
                                        @endforeach

                                        <div class="pt-2">
                                            <button
                                                type="submit"
                                                class="w-full py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                            >
                                                تکمیل ثبت‌نام و ورود
                                            </button>
                                            <button type="button" wire:click="resetAuthStep" class="w-full mt-2 text-[11px] font-bold text-zinc-500 hover:underline">
                                                انصراف
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- Closes Content Area (L405) -->
            @else
                <div 
                    class="flex-1 w-full max-w-3xl mx-auto px-4 md:px-6 pt-24 z-10 flex flex-col gap-8"
                    :style="'padding-bottom: ' + bottomHeight + 'px'"
                    wire:key="sa-chat-container"
                >
                    <!-- CASE A: ONLY WELCOME STATE (SayHalo Inspiration) -->
                    @php
                        $hasUserMessages = false;
                        foreach($messages as $m) {
                            if (($m['role'] ?? '') === 'user') {
                                $hasUserMessages = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$hasUserMessages && !$isThinking)
                        <div class="flex-1 flex flex-col items-center justify-center min-h-[50vh] smartbot-fade-in">

                            <!-- AI Orb / Logo -->
                            <div class="relative w-24 h-24 flex items-center justify-center mb-8">
                                <div class="absolute inset-0 bg-[#3F7D20]/20 dark:bg-[#3F7D20]/30 rounded-full blur-2xl animate-pulse"></div>
                                <div class="relative w-16 h-16 bg-gradient-to-tr from-[#3F7D20] via-[#5cba2f] to-[#3F7D20] rounded-2xl shadow-xl shadow-[#3F7D20]/25 flex items-center justify-center text-white rotate-3 hover:rotate-6 transition-transform duration-500 overflow-hidden p-3.5">
                                    @if($botIconSvg)
                                        <div class="w-full h-full text-white fill-current [&_path]:fill-white [&_svg]:w-full [&_svg]:h-full [&_svg_path]:fill-white flex items-center justify-center">
                                            {!! $botIconSvg !!}
                                        </div>
                                    @elseif($botIcon)
                                        <img src="{{ $botIcon }}" class="w-full h-full object-contain rounded-xl brightness-0 invert" alt="{{ $botName }}" />
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                    @endif
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
                            <div wire:key="msg-sa-u-{{ $msg['id'] ?? $loop->index }}" id="msg-sa-row-{{ $msg['id'] ?? $loop->index }}" class="flex justify-start w-full smartbot-fade-in group">
                                <div class="bg-indigo-500/5 dark:bg-indigo-500/10 backdrop-blur-md border border-indigo-200/50 dark:border-indigo-500/35 text-indigo-900 dark:text-indigo-200 px-6 py-3.5 rounded-3xl text-[15px] leading-relaxed max-w-[85%] md:max-w-[75%] font-semibold shadow-[0_8px_30px_rgba(99,102,241,0.03)] dark:shadow-[0_8px_30px_rgba(99,102,241,0.1)]">
                                    {!! nl2br(e($msg['content'])) !!}
                                </div>
                            </div>
                        @else
                            <!-- پاسخ دستیار: ترازبندی در راست (RTL)، بدون حباب، ظاهر مستند‌گونه شبیه ChatGPT -->
                            <div wire:key="msg-sa-b-{{ $msg['id'] ?? $loop->index }}" id="msg-sa-row-{{ $msg['id'] ?? $loop->index }}" class="smartbot-ai-msg flex justify-start w-full gap-4 md:gap-6 smartbot-fade-in scroll-mt-24 md:scroll-mt-28">
                                <!-- آواتار هوش مصنوعی -->
                                <div class="flex-shrink-0 mt-1 w-8 h-8 md:w-9 md:h-9 rounded-full border border-indigo-100 dark:border-indigo-500/30 bg-gradient-to-tr from-indigo-50 dark:from-indigo-950/50 to-purple-50 dark:to-purple-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm overflow-hidden p-0.5">
                                    @if($botIconSvg)
                                        {!! $botIconSvg !!}
                                    @elseif($botIcon)
                                        <img src="{{ $botIcon }}" class="w-full h-full object-cover rounded-full" alt="{{ $botName }}" />
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1 1.275-1.275Z"/></svg>
                                    @endif
                                </div>

                                <!-- متن پیام و محصولات -->
                                <div class="flex-1 min-w-0 pt-1.5 pb-2">
                                    <div class="text-[15px] leading-[1.8] text-zinc-800 dark:text-zinc-200 prose-ai">
                                        {!! \Modules\SmartBot\App\Services\SmartTagParserService::parse($msg['content']) !!}
                                    </div>

                                    <!-- پیوست‌های تعاملی و هوشمند (کارت بانکی، شبا، کریپتو، دکمه) -->
                                    @if(!empty($msg['smart_attachments']))
                                        <div class="mt-4 flex flex-col gap-3 max-w-xl">
                                            @foreach($msg['smart_attachments'] as $attIndex => $att)
                                                @if(($att['type'] ?? '') === 'bank_card' && !empty($att['card_number']))
                                                    <!-- کارت بانکی -->
                                                    <div x-data="{ copied: false }" class="relative overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-850 to-emerald-950 text-white border border-slate-700/60 shadow-lg shadow-emerald-950/20 space-y-3 font-iranYekan">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30 text-xs">
                                                                    💳
                                                                </div>
                                                                <span class="text-xs font-bold text-slate-200">{{ $att['bank_name'] ?: 'کارت بانکی' }}</span>
                                                            </div>
                                                            @if(!empty($att['card_holder']))
                                                                <span class="text-[11px] font-semibold text-slate-400">به نام: {{ $att['card_holder'] }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
                                                            <span class="text-sm sm:text-base font-bold tracking-widest text-emerald-300 dir-ltr" style="direction: ltr !important; text-align: left;">
                                                                {{ implode(' ', str_split(preg_replace('/[^0-9]/', '', $att['card_number']), 4)) }}
                                                            </span>
                                                            <button 
                                                                type="button"
                                                                @click="
                                                                    (function(val){
                                                                        if (navigator.clipboard && window.isSecureContext) {
                                                                            return navigator.clipboard.writeText(val);
                                                                        } else {
                                                                            var el = document.createElement('textarea');
                                                                            el.value = val;
                                                                            el.style.position = 'fixed';
                                                                            el.style.left = '-9999px';
                                                                            document.body.appendChild(el);
                                                                            el.focus();
                                                                            el.select();
                                                                            try { document.execCommand('copy'); } catch(e){}
                                                                            document.body.removeChild(el);
                                                                            return Promise.resolve();
                                                                        }
                                                                    })('{{ preg_replace('/[^0-9]/', '', $att['card_number']) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
                                                                "
                                                                class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl shrink-0 mr-2"
                                                                :class="copied ? 'bg-emerald-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
                                                            >
                                                                <template x-if="!copied">
                                                                    <span>کپی کارت</span>
                                                                </template>
                                                                <template x-if="copied">
                                                                    <span>کپی شد ✓</span>
                                                                </template>
                                                            </button>
                                                        </div>
                                                    </div>

                                                @elseif(($att['type'] ?? '') === 'iban' && !empty($att['iban_code']))
                                                    <!-- شماره شبا -->
                                                    <div x-data="{ copied: false }" class="relative overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-850 to-sky-950 text-white border border-slate-700/60 shadow-lg shadow-sky-950/20 space-y-3 font-iranYekan">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center border border-sky-500/30 text-xs">
                                                                    🏦
                                                                </div>
                                                                <span class="text-xs font-bold text-slate-200">شماره شبا</span>
                                                            </div>
                                                            @if(!empty($att['account_holder']))
                                                                <span class="text-[11px] font-semibold text-slate-400">به نام: {{ $att['account_holder'] }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
                                                            <span class="text-xs sm:text-sm font-bold tracking-wider text-sky-300 truncate mr-2 dir-ltr" style="direction: ltr !important; text-align: left;">
                                                                {{ 'IR' . preg_replace('/^IR/i', '', str_replace(' ', '', $att['iban_code'])) }}
                                                            </span>
                                                            <button 
                                                                type="button"
                                                                @click="
                                                                    (function(val){
                                                                        var p=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], a=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                                                                        for(var i=0;i<10;i++){ val=val.replace(new RegExp(p[i],'g'),i).replace(new RegExp(a[i],'g'),i); }
                                                                        val = val.replace(/^ir/i, '').replace(/[^0-9]/g, '').trim();
                                                                        if (navigator.clipboard && window.isSecureContext) {
                                                                            return navigator.clipboard.writeText(val);
                                                                        } else {
                                                                            var el = document.createElement('textarea');
                                                                            el.value = val;
                                                                            el.style.position = 'fixed';
                                                                            el.style.left = '-9999px';
                                                                            document.body.appendChild(el);
                                                                            el.focus();
                                                                            el.select();
                                                                            try { document.execCommand('copy'); } catch(e){}
                                                                            document.body.removeChild(el);
                                                                            return Promise.resolve();
                                                                        }
                                                                    })('{{ preg_replace('/^IR/i', '', str_replace(' ', '', $att['iban_code'])) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
                                                                "
                                                                class="shrink-0 px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl"
                                                                :class="copied ? 'bg-sky-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
                                                            >
                                                                <template x-if="!copied">
                                                                    <span>کپی شبا</span>
                                                                </template>
                                                                <template x-if="copied">
                                                                    <span>کپی شد ✓</span>
                                                                </template>
                                                            </button>
                                                        </div>
                                                    </div>

                                                @elseif(($att['type'] ?? '') === 'crypto_wallet' && !empty($att['address']))
                                                    <!-- کیف پول کریپتو -->
                                                    <div x-data="{ copied: false }" class="relative overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 text-white border border-purple-900/50 shadow-lg shadow-purple-950/20 space-y-3 font-iranYekan">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center border border-purple-500/30 text-xs">
                                                                    🪙
                                                                </div>
                                                                <span class="text-xs font-bold text-slate-200">کیف پول {{ strtoupper($att['currency'] ?? 'Crypto') }}</span>
                                                            </div>
                                                            @if(!empty($att['network']))
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase">
                                                                    {{ $att['network'] }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
                                                            <span class="text-xs font-semibold tracking-wider text-purple-200 truncate mr-2 dir-ltr" style="direction: ltr !important; text-align: left;">
                                                                {{ $att['address'] }}
                                                            </span>
                                                            <button 
                                                                type="button"
                                                                @click="
                                                                    (function(val){
                                                                        if (navigator.clipboard && window.isSecureContext) {
                                                                            return navigator.clipboard.writeText(val);
                                                                        } else {
                                                                            var el = document.createElement('textarea');
                                                                            el.value = val;
                                                                            el.style.position = 'fixed';
                                                                            el.style.left = '-9999px';
                                                                            document.body.appendChild(el);
                                                                            el.focus();
                                                                            el.select();
                                                                            try { document.execCommand('copy'); } catch(e){}
                                                                            document.body.removeChild(el);
                                                                            return Promise.resolve();
                                                                        }
                                                                    })('{{ trim($att['address']) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
                                                                "
                                                                class="shrink-0 px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl"
                                                                :class="copied ? 'bg-purple-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
                                                            >
                                                                <template x-if="!copied">
                                                                    <span>کپی آدرس</span>
                                                                </template>
                                                                <template x-if="copied">
                                                                    <span>کپی شد ✓</span>
                                                                </template>
                                                            </button>
                                                        </div>
                                                    </div>

                                                @elseif(($att['type'] ?? '') === 'url_button' && !empty($att['button_url']))
                                                    <!-- دکمه اکشن / لینک -->
                                                    <div class="mt-1">
                                                        <a href="{{ $att['button_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                                                            <span>{{ $att['button_label'] ?: 'مشاهده و اقدام' }}</span>
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- آیتم‌های منوی شرطی -->
                                    @if(!empty($msg['menu_items']))
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mt-4 max-w-2xl">
                                            @foreach($msg['menu_items'] as $mItem)
                                                <button
                                                    wire:click="clickMenuItem({{ $mItem['id'] }}, '{{ addslashes($mItem['label']) }}')"
                                                    wire:key="mitem-sa-{{ $msg['id'] ?? $loop->parent->index }}-{{ $mItem['id'] }}"
                                                    type="button"
                                                    class="flex items-center justify-between p-3.5 text-xs font-bold text-zinc-800 dark:text-zinc-100 bg-white/95 dark:bg-[#18181c] border border-zinc-200/80 dark:border-zinc-800/85 rounded-2xl hover:bg-purple-50/80 dark:hover:bg-purple-950/40 hover:border-purple-300 dark:hover:border-purple-500/50 hover:text-purple-600 dark:hover:text-purple-300 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer text-right group"
                                                >
                                                    <span class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-purple-500 group-hover:scale-125 transition-transform"></span>
                                                        {{ $mItem['label'] }}
                                                    </span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-zinc-400 group-hover:text-purple-500 transform group-hover:-translate-x-1 transition-all flex-shrink-0 mr-2 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- لینک خروجی -->
                                    @if(!empty($msg['url']))
                                        <div class="mt-3">
                                            <a href="{{ $msg['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all">
                                                <span>مشاهده لینک</span>
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif

                                    <!-- لیست محصولات پیشنهادی -->
                                    @if(!empty($msg['products']))
                                        @php
                                            $isVariantCardMsg = (($msg['answer_type'] ?? '') === 'variant_card');
                                        @endphp
                                        <div class="grid {{ ($assistantLevel === 2 && $isVariantCardMsg) ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2' }} gap-3.5 mt-4 max-w-3xl">
                                            @foreach($msg['products'] as $product)
                                                @php
                                                    $activeVariant = null;
                                                    $hasVariations = !empty($product['has_variations']);
                                                    $isVariantCard = (($msg['answer_type'] ?? '') === 'variant_card');
                                                    if ($assistantLevel === 2 && $isVariantCard && $hasVariations && !empty($expandedVariants[$product['id']])) {
                                                        $selId = $expandedVariants[$product['id']]['selected_variant_id'] ?? null;
                                                        if ($selId) {
                                                            $activeVariant = collect($expandedVariants[$product['id']]['variants'])->firstWhere('variant_id', $selId);
                                                        }
                                                    }
                                                @endphp
                                                <div wire:key="p-wrap-sa-{{ $msg['id'] ?? $loop->parent->index }}-{{ $product['id'] ?? $loop->index }}" class="col-span-1 flex flex-col">
                                                    <!-- کارت اصلی محصول -->
                                                    <div class="flex flex-col bg-white/95 dark:bg-[#18181c] backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800/85 rounded-2xl overflow-hidden hover:bg-zinc-50/80 dark:hover:bg-[#202025] shadow-sm hover:shadow-indigo-500/5 hover:-translate-y-0.5 hover:border-indigo-300/60 dark:hover:border-indigo-500/30 transition-all duration-300 p-3.5 gap-3.5 items-stretch flex-1">
                                                        <!-- بخش بالا (عکس + عنوان + بج‌ها) -->
                                                        <div class="flex gap-3.5 items-start w-full">
                                                            <!-- عکس محصول -->
                                                            <a href="{{ $this->getProductUrl($product, $activeVariant ?? null) }}" 
                                                               class="block flex-shrink-0 group relative overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800/80 bg-zinc-50 dark:bg-zinc-800/40 p-2 w-16 h-16 md:w-20 md:h-20 flex items-center justify-center">
                                                                @if(!empty($product['image']))
                                                                    <img src="{{ $product['image'] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" alt="{{ $product['title'] }}" />
                                                                @else
                                                                    <div class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105 text-zinc-400">
                                                                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                            </a>

                                                            <!-- عنوان و بج‌ها -->
                                                            <div class="flex-1 min-w-0 flex flex-col gap-1.5 py-0.5">
                                                                <a href="{{ $this->getProductUrl($product, $activeVariant ?? null) }}" class="block group">
                                                                    <h4 class="text-xs md:text-sm font-bold text-zinc-900 dark:text-zinc-100 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-relaxed">{{ $product['title'] }}</h4>
                                                                </a>

                                                                @if(!empty($product['variant_name']) || (!empty($product['has_variations']) && (!$isVariantCard)))
                                                                    <div class="flex flex-wrap gap-1.5">
                                                                        @if(!empty($product['variant_name']))
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[10px] font-medium border border-zinc-200/60 dark:border-zinc-700/50">
                                                                                {{ $product['variant_name'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(!empty($product['has_variations']) && (!$isVariantCard))
                                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-[10px] font-bold border border-indigo-100 dark:border-indigo-900/40">
                                                                                <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12M6 12h12M6 18h12" />
                                                                                </svg>
                                                                                دارای تنوع
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- انتخاب ویژگی‌ها (فقط موقعی که پیام از نوع variant_card باشد) -->
                                                        @if($assistantLevel === 2 && $isVariantCard && $hasVariations && !empty($expandedVariants[$product['id']]['available_attributes']))
                                                            <div class="flex flex-col gap-3 border-t border-zinc-100 dark:border-zinc-800/60 pt-3 mt-1.5 w-full">
                                                                @foreach($expandedVariants[$product['id']]['available_attributes'] as $attrKey => $attrValues)
                                                                    @php
                                                                        $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                                                                        $type = $dictAttr ? $dictAttr->type : 'select';
                                                                        $unit = $dictAttr ? $dictAttr->unit : '';
                                                                        $currentVal = $selectedProductAttributes[$product['id']][$attrKey] ?? '';
                                                                    @endphp
                                                                    <div class="flex flex-col gap-1.5">
                                                                        <div class="flex items-center gap-1.5">
                                                                            <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500">{{ $attrKey }}:</span>
                                                                            <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400">{{ $currentVal }}@if($unit)<span class="text-[9px] font-medium opacity-80"> {{ $unit }}</span>@endif</span>
                                                                        </div>
                                                                        <div class="flex flex-wrap gap-2">
                                                                            @foreach($attrValues as $val)
                                                                                @php
                                                                                    $isSelected = $currentVal === $val;
                                                                                    $metaValue = null;
                                                                                    if ($dictAttr) {
                                                                                        $dictVal = $dictAttr->values->firstWhere('value', $val);
                                                                                        if ($dictVal) {
                                                                                            $metaValue = $dictVal->meta_value;
                                                                                        }
                                                                                    }
                                                                                    $borderActive = 'border-indigo-600 dark:border-indigo-500';
                                                                                    $borderInactive = 'border-zinc-200 dark:border-zinc-800/80';
                                                                                    $bgActive = ($type === 'color' || $type === 'image') ? '' : 'bg-indigo-50 dark:bg-indigo-950/40';
                                                                                    $bgInactive = ($type === 'color' || $type === 'image') ? '' : 'bg-zinc-50 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800/80';
                                                                                    $textActive = ($type === 'color' || $type === 'image') ? '' : 'text-indigo-600 dark:text-indigo-400';
                                                                                    $textInactive = ($type === 'color' || $type === 'image') ? '' : 'text-zinc-700 dark:text-zinc-300';
                                                                                @endphp
                                                                                <div class="relative group flex-shrink-0">
                                                                                    <button wire:click="selectAttribute({{ $product['id'] }}, '{{ $attrKey }}', '{{ $val }}')"
                                                                                            wire:key="attr-sa-{{ $product['id'] }}-{{ $attrKey }}-{{ $val }}"
                                                                                            type="button"
                                                                                            class="relative transition-all flex items-center justify-center overflow-hidden outline-none border-2 cursor-pointer
                                                                                            {{ $isSelected ? "$borderActive $bgActive $textActive" : "$borderInactive $bgInactive $textInactive" }}
                                                                                            {{ $type === 'color' ? 'w-8 h-8 p-0.5 rounded-full' : ($type === 'image' ? 'w-11 h-11 p-0.5 rounded-lg' : 'px-3 py-1.5 text-xs font-bold rounded-xl') }}">
                                                                                        @if($type === 'color' || $type === 'image')
                                                                                            @if($metaValue && str_starts_with($metaValue, 'attributes/'))
                                                                                                <img src="{{ Storage::url($metaValue) }}" class="w-full h-full object-cover {{ $type === 'color' ? 'rounded-full' : 'rounded-md' }}">
                                                                                            @else
                                                                                                @if($type === 'color')
                                                                                                    <span class="w-full h-full rounded-full shadow-inner" style="background-color: {{ $metaValue ?? '#ccc' }}"></span>
                                                                                                @else
                                                                                                    <span class="text-[9px] leading-tight text-center">{{ $val }}</span>
                                                                                                @endif
                                                                                            @endif
                                                                                        @else
                                                                                            {{ $val }}
                                                                                        @endif

                                                                                        @if($type === 'color')
                                                                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity pointer-events-none" style="opacity: {{ $isSelected ? '1' : '0' }}">
                                                                                                <div class="bg-white/80 dark:bg-black/50 rounded-full w-full h-full flex items-center justify-center">
                                                                                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endif
                                                                                    </button>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                 @endforeach
                                                            </div>
                                                        @endif

                                                        <!-- بخش پایین (تمام عرض: دکمه سمت راست، قیمت سمت چپ) -->
                                                        <div class="flex justify-between items-center w-full border-t border-zinc-100 dark:border-zinc-800/60 pt-2.5 mt-auto">
                                                            <!-- دکمه اقدام (سمت راست در RTL) -->
                                                            <div class="flex-shrink-0">
                                                                @if($hasVariations && $assistantLevel === 2 && !$isVariantCard)
                                                                    <!-- دکمه سطح ۲ در پیام اولیه: باز کردن کارت تنوع با ایجاد پیام جدید -->
                                                                    <button wire:click="showVariantCard({{ $product['id'] }}, '{{ addslashes($product['title']) }}', '{{ $msg['id'] ?? $loop->parent->index }}')" 
                                                                            wire:key="btn-var-sa-open-{{ $product['id'] }}-{{ $msg['id'] ?? $loop->parent->index }}"
                                                                            type="button"
                                                                            class="inline-flex items-center justify-center gap-1.5 px-3.5 h-8 text-[11px] font-bold text-white rounded-xl whitespace-nowrap transition-all duration-300 hover:brightness-110 active:scale-95 shadow-sm cursor-pointer" 
                                                                            style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px -2px {{ $primaryColor }}50;">
                                                                        <span>انتخاب تنوع</span>
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                                        </svg>
                                                                    </button>
                                                                @elseif($hasVariations && $assistantLevel !== 2)
                                                                    <!-- دکمه سطح ۱: لینک به صفحه محصول -->
                                                                    <a href="{{ $this->getProductUrl($product) }}" 
                                                                       wire:key="btn-var-sa-lvl1-{{ $product['id'] }}"
                                                                       class="inline-flex items-center justify-center gap-1 px-3 h-8 text-[11px] font-bold text-white rounded-xl whitespace-nowrap transition-all duration-300 hover:brightness-110 active:scale-95 shadow-sm" 
                                                                       style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px -2px {{ $primaryColor }}50;">
                                                                        <span>مشاهده و خرید</span>
                                                                        <svg class="w-3 h-3 transform -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                                        </svg>
                                                                    </a>
                                                                @elseif($assistantLevel === 2 && $isVariantCard && $hasVariations)
                                                                    @if($activeVariant && $activeVariant['has_stock'])
                                                                        <div class="w-full min-w-[90px] max-w-[110px]" wire:key="cart-btn-sa-var-{{ $product['id'] }}-{{ $activeVariant['variant_id'] }}">
                                                                            @livewire('market::web.add-to-cart-button', [
                                                                                'variantId' => $activeVariant['variant_id'],
                                                                                'vendorProductId' => $activeVariant['vendor_product_id'],
                                                                                't' => [
                                                                                    'style' => 'background-color: ' . $primaryColor . ';',
                                                                                    'shadow_color' => $primaryColor,
                                                                                    'justify' => 'justify-start',
                                                                                ]
                                                                            ], key('cart-btn-sa-var-' . $product['id'] . '-' . $activeVariant['variant_id']))
                                                                        </div>
                                                                    @else
                                                                        <span class="inline-flex items-center justify-center px-3 h-8 text-[11px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl whitespace-nowrap">ناموجود</span>
                                                                    @endif
                                                                @elseif($product['has_stock'] && $product['variant_id'] && $product['vendor_product_id'])
                                                                    <div class="w-full min-w-[90px] max-w-[110px]">
                                                                        @livewire('market::web.add-to-cart-button', [
                                                                            'variantId' => $product['variant_id'],
                                                                            'vendorProductId' => $product['vendor_product_id'],
                                                                            't' => [
                                                                                'style' => 'background-color: ' . $primaryColor . ';',
                                                                                'shadow_color' => $primaryColor,
                                                                                'justify' => 'justify-start',
                                                                            ]
                                                                        ], key('cart-btn-' . $product['id'] . '-' . $loop->index . '-' . ($msg['id'] ?? 0)))
                                                                    </div>
                                                                @else
                                                                    <span class="inline-flex items-center justify-center px-3 h-8 text-[11px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl whitespace-nowrap">ناموجود</span>
                                                                @endif
                                                            </div>

                                                            <!-- قیمت (سمت چپ در RTL) -->
                                                            <div class="flex flex-col items-end text-left">
                                                                @php
                                                                    $priceVal = $activeVariant ? $activeVariant['formatted_price'] : $product['formatted_price'];
                                                                    $origPriceVal = $activeVariant ? $activeVariant['formatted_original_price'] : $product['formatted_original_price'];
                                                                    $discPercent = $activeVariant ? $activeVariant['discount_percent'] : $product['discount_percent'];
                                                                @endphp
                                                                @if($discPercent > 0)
                                                                    <div class="flex items-center gap-1 mb-0.5">
                                                                        <span class="text-[10px] text-zinc-400 line-through">
                                                                            {{ $origPriceVal }}
                                                                        </span>
                                                                        <span class="px-1 py-0.2 rounded text-[9px] font-black bg-rose-500 text-white">
                                                                            {{ $discPercent }}%
                                                                        </span>
                                                                    </div>
                                                                    <span class="text-xs md:text-sm font-black text-rose-600 dark:text-rose-400">
                                                                        {{ $priceVal }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-xs md:text-sm font-black text-zinc-900 dark:text-zinc-100">
                                                                        {{ $priceVal }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($isVariantCard && !empty($msg['parent_message_key']))
                                            <div class="mt-4 flex justify-end">
                                                <button 
                                                    @click="
                                                        const el = document.getElementById('msg-sa-row-{{ $msg['parent_message_key'] }}');
                                                        if (el) {
                                                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                            const target = el.querySelector('.flex-1') || el;
                                                            target.classList.remove('telegram-flash');
                                                            target.offsetHeight;
                                                            target.classList.add('telegram-flash');
                                                        }
                                                        const row = document.getElementById('msg-sa-row-{{ $msg['id'] }}');
                                                        if (row) {
                                                            row.style.maxHeight = row.offsetHeight + 'px';
                                                            row.style.overflow = 'hidden';
                                                            row.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                                                            row.offsetHeight;
                                                            row.style.opacity = '0';
                                                            row.style.maxHeight = '0';
                                                            row.style.paddingTop = '0';
                                                            row.style.paddingBottom = '0';
                                                            row.style.marginTop = '0';
                                                            row.style.marginBottom = '0';
                                                        }
                                                        setTimeout(() => {
                                                            $wire.removeMessage('{{ $msg['id'] }}');
                                                        }, 350);
                                                    "
                                                    type="button"
                                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-900/60 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors shadow-sm cursor-pointer w-full sm:w-auto"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                    </svg>
                                                    <span>به محصول دیگری نیاز دارید؟</span>
                                                </button>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- دکمه بازگشت تمام‌عرض فقط برای آخرین پیام -->
                                    @if($loop->last && $msg['role'] === 'bot' && !$loop->first && (($msg['answer_type'] ?? '') !== 'variant_card'))
                                        <div class="mt-4 w-full">
                                            <button
                                                x-on:click="
                                                    const row = document.getElementById('msg-sa-row-' + '{{ $msg['id'] ?? $loop->index }}');
                                                    if (row) {
                                                        row.style.maxHeight = row.offsetHeight + 'px';
                                                        row.style.overflow = 'hidden';
                                                        row.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                                                        row.offsetHeight;
                                                        row.style.opacity = '0';
                                                        row.style.maxHeight = '0';
                                                        row.style.paddingTop = '0';
                                                        row.style.paddingBottom = '0';
                                                        row.style.marginTop = '0';
                                                        row.style.marginBottom = '0';
                                                    }
                                                    setTimeout(() => {
                                                        $wire.goBackStep('{{ $msg['id'] ?? $loop->index }}');
                                                    }, 350);
                                                "
                                                type="button"
                                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300 bg-zinc-50/90 dark:bg-zinc-900/60 hover:bg-purple-50 dark:hover:bg-purple-950/40 hover:text-purple-600 dark:hover:text-purple-300 hover:border-purple-300 dark:hover:border-purple-500/50 transition-all duration-200 shadow-sm cursor-pointer"
                                            >
                                                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                </svg>
                                                <span>بازگشت</span>
                                            </button>
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
            <div x-ref="bottomBar" class="fixed bottom-0 left-0 w-full z-20 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-[#0a0a0d] dark:via-[#0a0a0d]/97 dark:to-transparent pt-12 pb-6" wire:key="sa-input-bar">
                <div class="max-w-3xl mx-auto w-full px-4 flex flex-col gap-3">

                    <!-- پیشنهادات حین چت / منو اصلی در صورت غیرفعال بودن تایپ متنی -->
                    @if(count($messages) > 1)
                        @if($allowCustomTyping)
                            @if(!empty($suggestions))
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
                            @endif
                        @else
                            <!-- پنل با استایل قبلی و فقط گزینه منو اصلی در صورت غیرفعال بودن تایپ متنی -->
                            <div class="w-full bg-white dark:bg-zinc-900/90 border border-zinc-200/80 dark:border-zinc-800/70 rounded-[28px] p-5 shadow-[0_4px_24px_rgba(0,0,0,0.08)] dark:shadow-[0_4px_24px_rgba(0,0,0,0.4)] flex flex-col gap-4 smartbot-fade-in">
                                <div class="flex items-center gap-2 text-xs font-bold text-zinc-800 dark:text-zinc-200 border-b border-zinc-100 dark:border-zinc-800/60 pb-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                    <span>دسترسی سریع</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2.5">
                                    <button
                                        wire:click="resetSession"
                                        type="button"
                                        class="flex items-center justify-between p-3.5 text-xs font-bold text-zinc-800 dark:text-zinc-200 bg-zinc-50/80 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/50 rounded-2xl hover:bg-indigo-50 dark:hover:bg-indigo-500/15 hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all duration-200 text-right group shadow-sm hover:shadow-md cursor-pointer"
                                    >
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            <span>منو اصلی</span>
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-zinc-400 group-hover:text-indigo-500 transform group-hover:-translate-x-1 transition-all flex-shrink-0 mr-2 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- باکس ورودی چت اصلی یا دکمه منو اصلی -->
                    @if($allowCustomTyping)
                        <div class="relative bg-white/95 dark:bg-[#151518]/95 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-700/60 rounded-[28px] p-2 sm:p-2.5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.5)] flex items-end transition-all focus-within:ring-2 focus-within:ring-indigo-500/30 focus-within:border-indigo-500">
                            <!-- دکمه ضمیمه یا اختیاری -->
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
        @endif
    </div>
@endif

    <!-- 2. STANDARD COMPACT FLOATING CHAT PANEL -->
    @if(!$isStandalone && $isWidgetOpen)
        <div
            class="fixed bottom-24 left-6 w-[380px] h-[600px] z-50 bg-white dark:bg-[#0c0c0e] border border-zinc-200 dark:border-zinc-800/80 rounded-3xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300"
            x-data="{
                inputValue: $wire.entangle('userMessage'),
                init() {
                    this.scrollToLatestResponse();
                    window.addEventListener('chatScrollToBottom', () => this.scrollToLatestResponse());
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.chatBody;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
                scrollToLatestResponse() {
                    this.$nextTick(() => {
                        const el = this.$refs.chatBody;
                        if (!el) return;
                        const botMessages = el.querySelectorAll('.smartbot-cmp-ai-msg');
                        if (botMessages.length > 0) {
                            const latestMsg = botMessages[botMessages.length - 1];
                            const targetTop = latestMsg.offsetTop - 75;
                            el.scrollTo({
                                top: Math.max(0, targetTop),
                                behavior: 'smooth'
                            });
                        } else {
                            el.scrollTop = el.scrollHeight;
                        }
                    });
                },
                submitForm() {
                    const val = this.$refs.inputField.value.trim();
                    if (val && !this.$wire.isThinking) {
                        this.$wire.sendMessage();
                        this.scrollToBottom();
                    }
                }
            }"
            x-init="init()"
        >
            <!-- Header -->
            <div class="px-5 py-4 border-b border-zinc-150 dark:border-zinc-800/80 flex items-center justify-between bg-white/90 dark:bg-[#0c0c0e]/90 backdrop-blur-md z-20 absolute top-0 w-full">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100 dark:border-indigo-500/20 overflow-hidden p-0.5">
                        @if($botIconSvg)
                            {!! $botIconSvg !!}
                        @elseif($botIcon)
                            <img src="{{ $botIcon }}" class="w-full h-full object-cover rounded-full" alt="{{ $botName }}" />
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                        @endif
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

            <!-- Messages List & Auth Panel -->
            @if($showAuthPanel)
                <div class="flex-grow flex flex-col justify-center p-6 bg-zinc-50/50 dark:bg-[#0f0f12] overflow-y-auto mt-14">
                    <!-- Auth Header -->
                    <div class="text-center mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 mx-auto mb-3 border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                            @if($botIconSvg)
                                {!! $botIconSvg !!}
                            @elseif($botIcon)
                                <img src="{{ $botIcon }}" class="w-full h-full object-cover rounded-2xl" alt="{{ $botName }}" />
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                            @endif
                        </div>
                        <h4 class="text-base font-bold text-zinc-900 dark:text-white">ورود / ثبت‌نام در {{ $botName }}</h4>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">جهت گفتگو با دستیار، ابتدا وارد شوید.</p>
                    </div>

                    @if($authError)
                        <div class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs font-semibold text-red-600 dark:text-red-400 leading-relaxed text-right">
                            {{ $authError }}
                        </div>
                    @endif

                    @if($authSuccessMsg)
                        <div class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-600 dark:text-emerald-400 leading-relaxed text-right">
                            {{ $authSuccessMsg }}
                        </div>
                    @endif

                    <!-- Dynamic Auth Step Container -->
                    <div wire:key="auth-cmp-step-container-{{ $authStep }}">
                        <!-- Step 1: Identifier -->
                        @if($authStep === 'identifier')
                            <form wire:submit.prevent="checkIdentifier" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">{{ $usernameLabel }}</label>
                                    <input
                                        type="text"
                                        wire:model="authUsername"
                                        placeholder="ورود {{ $usernameLabel }}..."
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-right"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                >
                                    ادامه
                                </button>
                            </form>

                        <!-- Step 2: Password -->
                        @elseif($authStep === 'password')
                            <form wire:submit.prevent="attemptLogin" class="space-y-4">
                                <div class="flex items-center justify-between text-xs bg-zinc-100 dark:bg-zinc-800/60 p-2.5 rounded-xl border border-zinc-200/60 dark:border-zinc-700/50">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300 truncate max-w-[200px]">{{ $authUsername }}</span>
                                    <button type="button" wire:click="resetAuthStep" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">تغییر</button>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">رمز عبور</label>
                                    <input
                                        type="password"
                                        wire:model="authPassword"
                                        placeholder="رمز عبور را وارد کنید..."
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-right"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                >
                                    ورود به حساب
                                </button>

                                @if($authMode === 'both' || $authMode === 'otp')
                                    <div class="text-center pt-2">
                                        <button type="button" wire:click="sendOtpCode" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            ورود با کد یک‌بار مصرف (OTP)
                                        </button>
                                    </div>
                                @endif
                            </form>

                        <!-- Step 3: OTP -->
                        @elseif($authStep === 'otp')
                            <form wire:submit.prevent="verifyOtpCode" class="space-y-4">
                                <div class="flex items-center justify-between text-xs bg-zinc-100 dark:bg-zinc-800/60 p-2.5 rounded-xl border border-zinc-200/60 dark:border-zinc-700/50">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300 truncate max-w-[200px]">{{ !empty($pendingRegistrationData['phone']) ? $pendingRegistrationData['phone'] : $authUsername }}</span>
                                    <button type="button" wire:click="backFromOtp" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">تغییر</button>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 text-right">کد تأیید پیامک‌شده</label>
                                    <input
                                        type="text"
                                        wire:model="authOtp"
                                        placeholder="کد ۵ رقمی..."
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-center tracking-widest"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                >
                                    تأیید و ورود
                                </button>

                                <div class="text-center pt-1">
                                    <button type="button" wire:click="sendOtpCode" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        ارسال مجدد کد تأیید
                                    </button>
                                </div>
                            </form>

                        <!-- Step 4: Register -->
                        @elseif($authStep === 'register')
                            <form wire:submit.prevent="attemptRegister" class="space-y-3 text-right">
                                <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] font-semibold text-amber-700 dark:text-amber-300 leading-relaxed mb-3">
                                    حساب کاربری یافت نشد. جهت استفاده از دستیار، ثبت‌نام خود را تکمیل کنید.
                                </div>

                                @foreach($regFormFields as $field)
                                    @php
                                        $fid = $field['id'];
                                        if ($fid === 'username' || ($authMode === 'otp' && $fid === 'password')) continue;
                                        $isRequired = !empty($field['required']);
                                    @endphp
                                    <div>
                                        <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                            {{ $field['label'] ?? $fid }}
                                            @if($isRequired)<span class="text-red-500">*</span>@endif
                                        </label>
                                        @if(($field['type'] ?? '') === 'textarea')
                                            <textarea
                                                wire:model="regInputs.{{ $fid }}"
                                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                            ></textarea>
                                        @elseif(($field['type'] ?? '') === 'select')
                                            @php
                                                $options = [];
                                                if (!empty($field['options_json'])) {
                                                    $lines = array_filter(array_map('trim', explode("\n", $field['options_json'])));
                                                    foreach ($lines as $line) {
                                                        if (str_contains($line, ':')) {
                                                            [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                            $options[$okey] = $oval;
                                                        } else {
                                                            $options[$line] = $line;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <select
                                                wire:model="regInputs.{{ $fid }}"
                                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                            >
                                                <option value="">انتخاب کنید...</option>
                                                @foreach($options as $okey => $oval)
                                                    <option value="{{ $okey }}">{{ $oval }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? '') === 'password')
                                            <input
                                                type="password"
                                                wire:model="regInputs.{{ $fid }}"
                                                placeholder="حداقل ۶ کاراکتر"
                                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                            />
                                        @else
                                            <input
                                                type="{{ ($field['type'] ?? '') === 'email' ? 'email' : (($field['type'] ?? '') === 'number' ? 'number' : 'text') }}"
                                                wire:model="regInputs.{{ $fid }}"
                                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                            />
                                        @endif
                                    </div>
                                @endforeach

                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        class="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-all shadow-md shadow-indigo-500/20 cursor-pointer"
                                    >
                                        تکمیل ثبت‌نام و ورود
                                    </button>
                                    <button type="button" wire:click="resetAuthStep" class="w-full mt-2 text-[11px] font-bold text-zinc-500 hover:underline">
                                        انصراف
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <!-- Messages List -->
                <div class="flex-grow overflow-y-auto pt-20 p-4 space-y-6 bg-zinc-50/50 dark:bg-[#0f0f12]" x-ref="chatBody">
                    @php
                        $hasUserMessagesCmp = false;
                        foreach($messages as $m) {
                            if (($m['role'] ?? '') === 'user') {
                                $hasUserMessagesCmp = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$hasUserMessagesCmp && !$isThinking)
                        <div class="text-center py-10">
                            <div class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-900 mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 20.94c1.5 0 2.75 1.06 4 1.06 3 0 6-8 6-12.22A4.91 4.91 0 0 0 17 5c-2.22 0-4 1.44-5 2-1-.56-2.78-2-5-2a4.9 4.9 0 0 0-5 4.78C2 14 5 22 8 22c1.25 0 2.5-1.06 4-1.06Z"/></svg>
                            </div>
                            <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200 mb-1">سلام!</h4>
                            <p class="text-xs text-zinc-500">چگونه می‌توانم راهنماییتان کنم؟</p>
                        </div>
                    @endif

                    @foreach($messages as $msg)
                        <div wire:key="msg-cmp-row-{{ $msg['id'] ?? $loop->index }}" id="msg-cmp-row-{{ $msg['id'] ?? $loop->index }}" class="{{ $msg['role'] !== 'user' ? 'smartbot-cmp-ai-msg ' : '' }}flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }} w-full smartbot-fade-in scroll-mt-20">
                            @if($msg['role'] === 'user')
                                <div class="max-w-[85%] bg-indigo-600 text-white px-4 py-2.5 rounded-2xl rounded-tr-sm text-[13px] leading-relaxed shadow-sm">
                                    {!! nl2br(e($msg['content'])) !!}
                                </div>
                            @else
                                <div class="flex gap-2 w-full">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-indigo-600 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                    </div>
                                    <div class="flex-1 text-[13px] text-zinc-800 dark:text-zinc-200 leading-relaxed pt-1">
                                        {!! \Modules\SmartBot\App\Services\SmartTagParserService::parse($msg['content']) !!}

                                        <!-- آیتم‌های منوی شرطی در ویجت -->
                                        @if(!empty($msg['menu_items']))
                                            <div class="grid grid-cols-1 gap-2 mt-3">
                                                @foreach($msg['menu_items'] as $mItem)
                                                    <button
                                                        wire:click="clickMenuItem({{ $mItem['id'] }}, '{{ addslashes($mItem['label']) }}')"
                                                        wire:key="mitem-cmp-{{ $msg['id'] ?? $loop->parent->index }}-{{ $mItem['id'] }}"
                                                        type="button"
                                                        class="flex items-center justify-between p-2.5 text-xs font-bold text-zinc-800 dark:text-zinc-100 bg-white/95 dark:bg-[#18181c] border border-zinc-200/80 dark:border-zinc-800/85 rounded-xl hover:bg-purple-50/80 dark:hover:bg-purple-950/40 hover:border-purple-300 dark:hover:border-purple-500/50 hover:text-purple-600 dark:hover:text-purple-300 transition-all duration-200 shadow-sm cursor-pointer text-right group"
                                                    >
                                                        <span class="flex items-center gap-1.5">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 group-hover:scale-125 transition-transform"></span>
                                                            {{ $mItem['label'] }}
                                                        </span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-400 group-hover:text-purple-500 transform group-hover:-translate-x-1 transition-all flex-shrink-0 mr-1 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- لینک خروجی در ویجت -->
                                        @if(!empty($msg['url']))
                                            <div class="mt-2.5">
                                                <a href="{{ $msg['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-all">
                                                    <span>مشاهده لینک</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            </div>
                                        @endif

                                        <!-- Products in Widget -->
                                        @if(!empty($msg['products']))
                                            <div class="grid grid-cols-1 gap-2.5 mt-3">
                                                @foreach($msg['products'] as $product)
                                                    @php
                                                        $activeVariant = null;
                                                        $hasVariations = !empty($product['has_variations']);
                                                        $isVariantCard = (($msg['answer_type'] ?? '') === 'variant_card');
                                                        if ($assistantLevel === 2 && $isVariantCard && $hasVariations && !empty($expandedVariants[$product['id']])) {
                                                            $selId = $expandedVariants[$product['id']]['selected_variant_id'] ?? null;
                                                            if ($selId) {
                                                                $activeVariant = collect($expandedVariants[$product['id']]['variants'])->firstWhere('variant_id', $selId);
                                                            }
                                                        }
                                                    @endphp
                                                    <div wire:key="p-wrap-cmp-{{ $msg['id'] ?? $loop->parent->index }}-{{ $product['id'] ?? $loop->index }}" class="col-span-1 flex flex-col">
                                                        <!-- کارت محصول در ویجت -->
                                                        <div class="flex flex-col bg-white/95 dark:bg-[#18181c] backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800/85 rounded-2xl overflow-hidden shadow-sm hover:bg-zinc-50/80 dark:hover:bg-[#202025] hover:border-indigo-300/60 dark:hover:border-indigo-500/30 transition-all duration-300 p-2.5 gap-2.5 items-stretch">
                                                            <!-- بخش بالا (عکس + عنوان + بج‌ها) -->
                                                            <div class="flex gap-2.5 items-start w-full">
                                                                <!-- عکس محصول -->
                                                                <a href="{{ $this->getProductUrl($product, $activeVariant ?? null) }}" 
                                                                   class="block flex-shrink-0 group overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40 p-1 w-12 h-12 flex items-center justify-center">
                                                                    @if(!empty($product['image']))
                                                                        <img src="{{ $product['image'] }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" alt="" />
                                                                    @else
                                                                        <div class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105 text-zinc-400">
                                                                            <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </a>

                                                                <!-- جزییات -->
                                                                <div class="flex-1 min-w-0 flex flex-col gap-1 py-0.5">
                                                                    <a href="{{ $this->getProductUrl($product, $activeVariant ?? null) }}" class="block group">
                                                                        <h4 class="text-xs font-bold truncate text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-normal">{{ $product['title'] }}</h4>
                                                                    </a>
                                                                    @if(!empty($product['variant_name']) || (!empty($product['has_variations']) && (!$isVariantCard)))
                                                                        <div class="flex flex-wrap gap-1 mt-0.5">
                                                                            @if(!empty($product['variant_name']))
                                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[9px] font-medium border border-zinc-200/60 dark:border-zinc-700/50">
                                                                                    {{ $product['variant_name'] }}
                                                                                </span>
                                                                            @endif
                                                                            @if(!empty($product['has_variations']) && (!$isVariantCard))
                                                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-[9px] font-bold border border-indigo-100 dark:border-indigo-900/40">
                                                                                    <svg class="w-2.5 h-2.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12M6 12h12M6 18h12" />
                                                                                    </svg>
                                                                                    دارای تنوع
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- انتخاب ویژگی‌ها (فقط موقعی که این پیام از نوع variant_card باشد) -->
                                                            @if($assistantLevel === 2 && $isVariantCard && $hasVariations && !empty($expandedVariants[$product['id']]['available_attributes']))
                                                                <div class="flex flex-col gap-2 border-t border-zinc-100 dark:border-zinc-800/60 pt-2 mt-1 w-full">
                                                                    @foreach($expandedVariants[$product['id']]['available_attributes'] as $attrKey => $attrValues)
                                                                        @php
                                                                            $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                                                                            $type = $dictAttr ? $dictAttr->type : 'select';
                                                                            $unit = $dictAttr ? $dictAttr->unit : '';
                                                                            $currentVal = $selectedProductAttributes[$product['id']][$attrKey] ?? '';
                                                                        @endphp
                                                                        <div class="flex flex-col gap-1">
                                                                            <div class="flex items-center gap-1">
                                                                                <span class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500">{{ $attrKey }}:</span>
                                                                                <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400">{{ $currentVal }}@if($unit)<span class="text-[8px] font-medium opacity-80"> {{ $unit }}</span>@endif</span>
                                                                            </div>
                                                                            <div class="flex flex-wrap gap-1.5">
                                                                                @foreach($attrValues as $val)
                                                                                    @php
                                                                                        $isSelected = $currentVal === $val;
                                                                                        $metaValue = null;
                                                                                        if ($dictAttr) {
                                                                                            $dictVal = $dictAttr->values->firstWhere('value', $val);
                                                                                            if ($dictVal) {
                                                                                                $metaValue = $dictVal->meta_value;
                                                                                            }
                                                                                        }
                                                                                        $borderActive = 'border-indigo-600 dark:border-indigo-500';
                                                                                        $borderInactive = 'border-zinc-200 dark:border-zinc-800/80';
                                                                                        $bgActive = ($type === 'color' || $type === 'image') ? '' : 'bg-indigo-50 dark:bg-indigo-950/40';
                                                                                        $bgInactive = ($type === 'color' || $type === 'image') ? '' : 'bg-zinc-50 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800/80';
                                                                                        $textActive = ($type === 'color' || $type === 'image') ? '' : 'text-indigo-600 dark:text-indigo-400';
                                                                                        $textInactive = ($type === 'color' || $type === 'image') ? '' : 'text-zinc-700 dark:text-zinc-300';
                                                                                    @endphp
                                                                                    <div class="relative group flex-shrink-0">
                                                                                        <button wire:click="selectAttribute({{ $product['id'] }}, '{{ $attrKey }}', '{{ $val }}')"
                                                                                                wire:key="attr-cmp-{{ $product['id'] }}-{{ $attrKey }}-{{ $val }}"
                                                                                                type="button"
                                                                                                class="relative transition-all flex items-center justify-center overflow-hidden outline-none border-2 cursor-pointer
                                                                                                {{ $isSelected ? "$borderActive $bgActive $textActive" : "$borderInactive $bgInactive $textInactive" }}
                                                                                                {{ $type === 'color' ? 'w-7 h-7 p-0.5 rounded-full' : ($type === 'image' ? 'w-9 h-9 p-0.5 rounded-md' : 'px-2 py-1 text-[10px] font-bold rounded-lg') }}">
                                                                                            @if($type === 'color' || $type === 'image')
                                                                                                @if($metaValue && str_starts_with($metaValue, 'attributes/'))
                                                                                                    <img src="{{ Storage::url($metaValue) }}" class="w-full h-full object-cover {{ $type === 'color' ? 'rounded-full' : 'rounded-md' }}">
                                                                                                @else
                                                                                                    @if($type === 'color')
                                                                                                        <span class="w-full h-full rounded-full shadow-inner" style="background-color: {{ $metaValue ?? '#ccc' }}"></span>
                                                                                                    @else
                                                                                                        <span class="text-[8px] leading-tight text-center">{{ $val }}</span>
                                                                                                    @endif
                                                                                                @endif
                                                                                            @else
                                                                                                {{ $val }}
                                                                                            @endif

                                                                                            @if($type === 'color')
                                                                                                <div class="absolute inset-0 flex items-center justify-center transition-opacity pointer-events-none" style="opacity: {{ $isSelected ? '1' : '0' }}">
                                                                                                    <div class="bg-white/80 dark:bg-black/50 rounded-full w-full h-full flex items-center justify-center">
                                                                                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif
                                                                                        </button>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <!-- بخش پایین (تمام عرض: دکمه سمت راست، قیمت سمت چپ) -->
                                                            <div class="mt-2 flex items-center justify-between gap-1.5 border-t border-zinc-100 dark:border-zinc-800/60 pt-2 w-full mt-auto">
                                                                <!-- دکمه اقدام (سمت راست در RTL) -->
                                                                <div class="flex-shrink-0">
                                                                    @if($hasVariations && $assistantLevel === 2 && !$isVariantCard)
                                                                        <!-- دکمه سطح ۲ در پیام اولیه: باز کردن کارت تنوع با ایجاد پیام جدید -->
                                                                        <button wire:click="showVariantCard({{ $product['id'] }}, '{{ addslashes($product['title']) }}', '{{ $msg['id'] ?? $loop->parent->index }}')" 
                                                                                wire:key="btn-var-cmp-open-{{ $product['id'] }}-{{ $msg['id'] ?? $loop->parent->index }}"
                                                                                type="button"
                                                                                class="inline-flex items-center justify-center gap-1 px-2.5 h-7 text-[10px] font-bold text-white rounded-lg whitespace-nowrap transition-all duration-300 hover:brightness-110 active:scale-95 shadow-sm cursor-pointer" 
                                                                                style="background-color: {{ $primaryColor }}; box-shadow: 0 2px 8px -2px {{ $primaryColor }}50;">
                                                                            <span>انتخاب تنوع</span>
                                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                                            </svg>
                                                                        </button>
                                                                    @elseif($hasVariations && $assistantLevel !== 2)
                                                                        <!-- دکمه سطح ۱: لینک به صفحه محصول -->
                                                                        <a href="{{ $this->getProductUrl($product) }}" 
                                                                           wire:key="btn-var-cmp-lvl1-{{ $product['id'] }}"
                                                                           class="inline-flex items-center justify-center gap-1 px-2.5 h-7 text-[10px] font-bold text-white rounded-lg whitespace-nowrap transition-all duration-300 hover:brightness-110 active:scale-95 shadow-sm" 
                                                                           style="background-color: {{ $primaryColor }}; box-shadow: 0 2px 8px -2px {{ $primaryColor }}50;">
                                                                            <span>مشاهده</span>
                                                                            <svg class="w-3 h-3 transform -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                                            </svg>
                                                                        </a>
                                                                    @elseif($assistantLevel === 2 && $isVariantCard && $hasVariations)
                                                                        @if($activeVariant && $activeVariant['has_stock'])
                                                                            <div class="w-full min-w-[75px] max-w-[95px]" wire:key="cart-btn-cmp-var-{{ $product['id'] }}-{{ $activeVariant['variant_id'] }}">
                                                                                @livewire('market::web.add-to-cart-button', [
                                                                                    'variantId' => $activeVariant['variant_id'],
                                                                                    'vendorProductId' => $activeVariant['vendor_product_id'],
                                                                                    't' => [
                                                                                        'style' => 'background-color: ' . $primaryColor . ';',
                                                                                        'shadow_color' => $primaryColor,
                                                                                        'justify' => 'justify-start',
                                                                                    ]
                                                                                ], key('cart-btn-cmp-var-' . $product['id'] . '-' . $activeVariant['variant_id']))
                                                                            </div>
                                                                        @else
                                                                            <span class="inline-flex items-center justify-center px-2 py-1 text-[9px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-lg whitespace-nowrap">ناموجود</span>
                                                                        @endif
                                                                    @elseif($product['has_stock'] && $product['variant_id'] && $product['vendor_product_id'])
                                                                        <div class="w-full min-w-[75px] max-w-[95px]">
                                                                            @livewire('market::web.add-to-cart-button', [
                                                                                'variantId' => $product['variant_id'],
                                                                                'vendorProductId' => $product['vendor_product_id'],
                                                                                't' => [
                                                                                    'style' => 'background-color: ' . $primaryColor . ';',
                                                                                    'shadow_color' => $primaryColor,
                                                                                    'justify' => 'justify-start',
                                                                                ]
                                                                            ], key('cart-btn-widget-' . $product['id'] . '-' . $loop->index . '-' . ($msg['id'] ?? 0)))
                                                                        </div>
                                                                    @else
                                                                        <span class="inline-flex items-center justify-center px-2 py-1 text-[9px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-lg whitespace-nowrap">ناموجود</span>
                                                                    @endif
                                                                </div>

                                                                <!-- قیمت (سمت چپ در RTL) -->
                                                                <div class="flex flex-col items-end text-left">
                                                                    @php
                                                                        $priceVal = $activeVariant ? $activeVariant['formatted_price'] : $product['formatted_price'];
                                                                        $origPriceVal = $activeVariant ? $activeVariant['formatted_original_price'] : $product['formatted_original_price'];
                                                                        $discPercent = $activeVariant ? $activeVariant['discount_percent'] : $product['discount_percent'];
                                                                    @endphp
                                                                    @if($discPercent > 0)
                                                                        <div class="flex items-center gap-1">
                                                                            <span class="text-[8px] text-zinc-400 line-through">
                                                                                {{ $origPriceVal }}
                                                                            </span>
                                                                            <span class="px-0.5 py-0.2 rounded text-[7px] font-black bg-rose-500 text-white">
                                                                                {{ $discPercent }}%
                                                                            </span>
                                                                        </div>
                                                                        <span class="text-[11px] font-black text-rose-600 dark:text-rose-400">
                                                                            {{ $priceVal }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-[11px] font-black text-zinc-900 dark:text-zinc-100">
                                                                            {{ $priceVal }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($isVariantCard && !empty($msg['parent_message_key']))
                                                <div class="mt-3 flex justify-end w-full">
                                                    <button 
                                                        @click="
                                                            const el = document.getElementById('msg-cmp-row-{{ $msg['parent_message_key'] }}');
                                                            if (el) {
                                                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                                const target = el.querySelector('.flex-1') || el;
                                                                target.classList.remove('telegram-flash');
                                                                target.offsetHeight;
                                                                target.classList.add('telegram-flash');
                                                            }
                                                            const row = document.getElementById('msg-cmp-row-{{ $msg['id'] }}');
                                                            if (row) {
                                                                row.style.maxHeight = row.offsetHeight + 'px';
                                                                row.style.overflow = 'hidden';
                                                                row.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                                                                row.offsetHeight;
                                                                row.style.opacity = '0';
                                                                row.style.maxHeight = '0';
                                                                row.style.paddingTop = '0';
                                                                row.style.paddingBottom = '0';
                                                                row.style.marginTop = '0';
                                                                row.style.marginBottom = '0';
                                                            }
                                                            setTimeout(() => {
                                                                $wire.removeMessage('{{ $msg['id'] }}');
                                                            }, 350);
                                                        "
                                                        type="button"
                                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-900/60 hover:bg-zinc-50 dark:hover:bg-zinc-805 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors shadow-sm cursor-pointer w-full"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                        </svg>
                                                        <span>به محصول دیگری نیاز دارید؟</span>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- دکمه بازگشت تمام‌عرض فقط برای آخرین پیام در ویجت -->
                                        @if($loop->last && $msg['role'] === 'bot' && !$loop->first && (($msg['answer_type'] ?? '') !== 'variant_card'))
                                            <div class="mt-3 w-full">
                                                <button
                                                    x-on:click="
                                                        const row = document.getElementById('msg-cmp-row-' + '{{ $msg['id'] ?? $loop->index }}');
                                                        if (row) {
                                                            row.style.maxHeight = row.offsetHeight + 'px';
                                                            row.style.overflow = 'hidden';
                                                            row.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                                                            row.offsetHeight;
                                                            row.style.opacity = '0';
                                                            row.style.maxHeight = '0';
                                                            row.style.paddingTop = '0';
                                                            row.style.paddingBottom = '0';
                                                            row.style.marginTop = '0';
                                                            row.style.marginBottom = '0';
                                                        }
                                                        setTimeout(() => {
                                                            $wire.goBackStep('{{ $msg['id'] ?? $loop->index }}');
                                                        }, 350);
                                                    "
                                                    type="button"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300 bg-zinc-50/90 dark:bg-zinc-900/60 hover:bg-purple-50 dark:hover:bg-purple-950/40 hover:text-purple-600 dark:hover:text-purple-300 hover:border-purple-300 dark:hover:border-purple-500/50 transition-all duration-200 shadow-sm cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                    </svg>
                                                    <span>بازگشت</span>
                                                </button>
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
                        <!-- دکمه منو اصلی در ویجت شناور در صورت غیرفعال بودن تایپ دلخواه -->
                        <button
                            wire:click="resetSession"
                            type="button"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50/90 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all duration-200 shadow-sm cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>منو اصلی</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- 3. FLOATING ACTION BUTTON (Widget closed) -->
    @if(!$isStandalone && !$isWidgetOpen)
        <button
            wire:click="toggleWidget"
            class="fixed bottom-6 left-6 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-white hover:scale-105 active:scale-95 transition-transform duration-300 z-50 group"
            style="background: {{ $primaryColor ?? '#4f46e5' }}; box-shadow: 0 10px 25px -5px {{ $primaryColor ?? '#4f46e5' }}80;"
        >
            @if($botIconSvg)
                <div class="w-8 h-8 object-contain relative z-10 flex items-center justify-center">
                    {!! $botIconSvg !!}
                </div>
            @elseif($botIcon)
                <img src="{{ $botIcon }}" class="w-8 h-8 object-contain rounded-xl relative z-10" alt="{{ $botName }}" />
            @else
                <svg class="w-6 h-6 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
            @endif
        </button>
    @endif
</div>
