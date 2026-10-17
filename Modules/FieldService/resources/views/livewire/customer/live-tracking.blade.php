<div class="space-y-4 font-sans text-xs" wire:poll.5s>
    @if($order)
    {{-- Status Card --}}
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full {{ in_array($order->status->value, ['searching', 'dispatched']) ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
                <span class="font-black text-sm text-white">{{ $order->status->label() }}</span>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">
                کد: {{ $order->order_number }}
            </span>
        </div>

        @if(in_array($order->status->value, ['searching', 'dispatched']))
        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-base animate-spin">📡</span>
                <span class="font-bold">رادار اعزام فعال است (شعاع: {{ $order->search_radius_km }} کیلومتر)</span>
            </div>
            <p class="text-[11px] text-amber-400/90 leading-relaxed">
                سیستم در حال ارسال سفارش به نزدیک‌ترین تکنسین‌های آزاد در محدوده شماست. در صورت عدم پاسخ در ۴۵ ثانیه، شعاع جستجو به صورت خودکار به شهرهای همجوار مازندران بسط می‌یابد.
            </p>
        </div>
        @endif

        {{-- Assigned Technician Spotlight --}}
        @if($order->technician)
        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-black text-base">
                    🔧
                </div>
                <div>
                    <div class="font-bold text-white text-sm">{{ $order->technician->full_name }}</div>
                    <div class="text-[10px] text-slate-400">تکنسین رسمی نصاب‌باشی ({{ $order->technician->city }})</div>
                    <div class="text-[10px] text-amber-400 mt-0.5">★ {{ $order->technician->rating_avg }} ({{ $order->technician->completed_orders_count }} خدمت موفق)</div>
                </div>
            </div>

            <a href="tel:{{ $order->technician->mobile }}" class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold flex items-center gap-1.5 hover:bg-emerald-500/30 transition">
                <span>📞</span>
                <span>تماس امن</span>
            </a>
        </div>
        @endif
    </div>

    {{-- Digital Invoice & Escrow Section --}}
    @if($order->invoice)
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
            <div class="font-black text-white text-xs">فاکتور تفکیکی دیجیتال</div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $order->invoice->payment_status->badgeClass() }}">
                {{ $order->invoice->payment_status->label() }}
            </span>
        </div>

        {{-- Items breakdown --}}
        <div class="space-y-1.5">
            @foreach($order->invoice->items as $it)
            <div class="flex items-center justify-between py-1 text-slate-300 text-[11px]">
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500">•</span>
                    <span>{{ $it->title }} ({{ $it->quantity }}x)</span>
                </div>
                <span class="font-bold text-white">{{ number_format($it->total_price / 10) }} ت</span>
            </div>
            @endforeach

            <div class="flex items-center justify-between py-1 text-slate-400 text-[11px]">
                <span>هزینه ایاب و ذهاب استاندارد:</span>
                <span class="font-bold text-slate-200">{{ number_format($order->invoice->travel_fee / 10) }} ت</span>
            </div>

            <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
                <span class="font-bold text-white">مبلغ کل قابل پرداخت:</span>
                <span class="font-black text-sm text-emerald-400">{{ number_format($order->invoice->total_amount / 10) }} تومان</span>
            </div>
        </div>

        {{-- Escrow Payment Button --}}
        @if($order->invoice->payment_status->value === 'pending')
        <div class="pt-2 space-y-2">
            <button type="button" wire:click="payAndApproveInvoice" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 text-white font-black text-xs rounded-xl shadow-lg transition">
                💳 پرداخت امن در حساب امانی نصاب‌باشی (Escrow)
            </button>
            <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                مبلغ در حساب امن پلتفرم نگهداری می‌شود و تنها پس از رضایت کامل شما از نتیجه کار به تکنسین تسویه خواهد شد.
            </p>
        </div>
        @elseif($order->invoice->payment_status->value === 'escrow_held')
        <div class="pt-2 space-y-2">
            <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-[11px] text-center font-bold">
                🔒 وجه در حساب امانی قفل شده است.
            </div>
            <button type="button" wire:click="confirmSatisfactionAndComplete" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                ✓ تایید رضایت از کار، تسویه و صدور گارانتی ۹۰ روزه
            </button>
        </div>
        @elseif($order->invoice->payment_status->value === 'released')
        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center font-bold text-xs space-y-1">
            <div>🎉 خدمت با موفقیت به پایان رسید و تسویه شد.</div>
            <div class="text-[10px] text-slate-300">کارت گارانتی ۹۰ روزه تا تاریخ {{ $order->invoice->warranty_until?->toDateString() }} فعال است.</div>
        </div>
        @endif
    </div>
    @endif
    @else
    <div class="py-8 text-center text-slate-500">
        سفارشی جهت پیگیری یافت نشد.
    </div>
    @endif
</div>
