<div>
    <button type="button" wire:click="toggleOnline" class="flex items-center gap-2 px-3 py-1.5 rounded-full border transition-all text-xs font-bold {{ $isOnline ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400 shadow-sm shadow-emerald-500/20' : 'bg-slate-800 border-slate-700 text-slate-400' }}">
        <span class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
        <span>{{ $isOnline ? 'آنلاین' : 'آفلاین' }}</span>
    </button>
</div>
