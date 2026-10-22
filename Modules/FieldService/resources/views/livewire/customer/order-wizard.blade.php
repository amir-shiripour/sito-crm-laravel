<div class="space-y-3 font-sans select-none" @toggle-order-mode.window="setMode(mode === 'create' ? 'lookup' : 'create')">

    {{-- ========================================================================= --}}
    {{-- LOOKUP MODE: Search active order by mobile or order number                --}}
    {{-- ========================================================================= --}}
    @if($mode === 'lookup' && $step < 4)
    <div class="space-y-4 py-2">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">پیگیری و رهگیری سفارش</h3>
            <button type="button" wire:click="setMode('create')" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                ← بازگشت به نقشه
            </button>
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
    {{-- CREATE MODE: Authentic Snapp-Style Experience                             --}}
    {{-- ========================================================================= --}}
    @if($mode === 'create')
        {{-- STEP 1: SNAPP-NATIVE ORIGIN CARD (مبدأ) --}}
        @if($step === 1)
        <div class="space-y-3">
            {{-- Drag Handle Indicator --}}
            <div class="pt-1 pb-1 flex justify-center">
                <span class="w-10 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
            </div>

            {{-- Address Search Bar (Identical to Snapp screenshot) --}}
            <div @click="$dispatch('open-search-modal')"
                 class="p-3 sm:p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3 shadow-xs cursor-pointer hover:border-indigo-400 transition-all">
                {{-- Search Icon on Left (RTL) --}}
                <div class="w-8 h-8 rounded-xl bg-white dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 shrink-0 shadow-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                {{-- Center: Location Text --}}
                <div class="min-w-0 flex-1 text-center sm:text-right">
                    <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">
                        <span x-text="selectedLocationLabel || '{{ $location_name ?: 'مازندران، بابل' }}'">{{ $location_name ?: 'مازندران، بابل' }}</span>
                    </div>
                </div>

                {{-- Blue Target Dot on Right (RTL) --}}
                <div class="shrink-0 flex items-center justify-center">
                    <span class="w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/50 flex-shrink-0"></span>
                </div>
            </div>

            {{-- Order For 'Other' indicator pill if selected --}}
            @if($order_for === 'other')
            <div class="p-2 rounded-xl bg-indigo-50/80 dark:bg-indigo-500/15 border border-indigo-200/80 dark:border-indigo-500/30 flex items-center justify-between text-xs text-indigo-700 dark:text-indigo-300">
                <div class="flex items-center gap-1.5 font-bold">
                    <span>👥</span>
                    <span>ثبت سفارش برای شخص دیگر فعال است</span>
                </div>
                <button type="button" wire:click="setOrderFor('self')" class="text-[11px] font-bold text-indigo-600 hover:underline">
                    تغییر به خودم
                </button>
            </div>
            @endif

            {{-- Full-Width Snapp Primary Button (تأیید محل اعزام) --}}
            <div class="pt-1">
                <button type="button"
                        @click="$wire.confirmLocationWithAddress(map ? map.getCenter().lat : {{ $lat }}, map ? map.getCenter().lng : {{ $lng }}, selectedLocationLabel, currentCity)"
                        class="w-full py-3.5 sm:py-4 bg-[#00a651] hover:bg-[#009247] active:scale-[0.98] text-white font-black text-sm sm:text-base rounded-2xl shadow-xl shadow-emerald-600/25 transition-all flex items-center justify-center gap-2">
                    <span>تأیید محل اعزام</span>
                    <span>←</span>
                </button>
            </div>
        </div>
        @endif

        {{-- STEP 2: SNAPP-EXACT SERVICE SELECTION & INLINE TABS --}}
        @if($step === 2)
        <div class="space-y-3 font-sans">
            {{-- Drag Handle / Collapse Indicator for Mobile --}}
            <div @click="$dispatch('toggle-sheet')" class="pt-0.5 pb-1 flex justify-center cursor-pointer select-none">
                <span class="w-10 h-1 rounded-full bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 transition-colors"></span>
            </div>

            {{-- ============================================================= --}}
            {{-- SUB-TAB 1: MAIN SERVICE SELECTION & INSTANT BOOKING           --}}
            {{-- ============================================================= --}}
            @if($activeSubTab === 'main')
            <div class="space-y-3">
                {{-- Category Grid: 2 columns matching CRM UI and user specification --}}
                <div>
                    <div class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-1.5 px-0.5 flex items-center justify-between">
                        <span>انتخاب نوع خدمت و دستگاه:</span>
                        @if($categories->count() > 3)
                        <button type="button" wire:click="setSubTab('all_services')" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline flex items-center gap-1 transition">
                            <span>خدمات کامل ({{ $categories->count() }})</span>
                            <span class="text-xs">←</span>
                        </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:gap-2.5">
                        @foreach($mainCategories as $cat)
                        <button type="button" wire:click="selectCategory({{ $cat->id }})"
                                class="p-2.5 sm:p-3 rounded-2xl flex items-center gap-2.5 text-right transition-all cursor-pointer relative {{ $selectedCategoryId === $cat->id ? 'bg-indigo-50/90 dark:bg-indigo-500/20 border-2 border-indigo-600 dark:border-indigo-500 shadow-sm shadow-indigo-600/10' : 'bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-750 border border-slate-200/70 dark:border-slate-700/60' }}">
                            
                            {{-- Icon in soft rounded badge --}}
                            <div class="w-10 h-10 rounded-xl {{ $selectedCategoryId === $cat->id ? 'bg-indigo-100 dark:bg-indigo-500/30 text-indigo-700 dark:text-indigo-200' : 'bg-white dark:bg-slate-700/80 shadow-xs' }} flex items-center justify-center text-xl shrink-0">
                                {{ $cat->icon ?: '🛠️' }}
                            </div>

                            {{-- Title and Subtitle --}}
                            <div class="min-w-0 flex-1">
                                <div class="text-xs sm:text-[13px] font-black text-slate-800 dark:text-slate-100 truncate">
                                    {{ $cat->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold truncate mt-0.5 font-sans">
                                    {{ $cat->base_fee ? number_format($cat->base_fee) . ' ت' : 'شروع از ۳۵۰،۰۰۰ ت' }}
                                </div>
                            </div>

                            {{-- Active selection indicator --}}
                            @if($selectedCategoryId === $cat->id)
                            <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                            @endif
                        </button>
                        @endforeach

                        {{-- 4th slot: "خدمات کامل" button if there are more categories --}}
                        @if($categories->count() > 3)
                        <button type="button" wire:click="setSubTab('all_services')"
                                class="p-2.5 sm:p-3 rounded-2xl flex items-center justify-between text-right transition-all cursor-pointer bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-750 border border-dashed border-indigo-300 dark:border-indigo-600/70 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0 font-black group-hover:scale-105 transition-transform">
                                    ⊞
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-[13px] font-black text-indigo-600 dark:text-indigo-400 truncate flex items-center gap-1">
                                        <span>خدمات کامل</span>
                                        <span class="text-xs">←</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold truncate mt-0.5">
                                        سایر دستگاه‌ها ({{ $categories->count() }})
                                    </div>
                                </div>
                            </div>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Two Snapp-Style Service Rows (Normal vs Emergency) --}}
                <div class="space-y-1.5 pt-0.5">
                    {{-- Row 1: نصاب‌باشی (اعزام عادی) --}}
                    <div wire:click="setPriority('normal')"
                         class="w-full p-3 rounded-2xl flex items-center justify-between cursor-pointer transition-all duration-150 {{ $priority === 'normal' ? 'bg-[#f0fdf4] dark:bg-emerald-950/25 border border-emerald-500/30 shadow-xs' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent' }}">
                        
                        {{-- Right in RTL: Icon + Title + Subtitle --}}
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                                🚗
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>نصاب‌باشی</span>
                                    @if($priority === 'normal')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @endif
                                </div>
                                <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-bold mt-0.5">
                                    به‌صرفه (نوبت استاندارد)
                                </div>
                            </div>
                        </div>

                        {{-- Left in RTL: Price --}}
                        <div class="text-left shrink-0">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white font-sans">
                                {{ number_format($baseFee) }} <span class="text-[10px] font-bold text-slate-500">تومان</span>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: نصاب‌باشی پلاس (اعزام فوری) --}}
                    <div wire:click="setPriority('emergency')"
                         class="w-full p-3 rounded-2xl flex items-center justify-between cursor-pointer transition-all duration-150 {{ $priority === 'emergency' ? 'bg-[#f0fdf4] dark:bg-emerald-950/25 border border-emerald-500/30 shadow-xs' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent' }}">
                        
                        {{-- Right in RTL: Icon + Title + Subtitle --}}
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-700/40 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                                ⚡
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>نصاب‌باشی پلاس</span>
                                    @if($priority === 'emergency')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @endif
                                </div>
                                <div class="text-[10px] sm:text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                                    ویژه (رسیدن زیر ۳۰ دقیقه)
                                </div>
                            </div>
                        </div>

                        {{-- Left in RTL: Price --}}
                        <div class="text-left shrink-0">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white font-sans">
                                {{ number_format($baseFee + 100000) }} <span class="text-[10px] font-bold text-slate-500">تومان</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                {{-- Options & Promo Code Row (Exact Snapp Toolbar) --}}
                <div class="flex items-center justify-around py-0.5">
                    {{-- Right in RTL: گزینه‌های سفر / خدمت --}}
                    <button type="button" wire:click="setSubTab('options')"
                            class="flex-1 flex items-center justify-center gap-2 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span>گزینه‌های خدمت</span>
                        @if(!empty($building_details) || !empty($appliance_brand_model) || (!empty($problem_description) && $problem_description !== 'درخواست بررسی و کارشناسی فنی در محل'))
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>

                    {{-- Vertical separator --}}
                    <div class="w-px h-5 bg-slate-200 dark:bg-slate-700"></div>

                    {{-- Left in RTL: کد تخفیف --}}
                    <button type="button" wire:click="setSubTab('discount')"
                            class="flex-1 flex items-center justify-center gap-2 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span>کد تخفیف</span>
                        @if(!empty($referral_code))
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>
                </div>

                {{-- Full-Width Primary Green Button --}}
                <div class="pt-0.5">
                    <button type="button" wire:click="submitOrder"
                            class="w-full py-3.5 sm:py-4 bg-[#00a651] hover:bg-[#009247] active:scale-[0.99] text-white font-black text-sm sm:text-base rounded-2xl shadow-xl shadow-emerald-600/25 transition-all flex items-center justify-center">
                        <span>درخواست نصاب‌باشی</span>
                    </button>
                </div>
            </div>
            @endif

            {{-- ============================================================= --}}
            {{-- SUB-TAB 2: INLINE SERVICE OPTIONS (بدون مودال مجزا)            --}}
            {{-- ============================================================= --}}
            @if($activeSubTab === 'options')
            <div class="space-y-3">
                {{-- Header with Back Button --}}
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="setSubTab('main')"
                            class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1.5 transition">
                        <span>←</span>
                        <span>بازگشت به انتخاب خدمت</span>
                    </button>
                    <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>⚙️</span>
                        <span>گزینه‌های تکمیلی خدمت</span>
                    </div>
                </div>

                {{-- Form fields inside bottom sheet --}}
                <div class="space-y-2.5 max-h-64 overflow-y-auto custom-scrollbar p-0.5">
                    {{-- Appliance Brand/Model --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">برند و مدل دستگاه (اختیاری)</label>
                        <input type="text" wire:model="appliance_brand_model" placeholder="مثال: پکیج بوتان بیتا / اسپلیت ال‌جی"
                               class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    </div>

                    {{-- Problem Description & Quick Chips --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شرح عیب یا مشکل دستگاه</label>
                        <textarea wire:model="problem_description" rows="2" placeholder="توضیح کوتاه درباره مشکل یا انتخاب از چیپ‌های زیر..."
                                  class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans"></textarea>
                        
                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                            @foreach(['روشن نشدن', 'افت فشار', 'آبریزش', 'سردی آب', 'صدای پمپ', 'ارور برد', 'سرویس دوره‌ای'] as $symptom)
                            <button type="button" wire:click="addProblemTag('{{ $symptom }}')"
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 dark:bg-slate-800 dark:hover:bg-indigo-900/40 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60 transition active:scale-95 font-sans">
                                + {{ $symptom }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Building details --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">پلاک / واحد / طبقه</label>
                        <input type="text" wire:model="building_details" placeholder="مثال: پلاک ۴، طبقه ۲، واحد ۵"
                               class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    </div>

                    {{-- Order for other recipient --}}
                    @if($order_for === 'other')
                    <div class="p-3 rounded-2xl bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/20 space-y-2">
                        <div class="text-xs font-black text-amber-900 dark:text-amber-300">👥 مشخصات تحویل‌گیرنده در محل:</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <input type="text" wire:model="recipient_name" placeholder="نام تحویل‌گیرنده *"
                                       class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                                @error('recipient_name') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <input type="tel" wire:model="recipient_phone" placeholder="شماره همراه *" dir="ltr"
                                       class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                                @error('recipient_phone') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="pt-1">
                    <button type="button" wire:click="setSubTab('main')"
                            class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5">
                        <span>تأیید و بازگشت به سفارش</span>
                        <span>✓</span>
                    </button>
                </div>
            </div>
            @endif

            {{-- ============================================================= --}}
            {{-- SUB-TAB 3: INLINE PROMO CODE (بدون مودال مجزا)               --}}
            {{-- ============================================================= --}}
            @if($activeSubTab === 'discount')
            <div class="space-y-3">
                {{-- Header with Back Button --}}
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="setSubTab('main')"
                            class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1.5 transition">
                        <span>←</span>
                        <span>بازگشت به انتخاب خدمت</span>
                    </button>
                    <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>کد تخفیف و معرف</span>
                    </div>
                </div>

                <div class="space-y-2 py-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">کد معرف فروشگاه همکار یا تخفیف ویژه</label>
                    <input type="text" wire:model.live.debounce.400ms="referral_code" placeholder="مثال: NB-ABCDE" dir="ltr"
                           class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 font-sans uppercase">
                    @if($referralStatusMessage)
                    <div class="text-[11px] mt-1 font-bold {{ str_contains($referralStatusMessage, 'تایید') ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                        {{ $referralStatusMessage }}
                    </div>
                    @endif
                </div>

                <div class="pt-1">
                    <button type="button" wire:click="setSubTab('main')"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5">
                        <span>ثبت و بازگشت به سفارش</span>
                        <span>✓</span>
                    </button>
                </div>
            </div>
            @endif

            {{-- ============================================================= --}}
            {{-- SUB-TAB 4: ALL SERVICES & APPLIANCES CATALOG (خدمات کامل)      --}}
            {{-- ============================================================= --}}
            @if($activeSubTab === 'all_services')
            <div class="space-y-3 font-sans">
                {{-- Header with Back Button --}}
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="setSubTab('main')"
                            class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1.5 transition">
                        <span>←</span>
                        <span>بازگشت به سفارش</span>
                    </button>
                    <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span class="text-indigo-600 dark:text-indigo-400">⊞</span>
                        <span>فهرست کامل خدمات و دستگاه‌ها</span>
                    </div>
                </div>

                {{-- Fast Search Input --}}
                <div class="relative">
                    <input type="text" wire:model.live.debounce.200ms="categorySearch" placeholder="جستجو در خدمات (پکیج، اسپلیت، یخچال، پمپ، آسانسور...)"
                           class="w-full h-10 pr-9 pl-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                    <span class="absolute right-3 top-2.5 text-slate-400 text-sm">🔍</span>
                </div>

                {{-- Full 2-Column Grid of All Categories --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto custom-scrollbar p-0.5">
                    @forelse($filteredCategories as $cat)
                    <button type="button" wire:click="selectCategoryAndReturn({{ $cat->id }})"
                            class="p-2.5 sm:p-3 rounded-2xl flex items-center justify-between text-right transition-all cursor-pointer relative {{ $selectedCategoryId === $cat->id ? 'bg-indigo-50/90 dark:bg-indigo-500/20 border-2 border-indigo-600 dark:border-indigo-500 shadow-sm shadow-indigo-600/10' : 'bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-750 border border-slate-200/70 dark:border-slate-700/60' }}">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $selectedCategoryId === $cat->id ? 'bg-indigo-100 dark:bg-indigo-500/30 text-indigo-700 dark:text-indigo-200' : 'bg-white dark:bg-slate-700/80 shadow-xs' }} flex items-center justify-center text-xl shrink-0">
                                {{ $cat->icon ?: '🛠️' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs sm:text-[13px] font-black text-slate-800 dark:text-slate-100 truncate">
                                    {{ $cat->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold truncate mt-0.5 font-sans">
                                    {{ $cat->description ?: ($cat->base_fee ? 'شروع از ' . number_format($cat->base_fee) . ' ت' : 'اعزام فوری و استاندارد') }}
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-1.5 mr-2">
                            @if($selectedCategoryId === $cat->id)
                            <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                <span>انتخاب شده</span>
                                <span>✓</span>
                            </span>
                            @else
                            <span class="text-[11px] text-slate-400 font-bold hover:text-indigo-600">انتخاب ←</span>
                            @endif
                        </div>
                    </button>
                    @empty
                    <div class="col-span-full py-6 text-center text-xs text-slate-400 font-bold">
                        خدمتی با عبارت «{{ $categorySearch }}» یافت نشد.
                    </div>
                    @endforelse
                </div>

                {{-- Bottom Return Button --}}
                <div class="pt-0.5">
                    <button type="button" wire:click="setSubTab('main')"
                            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl transition">
                        بازگشت به سفارش
                    </button>
                </div>
            </div>
            @endif
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
