<div class="space-y-4 font-sans text-xs select-none" @if($tech && $tech->is_online) wire:poll.10s @endif>

    {{-- ========================================================================= --}}
    {{-- 0. UNAUTHENTICATED NOTICE (WHEN NO TECHNICIAN IS LOGGED IN)               --}}
    {{-- ========================================================================= --}}
    @if(!$tech)
    <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 text-center space-y-3 shadow-xl">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl font-black">
            🔒
        </div>
        <div>
            <h3 class="text-sm font-black text-white">ورود به کنسول سفیران فنی</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">
                جهت دسترسی به رادار درخواست‌های استانی، پذیرش ماموریت‌ها و ثبت درآمد روزانه، لطفاً وارد حساب خود شوید.
            </p>
        </div>
        <div class="pt-2">
            <button type="button" @click="$dispatch('open-technician-auth')"
                    class="w-full max-w-xs mx-auto py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                <span>ورود یا ثبت‌نام تکنسین</span>
                <span>←</span>
            </button>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. OFFLINE NOTICE (WHEN TECHNICIAN IS NOT READY FOR MISSIONS)             --}}
    {{-- ========================================================================= --}}
    @if($tech && !$tech->is_online && !$activeMission)
    <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 text-center space-y-3 shadow-xl">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl font-black">
            🌙
        </div>
        <div>
            <h3 class="text-sm font-black text-white">شما در وضعیت استراحت (آفلاین) هستید</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">
                در این وضعیت سفارش‌های جدید رادار برای شما نمایش داده نمی‌شود. برای شروع به کار و دریافت سرویس، وضعیت خود را به آنلاین تغییر دهید.
            </p>
        </div>
        <div class="pt-1 flex justify-center">
            @livewire('fieldservice::technician-duty-toggle', ['technicianId' => $tech->id])
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 2. ACTIVE MISSION CARD (IF ASSIGNED TO AN ORDER)                          --}}
    {{-- ========================================================================= --}}
    @if($tech && $activeMission)
    <div class="p-4 sm:p-5 rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950/60 to-slate-900 border border-indigo-500/40 shadow-2xl space-y-4">
        
        {{-- Header & Stepped Lifecycle Bar --}}
        <div class="space-y-3 pb-3 border-b border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-xs font-black text-white">ماموریت فعال اختصاص‌یافته</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $activeMission->status->badgeClass() }}">
                    {{ $activeMission->status->label() }}
                </span>
            </div>

            {{-- 5-Step Visual Stepper --}}
            <div class="grid grid-cols-5 gap-1 pt-1 text-center font-sans">
                @php
                    $statusSteps = [
                        'accepted'        => 1,
                        'en_route'        => 2,
                        'arrived'         => 3,
                        'in_progress'     => 4,
                        'invoice_pending' => 4,
                        'completed'       => 5,
                    ];
                    $currentStepNum = $statusSteps[$activeMission->status->value] ?? 1;
                @endphp
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 1 ? 'bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 1 ? 'text-indigo-300 font-black' : 'text-slate-600' }}">پذیرش</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 2 ? 'bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 2 ? 'text-indigo-300 font-black' : 'text-slate-600' }}">در مسیر</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 3 ? 'bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 3 ? 'text-indigo-300 font-black' : 'text-slate-600' }}">حضور</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 4 ? 'bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 4 ? 'text-indigo-300 font-black' : 'text-slate-600' }}">فاکتور</span>
                </div>
                <div class="space-y-1">
                    <div class="h-1.5 rounded-full {{ $currentStepNum >= 5 ? 'bg-emerald-500 shadow-sm shadow-emerald-500/50' : 'bg-slate-800' }}"></div>
                    <span class="text-[9px] font-bold {{ $currentStepNum >= 5 ? 'text-emerald-400 font-black' : 'text-slate-600' }}">تسویه</span>
                </div>
            </div>
        </div>

        {{-- Order & Appliance Details --}}
        <div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $activeMission->category?->icon ?: ($activeMission->appliance_type?->icon() ?: '🛠️') }}</span>
                    <div>
                        <div class="text-xs sm:text-sm font-black text-white">
                            {{ $activeMission->category?->name ?: ($activeMission->appliance_brand_model ?: $activeMission->appliance_type?->label()) }}
                        </div>
                        <div class="text-[10px] text-indigo-300 font-bold mt-0.5">
                            سفارش #{{ $activeMission->order_number }}
                        </div>
                    </div>
                </div>

                @if($activeMission->priority === 'emergency')
                <span class="px-2 py-1 rounded-xl text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                    <span>⚡</span>
                    <span>نصاب‌باشی پلاس (فوری)</span>
                </span>
                @endif
            </div>

            <p class="text-[11px] text-slate-300 mt-2 bg-slate-900/60 p-2.5 rounded-xl border border-slate-800/80 leading-relaxed">
                <span class="text-slate-400 font-bold">شرح مشکل:</span> {{ $activeMission->problem_description ?: 'بدون توضیحات تکمیلی' }}
            </p>
        </div>

        {{-- Customer & Address Card --}}
        <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-sm font-bold text-slate-300">
                        👤
                    </span>
                    <div>
                        <div class="text-xs font-black text-white">{{ $activeMission->customer_name }}</div>
                        <div class="text-[10px] text-slate-400">کارفرمای خدمت</div>
                    </div>
                </div>

                {{-- Quick Call Button --}}
                <a href="tel:{{ $activeMission->customer_phone }}"
                   class="px-3 py-1.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 font-bold text-xs flex items-center gap-1.5 transition active:scale-95">
                    <span>📞</span>
                    <span>تماس مستقیم</span>
                </a>
            </div>

            <div class="pt-1.5 border-t border-slate-800/60 flex items-start gap-1.5 text-[11px] text-slate-300">
                <span class="text-rose-400 shrink-0 mt-0.5">📍</span>
                <span class="leading-relaxed font-sans">{{ $activeMission->address ?: "مازندران، {$activeMission->city}" }}</span>
            </div>
        </div>

        {{-- Lifecycle Action Buttons --}}
        <div class="space-y-2.5">
            @if($activeMission->status->value === 'accepted')
            <button type="button" wire:click="startRoute({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
                <span>🚗</span>
                <span>حرکت به سمت مشتری (آغاز اعزام)</span>
                <span>←</span>
            </button>
            @elseif($activeMission->status->value === 'en_route')
            <button type="button" wire:click="markArrived({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-teal-600 hover:bg-teal-500 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl shadow-teal-600/25 transition-all flex items-center justify-center gap-2">
                <span>📍</span>
                <span>رسیدم به محل مشتری (اعلام حضور)</span>
                <span>✓</span>
            </button>
            @elseif(in_array($activeMission->status->value, ['arrived', 'in_progress', 'invoice_pending'], true))
            <button type="button" wire:click="completeOrder({{ $activeMission->id }})" 
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl shadow-emerald-600/25 transition-all flex items-center justify-center gap-2">
                <span>✓</span>
                <span>اتمام موفقیت‌آمیز کار و تسویه نهایی</span>
            </button>
            @endif

            {{-- Secondary Actions: External Navigation & Backup Tech --}}
            <div class="grid grid-cols-2 gap-2">
                {{-- Assistant tech button --}}
                <button type="button" wire:click="openBackupModal({{ $activeMission->id }})" 
                        class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-750 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 active:scale-95">
                    <span>🤝</span>
                    <span>درخواست نصاب کمکی</span>
                </button>

                {{-- Fast GPS Routing Menu (Neshan, Balad, Google) --}}
                @php
                    $lat = $activeMission->lat ?: 36.5444;
                    $lng = $activeMission->lng ?: 52.6780;
                @endphp
                <div x-data="{ navOpen: false }" class="relative">
                    <button type="button" @click="navOpen = !navOpen"
                            class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5 active:scale-95">
                        <span>🗺️</span>
                        <span>مسیریابی هوشمند</span>
                        <span class="text-[10px]">▼</span>
                    </button>

                    <div x-show="navOpen" @click.outside="navOpen = false" x-cloak
                         class="absolute bottom-full mb-1.5 inset-x-0 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-1.5 space-y-1 z-30 font-sans">
                        <a href="https://neshan.org/maps/route?destination={{ $lat }},{{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span class="font-bold">مسیریابی در نشان</span>
                            <span>🚗</span>
                        </a>
                        <a href="https://balad.ir/location?latitude={{ $lat }}&longitude={{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span class="font-bold">مسیریابی در بلد</span>
                            <span>📍</span>
                        </a>
                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank"
                           class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-800 text-xs text-slate-200 transition">
                            <span class="font-bold">گوگل مپ</span>
                            <span>🌐</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Embedded Invoice Builder Drawer --}}
        <div class="pt-3 border-t border-slate-800" x-data="{ showInvoice: false }">
            <button type="button" @click="showInvoice = !showInvoice"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 text-xs font-bold text-slate-300 flex items-center justify-between transition">
                <span class="flex items-center gap-1.5">
                    <span>🧾</span>
                    <span>صدور فاکتور و مشاهده اقلام اجرت</span>
                </span>
                <span class="text-indigo-400 font-bold" x-text="showInvoice ? 'بستن فاکتور ▲' : 'مشاهده و ثبت فاکتور ▼'"></span>
            </button>

            <div x-show="showInvoice" x-cloak class="mt-3">
                @livewire('fieldservice::invoice-builder', ['orderId' => $activeMission->id], key('inv-'.$activeMission->id))
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 3. INCOMING DISPATCH FEED (AVAILABLE JOBS WHEN NO ACTIVE MISSION)         --}}
    {{-- ========================================================================= --}}
    @if($tech && !$activeMission && $tech->is_online)
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-xs font-black text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>فید پیشنهادهای رادار در {{ $tech->city ?: 'مازندران' }}</span>
            </h2>
            <span class="text-[10px] text-slate-400 font-bold">
                {{ $availableMissions->count() }} سفارش مناسب شما
            </span>
        </div>

        <div class="space-y-2.5">
            @forelse($availableMissions as $m)
            <div id="mission-card-{{ $m->id }}"
                 wire:key="mission-card-item-{{ $m->id }}"
                 wire:click="focusMission({{ $m->id }})"
                 class="p-4 rounded-3xl cursor-pointer transition-all duration-200 shadow-xl space-y-3 border {{ $previewOrderId === $m->id ? 'bg-slate-850 border-indigo-500 ring-2 ring-indigo-500/40 shadow-indigo-500/20' : 'bg-slate-900/90 border-slate-800 hover:border-indigo-500/60 hover:bg-slate-850/80' }}">
                
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $m->category?->icon ?: ($m->appliance_type?->icon() ?: '🛠️') }}</span>
                            <span class="font-black text-white text-xs sm:text-sm truncate">
                                {{ $m->category?->name ?: ($m->appliance_brand_model ?: $m->appliance_type?->label()) }}
                            </span>
                            @if($m->priority === 'emergency' || $m->is_emergency)
                            <span class="px-2 py-0.5 rounded-full text-[9px] bg-rose-500/20 text-rose-300 border border-rose-500/30 font-black shrink-0">
                                فوری
                            </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5 truncate">
                            <span class="text-rose-400">📍</span>
                            <span>{{ $m->city }}، {{ $m->district ?: 'مرکز شهر' }}</span>
                            @if($m->distance_km !== null)
                            <span class="text-slate-600">•</span>
                            <span class="text-indigo-400 font-bold">فاصله: {{ $m->distance_km }} کیلومتر</span>
                            @endif
                        </div>
                    </div>

                    {{-- Order ID Badge --}}
                    <div class="px-2.5 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-[10px] font-black shrink-0">
                        #{{ $m->order_number }}
                    </div>
                </div>

                @if($m->problem_description)
                <p class="text-[11px] text-slate-300 leading-relaxed truncate bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                    <span class="text-slate-400 font-bold">شرح:</span> {{ $m->problem_description }}
                </p>
                @endif

                {{-- Action Row: Details & Location Focus, Dismiss, and Accept --}}
                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between gap-2" @click.stop>
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="focusMission({{ $m->id }})"
                                class="py-2 px-3 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/35 text-indigo-300 border border-indigo-500/30 text-[11px] font-bold transition flex items-center gap-1.5 active:scale-95">
                            <span>🗺️</span>
                            <span>موقعیت و جزئیات</span>
                        </button>
                        <button type="button" wire:click="dismissOrder({{ $m->id }})"
                                class="py-2 px-2.5 rounded-xl bg-slate-800/60 hover:bg-rose-500/20 text-slate-400 hover:text-rose-300 border border-slate-700/60 text-[11px] font-bold transition">
                            رد کردن
                        </button>
                    </div>

                    <button type="button" wire:click="acceptMission({{ $m->id }})" 
                            class="py-2 px-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <span>✓</span>
                        <span>قبول ماموریت</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 rounded-3xl bg-slate-900/60 border border-slate-800 text-center space-y-2.5">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl animate-pulse">
                    📡
                </div>
                <div class="text-xs font-black text-white">در حال رصد سفارش‌های جدید در حوزه شما...</div>
                <p class="text-[11px] text-slate-400 max-w-xs mx-auto leading-relaxed">
                    به محض ثبت درخواست توسط مشتریان در {{ $tech->city ?: 'مازندران' }} در حوزه تخصص شما، هشدار فعال شده و سفارش روی نقشه نمایش داده می‌شود.
                </p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 4. ORDER DETAILS PREVIEW MODAL (بررسی کامل سفارش قبل از قبول)            --}}
    {{-- ========================================================================= --}}
    @if($previewOrder)
    <div class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $previewOrder->category?->icon ?: '🛠️' }}</span>
                    <div>
                        <h3 class="text-xs font-black text-white">بررسی درخواست #{{ $previewOrder->order_number }}</h3>
                        <div class="text-[10px] text-slate-400">{{ $previewOrder->category?->name }} • {{ $previewOrder->city }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($previewOrder->lat && $previewOrder->lng)
                    <button type="button" @click="$dispatch('focus-mission-on-map', { orderId: {{ $previewOrder->id }}, lat: {{ (float)$previewOrder->lat }}, lng: {{ (float)$previewOrder->lng }}, title: '{{ $previewOrder->category?->name ?: 'سفارش' }}' })"
                            class="py-1 px-2.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/35 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold flex items-center gap-1 transition">
                        <span>📍</span>
                        <span>زوم روی نقشه</span>
                    </button>
                    @endif
                    <button type="button" wire:click="closeOrderDetails" class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white text-xs">✕</button>
                </div>
            </div>

            <div class="space-y-2.5 text-[11px]">
                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-850 space-y-1.5">
                    <div class="text-slate-400 font-bold">شرح عیب یا تقاضای کارفرما:</div>
                    <div class="text-white leading-relaxed">{{ $previewOrder->problem_description ?: 'توضیحات ثبت نشده است.' }}</div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-850 flex items-start gap-2">
                    <span class="text-rose-400 text-sm">📍</span>
                    <div class="min-w-0 flex-1">
                        <div class="text-slate-400 font-bold">نشانی تقریبی خدمت:</div>
                        <div class="text-white mt-0.5">{{ $previewOrder->address ?: "استان مازندران، {$previewOrder->city}" }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @if($previewOrder->preferred_time)
                    <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-850 flex items-center justify-between">
                        <span class="text-slate-400">زمان ترجیحی:</span>
                        <span class="font-bold text-indigo-300">{{ $previewOrder->preferred_time }}</span>
                    </div>
                    @endif

                    <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-850 flex items-center justify-between">
                        <span class="text-slate-400">ایاب و ذهاب:</span>
                        <span class="font-bold text-emerald-400">۶۰,۰۰۰ تومان</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                <button type="button" wire:click="dismissOrder({{ $previewOrder->id }})" class="w-1/3 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-400 hover:text-rose-300 font-bold text-xs rounded-xl transition">
                    رد سفارش
                </button>
                <button type="button" wire:click="acceptMission({{ $previewOrder->id }})" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center justify-center gap-1.5">
                    <span>✓</span>
                    <span>قبول و حرکت به سمت آدرس</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. ASSISTANT TECHNICIAN MODAL (درخواست نصاب کمکی)                         --}}
    {{-- ========================================================================= --}}
    @if($showBackupModal)
    <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-xs font-black text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-xs">🤝</span>
                    <span>درخواست نصاب کمکی و پشتیبان</span>
                </h3>
                <button type="button" @click="$wire.showBackupModal = false" class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white text-xs">✕</button>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed">
                در صورت نیاز به نیروی کمکی جهت کارهای دونفره، حمل وزن سنگین یا تجهیزات خاص، درخواست شما برای نزدیک‌ترین همکار ارسال می‌شود.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">علت نیاز به همکار</label>
                <select wire:model="backupReason" class="w-full h-10 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="heavy_lifting">جابجایی دستگاه و وزن سنگین</option>
                    <option value="height_work">نصب در ارتفاع و نردبان بلند</option>
                    <option value="specialized_tools">نیاز به ابزار خاص (پمپ وکیوم، پرس)</option>
                    <option value="double_manpower">نیاز به نیروی کار همزمان</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">توضیحات تکمیلی برای همکار</label>
                <textarea wire:model="backupNotes" rows="2" placeholder="توضیح موقعیت یا نوع ابزار مورد نیاز..." class="w-full p-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-500 font-sans focus:outline-none focus:ring-1 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="button" @click="$wire.showBackupModal = false" class="w-1/3 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-300 font-bold text-xs rounded-xl transition">
                    انصراف
                </button>
                <button type="button" wire:click="submitBackupRequest" class="w-2/3 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-lg shadow-amber-600/20 transition">
                    ارسال فراخوان کمکی
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
