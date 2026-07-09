<div class="space-y-4" dir="rtl">
    <!-- Filter bar -->
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Status filter -->
            <select wire:model.live="filterStatus" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                <option value="all">همه وضعیت‌ها (فعال)</option>
                <option value="pending">در انتظار تماس</option>
                <option value="contacted">تماس گرفته شده</option>
                <option value="responded">پاسخ‌داده‌شده</option>
                <option value="lost">شکست خورده</option>
            </select>
        </div>

        <!-- Search input -->
        <div class="relative w-full sm:w-64">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو در سرنخ‌های من..." 
                class="w-full text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-bold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="p-4">نام سرنخ</th>
                        <th class="p-4">شماره تماس</th>
                        <th class="p-4">کمپین مربوطه</th>
                        <th class="p-4">وضعیت تماس</th>
                        <th class="p-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-gray-900 dark:text-gray-100">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="p-4 font-bold text-gray-900 dark:text-white">
                                {{ $contact->name }}
                                @if($contact->client_id)
                                    <span class="text-[9px] bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400 px-1.5 py-0.5 rounded font-normal ms-1">عضو CRM</span>
                                @endif
                            </td>
                            <td class="p-4 font-semibold" dir="ltr">{{ $contact->phone }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-750 dark:text-gray-300 text-[10px] font-bold">
                                    {{ $contact->campaign?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <select 
                                    wire:change="updateContactStatus({{ $contact->id }}, $event.target.value)"
                                    class="text-[11px] rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 py-1 px-2 focus:ring-1 focus:ring-indigo-500"
                                    {{ $contact->status === 'converted' ? 'disabled' : '' }}
                                >
                                    <option value="pending" {{ $contact->status === 'pending' ? 'selected' : '' }}>در انتظار تماس</option>
                                    <option value="contacted" {{ $contact->status === 'contacted' ? 'selected' : '' }}>تماس گرفته شده</option>
                                    <option value="responded" {{ $contact->status === 'responded' ? 'selected' : '' }}>پاسخ‌داده‌شده</option>
                                    <option value="converted" {{ $contact->status === 'converted' ? 'selected' : '' }} disabled>تبدیل به پرونده</option>
                                    <option value="lost" {{ $contact->status === 'lost' ? 'selected' : '' }}>شکست خورده</option>
                                </select>
                            </td>
                            <td class="p-4 text-center flex items-center justify-center gap-2">
                                @if($contact->assigned_to === null)
                                    <button wire:click="claimContact({{ $contact->id }})" 
                                            title="قبول سرنخ"
                                            class="text-indigo-600 hover:text-indigo-800 transition-colors p-1.5 bg-indigo-50 dark:bg-indigo-950/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 flex items-center gap-1 font-bold">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>قبول سرنخ</span>
                                    </button>
                                @endif

                                @if($contact->status !== 'converted')
                                    <button wire:click="convertToDeal({{ $contact->id }})" 
                                            title="تبدیل به پرونده فروش (Deal)"
                                            class="text-emerald-600 hover:text-emerald-800 transition-colors p-1.5 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 flex items-center gap-1 font-bold">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>تبدیل به پرونده</span>
                                    </button>
                                @else
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[11px] bg-emerald-50 dark:bg-emerald-950/30 px-2 py-1 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        پرونده فعال
                                    </span>
                                @endif

                                <button wire:click="initiateVoipCall('{{ $contact->phone }}', {{ $contact->id }})" 
                                        title="تماس صوتی VoIP"
                                        class="text-blue-600 hover:text-blue-800 transition-colors p-1.5 bg-blue-50 dark:bg-blue-950/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                هیچ سرنخ کمپینی به شما تخصیص نیافته است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $contacts->links() }}
        </div>
    </div>
</div>
