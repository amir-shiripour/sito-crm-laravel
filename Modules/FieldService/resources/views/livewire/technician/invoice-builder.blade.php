<div class="space-y-3.5 font-sans text-xs select-none">
    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2.5">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-sm font-black">
                🧾
            </span>
            <div>
                <h4 class="font-black text-white text-xs">صدور فاکتور تفکیکی و محاسبه دستمزد</h4>
                <div class="text-[10px] text-slate-400">اقلام اجرت و قطعات مصرفی را ثبت و تایید کنید</div>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/25">
            کمیسیون {{ $platformName ?? 'سامانه' }}: ۱۵٪
        </span>
    </div>

    {{-- Items list --}}
    <div class="space-y-1.5 max-h-56 overflow-y-auto custom-scrollbar p-0.5">
        @foreach($items as $idx => $it)
        <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-slate-700 flex items-center justify-between transition">
            <div class="min-w-0 flex-1">
                <div class="font-bold text-white text-[11px] flex items-center gap-1.5">
                    <span class="text-[9px] px-1.5 py-0.5 rounded-md font-black {{ $it['item_type'] === 'labor' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                        {{ $it['item_type'] === 'labor' ? 'اجرت کار' : 'قطعه یدکی' }}
                    </span>
                    <span class="truncate">{{ $it['title'] }}</span>
                </div>
                <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                    <span>واحد: {{ number_format($it['unit_price'] / 10) }} تومان</span>
                    <span>•</span>
                    <span>تعداد: {{ $it['quantity'] }}</span>
                    @if(!empty($it['warranty_months']))
                    <span>•</span>
                    <span class="text-emerald-400">{{ $it['warranty_months'] }} ماه گارانتی</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 mr-2">
                <span class="font-black text-white text-xs">{{ number_format($it['total_price'] / 10) }} ت</span>
                <button type="button" wire:click="removeItem({{ $idx }})" 
                        title="حذف قلم"
                        class="w-6 h-6 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 flex items-center justify-center text-xs transition">
                    ✕
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add item form --}}
    <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2.5">
        <div class="text-[11px] font-bold text-slate-300 flex items-center gap-1">
            <span>+</span>
            <span>افزودن قلم جدید به فاکتور:</span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <select wire:model="new_item_type" class="h-9 px-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="labor">اجرت کار</option>
                <option value="part">قطعه یدکی</option>
            </select>
            <input type="text" wire:model="new_title" placeholder="شرح خدمت یا نام قطعه..." class="col-span-2 h-9 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-500 font-sans focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="col-span-2 relative">
                <input type="number" wire:model="new_unit_price" placeholder="مبلغ (ریال)..." class="w-full h-9 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-500 font-sans focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="absolute left-3 top-2.5 text-[10px] text-slate-500 font-bold">ریال</span>
            </div>
            <button type="button" wire:click="addItem" class="h-9 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-1">
                <span>ثبت قلم</span>
                <span>✓</span>
            </button>
        </div>
        @error('new_title') <span class="text-rose-400 text-[10px] font-bold">{{ $message }}</span> @enderror
        @error('new_unit_price') <span class="text-rose-400 text-[10px] font-bold">{{ $message }}</span> @enderror
    </div>

    {{-- Calculation summary --}}
    <div class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 space-y-2 text-[11px]">
        <div class="flex items-center justify-between text-slate-400">
            <span>مجموع اجرت فنی:</span>
            <span class="font-bold text-white">{{ number_format($laborTotal / 10) }} تومان</span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>مجموع قطعات تعویضی:</span>
            <span class="font-bold text-white">{{ number_format($partsTotal / 10) }} تومان</span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>ایاب و ذهاب تضمینی:</span>
            <span class="font-bold text-white">{{ number_format($travel_fee / 10) }} تومان</span>
        </div>
        <div class="flex items-center justify-between text-indigo-300">
            <span>سهم کمیسیون {{ $platformName ?? 'سامانه' }} (۱۵٪ از اجرت):</span>
            <span class="font-bold">- {{ number_format($commission / 10) }} تومان</span>
        </div>

        <div class="pt-2.5 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs font-black text-emerald-400">درآمد خالص واریزی به تکنسین:</span>
            <div class="text-right">
                <span class="text-sm font-black text-emerald-400">{{ number_format($netIncome / 10) }}</span>
                <span class="text-[10px] font-bold text-emerald-500">تومان</span>
            </div>
        </div>
    </div>

    {{-- Submit Invoice --}}
    <button type="button" wire:click="submitInvoice" 
            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl shadow-emerald-600/25 transition-all flex items-center justify-center gap-2">
        <span>صدور و ارسال فاکتور دیجیتال به مشتری</span>
        <span>←</span>
    </button>
</div>
