<div class="space-y-6 font-sans">
    {{-- Header Banner & Stats --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        🏪
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 dark:text-white">فروشگاه‌های همکار و فعال‌سازی گارانتی</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">شبکه شرکای B2B، فروشندگان لوازم خانگی و تاسیسات جهت ثبت نصب خریدار و پورسانت معرف</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-200 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>ثبت فروشگاه همکار جدید</span>
                </button>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">کل شرکای فروشگاهی</div>
                    <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ number_format($totalVendors) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    🏬
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">همکاران فعال تجاری</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($activeVendors) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✅
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">سفارشات ارجاعی نصب و گارانتی</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalReferredOrders) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    📦
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-80 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو بر اساس نام فروشگاه، مالک، موبایل، کد معرف..." class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">وضعیت:</span>
            <select wire:model.live="statusFilter" class="h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
                <option value="suspended">معلق</option>
            </select>
        </div>
    </div>

    {{-- Vendors Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($vendors as $v)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shrink-0">
                            🏪
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white leading-snug">{{ $v->store_name }}</h3>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">مدیر: {{ $v->owner_name }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 dir-ltr text-right">{{ $v->mobile }}</div>
                        </div>
                    </div>

                    {{-- Status Badge / Toggle --}}
                    <button type="button" wire:click="toggleStatus({{ $v->id }})" title="برای تغییر وضعیت کلیک کنید" class="shrink-0 px-2.5 py-1 rounded-xl text-xs font-bold transition {{ $v->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30' : 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-200/60 dark:border-rose-500/30' }}">
                        {{ $v->status === 'active' ? 'فعال' : 'غیرفعال' }}
                    </button>
                </div>

                {{-- Client Connection Badge --}}
                @if($v->client)
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200/60 dark:border-slate-700/40 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">مشتری CRM:</span>
                        <a href="{{ route('user.clients.show', $v->client_id) }}" target="_blank" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $v->client->full_name }}
                        </a>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 font-semibold">
                        شناسه #{{ $v->client_id }}
                    </span>
                </div>
                @else
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-[11px] text-amber-700 dark:text-amber-300">
                    ⚠️ فاقد لینک به مشتری CRM
                </div>
                @endif

                {{-- Referral Code Box --}}
                <div class="p-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">کد معرف فروشگاهی اختصاصی:</div>
                        <div class="text-sm font-black text-indigo-700 dark:text-indigo-300 mt-0.5 tracking-wider font-sans">{{ $v->referral_code ?? '---' }}</div>
                    </div>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $v->referral_code }}'); alert('کد معرف {{ $v->referral_code }} کپی شد');" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700 text-xs font-bold hover:bg-indigo-50 dark:hover:bg-slate-700 shadow-sm transition">
                        کپی کد
                    </button>
                </div>

                {{-- Metrics Grid --}}
                <div class="grid grid-cols-3 gap-2 text-center pt-1">
                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-150 dark:border-slate-700/50">
                        <div class="text-[10px] text-slate-400 dark:text-slate-500">پورسانت معرف</div>
                        <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $v->referral_commission_percent }}٪</div>
                    </div>

                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-150 dark:border-slate-700/50">
                        <div class="text-[10px] text-slate-400 dark:text-slate-500">سفارشات ارجاعی</div>
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">{{ $v->orders_count ?? $v->orders->count() }} مورد</div>
                    </div>

                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-150 dark:border-slate-700/50">
                        <div class="text-[10px] text-slate-400 dark:text-slate-500">کیف پول همکار</div>
                        <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($v->wallet_balance) }} ت</div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="text-xs text-slate-600 dark:text-slate-300 flex items-start gap-1.5 pt-1">
                    <span class="shrink-0 text-slate-400">📍</span>
                    <span class="line-clamp-2">{{ $v->province }}، {{ $v->city }} - {{ $v->address }}</span>
                </div>
            </div>

            {{-- Card Actions --}}
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-2">
                <button type="button" wire:click="editVendor({{ $v->id }})" class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>ویرایش</span>
                </button>

                <button type="button" wire:click="deleteVendor({{ $v->id }})" wire:confirm="آیا از حذف این فروشگاه همکار اطمینان دارید؟" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition" title="حذف فروشگاه">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 space-y-3">
            <div class="text-4xl">🏬</div>
            <div class="text-base font-bold text-slate-800 dark:text-slate-200">فروشگاه همکاری یافت نشد</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">می‌توانید با ثبت اولین فروشگاه همکار، سامانه فعال‌سازی گارانتی و پورسانت معرف را آغاز کنید.</p>
            <button type="button" wire:click="openCreateModal" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                + ثبت اولین فروشگاه همکار
            </button>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($vendors->hasPages())
    <div class="pt-4">
        {{ $vendors->links() }}
    </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl border border-slate-200 dark:border-slate-700 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        🏪
                    </div>
                    <h3 class="font-black text-base text-slate-900 dark:text-white">
                        {{ $editingVendorId ? 'ویرایش فروشگاه همکار' : 'ثبت فروشگاه همکار جدید' }}
                    </h3>
                </div>
                <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">✕</button>
            </div>

            <div class="space-y-4 max-h-[75vh] overflow-y-auto pr-1">
                {{-- Client CRM Association --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-750 border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>👤</span>
                            <span>اتصال به مشتری در ماژول کلاینت (CRM Client)</span>
                        </label>
                        <button type="button" wire:click="$toggle('isCreatingNewClient')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $isCreatingNewClient ? 'یا انتخاب مشتری موجود' : '+ ثبت سریع مشتری جدید' }}
                        </button>
                    </div>

                    @if($isCreatingNewClient)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">نام و نام خانوادگی مشتری</label>
                            <input type="text" wire:model="newClientName" placeholder="مثال: محمد حسینی" class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">شماره همراه مشتری</label>
                            <input type="text" wire:model="newClientPhone" placeholder="0911xxxxxxx" dir="ltr" class="w-full h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans">
                        </div>
                    </div>
                    @else
                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model.live.debounce.300ms="clientSearch" placeholder="نام یا شماره موبایل مشتری CRM را جستجو کنید..." class="flex-1 h-9 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans">
                            @if($selectedClientId)
                            <button type="button" wire:click="clearClientSelection" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-xs font-bold rounded-xl">حذف انتخاب</button>
                            @endif
                        </div>

                        {{-- Suggestions Dropdown --}}
                        @if($clientSuggestions->isNotEmpty())
                        <div class="absolute z-20 top-11 right-0 left-0 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-600">
                            @foreach($clientSuggestions as $suggestion)
                            <button type="button" wire:click="selectClient({{ $suggestion->id }})" class="w-full p-2.5 text-right hover:bg-slate-50 dark:hover:bg-slate-600/50 flex items-center justify-between text-xs transition">
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $suggestion->full_name }}</span>
                                <span class="text-slate-400 dir-ltr">{{ $suggestion->phone ?? 'بدون شماره' }}</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Vendor Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نام فروشگاه / نمایندگی *</label>
                        <input type="text" wire:model="store_name" placeholder="فروشگاه لوازم خانگی و تاسیسات طبرستان" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('store_name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نام مدیر / مالک فروشگاه *</label>
                        <input type="text" wire:model="owner_name" placeholder="نام صاحب پروانه یا مدیر" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('owner_name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شماره همراه *</label>
                        <input type="text" wire:model="mobile" placeholder="0911xxxxxxx" dir="ltr" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('mobile') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">تلفن ثابت فروشگاه</label>
                        <input type="text" wire:model="phone" placeholder="01132xxxxxx" dir="ltr" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شماره پروانه کسب / جواز</label>
                        <input type="text" wire:model="license_number" placeholder="اختیاری" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                {{-- Province & City (Standard CRM) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">استان *</label>
                        <select wire:model.live="province" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($provinces as $prov)
                            <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">شهر *</label>
                        <select wire:model="city" class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($cities as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">آدرس فروشگاه</label>
                    <textarea wire:model="address" rows="2" placeholder="بابل، خیابان شریعتی، روبروی..." class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Financial & Status Settings --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">درصد پورسانت معرف (٪) *</label>
                        <input type="number" step="0.5" wire:model="referral_commission_percent" class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                        <span class="text-[11px] text-slate-400 mt-0.5 block">درصد سهم فروشگاه از هر فاکتور نصب مشتری</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">وضعیت همکاری *</label>
                        <select wire:model="status" class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                            <option value="active">فعال (امکان ثبت سفارش و محاسبه پورسانت)</option>
                            <option value="inactive">غیرفعال</option>
                            <option value="suspended">معلق</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">یادداشت داخلی</label>
                    <input type="text" wire:model="notes" placeholder="توضیحات و شرایط اختصاصی توافق با این همکار..." class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-sans text-slate-800 dark:text-slate-100">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                <button type="button" wire:click="$set('showModal', false)" class="w-1/3 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    انصراف
                </button>
                <button type="button" wire:click="saveVendor" class="w-2/3 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/20 transition active:scale-95">
                    {{ $editingVendorId ? 'ذخیره تغییرات فروشگاه' : 'ثبت نهایی فروشگاه همکار' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
