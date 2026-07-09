<div class="space-y-6">
    <!-- Top Filter Bar & Actions -->
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو سرنخ..." 
                    class="w-full text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>

            <!-- Status Filter -->
            <select wire:model.live="selectedStatusId" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                <option value="">همه وضعیت‌ها (سرنخ‌ها)</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Bulk Actions Panel -->
        @if(count($selectedClientIds) > 0)
            <div class="w-full lg:w-auto bg-indigo-50 dark:bg-indigo-950/20 px-4 py-2.5 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 flex flex-col sm:flex-row items-center gap-4 animate-fade-in" dir="rtl">
                <span class="text-xs text-indigo-700 dark:text-indigo-400 font-bold whitespace-nowrap">
                    {{ count($selectedClientIds) }} سرنخ انتخاب شده:
                </span>
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Action 1: Assign to Agent -->
                    <div class="flex items-center gap-2 sm:border-l border-indigo-200/50 sm:pl-4">
                        <select wire:model="assignToUserId" class="text-[11px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                            <option value="">انتخاب کارشناس...</option>
                            @foreach($salesAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="bulkAssign" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow transition-all whitespace-nowrap">
                            تخصیص کارشناس
                        </button>
                    </div>
                    
                    <!-- Action 2: Add to Campaign -->
                    <div class="flex items-center gap-2">
                        <select wire:model="assignToCampaignId" class="text-[11px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                            <option value="">انتخاب کمپین...</option>
                            @foreach($campaigns as $camp)
                                <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="addToCampaign" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow transition-all whitespace-nowrap">
                            افزودن به کمپین
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-bold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="p-4 w-12 text-center">
                            <input type="checkbox" 
                                   x-on:click="$wire.toggleSelectAll([{{ implode(',', $clientIdsOnPage) }}])"
                                   {{ count($selectedClientIds) === count($clientIdsOnPage) && count($clientIdsOnPage) > 0 ? 'checked' : '' }}
                                   class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        </th>
                        <th class="p-4">سرنخ / مشتری بالقوه</th>
                        <th class="p-4">شماره تماس</th>
                        <th class="p-4">وضعیت کلاینت</th>
                        <th class="p-4">کارشناسان فروش متصل</th>
                        <th class="p-4">تاریخ عضویت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-gray-900 dark:text-gray-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" value="{{ $lead->id }}" wire:model.live="selectedClientIds"
                                       class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            </td>
                            <td class="p-4 font-bold text-gray-900 dark:text-white">
                                {{ $lead->full_name }}
                                <span class="text-[10px] font-normal text-gray-400 block mt-0.5">@<span>{{ $lead->username }}</span></span>
                            </td>
                            <td class="p-4 font-semibold" dir="ltr">{{ $lead->phone ?: '—' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold inline-block"
                                    style="background-color: {{ ($lead->status?->color ?? '#e2e8f0') . '20' }}; color: {{ $lead->status?->color ?? '#64748b' }}; border: 1px solid {{ ($lead->status?->color ?? '#e2e8f0') . '40' }}">
                                    {{ $lead->status?->label ?? 'نامشخص' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($lead->users as $mgr)
                                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded text-[10px] font-medium">
                                            {{ $mgr->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 italic text-[10px]">بدون کارشناس</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4 text-gray-400 font-medium">{{ $lead->created_at ? $lead->created_at->format('Y-m-d') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                سرنخی برای نمایش یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
