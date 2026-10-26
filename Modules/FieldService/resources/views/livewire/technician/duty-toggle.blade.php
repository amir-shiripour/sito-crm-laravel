<div class="font-sans select-none">
    @if($status === 'pending_approval')
    <button type="button" wire:click="toggleOnline"
            class="relative flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full border border-amber-500/35 bg-amber-500/15 text-amber-300 transition shadow-md"
            title="حساب شما در انتظار بررسی و تایید مدارک می‌باشد">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
        <span class="text-xs font-black tracking-wide">در انتظار تایید مدارک</span>
        <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold bg-amber-500/25 text-amber-200">بررسی</span>
    </button>
    @else
    <button type="button" wire:click="toggleOnline"
            class="relative flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full border transition-all duration-200 active:scale-95 shadow-md {{ $isOnline ? 'bg-emerald-500/15 border-emerald-500/35 text-emerald-400 shadow-emerald-500/10' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:text-slate-200' }}">
        
        {{-- Status indicator dot with glow --}}
        <div class="relative flex items-center justify-center">
            @if($isOnline)
            <span class="absolute w-3.5 h-3.5 rounded-full bg-emerald-400/30 animate-ping"></span>
            @endif
            <span class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-slate-500' }}"></span>
        </div>

        {{-- Status text --}}
        <span class="text-xs font-black tracking-wide">
            {{ $isOnline ? 'آماده به‌کار (آنلاین)' : 'استراحت (آفلاین)' }}
        </span>

        {{-- Tactile indicator pill --}}
        <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold {{ $isOnline ? 'bg-emerald-500/25 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
            {{ $isOnline ? 'فعال' : 'خاموش' }}
        </span>
    </button>
    @endif
</div>
