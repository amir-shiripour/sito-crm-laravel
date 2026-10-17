<div class="space-y-3 font-sans text-xs">
    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <h4 class="font-black text-white text-xs flex items-center gap-1.5">
            <span>🧾</span>
            <span>صدور فاکتور تفکیکی اقلام کار</span>
        </h4>
        <span class="text-[10px] text-slate-400">کمیسیون پلتفرم: ۱۵٪</span>
    </div>

    {{-- Items list --}}
    <div class="space-y-1.5">
        @foreach($items as $idx => $it)
        <div class="p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
            <div>
                <div class="font-bold text-white text-[11px] flex items-center gap-1">
                    <span class="text-[10px] px-1 py-0.2 rounded {{ $it['item_type'] === 'labor' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                        {{ $it['item_type'] === 'labor' ? 'اجرت' : 'قطعه' }}
                    </span>
                    <span>{{ $it['title'] }}</span>
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                    {{ number_format($it['unit_price'] / 10) }} تومان × {{ $it['quantity'] }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="font-black text-white text-[11px]">{{ number_format($it['total_price'] / 10) }} ت</span>
                <button type="button" wire:click="removeItem({{ $idx }})" class="text-rose-400 hover:text-rose-300 p-1 text-[11px]">✕</button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add item form --}}
    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-2.5">
        <div class="grid grid-cols-3 gap-2">
            <select wire:model="new_item_type" class="h-8 px-2 rounded-lg bg-slate-800 border border-slate-700 text-[11px] text-white font-sans">
                <option value="labor">اجرت کار</option>
                <option value="part">قطعه یدکی</option>
            </select>
            <input type="text" wire:model="new_title" placeholder="عنوان قلم کار..." class="col-span-2 h-8 px-2.5 rounded-lg bg-slate-800 border border-slate-700 text-[11px] text-white placeholder-slate-500 font-sans">
        </div>

        <div class="grid grid-cols-2 gap-2">
            <input type="number" wire:model="new_unit_price" placeholder="مبلغ (ریال)..." class="h-8 px-2.5 rounded-lg bg-slate-800 border border-slate-700 text-[11px] text-white placeholder-slate-500 font-sans">
            <button type="button" wire:click="addItem" class="h-8 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] rounded-lg transition">
                + افزودن به فاکتور
            </button>
        </div>
    </div>

    {{-- Calculation summary --}}
    <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 space-y-1.5 text-[11px]">
        <div class="flex items-center justify-between text-slate-400">
            <span>مجموع اجرت:</span>
            <span class="font-bold text-white">{{ number_format($laborTotal / 10) }} ت</span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>مجموع قطعات تعویضی:</span>
            <span class="font-bold text-white">{{ number_format($partsTotal / 10) }} ت</span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>ایاب و ذهاب ثابت:</span>
            <span class="font-bold text-white">{{ number_format($travel_fee / 10) }} ت</span>
        </div>
        <div class="flex items-center justify-between text-purple-400">
            <span>سهم کمیسیون نصاب‌باشی (۱۵٪ از اجرت):</span>
            <span class="font-bold">- {{ number_format($commission / 10) }} ت</span>
        </div>
        <div class="pt-2 border-t border-slate-800 flex items-center justify-between font-black text-xs">
            <span class="text-emerald-400">درآمد خالص واریزی تکنسین:</span>
            <span class="text-emerald-400 text-sm">{{ number_format($netIncome / 10) }} تومان</span>
        </div>
    </div>

    {{-- Submit Invoice --}}
    <button type="button" wire:click="submitInvoice" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg transition">
        صدور و ارسال فاکتور دیجیتال به مشتری
    </button>
</div>
