<div class="space-y-4 font-sans text-xs select-none" wire:poll.3s
     @if($order)
     x-init="
        $dispatch('start-radar-animation', { lat: {{ $order->lat ?: 36.5444 }}, lng: {{ $order->lng ?: 52.6780 }}, radiusKm: {{ $order->search_radius_km ?: 3 }} });
        @if($order->technician && $order->technician->lat && $order->technician->lng)
        $dispatch('technician-assigned', {
            lat: {{ $order->technician->lat }},
            lng: {{ $order->technician->lng }},
            name: '{{ addslashes($order->technician->full_name) }}',
            mobile: '{{ $order->technician->mobile }}',
            rating: '{{ $order->technician->rating_avg ?: "5.0" }}'
        });
        @endif

        // Client-side Echo channel subscription for instantaneous order events
        if (window.Echo && {{ $order->id }}) {
            window.Echo.channel('orders.{{ $order->id }}')
                .listen('.ServiceOrderStatusChanged', (e) => {
                    $wire.call('$refresh');
                    if (e.technician && e.technician.lat && e.technician.lng) {
                        $dispatch('technician-assigned', {
                            lat: e.technician.lat,
                            lng: e.technician.lng,
                            name: e.technician.full_name,
                            mobile: e.technician.mobile,
                            rating: e.technician.rating_avg || '5.0'
                        });
                    }
                })
                .listen('.TechnicianLocationUpdated', (e) => {
                    $dispatch('technician-location-live-updated', e);
                });
        }
     "
     @endif>

    @if($order)
    {{-- Order Header & Status Card --}}
    <div class="p-4 sm:p-5 rounded-3xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 space-y-3.5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3 shrink-0">
                    @if(in_array($order->status->value, ['searching', 'dispatched']))
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    @else
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    @endif
                </span>
                <span class="font-black text-sm text-slate-900 dark:text-white">{{ $order->status->label() }}</span>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/30 font-sans tracking-wide">
                کد پیگیری: {{ $order->order_number }}
            </span>
        </div>

        {{-- Visual Timeline Steps --}}
        @php
            $currentStatus = $order->status->value;
            $stepIndex = match($currentStatus) {
                'pending', 'searching', 'dispatched' => 1,
                'accepted', 'en_route'               => 2,
                'arrived', 'in_progress'             => 3,
                'invoice_pending'                    => 4,
                'completed'                          => 5,
                default                              => 1,
            };
        @endphp
        <div class="grid grid-cols-4 gap-1.5 pt-1">
            <div class="text-center space-y-1">
                <div class="h-1.5 rounded-full {{ $stepIndex >= 1 ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                <div class="text-[10px] font-bold {{ $stepIndex >= 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">رادار اعزام</div>
            </div>
            <div class="text-center space-y-1">
                <div class="h-1.5 rounded-full {{ $stepIndex >= 2 ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                <div class="text-[10px] font-bold {{ $stepIndex >= 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">تکنسین در مسیر</div>
            </div>
            <div class="text-center space-y-1">
                <div class="h-1.5 rounded-full {{ $stepIndex >= 3 ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                <div class="text-[10px] font-bold {{ $stepIndex >= 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">عیب‌یابی محل</div>
            </div>
            <div class="text-center space-y-1">
                <div class="h-1.5 rounded-full {{ $stepIndex >= 5 ? 'bg-emerald-500' : ($stepIndex >= 4 ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700') }}"></div>
                <div class="text-[10px] font-bold {{ $stepIndex >= 4 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">فاکتور و گارانتی</div>
            </div>
        </div>

        {{-- Active Radar Status Card with Countdown (Snapp Radar Style) --}}
        @if(in_array($order->status->value, ['searching', 'dispatched']))
        <div class="p-4 sm:p-5 rounded-3xl bg-amber-50/90 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/20 text-amber-900 dark:text-amber-200 space-y-3.5 shadow-sm"
             x-data="{ countdown: 45, timer: null }"
             x-init="timer = setInterval(() => { if (countdown > 0) countdown--; else countdown = 45; }, 1000)"
             x-on:destroy="clearInterval(timer)">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-200/60 dark:border-indigo-500/30">
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                        </svg>
                    </span>
                    <div>
                        <div class="font-black text-xs sm:text-sm text-slate-900 dark:text-white">رادار هوشمند اعزام فعال است</div>
                        <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium">در حال جستجوی نزدیک‌ترین متخصصین</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-200/70 dark:bg-amber-500/20 text-amber-900 dark:text-amber-200 font-black text-xs shadow-xs border border-amber-300/50 dark:border-amber-500/30">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span x-text="countdown"></span>
                    <span>ثانیه</span>
                </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed bg-white/70 dark:bg-slate-850/60 p-3 rounded-2xl border border-amber-200/50 dark:border-amber-500/10">
                سفارش شما در محدوده شهر <strong class="text-slate-900 dark:text-white">{{ $order->city }}</strong> به تکنسین‌های دارای مهارت ارسال شد. به محض پذیرش تکنسین، موقعیت زنده وی نمایش داده می‌شود.
            </p>

            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>امواج جستجو فعال روی نقشه</span>
                </div>
                <button type="button" wire:click="openCancelModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-600 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30 text-xs font-bold transition-all active:scale-95 shadow-xs">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    <span>انصراف از سفارش</span>
                </button>
            </div>
        </div>
        @endif

        {{-- Order Cancelled Notice Card --}}
        @if($order->status->value === 'cancelled')
        <div class="p-4 sm:p-5 rounded-3xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200/70 dark:border-rose-500/20 text-rose-800 dark:text-rose-300 space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 flex items-center justify-center font-black text-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </span>
                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">این سفارش لغو گردید.</span>
            </div>
            <p class="text-xs text-rose-700 dark:text-rose-300/90 leading-relaxed bg-white/60 dark:bg-slate-850/60 p-3 rounded-2xl border border-rose-200/40 dark:border-rose-500/10">
                {{ $order->cancellation_reason ?: 'درخواست اعزام تکنسین لغو شده است. در صورت تمایل می‌توانید سفارش جدیدی ثبت کنید.' }}
            </p>
            <div class="pt-1">
                <button type="button" wire:click="$dispatchTo('fieldservice::order-wizard', 'start-new-order')"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition active:scale-95">
                    <span>+</span>
                    <span>ثبت مجدد سفارش</span>
                </button>
            </div>
        </div>
        @endif

        {{-- Assigned Technician Spotlight Card --}}
        @if($order->technician)
        <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 flex flex-col gap-3.5 shadow-sm">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/30 flex items-center justify-center font-bold text-2xl shrink-0 shadow-xs">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-black text-slate-900 dark:text-white text-sm truncate">{{ $order->technician->full_name }}</div>
                        <div class="text-slate-500 dark:text-slate-400 text-xs truncate">تکنسین رسمی {{ $platformName ?? '' }} • شهر {{ $order->technician->city }}</div>
                        <div class="text-amber-500 dark:text-amber-400 font-bold text-xs mt-0.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>{{ $order->technician->rating_avg }}</span>
                            <span class="text-slate-400 font-normal">({{ $order->technician->completed_orders_count }} خدمت موفق)</span>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Full width actions bar --}}
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 w-full">
                    <a href="tel:{{ $order->technician->mobile }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-black text-xs shadow-lg shadow-indigo-600/20 transition-all">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <span>تماس مستقیم</span>
                    </a>
                    @if($order->canCustomerCancel())
                    <button type="button" wire:click="openCancelModal"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-600 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30 font-bold text-xs transition-all active:scale-95 shadow-xs shrink-0"
                            title="انصراف از خدمت">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        <span>انصراف</span>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Live Route & ETA Status Banner --}}
            <div class="w-full pt-2.5 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-2"
                 x-show="etaText || distanceKm">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span x-text="'تکنسین ' + (etaText || 'در مسیر')"></span>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-750 text-slate-700 dark:text-slate-300 font-bold text-[11px] border border-slate-200/60 dark:border-slate-700/60"
                     x-show="distanceKm">
                    <span>فاصله:</span>
                    <span x-text="distanceKm + ' کیلومتر'"></span>
                </div>
            </div>
        </div>
        @endif

        {{-- Notice when Technician is On-Site / Cannot Unilaterally Cancel --}}
        @if(in_array($order->status->value, ['arrived', 'in_progress', 'invoice_pending']))
        <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/30 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 min-w-0">
                <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span class="font-bold text-[11px] leading-snug">تکنسین در محل حضور دارد. لغو منوط به هماهنگی با پشتیبانی است.</span>
            </div>
            <a href="tel:01132220000" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition active:scale-95 shrink-0">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <span>تماس با پشتیبانی</span>
            </a>
        </div>
        @endif

        {{-- Order Specs Recap --}}
        <div class="grid grid-cols-2 gap-2 text-slate-600 dark:text-slate-300 pt-0.5">
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-750 border border-slate-100 dark:border-slate-700/50">
                <span class="text-slate-400 block text-[10px]">دستگاه:</span>
                <span class="font-bold text-slate-900 dark:text-white truncate block">
                    {{ $order->appliance_type?->label() }} @if($order->appliance_brand_model)({{ $order->appliance_brand_model }})@endif
                </span>
            </div>
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-750 border border-slate-100 dark:border-slate-700/50">
                <span class="text-slate-400 block text-[10px]">موقعیت خدمت:</span>
                <span class="font-bold text-slate-900 dark:text-white truncate block">{{ $order->city }}</span>
            </div>
        </div>
    </div>

    {{-- Digital Itemized Invoice & Escrow Section --}}
    @if($order->invoice)
    <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 space-y-3.5 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div class="font-black text-slate-900 dark:text-white text-xs sm:text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>فاکتور دیجیتال رسمی خدمات</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-xs font-bold {{ $order->invoice->payment_status->badgeClass() }}">
                {{ $order->invoice->payment_status->label() }}
            </span>
        </div>

        {{-- Items breakdown --}}
        <div class="space-y-2 text-xs">
            @foreach($order->invoice->items as $it)
            <div class="flex items-center justify-between py-1 text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-1.5">
                    <span class="text-indigo-500">•</span>
                    <span>{{ $it->title }} ({{ $it->quantity }}x)</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white">{{ number_format($it->total_price / 10) }} تومان</span>
            </div>
            @endforeach

            <div class="flex items-center justify-between py-1 text-slate-500 dark:text-slate-400">
                <span>ایاب و ذهاب استاندارد تکنسین:</span>
                <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($order->invoice->travel_fee / 10) }} تومان</span>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-sm">
                <span class="font-bold text-slate-800 dark:text-slate-200">مبلغ کل فاکتور:</span>
                <span class="font-black text-base text-emerald-600 dark:text-emerald-400">{{ number_format($order->invoice->total_amount / 10) }} تومان</span>
            </div>
        </div>

        {{-- Escrow Payment Button --}}
        @if($order->invoice->payment_status->value === 'pending')
        <div class="pt-2 space-y-2">
            <button type="button" wire:click="payAndApproveInvoice"
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-500/25 transition active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>پرداخت امن در حساب امانی {{ $platformName ?? '' }} (Escrow)</span>
            </button>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center leading-relaxed flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>وجه در حساب امن پلتفرم نگهداری می‌شود و تنها پس از رضایت کامل شما به تکنسین تسویه خواهد شد.</span>
            </p>
        </div>
        @elseif($order->invoice->payment_status->value === 'escrow_held')
        <div class="pt-2 space-y-3">
            <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200/60 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs text-center font-bold flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>وجه در حساب امانی قفل شده و گارانتی طلایی فعال گردید.</span>
            </div>
            <button type="button" wire:click="confirmSatisfactionAndComplete"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/20 transition active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>تایید کیفیت کار، تسویه و صدور کارت گارانتی ۹۰ روزه</span>
            </button>
        </div>
        @elseif($order->invoice->payment_status->value === 'released' || $order->status->value === 'completed')
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/60 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-center font-bold text-xs space-y-2">
            <div class="text-sm flex items-center justify-center gap-1.5 font-black text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                <span>خدمت با رضایت شما پایان یافت و گارانتی رسمی صادر شد.</span>
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-400">
                کارت ضمانت کیفیت ۹۰ روزه {{ $platformName ?? '' }} برای این خدمت تا تاریخ {{ function_exists('jdate') ? jdate(now()->addDays(90))->format('Y/m/d') : now()->addDays(90)->toDateString() }} معتبر است.
            </div>
            <div class="pt-1">
                <button type="button" wire:click="$dispatchTo('fieldservice::order-wizard', 'start-new-order')"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition active:scale-95 flex items-center gap-1.5 mx-auto">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>ثبت سفارش جدید دیگر</span>
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif
    @else
    <div class="py-12 text-center text-slate-400 space-y-2">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
        </div>
        <div class="text-xs font-bold text-slate-600 dark:text-slate-300">سفارشی جهت پیگیری یافت نشد.</div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- CUSTOMER CANCELLATION MODAL                                              --}}
    {{-- ========================================================================= --}}
    @if($showCancelModal && $order)
    <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-sans select-none">
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-2xl animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-rose-50 dark:bg-rose-500/15 border border-rose-200/60 dark:border-rose-500/30 text-rose-500 flex items-center justify-center text-xs">✕</span>
                    <span>انصراف و لغو درخواست خدمت</span>
                </h3>
                <button type="button" wire:click="closeCancelModal"
                        class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs transition active:scale-95">✕</button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                لطفاً علت انصراف از خدمت را انتخاب فرمایید تا در بهبود کیفیت خدمات به کار گرفته شود:
            </p>

            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @foreach($cancellationReasons as $reasonKey => $reasonLabel)
                <label class="flex items-center gap-3 p-3 rounded-2xl border transition cursor-pointer select-none text-xs {{ $cancelReason === $reasonKey ? 'bg-indigo-50/90 border-indigo-600 dark:bg-indigo-500/20 dark:border-indigo-500 text-slate-900 dark:text-white font-bold shadow-xs' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <input type="radio" wire:model.live="cancelReason" value="{{ $reasonKey }}" class="sr-only">
                    <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 {{ $cancelReason === $reasonKey ? 'border-indigo-600 bg-indigo-600 dark:border-indigo-400 dark:bg-indigo-400' : 'border-slate-300 dark:border-slate-600' }}">
                        @if($cancelReason === $reasonKey)
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </span>
                    <span class="text-xs leading-snug">{{ $reasonLabel }}</span>
                </label>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">توضیحات تکمیلی (اختیاری)</label>
                <textarea wire:model="cancelNote" rows="2" placeholder="توضیح بیشتر در صورت تمایل..."
                          class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-white font-sans focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                <button type="button" wire:click="closeCancelModal"
                        class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition active:scale-95">
                    بازگشت
                </button>
                <button type="button" wire:click="confirmCustomerCancel"
                        class="w-2/3 py-3 bg-rose-600 hover:bg-rose-500 active:scale-95 text-white font-black text-xs rounded-2xl shadow-lg shadow-rose-600/25 transition">
                    تایید و لغو درخواست
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
