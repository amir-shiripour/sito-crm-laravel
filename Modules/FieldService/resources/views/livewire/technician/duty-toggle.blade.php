<div class="font-sans select-none">
    @if($status === 'pending_approval')
    <button type="button" wire:click="toggleOnline"
            class="relative flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full border border-amber-500/40 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 transition-all shadow-xs"
            title="حساب شما در انتظار بررسی و تایید مدارک است">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span class="text-xs font-bold tracking-tight">در انتظار تایید مدارک</span>
        <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold bg-amber-200/60 dark:bg-amber-500/25 text-amber-800 dark:text-amber-200">بررسی</span>
    </button>
    @else
    <button type="button" wire:click="toggleOnline"
            class="relative flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full border transition-all duration-200 active:scale-95 shadow-xs {{ $isOnline ? 'bg-emerald-50 dark:bg-emerald-500/15 border-emerald-300 dark:border-emerald-500/35 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-850/80 border-slate-200 dark:border-slate-750 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
        
        {{-- Status indicator dot --}}
        <div class="relative flex items-center justify-center">
            @if($isOnline)
            <span class="absolute w-3.5 h-3.5 rounded-full bg-emerald-400/40 animate-ping"></span>
            @endif
            <span class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-slate-400 dark:bg-slate-500' }}"></span>
        </div>

        {{-- Status text --}}
        <span class="text-xs font-bold tracking-tight">
            {{ $isOnline ? 'آماده به‌کار (آنلاین)' : 'استراحت (آفلاین)' }}
        </span>

        {{-- Tactile indicator badge --}}
        <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold {{ $isOnline ? 'bg-emerald-200/70 dark:bg-emerald-500/25 text-emerald-800 dark:text-emerald-200' : 'bg-slate-200 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300' }}">
            {{ $isOnline ? 'فعال' : 'خاموش' }}
        </span>
    </button>
    @endif
</div>
