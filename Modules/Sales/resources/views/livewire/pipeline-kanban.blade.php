<div class="space-y-6" x-data="{ draggingDealId: null }">
    <!-- Filters and Top Actions -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو پرونده یا مشتری..." 
                    class="w-full text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>

            <!-- Only My Deals -->
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="onlyMyDeals" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <span class="text-sm text-gray-700 dark:text-gray-300">فقط پرونده‌های من</span>
            </label>

            <!-- Campaign Filter -->
            @if(count($campaigns) > 0)
                <select wire:model.live="selectedCampaignId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-300">
                    <option value="">همه کمپین‌ها</option>
                    @foreach($campaigns as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <button wire:click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-2xl shadow-md hover:shadow-lg transition-all w-full md:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            ایجاد پرونده جدید
        </button>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-6 select-none" style="min-height: calc(100vh - 300px);">
        @foreach ($stages as $stage)
            <div 
                class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-900/40 rounded-3xl p-4 flex flex-col border border-gray-100/50 dark:border-gray-800/50"
                x-on:dragover.prevent=""
                x-on:drop="
                    $wire.moveDeal(draggingDealId, {{ $stage->id }});
                    draggingDealId = null;
                "
            >
                <!-- Stage Title -->
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200/50 dark:border-gray-800/50">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color }}"></span>
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $stage->name }}</h3>
                        <span class="text-xs bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full font-bold">
                            {{ count($dealsByStage[$stage->id] ?? []) }}
                        </span>
                    </div>
                    
                    <button wire:click="openCreateModal({{ $stage->id }})" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 space-y-3 overflow-y-auto max-h-[600px] pr-1">
                    @forelse ($dealsByStage[$stage->id] ?? [] as $deal)
                        @php
                            // Check if no active follow-up and last updated was > 3 days ago
                            $hasActiveFollowups = $deal->tasks()
                                ->whereIn('status', [\Modules\Tasks\Entities\Task::STATUS_TODO, \Modules\Tasks\Entities\Task::STATUS_IN_PROGRESS])
                                ->where('due_at', '>=', now())
                                ->exists();
                            $showWarning = !$hasActiveFollowups && $deal->updated_at < now()->subDays(3);
                        @endphp
                        <div 
                            draggable="true"
                            x-on:dragstart="draggingDealId = {{ $deal->id }}"
                            x-on:dragend="draggingDealId = null"
                            class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-indigo-100 dark:hover:border-indigo-900/30 transition-all cursor-grab active:cursor-grabbing relative"
                        >
                            <!-- Warning flag -->
                            @if($showWarning)
                                <div class="absolute top-3 left-3 text-amber-500 hover:text-amber-600 tooltip" title="عدم پیگیری در ۳ روز گذشته">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="flex flex-col gap-2">
                                <a href="{{ route('user.sales.deals.show', $deal->id) }}" class="font-bold text-gray-900 dark:text-white text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block pr-4">
                                    {{ $deal->title }}
                                </a>

                                <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ $deal->client?->full_name ?? 'بدون مشتری' }}</span>
                                </div>

                                <!-- Expected Revenue -->
                                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                    <span class="text-xs text-gray-400">ارزش تخمینی:</span>
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ number_format((float) $deal->expected_revenue) }} ریال
                                    </span>
                                </div>

                                <!-- Bottom actions: quick call -->
                                @if($deal->client && $deal->client->phone)
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-[10px] text-gray-400">کارشناس: {{ $deal->owner?->name ?? '—' }}</span>
                                        <button wire:click="initiateVoip('{{ $deal->client->phone }}', {{ $deal->client->id }})" class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                            پرونده‌ای وجود ندارد
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Deal Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 relative rounded-3xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100 dark:border-gray-700 p-6 flex flex-col gap-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">ایجاد پرونده فروش جدید</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Layout Grid (Multi-column) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Title -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">عنوان پرونده <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="newDealTitle" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                        @error('newDealTitle') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Client selection -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">مشتری مرتبط <span class="text-red-500">*</span></label>
                        <select wire:model="newDealClientId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                            <option value="">انتخاب کنید...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                        @error('newDealClientId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stage -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">مرحله اولیه <span class="text-red-500">*</span></label>
                        <select wire:model="newDealStageId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                            @foreach($stages as $stg)
                                <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                            @endforeach
                        </select>
                        @error('newDealStageId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Expected Revenue -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">ارزش تخمینی (ریال) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="newDealExpectedRevenue" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                        @error('newDealExpectedRevenue') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Probability -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">احتمال موفقیت (درصد)</label>
                        <input type="number" wire:model="newDealProbability" placeholder="مثال: ۵۰" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                        @error('newDealProbability') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Expected Close Date -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">تاریخ احتمالی بسته شدن</label>
                        <input type="date" wire:model="newDealExpectedCloseDate" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                        @error('newDealExpectedCloseDate') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Account Manager -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">کارشناس فروش <span class="text-red-500">*</span></label>
                        <select wire:model="newDealUserId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                            @endforeach
                        </select>
                        @error('newDealUserId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lead Source -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">منبع ورودی</label>
                        <input type="text" wire:model="newDealSource" placeholder="گوگل، اینستاگرام، معرف..." class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                    </div>

                    <!-- Description -->
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">توضیحات</label>
                        <textarea wire:model="newDealDescription" rows="3" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="$set('showCreateModal', false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                    <button wire:click="saveDeal" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all">ذخیره پرونده</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Loss Reason Modal -->
    @if($showLossReasonModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 relative rounded-3xl shadow-xl w-full max-w-md border border-gray-100 dark:border-gray-700 p-6 flex flex-col gap-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">دلیل شکست پرونده چیست؟</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">لطفاً دلیل بسته شدن پرونده با وضعیت ناموفق را انتخاب کنید.</p>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">علت شکست پرونده</label>
                    <select wire:model="lossReasonId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                        <option value="">انتخاب دلیل شکست...</option>
                        @foreach($lossReasons as $reason)
                            <option value="{{ $reason->id }}">{{ $reason->reason_text }}</option>
                        @endforeach
                    </select>
                    @error('lossReasonId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="$set('showLossReasonModal', false)" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                    <button wire:click="submitLossReason" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-md hover:shadow-lg transition-all">تایید و ثبت شکست</button>
                </div>
            </div>
        </div>
    @endif
</div>
