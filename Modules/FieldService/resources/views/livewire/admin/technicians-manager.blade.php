<div class="space-y-4 font-sans">
    {{-- Header & Filters --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">ناوگان متخصصین و نصابان استان مازندران</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">احراز هویت، تایید صلاحیت فنی، سطح‌بندی درآمدی و مانیتورینگ امتیازات</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="openCreateModal" class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 font-sans">
                <span>➕</span>
                <span>ثبت تکنسین جدید</span>
            </button>

            <div class="relative w-56">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی نام، تلفن یا شهر..." class="w-full h-9 pr-8 pl-3 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 font-sans">
                <span class="absolute right-2.5 top-2.5 text-gray-400 text-xs">🔍</span>
            </div>

            <select wire:model.live="statusFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه وضعیت‌ها</option>
                <option value="active">فعال و تایید شده</option>
                <option value="pending_approval">در انتظار تایید مدارک</option>
                <option value="suspended">تعلیق شده</option>
            </select>

            <select wire:model.live="tierFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه سطوح</option>
                <option value="silver">نقره‌ای</option>
                <option value="gold">طلایی (VIP)</option>
                <option value="master">استادکار (Master)</option>
            </select>
        </div>
    </div>

    {{-- Technicians Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($technicians as $tech)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 p-5 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-all">
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-black">
                            🔧
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $tech->full_name }}</h3>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">{{ $tech->mobile }}</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tech->status->badgeClass() }}">
                        {{ $tech->status->label() }}
                    </span>
                </div>

                {{-- Skills & City --}}
                <div class="mt-3.5 space-y-2">
                    <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                        <span>📍</span>
                        <span class="font-bold">{{ $tech->province }}</span>
                        <span class="text-gray-400">/</span>
                        <span>{{ $tech->city }}</span>
                        <span class="text-gray-400">-</span>
                        <span class="text-gray-500 dark:text-gray-400 text-[11px] truncate max-w-[170px]">{{ $tech->address ?: 'مازندران' }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded text-[10px] {{ $tech->tier->badgeClass() }}">
                            {{ $tech->tier->label() }}
                        </span>
                        @foreach($tech->categories as $cat)
                        <span class="px-2 py-0.5 rounded text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ $cat->name }}
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Stats Bar --}}
                <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 text-center">
                    <div>
                        <div class="text-[10px] text-gray-400">امتیاز</div>
                        <div class="font-black text-xs text-amber-500">★ {{ $tech->rating_avg }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400">سرویس موفق</div>
                        <div class="font-black text-xs text-gray-800 dark:text-gray-200">{{ $tech->completed_orders_count }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400">وضعیت فعلی</div>
                        <div class="font-bold text-[11px] {{ $tech->is_online ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                            {{ $tech->is_online ? ($tech->is_busy ? 'در کار' : 'آنلاین') : 'آفلاین' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 flex items-center justify-between gap-2">
                <button type="button" wire:click="toggleVerify({{ $tech->id }})" class="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold transition {{ $tech->status->value === 'active' ? 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-500/25' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-500/25' }}">
                    {{ $tech->status->value === 'active' ? 'تعلیق تکنسین' : 'تایید صلاحیت' }}
                </button>

                <div class="flex items-center gap-1">
                    <button type="button" wire:click="updateTier({{ $tech->id }}, 'gold')" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/25" title="ارتقا به طلایی">
                        VIP
                    </button>
                    <button type="button" wire:click="updateTier({{ $tech->id }}, 'master')" class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25" title="ارتقا به استادکار">
                        Master
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
            تکنسینی در این بخش یافت نشد. می‌توانید با دکمه «ثبت تکنسین جدید» اولین متخصص را اضافه کنید.
        </div>
        @endforelse
    </div>

    @if($technicians->hasPages())
    <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
        {{ $technicians->links() }}
    </div>
    @endif

    {{-- Create Technician Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-2xl w-full p-6 space-y-5 my-8 max-h-[90vh] overflow-y-auto font-sans" @click.outside="closeCreateModal">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        🔧
                    </div>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">ثبت تکنسین جدید در ناوگان نصاب‌باشی</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">افزودن مستقیم متخصص فنی به پایگاه داده و تخصیص محدوده جغرافیایی</p>
                    </div>
                </div>
                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1">
                    ✕
                </button>
            </div>

            <form wire:submit.prevent="storeTechnician" class="space-y-4">
                {{-- Row 1: First & Last Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام *</label>
                        <input type="text" wire:model="firstName" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="مثال: علیرضا">
                        @error('firstName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی *</label>
                        <input type="text" wire:model="lastName" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="مثال: حسینی">
                        @error('lastName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 2: Mobile & National Code --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره موبایل *</label>
                        <input type="text" wire:model="mobile" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="09112110000">
                        @error('mobile') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کد ملی</label>
                        <input type="text" wire:model="nationalCode" dir="ltr" maxlength="10" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="۲۱۵۰۳۴۲۱۱۲">
                        @error('nationalCode') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 3: Province & City (Strict Core ProvinceCity Integration) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">استان فعالیت *</label>
                        <select wire:model.live="province" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            @foreach($provinces as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                        @error('province') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شهر فعالیت *</label>
                        <select wire:model="city" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            @foreach($cities as $ct)
                                <option value="{{ $ct }}">{{ $ct }}</option>
                            @endforeach
                        </select>
                        @error('city') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 4: Shaba & Radius --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره شبا (تسویه پایا)</label>
                        <input type="text" wire:model="shabaNumber" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="IR120120000000001234567890">
                        @error('shabaNumber') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شعاع پوشش عملیاتی (کیلومتر) *</label>
                        <input type="number" wire:model="operationalRadiusKm" min="1" max="200" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="25">
                        @error('operationalRadiusKm') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 5: Tier & Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">سطح ارزیابی تکنسین *</label>
                        <select wire:model="tier" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="silver">تکنسین نقره‌ای (استاندارد)</option>
                            <option value="gold">تکنسین طلایی (VIP)</option>
                            <option value="master">استادکار ارشد (Master)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">وضعیت احراز و صلاحیت *</label>
                        <select wire:model="status" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="active">فعال و تایید شده (آماده اعزام)</option>
                            <option value="pending_approval">در انتظار تایید مدارک</option>
                            <option value="suspended">تعلیق شده</option>
                        </select>
                    </div>
                </div>

                {{-- Row 6: Categories / Specialties Checkboxes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">تخصص‌ها و حوزه‌های کاری مجاز</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700">
                        @foreach($allCategories as $cat)
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-700 dark:text-gray-300 hover:text-indigo-600 select-none">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $cat->id }}" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                            <span>{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Row 7: Address --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس کارگاه یا محل استقرار</label>
                    <textarea wire:model="address" rows="2" class="w-full p-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="مثال: بابل، میدان کشوری، ابتدای خیابان سرگرد قاسمی، مجتمع فنی"></textarea>
                </div>

                {{-- Actions --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeCreateModal" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-sans">
                        انصراف
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 text-xs font-bold transition flex items-center gap-2 shadow-sm shadow-indigo-600/30 font-sans">
                        <span wire:loading.remove wire:target="storeTechnician">ثبت و تایید تکنسین</span>
                        <span wire:loading wire:target="storeTechnician">در حال ثبت...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
