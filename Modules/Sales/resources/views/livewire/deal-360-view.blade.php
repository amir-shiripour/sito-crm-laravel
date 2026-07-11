<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Stepper / Pipeline Progress Header (Span full width) -->
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <h2 class="text-sm font-bold text-gray-400 mb-4">مسیر پیشرفت پرونده فروش</h2>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            @foreach($stages as $stg)
                @php
                    $isCurrent = $deal->pipeline_stage_id === $stg->id;
                    $passed = $stg->order <= ($deal->stage?->order ?? 0);
                @endphp
                <div class="flex-1 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                            {{ $isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 dark:ring-indigo-900/30' : ($passed ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($passed && !$isCurrent)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $stg->order }}
                            @endif
                        </div>
                        <span class="text-xs font-bold {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-400' : ($passed ? 'text-gray-900 dark:text-white' : 'text-gray-400') }}">
                            {{ $stg->name }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="hidden md:block flex-1 h-0.5 mx-2 rounded {{ $passed && !$isCurrent ? 'bg-emerald-300 dark:bg-emerald-900/50' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Side: Timeline of interaction logs (2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Deal Info Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-4" dir="rtl">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $deal->title }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $deal->description ?: 'بدون توضیحات اضافی' }}</p>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2">
                    <div class="text-right sm:text-left">
                        <span class="text-xs text-gray-400">ارزش پیش‌بینی‌شده:</span>
                        <h3 class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ number_format((float) $deal->expected_revenue) }} ریال</h3>
                    </div>
                    @if($deal->status === 'open')
                        <div class="flex items-center gap-2 mt-1">
                            <button wire:click="openCloseModal('won')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-md transition-all flex items-center gap-1">
                                🏆 موفق (Won)
                            </button>
                            <button wire:click="openCloseModal('lost')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-md transition-all flex items-center gap-1">
                                ❌ شکست (Lost)
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                <div>
                    <span class="text-[10px] text-gray-400 block">احتمال موفقیت</span>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $deal->probability ?? '—' }}٪</span>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 block">تاریخ پیش‌بینی بسته شدن</span>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $deal->expected_close_date ? $deal->expected_close_date->format('Y-m-d') : '—' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 block">منبع ورودی پرونده</span>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $deal->lead_source ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 block">وضعیت فعلی</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold inline-block
                        {{ $deal->status === 'won' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' : ($deal->status === 'lost' ? 'bg-red-50 text-red-600 dark:bg-red-950/30 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400') }}">
                        @if($deal->status === 'won')
                            پیروزی
                        @elseif($deal->status === 'lost')
                            شکست ({{ $deal->lossReason?->reason_text }})
                        @else
                            فعال
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Interactive Timeline -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-6">تایملاین تعاملات و تاریخچه</h3>
            
            <div class="relative border-r-2 border-gray-100 dark:border-gray-700 pr-6 space-y-6 mr-3">
                @forelse($timeline as $event)
                    <div class="relative">
                        <!-- Bullet Icon -->
                        <span class="absolute top-0 right-[-32px] w-6 h-6 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-gray-800
                            {{ $event['type'] === 'call' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : ($event['type'] === 'task' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400') }}">
                            @if($event['type'] === 'call')
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            @elseif($event['type'] === 'task')
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            @endif
                        </span>

                        <div class="bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $event['title'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ $event['date'] }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 line-clamp-3">{{ $event['description'] }}</p>
                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-200/30 dark:border-gray-800/30">
                                <span class="text-[10px] text-gray-400">ثبت‌کننده: {{ $event['user'] }}</span>
                                <span class="text-[10px] uppercase font-bold
                                    {{ $event['status'] === 'done' || $event['status'] === 'DONE' ? 'text-emerald-500' : 'text-amber-500' }}">
                                    {{ $event['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-xs text-gray-400">
                        تاریخچه‌ای برای این پرونده یافت نشد.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Left Side: Client Widget & Tasks (1 col) -->
    <div class="space-y-6">
        <!-- Client profile Widget -->
        @if($deal->client)
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                        {{ mb_substr($deal->client->full_name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $deal->client->full_name }}</h4>
                        <span class="text-[10px] text-gray-400">مشتری بالقوه / لید</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 pt-3 border-t border-gray-100 dark:border-gray-700/50 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-400">تلفن تماس:</span>
                        <span class="font-semibold text-gray-900 dark:text-white" dir="ltr">{{ $deal->client->phone ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">کد ملی:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $deal->client->national_code ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">شماره پرونده:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $deal->client->case_number ?: '—' }}</span>
                    </div>
                </div>

                <!-- Custom Client Fields (Form builder integrations) -->
                @if(count($clientCustomFields) > 0)
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/50">
                        <h5 class="text-[10px] font-bold text-gray-400 mb-2 uppercase">فیلدهای فرم‌ساز مشتری</h5>
                        <div class="flex flex-col gap-2 text-xs">
                            @foreach($clientCustomFields as $field)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">{{ $field['label'] }}:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $field['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Follow-ups / Open Tasks -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">اقدامات بعدی و پیگیری‌ها</h3>

            <!-- Open Tasks List -->
            <div class="space-y-3">
                @forelse($openTasks as $task)
                    <div class="flex items-start justify-between gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">{{ $task->title }}</span>
                            <span class="text-[10px] text-gray-400 mt-1 block">سررسید: {{ $task->due_at ? $task->due_at->format('Y-m-d') : '—' }}</span>
                        </div>
                        <button wire:click="completeTask({{ $task->id }})" class="p-1 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/40 dark:text-emerald-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                @empty
                    <div class="text-center py-4 text-xs text-gray-400">
                        هیچ پیگیری بازی ثبت نشده است.
                    </div>
                @endforelse
            </div>

            <!-- Quick Add Task Form -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex flex-col gap-3">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white">برنامه‌ریزی پیگیری بعدی</h4>
                
                <div class="flex flex-col gap-2">
                    <input type="text" wire:model="newTaskTitle" placeholder="عنوان پیگیری..." class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('newTaskTitle') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model="newTaskDueAt" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                        <select wire:model="newTaskPriority" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                            <option value="LOW">اولویت کم</option>
                            <option value="MEDIUM">معمولی</option>
                            <option value="HIGH">زیاد</option>
                            <option value="CRITICAL">بحرانی</option>
                        </select>
                    </div>
                    @error('newTaskDueAt') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror

                    <textarea wire:model="newTaskDescription" rows="2" placeholder="توضیحات کوتاه..." class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-400"></textarea>
                    
                    <button wire:click="addFollowUp" class="mt-1 w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm hover:shadow-md transition-all">
                        ثبت پیگیری
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Deal Modal -->
    @if($showCloseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" dir="rtl">
            <div class="bg-white dark:bg-gray-800 relative rounded-3xl shadow-xl w-full max-w-md border border-gray-100 dark:border-gray-700 p-6 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        {{ $closeType === 'won' ? 'ثبت موفقیت پرونده فروش' : 'ثبت شکست پرونده فروش' }}
                    </h3>
                    <button wire:click="$set('showCloseModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    @if($closeType === 'won')
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">درآمد واقعی نهایی (ریال) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="closingRevenue" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                            @error('closingRevenue') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">علت شکست پرونده <span class="text-red-500">*</span></label>
                            <select wire:model="closingLossReasonId" class="text-sm bg-gray-50 dark:bg-gray-900 border-0 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-750 dark:text-300">
                                <option value="">انتخاب دلیل شکست...</option>
                                @foreach($lossReasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->reason_text }}</option>
                                @endforeach
                            </select>
                            @error('closingLossReasonId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="$set('showCloseModal', false)" class="px-4 py-2 text-xs font-bold text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-xl transition-all">
                        انصراف
                    </button>
                    <button wire:click="closeDeal" class="px-5 py-2 text-xs font-bold text-white rounded-xl shadow-md transition-all {{ $closeType === 'won' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-red-600 hover:bg-red-700 shadow-red-500/20' }}">
                        ثبت وضعیت نهایی
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
