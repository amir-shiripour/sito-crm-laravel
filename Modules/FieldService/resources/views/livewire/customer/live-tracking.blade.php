<div class="space-y-4 font-sans text-xs select-none" wire:poll.4s
     @if($order)
     x-init="
        $dispatch('start-radar-animation', { lat: {{ $order->lat ?: 36.5444 }}, lng: {{ $order->lng ?: 52.6780 }}, radiusKm: {{ $order->search_radius_km ?: 3 }} });
        @if($order->technician && $order->technician->lat && $order->technician->lng)
        $dispatch('technician-assigned-marker', {
            lat: {{ $order->technician->lat }},
            lng: {{ $order->technician->lng }},
            name: '{{ addslashes($order->technician->full_name) }}',
            mobile: '{{ $order->technician->mobile }}'
        });
        @endif
     "
     @endif>

    @if($order)
    {{-- Order Header & Status Card --}}
    <div class="p-4 sm:p-5 rounded-3xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 space-y-3.5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full {{ in_array($order->status->value, ['searching', 'dispatched']) ? 'bg-amber-500 animate-ping' : 'bg-emerald-500' }}"></span>
                <span class="font-black text-sm text-slate-900 dark:text-white">{{ $order->status->label() }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-sans tracking-wide">
                کد: {{ $order->order_number }}
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
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/70 dark:border-amber-500/20 text-amber-800 dark:text-amber-300 space-y-3"
             x-data="{ countdown: 45, timer: null }"
             x-init="timer = setInterval(() => { if (countdown > 0) countdown--; else countdown = 45; }, 1000)"
             x-on:destroy="clearInterval(timer)">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl animate-spin">📡</span>
                    <span class="font-bold text-xs">رادار اعزام تکنسین فعال است</span>
                </div>
                <div class="flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-200/70 dark:bg-amber-900/50 text-amber-900 dark:text-amber-200 font-black text-xs">
                    <span x-text="countdown"></span>
                    <span>ثانیه</span>
                </div>
            </div>

            <p class="text-[11px] text-amber-700 dark:text-amber-300/90 leading-relaxed">
                سفارش شما در شعاع {{ $order->search_radius_km }} کیلومتری شهر {{ $order->city }} به تکنسین‌های دارای مهارت ارسال شد. در صورت عدم پذیرش ظرف چند ثانیه، شعاع رادار به صورت خودکار گسترش می‌یابد.
            </p>

            <div class="flex items-center justify-between pt-1">
                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">امواج جستجو روی نقشه در حال پخش است...</span>
                <button type="button" wire:click="cancelOrder" wire:confirm="آیا از لغو درخواست اعزام تکنسین اطمینان دارید؟"
                        class="text-[11px] text-rose-600 dark:text-rose-400 hover:underline font-bold">
                    انصراف از سفارش ✕
                </button>
            </div>
        </div>
        @endif

        {{-- Assigned Technician Spotlight Card --}}
        @if($order->technician)
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-750 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shrink-0">
                    👨‍🔧
                </div>
                <div>
                    <div class="font-black text-slate-900 dark:text-white text-sm">{{ $order->technician->full_name }}</div>
                    <div class="text-slate-500 dark:text-slate-400 text-xs">تکنسین تاییدصلاحیت‌شده نصاب‌باشی در {{ $order->technician->city }}</div>
                    <div class="text-amber-500 dark:text-amber-400 font-bold text-[11px] mt-0.5">
                        ★ {{ $order->technician->rating_avg }} ({{ $order->technician->completed_orders_count }} خدمت موفق)
                    </div>
                </div>
            </div>

            <a href="tel:{{ $order->technician->mobile }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95">
                <span>📞</span>
                <span>تماس با تکنسین</span>
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
            <div class="font-black text-slate-900 dark:text-white text-xs sm:text-sm flex items-center gap-1.5">
                <span>💳</span>
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
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-500/25 transition active:scale-95">
                💳 پرداخت امن در حساب امانی نصاب‌باشی (Escrow)
            </button>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center leading-relaxed">
                🔒 وجه در حساب امن پلتفرم نگهداری می‌شود و تنها پس از رضایت کامل شما از نتیجه کار به تکنسین تسویه خواهد شد.
            </p>
        </div>
        @elseif($order->invoice->payment_status->value === 'escrow_held')
        <div class="pt-2 space-y-3">
            <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200/60 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs text-center font-bold">
                🔒 وجه در حساب امانی قفل شده و گارانتی طلایی فعال گردید.
            </div>
            <button type="button" wire:click="confirmSatisfactionAndComplete"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/20 transition active:scale-95">
                ✓ تایید کیفیت کار، تسویه و صدور کارت گارانتی ۹۰ روزه
            </button>
        </div>
        @elseif($order->invoice->payment_status->value === 'released')
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/60 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-center font-bold text-xs space-y-1.5">
            <div class="text-sm">🎉 خدمت با رضایت شما پایان یافت و گارانتی رسمی صادر شد.</div>
            <div class="text-xs text-slate-600 dark:text-slate-400">
                کارت ضمانت کیفیت ۹۰ روزه نصاب‌باشی برای این خدمت تا تاریخ {{ function_exists('jdate') ? jdate(now()->addDays(90))->format('Y/m/d') : now()->addDays(90)->toDateString() }} معتبر است.
            </div>
        </div>
        @endif
    </div>
    @endif
    @else
    <div class="py-12 text-center text-slate-400 space-y-2">
        <div class="text-3xl">🔍</div>
        <div class="text-xs font-bold text-slate-600 dark:text-slate-300">سفارشی جهت پیگیری یافت نشد.</div>
    </div>
    @endif
</div>
