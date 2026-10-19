<div class="p-6 sm:p-8 space-y-8 font-sans">
    {{-- Partner / Vendor Store Section (If Registered as Partner) --}}
    @if($clientVendorPartner)
    <div class="bg-gradient-to-r from-indigo-50/70 via-purple-50/50 to-white dark:from-indigo-950/30 dark:via-purple-950/20 dark:to-slate-800 rounded-3xl border border-indigo-200/80 dark:border-indigo-800/60 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-indigo-600/30">
                    🏪
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-black text-slate-900 dark:text-white">{{ $clientVendorPartner->store_name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $clientVendorPartner->status === 'active' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-500/30' }}">
                            {{ $clientVendorPartner->status === 'active' ? 'همکار فعال' : 'غیرفعال' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">این مشتری به عنوان فروشگاه همکار B2B در شبکه نصب و گارانتی ثبت شده است.</p>
                </div>
            </div>

            <a href="{{ route('user.fieldservice.vendors.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-slate-700 border border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-50 dark:hover:bg-slate-600 transition shadow-xs">
                <span>مدیریت فروشگاه‌ها</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
            {{-- Referral Code --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 block">کد معرف اختصاصی</span>
                    <span class="text-sm font-black text-indigo-700 dark:text-indigo-300 font-sans tracking-wider">{{ $clientVendorPartner->referral_code }}</span>
                </div>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $clientVendorPartner->referral_code }}'); alert('کد معرف کپی شد');" class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 text-[11px] font-bold">
                    کپی
                </button>
            </div>

            {{-- Commission Percent --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/60">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 block">درصد پورسانت معرف</span>
                <span class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $clientVendorPartner->referral_commission_percent }}٪ از فاکتور</span>
            </div>

            {{-- Referred Orders --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/60">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 block">سفارشات ارجاعی گارانتی</span>
                <span class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $clientVendorPartner->orders->count() }} سفارش</span>
            </div>

            {{-- Wallet Balance --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700/60">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 block">موجودی کیف پول</span>
                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ number_format($clientVendorPartner->wallet_balance) }} تومان</span>
            </div>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 pt-1">
            <span>📍 آدرس فروشگاه:</span>
            <span>{{ $clientVendorPartner->province }}، {{ $clientVendorPartner->city }} - {{ $clientVendorPartner->address ?: 'بدون آدرس دقیق' }}</span>
        </div>
    </div>
    @endif

    {{-- Buildings & Facilities Passport Section --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-150 dark:border-slate-700/60 pb-3">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="text-lg">🏢</span>
                    <span>شناسنامه فنی و ساختمان‌های تحت مدیریت</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        {{ $clientBuildings->count() }} ساختمان
                    </span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">ثبت شناسنامه تجهیزات موتورخانه، چیلر، بوسترپمپ، آسانسور و قراردادهای پایش SLA</p>
            </div>

            <a href="{{ route('user.fieldservice.buildings.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                <span>+ ثبت ساختمان جدید</span>
            </a>
        </div>

        @forelse($clientBuildings as $building)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-black text-base text-slate-900 dark:text-white">{{ $building->name }}</h3>
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                            {{ $building->building_type_label }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span>{{ $building->floors_count }} طبقه</span>
                        <span>•</span>
                        <span>{{ $building->units_count }} واحد</span>
                        <span>•</span>
                        <span>مدیر ساختمان: {{ $building->manager_name }} ({{ $building->manager_phone }})</span>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                        <span>📍</span>
                        <span>{{ $building->province }}، {{ $building->city }} - {{ $building->address }}</span>
                    </div>
                </div>

                {{-- SLA Contract Status --}}
                @php
                    $activeSla = $building->slaContracts->where('status', 'active')->first();
                @endphp
                <div class="shrink-0 text-left sm:text-right">
                    @if($activeSla)
                    <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-xs space-y-1">
                        <div class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>قرارداد پایش {{ $activeSla->tier_label }}</span>
                        </div>
                        <div class="text-[11px] text-amber-600 dark:text-amber-400">
                            شماره: {{ $activeSla->contract_number }} • ماهیانه {{ number_format($activeSla->monthly_fee) }} ت
                        </div>
                    </div>
                    @else
                    <div class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 text-xs text-slate-400">
                        فاقد قرارداد فعال SLA
                    </div>
                    @endif
                </div>
            </div>

            {{-- Equipments Passport Pills --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/50">
                <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2.5 flex items-center justify-between">
                    <span>تجهیزات و تاسیسات ثبت‌شده ({{ $building->equipments->count() }} مورد):</span>
                    <a href="{{ route('user.fieldservice.buildings.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-[11px]">
                        مدیریت در پاسپورت تاسیسات ←
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @forelse($building->equipments as $eq)
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ $eq->equipment_type_label }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $eq->health_badge_class }}">
                                {{ $eq->health_status_label }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $eq->title }} @if($eq->brand_model)({{ $eq->brand_model }})@endif
                        </div>
                        @if($eq->next_service_due)
                        <div class="text-[10px] text-slate-400">
                            سرویس بعدی: {{ $eq->next_service_due }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="col-span-full py-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-750 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                        تجهیزی برای این ساختمان ثبت نشده است.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 space-y-4">
            <div class="text-4xl">🏢</div>
            <div class="space-y-1">
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">هیچ ساختمان یا شناسنامه فنی برای این مشتری ثبت نشده است</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">می‌توانید ساختمان‌های تحت مدیریت این کارفرما را اضافه نموده و تجهیزات، شناسنامه فنی و قرارداد نگهداری ماهانه تنظیم کنید.</p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <a href="{{ route('user.fieldservice.buildings.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                    + ثبت اولین ساختمان
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>
