@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-3 flex-grow">
            <div class="w-full sm:w-1/4">
                <input type="text" wire:model.live="search" class="{{ $inputClass }}" placeholder="جستجو در پیگیری‌ها...">
            </div>
            <div class="w-full sm:w-1/4">
                <select wire:model.live="status" class="{{ $inputClass }}">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="open">باز</option>
                    <option value="in_progress">در حال بررسی</option>
                    <option value="done">تکمیل شده</option>
                    <option value="cancelled">لغو شده</option>
                </select>
            </div>
            <div class="w-full sm:w-1/4">
                <select wire:model.live="priority" class="{{ $inputClass }}">
                    <option value="">همه اولویت‌ها</option>
                    <option value="low">کم</option>
                    <option value="medium">متوسط</option>
                    <option value="high">زیاد</option>
                    <option value="urgent">فوری/بحرانی</option>
                </select>
            </div>
        </div>
        <div>
            <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                پیگیری جدید
            </button>
        </div>
    </div>

    <!-- Follow-ups Timeline -->
    <div class="relative pl-4 sm:pl-6 border-l-2 border-gray-100 dark:border-gray-800 space-y-5 ml-2 mt-4">
        @forelse($followups as $fu)
            @php
                $priorityColor = match($fu->priority) {
                    'urgent', 'CRITICAL' => 'bg-rose-500',
                    'high', 'HIGH' => 'bg-amber-500',
                    'medium', 'MEDIUM' => 'bg-indigo-500',
                    default => 'bg-gray-400 dark:bg-gray-600',
                };
            @endphp
            <div class="relative">
                <!-- Timeline Dot -->
                <div class="absolute -left-[21px] sm:-left-[29px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800 {{ $priorityColor }} shadow-sm"></div>

                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ $fu->title }}</h4>
                            @if($fu->client)
                                <span class="text-[10px] text-gray-400">|</span>
                                <button wire:click="selectClient({{ $fu->client->id }})" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">مشتری: {{ $fu->client->full_name }}</button>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-500 font-semibold" dir="ltr">
                                موعد: {{ \Morilog\Jalali\Jalalian::fromDateTime($fu->due_date ?? $fu->due_at)->format('Y/m/d H:i') }}
                            </span>
                            @if(in_array($fu->status, ['done', 'DONE']))
                                <span class="bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md">تکمیل شده</span>
                            @elseif(in_array($fu->status, ['cancelled', 'CANCELED']))
                                <span class="bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md">لغو شده</span>
                            @else
                                <span class="bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md">در انتظار</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">{{ $fu->description ?: 'بدون توضیحات' }}</p>

                    @if(in_array($fu->status, ['open', 'in_progress', 'TODO', 'IN_PROGRESS']))
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-50 dark:border-gray-700/50">
                            <button wire:click="completeFollowUp({{ $fu->id }})" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/20 dark:hover:bg-emerald-950/40 dark:text-emerald-400 rounded-lg text-[10px] font-bold transition-colors border border-emerald-100 dark:border-emerald-900/50">تکمیل</button>
                            <button wire:click="cancelFollowUp({{ $fu->id }})" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 dark:text-rose-400 rounded-lg text-[10px] font-bold transition-colors border border-rose-100 dark:border-rose-900/50">لغو</button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-8 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 mr-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="text-xs font-semibold">پیگیری یافت نشد.</span>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($followups->count())
        <div class="pt-4">
            {{ $followups->links() }}
        </div>
    @endif

    <!-- Create Followup Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">✅ ثبت اقدام پیگیری</h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                        <form wire:submit.prevent="saveFollowUp" class="space-y-5">
                            <div>
                                <label class="{{ $labelClass }}">عنوان پیگیری <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="title" class="{{ $inputClass }}" placeholder="مثال: ارسال پیش‌فاکتور و کاتالوگ">
                                @error('title') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="{{ $labelClass }}">اولویت <span class="text-rose-500">*</span></label>
                                    <select wire:model="followup_priority" class="{{ $inputClass }}">
                                        <option value="low">🔽 کم</option>
                                        <option value="medium">▶️ متوسط</option>
                                        <option value="high">🔼 زیاد</option>
                                        <option value="urgent">🔥 فوری / بحرانی</option>
                                    </select>
                                    @error('followup_priority') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">کمپین مرتبط</label>
                                    <select wire:model="campaign_id" class="{{ $inputClass }}">
                                        <option value="">-- بدون کمپین --</option>
                                        @foreach($campaigns as $camp)
                                            <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('campaign_id') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-5 bg-emerald-50/50 dark:bg-emerald-900/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800/50">
                                <div>
                                    <label class="{{ $labelClass }}">موعد انجام <span class="text-rose-500">*</span></label>
                                    <input type="datetime-local" wire:model="due_date" class="{{ $inputClass }}">
                                    @error('due_date') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">یادآوری سیستم</label>
                                    <input type="datetime-local" wire:model="reminder_at" class="{{ $inputClass }}">
                                    @error('reminder_at') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @if($selectedClientId && $calls && $calls->count())
                                <div>
                                    <label class="{{ $labelClass }}">اتصال به تماس ثبت شده قبلی</label>
                                    <select wire:model="call_id" class="{{ $inputClass }}">
                                        <option value="">-- بدون اتصال --</option>
                                        @foreach($calls as $cl)
                                            <option value="{{ $cl->id }}">تماس مورخ {{ \Morilog\Jalali\Jalalian::fromDateTime($cl->call_date)->format('Y/m/d') }} ({{ $cl->reason ?: 'بدون موضوع' }})</option>
                                        @endforeach
                                    </select>
                                    @error('call_id') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div>
                                <label class="{{ $labelClass }}">توضیحات و دستورالعمل پیگیری</label>
                                <textarea wire:model="description" rows="3" class="{{ $inputClass }} resize-none" placeholder="شرح دقیق اقداماتی که باید انجام شود..."></textarea>
                                @error('description') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                <button type="button" x-on:click="show = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">ذخیره پیگیری</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
