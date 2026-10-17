<div class="space-y-4 font-sans">
    {{-- Step Progress Indicator --}}
    <div class="flex items-center justify-between px-2 mb-2">
        <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 1 ? 'text-emerald-400' : 'text-slate-600' }}">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 1 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-500' }}">۱</span>
            <span>انتخاب دستگاه</span>
        </div>
        <div class="w-6 h-px {{ $step >= 2 ? 'bg-emerald-500' : 'bg-slate-800' }}"></div>
        <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 2 ? 'text-emerald-400' : 'text-slate-600' }}">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 2 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-500' }}">۲</span>
            <span>شرح ایراد</span>
        </div>
        <div class="w-6 h-px {{ $step >= 3 ? 'bg-emerald-500' : 'bg-slate-800' }}"></div>
        <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 3 ? 'text-emerald-400' : 'text-slate-600' }}">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 3 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-500' }}">۳</span>
            <span>موقعیت و اعزام</span>
        </div>
    </div>

    {{-- STEP 1: CATEGORY SELECTION --}}
    @if($step === 1)
    <div class="space-y-3">
        <div class="text-xs font-bold text-slate-300">دستگاه مورد نظر جهت سرویس یا تعمیر را انتخاب کنید:</div>
        <div class="grid grid-cols-2 gap-2.5">
            @foreach($categories as $cat)
            <button type="button" wire:click="selectCategory({{ $cat->id }})" class="p-3 rounded-2xl border text-right transition flex flex-col justify-between h-24 {{ $selectedCategoryId === $cat->id ? 'border-emerald-500 bg-emerald-500/10' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700' }}">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">{{ $cat->icon ?: '🛠️' }}</span>
                    @if($selectedCategoryId === $cat->id)
                    <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]">✓</span>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-black text-white">{{ $cat->name }}</div>
                    <div class="text-[10px] text-slate-400">اجرت پایه: {{ number_format($cat->base_labor_fee / 10) }} ت</div>
                </div>
            </button>
            @endforeach
        </div>

        <div class="pt-2">
            <button type="button" wire:click="nextStep" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition">
                مرحله بعد: ثبت ایراد دستگاه ←
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 2: PROBLEM DETAILS --}}
    @if($step === 2)
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1.5">برند و مدل دستگاه (اختیاری)</label>
            <input type="text" wire:model="appliance_brand_model" placeholder="مثال: پکیج بوتان پارما / کولر گری ۱۸۰۰۰" class="w-full h-10 px-3.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-emerald-500 font-sans">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1.5">شرح مشکل یا عیب فنی دستگاه</label>
            <textarea wire:model="problem_description" rows="3" placeholder="توضیح کوتاه درباره مشکل دستگاه (مثال: روشن نشدن مشعل، آب‌ریزی زیر پکیج، افت فشار یا کد ارور E01)" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-emerald-500 font-sans"></textarea>
            @error('problem_description') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1.5">میزان فوریت</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="p-2.5 rounded-xl border cursor-pointer text-center text-xs font-bold transition {{ $priority === 'normal' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400' : 'border-slate-800 text-slate-400' }}">
                    <input type="radio" wire:model="priority" value="normal" class="hidden">
                    عادی (طی روز جاری)
                </label>
                <label class="p-2.5 rounded-xl border cursor-pointer text-center text-xs font-bold transition {{ $priority === 'emergency' ? 'border-rose-500 bg-rose-500/10 text-rose-400' : 'border-slate-800 text-slate-400' }}">
                    <input type="radio" wire:model="priority" value="emergency" class="hidden">
                    🚨 فوری (اعزام زیر ۴۵ دقیقه)
                </label>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="button" wire:click="prevStep" class="w-1/3 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                بازگشت
            </button>
            <button type="button" wire:click="nextStep" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition">
                مرحله بعد: آدرس و اعزام ←
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 3: LOCATION & CONTACT --}}
    @if($step === 3)
    <div class="space-y-3.5">
        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">نام و نام خانوادگی</label>
                <input type="text" wire:model="customer_name" placeholder="نام کارفرما" class="w-full h-9 px-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-emerald-500 font-sans">
                @error('customer_name') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">شماره همراه</label>
                <input type="text" wire:model="customer_phone" placeholder="0911xxxxxxx" class="w-full h-9 px-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-emerald-500 font-sans" dir="ltr">
                @error('customer_phone') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">شهر محل سکونت در مازندران</label>
            <select wire:model="city" class="w-full h-9 px-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:border-emerald-500 font-sans">
                <option value="بابل">بابل</option>
                <option value="ساری">ساری</option>
                <option value="قائمشهر">قائمشهر</option>
                <option value="آمل">آمل</option>
                <option value="بابلسر">بابلسر</option>
                <option value="محمودآباد">محمودآباد</option>
                <option value="نکا">نکا</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">آدرس دقیق، محله و پلاک</label>
            <textarea wire:model="address" rows="2" placeholder="خیابان، کوچه، مجتمع، واحد..." class="w-full p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-emerald-500 font-sans"></textarea>
            @error('address') <span class="text-rose-400 text-[10px]">{{ $message }}</span> @enderror
        </div>

        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 space-y-1">
            <div class="flex items-center justify-between">
                <span>هزینه تضمینی ایاب و ذهاب تکنسین:</span>
                <span class="font-bold text-white">۶۰,۰۰۰ تومان</span>
            </div>
            <div class="text-[10px] text-emerald-400">✓ ضمانت رسمی ۹۰ روزه کیفیت خدمات پس از پرداخت امن</div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="button" wire:click="prevStep" class="w-1/3 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                بازگشت
            </button>
            <button type="button" wire:click="submitOrder" class="w-2/3 py-3 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition">
                🚀 فعال‌سازی رادار و اعزام تکنسین
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 4: TRACKING & RADAR --}}
    @if($step === 4 && $createdOrderId)
    <div class="space-y-4">
        @livewire('fieldservice::live-tracking', ['orderId' => $createdOrderId], key('tracking-'.$createdOrderId))
    </div>
    @endif
</div>
