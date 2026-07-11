@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-6" dir="rtl">
    <!-- Top Filter Bar -->
    <div class="flex flex-col gap-4 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-3 flex-grow w-full">
                <!-- Search -->
                <div class="w-full sm:w-1/3">
                    <input type="text" wire:model.live="search" class="{{ $inputClass }}" placeholder="جستجو در تماس‌ها...">
                </div>
                <!-- Status Filter -->
                <div class="w-full sm:w-1/3">
                    <select wire:model.live="filterStatus" class="{{ $inputClass }}">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="planned">📅 برنامه‌ریزی شده</option>
                        <option value="answered">✅ پاسخ داده شده</option>
                        <option value="no_answer">❌ بدون پاسخ</option>
                        <option value="busy">⏳ اشغال</option>
                        <option value="cancelled">🚫 لغو شده</option>
                        <option value="failed">⚠️ ناموفق</option>
                    </select>
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    تماس جدید
                </button>
            </div>
        </div>

        <!-- Quick Date Filters -->
        <div class="flex items-center gap-2 border-t border-gray-200 dark:border-gray-700 pt-3">
            <span class="text-xs font-semibold text-gray-400">بازه زمانی:</span>
            <div class="flex flex-wrap gap-1">
                <button wire:click="$set('filterDate', 'today')" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $filterDate === 'today' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">امروز</button>
                <button wire:click="$set('filterDate', 'week')" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $filterDate === 'week' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">این هفته</button>
                <button wire:click="$set('filterDate', 'month')" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $filterDate === 'month' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">این ماه</button>
                <button wire:click="$set('filterDate', 'all')" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $filterDate === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">همه</button>
            </div>
        </div>
    </div>

    <!-- Calls Timeline -->
    <div class="relative pr-4 sm:pr-6 border-r-2 border-indigo-100 dark:border-indigo-900/50 space-y-5 mr-2 mt-4">
        @forelse($calls as $call)
            @php
                $isOverdue = $call->status === 'planned' && $call->call_date && $call->call_date->isPast() && !$call->call_date->isToday();
            @endphp
            <div class="relative">
                <!-- Timeline Dot -->
                <div class="absolute -right-[22px] sm:-right-[30px] top-1.5 w-4 h-4 rounded-full border-2 border-white dark:border-gray-800 {{ $call->direction == 'inbound' ? 'bg-teal-500' : 'bg-indigo-500' }} shadow-sm"></div>
                
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-gray-900 dark:text-white">
                                @if($call->client)
                                    <button wire:click="selectClient({{ $call->client->id }})" class="hover:underline text-indigo-600 dark:text-indigo-400">{{ $call->client->full_name }}</button>
                                @else
                                    {{ $call->contact_phone ?: 'بدون شماره' }}
                                @endif
                            </span>
                            @if($call->direction == 'inbound')
                                <span class="text-[10px] bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400 px-1.5 py-0.5 rounded-md font-semibold">ورودی</span>
                            @else
                                <span class="text-[10px] bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 px-1.5 py-0.5 rounded-md font-semibold">خروجی</span>
                            @endif

                            @if($call->duration_seconds)
                                @php
                                    $mins = floor($call->duration_seconds / 60);
                                    $secs = $call->duration_seconds % 60;
                                    $durationStr = ($mins > 0 ? "$mins دقیقه " : "") . ($secs > 0 ? "$secs ثانیه" : "");
                                @endphp
                                <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded-md font-bold">⏱ {{ $durationStr }}</span>
                            @endif

                            @if($isOverdue)
                                <span class="text-[9px] bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400 px-1.5 py-0.5 rounded-md font-extrabold animate-pulse">🔴 معوق سررسید گذشته</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-gray-500 font-semibold" dir="ltr">
                            {{ $call->call_date ? \Morilog\Jalali\Jalalian::fromDateTime($call->call_date)->format('Y/m/d') : '-' }} {{ $call->call_time }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                        <span class="font-bold text-gray-800 dark:text-gray-100 block mb-1">موضوع: {{ $call->reason ?: '-' }}</span>
                        {{ $call->result ?: ($call->notes ?: 'بدون یادداشت و نتیجه') }}
                    </p>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-700/50">
                        <div class="flex items-center gap-3">
                            @if($call->status == 'answered')
                                <span class="bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 text-[10px] font-bold px-2 py-1 rounded-lg">✅ پاسخ داده شده</span>
                            @elseif($call->status == 'planned')
                                <span class="bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 text-[10px] font-bold px-2 py-1 rounded-lg">📅 برنامه‌ریزی شده</span>
                            @elseif($call->status == 'no_answer')
                                <span class="bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 text-[10px] font-bold px-2 py-1 rounded-lg">❌ بدون پاسخ</span>
                            @elseif($call->status == 'busy')
                                <span class="bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 text-[10px] font-bold px-2 py-1 rounded-lg">⏳ اشغال</span>
                            @elseif($call->status == 'cancelled')
                                <span class="bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300 text-[10px] font-bold px-2 py-1 rounded-lg">🚫 لغو شده</span>
                            @else
                                <span class="bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400 text-[10px] font-bold px-2 py-1 rounded-lg">⚠️ ناموفق</span>
                            @endif
                            
                            <span class="text-[10px] text-gray-400">ثبت‌کننده: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $call->user ? $call->user->name : '-' }}</span></span>
                        </div>

                        <!-- Actions (Edit/Delete) -->
                        <div class="flex items-center gap-2">
                            <button wire:click="editCall({{ $call->id }})" class="text-[11px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-extrabold flex items-center gap-0.5">
                                ✏️ ویرایش
                            </button>
                            <button onclick="confirm('آیا از حذف این تماس مطمئن هستید؟') || event.stopImmediatePropagation()" wire:click="deleteCall({{ $call->id }})" class="text-[11px] text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 font-extrabold flex items-center gap-0.5">
                                🗑️ حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-8 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 mr-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="text-xs font-semibold">تماسی یافت نشد.</span>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($calls->count())
        <div class="pt-4" dir="rtl">
            {{ $calls->links() }}
        </div>
    @endif

    <!-- Create/Edit Call Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="$wire.cancelEditing()"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                                    {{ $editingCallId ? '✏️ ویرایش اطلاعات تماس' : '📞 ثبت تماس جدید' }}
                                </h3>
                                <button x-on:click="$wire.cancelEditing()" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveCall" class="space-y-5">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ تماس <span class="text-rose-500">*</span></label>
                                        <input type="date" wire:model="call_date" class="{{ $inputClass }}">
                                        @error('call_date') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">ساعت تماس</label>
                                        <input type="time" wire:model="call_time" class="{{ $inputClass }}">
                                        @error('call_time') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">جهت تماس <span class="text-rose-500">*</span></label>
                                        <select wire:model="direction" class="{{ $inputClass }}">
                                            <option value="outbound">↗ خروجی</option>
                                            <option value="inbound">↙ ورودی</option>
                                        </select>
                                        @error('direction') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">وضعیت تماس <span class="text-rose-500">*</span></label>
                                        <select wire:model="status" class="{{ $inputClass }}">
                                            <option value="planned">📅 برنامه‌ریزی شده</option>
                                            <option value="answered">✅ پاسخ داده شده</option>
                                            <option value="no_answer">❌ بدون پاسخ</option>
                                            <option value="busy">⏳ اشغال</option>
                                            <option value="cancelled">🚫 لغو شده</option>
                                            <option value="failed">⚠️ ناموفق</option>
                                        </select>
                                        @error('status') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">شماره تماس (اختیاری)</label>
                                        <input type="text" wire:model="contact_phone" class="{{ $inputClass }}" placeholder="0912...">
                                        @error('contact_phone') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">کمپین مرتبط</label>
                                        <select wire:model="campaign_id" class="{{ $inputClass }}">
                                            <option value="">-- انتخاب کمپین --</option>
                                            @if(isset($campaigns))
                                                @foreach($campaigns as $camp)
                                                    <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('campaign_id') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">مدت مکالمه (ثانیه)</label>
                                        <input type="number" wire:model="duration_seconds" class="{{ $inputClass }}" placeholder="120">
                                        @error('duration_seconds') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">علت/موضوع</label>
                                        <input type="text" wire:model="reason" class="{{ $inputClass }}" placeholder="مثال: پیگیری فاکتور">
                                        @error('reason') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5 bg-indigo-50/50 dark:bg-indigo-900/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                                    <div>
                                        <label class="{{ $labelClass }}">اقدام بعدی (ایجاد تسک خودکار)</label>
                                        <input type="text" wire:model="next_action" class="{{ $inputClass }}" placeholder="مثال: ارسال نمونه محصول">
                                        @error('next_action') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ اقدام بعدی</label>
                                        <input type="date" wire:model="next_action_date" class="{{ $inputClass }}">
                                        @error('next_action_date') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">خلاصه و نتیجه مکالمه</label>
                                    <textarea wire:model="result" rows="2" class="{{ $inputClass }} resize-none" placeholder="مشتری موافقت کرد که..."></textarea>
                                    @error('result') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">یادداشت‌های داخلی اپراتور</label>
                                    <textarea wire:model="notes" rows="2" class="{{ $inputClass }} resize-none" placeholder="نکات محرمانه یا داخلی..."></textarea>
                                    @error('notes') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                    <button type="button" x-on:click="$wire.cancelEditing()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">
                                        {{ $editingCallId ? 'ویرایش تماس' : 'ثبت تماس' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
