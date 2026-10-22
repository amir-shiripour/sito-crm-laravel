<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>سامانه هوشمند اعزام تکنسین و خدمات فنی نصاب‌باشی | استان مازندران</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Radar Animation Rings */
        @keyframes radar-pulse {
            0% {
                transform: scale(0.1);
                opacity: 0.9;
            }
            70% {
                opacity: 0.35;
            }
            100% {
                transform: scale(2.4);
                opacity: 0;
            }
        }
        .radar-wave-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid #2563eb;
            background: rgba(37, 99, 235, 0.15);
            animation: radar-pulse 2.2s cubic-bezier(0.2, 0.8, 0.4, 1) infinite;
            pointer-events: none;
        }

        /* Leaflet Controls Styling */
        .leaflet-control-zoom {
            display: none !important;
        }
        .leaflet-control-attribution {
            background: rgba(255, 255, 255, 0.7) !important;
            font-size: 9px !important;
            border-radius: 6px !important;
            margin: 4px !important;
        }
        .dark .leaflet-control-attribution {
            background: rgba(15, 23, 42, 0.8) !important;
            color: #94a3b8 !important;
        }

        /* Instant Touch & GPU Hardware Acceleration for Zero-Latency Panning */
        #customerMap {
            touch-action: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
            -webkit-tap-highlight-color: transparent;
            will-change: transform;
            transform: translateZ(0);
        }
        .leaflet-container {
            touch-action: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
            cursor: grab;
        }
        .leaflet-container:active {
            cursor: grabbing;
        }
        .leaflet-map-pane {
            will-change: transform;
            transform: translateZ(0);
        }
        .center-pin-wrapper,
        .center-pin-wrapper * {
            pointer-events: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
        }
    </style>
</head>
<body class="h-dvh w-screen overflow-hidden bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans selection:bg-indigo-600 selection:text-white select-none relative"
      x-data="customerAppHandler(@js($mapConfig), @js($onlineTechnicians), @js($mazandaranCities), @js(Auth::guard('client')->user() ? Auth::guard('client')->user()->addresses()->latest()->take(6)->get() : []))"
      x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. FULL-VIEWPORT MAP LAYER                                                --}}
    {{-- ========================================================================= --}}
    <div id="customerMap" class="w-full h-full absolute inset-0 z-0 bg-slate-200 dark:bg-slate-850"></div>

    {{-- ========================================================================= --}}
    {{-- 2. CENTER PIN: SNAPP-NATIVE BLUE TARGET BULLSEYE PIN                      --}}
    {{-- ========================================================================= --}}
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center z-10"
         x-show="!isTrackingActive && !isOriginConfirmed"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="center-pin-wrapper relative flex flex-col items-center select-none -translate-y-4 pointer-events-none">
            {{-- Floating status address pill above pin --}}
            <div :class="isMoving ? 'opacity-0 -translate-y-2 scale-95' : 'opacity-100 translate-y-0 scale-100'"
                 class="transition-transform transition-opacity duration-150 ease-out mb-2 px-3.5 py-1.5 rounded-full bg-slate-900 dark:bg-slate-850 text-white text-[11px] font-bold shadow-lg border border-slate-700/80 flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 rounded-full" :class="isGeocoding ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"></span>
                <span x-text="selectedLocationLabel || 'محل اعزام تکنسین (نقشه را تنظیم کنید)'"></span>
            </div>
            
            {{-- Snapp Blue Concentric Target Dot with Animated Wave --}}
            <div class="relative flex items-center justify-center transition-transform duration-150 ease-out"
                 :class="isMoving ? '-translate-y-3 scale-110' : 'translate-y-0 scale-100'">
                {{-- Outer Pulse Ring --}}
                <span class="absolute w-10 h-10 rounded-full bg-blue-500/20 dark:bg-blue-400/20 ring-2 ring-blue-500/30 pointer-events-none"></span>
                
                {{-- Middle White Circular Ring --}}
                <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-900 shadow-2xl border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center z-10">
                    {{-- Inner Vivid Blue Core Dot --}}
                    <span class="w-4 h-4 rounded-full bg-blue-600 dark:bg-blue-500 shadow-sm"></span>
                </div>
            </div>

            {{-- Pin Connector Line & Road Shadow --}}
            <div class="w-0.5 h-3 bg-slate-800 dark:bg-slate-300 transition-opacity duration-150 ease-out"
                 :class="isMoving ? 'opacity-0' : 'opacity-100'"></div>
            <div :class="isMoving ? 'scale-50 opacity-20' : 'scale-100 opacity-60'"
                 class="transition-transform transition-opacity duration-150 ease-out w-4 h-1.5 bg-slate-950 rounded-full blur-[1px]"></div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. FLOATING TOP HEADER: EXACT SNAPP TRIPLE FLOATING CONTROLS (RTL ORDER)   --}}
    {{-- ========================================================================= --}}
    <header class="fixed top-3 inset-x-3 sm:top-5 sm:inset-x-6 z-20 pointer-events-none">
        <div class="max-w-md mx-auto flex items-center justify-between gap-2">
            
            {{-- Element 1 (Right in RTL): Saved Addresses OR Snapp Back Arrow (when in Step 2) --}}
            <button type="button" 
                    @click="currentStep > 1 ? $dispatch('return-to-map') : addressesModalOpen = true"
                    class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
                    :title="currentStep > 1 ? 'بازگشت به نقشه' : 'آدرس‌های منتخب'">
                <template x-if="currentStep > 1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </template>
                <template x-if="currentStep <= 1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </template>
            </button>

            {{-- Element 2 (Center): Segmented Capsule (برای خودم | برای دیگری) --}}
            <div class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl rounded-full p-1 border border-slate-200/80 dark:border-slate-800 flex items-center font-sans shrink-0">
                <button type="button" @click="orderFor = 'other'; $dispatch('set-order-for', 'other')"
                        :class="orderFor === 'other' ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs transition-all whitespace-nowrap">
                    برای دیگری
                </button>
                <button type="button" @click="orderFor = 'self'; $dispatch('set-order-for', 'self')"
                        :class="orderFor === 'self' ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs transition-all whitespace-nowrap">
                    برای خودم
                </button>
            </div>

            {{-- Element 3 (Left in RTL): User Profile Circular Button --}}
            @php
                $loggedClient = Auth::guard('client')->user();
            @endphp
            @if($loggedClient)
            <a href="{{ route('client.dashboard') }}"
               class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
               title="پرتال مشتریان">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
            @else
            <button type="button" @click="$dispatch('open-customer-auth')"
                    class="pointer-events-auto w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95 shrink-0"
                    title="ورود / ثبت‌نام">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </button>
            @endif

        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 4. FLOATING GPS / LOCATE ME BUTTON (RIGHT-BOTTOM AS REQUESTED)            --}}
    {{-- ========================================================================= --}}
    <div :class="sheetExpanded ? 'bottom-[330px] sm:bottom-[340px]' : (currentStep >= 2 ? 'bottom-24' : 'bottom-44 sm:bottom-48')"
         class="fixed right-4 sm:right-6 z-20 transition-all duration-300 ease-out">
        <button type="button" @click="locateMe()"
                :class="isLocating ? 'ring-4 ring-indigo-500/30 animate-spin' : ''"
                title="موقعیت فعلی من"
                class="w-12 h-12 rounded-full bg-white/95 dark:bg-slate-850/95 backdrop-blur-md shadow-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
            </svg>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 5. FLOATING BOTTOM SHEET (EXACT SNAPP EXPERIENCE)                         --}}
    {{-- ========================================================================= --}}
    <section class="fixed inset-x-0 bottom-0 z-30 lg:top-20 lg:bottom-6 lg:right-6 lg:left-auto lg:w-[460px] pointer-events-none transition-all duration-300 ease-out">
        <div class="pointer-events-auto bg-white/95 dark:bg-slate-850/95 backdrop-blur-2xl rounded-t-[32px] lg:rounded-3xl border border-slate-200/90 dark:border-slate-750 shadow-2xl flex flex-col transition-all duration-300 ease-out"
             :class="{
                 'h-auto max-h-[85vh] lg:h-auto lg:max-h-[85vh]': sheetExpanded,
                 'h-[76px] max-h-[76px] lg:h-auto lg:max-h-full': !sheetExpanded && currentStep >= 2,
                 'h-auto max-h-[45vh] lg:h-auto lg:max-h-full': !sheetExpanded && currentStep < 2
             }"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd()">
            
            {{-- Peek Bar: Mobile only, visible when user collapses sheet in Step >= 2 --}}
            <div x-show="currentStep >= 2 && !sheetExpanded"
                 @click="sheetExpanded = true"
                 class="lg:hidden px-4 py-2.5 cursor-pointer flex flex-col justify-between h-[76px] select-none">
                {{-- Drag Handle --}}
                <div class="flex justify-center -mt-0.5">
                    <span class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-600 animate-pulse"></span>
                </div>
                
                {{-- Compact peek row --}}
                <div class="flex items-center justify-between gap-3 pt-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <div class="min-w-0">
                            <div class="text-xs font-black text-slate-800 dark:text-slate-100 truncate">
                                محل اعزام: <span x-text="confirmedOrigin?.locationText || selectedLocationLabel"></span>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold truncate">
                                برای مشاهده و تکمیل درخواست بالا بکشید
                            </div>
                        </div>
                    </div>

                    <button type="button" @click.stop="sheetExpanded = true"
                            class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 flex items-center gap-1 transition active:scale-95">
                        <span>ادامه</span>
                        <span class="text-xs">⌃</span>
                    </button>
                </div>
            </div>

            {{-- Full Content Area: Wizard and details --}}
            <div :class="(currentStep >= 2 && !sheetExpanded) ? 'hidden lg:flex lg:flex-col lg:flex-1 lg:min-h-0' : 'flex flex-col flex-1 min-h-0'"
                 class="transition-all duration-200">
                <div class="p-3 sm:p-5 custom-scrollbar flex-1"
                     :class="sheetExpanded ? 'overflow-y-auto' : (currentStep >= 2 ? 'overflow-hidden' : 'overflow-visible')">
                    @livewire('fieldservice::order-wizard')
                </div>

                {{-- Trust Footnote --}}
                <div class="px-5 py-2.5 bg-slate-50/80 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 shrink-0"
                     :class="!sheetExpanded ? 'hidden lg:flex' : 'flex'">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">گارانتی رسمی خدمات نصاب‌باشی</span>
                    </div>
                    <div class="font-medium">
                        اعزام تکنسین احراز هویت شده
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 6. MODALS & SLIDE-OVERS (PROFILE, ADDRESSES & SEARCH)                     --}}
    {{-- ========================================================================= --}}

    {{-- PROFILE & SETTINGS MODAL --}}
    <div x-show="profileModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="profileModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">حساب کاربری نصاب‌باشی</h3>
                <button type="button" @click="profileModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            @if($loggedClient)
            <div class="p-3.5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-500/15 border border-indigo-200/80 dark:border-indigo-500/30 flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-base font-black shadow-md">
                    {{ mb_substr($loggedClient->full_name ?: 'ک', 0, 1) }}
                </span>
                <div class="min-w-0">
                    <div class="font-black text-sm text-slate-900 dark:text-white truncate">{{ $loggedClient->full_name ?: 'کاربر گرامی' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-sans mt-0.5" dir="ltr">{{ $loggedClient->phone }}</div>
                </div>
            </div>

            <div class="space-y-1 text-xs">
                <a href="{{ route('client.dashboard') }}" target="_blank"
                   class="flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <span>📊</span>
                    <span>داشبورد پرتال کارفرمایان</span>
                </a>
                <a href="{{ route('client.orders.index') }}" target="_blank"
                   class="flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <span>📦</span>
                    <span>سفارش‌های من و سوابق</span>
                </a>
                <button type="button" @click="$dispatch('toggle-order-mode'); profileModalOpen = false;"
                        class="w-full text-right flex items-center gap-2.5 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition font-bold">
                    <span>🔍</span>
                    <span>پیگیری سفارش با کد رهگیری</span>
                </button>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                {{-- Theme Switcher --}}
                <button type="button" @click="toggleTheme()" class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 transition">
                    <span x-show="isDark">☀️ تم روز</span>
                    <span x-show="!isDark">🌙 تم شب</span>
                </button>

                {{-- Logout --}}
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">
                        خروج از حساب 🚪
                    </button>
                </form>
            </div>
            @else
            <div class="text-center py-3 space-y-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">شما هنوز وارد حساب کاربری خود نشده‌اید.</p>
                <button type="button" @click="profileModalOpen = false; $dispatch('open-customer-auth')"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition">
                    ورود / ثبت‌نام سریع با شماره همراه
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-center">
                <button type="button" @click="toggleTheme()" class="text-xs font-bold text-slate-600 dark:text-slate-400">
                    <span x-show="isDark">☀️ تم روز</span>
                    <span x-show="!isDark">🌙 تم شب</span>
                </button>
            </div>
            @endif

        </div>
    </div>

    {{-- SAVED ADDRESSES & CITIES MODAL --}}
    <div x-show="addressesModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="addressesModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">آدرس‌های منتخب و تغییر شهر</h3>
                <button type="button" @click="addressesModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            {{-- Saved Client Addresses --}}
            <div>
                <div class="text-[11px] font-bold text-slate-400 mb-2">آدرس‌های ذخیره شده من:</div>
                <div class="space-y-1.5 max-h-40 overflow-y-auto custom-scrollbar">
                    <template x-for="addr in savedAddresses" :key="addr.id">
                        <button type="button" @click="chooseSavedAddress(addr)"
                                class="w-full text-right p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-500/40 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between transition">
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-800 dark:text-slate-100 truncate" x-text="addr.title || 'آدرس من'"></div>
                                <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="addr.address"></div>
                            </div>
                            <span class="text-indigo-600 dark:text-indigo-400 text-xs font-bold shrink-0 mr-2">انتخاب 📍</span>
                        </button>
                    </template>
                    <div x-show="savedAddresses.length === 0" class="text-xs text-slate-400 text-center py-2">
                        هنوز آدرس ذخیره شده‌ای ندارید.
                    </div>
                </div>
            </div>

            {{-- Covered Cities Quick Switch --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="text-[11px] font-bold text-slate-400 mb-2">تغییر سریع شهر (مناطق فعال تحت پوشش):</div>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach($mazandaranCities as $cName => $cData)
                    <button type="button" @click="changeCity('{{ $cName }}'); addressesModalOpen = false;"
                            :class="currentCity === '{{ $cName }}' ? 'bg-indigo-600 text-white font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-700 font-bold'"
                            class="py-2 px-2 rounded-xl text-xs text-center transition">
                        {{ $cData['name'] }}
                    </button>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ADDRESS SEARCH MODAL --}}
    <div x-show="searchModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 bg-slate-950/60 backdrop-blur-xs font-sans">
        <div @click.outside="searchModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 space-y-4">
            
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white">جستجوی آدرس و خیابان</h3>
                <button type="button" @click="searchModalOpen = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">✕</button>
            </div>

            <div>
                <input type="text" x-model="searchQuery" @input="performPlaceSearch()" placeholder="نام خیابان، میدان یا محله در مازندران..."
                       class="w-full h-12 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="space-y-1.5 max-h-56 overflow-y-auto custom-scrollbar">
                <template x-for="place in searchResults" :key="place.name">
                    <button type="button" @click="selectPlace(place)"
                            class="w-full text-right p-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-between text-xs transition">
                        <span class="font-bold" x-text="place.name"></span>
                        <span class="text-slate-400 text-[10px]" x-text="place.city"></span>
                    </button>
                </template>
                <div x-show="searchResults.length === 0" class="text-xs text-slate-400 text-center py-4">
                    نام محله، خیابان یا میدان مورد نظر خود را تایپ کنید.
                </div>
            </div>

        </div>
    </div>

    {{-- Fast Customer Auth Modal (Snapp / Tap30 Style with SMS OTP & Password) --}}
    @livewire('fieldservice::customer-auth-modal')

    {{-- Leaflet Map & App Handler Script --}}
    <script>
        document.addEventListener('livewire:init', () => {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            window.location.reload();
                        }
                    });
                });
            }
        });

        window.addEventListener('client-authenticated', () => {
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        window.addEventListener('client-logged-out', () => {
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });

        // Non-reactive raw Leaflet objects to prevent Alpine Proxy traversal overhead
        let rawMap = null;
        let rawTechMarkersLayer = null;
        let rawRadarCircleLayer = null;
        let rawOriginMarker = null;

        document.addEventListener('alpine:init', () => {
            Alpine.data('customerAppHandler', (config, technicians, cities, savedAddresses) => ({
                get map() { return rawMap; },
                set map(val) { rawMap = val; },
                get techMarkersLayer() { return rawTechMarkersLayer; },
                set techMarkersLayer(val) { rawTechMarkersLayer = val; },
                get radarCircleLayer() { return rawRadarCircleLayer; },
                set radarCircleLayer(val) { rawRadarCircleLayer = val; },
                get originMarker() { return rawOriginMarker; },
                set originMarker(val) { rawOriginMarker = val; },
                config: config,
                technicians: technicians || [],
                cities: cities || {},
                savedAddresses: savedAddresses || [],
                currentCity: 'بابل',
                isMoving: false,
                isLocating: false,
                isGeocoding: false,
                isTrackingActive: false,
                selectedLocationLabel: 'مازندران، بابل',
                isDark: document.documentElement.classList.contains('dark'),
                
                // Snapp App States
                orderFor: 'self',
                profileModalOpen: false,
                addressesModalOpen: false,
                searchModalOpen: false,
                searchQuery: '',
                searchResults: [],

                // Confirmed Origin on Map
                isOriginConfirmed: false,
                confirmedOrigin: null,
                currentStep: 1,

                // Mobile Bottom Sheet State
                sheetExpanded: false,
                touchStartY: 0,
                touchStartX: 0,
                touchEndY: 0,
                touchEndX: 0,

                toggleSheet() {
                    this.sheetExpanded = !this.sheetExpanded;
                },

                openSheet() {
                    this.sheetExpanded = true;
                },

                closeSheet() {
                    this.sheetExpanded = false;
                },

                handleTouchStart(e) {
                    if (e.touches && e.touches.length > 0) {
                        this.touchStartY = e.touches[0].clientY;
                        this.touchStartX = e.touches[0].clientX;
                    }
                },

                handleTouchMove(e) {
                    if (e.touches && e.touches.length > 0) {
                        this.touchEndY = e.touches[0].clientY;
                        this.touchEndX = e.touches[0].clientX;
                    }
                },

                handleTouchEnd() {
                    if (!this.touchStartY || !this.touchEndY) return;
                    const deltaY = this.touchStartY - this.touchEndY;
                    const deltaX = Math.abs((this.touchStartX || 0) - (this.touchEndX || 0));
                    
                    if (Math.abs(deltaY) > 35 && Math.abs(deltaY) > deltaX) {
                        if (deltaY > 35) {
                            // Swipe Up -> Expand
                            this.sheetExpanded = true;
                        } else if (deltaY < -35) {
                            // Swipe Down -> Collapse to peek bar
                            this.sheetExpanded = false;
                        }
                    }
                    this.touchStartY = 0;
                    this.touchEndY = 0;
                },

                initMap() {
                    const defaultLat = this.config.center?.lat || 36.5444;
                    const defaultLng = this.config.center?.lng || 52.6780;
                    const zoom = this.config.zoom || 16;

                    // Strict geographic boundaries strictly locked to covered cities and provinces
                    const sw = this.config.bounds?.southWest || [36.35, 52.45];
                    const ne = this.config.bounds?.northEast || [36.75, 52.95];
                    const coveredBounds = L.latLngBounds(
                        L.latLng(sw[0], sw[1]),
                        L.latLng(ne[0], ne[1])
                    );

                    // Initialize raw Leaflet map with completely frictionless drag start
                    rawMap = L.map('customerMap', {
                        center: [defaultLat, defaultLng],
                        zoom: zoom,
                        minZoom: this.config.min_zoom || 10,
                        maxZoom: this.config.max_zoom || 19,
                        preferCanvas: true,
                        zoomControl: false,
                        attributionControl: true,
                        fadeAnimation: true,
                        zoomAnimation: true,
                        inertia: true,
                        inertiaDeceleration: 3400,
                        inertiaMaxSpeed: 2000,
                    });

                    // Add map tile layer with STRICT covered bounds:
                    const subdomains = this.config.subdomains || ['a', 'b', 'c'];
                    L.tileLayer(this.config.tile_url, {
                        maxZoom: this.config.max_zoom || 19,
                        minZoom: this.config.min_zoom || 10,
                        bounds: coveredBounds,
                        subdomains: subdomains,
                        keepBuffer: 6,
                        updateWhenIdle: false,
                        updateWhenZooming: true,
                        attribution: '&copy; نصاب‌باشی | ' + (this.config.name || 'GIS'),
                    }).addTo(rawMap);

                    // Layer for nearby technician markers
                    rawTechMarkersLayer = L.layerGroup().addTo(rawMap);
                    rawRadarCircleLayer = L.layerGroup().addTo(rawMap);

                    this.renderNearbyTechnicians();

                    // Map Move Events for Snapp-like center pin
                    rawMap.on('movestart', () => {
                        // Immediately abort pending geocoding requests to completely free up CPU & network
                        if (this._abortController) {
                            this._abortController.abort();
                            this._abortController = null;
                        }
                        if (this._geocodeTimeout) {
                            clearTimeout(this._geocodeTimeout);
                        }
                        if (!this.isMoving) {
                            this.isMoving = true;
                        }
                    });

                    rawMap.on('moveend', () => {
                        this.isMoving = false;
                        const center = rawMap.getCenter();

                        // Smooth boundary protection: gently slide back if pulled outside coverage area
                        if (!coveredBounds.contains(center)) {
                            rawMap.panInsideBounds(coveredBounds, { animate: true, duration: 0.35 });
                            this.selectedLocationLabel = 'موقعیت خارج از محدوده تحت پوشش خدمات';
                            return;
                        }

                        this.onLocationChanged(center.lat, center.lng);
                    });

                    // Initial location sync
                    this.onLocationChanged(defaultLat, defaultLng);

                    // Listeners from Livewire
                    window.addEventListener('order-step-changed', (e) => {
                        if (e.detail?.step) {
                            this.currentStep = e.detail.step;
                            if (e.detail.step > 1) {
                                this.sheetExpanded = true;
                            } else {
                                this.sheetExpanded = false;
                            }
                        }
                    });

                    const handleLocationConfirmed = (e) => {
                        this.isOriginConfirmed = true;
                        this.confirmedOrigin = {
                            lat: e.detail.lat,
                            lng: e.detail.lng,
                            locationText: e.detail.locationText
                        };
                        this.renderConfirmedOrigin(e.detail.lat, e.detail.lng, e.detail.locationText);
                    };

                    const handleLocationCleared = () => {
                        this.isOriginConfirmed = false;
                        this.confirmedOrigin = null;
                        if (this.originMarker) {
                            this.map.removeLayer(this.originMarker);
                            this.originMarker = null;
                        }
                    };

                    window.addEventListener('origin-confirmed', handleLocationConfirmed);
                    window.addEventListener('location-confirmed', handleLocationConfirmed);
                    window.addEventListener('origin-cleared', handleLocationCleared);
                    window.addEventListener('location-cleared', handleLocationCleared);

                    window.addEventListener('toggle-sheet', () => {
                        this.toggleSheet();
                    });

                    window.addEventListener('expand-sheet', () => {
                        this.sheetExpanded = true;
                    });

                    window.addEventListener('collapse-sheet', () => {
                        this.sheetExpanded = false;
                    });

                    window.addEventListener('open-search-modal', () => {
                        this.searchModalOpen = true;
                    });

                    window.addEventListener('center-map-on-coords', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.map.setView([e.detail.lat, e.detail.lng], e.detail.zoom || 16, { animate: true });
                        }
                    });

                    window.addEventListener('start-radar-animation', (e) => {
                        this.isTrackingActive = true;
                        this.drawRadarRipple(e.detail.lat, e.detail.lng, e.detail.radiusKm || 3);
                    });

                    window.addEventListener('technician-assigned', (e) => {
                        if (e.detail?.lat && e.detail?.lng) {
                            this.renderAssignedTechnician(e.detail.lat, e.detail.lng, e.detail.name, e.detail.mobile);
                        }
                    });
                },

                changeCity(cityName) {
                    if (!this.cities[cityName]) return;
                    this.currentCity = cityName;
                    const c = this.cities[cityName];
                    this.map.setView([c.lat, c.lng], c.zoom || 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${c.name}`;
                    this.$dispatch('city-selected', cityName);
                },

                chooseSavedAddress(addr) {
                    if (!addr || !addr.lat || !addr.lng) return;
                    this.map.setView([addr.lat, addr.lng], 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${addr.city || this.currentCity} - ${addr.title || addr.address}`;
                    if (addr.city) this.currentCity = addr.city;
                    this.addressesModalOpen = false;
                },

                performPlaceSearch() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) {
                        this.searchResults = [];
                        return;
                    }

                    const samplePlaces = [
                        { name: 'میدان جهاد (اوقاف)', city: 'بابل', lat: 36.5444, lng: 52.6780 },
                        { name: 'خیابان شریعتی', city: 'بابل', lat: 36.5490, lng: 52.6820 },
                        { name: 'میدان باغ فردوس', city: 'بابل', lat: 36.5410, lng: 52.6740 },
                        { name: 'بلوار جانبازان', city: 'بابل', lat: 36.5560, lng: 52.6680 },
                        { name: 'میدان ساعت', city: 'ساری', lat: 36.5659, lng: 53.0586 },
                        { name: 'بلوار خزر', city: 'ساری', lat: 36.5750, lng: 53.0700 },
                        { name: 'میدان ۱۷ شهریور', city: 'آمل', lat: 36.4676, lng: 52.3507 },
                        { name: 'بلوار امام رضا', city: 'آمل', lat: 36.4750, lng: 52.3600 },
                    ];

                    this.searchResults = samplePlaces.filter(p => p.name.toLowerCase().includes(q) || p.city.toLowerCase().includes(q));
                },

                selectPlace(place) {
                    this.map.setView([place.lat, place.lng], 16, { animate: true });
                    this.selectedLocationLabel = `مازندران، ${place.city} - ${place.name}`;
                    this.currentCity = place.city;
                    this.searchModalOpen = false;
                    this.searchQuery = '';
                },

                async onLocationChanged(lat, lng) {
                    if (this._geocodeTimeout) {
                        clearTimeout(this._geocodeTimeout);
                    }

                    // Cancel previous in-flight fetch immediately
                    if (this._abortController) {
                        this._abortController.abort();
                        this._abortController = null;
                    }

                    // Boundary Guard: Ignore coordinates outside covered region bounds
                    const sw = this.config.bounds?.southWest || [25.0, 44.0];
                    const ne = this.config.bounds?.northEast || [39.8, 63.5];
                    if (lat < sw[0] || lat > ne[0] || lng < sw[1] || lng > ne[1]) {
                        this.selectedLocationLabel = 'موقعیت خارج از محدوده تحت پوشش خدمات';
                        this.isGeocoding = false;
                        return;
                    }

                    // In-memory instant client cache (~11m resolution: 4 decimal places)
                    const cacheKey = `${lat.toFixed(4)},${lng.toFixed(4)}`;
                    if (this._geoCache && this._geoCache[cacheKey]) {
                        const cached = this._geoCache[cacheKey];
                        this.selectedLocationLabel = cached.formatted_address;
                        if (cached.city) this.currentCity = cached.city;
                        this.isGeocoding = false;
                        return;
                    }

                    this.isGeocoding = true;

                    // Fast debounce (250ms) after drag ends: eliminates lagging & queued requests
                    this._geocodeTimeout = setTimeout(async () => {
                        this._abortController = new AbortController();
                        const signal = this._abortController.signal;

                        try {
                            const res = await fetch(`/fieldservice/api/reverse-geocode?lat=${lat}&lng=${lng}`, { signal });
                            if (res.ok) {
                                const data = await res.json();
                                if (data.formatted_address) {
                                    if (!this._geoCache) this._geoCache = {};
                                    this._geoCache[cacheKey] = data;
                                    this.selectedLocationLabel = data.formatted_address;
                                    if (data.city) {
                                        this.currentCity = data.city;
                                    }
                                }
                            }
                        } catch (e) {
                            if (e.name === 'AbortError') {
                                return; // Quietly ignore aborted request
                            }
                            if (!this.selectedLocationLabel || this.selectedLocationLabel.includes('در حال')) {
                                this.selectedLocationLabel = `مازندران، ${this.currentCity}`;
                            }
                        } finally {
                            this.isGeocoding = false;
                        }
                    }, 250);
                },

                locateMe() {
                    if (!navigator.geolocation) {
                        alert('مرورگر شما از قابلیت مکان‌یابی پشتیبانی نمی‌کند.');
                        return;
                    }

                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.isLocating = false;
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            this.map.setView([lat, lng], 16, { animate: true });
                        },
                        (err) => {
                            this.isLocating = false;
                            alert('دسترسی به موقعیت مکانی داده نشد یا GPS خاموش است.');
                        },
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                },

                zoomIn() {
                    if (this.map) this.map.zoomIn();
                },

                zoomOut() {
                    if (this.map) this.map.zoomOut();
                },

                toggleTheme() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                },

                renderNearbyTechnicians() {
                    if (!this.techMarkersLayer) return;
                    this.techMarkersLayer.clearLayers();

                    this.technicians.forEach(tech => {
                        let lat = tech.lat;
                        let lng = tech.lng;

                        if (!lat || !lng) {
                            lat = 36.5444 + (Math.random() - 0.5) * 0.04;
                            lng = 52.6780 + (Math.random() - 0.5) * 0.04;
                        }

                        const isBusy = !!tech.is_busy;
                        const pinColor = isBusy ? '#f59e0b' : '#10b981';

                        const techIcon = L.divIcon({
                            className: 'custom-nearby-tech',
                            html: `
                                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                    <div style="background-color: ${pinColor}; width: 34px; height: 34px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 14px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
                                        🚗
                                    </div>
                                    <div style="position: absolute; bottom: -4px; width: 8px; height: 8px; background: ${pinColor}; border-radius: 50%; border: 1.5px solid white;"></div>
                                </div>
                            `,
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                        });

                        const marker = L.marker([lat, lng], { icon: techIcon }).addTo(this.techMarkersLayer);
                        marker.bindPopup(`
                            <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px; min-width: 160px;">
                                <div style="font-weight: 900; font-size: 13px; color: #0f172a;">${tech.first_name} ${tech.last_name}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">تکنسین رسمی در ${tech.city || 'مازندران'}</div>
                                <div style="margin-top: 6px; display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; background: ${isBusy ? '#fef3c7; color: #92400e;' : '#dcfce7; color: #166534;'}">
                                    ${isBusy ? 'در حال ماموریت' : '🟢 آماده اعزام فوری'}
                                </div>
                            </div>
                        `);
                    });
                },

                drawRadarRipple(lat, lng, radiusKm) {
                    if (!this.radarCircleLayer) return;
                    this.radarCircleLayer.clearLayers();

                    // Main Search Zone Circle
                    L.circle([lat, lng], {
                        radius: radiusKm * 1000,
                        color: '#2563eb',
                        fillColor: '#3b82f6',
                        fillOpacity: 0.12,
                        weight: 2,
                        dashArray: '6, 8',
                    }).addTo(this.radarCircleLayer);

                    // Client Location Marker
                    const clientIcon = L.divIcon({
                        className: 'client-radar-origin',
                        html: `
                            <div style="position: relative; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <div style="width: 18px; height: 18px; background: #2563eb; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 16px #2563eb;"></div>
                                <div class="radar-wave-ring" style="width: 80px; height: 80px; top: -28px; left: -28px;"></div>
                            </div>
                        `,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12],
                    });

                    L.marker([lat, lng], { icon: clientIcon }).addTo(this.radarCircleLayer);
                },

                renderAssignedTechnician(lat, lng, name, mobile) {
                    if (!this.radarCircleLayer) return;

                    const techCarIcon = L.divIcon({
                        className: 'assigned-tech-moving',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                <div style="background-color: #4f46e5; width: 42px; height: 42px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 16px rgba(79,70,229,0.5); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                    🚗
                                </div>
                                <div style="background: rgba(15,23,42,0.9); color: white; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; margin-top: 3px; white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                                    ${name} (در مسیر)
                                </div>
                            </div>
                        `,
                        iconSize: [42, 60],
                        iconAnchor: [21, 21],
                    });

                    L.marker([lat, lng], { icon: techCarIcon }).addTo(this.radarCircleLayer)
                        .bindPopup(`<div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif;"><b>${name}</b><br>تلفن: ${mobile}<br><span style="color: #4f46e5; font-weight: bold;">در حال حرکت به سمت شما</span></div>`).openPopup();
                },

                renderConfirmedOrigin(lat, lng, locationText) {
                    if (!this.map) return;
                    if (this.originMarker) {
                        this.map.removeLayer(this.originMarker);
                        this.originMarker = null;
                    }

                    const originIcon = L.divIcon({
                        className: 'confirmed-origin-marker',
                        html: `
                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3)); pointer-events: auto; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00a651 0%, #059669 100%); color: #ffffff; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 900; font-family: IRANYekanX, sans-serif; white-space: nowrap; border: 2px solid #ffffff; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,166,81,0.4);">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #ffffff; display: inline-block; box-shadow: 0 0 6px rgba(255,255,255,0.9);"></span>
                                    <span>محل اعزام</span>
                                </div>
                                <div style="width: 2px; height: 10px; background: #00a651;"></div>
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #00a651; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.35);"></div>
                            </div>
                        `,
                        iconSize: [80, 50],
                        iconAnchor: [40, 50],
                    });

                    this.originMarker = L.marker([lat, lng], { icon: originIcon, zIndexOffset: 1000 }).addTo(this.map);
                    this.originMarker.bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: IRANYekanX, sans-serif; padding: 4px; min-width: 170px;">
                            <div style="font-weight: 900; font-size: 12px; color: #00a651; margin-bottom: 2px;">📍 محل تأیید شده اعزام</div>
                            <div style="font-size: 11px; color: #1e293b; font-weight: 700;">${locationText || 'موقعیت شما روی نقشه'}</div>
                        </div>
                    `);

                    this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15), { animate: true });
                }
            }));
        });
    </script>

    @livewireScripts
</body>
</html>
