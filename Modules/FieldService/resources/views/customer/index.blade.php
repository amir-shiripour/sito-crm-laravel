<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>پرتال مشتریان نصاب‌باشی | درخواست هوشمند خدمات فنی منزل</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-indigo-600 selection:text-white flex flex-col items-center py-3 px-2 sm:py-6">

    {{-- Switcher Bar (Same as presentation prototype) --}}
    <div class="mb-4 flex flex-wrap items-center justify-center gap-1.5 bg-slate-950/90 backdrop-blur-md border border-slate-800 rounded-2xl px-3 py-1.5 shadow-xl z-50 text-xs">
        <a href="{{ route('user.fieldservice.dashboard') }}" class="px-2.5 py-1.5 text-slate-400 hover:text-white rounded-xl transition">
            ⚙️ پنل مدیریت CRM
        </a>
        <span class="w-px h-4 bg-slate-800"></span>
        <div class="px-2.5 py-1.5 bg-emerald-500/15 text-emerald-400 font-black rounded-xl border border-emerald-500/30 flex items-center gap-1">
            <span>👤</span>
            <span>پنل مشتری (فعال)</span>
        </div>
        <span class="w-px h-4 bg-slate-800"></span>
        <a href="{{ route('fieldservice.technician') }}" class="px-2.5 py-1.5 text-amber-400 hover:text-amber-300 font-bold rounded-xl transition">
            🔧 وب‌اپ تکنسین
        </a>
        <span class="w-px h-4 bg-slate-800"></span>
        <a href="{{ route('fieldservice.b2b') }}" class="px-2.5 py-1.5 text-sky-400 hover:text-sky-300 font-bold rounded-xl transition">
            🏢 پرتال مدیران ساختمان
        </a>
    </div>

    {{-- Mobile Container / Web-App Box --}}
    <div class="w-full max-w-lg bg-slate-950 rounded-[32px] shadow-2xl border border-slate-800 overflow-hidden flex flex-col">
        {{-- Header --}}
        <header class="p-4 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center font-black text-xs shadow-md shadow-emerald-500/20">
                    ن‌ب
                </div>
                <div>
                    <h1 class="text-sm font-black text-white">نصاب‌باشی مازندران</h1>
                    <p class="text-[10px] text-slate-400">اعزام فوری نزدیک‌ترین تکنسین متخصص</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ $activeTechniciansCount }} تکنسین آماده</span>
            </div>
        </header>

        {{-- Livewire Order Wizard --}}
        <main class="p-4 sm:p-5 flex-1">
            @livewire('fieldservice::order-wizard')
        </main>
    </div>

    @livewireScripts
</body>
</html>
