@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-6" dir="rtl">
    <!-- Top Filters & View Selector -->
    <div class="bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-3 flex-grow w-full">
                <!-- Search -->
                <div class="w-full sm:w-1/3">
                    <input type="text" wire:model.live="search" class="{{ $inputClass }}" placeholder="جستجو در تسک‌ها...">
                </div>
                <!-- Status Filter -->
                <div class="w-full sm:w-1/3">
                    <select wire:model.live="filterStatus" class="{{ $inputClass }}">
                        <option value="active">تسک‌های فعال (Todo / In Progress)</option>
                        <option value="todo">در صف انجام (Todo)</option>
                        <option value="in_progress">در حال انجام</option>
                        <option value="done">انجام شده</option>
                        <option value="cancelled">لغو شده</option>
                        <option value="all">همه تسک‌ها</option>
                    </select>
                </div>
                <!-- View toggle -->
                <div class="w-full sm:w-auto">
                    <button wire:click="toggleViewMode" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors w-full">
                        @if($viewMode === 'list')
                            🗂 نمای کانبان
                        @else
                            📋 نمای لیست
                        @endif
                    </button>
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    تسک جدید
                </button>
            </div>
        </div>

        <!-- Secondary Filters Grid -->
        <div class="grid grid-cols-3 gap-3 border-t border-gray-200 dark:border-gray-700 pt-3 text-xs">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">نوع تسک</label>
                <select wire:model.live="filterType" class="w-full rounded-xl border border-gray-200 bg-white px-2 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="all">همه انواع</option>
                    <option value="followup">پیگیری (Follow-up)</option>
                    <option value="general">وظیفه عمومی</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">اولویت</label>
                <select wire:model.live="filterPriority" class="w-full rounded-xl border border-gray-200 bg-white px-2 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="">همه اولویت‌ها</option>
                    <option value="LOW">کم</option>
                    <option value="MEDIUM">معمولی</option>
                    <option value="HIGH">زیاد</option>
                    <option value="CRITICAL">بحرانی</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">سررسید</label>
                <select wire:model.live="filterDate" class="w-full rounded-xl border border-gray-200 bg-white px-2 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="all">همه تاریخ‌ها</option>
                    <option value="today">امروز</option>
                    <option value="week">این هفته</option>
                </select>
            </div>
        </div>
    </div>

    @if($isKanban)
        <!-- Kanban Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- TO DO column -->
            <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl p-4 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">⏳ در صف انجام</h3>
                    <span class="text-[10px] font-bold bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ $kanbanTasks['todo']->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($kanbanTasks['todo'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <p class="text-[11px] text-gray-400 text-center py-4">تسک در صف انجامی وجود ندارد.</p>
                    @endforelse
                </div>
            </div>

            <!-- IN PROGRESS column -->
            <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl p-4 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">⚡ در حال انجام</h3>
                    <span class="text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ $kanbanTasks['in_progress']->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($kanbanTasks['in_progress'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <p class="text-[11px] text-gray-400 text-center py-4">تسکی در حال انجام نیست.</p>
                    @endforelse
                </div>
            </div>

            <!-- DONE column -->
            <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl p-4 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">✅ انجام شده</h3>
                    <span class="text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full">{{ $kanbanTasks['done']->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($kanbanTasks['done'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <p class="text-[11px] text-gray-400 text-center py-4">تسکی انجام نشده است.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <!-- List Layout -->
        <div class="space-y-3">
            @forelse($tasks as $task)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left block: title/desc and priority -->
                    <div class="flex items-start gap-3">
                        <!-- Priority bar indicator -->
                        <div class="w-1.5 h-12 rounded-full {{ $task->priority === 'CRITICAL' ? 'bg-red-600' : ($task->priority === 'HIGH' ? 'bg-orange-500' : ($task->priority === 'MEDIUM' ? 'bg-indigo-500' : 'bg-gray-400')) }}"></div>
                        
                        <div>
                            <div class="flex items-center flex-wrap gap-2">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white {{ $task->status === 'DONE' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">{{ $task->title }}</h4>
                                <span class="text-[9px] bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400 px-1.5 py-0.5 rounded-md font-bold">
                                    {{ $task->task_type === 'FOLLOW_UP' ? 'پیگیری' : 'وظیفه عمومی' }}
                                </span>
                                @if($task->due_at && $task->due_at->isPast() && $task->status !== 'DONE')
                                    <span class="text-[9px] bg-red-50 text-red-600 px-1.5 py-0.5 rounded-md font-extrabold animate-pulse">معوق</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">{{ $task->description ?: 'بدون توضیح' }}</p>
                            @if($task->due_at)
                                <span class="text-[9px] text-gray-400 block mt-1" dir="ltr">⏱ موعد: {{ \Morilog\Jalali\Jalalian::fromDateTime($task->due_at)->format('Y/m/d H:i') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right block: assignee + status operations -->
                    <div class="flex items-center gap-3 justify-between md:justify-end border-t md:border-t-0 border-gray-100 dark:border-gray-700/50 pt-2.5 md:pt-0">
                        <div class="text-[10px] text-gray-400">
                            <span>مسئول: <span class="font-bold text-gray-600 dark:text-gray-300">{{ $task->assignee ? $task->assignee->name : 'ناشناس' }}</span></span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1.5">
                            @if($task->status !== 'DONE' && $task->status !== 'CANCELED')
                                <button wire:click="completeTask({{ $task->id }})" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-bold shadow-sm transition-colors">✓ انجام شد</button>
                                <button wire:click="cancelTask({{ $task->id }})" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-[10px] font-bold transition-colors">لغو</button>
                            @endif
                            @if($task->assignee_id !== auth()->id() && $task->status !== 'DONE')
                                <button wire:click="assignToMe({{ $task->id }})" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold transition-colors">من انجام می‌دهم</button>
                            @endif
                            
                            <button wire:click="editTask({{ $task->id }})" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-indigo-600 rounded-lg transition-colors" title="ویرایش">
                                ✏️
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-10 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span class="text-xs font-semibold">تسک یا پیگیری یافت نشد.</span>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($tasks->count())
            <div class="pt-4">
                {{ $tasks->links() }}
            </div>
        @endif
    @endif

    <!-- Create/Edit Task Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                                    {{ $editingTaskId ? '✏️ ویرایش تسک' : '✅ ثبت تسک جدید' }}
                                </h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveTask" class="space-y-5">
                                <div>
                                    <label class="{{ $labelClass }}">عنوان وظیفه / پیگیری <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="title" class="{{ $inputClass }}" placeholder="مثال: تماس بابت تمدید قرارداد">
                                    @error('title') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">نوع تسک <span class="text-rose-500">*</span></label>
                                        <select wire:model="taskType" class="{{ $inputClass }}">
                                            <option value="FOLLOW_UP">📞 پیگیری (Follow-up)</option>
                                            <option value="GENERAL">📝 وظیفه عمومی</option>
                                        </select>
                                        @error('taskType') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">اولویت تسک <span class="text-rose-500">*</span></label>
                                        <select wire:model="taskPriority" class="{{ $inputClass }}">
                                            <option value="LOW">🟢 کم</option>
                                            <option value="MEDIUM">🔵 معمولی</option>
                                            <option value="HIGH">🟠 زیاد</option>
                                            <option value="CRITICAL">🔴 بحرانی</option>
                                        </select>
                                        @error('taskPriority') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">موعد سررسید <span class="text-rose-500">*</span></label>
                                        <input type="datetime-local" wire:model="due_at" class="{{ $inputClass }}">
                                        @error('due_at') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">تخصیص به</label>
                                        <select wire:model="assignee_id" class="{{ $inputClass }}">
                                            <option value="">-- بدون تخصیص --</option>
                                            @if(isset($users))
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('assignee_id') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">توضیحات و جزئیات</label>
                                    <textarea wire:model="description" rows="3" class="{{ $inputClass }} resize-none" placeholder="نکاتی درباره چگونگی انجام وظیفه..."></textarea>
                                    @error('description') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                    <button type="button" x-on:click="show = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">
                                        {{ $editingTaskId ? 'ویرایش تسک' : 'ذخیره تسک' }}
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
