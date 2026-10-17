<div class="space-y-4 font-sans">
    {{-- Header & Search Controls --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">مدیریت سفارش‌ها و ماموریت‌های فنی</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">پایش، بازتوزیع راداری و کنترل جریان فاکتورهای امانی</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی کد سفارش، نام، تلفن..." class="w-full h-9 pr-8 pl-3 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 font-sans">
                <span class="absolute right-2.5 top-2.5 text-gray-400 text-xs">🔍</span>
            </div>

            {{-- Status filter --}}
            <select wire:model.live="statusFilter" class="h-9 px-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 font-sans">
                <option value="all">تمامی وضعیت‌ها</option>
                <option value="searching">رادار جستجو</option>
                <option value="dispatched">اعزام شده به تکنسین</option>
                <option value="accepted">تایید تکنسین</option>
                <option value="en_route">در مسیر</option>
                <option value="arrived">حضور در محل</option>
                <option value="in_progress">در حال عیب‌یابی</option>
                <option value="invoice_pending">در انتظار پرداخت فاکتور</option>
                <option value="completed">پایان یافته</option>
                <option value="cancelled">لغو شده</option>
            </select>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50/70 dark:bg-gray-750 border-b border-gray-200/60 dark:border-gray-700/60 text-gray-400 font-bold">
                        <th class="py-3.5 px-4">کد سفارش</th>
                        <th class="py-3.5 px-4">مشتری (کلاینت CRM)</th>
                        <th class="py-3.5 px-4">دستگاه و عیب</th>
                        <th class="py-3.5 px-4">موقعیت و شعاع</th>
                        <th class="py-3.5 px-4">تکنسین مامور</th>
                        <th class="py-3.5 px-4">وضعیت</th>
                        <th class="py-3.5 px-4">مبلغ فاکتور</th>
                        <th class="py-3.5 px-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                        <td class="py-3 px-4 font-black text-gray-900 dark:text-gray-100">
                            <div>{{ $ord->order_number }}</div>
                            <div class="text-[10px] text-gray-400 font-normal">
                                {{ function_exists('jdate') ? jdate($ord->created_at)->format('Y/m/d H:i') : $ord->created_at->format('Y-m-d H:i') }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $ord->customer_name }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ $ord->customer_phone }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <span>{{ $ord->appliance_type?->icon() }}</span>
                                <span>{{ $ord->appliance_type?->label() }}</span>
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-xs" title="{{ $ord->problem_description }}">
                                {{ $ord->problem_description }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-700 dark:text-gray-300">{{ $ord->city }}</div>
                            <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">شعاع: {{ $ord->search_radius_km }} کیلومتر</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($ord->technician)
                            <div class="font-bold text-indigo-600 dark:text-indigo-400">{{ $ord->technician->full_name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $ord->technician->mobile }}</div>
                            @else
                            <span class="text-amber-600 dark:text-amber-400 font-medium">بدون تکنسین</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $ord->status->badgeClass() }}">
                                {{ $ord->status->label() }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($ord->invoice)
                            <div class="font-black text-gray-900 dark:text-white">{{ number_format($ord->invoice->total_amount / 10) }} تومان</div>
                            <span class="text-[9px] px-1 py-0.2 rounded {{ $ord->invoice->payment_status->badgeClass() }}">
                                {{ $ord->invoice->payment_status->label() }}
                            </span>
                            @else
                            <span class="text-gray-400 text-[10px]">فاکتور صادر نشده</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if(in_array($ord->status->value, ['pending', 'searching', 'dispatched']))
                                <button type="button" wire:click="reDispatch({{ $ord->id }})" class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 transition" title="ارسال مجدد در رادار">
                                    📡
                                </button>
                                @endif

                                @if($ord->status->value !== 'cancelled' && $ord->status->value !== 'completed')
                                <button type="button" wire:click="cancelOrder({{ $ord->id }})" wire:confirm="آیا از لغو این سفارش اطمینان دارید؟" class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 hover:bg-rose-100 transition" title="لغو سفارش">
                                    ✕
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            هیچ سفارشی مطابق جستجوی شما یافت نشد.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
