@includeIf('partials.jalali-date-picker')

<div class="space-y-6">

    {{-- Toast Notifications --}}
    @if ($toastSuccess)
        <div class="flex items-center justify-between gap-3 rounded-2xl border-l-4 border-emerald-500 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-200 transition-all">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-bold">{{ $toastSuccess }}</span>
            </div>
            <button wire:click="$set('toastSuccess', null)" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
        </div>
    @endif

    @if ($toastError)
        <div class="flex items-center justify-between gap-3 rounded-2xl border-l-4 border-rose-500 bg-rose-50 px-5 py-4 text-rose-800 shadow-sm dark:bg-rose-900/40 dark:text-rose-200 transition-all">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-bold">{{ $toastError }}</span>
            </div>
            <button wire:click="$set('toastError', null)" class="text-rose-600 hover:text-rose-900 font-bold">✕</button>
        </div>
    @endif

    {{-- Queue Disabled Warning --}}
    @if (!$isQueueEnabled)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">صف انتظار نوبت‌دهی غیرفعال است</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">برای فعال‌سازی و تنظیم حداکثر ظرفیت صف، به بخش تنظیمات نوبت‌دهی مراجعه فرمایید.</p>
                </div>
            </div>
            <a href="{{ route('user.booking.settings.edit') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap">
                تنظیمات سیستم
            </a>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-teal-600 text-white shadow-lg shadow-teal-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                مدیریت صف انتظار نوبت‌دهی
            </h1>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1.5">
                مشاهده، نوبت‌دهی و مدیریت اولویت افراد منتظر در صف خدمات
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.booking.schedule.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                📅 تقویم نوبت‌ها
            </a>
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-lg shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + افزودن به صف انتظار
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Card 1: Total Waiting --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 block">در انتظار نوبت</span>
                <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5 block">{{ $totalWaitingCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl">
                ⏳
            </div>
        </div>

        {{-- Card 2: General Queue --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block">صف عمومی (بدون سرویس)</span>
                <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5 block">{{ $generalWaitingCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl">
                🌐
            </div>
        </div>

        {{-- Card 3: Converted --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block">نوبت داده شده</span>
                <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5 block">{{ $convertedCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl">
                ✅
            </div>
        </div>

        {{-- Card 4: Added Today --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 block">ثبت شده‌های امروز</span>
                <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1.5 block">{{ $todayAddedCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xl">
                📥
            </div>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search Input --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">🔍 جستجوی مراجع</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="نام، شماره تماس یا کد ملی..."
                       class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
            </div>

            {{-- Service Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">🛠️ فیلتر بر اساس سرویس</label>
                <select wire:model.live="selectedServiceFilter" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
                    <option value="">همه صف‌ها</option>
                    <option value="general">🌐 فقط صف عمومی</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Provider Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">👨‍⚕️ {{ config('booking.labels.provider', 'ارائه‌دهنده') }}</label>
                <select wire:model.live="selectedProviderFilter" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
                    <option value="">همه ارائه‌دهندگان</option>
                    @foreach($providers as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">🏷️ وضعیت نوبت در صف</label>
                <select wire:model.live="statusFilter" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
                    <option value="all">همه وضعیت‌ها</option>
                    @foreach($statusLabels as $sKey => $sLabel)
                        <option value="{{ $sKey }}">{{ $sLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Waitlist Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-sm text-right">
                <thead class="bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-center w-36">موقعیت در صف</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مراجع / پرونده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">سرویس / {{ config('booking.labels.provider', 'ارائه‌دهنده') }}</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ ترجیحی</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">توضیحات</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-center w-28">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($entries as $entry)
                        @php
                            $statusStyle = match($entry->status) {
                                'waiting' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200 border border-amber-200 dark:border-amber-800/40',
                                'notified' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800/40',
                                'in_progress' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200 border border-blue-200 dark:border-blue-800/40',
                                'converted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800/40',
                                'canceled' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200 border border-rose-200 dark:border-rose-800/40',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                            };
                        @endphp
                        <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            {{-- Position / Priority --}}
                            <td class="px-4 py-3 text-center">
                                @if($entry->queue_rank === 1 && in_array($entry->status, ['waiting', 'notified', 'in_progress']))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 text-xs font-bold whitespace-nowrap shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>نفر ۱ (نوبت بعدی)</span>
                                    </span>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-800 dark:text-gray-200 text-xs font-bold whitespace-nowrap">
                                        <span>نفر {{ $entry->queue_rank }}</span>
                                        @if($entry->queue_ahead_count > 0 && in_array($entry->status, ['waiting', 'notified', 'in_progress']))
                                            <span class="text-[11px] font-normal text-gray-500 dark:text-gray-400">({{ $entry->queue_ahead_count }} نفر جلوتر)</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Client Info --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $entry->client?->full_name ?? '—' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">{{ $entry->client?->phone }} @if($entry->client?->national_code) • {{ $entry->client?->national_code }} @endif</span>
                                </div>
                            </td>

                            {{-- Service & Provider --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                                        @if($entry->service)
                                            {{ $entry->service->name }}
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">🌐 صف عمومی</span>
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $entry->provider ? $entry->provider->name : 'هر ارائه‌دهنده‌ای' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Preferred Date --}}
                            <td class="px-4 py-3">
                                @if($entry->preferred_date)
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ \Morilog\Jalali\Jalalian::fromDateTime($entry->preferred_date)->format('Y/m/d') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">تعیین نشده</span>
                                @endif
                            </td>

                            {{-- Notes --}}
                            <td class="px-4 py-3 max-w-[180px]">
                                <span class="text-xs text-gray-600 dark:text-gray-400 truncate block" title="{{ $entry->notes }}">
                                    {{ $entry->notes ?: '—' }}
                                </span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $statusStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $entry->status_label }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openStatusModal({{ $entry->id }})" title="ویرایش وضعیت و یادداشت"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    @if($entry->status !== 'converted')
                                        <a href="{{ route('user.booking.schedule.index') }}?client_id={{ $entry->client_id }}&service_id={{ $entry->service_id }}&waitlist_id={{ $entry->id }}"
                                           title="تخصیص نوبت در تقویم"
                                           class="p-1.5 rounded-lg text-teal-600 hover:text-teal-900 hover:bg-teal-50 dark:text-teal-400 dark:hover:text-teal-200 dark:hover:bg-teal-900/40 transition">
                                            📅
                                        </a>
                                    @endif

                                    <button wire:click="cancelEntry({{ $entry->id }})"
                                            onclick="return confirm('آیا از خروج این مراجع از صف انتظار مطمئن هستید؟')"
                                            title="خروج از صف"
                                            class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:text-rose-400 dark:hover:text-rose-300 dark:hover:bg-rose-900/40 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-3xl mb-2">📋</span>
                                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400">هیچ مراجعی در صف انتظار یافت نشد</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($entries->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full shadow-2xl border border-gray-100 dark:border-gray-700 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="h-1.5 w-full bg-gradient-to-r from-teal-500 to-emerald-500"></div>

                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span>➕</span> افزودن مراجع به صف انتظار
                        </h3>
                        <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold">✕</button>
                    </div>

                    @if ($modalError)
                        <div class="p-3 bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-200 text-xs font-bold">
                            {{ $modalError }}
                        </div>
                    @endif

                    <div class="space-y-4 text-xs font-bold">
                        {{-- Client Selector --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">انتخاب مراجع / بیمار <span class="text-rose-500">*</span></label>
                            @if ($selectedModalClient)
                                <div class="p-3 rounded-xl border border-teal-200 bg-teal-50 dark:bg-teal-900/30 flex items-center justify-between">
                                    <div>
                                        <span class="font-black text-teal-950 dark:text-teal-100 block">{{ $selectedModalClient->full_name }}</span>
                                        <span class="text-[10px] text-teal-700 dark:text-teal-300 block mt-0.5 dir-ltr text-right">{{ $selectedModalClient->phone }}</span>
                                    </div>
                                    <button type="button" wire:click="$set('modalClientId', null)" class="px-2.5 py-1 bg-teal-200 dark:bg-teal-800 text-teal-900 dark:text-teal-100 rounded-lg text-[10px] font-bold">تغییر مراجع</button>
                                </div>
                            @else
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="modalClientSearch" placeholder="جستجوی نام یا شماره تماس..."
                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100">
                                </div>
                                @if (!empty($clientsForModal) && count($clientsForModal) > 0)
                                    <div class="mt-1 max-h-36 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800 shadow-md">
                                        <div class="px-2 py-1 text-[10px] font-bold text-gray-400 dark:text-gray-500 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30">
                                            <span>{{ empty($modalClientSearch) ? '۳ مراجع اخیر' : 'نتایج جستجو (' . count($clientsForModal) . ' مورد)' }}</span>
                                            <span class="text-[9px] text-teal-600">برای انتخاب کلیک کنید</span>
                                        </div>
                                        @foreach($clientsForModal as $cm)
                                            <div wire:click="selectModalClient({{ $cm->id }})" class="p-2 hover:bg-teal-50 dark:hover:bg-teal-950/40 cursor-pointer flex items-center justify-between">
                                                <div>
                                                    <span class="font-black text-gray-900 dark:text-white">{{ $cm->full_name }}</span>
                                                    <span class="text-[10px] text-gray-400 block dir-ltr text-right">{{ $cm->phone }}</span>
                                                </div>
                                                <span class="text-[10px] text-teal-600 font-bold">انتخاب</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Service & Provider Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                                    <span>سرویس نوبت</span>
                                    @if($modalProviderId)
                                        <span class="text-[10px] text-teal-600 dark:text-teal-400">وابسته به ارائه‌دهنده</span>
                                    @endif
                                </label>
                                <select wire:model.live="modalServiceId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100">
                                    <option value="">صف عمومی (بدون وابستگی)</option>
                                    @foreach($modalServices as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                                    <span>{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</span>
                                    @if($modalServiceId)
                                        <span class="text-[10px] text-teal-600 dark:text-teal-400">وابسته به سرویس</span>
                                    @endif
                                </label>
                                <select wire:model.live="modalProviderId" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100">
                                    <option value="">بدون ترجیح (هر ارائه‌دهنده)</option>
                                    @foreach($modalProviders as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Preferred Date --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">تاریخ ترجیحی مراجع (اختیاری)</label>
                            <input type="text" wire:model="modalPreferredDateJalali" placeholder="۱۴۰۵/۰۶/۰۱" data-jdp-only-date
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100 text-center">
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">توضیحات و یادداشت</label>
                            <textarea wire:model="modalNotes" rows="2" placeholder="علت قرارگیری در صف یا درخواست خاص مراجع..."
                                      class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button wire:click="closeCreateModal" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200">انصراف</button>
                        <button wire:click="saveNewWaitlistEntry" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-md">ثبت در صف</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Status Update Modal --}}
    @if ($showStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-sm w-full shadow-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">تغییر وضعیت نوبت در صف</h3>
                    <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <div class="space-y-3 text-xs font-bold">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 mb-1">وضعیت جدید</label>
                        <select wire:model="editingStatus" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100">
                            @foreach($statusLabels as $sKey => $sLabel)
                                <option value="{{ $sKey }}">{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 mb-1">یادداشت</label>
                        <textarea wire:model="editingNotes" rows="3" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="closeStatusModal" class="px-3.5 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300">انصراف</button>
                    <button wire:click="updateEntryStatus" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-sm">ذخیره تغییرات</button>
                </div>
            </div>
        </div>
    @endif
</div>
