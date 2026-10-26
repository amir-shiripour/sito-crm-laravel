<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>پرتال سازمانی B2B مدیران ساختمان و فروشگاه‌های همکار | {{ $platformName ?? 'خدمات فنی' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-sky-500 selection:text-white flex flex-col items-center py-3 px-2 sm:py-6">

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
        <a href="{{ route('fieldservice.technician') }}" class="px-2.5 py-1.5 text-amber-400 hover:text-amber-300 font-bold rounded-xl transition">
            🔧 پنل تکنسین
        </a>
        <span class="w-px h-4 bg-slate-800"></span>
        <div class="px-2.5 py-1.5 bg-sky-500/15 text-sky-400 font-black rounded-xl border border-sky-500/30 flex items-center gap-1">
            <span>🏢</span>
            <span>مدیران ساختمان (فعال)</span>
        </div>
    </div>

    {{-- Container --}}
    <div class="w-full max-w-2xl bg-slate-950 rounded-[32px] shadow-2xl border border-slate-800 overflow-hidden flex flex-col">
        {{-- Header --}}
        <header class="p-5 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-500/15 border border-sky-500/25 text-sky-400 flex items-center justify-center text-xl font-black">
                    🏢
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-black text-white">پرتال سازمانی تاسیسات و مجتمع‌ها</h1>
                    <p class="text-[11px] text-slate-400">شناسنامه فنی، قراردادهای پیشگیرانه ادواری و اعزام اضطراری</p>
                </div>
            </div>

            <span class="px-3 py-1 rounded-full bg-sky-500/15 border border-sky-500/30 text-sky-400 text-xs font-bold">
                B2B Services
            </span>
        </header>

        <main class="p-5 space-y-6 flex-1">
            {{-- Building Technical Passport --}}
            @livewire('fieldservice::building-passport')

            {{-- Emergency Facility Dispatch --}}
            @livewire('fieldservice::emergency-dispatch')
        </main>
    </div>

    @livewireScripts
</body>
</html>
