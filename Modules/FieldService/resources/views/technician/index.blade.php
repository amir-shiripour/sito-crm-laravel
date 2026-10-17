<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>وب‌اپلیکیشن تکنسین‌ها | نصاب‌باشی (سبک اسنپ رانندگان)</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-amber-500 selection:text-white flex flex-col items-center py-3 px-2 sm:py-6">

    {{-- Switcher Bar --}}
    <div class="mb-4 flex flex-wrap items-center justify-center gap-1.5 bg-slate-950/90 backdrop-blur-md border border-slate-800 rounded-2xl px-3 py-1.5 shadow-xl z-50 text-xs">
        <a href="{{ route('user.fieldservice.dashboard') }}" class="px-2.5 py-1.5 text-slate-400 hover:text-white rounded-xl transition">
            ⚙️ پنل مدیریت CRM
        </a>
        <span class="w-px h-4 bg-slate-800"></span>
        <a href="{{ route('fieldservice.customer') }}" class="px-2.5 py-1.5 text-emerald-400 hover:text-emerald-300 font-bold rounded-xl transition">
            👤 پنل مشتری
        </a>
        <span class="w-px h-4 bg-slate-800"></span>
        <div class="px-2.5 py-1.5 bg-amber-500/15 text-amber-400 font-black rounded-xl border border-amber-500/30 flex items-center gap-1">
            <span>🔧</span>
            <span>پنل تکنسین (فعال)</span>
        </div>
        <span class="w-px h-4 bg-slate-800"></span>
        <a href="{{ route('fieldservice.b2b') }}" class="px-2.5 py-1.5 text-sky-400 hover:text-sky-300 font-bold rounded-xl transition">
            🏢 مدیران ساختمان
        </a>
    </div>

    {{-- Mobile Container / Driver App Box --}}
    <div class="w-full max-w-lg bg-slate-950 rounded-[32px] shadow-2xl border border-slate-800 overflow-hidden flex flex-col">
        {{-- Driver Header --}}
        <header class="p-4 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/15 border border-amber-500/25 text-amber-400 flex items-center justify-center text-xl font-black">
                    👷‍♂️
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-black text-white">{{ $technician->full_name }}</span>
                        <span class="px-1.5 py-0.2 rounded text-[10px] {{ $technician->tier?->badgeClass() }}">
                            {{ $technician->tier?->label() }}
                        </span>
                    </div>
                    <div class="text-[11px] text-amber-400 mt-0.5 font-bold">★ {{ $technician->rating_avg }} ({{ $technician->completed_orders_count }} خدمت موفق)</div>
                </div>
            </div>

            {{-- Duty Switch --}}
            @livewire('fieldservice::technician-duty-toggle', ['technicianId' => $technician->id])
        </header>

        {{-- Mission Feed & Active Panel --}}
        <main class="p-4 sm:p-5 flex-1 space-y-4">
            @livewire('fieldservice::mission-feed', ['technicianId' => $technician->id])
        </main>
    </div>

    @livewireScripts
</body>
</html>
