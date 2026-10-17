<div class="p-4 rounded-2xl bg-gradient-to-br from-rose-950/40 via-slate-900 to-slate-900 border border-rose-500/30 space-y-3 font-sans text-xs">
    <div class="flex items-center gap-2 border-b border-rose-500/20 pb-2.5">
        <span class="text-base animate-pulse">🚨</span>
        <h3 class="font-black text-rose-300 text-xs sm:text-sm">درخواست اعزام اضطراری و فوری تاسیسات ساختمان</h3>
    </div>

    <p class="text-[11px] text-slate-400 leading-relaxed">
        در شرایط ترکیدگی لوله، افت ناگهانی فشار آب، خاموشی مشعل موتورخانه یا خرابی آسانسور، درخواست شما با اولویت قرمز در شبکه رادار استان مازندران منتشر شده و نزدیک‌ترین تیم ارشد اعزام خواهد شد.
    </p>

    <div class="space-y-3 pt-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">مجتمع یا ساختمان مربوطه</label>
                <select wire:model="buildingId" class="w-full h-9 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                    @foreach($buildings as $b)
                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">بخش حادثه‌دیده</label>
                <select wire:model="appliance_type" class="w-full h-9 px-3 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-sans">
                    <option value="boiler">موتورخانه مرکزی و مشعل</option>
                    <option value="plumbing">ترکیدگی لوله و نشتی شدید</option>
                    <option value="booster_pump">افت فشار آب و از کار افتادن پمپ</option>
                    <option value="elevator">خرابی آسانسور و گیر افتادن کابین</option>
                    <option value="chiller">چیلر و تهویه مرکزی</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">شرح دقیق حادثه و وضعیت بحران</label>
            <textarea wire:model="problem_description" rows="2" placeholder="توضیح کوتاه موقعیت اضطراری (مثال: ترکیدگی رایزر اصلی طبقه ۳ و آب‌گرفتگی پارکینگ)" class="w-full p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-500 font-sans"></textarea>
            @error('problem_description') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
        </div>

        <button type="button" wire:click="dispatchEmergency" class="w-full py-3 bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-2">
            <span>🚨</span>
            <span>اعزام فوری تیم بحران به محل مجتمع (زیر ۴۵ دقیقه)</span>
        </button>
    </div>
</div>
