<div class="space-y-4 font-sans text-xs">
    @if($building)
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-black text-white">{{ $building->name }}</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30">
                        {{ $building->units_count }} واحد / {{ $building->floors_count }} طبقه
                    </span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    مدیر مجتمع: <span class="text-slate-200 font-bold">{{ $building->manager_name }}</span> ({{ $building->manager_phone }})
                </div>
            </div>

            <button type="button" @click="$wire.showAddEquipModal = true" class="py-1.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition">
                + افزودن تجهیز
            </button>
        </div>

        {{-- SLA Contract Banner --}}
        @php $activeSla = $building->activeSlaContract(); @endphp
        @if($activeSla)
        <div class="p-3 rounded-xl bg-gradient-to-r from-amber-950/60 to-slate-900 border border-amber-500/30 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">🏆</span>
                <div>
                    <div class="font-black text-amber-300 text-xs">{{ $activeSla->title }}</div>
                    <div class="text-[10px] text-slate-400">حداکثر زمان پاسخ به فوریت: {{ $activeSla->emergency_response_hours }} ساعت | {{ $activeSla->periodic_visits_per_month }} بازدید ادواری در ماه</div>
                </div>
            </div>
            <span class="px-2 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-[10px] font-black">فعال</span>
        </div>
        @endif

        {{-- Equipment Inventory List --}}
        <div class="space-y-2 pt-1">
            <div class="font-bold text-slate-300 text-xs">تجهیزات و شناسنامه فنی ثبت‌شده:</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @forelse($building->equipments as $eq)
                <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white text-[11px]">{{ $eq->title }}</span>
                        <span class="w-2 h-2 rounded-full {{ $eq->health_status === 'good' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                    </div>
                    <div class="text-[10px] text-slate-400">مدل: {{ $eq->brand_model ?: 'استاندارد' }} | سال نصب: {{ $eq->installation_year }}</div>
                    <div class="text-[10px] text-emerald-400">وضعیت سلامت: {{ $eq->health_status === 'good' ? 'مساعد و پایش‌شده' : 'نیازمند سرویس' }}</div>
                </div>
                @empty
                <div class="col-span-full py-4 text-center text-slate-500">
                    تجهیزی ثبت نشده است.
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- Add Equipment Modal --}}
    @if($showAddEquipModal)
    <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-3.5 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                <h3 class="text-xs font-black text-white">افزودن تجهیز به شناسنامه فنی ساختمان</h3>
                <button type="button" @click="$wire.showAddEquipModal = false" class="text-slate-400 hover:text-white text-sm">✕</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">نوع تجهیز</label>
                <select wire:model="equipment_type" class="w-full h-8 px-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                    <option value="boiler_room">موتورخانه مرکزی و دیگ</option>
                    <option value="absorption_chiller">چیلر جذبی یا تراکمی</option>
                    <option value="booster_pump">بوستر پمپ آبرسانی</option>
                    <option value="elevator">آسانسور و بالابر</option>
                    <option value="generator">دیزل ژنراتور اضطراری</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">نام یا عنوان مشخصه</label>
                <input type="text" wire:model="title" placeholder="مثال: دیگ چدنی شوفاژکار ۳۰۰ هزار" class="w-full h-8 px-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">برند و مدل</label>
                    <input type="text" wire:model="brand_model" placeholder="برند" class="w-full h-8 px-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">سال نصب</label>
                    <input type="text" wire:model="installation_year" placeholder="۱۴۰۱" class="w-full h-8 px-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="button" @click="$wire.showAddEquipModal = false" class="w-1/3 py-2 bg-slate-800 text-slate-300 font-bold text-xs rounded-xl">انصراف</button>
                <button type="button" wire:click="addEquipment" class="w-2/3 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-md">ثبت تجهیز</button>
            </div>
        </div>
    </div>
    @endif
</div>
