<div class="space-y-4 font-sans select-none" @toggle-order-mode.window="setMode(mode === 'create' ? 'lookup' : 'create')">
    {{-- Top Mode Switcher (New Request vs Track Existing) --}}
    @if($step < 4)
    <div class="flex items-center justify-center p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl max-w-sm mx-auto shadow-inner">
        <button type="button" wire:click="setMode('create')"
                class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $mode === 'create' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
            ثبت درخواست جدید
        </button>
        <button type="button" wire:click="setMode('lookup')"
                class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $mode === 'lookup' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
            🔍 پیگیری سفارش قبلی
        </button>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- LOOKUP MODE: Search active order by mobile or order number                --}}
    {{-- ========================================================================= --}}
    @if($mode === 'lookup' && $step < 4)
    <div class="space-y-4 py-2">
        <div class="text-center space-y-1">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">پیگیری و رهگیری زنده سفارش</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">شماره موبایل یا شماره سفارش خود را وارد کنید تا موقعیت تکنسین را روی نقشه مشاهده فرمایید.</p>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شماره همراه کارفرما</label>
                <input type="text" wire:model="lookupPhone" placeholder="0911xxxxxxx" dir="ltr"
                       class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
            </div>

            <div class="text-center text-xs text-slate-400 font-medium">یا</div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">کد پیگیری سفارش</label>
                <input type="text" wire:model="lookupOrderNumber" placeholder="مثال: NB-14030101-1234" dir="ltr"
                       class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
            </div>

            @if($lookupError)
            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs text-center font-bold">
                {{ $lookupError }}
            </div>
            @endif

            <button type="button" wire:click="lookupOrder"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition active:scale-95">
                جستجو و رهگیری سفارش روی نقشه
            </button>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- CREATE MODE: Snapp-Style Steps                                             --}}
    {{-- ========================================================================= --}}
    @if($mode === 'create')
        {{-- Step Progress Indicator --}}
        @if($step < 4)
        <div class="flex items-center justify-between px-1 mb-2">
            <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-600' }}">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">۱</span>
                <span>انتخاب خدمت</span>
            </div>
            <div class="flex-1 mx-2 h-0.5 {{ $step >= 2 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
            <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-600' }}">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">۲</span>
                <span>شرح عیب و مشخصات</span>
            </div>
            <div class="flex-1 mx-2 h-0.5 {{ $step >= 4 ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
            <div class="flex items-center gap-1.5 text-xs font-bold {{ $step >= 4 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-600' }}">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $step >= 4 ? 'bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">۳</span>
                <span>رادار اعزام</span>
            </div>
        </div>
        @endif

        {{-- STEP 1: CATEGORY & LOCATION PIN CONFIRMATION --}}
        @if($step === 1)
        <div class="space-y-4">
            {{-- Selected Location Card on Map --}}
            <div class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-500/10 border border-indigo-200/80 dark:border-indigo-500/25 flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="text-xl shrink-0">📍</span>
                    <div class="min-w-0">
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">موقعیت مبدأ اعزام تکنسین (مرکز نقشه):</div>
                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">
                            {{ $location_name ?: 'مازندران، بابل' }}
                        </div>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 px-2 py-1 rounded-xl shadow-xs border border-indigo-100 dark:border-slate-700 shrink-0">
                    جابجایی با نقشه ↺
                </span>
            </div>

            {{-- Categories Selector --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">دستگاه یا خدمت مورد نیاز:</span>
                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">⏱️ اعزام زیر ۳۰ دقیقه</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-[300px] overflow-y-auto pr-0.5 custom-scrollbar">
                    @foreach($categories as $cat)
                    <button type="button" wire:click="selectCategory({{ $cat->id }})"
                            class="p-3 rounded-2xl border text-right transition-all duration-200 flex flex-col justify-between h-24 {{ $selectedCategoryId === $cat->id ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-500/20 shadow-sm ring-2 ring-indigo-500/20' : 'border-slate-200/80 dark:border-slate-750 bg-slate-50/60 dark:bg-slate-800/60 hover:border-indigo-300 dark:hover:border-slate-600' }}">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-2xl">{{ $cat->icon ?: '🛠️' }}</span>
                            @if($selectedCategoryId === $cat->id)
                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white leading-tight truncate">{{ $cat->name }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">اجرت پایه: {{ number_format($cat->base_labor_fee / 10) }} ت</div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Estimated Fee Bar --}}
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-400 font-medium">تعرفه مصوب ایاب و ذهاب تکنسین:</span>
                <span class="font-black text-slate-900 dark:text-white">۶۰,۰۰۰ تومان</span>
            </div>

            {{-- Big Snapp-Style CTA --}}
            <div class="pt-1">
                <button type="button" wire:click="nextStep"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/25 transition active:scale-95 flex items-center justify-center gap-2">
                    <span>تایید خدمت و تکمیل مشخصات</span>
                    <span>←</span>
                </button>
            </div>
        </div>
        @endif

        {{-- STEP 2: DETAILS, COMMON SYMPTOM TAGS & CONTACT --}}
        @if($step === 2)
        <div class="space-y-3.5">
            {{-- Appliance Brand / Model --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">برند و مدل دستگاه (اختیاری)</label>
                <input type="text" wire:model="appliance_brand_model" placeholder="مثال: پکیج دیواری بوتان مدل بیتا / اسپلیت ۲۴ هزار ال‌جی"
                       class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
            </div>

            {{-- Problem Description & Fast Symptom Chips --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شرح مشکل یا عیب دستگاه *</label>
                <textarea wire:model="problem_description" rows="2" placeholder="توضیح کوتاه درباره مشکل دستگاه یا انتخاب از علائم پرتکرار زیر..."
                          class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans"></textarea>
                @error('problem_description') <span class="text-rose-500 text-[11px] font-bold">{{ $message }}</span> @enderror

                {{-- Fast-Pick Symptom Chips (Just like Snapp/Tap30 options) --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @php
                        $commonSymptoms = [
                            'روشن نشدن مشعل',
                            'افت فشار بار مدار',
                            'آبریزش یا چکه آب',
                            'سردی آب مصرفی',
                            'صدای غیرعادی پمپ',
                            'سرمایش و پرتاب باد ضعیف',
                            'کد ارور و خطای برد',
                            'سرویس کامل دوره‌ای',
                        ];
                    @endphp
                    @foreach($commonSymptoms as $symptom)
                    <button type="button" wire:click="addProblemTag('{{ $symptom }}')"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 dark:bg-slate-800 dark:hover:bg-indigo-900/40 dark:text-slate-300 dark:hover:text-indigo-300 border border-slate-200/60 dark:border-slate-700/60 transition active:scale-95">
                        + {{ $symptom }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Priority Toggle (Normal vs Emergency) --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نوع و فوریت اعزام</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2 {{ $priority === 'normal' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                        <input type="radio" wire:model="priority" value="normal" class="hidden">
                        <span class="text-base">🕒</span>
                        <div>
                            <div class="text-xs font-bold">عادی</div>
                            <div class="text-[10px] text-slate-400">هماهنگی زمان مراجعه</div>
                        </div>
                    </label>

                    <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2 {{ $priority === 'emergency' ? 'border-rose-500 bg-rose-50 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                        <input type="radio" wire:model="priority" value="emergency" class="hidden">
                        <span class="text-base">🚨</span>
                        <div>
                            <div class="text-xs font-bold">اعزام فوری</div>
                            <div class="text-[10px] text-slate-400">زیر ۴۵ دقیقه</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Customer Name & Phone --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نام کارفرما *</label>
                    <input type="text" wire:model="customer_name" placeholder="نام و نام خانوادگی"
                           class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    @error('customer_name') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شماره همراه *</label>
                    <input type="text" wire:model="customer_phone" placeholder="0911xxxxxxx" dir="ltr"
                           class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    @error('customer_phone') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Address and Building details --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">آدرس خیابان/کوچه *</label>
                    <input type="text" wire:model="address" placeholder="خیابان اصلی، کوچه، محله"
                           class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    @error('address') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">پلاک / واحد</label>
                    <input type="text" wire:model="building_details" placeholder="پلاک ۴، ط ۲"
                           class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                </div>
            </div>

            {{-- Optional Referral Code --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">کد معرف فروشگاه همکار (اختیاری)</label>
                <input type="text" wire:model.live.debounce.400ms="referral_code" placeholder="مثال: NB-ABCDE" dir="ltr"
                       class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 font-sans uppercase">
                @if($referralStatusMessage)
                <div class="text-[11px] mt-1 font-bold {{ str_contains($referralStatusMessage, 'تایید') ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                    {{ $referralStatusMessage }}
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 pt-1">
                <button type="button" wire:click="prevStep"
                        class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    بازگشت
                </button>
                <button type="button" wire:click="submitOrder"
                        class="w-2/3 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-500/25 transition active:scale-95 flex items-center justify-center gap-2">
                    <span>درخواست تکنسین (اعزام هوشمند)</span>
                    <span>🚀</span>
                </button>
            </div>
        </div>
        @endif

        {{-- STEP 4: TRACKING & RADAR --}}
        @if($step === 4 && $createdOrderId)
        <div class="space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400">رهگیری زنده ماموریت</div>
                <button type="button" wire:click="startNewOrder" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                    + ثبت درخواست جدید دیگر
                </button>
            </div>

            @livewire('fieldservice::live-tracking', ['orderId' => $createdOrderId], key('tracking-'.$createdOrderId))
        </div>
        @endif
    @endif
</div>
