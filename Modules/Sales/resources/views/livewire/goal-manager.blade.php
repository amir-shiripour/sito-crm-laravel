@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-4 text-right" dir="rtl">
    @if($isManager)
        <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-150 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">مدیریت اهداف کارشناس:</span>
                <select wire:model.live="selectedUserId" class="rounded-xl border border-gray-200 bg-white dark:bg-gray-950 px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:text-gray-100 transition-colors w-52">
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->getRoleNames()->first() ?? 'کاربر' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="text-[10px] text-gray-400">
                در این بخش می‌توانید برای کارشناس انتخابی، هدف تماس یا پرونده جدید تعریف کنید.
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
        <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ $isManager ? 'مدیریت اهداف کارشناسان' : 'اهداف عملکردی من' }}
        </h3>
        <button wire:click="openCreateModal" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-bold shadow-sm transition-colors">
            ＋ تعریف هدف
        </button>
    </div>

    <!-- Active Goals List -->
    <div class="space-y-2 max-h-[300px] overflow-y-auto">
        @forelse($goals as $goal)
            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 p-3 rounded-xl flex items-center justify-between gap-3 transition-colors {{ $goal->is_active ? 'border-r-4 border-r-indigo-500' : 'opacity-60' }}">
                <div>
                    <h4 class="text-xs font-bold text-gray-950 dark:text-white">
                        {{ $goalTypes[$goal->goal_type] ?? $goal->goal_type }}
                    </h4>
                    <p class="text-[10px] text-gray-500 mt-1">
                        مقدار هدف: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $goal->target_value }}</span>
                        ({{ $goal->period === 'daily' ? 'روزانه' : ($goal->period === 'weekly' ? 'هفتگی' : 'ماهانه') }})
                    </p>
                    @if($goal->active_from || $goal->active_until)
                        <span class="text-[9px] text-gray-400 block mt-0.5">
                            بازه اعتبار: 
                            {{ $goal->active_from ? \Morilog\Jalali\Jalalian::fromDateTime($goal->active_from)->format('Y/m/d') : 'نامحدود' }} 
                            تا 
                            {{ $goal->active_until ? \Morilog\Jalali\Jalalian::fromDateTime($goal->active_until)->format('Y/m/d') : 'نامحدود' }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    <!-- Toggle active -->
                    <button wire:click="toggleGoalActive({{ $goal->id }})" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg flex items-center justify-center" title="تغییر وضعیت فعال بودن">
                        <span class="w-2.5 h-2.5 rounded-full {{ $goal->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                    </button>
                    <!-- Edit -->
                    <button wire:click="editGoal({{ $goal->id }})" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 rounded-lg transition-colors" title="ویرایش">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <!-- Delete -->
                    <button onclick="confirm('آیا از حذف این هدف مطمئن هستید؟') || event.stopImmediatePropagation()" wire:click="deleteGoal({{ $goal->id }})" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 text-rose-600 dark:text-rose-400 rounded-lg transition-colors" title="حذف">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <p class="text-[11px] text-gray-400 text-center py-4">هدفی در حال حاضر تعریف نشده است.</p>
        @endforelse
    </div>

    <!-- Create/Edit Goal Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white inline-flex items-center gap-2" id="modal-title">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    {{ $editingGoalId ? 'ویرایش هدف عملکردی' : 'تعریف هدف عملکردی جدید' }}
                                </h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveGoal" class="space-y-5">
                                <div>
                                    <label class="{{ $labelClass }}">نوع هدف عملکردی <span class="text-rose-500">*</span></label>
                                    <select wire:model="goal_type" class="{{ $inputClass }}">
                                        @foreach($goalTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('goal_type') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">مقدار هدف (عدد) <span class="text-rose-500">*</span></label>
                                        <input type="number" wire:model="target_value" class="{{ $inputClass }}" min="1">
                                        @error('target_value') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">دوره هدف <span class="text-rose-500">*</span></label>
                                        <select wire:model="period" class="{{ $inputClass }}">
                                            <option value="daily">روزانه</option>
                                            <option value="weekly">هفتگی</option>
                                            <option value="monthly">ماهانه</option>
                                        </select>
                                        @error('period') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">فعال از تاریخ</label>
                                        <input type="date" wire:model="active_from" class="{{ $inputClass }}">
                                        @error('active_from') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">فعال تا تاریخ</label>
                                        <input type="date" wire:model="active_until" class="{{ $inputClass }}">
                                        @error('active_until') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">توضیح یا یادداشت هدف</label>
                                    <input type="text" wire:model="note" class="{{ $inputClass }}" placeholder="مثال: هدف افزایش تماس به مناسبت جشنواره پاییز">
                                    @error('note') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                    <button type="button" x-on:click="show = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">
                                        {{ $editingGoalId ? 'ویرایش هدف' : 'ذخیره هدف' }}
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
