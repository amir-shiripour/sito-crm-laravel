<div class="space-y-4 font-sans" x-data="fleetMapHandler(@js($mapConfig), @js($technicians), @js($activeOrders))" x-init="initMap()">
    {{-- Header controls --}}
    <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-black text-gray-900 dark:text-white">مانیتورینگ GIS ناوگان استان مازندران</h1>
                <span class="px-2 py-0.5 text-[11px] rounded-md bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20">
                    پایش زنده
                </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                توزیع موقعیت مکانی لحظه‌ای تکنسین‌های آزاد، مشغول و ماموریت‌های ثبت‌شده در شهرهای استان
            </p>
        </div>

        {{-- Filters & City Selection --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- City selector --}}
            <select wire:model.live="selectedCity" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans">
                <option value="all">📍 تمامی شهرهای مازندران</option>
                <option value="بابل">بابل (مرکز عملیات)</option>
                <option value="ساری">ساری</option>
                <option value="قائمشهر">قائمشهر</option>
                <option value="آمل">آمل</option>
                <option value="بابلسر">بابلسر</option>
            </select>

            {{-- Status Filter --}}
            <select wire:model.live="selectedFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans">
                <option value="all">همه وضعیت‌ها</option>
                <option value="online">🟢 آزاد و آنلاین</option>
                <option value="busy">🟡 مشغول ماموریت</option>
            </select>

            <button type="button" @click="resetView()" class="h-9 px-3 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold hover:bg-indigo-100 transition">
                مرکز نقشه (بابل)
            </button>
        </div>
    </div>

    {{-- Map Canvas & Floating Fleet Card --}}
    <div class="relative w-full rounded-3xl overflow-hidden border border-gray-200/80 dark:border-gray-700/80 shadow-lg bg-gray-100 dark:bg-gray-900" style="height: 580px;">
        <div id="fleetMap" class="w-full h-full z-0" wire:ignore></div>

        {{-- Floating Summary Overlay --}}
        <div class="absolute top-4 right-4 z-[400] bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-3.5 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xl flex items-center gap-4 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-gray-600 dark:text-gray-300 font-bold">تکنسین آزاد:</span>
                <span class="font-black text-gray-900 dark:text-white">{{ $stats['online_count'] }}</span>
            </div>
            <span class="w-px h-4 bg-gray-200 dark:bg-gray-700"></span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <span class="text-gray-600 dark:text-gray-300 font-bold">در ماموریت:</span>
                <span class="font-black text-gray-900 dark:text-white">{{ $stats['busy_count'] }}</span>
            </div>
            <span class="w-px h-4 bg-gray-200 dark:bg-gray-700"></span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                <span class="text-gray-600 dark:text-gray-300 font-bold">سفارش‌های فعال:</span>
                <span class="font-black text-gray-900 dark:text-white">{{ $stats['orders_count'] }}</span>
            </div>
        </div>

        {{-- Map Tile Provider Badge --}}
        <div class="absolute bottom-3 left-3 z-[400] bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm px-2.5 py-1 rounded-lg border border-gray-200/70 dark:border-gray-700/60 text-[10px] text-gray-500 dark:text-gray-400">
            سرویس نقشه: <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mapConfig['name'] }}</span>
        </div>
    </div>
</div>
