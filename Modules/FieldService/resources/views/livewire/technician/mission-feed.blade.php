<div class="space-y-4 font-sans text-xs" wire:poll.5s>
    {{-- ACTIVE MISSION CARD (IF ANY) --}}
    @if($activeMission)
    <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-950/90 to-slate-900 border border-indigo-500/40 shadow-xl space-y-3.5">
        <div class="flex items-center justify-between">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                ماموریت فعال جاری
            </span>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $activeMission->status->badgeClass() }}">
                {{ $activeMission->status->label() }}
            </span>
        </div>

        <div>
            <div class="flex items-center gap-2">
                <span class="text-base">{{ $activeMission->appliance_type?->icon() }}</span>
                <span class="text-sm font-black text-white">{{ $activeMission->appliance_brand_model ?: $activeMission->appliance_type?->label() }}</span>
            </div>
            <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">{{ $activeMission->problem_description }}</p>
        </div>

        {{-- Customer details & address --}}
        <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="font-bold text-white">{{ $activeMission->customer_name }}</span>
                <a href="tel:{{ $activeMission->customer_phone }}" class="text-emerald-400 font-bold hover:underline flex items-center gap-1">
                    <span>📞</span>
                    <span>{{ $activeMission->customer_phone }}</span>
                </a>
            </div>
            <div class="text-[11px] text-slate-400 flex items-start gap-1">
                <span>📍</span>
                <span>{{ $activeMission->city }}، {{ $activeMission->address }}</span>
            </div>
        </div>

        {{-- Mission Action Buttons --}}
        <div class="space-y-2 pt-1">
            @if($activeMission->status->value === 'accepted')
            <button type="button" wire:click="startRoute({{ $activeMission->id }})" class="w-full py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <span>🚗</span>
                <span>حرکت به سمت مشتری (مسیریابی فعال)</span>
            </button>
            @elseif($activeMission->status->value === 'en_route')
            <button type="button" wire:click="markArrived({{ $activeMission->id }})" class="w-full py-2.5 bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <span>📍</span>
                <span>رسیدم به محل مشتری</span>
            </button>
            @endif

            <div class="grid grid-cols-2 gap-2">
                {{-- Assistant tech button --}}
                <button type="button" wire:click="openBackupModal({{ $activeMission->id }})" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <span>🤝</span>
                    <span>نصاب کمکی</span>
                </button>

                {{-- Map link --}}
                <a href="https://maps.google.com/?q={{ $activeMission->lat }},{{ $activeMission->lng }}" target="_blank" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <span>🗺️</span>
                    <span>مسیریابی نشان/گوگل</span>
                </a>
            </div>
        </div>

        {{-- Embedded Invoice Builder --}}
        <div class="pt-3 border-t border-slate-800">
            @livewire('fieldservice::invoice-builder', ['orderId' => $activeMission->id], key('inv-'.$activeMission->id))
        </div>
    </div>
    @endif

    {{-- INCOMING DISPATCH FEED (AVAILABLE JOBS) --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black text-white flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>فید پیشنهادهای رادار در مازندران</span>
            </h2>
            <span class="text-[10px] text-slate-400">{{ $availableMissions->count() }} سفارش آماده پذیرش</span>
        </div>

        <div class="space-y-3">
            @forelse($availableMissions as $m)
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 transition-all shadow-md space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">{{ $m->appliance_type?->icon() }}</span>
                            <span class="font-bold text-white text-xs">{{ $m->appliance_type?->label() }}</span>
                            @if($m->is_emergency)
                            <span class="px-1.5 py-0.2 rounded text-[9px] bg-rose-500/20 text-rose-400 font-black">فوری</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1">
                            📍 {{ $m->city }}، {{ $m->district ?: 'مرکز شهر' }} (شعاع رادار: {{ $m->search_radius_km }} کیلومتر)
                        </div>
                    </div>

                    {{-- Countdown Badge --}}
                    <div class="px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-black animate-pulse">
                        ⏱️ ۴۵ ثانیه
                    </div>
                </div>

                <p class="text-[11px] text-slate-300 leading-relaxed truncate">{{ $m->problem_description }}</p>

                {{-- Earnings preview & Accept button --}}
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-400">ایاب و ذهاب تضمینی:</div>
                        <div class="font-black text-xs text-emerald-400">۶۰,۰۰۰ تومان + اجرت کار</div>
                    </div>

                    <button type="button" wire:click="acceptMission({{ $m->id }})" class="py-2 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-md shadow-emerald-600/20 transition">
                        ✓ قبول سرویس و اعزام
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 text-center space-y-2">
                <span class="text-2xl animate-spin inline-block">📡</span>
                <div class="text-xs font-bold text-slate-400">در حال رصد سفارش‌های جدید در حوزه شما...</div>
                <div class="text-[10px] text-slate-500">به محض ثبت سفارش توسط مشتری در مازندران، زنگ هشدار به صدا درخواهد آمد.</div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ASSISTANT TECHNICIAN MODAL --}}
    @if($showBackupModal)
    <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-xs font-black text-white flex items-center gap-2">
                    <span>🤝</span>
                    <span>درخواست نصاب کمکی و پشتیبان</span>
                </h3>
                <button type="button" @click="$wire.showBackupModal = false" class="text-slate-400 hover:text-white text-sm">✕</button>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed">
                در صورت نیاز به نیروی کمکی جهت کارهای دونفره، حمل وزن سنگین یا تجهیزات خاص، درخواست شما برای نزدیک‌ترین همکار ارسال می‌شود.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">علت نیاز به همکار</label>
                <select wire:model="backupReason" class="w-full h-9 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                    <option value="heavy_lifting">جابجایی دستگاه و وزن سنگین</option>
                    <option value="height_work">نصب در ارتفاع و نردبان بلند</option>
                    <option value="specialized_tools">نیاز به ابزار خاص (پمپ وکیوم، پرس)</option>
                    <option value="double_manpower">نیاز به نیروی کار همزمان</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">توضیحات تکمیلی برای همکار</label>
                <textarea wire:model="backupNotes" rows="2" placeholder="توضیح موقعیت یا نوع ابزار مورد نیاز..." class="w-full p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-500 font-sans"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="button" @click="$wire.showBackupModal = false" class="w-1/3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                    انصراف
                </button>
                <button type="button" wire:click="submitBackupRequest" class="w-2/3 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                    ارسال فراخوان کمکی
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
