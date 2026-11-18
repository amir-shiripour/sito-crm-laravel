<div class="space-y-5 font-sans">
    {{-- Top Overview Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400">کل ناوگان متخصصین</div>
                <div class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($totalCount) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                👥
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400">فعال و تایید صلاحیت</div>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($activeCount) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                ✅
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400">آنلاین و آماده خدمت</div>
                <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1">{{ number_format($onlineCount) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                ⚡
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400">متخصصین VIP و Master</div>
                <div class="text-xl font-black text-amber-500 dark:text-amber-400 mt-1">{{ number_format($vipCount) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg">
                ⭐
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400">در انتظار امضای قرارداد</div>
                <div class="text-xl font-black {{ $pendingContractCount > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }} mt-1">
                    {{ number_format($pendingContractCount) }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-500 dark:text-rose-400 flex items-center justify-center text-lg">
                ✍️
            </div>
        </div>
    </div>

    {{-- Header & Filters --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-black text-gray-900 dark:text-white">ناوگان متخصصین و نصابان</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت سلسله‌مراتبی بخش‌ها، دسته‌ها و خدمات فنی، احراز هویت و قراردادهای حقوقی</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" wire:click="openCreateModal" class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 font-sans cursor-pointer">
                    <span>➕</span>
                    <span>ثبت تکنسین جدید</span>
                </button>
            </div>
        </div>

        {{-- Filters Row with Sections & Groups --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="نام، موبایل، کد ملی یا شهر..." class="w-full h-10 pr-9 pl-3 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 font-sans">
                <span class="absolute right-3 top-3 text-gray-400 text-xs">🔍</span>
            </div>

            {{-- Section Filter --}}
            <select wire:model.live="sectionFilter" class="h-10 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه بخش‌ها (تعمیر، نصب و...)</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}">بخش: {{ $sec->name }}</option>
                @endforeach
            </select>

            {{-- Group Filter --}}
            <select wire:model.live="groupFilter" class="h-10 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه دسته‌ها (پکیج، کولر و...)</option>
                @foreach($filterGroups as $grp)
                    <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select wire:model.live="statusFilter" class="h-10 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه وضعیت‌ها</option>
                <option value="active">فعال و تایید شده</option>
                <option value="pending_approval">در انتظار بررسی مدارک</option>
                <option value="suspended">تعلیق شده</option>
            </select>

            {{-- Tier Filter --}}
            <select wire:model.live="tierFilter" class="h-10 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                <option value="all">همه سطوح</option>
                <option value="silver">نقره‌ای (استاندارد)</option>
                <option value="gold">طلایی (VIP)</option>
                <option value="master">استادکار (Master)</option>
            </select>
        </div>
    </div>

    {{-- Technicians Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($technicians as $tech)
        @php
            $hasPendingContract = $tech->hasPendingContract();
            $latestContract = $contractForgeActive ? $tech->contracts->sortByDesc('id')->first() : null;

            // Group skills hierarchically: Section -> Group -> Services
            $skillsBySection = $tech->categories->groupBy(function ($cat) {
                return $cat->group?->section?->name ?? $cat->section?->name ?? 'سایر بخش‌ها';
            });
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/70 dark:border-gray-700/60 p-5 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-all font-sans relative">
            <div>
                {{-- Top Card Header: Status Badges & Info --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold shadow-sm">
                                🔧
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800 {{ $tech->is_online ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}" title="{{ $tech->is_online ? 'آنلاین' : 'آفلاین' }}"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $tech->full_name }}</span>
                            </h3>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right font-sans">{{ $tech->mobile }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold {{ $tech->status->badgeClass() }}">
                            {{ $tech->status->label() }}
                        </span>
                        @if($hasPendingContract)
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 flex items-center gap-1">
                            ⚠️ منتظر امضا
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Location, Radius & National Code --}}
                <div class="mt-3.5 space-y-2 text-xs">
                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span>📍</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $tech->province }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $tech->city }}</span>
                        @if($tech->address)
                        <span class="text-gray-400">-</span>
                        <span class="text-gray-500 dark:text-gray-400 text-[11px] truncate max-w-[150px]">{{ $tech->address }}</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px]">
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40 font-bold flex items-center gap-1">
                            🎯 شعاع: {{ $tech->operational_radius_km ?? 25 }} کیلومتر
                        </span>

                        @if(!empty($tech->national_code))
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 font-bold">
                            کد ملی: {{ $tech->national_code }}
                        </span>
                        @endif
                    </div>

                    {{-- Hierarchical Section & Group Badges --}}
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700/40 space-y-2">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-gray-400 font-medium">سطح و صلاحیت فنی:</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tech->tier->badgeClass() }}">
                                {{ $tech->tier->label() }}
                            </span>
                        </div>

                        {{-- Structured Section -> Group -> Service Badges --}}
                        <div class="space-y-1.5">
                            @forelse($skillsBySection as $secTitle => $secCategories)
                            <div class="p-2 rounded-xl bg-gray-50/80 dark:bg-gray-750/60 border border-gray-200/60 dark:border-gray-700/50 text-[11px] space-y-1">
                                <div class="font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1">
                                    <span>🧩 بخش {{ $secTitle }}</span>
                                    <span class="text-gray-400 text-[10px]">({{ $secCategories->count() }} خدمت)</span>
                                </div>
                                <div class="flex flex-wrap gap-1 pt-0.5">
                                    @php
                                        $groupedServices = $secCategories->groupBy(fn($c) => $c->group?->name ?? 'سایر');
                                    @endphp
                                    @foreach($groupedServices as $grpTitle => $services)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-2xs font-medium">
                                            @if($grpTitle !== 'سایر')
                                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $grpTitle }}:</span>
                                            @endif
                                            <span>{{ $services->pluck('name')->implode('، ') }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @empty
                            <div class="text-[10px] text-gray-400 italic">هیچ بخش یا تخصصی ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Performance Stats Bar --}}
                <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 text-center">
                    <div>
                        <div class="text-[10px] text-gray-400 dark:text-gray-400">امتیاز رضایت</div>
                        <div class="font-black text-xs text-amber-500 mt-0.5">
                            @if($tech->rating_count > 0)
                                ★ {{ number_format($tech->rating_avg, 1) }}
                            @else
                                <span class="text-gray-400 dark:text-gray-400 font-normal">۰.۰ (بدون نظر)</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 dark:text-gray-400">ماموریت موفق</div>
                        <div class="font-black text-xs text-gray-800 dark:text-gray-200 mt-0.5">{{ number_format($tech->completed_orders_count) }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 dark:text-gray-400">وضعیت فعلی</div>
                        <div class="font-bold text-[11px] mt-0.5 {{ $tech->is_online ? ($tech->is_busy ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400') : 'text-gray-400 dark:text-gray-400' }}">
                            {{ $tech->is_online ? ($tech->is_busy ? 'در حال ماموریت' : 'آنلاین') : 'آفلاین' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ContractForge Status Section --}}
            @if($contractForgeActive)
            <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/50 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">قرارداد همکاری:</span>
                    @if($latestContract)
                        <span class="px-2 py-0.5 rounded-lg font-bold {{ $latestContract->status === 'signed' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : ($latestContract->status === 'active' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300') }}">
                            {{ $latestContract->status_label }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-lg font-bold bg-slate-100 dark:bg-slate-700/70 text-slate-500 dark:text-slate-400">
                            بدون قرارداد
                        </span>
                    @endif
                </div>

                @if($latestContract)
                <div class="flex items-center gap-1">
                    <a href="{{ route('user.contracts.show', $latestContract->id) }}"
                       class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-[10px] font-bold transition flex items-center gap-1" title="مشاهده قرارداد">
                        <span>مشاهده</span>
                    </a>
                    <a href="{{ route('user.contracts.pdf', $latestContract->id) }}" target="_blank"
                       class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-[10px] font-bold transition" title="دانلود نسخه PDF">
                        PDF
                    </a>
                    <a href="{{ route('user.contracts.print', $latestContract->id) }}" target="_blank"
                       class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-[10px] font-bold transition" title="چاپ">
                        چاپ
                    </a>
                </div>
                @else
                <button type="button" wire:click="openContractModal({{ $tech->id }})"
                        class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-[10px] font-bold transition cursor-pointer">
                    صدور قرارداد
                </button>
                @endif
            </div>
            @endif

            {{-- Action Buttons Row --}}
            <div class="pt-2 flex flex-wrap items-center justify-between gap-1.5 border-t border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-1.5 flex-1">
                    <button type="button" wire:click="openEditModal({{ $tech->id }})"
                            class="py-1.5 px-3 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 transition flex items-center gap-1 cursor-pointer" title="ویرایش اطلاعات">
                        <span>✏️</span>
                        <span>ویرایش</span>
                    </button>

                    <button type="button" wire:click="openDossierModal({{ $tech->id }})"
                            class="py-1.5 px-3 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition flex items-center gap-1 cursor-pointer" title="مشاهده پرونده و مدارک">
                        <span>📁</span>
                        <span>پرونده</span>
                    </button>

                    <button type="button" wire:click="toggleVerify({{ $tech->id }})"
                            class="py-1.5 px-3 rounded-xl text-xs font-bold transition cursor-pointer {{ $tech->status->value === 'active' ? 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-500/25' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-500/25' }}">
                        {{ $tech->status->value === 'active' ? 'تعلیق' : 'تایید' }}
                    </button>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" wire:click="updateTier({{ $tech->id }}, 'gold')"
                            class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/25 cursor-pointer" title="ارتقا به VIP (طلایی)">
                        VIP
                    </button>
                    <button type="button" wire:click="updateTier({{ $tech->id }}, 'master')"
                            class="px-2 py-1.5 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 cursor-pointer" title="ارتقا به استادکار (Master)">
                        Master
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
            <div class="text-4xl mb-3">🔧</div>
            <div class="text-sm font-bold text-gray-700 dark:text-gray-300">هیچ تکنسینی با معیارهای جستجو یافت نشد.</div>
            <div class="text-xs text-gray-400 dark:text-gray-400 mt-1">با تغییر فیلتر بخش و دسته یا ثبت تکنسین جدید، اطلاعات را بررسی فرمایید.</div>
        </div>
        @endforelse
    </div>

    @if($technicians->hasPages())
    <div class="p-4 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/70 dark:border-gray-700/60 font-sans">
        {{ $technicians->links() }}
    </div>
    @endif

    {{-- Create Technician Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-4xl w-full p-6 space-y-5 my-8 max-h-[92vh] overflow-y-auto font-sans" @click.outside="closeCreateModal">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        ➕
                    </div>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">ثبت تکنسین جدید در ناوگان</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ثبت مشخصات هویتی، تخصیص بخش‌ها، دسته‌ها و خدمات فنی، شعاع عملیاتی و قرارداد حقوقی</p>
                    </div>
                </div>
                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1 cursor-pointer">
                    ✕
                </button>
            </div>

            <form wire:submit.prevent="storeTechnician" class="space-y-5">
                {{-- Section 1: Basic Identity --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>👤</span>
                        <span>۱. اطلاعات فردی و هویتی</span>
                    </div>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره موبایل (ورود به سامانه) *</label>
                            <input type="text" wire:model="mobile" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="09112110000">
                            @error('mobile') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کد ملی (۱۰ رقم)</label>
                            <input type="text" wire:model="nationalCode" dir="ltr" maxlength="10" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="۲۱۵۰۳۴۲۱۱۲">
                            @error('nationalCode') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 2: Coverage & Location --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>📍</span>
                        <span>۲. موقعیت مکانی و محدوده عملیاتی</span>
                    </div>

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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                شعاع پوشش عملیاتی (کیلومتر) *
                                <span class="text-indigo-600 dark:text-indigo-400 font-normal mr-1">(پیش‌فرض سامانه: {{ $defaultRadius }} کیلومتر)</span>
                            </label>
                            <input type="number" wire:model="operationalRadiusKm" min="1" max="200" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="25">
                            @error('operationalRadiusKm') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره شبا (تسویه پایا)</label>
                            <input type="text" wire:model="shabaNumber" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right" placeholder="IR120120000000001234567890">
                            @error('shabaNumber') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس کارگاه یا محل استقرار</label>
                        <textarea wire:model="address" rows="2" class="w-full p-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans" placeholder="مثال: بابل، میدان کشوری، ابتدای خیابان سرگرد قاسمی، مجتمع فنی"></textarea>
                    </div>
                </div>

                {{-- Section 3: Tier & Status --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>⭐</span>
                        <span>۳. سطح ارزیابی و وضعیت صلاحیت</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">سطح درآمدی و ارزیابی *</label>
                            <select wire:model="tier" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                <option value="silver">تکنسین نقره‌ای (استاندارد)</option>
                                <option value="gold">تکنسین طلایی (VIP)</option>
                                <option value="master">استادکار ارشد (Master)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">وضعیت احراز هویت *</label>
                            <select wire:model="status" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                <option value="active">فعال و تایید شده (آماده اعزام)</option>
                                <option value="pending_approval">در انتظار تایید مدارک</option>
                                <option value="suspended">تعلیق شده</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Hierarchical Specialties (Section -> Group -> Services) --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                            <span>🛠️</span>
                            <span>۴. تخصص‌ها و حوزه‌های کاری مجاز (بخش‌ها، دسته‌ها و خدمات)</span>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-0.5 rounded-lg border border-indigo-200 dark:border-indigo-800/40">
                            {{ count($selectedCategories) }} خدمت انتخاب شده
                        </span>
                    </div>

                    {{-- Section Tabs --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($sections as $sec)
                        @php
                            $allSecServiceIds = $sec->services->pluck('id')
                                ->merge($sec->groups->flatMap(fn($g) => $g->services->pluck('id')))
                                ->map(fn($v) => (int)$v)
                                ->toArray();
                            $secSelectedCount = count(array_intersect(array_map('intval', $selectedCategories), $allSecServiceIds));
                            $isActiveTab = $createActiveSectionId === $sec->id;
                        @endphp
                        <button type="button" wire:click="setCreateActiveSection({{ $sec->id }})"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $isActiveTab ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            <span>{{ $sec->icon ?: '🧩' }}</span>
                            <span>بخش {{ $sec->name }}</span>
                            @if($secSelectedCount > 0)
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300' }}">
                                    {{ $secSelectedCount }}
                                </span>
                            @endif
                        </button>
                        @endforeach
                    </div>

                    {{-- Active Section's Groups and Services --}}
                    @php
                        $activeSec = $sections->firstWhere('id', $createActiveSectionId) ?? $sections->first();
                    @endphp

                    @if($activeSec)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <span>دسته‌ها و خدمات مجاز در «بخش {{ $activeSec->name }}»</span>
                            </div>
                            <button type="button" wire:click="toggleCreateSection({{ $activeSec->id }})"
                                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                انتخاب / لغو همه خدمات این بخش
                            </button>
                        </div>

                        {{-- Service Groups inside Section --}}
                        <div class="space-y-4">
                            @forelse($activeSec->groups as $grp)
                            <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                    <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <span>{{ $grp->icon ?: '📂' }}</span>
                                        <span>دسته: {{ $grp->name }}</span>
                                    </div>
                                    <button type="button" wire:click="toggleCreateGroup({{ $grp->id }})"
                                            class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 cursor-pointer">
                                        انتخاب / لغو همه خدمات دسته
                                    </button>
                                </div>

                                {{-- Services Grid inside Group --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @forelse($grp->services as $srv)
                                    <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer text-xs select-none border border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition">
                                        <input type="checkbox" wire:model="selectedCategories" value="{{ $srv->id }}" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $srv->name }}</div>
                                            @if($srv->base_labor_fee)
                                            <div class="text-[10px] text-gray-400 mt-0.5">اجرت پایه: {{ number_format($srv->base_labor_fee) }} ریال</div>
                                            @endif
                                        </div>
                                    </label>
                                    @empty
                                    <div class="col-span-full text-xs text-gray-400 italic">هیچ خدمتی در این دسته تعریف نشده است.</div>
                                    @endforelse
                                </div>
                            </div>
                            @empty
                            @endforelse

                            {{-- Ungrouped Services directly under Section --}}
                            @if($activeSec->services->isNotEmpty())
                            <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5 pb-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                    <span>🧩</span>
                                    <span>سایر خدمات مستقیم بخش {{ $activeSec->name }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($activeSec->services as $srv)
                                    <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer text-xs select-none">
                                        <input type="checkbox" wire:model="selectedCategories" value="{{ $srv->id }}" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $srv->name }}</div>
                                            @if($srv->base_labor_fee)
                                            <div class="text-[10px] text-gray-400 mt-0.5">اجرت پایه: {{ number_format($srv->base_labor_fee) }} ریال</div>
                                            @endif
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Section 5: Identity Document Upload --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>🪪</span>
                        <span>۵. مدرک شناسایی (تصویر کارت ملی)</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-dashed border-gray-300 dark:border-gray-600 text-center">
                        <input type="file" wire:model="nationalCardFile" accept="image/*,.pdf" id="nationalCardFileInput" class="hidden">
                        <label for="nationalCardFileInput" class="cursor-pointer flex flex-col items-center justify-center gap-2">
                            <span class="text-2xl">📸</span>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                @if($nationalCardFile)
                                    فایل انتخاب شد: {{ $nationalCardFile->getClientOriginalName() }}
                                @else
                                    بارگذاری تصویر کارت ملی یا مدرک فنی (اختیاری)
                                @endif
                            </span>
                            <span class="text-[10px] text-gray-400">فرمت‌های مجاز: JPG, PNG, WEBP, PDF (حداکثر ۵ مگابایت)</span>
                        </label>
                        @error('nationalCardFile') <span class="text-[11px] text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Section 6: ContractForge Integration --}}
                @if($contractForgeActive)
                <div class="space-y-3 p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-800/40">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-black text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                            <span>📜</span>
                            <span>۶. قرارداد همکاری تکنسین (ContractForge)</span>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-700 dark:text-indigo-300">
                            <input type="checkbox" wire:model.live="autoIssueContract" class="w-4 h-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                            <span>صدور همزمان قرارداد</span>
                        </label>
                    </div>

                    @if($autoIssueContract)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">انتخاب قالب قرارداد *</label>
                            <select wire:model="createTemplateId" class="w-full h-10 px-3 text-xs rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans">
                                @foreach($technicianTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">وضعیت قرارداد</label>
                            <select wire:model="createContractStatus" class="w-full h-10 px-3 text-xs rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans">
                                <option value="active">ارسال مستقیم برای تایید و امضای تکنسین</option>
                                <option value="draft">ذخیره به عنوان پیش‌نویس</option>
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Modal Actions --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeCreateModal" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-sans cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm shadow-indigo-600/30 font-sans cursor-pointer">
                        <span wire:loading.remove wire:target="storeTechnician">ثبت و ایجاد تکنسین</span>
                        <span wire:loading wire:target="storeTechnician">در حال پردازش...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Technician Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-4xl w-full p-6 space-y-5 my-8 max-h-[92vh] overflow-y-auto font-sans" @click.outside="closeEditModal">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                        ✏️
                    </div>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">ویرایش اطلاعات تکنسین #{{ $editTechnicianId }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">به‌روزرسانی مشخصات، سلسله‌مراتب بخش‌ها و خدمات، شعاع عملیاتی و وضعیت</p>
                    </div>
                </div>
                <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1 cursor-pointer">
                    ✕
                </button>
            </div>

            <form wire:submit.prevent="updateTechnician" class="space-y-5">
                {{-- Basic Identity --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>👤</span>
                        <span>۱. اطلاعات فردی</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام *</label>
                            <input type="text" wire:model="editFirstName" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans">
                            @error('editFirstName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نام خانوادگی *</label>
                            <input type="text" wire:model="editLastName" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans">
                            @error('editLastName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره موبایل *</label>
                            <input type="text" wire:model="editMobile" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right">
                            @error('editMobile') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کد ملی</label>
                            <input type="text" wire:model="editNationalCode" dir="ltr" maxlength="10" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right">
                            @error('editNationalCode') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Location & Operational Radius --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>📍</span>
                        <span>۲. موقعیت مکانی و شعاع فعالیت</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">استان فعالیت *</label>
                            <select wire:model.live="editProvince" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov }}">{{ $prov }}</option>
                                @endforeach
                            </select>
                            @error('editProvince') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شهر فعالیت *</label>
                            <select wire:model="editCity" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                @foreach($editCities as $ct)
                                    <option value="{{ $ct }}">{{ $ct }}</option>
                                @endforeach
                            </select>
                            @error('editCity') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شعاع پوشش عملیاتی (کیلومتر) *</label>
                            <input type="number" wire:model="editOperationalRadiusKm" min="1" max="200" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans">
                            @error('editOperationalRadiusKm') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">شماره شبا (IBAN)</label>
                            <input type="text" wire:model="editShabaNumber" dir="ltr" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans text-right">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس کارگاه یا محل استقرار</label>
                        <textarea wire:model="editAddress" rows="2" class="w-full p-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 font-sans"></textarea>
                    </div>
                </div>

                {{-- Tier & Status --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>⭐</span>
                        <span>۳. سطح ارزیابی و وضعیت صلاحیت</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">سطح تکنسین *</label>
                            <select wire:model="editTier" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                <option value="silver">تکنسین نقره‌ای (استاندارد)</option>
                                <option value="gold">تکنسین طلایی (VIP)</option>
                                <option value="master">استادکار ارشد (Master)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">وضعیت احراز و صلاحیت *</label>
                            <select wire:model="editStatus" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                                <option value="active">فعال و تایید شده</option>
                                <option value="pending_approval">در انتظار تایید مدارک</option>
                                <option value="suspended">تعلیق شده</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Hierarchical Specialties (Section -> Group -> Services) for Edit --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                            <span>🛠️</span>
                            <span>۴. تخصص‌ها و حوزه‌های کاری مجاز (بخش‌ها، دسته‌ها و خدمات)</span>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-0.5 rounded-lg border border-indigo-200 dark:border-indigo-800/40">
                            {{ count($editSelectedCategories) }} خدمت انتخاب شده
                        </span>
                    </div>

                    {{-- Edit Section Tabs --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($sections as $sec)
                        @php
                            $allSecServiceIds = $sec->services->pluck('id')
                                ->merge($sec->groups->flatMap(fn($g) => $g->services->pluck('id')))
                                ->map(fn($v) => (int)$v)
                                ->toArray();
                            $secSelectedCount = count(array_intersect(array_map('intval', $editSelectedCategories), $allSecServiceIds));
                            $isActiveTab = $editActiveSectionId === $sec->id;
                        @endphp
                        <button type="button" wire:click="setEditActiveSection({{ $sec->id }})"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $isActiveTab ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            <span>{{ $sec->icon ?: '🧩' }}</span>
                            <span>بخش {{ $sec->name }}</span>
                            @if($secSelectedCount > 0)
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300' }}">
                                    {{ $secSelectedCount }}
                                </span>
                            @endif
                        </button>
                        @endforeach
                    </div>

                    {{-- Edit Active Section's Groups and Services --}}
                    @php
                        $editActiveSec = $sections->firstWhere('id', $editActiveSectionId) ?? $sections->first();
                    @endphp

                    @if($editActiveSec)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <span>دسته‌ها و خدمات مجاز در «بخش {{ $editActiveSec->name }}»</span>
                            </div>
                            <button type="button" wire:click="toggleEditSection({{ $editActiveSec->id }})"
                                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                انتخاب / لغو همه خدمات این بخش
                            </button>
                        </div>

                        {{-- Service Groups inside Section --}}
                        <div class="space-y-4">
                            @forelse($editActiveSec->groups as $grp)
                            <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                    <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <span>{{ $grp->icon ?: '📂' }}</span>
                                        <span>دسته: {{ $grp->name }}</span>
                                    </div>
                                    <button type="button" wire:click="toggleEditGroup({{ $grp->id }})"
                                            class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 cursor-pointer">
                                        انتخاب / لغو همه خدمات دسته
                                    </button>
                                </div>

                                {{-- Services Grid inside Group --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @forelse($grp->services as $srv)
                                    <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer text-xs select-none border border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition">
                                        <input type="checkbox" wire:model="editSelectedCategories" value="{{ $srv->id }}" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $srv->name }}</div>
                                            @if($srv->base_labor_fee)
                                            <div class="text-[10px] text-gray-400 mt-0.5">اجرت پایه: {{ number_format($srv->base_labor_fee) }} ریال</div>
                                            @endif
                                        </div>
                                    </label>
                                    @empty
                                    <div class="col-span-full text-xs text-gray-400 italic">هیچ خدمتی در این دسته تعریف نشده است.</div>
                                    @endforelse
                                </div>
                            </div>
                            @empty
                            @endforelse

                            {{-- Ungrouped Services directly under Section --}}
                            @if($editActiveSec->services->isNotEmpty())
                            <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5 pb-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                    <span>🧩</span>
                                    <span>سایر خدمات مستقیم بخش {{ $editActiveSec->name }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($editActiveSec->services as $srv)
                                    <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer text-xs select-none">
                                        <input type="checkbox" wire:model="editSelectedCategories" value="{{ $srv->id }}" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $srv->name }}</div>
                                            @if($srv->base_labor_fee)
                                            <div class="text-[10px] text-gray-400 mt-0.5">اجرت پایه: {{ number_format($srv->base_labor_fee) }} ریال</div>
                                            @endif
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Identity Document (National Card) --}}
                <div class="space-y-3">
                    <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                        <span>🪪</span>
                        <span>۵. تصویر مدرک شناسایی / کارت ملی</span>
                    </div>

                    @if($existingNationalCardPath)
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700">
                        <span class="text-lg">📄</span>
                        <div class="flex-1">
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200">مدرک شناسایی بارگذاری شده است</div>
                            <div class="text-[10px] text-gray-400 mt-0.5 dir-ltr text-right truncate max-w-xs">{{ $existingNationalCardPath }}</div>
                        </div>
                        <a href="{{ asset('storage/' . $existingNationalCardPath) }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition">
                            مشاهده مدرک
                        </a>
                    </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-dashed border-gray-300 dark:border-gray-600 text-center">
                        <input type="file" wire:model="editNationalCardFile" accept="image/*,.pdf" id="editNationalCardFileInput" class="hidden">
                        <label for="editNationalCardFileInput" class="cursor-pointer flex flex-col items-center justify-center gap-1">
                            <span class="text-xl">📤</span>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                @if($editNationalCardFile)
                                    فایل جدید انتخاب شد: {{ $editNationalCardFile->getClientOriginalName() }}
                                @else
                                    {{ $existingNationalCardPath ? 'جایگزینی مدرک شناسایی جدید' : 'بارگذاری مدرک شناسایی' }}
                                @endif
                            </span>
                            <span class="text-[10px] text-gray-400">JPG, PNG, WEBP, PDF (حداکثر ۵ مگابایت)</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeEditModal" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-sans cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm shadow-indigo-600/30 font-sans cursor-pointer">
                        <span wire:loading.remove wire:target="updateTechnician">ذخیره تغییرات</span>
                        <span wire:loading wire:target="updateTechnician">در حال ذخیره...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Technician Dossier / Details Modal --}}
    @if($showDossierModal && $dossierTech)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-4xl w-full p-6 space-y-5 my-8 max-h-[92vh] overflow-y-auto font-sans" @click.outside="closeDossierModal">
            {{-- Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                        📁
                    </div>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">پرونده جامع متخصص: {{ $dossierTech->full_name }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">شناسه تکنسین: #{{ $dossierTech->id }} | وضعیت: {{ $dossierTech->status->label() }}</p>
                    </div>
                </div>
                <button type="button" wire:click="closeDossierModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1 cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="space-y-4">
                {{-- Quick Overview Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60">
                        <div class="text-[10px] text-gray-400">سطح تکنسین</div>
                        <div class="text-xs font-black text-gray-800 dark:text-gray-200 mt-1">{{ $dossierTech->tier->label() }}</div>
                    </div>
                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60">
                        <div class="text-[10px] text-gray-400">شعاع پوشش</div>
                        <div class="text-xs font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $dossierTech->operational_radius_km ?? 25 }} کیلومتر</div>
                    </div>
                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60">
                        <div class="text-[10px] text-gray-400">امتیاز رضایت</div>
                        <div class="text-xs font-black text-amber-500 mt-1">★ {{ number_format($dossierTech->rating_avg, 1) }} ({{ $dossierTech->rating_count }})</div>
                    </div>
                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60">
                        <div class="text-[10px] text-gray-400">سرویس موفق</div>
                        <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($dossierTech->completed_orders_count) }}</div>
                    </div>
                </div>

                {{-- Identity & Contact Info --}}
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                    <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span>👤</span>
                        <span>اطلاعات فردی و بانکی</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400">شماره موبایل:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr inline-block mr-1">{{ $dossierTech->mobile }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">کد ملی:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1">{{ $dossierTech->national_code ?: 'ثبت نشده' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">شماره شبا:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1 dir-ltr inline-block">{{ $dossierTech->shaba_number ?: 'ثبت نشده' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">استان / شهر:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1">{{ $dossierTech->province }} - {{ $dossierTech->city }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">مختصات جغرافیایی:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1 dir-ltr inline-block">
                                {{ $dossierTech->lat ? number_format($dossierTech->lat, 4) . ', ' . number_format($dossierTech->lng, 4) : 'ثبت نشده' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">تاریخ تایید صلاحیت:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1">
                                {{ $dossierTech->verified_at ? (function_exists('jdate') ? jdate($dossierTech->verified_at)->format('Y/m/d') : $dossierTech->verified_at->format('Y-m-d')) : 'در انتظار' }}
                            </span>
                        </div>
                        @if($dossierTech->address)
                        <div class="sm:col-span-3">
                            <span class="text-gray-400">آدرس:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1">{{ $dossierTech->address }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Hierarchical Specialties Breakdown (Section -> Group -> Services) --}}
                @php
                    $dossierSkillsBySection = $dossierTech->categories->groupBy(function ($cat) {
                        return $cat->group?->section?->name ?? $cat->section?->name ?? 'سایر بخش‌ها';
                    });
                @endphp
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span>🛠️</span>
                            <span>ساختار سلسله‌مراتبی تخصص‌ها (بخش‌ها، دسته‌ها و خدمات مجاز)</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40">
                            {{ $dossierTech->categories->count() }} خدمت تخصصی
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($dossierSkillsBySection as $secTitle => $secCategories)
                        <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2">
                            <div class="text-xs font-black text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700/60 pb-1.5">
                                <span>🧩</span>
                                <span>بخش {{ $secTitle }}</span>
                                <span class="text-gray-400 text-[10px] font-normal">({{ $secCategories->count() }} خدمت فعال)</span>
                            </div>

                            @php
                                $dossierGrouped = $secCategories->groupBy(fn($c) => $c->group?->name ?? 'سایر خدمات');
                            @endphp

                            <div class="space-y-2 pt-1">
                                @foreach($dossierGrouped as $grpTitle => $srvs)
                                <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700/60 space-y-1.5">
                                    <div class="text-[11px] font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                        <span>📂</span>
                                        <span>دسته: {{ $grpTitle }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($srvs as $srv)
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 text-xs">
                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $srv->name }}</span>
                                            @if($srv->base_labor_fee)
                                            <span class="text-[10px] text-gray-400">{{ number_format($srv->base_labor_fee) }} ریال</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <div class="text-xs text-gray-400 italic">تخصصی برای این تکنسین ثبت نشده است.</div>
                        @endforelse
                    </div>
                </div>

                {{-- National Card Document Preview --}}
                @php
                    $dossierMeta = is_array($dossierTech->meta) ? $dossierTech->meta : [];
                    $dossierCardPath = $dossierMeta['national_card_image'] ?? $dossierMeta['custom_fields']['national_card_image'] ?? null;
                @endphp
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60 space-y-2">
                    <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span>🪪</span>
                        <span>مدرک شناسایی / کارت ملی</span>
                    </div>
                    @if($dossierCardPath)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                            <span>📎</span>
                            <span>تصویر کارت ملی پیوست شده است</span>
                        </div>
                        <a href="{{ asset('storage/' . $dossierCardPath) }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition">
                            مشاهده و دانلود مدرک
                        </a>
                    </div>
                    @else
                    <div class="text-xs text-gray-400">تصویر مدرک شناسایی بارگذاری نشده است.</div>
                    @endif
                </div>

                {{-- ContractForge Contracts Dossier --}}
                @if($contractForgeActive)
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-750 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span>📜</span>
                            <span>قراردادهای حقوقی (ContractForge)</span>
                        </div>
                        <button type="button" wire:click="openContractModal({{ $dossierTech->id }})"
                                class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white text-[10px] font-bold hover:bg-indigo-700 transition cursor-pointer">
                            صدور قرارداد جدید
                        </button>
                    </div>

                    @if($dossierTech->contracts->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-right">
                            <thead class="text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="pb-2">شماره قرارداد</th>
                                    <th class="pb-2">قالب</th>
                                    <th class="pb-2">تاریخ صدور</th>
                                    <th class="pb-2">وضعیت</th>
                                    <th class="pb-2 text-left">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($dossierTech->contracts as $c)
                                <tr>
                                    <td class="py-2.5 font-bold text-gray-800 dark:text-gray-200">{{ $c->contract_number }}</td>
                                    <td class="py-2.5 text-gray-600 dark:text-gray-300">{{ $c->template?->name ?? 'قرارداد تکنسین' }}</td>
                                    <td class="py-2.5 text-gray-500">
                                        {{ function_exists('jdate') ? jdate($c->created_at)->format('Y/m/d') : $c->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $c->status === 'signed' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : ($c->status === 'active' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600') }}">
                                            {{ $c->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-left">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('user.contracts.show', $c->id) }}" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-[10px] font-bold">
                                                مشاهده
                                            </a>
                                            <a href="{{ route('user.contracts.pdf', $c->id) }}" target="_blank" class="px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold">
                                                PDF
                                            </a>
                                            <a href="{{ route('user.contracts.print', $c->id) }}" target="_blank" class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold">
                                                چاپ
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-xs text-gray-400">تاکنون قراردادی برای این تکنسین صادر نشده است.</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Close Footer --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end">
                <button type="button" wire:click="closeDossierModal" class="px-5 py-2 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition font-sans cursor-pointer">
                    بستن پرونده
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Issue Contract Modal (ContractForge) --}}
    @if($contractForgeActive && $showContractModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-md w-full p-6 space-y-5 my-8 font-sans" @click.outside="closeContractModal">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">صدور قرارداد برای تکنسین</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ $contractTechnicianName }}</p>
                </div>
                <button type="button" wire:click="closeContractModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1 cursor-pointer">
                    ✕
                </button>
            </div>

            @if($technicianTemplates->isEmpty())
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs space-y-2">
                    <div class="font-bold">هیچ قالب قراردادی برای «تکنسین فنی» تعریف نشده است.</div>
                    <a href="{{ route('user.contracts.templates.create', ['entity_type' => 'field_service_technician']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold text-xs">
                        ایجاد قالب قرارداد تکنسین
                    </a>
                </div>
            @else
                <form wire:submit.prevent="issueContract" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">انتخاب قالب قرارداد *</label>
                        <select wire:model="selectedTemplateId" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="">انتخاب کنید...</option>
                            @foreach($technicianTemplates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedTemplateId') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت صدور</label>
                        <select wire:model="contractInitialStatus" class="w-full h-10 px-3 text-xs rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans cursor-pointer">
                            <option value="active">ارسال مستقیم برای تایید و امضای تکنسین</option>
                            <option value="draft">ذخیره به عنوان پیش‌نویس</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeContractModal" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-sans cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm shadow-indigo-600/30 font-sans cursor-pointer">
                            <span wire:loading.remove wire:target="issueContract">صدور قرارداد</span>
                            <span wire:loading wire:target="issueContract">در حال صدور...</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @endif
</div>
