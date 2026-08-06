{{-- clients::layouts.client --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'پنل '.config('clients.labels.plural', 'مشتریان') }} | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'IRANYekanX', sans-serif; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased selection:bg-indigo-500 selection:text-white">
<div class="min-h-screen flex flex-col">

    {{-- هدر سایت --}}
    <header class="sticky top-0 z-40 w-full border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                {{-- بخش لوگو و عنوان --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/20">
                        {{-- آیکون کاربر --}}
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex flex-col justify-center">
                        <h1 class="text-sm font-bold text-gray-900 dark:text-white">
                            پنل {{ config('clients.labels.plural', 'مشتریان') }}
                        </h1>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                {{ config('app.name') }}
                            </span>
                    </div>
                </div>

                {{-- بخش پروفایل و خروج --}}
                @auth('client')
                    <div class="flex items-center gap-4">
                        {{-- نام کاربر (فقط دسکتاپ) --}}
                        <div class="hidden md:flex flex-col items-end">
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ auth('client')->user()->full_name }}
                                </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr font-mono">
                                    {{ auth('client')->user()->username }}
                                </span>
                        </div>

                        {{-- دیوایدر عمودی --}}
                        <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                        {{-- دکمه خروج --}}
                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit"
                                    class="group flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition-all hover:bg-red-100 hover:text-red-700 dark:bg-red-900/10 dark:text-red-400 dark:hover:bg-red-900/20"
                                    title="خروج از حساب">
                                <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="hidden sm:inline">خروج</span>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{-- محتوای اصلی --}}
    <main class="flex-1 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    {{-- فوتر --}}
    <footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-6">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. تمامی حقوق محفوظ است.
            </p>
        </div>
    </footer>
</div>

@auth('client')
    @php
        $termsEnabled     = (bool) \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.enabled', false);
        $termsVersion     = (string) \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.version', '1.0');
        $clientUser       = auth('client')->user();
        $showTermsModal   = $termsEnabled && $clientUser && !$clientUser->hasAcceptedTerms($termsVersion);

        if ($showTermsModal) {
            $termsTitle       = \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.title', 'قوانین و مقررات استفاده از پرتال');
            $termsContent     = \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.content', '');
            $termsBtnAccept   = \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.btn_accept', 'قوانین را می‌پذیرم');
            $termsBtnLater    = \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.btn_later', 'بعداً می‌خوانم');
            $termsAllowLater  = (bool) \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.allow_later', true);
            $termsForceScroll = (bool) \Modules\Clients\Entities\ClientSetting::getValue('dashboard.terms.force_scroll', true);
        }
    @endphp

    @if($showTermsModal)
        <div x-data="{
                open: true,
                loading: false,
                forceScroll: {{ $termsForceScroll ? 'true' : 'false' }},
                allowLater: {{ $termsAllowLater ? 'true' : 'false' }},
                hasScrolledToBottom: {{ $termsForceScroll ? 'false' : 'true' }},
                version: '{{ $termsVersion }}',
                init() {
                    // اگر در این session دکمه بعداً خوانده شده باشد، نشان نده
                    if (this.allowLater && sessionStorage.getItem('terms_dismissed_' + this.version)) {
                        this.open = false;
                        return;
                    }
                    this.$nextTick(() => {
                        this.checkScroll();
                    });
                },
                checkScroll() {
                    if (!this.forceScroll) {
                        this.hasScrolledToBottom = true;
                        return;
                    }
                    let el = this.$refs.termsBody;
                    if (!el) return;
                    // اگر طول متن کم باشد و نیاز به اسکرول نداشته باشد
                    if (el.scrollHeight <= el.clientHeight + 10) {
                        this.hasScrolledToBottom = true;
                    } else {
                        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 15) {
                            this.hasScrolledToBottom = true;
                        }
                    }
                },
                acceptTerms() {
                    if (!this.hasScrolledToBottom || this.loading) return;
                    this.loading = true;
                    fetch('{{ route('client.terms.accept') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.open = false;
                        } else {
                            alert(data.message || 'خطا در ثبت قوانین');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('خطایی در ارتباط با سرور رخ داد.');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                },
                readLater() {
                    if (!this.allowLater) return;
                    sessionStorage.setItem('terms_dismissed_' + this.version, 'true');
                    this.open = false;
                }
            }"
             x-show="open"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">

            {{-- پس زمینه تاریک و تار (Backdrop) --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-950/70 backdrop-blur-md transition-opacity"></div>

            {{-- کارت مودال --}}
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-right shadow-2xl transition-all w-full max-w-2xl flex flex-col max-h-[85vh]">

                    {{-- هدر مودال --}}
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/80 dark:bg-gray-900/80 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ $termsTitle }}
                                </h3>
                                <span class="text-[11px] text-gray-400 font-mono">نسخه {{ $termsVersion }}</span>
                            </div>
                        </div>

                        @if($termsAllowLater)
                            <button type="button" @click="readLater" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                    </div>

                    {{-- بدنه اصلی متنی با اسکرول --}}
                    <div x-ref="termsBody"
                         @scroll="checkScroll()"
                         class="p-6 overflow-y-auto flex-1 text-sm text-gray-700 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none space-y-4">
                        @if(!empty($termsContent))
                            {!! $termsContent !!}
                        @else
                            <p class="text-gray-500 text-center py-6">متن قوانینی ثبت نشده است.</p>
                        @endif
                    </div>

                    {{-- فوتر و دکمه‌ها --}}
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                        @if($termsForceScroll)
                            <div class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5" x-show="!hasScrolledToBottom">
                                <svg class="w-4 h-4 shrink-0 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                <span>لطفاً متن قوانین را تا انتها مطالعه کنید.</span>
                            </div>
                        @else
                            <div></div>
                        @endif

                        <div class="flex items-center gap-3 w-full sm:w-auto mr-auto justify-end">
                            @if($termsAllowLater)
                                <button type="button"
                                        @click="readLater"
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                    {{ $termsBtnLater }}
                                </button>
                            @endif

                            <button type="button"
                                    @click="acceptTerms()"
                                    :disabled="!hasScrolledToBottom || loading"
                                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none flex items-center justify-center gap-2">
                                <template x-if="loading">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </template>
                                <span>{{ $termsBtnAccept }}</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@endauth

@livewireScripts
@livewireScriptConfig
</body>
</html>
