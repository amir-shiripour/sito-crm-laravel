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
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.4), transparent 70%);
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
                init() {
                    this.scrollToBottom();
                    window.addEventListener('chatScrollToBottom', () => this.scrollToBottom());
                },
                scrollToBottom() {
                    // در حالت تمام صفحه، کل پنجره مرورگر به پایین اسکرول می‌شود
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
                        }
                    }
                }
            }"
            x-init="init()"
        >
            <!-- هدر بالا (چسبیده به صفحه با افکت بلور شیشه‌ای و پشتیبانی از پروفایل و شروع مجدد) -->
            <div class="fixed top-0 left-0 w-full p-4 flex items-center justify-between z-30 pointer-events-none">
                <!-- دکمه‌ها در سمت چپ (RTL End) -->
                <div class="pointer-events-auto mr-auto flex items-center gap-2">
                    <!-- شروع مجدد گفتگو -->
                    <button 
                        wire:click="resetSession" 
                        class="flex items-center gap-1.5 px-4 py-2 rounded-full bg-zinc-100/85 dark:bg-zinc-900/85 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-800 hover:text-indigo-650 dark:hover:text-indigo-400 transition-all shadow-sm"
                        title="شروع مجدد گفتگو"
                        wire:loading.attr="disabled"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin-hover"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                        گفتگوی جدید
                    </button>

                    <!-- دکمه بازگشت -->
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-full bg-zinc-100/85 dark:bg-zinc-900/85 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-800 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-500"><path d="m15 18-6-6 6-6"/></svg>
                        بازگشت
                    </a>
                </div>

                <!-- عنوان و پروفایل در سمت راست (RTL Start) -->
                <div class="pointer-events-auto ml-auto flex items-center gap-3 px-4 py-1.5 rounded-full bg-zinc-100/85 dark:bg-zinc-900/85 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 shadow-sm select-none">
                    @auth('client')
                        @php
                            $clientName = auth('client')->user()->full_name;
                            $initial = mb_substr($clientName, 0, 1, 'utf-8');
                        @endphp
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shadow-sm">
                            {{ $initial }}
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ $clientName }}</span>
                            <span class="text-[9px] text-zinc-400 dark:text-zinc-500">پنل مشتریان</span>
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full bg-zinc-300 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 text-xs font-bold flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">کاربر مهمان</span>
                            <span class="text-[9px] text-zinc-400 dark:text-zinc-500">مشاوره عمومی</span>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- فضای اصلی چت (جریان پیام‌ها به صورت مستند) -->
            <!-- پدینگ بالا برای رد کردن هدر، پدینگ پایین بزرگ برای رد کردن باکش ورودی متن -->
            <div class="flex-1 w-full max-w-3xl mx-auto px-4 md:px-6 pt-24 pb-48 z-10 flex flex-col gap-8">

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
                            <h2 class="text-xl md:text-2xl font-medium text-zinc-505 dark:text-zinc-400 tracking-tight">
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
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 16-4-4-4 4"/><path d="M17 21v-9"/><path d="m3 8 4-4 4 4"/><path d="M7 3v9"/></svg>',
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
                                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
                                        ];
                                    @endphp
                                    <button
                                        wire:click="sendMessage('{{ addslashes($sug) }}')"
                                        class="group text-right p-5 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all duration-300"
                                    >
                                        <div class="w-10 h-10 rounded-full bg-white dark:bg-zinc-950 shadow-sm border border-zinc-100 dark:border-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-400 mb-4 group-hover:text-indigo-500 transition-colors">
                                            {!! $icons[$index % count($icons)] !!}
                                        </div>
                                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $sug }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- CASE B: SCROLLABLE CHAT MESSAGE HISTORY -->
                @else
                    @foreach($messages as $msg)
                        @if($msg['role'] === 'user')
                            <!-- سوال کاربر: ترازبندی در راست (RTL)، همراه با حباب رنگی -->
                            <div class="flex justify-start w-full smartbot-fade-in group">
                                <div class="bg-zinc-100 dark:bg-[#252525] text-zinc-900 dark:text-zinc-100 px-6 py-4 rounded-3xl text-[15px] leading-relaxed max-w-[85%] md:max-w-[75%] font-medium">
                                    {!! nl2br(e($msg['content'])) !!}
                                </div>
                            </div>
                        @else
                            <!-- پاسخ دستیار: ترازبندی در راست (RTL)، بدون حباب، ظاهر مستند‌گونه شبیه ChatGPT -->
                            <div class="flex justify-start w-full gap-4 md:gap-6 smartbot-fade-in">
                                <!-- آواتار هوش مصنوعی -->
                                <div class="flex-shrink-0 mt-1 w-8 h-8 md:w-9 md:h-9 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#151515] flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
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
                                                <div class="flex bg-white dark:bg-[#202020] border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden hover:shadow-md transition-shadow duration-300 p-2 gap-3">
                                                    <img src="{{ $product['image'] }}" class="w-16 h-16 md:w-20 md:h-20 rounded-xl object-cover flex-shrink-0 bg-zinc-100 dark:bg-zinc-900" alt="{{ $product['title'] }}" />
                                                    <div class="flex-1 flex flex-col justify-between min-w-0 py-1">
                                                        <h4 class="text-xs md:text-sm font-bold text-zinc-900 dark:text-white truncate">{{ $product['title'] }}</h4>

                                                        <div class="flex items-center justify-between gap-1 mt-1">
                                                            <div class="flex flex-col">
                                                                @if($product['discount_percent'] > 0)
                                                                    <span class="text-[10px] text-zinc-400 line-through">
                                                                        {{ number_format($product['price']) }} ریال
                                                                    </span>
                                                                    <span class="text-xs md:text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                                                        {{ number_format($product['price'] * (1 - $product['discount_percent'] / 100)) }} ریال
                                                                    </span>
                                                                @else
                                                                    <span class="text-xs md:text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                                                        {{ number_format($product['price']) }} ریال
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if($product['has_stock'])
                                                                <button
                                                                    wire:click="addToCart({{ $product['id'] }})"
                                                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-95 rounded-lg transition-all duration-200"
                                                                >
                                                                    خرید
                                                                </button>
                                                            @else
                                                                <span class="text-[10px] text-red-500 font-semibold bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded">ناموجود</span>
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
                            <div class="flex-shrink-0 mt-1 w-8 h-8 md:w-9 md:h-9 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#151515] flex items-center justify-center text-zinc-400 shadow-sm">
                                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0 pt-3">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">هوش مصنوعی در حال پردازش است...</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- نوار شناور و ثابت پایین صفحه برای دریافت متن -->
            <div class="fixed bottom-0 left-0 w-full z-20 bg-gradient-to-t from-white via-white to-transparent dark:from-[#151515] dark:via-[#151515] dark:to-transparent pt-12 pb-6">
                <div class="max-w-3xl mx-auto w-full px-4 flex flex-col gap-3">

                    <!-- پیشنهادات جمع و جور در بالا -->
                    @if(!empty($suggestions) && count($messages) > 1)
                        <div class="flex overflow-x-auto smartbot-scrollbar pb-1 gap-2 mask-linear-fade justify-start px-2">
                            @foreach($suggestions as $sug)
                                <button
                                    wire:click="sendMessage('{{ addslashes($sug) }}')"
                                    class="whitespace-nowrap flex-shrink-0 px-4 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-300 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border border-zinc-200 dark:border-zinc-700/50 rounded-2xl hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all"
                                >
                                    {{ $sug }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- فیلد ورودی متن به سبک شیشه‌ای و مدرن یا قفل هوشمند -->
                    @if($allowCustomTyping)
                        <div class="relative bg-zinc-105 dark:bg-[#252525] border border-zinc-200 dark:border-zinc-800/80 rounded-[32px] p-2 flex items-end shadow-sm focus-within:ring-2 focus-within:ring-indigo-500/20 transition-all duration-300">

                            <!-- دکمه فایل/گیره (اختیاری) -->
                            <button type="button" class="p-2.5 text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-300 transition-colors mb-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            </button>

                            <textarea
                                x-model="inputValue"
                                x-ref="inputField"
                                placeholder="از دستیار هوشمند بپرسید..."
                                class="flex-1 bg-transparent border-none focus:ring-0 text-zinc-900 dark:text-zinc-100 resize-none py-3 px-2 text-[15px] leading-relaxed outline-none"
                                style="height: 56px;"
                                x-on:keydown.enter.prevent="if (!e.shiftKey) { submitForm(); }"
                                @input="$refs.inputField.style.height = '56px'; $refs.inputField.style.height = Math.min($refs.inputField.scrollHeight, 200) + 'px';"
                                :disabled="$wire.isThinking"
                            ></textarea>

                            <!-- دکمه ارسال -->
                            <button
                                @click="submitForm()"
                                class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center transition-all duration-300 mb-0.5 ml-1"
                                :class="inputValue ? 'bg-indigo-600 text-white shadow-md' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-400'"
                                :disabled="$wire.isThinking || !inputValue"
                            >
                                <template x-if="$wire.isThinking">
                                    <div class="w-4 h-4 bg-zinc-500 dark:bg-zinc-400 rounded-sm animate-spin" style="animation-duration: 2s;"></div>
                                </template>
                                <template x-if="!$wire.isThinking">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="rtl:rotate-180"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </template>
                            </button>
                        </div>
                    @else
                        <div class="w-full py-4 px-6 text-center bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl text-zinc-500 dark:text-zinc-400 select-none">
                            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">💬 قابلیت ارسال پیام دلخواه غیرفعال است</span>
                            <p class="mt-1 text-[11px] text-zinc-400 dark:text-zinc-500">لطفاً برای مشاوره و دریافت پاسخ، از سوالات متداول یا پیشنهادی بالا استفاده کنید.</p>
                        </div>
                    @endif

                    <p class="text-center text-[10px] text-zinc-400 dark:text-zinc-500 font-medium">
                        هوش مصنوعی ممکن است اشتباه کند، اطلاعات مهم را چک کنید.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. STANDARD COMPACT FLOATING CHAT PANEL -->
    @if(!$isStandalone && $isWidgetOpen)
        <div
            class="fixed bottom-24 left-6 w-[380px] h-[600px] z-50 bg-white dark:bg-[#151515] border border-zinc-200 dark:border-zinc-800/80 rounded-3xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300"
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
            <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-white/80 dark:bg-[#151515]/80 backdrop-blur-md z-20 absolute top-0 w-full">
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
            <div class="flex-grow overflow-y-auto pt-20 p-4 space-y-6 bg-zinc-50/50 dark:bg-[#121212]" x-ref="chatBody">
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
                            <div class="max-w-[85%] bg-indigo-650 text-white px-4 py-2.5 rounded-2xl rounded-tr-sm text-[13px] leading-relaxed shadow-sm">
                                {!! nl2br(e($msg['content'])) !!}
                            </div>
                        @else
                            <div class="flex gap-2 w-full">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-indigo-650 mt-1">
                                    <svg xmlns="http://www.w3.org/2050/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/></svg>
                                </div>
                                <div class="flex-1 text-[13px] text-zinc-800 dark:text-zinc-200 leading-relaxed pt-1">
                                    {!! nl2br(e($msg['content'])) !!}

                                    <!-- Products in Widget -->
                                    @if(!empty($msg['products']))
                                        <div class="grid grid-cols-1 gap-2 mt-3">
                                            @foreach($msg['products'] as $product)
                                                <div class="flex p-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl hover:shadow-sm">
                                                    <img src="{{ $product['image'] }}" class="w-12 h-12 rounded-lg object-cover" alt="" />
                                                    <div class="flex-1 pr-3 py-0.5 flex flex-col justify-between">
                                                        <h4 class="text-xs font-bold truncate text-zinc-900 dark:text-white">{{ $product['title'] }}</h4>
                                                        <span class="text-xs font-bold text-indigo-650">{{ number_format($product['price']) }}</span>
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
            <div class="p-3 bg-white dark:bg-[#151515] border-t border-zinc-100 dark:border-zinc-800">
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
