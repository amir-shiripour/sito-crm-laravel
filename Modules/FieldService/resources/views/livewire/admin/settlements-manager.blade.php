<div class="space-y-4 font-sans">
    {{-- Header & Metrics --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">سامانه تسویه بانکی پایا تکنسین‌ها</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تولید فایل تجمیعی حواله پایا به شبا و مانیتورینگ انتقال وجوه بانکی</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.fieldservice.settlements.export') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <span>📥</span>
                <span>تولید و دانلود فایل بچ پایا</span>
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/30 p-4 rounded-2xl flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-amber-800 dark:text-amber-300">در صف پرداخت و تایید حواله پایا</div>
                <div class="text-xl font-black text-amber-900 dark:text-amber-200 mt-1">
                    {{ number_format($pendingTotal / 10) }} تومان
                </div>
            </div>
            <span class="text-2xl">⏳</span>
        </div>

        <div class="bg-emerald-50/80 dark:bg-emerald-500/10 border border-emerald-200/80 dark:border-emerald-500/30 p-4 rounded-2xl flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">مجموع تسویه‌های موفق پایا</div>
                <div class="text-xl font-black text-emerald-900 dark:text-emerald-200 mt-1">
                    {{ number_format($completedTotal / 10) }} تومان
                </div>
            </div>
            <span class="text-2xl">✅</span>
        </div>
    </div>

    {{-- Settlements Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <div class="relative w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی کد پیگیری، شبا یا نام..." class="w-full h-8 pr-7 pl-3 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 font-sans">
                <span class="absolute right-2 top-2 text-gray-400 text-xs">🔍</span>
            </div>

            <select wire:model.live="statusFilter" class="h-8 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs font-sans">
                <option value="all">همه وضعیت‌ها</option>
                <option value="pending">در صف تسویه</option>
                <option value="processing">در حال پردازش بانک</option>
                <option value="completed">تسویه شده</option>
                <option value="rejected">رد شده</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50/70 dark:bg-gray-750 border-b border-gray-200/60 dark:border-gray-700/60 text-gray-400 font-bold">
                        <th class="py-3 px-4">شناسه پیگیری</th>
                        <th class="py-3 px-4">تکنسین / ذینفع</th>
                        <th class="py-3 px-4">شماره شبا (IBAN)</th>
                        <th class="py-3 px-4">مبلغ تسویه</th>
                        <th class="py-3 px-4">تاریخ درخواست</th>
                        <th class="py-3 px-4">وضعیت</th>
                        <th class="py-3 px-4 text-center">اقدام</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    @forelse($settlements as $s)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">{{ $s->tracking_code }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $s->account_owner_name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $s->technician?->mobile }}</div>
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300 font-bold" dir="ltr">
                            {{ $s->shaba_number }}
                        </td>
                        <td class="py-3 px-4 font-black text-emerald-600 dark:text-emerald-400">
                            {{ number_format($s->amount / 10) }} تومان
                        </td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400">
                            {{ function_exists('jdate') ? jdate($s->requested_at)->format('Y/m/d H:i') : $s->requested_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $s->status->badgeClass() }}">
                                {{ $s->status->label() }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($s->status->value === 'pending' || $s->status->value === 'processing')
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" wire:click="approve({{ $s->id }})" class="p-1 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-bold" title="تایید تسویه">
                                    ✓
                                </button>
                                <button type="button" wire:click="reject({{ $s->id }})" class="p-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-bold" title="رد تسویه">
                                    ✕
                                </button>
                            </div>
                            @else
                            <span class="text-gray-400 text-[10px]">بسته شده</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">
                            هیچ رکورد تسویه پایا موجود نیست.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
            {{ $settlements->links() }}
        </div>
        @endif
    </div>
</div>
