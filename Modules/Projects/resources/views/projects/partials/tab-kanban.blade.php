@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $colCount = $taskStatuses->count();
    $gridClass = match(true) {
        $colCount === 1 => 'grid grid-cols-1 max-w-xl mx-auto',
        $colCount === 2 => 'grid grid-cols-1 md:grid-cols-2',
        $colCount === 3 => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        $colCount === 4 => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-' . min($colCount, 5),
    };
@endphp

<div class="space-y-6" x-data="kanbanBoardManager()">

    {{-- Top Action Bar --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800/80 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                برد کانبان کارها (Kanban Board)
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">کارت‌ها را با کشیدن و رها کردن (Drag & Drop) بین ستون‌های وضعیت
                جابجا کنید.</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Member Filter --}}
            <div
                class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900/60 px-3 py-1.5 rounded-2xl border border-gray-200 dark:border-gray-700">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <select x-model="memberFilter"
                        class="bg-transparent text-xs font-bold text-gray-700 dark:text-gray-200 border-none p-0 focus:ring-0 cursor-pointer">
                    <option value="all">همه اعضای پروژه</option>
                    @foreach($project->members as $m)
                        <option value="{{ $m->user_id }}">{{ $m->user?->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Kanban Responsive Grid Layout (No horizontal page break) --}}
    <div class="{{ $gridClass }} gap-5 items-start" id="kanban-columns-container">
        @foreach($taskStatuses as $status)
            @php
                $statusTasks = $project->tasks->where('status_id', $status->id);
            @endphp
            <div
                class="w-full bg-gray-50/80 dark:bg-gray-800/40 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 flex flex-col shadow-xs transition-all min-w-0"
                id="kanban-col-{{ $status->id }}">

                {{-- Column Header --}}
                <div class="p-4 border-b border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs"
                              style="background-color: {{ $status->color ?? '#6366f1' }}"></span>
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $status->name }}</h4>
                        @if($status->isDelayed())
                            <span
                                class="inline-flex items-center gap-1 text-[10px] bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 font-bold px-1.5 py-0.5 rounded-md border border-amber-200/60 dark:border-amber-800/40"
                                title="مدیریت خودکار توسط کرون‌جاب بر اساس سررسید">
                                <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                                 stroke-width="2"
                                                                 d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                خودکار
                            </span>
                        @endif
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold shrink-0 mr-2"
                          style="background-color: {{ $status->color ?? '#6366f1' }}18; color: {{ $status->color ?? '#6366f1' }}"
                          id="kanban-col-count-{{ $status->id }}">
                        {{ $faNum($statusTasks->count()) }} کار
                    </span>
                </div>

                {{-- Column Drop Zone / Task Cards --}}
                <div class="p-3 space-y-3 overflow-y-auto max-h-[680px] min-h-[160px] transition-all rounded-b-3xl"
                     id="kanban-drop-zone-{{ $status->id }}"
                     data-status-id="{{ $status->id }}"
                     data-status-name="{{ $status->name }}"
                     data-is-queued="{{ $status->isQueued() ? '1' : '0' }}"
                     data-is-in-progress="{{ $status->isInProgress() ? '1' : '0' }}"
                     data-is-completed="{{ $status->isCompleted() ? '1' : '0' }}"
                     data-is-canceled="{{ $status->isCanceled() ? '1' : '0' }}"
                     data-is-delayed="{{ $status->isDelayed() ? '1' : '0' }}"
                     ondragover="handleKanbanDragOver(event, this)"
                     ondragleave="handleKanbanDragLeave(event, this)"
                     ondrop="handleKanbanDrop(event, {{ $project->id }}, {{ $status->id }}, this)">

                    @forelse($statusTasks as $task)
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 shadow-xs hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800/60 transition-all {{ $task->isCanceled() ? 'cursor-not-allowed opacity-75' : 'cursor-grab active:cursor-grabbing' }} space-y-3 group"
                            id="kanban-task-{{ $task->id }}"
                            data-task-id="{{ $task->id }}"
                            data-status-id="{{ $status->id }}"
                            data-task-title="{{ addslashes($task->title) }}"
                            data-is-canceled="{{ $task->isCanceled() ? '1' : '0' }}"
                            data-is-delayed="{{ $task->isDelayed() ? '1' : '0' }}"
                            data-checklist='@json($task->checklistItems->map(fn($i) => ['id' => $i->id, 'title' => $i->title, 'is_done' => (bool)$i->is_done]))'
                            draggable="{{ $task->isCanceled() ? 'false' : 'true' }}"
                            ondragstart="handleKanbanDragStart(event, {{ $task->id }}, {{ $status->id }})"
                            x-show="memberFilter === 'all' || memberFilter == '{{ $task->assigned_to }}'">

                            {{-- Task Title --}}
                            <div class="flex items-start justify-between gap-2">
                                <h5 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors {{ $task->status?->isCompleted() ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                    {{ $task->title }}
                                </h5>
                            </div>

                            @if($task->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $task->description }}
                                </p>
                            @endif

                            {{-- Task Meta: Checklists & Time --}}
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span id="kanban-checklist-badge-{{ $task->id }}"
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold
                                    {{ $task->checklistProgress() === 100 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }} {{ $task->activeChecklistCount() > 0 ? '' : 'hidden' }}">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span id="kanban-checklist-badge-text-{{ $task->id }}">{{ $faNum($task->completedChecklistCount()) }} از {{ $faNum($task->activeChecklistCount()) }}</span>
                                </span>

                                @if($task->totalLoggedMinutes() > 0)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $task->formattedTotalTime() }}
                                    </span>
                                @endif
                            </div>

                            {{-- Footer: Assignee & Due Date --}}
                            <div
                                class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-700/50 text-xs gap-2">
                                {{-- Assignee --}}
                                @if($task->assignee)
                                    <span
                                        class="inline-flex items-center gap-1.5 font-bold text-gray-700 dark:text-gray-200 min-w-0">
                                        <span
                                            class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[10px] flex items-center justify-center font-black shrink-0">
                                            {{ mb_substr($task->assignee->name, 0, 1) }}
                                        </span>
                                        <span class="truncate">{{ $task->assignee->name }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 text-[11px]">بدون مدیر گروه</span>
                                @endif

                                {{-- Due Date --}}
                                @if($task->due_date)
                                    <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-lg shrink-0 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700" title="تاریخ سررسید">
                                        <svg class="w-3.5 h-3.5 text-gray-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ function_exists('jdate') ? $faNum(jdate($task->due_date)->format('Y/m/d')) : $task->due_date->format('Y/m/d') }}
                                    </span>
                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-400 text-xs empty-placeholder">
                            کاری در این ستون نیست
                        </div>
                    @endforelse

                </div>
            </div>
        @endforeach
    </div>

    {{-- Quick In-Progress Subtask Selection Modal --}}
    <div x-data="kanbanInProgressModalManager()"
         x-show="open"
         class="relative z-[9999]"
         role="dialog"
         aria-modal="true"
         style="display: none;"
         @keydown.escape.window="cancel()">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cancel()"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        {{-- Modal Wrapper --}}
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20" @click.self="cancel()">
            <div class="flex min-h-full items-end justify-center text-center sm:items-center" @click.self="cancel()">
                <div x-show="open"
                     @click.stop
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">

                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>تعیین کارهای انجام‌شده</span>
                            </h3>
                            <p class="text-xs text-gray-400 mt-1" x-text="'گروه: ' + taskTitle"></p>
                        </div>
                        <button type="button" @click="cancel()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-1">✕
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        برای به‌روزرسانی وضعیت این گروه، وضعیت کارهای زیر را تعیین نمایید:
                    </p>

                    {{-- Checklist Items List --}}
                    <div class="space-y-2 max-h-60 overflow-y-auto p-1">
                        <template x-for="item in items" :key="item.id">
                            <label
                                class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/70 dark:bg-gray-900/40 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 cursor-pointer transition-all">
                                <input type="checkbox"
                                       :value="item.id"
                                       :checked="selectedIds.includes(item.id)"
                                       @change="toggleItem(item.id, $event.target.checked)"
                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate flex-1"
                                      :class="{'line-through text-gray-400': selectedIds.includes(item.id)}"
                                      x-text="item.title"></span>
                            </label>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="cancel()"
                                class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 transition-all">
                            انصراف
                        </button>
                        <button type="button" @click="submit()" :disabled="submitting || selectedIds.length === 0"
                                class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5">
                            <svg x-show="submitting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>ثبت و انتقال وضعیت</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        function kanbanBoardManager() {
            return {
                memberFilter: 'all',
            };
        }

        function kanbanInProgressModalManager() {
            return {
                open: false,
                submitting: false,
                projectId: null,
                taskId: null,
                taskTitle: '',
                targetStatusId: null,
                items: [],
                selectedIds: [],
                zone: null,
                taskEl: null,
                fromZone: null,

                openModal(payload) {
                    this.projectId = payload.projectId;
                    this.taskId = payload.taskId;
                    this.taskTitle = payload.taskTitle;
                    this.targetStatusId = payload.targetStatusId;
                    this.items = payload.items || [];
                    this.selectedIds = this.items.filter(i => i.is_done).map(i => i.id);
                    if (this.selectedIds.length === 0 && this.items.length > 0) {
                        this.selectedIds.push(this.items[0].id);
                    }
                    this.zone = payload.zone;
                    this.taskEl = payload.taskEl;
                    this.fromZone = payload.fromZone;
                    this.submitting = false;
                    this.open = true;
                },

                toggleItem(id, checked) {
                    if (checked) {
                        if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
                    } else {
                        this.selectedIds = this.selectedIds.filter(i => i !== id);
                    }
                },

                cancel() {
                    this.open = false;
                },

                submit() {
                    if (this.selectedIds.length === 0) {
                        alert('لطفاً حداقل یک کار را انتخاب کنید.');
                        return;
                    }
                    this.submitting = true;
                    sendKanbanStatusUpdate(
                        this.projectId,
                        this.taskId,
                        this.targetStatusId,
                        this.selectedIds,
                        this.zone,
                        this.taskEl,
                        this.fromZone
                    ).then(() => {
                        this.open = false;
                    }).finally(() => {
                        this.submitting = false;
                    });
                }
            };
        }

        window.openKanbanInProgressModal = function (payload) {
            const el = document.querySelector('[x-data="kanbanInProgressModalManager()"]');
            if (el && window.Alpine) {
                window.Alpine.$data(el).openModal(payload);
            }
        };

        let draggedTaskId = null;
        let originalStatusId = null;

        function handleKanbanDragStart(e, taskId, statusId) {
            const taskEl = document.getElementById('kanban-task-' + taskId);
            if (taskEl && taskEl.getAttribute('data-is-canceled') === '1') {
                e.preventDefault();
                alert('این گروه لغو شده است و امکان جابجایی آن وجود ندارد.');
                return false;
            }
            draggedTaskId = taskId;
            originalStatusId = taskEl ? (parseInt(taskEl.getAttribute('data-status-id')) || statusId) : statusId;
            e.dataTransfer.setData('text/plain', taskId);
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleKanbanDragOver(e, zone) {
            if (zone.getAttribute('data-is-delayed') === '1') {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'none';
                return;
            }
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('bg-indigo-50/60', 'dark:bg-indigo-950/30', 'ring-2', 'ring-indigo-400/50');
        }

        function handleKanbanDragLeave(e, zone) {
            zone.classList.remove('bg-indigo-50/60', 'dark:bg-indigo-950/30', 'ring-2', 'ring-indigo-400/50');
        }

        function handleKanbanDrop(e, projectId, targetStatusId, zone) {
            e.preventDefault();
            zone.classList.remove('bg-indigo-50/60', 'dark:bg-indigo-950/30', 'ring-2', 'ring-indigo-400/50');

            if (!draggedTaskId || originalStatusId === targetStatusId) {
                draggedTaskId = null;
                originalStatusId = null;
                return;
            }

            const isDelayed = zone.getAttribute('data-is-delayed') === '1';
            if (isDelayed) {
                alert(' وضعیت تعویق به صورت خودکار بر اساس تاریخ سررسید و توسط کرون‌جاب سیستم مدیریت می‌شود و امکان انتقال دستی به آن وجود ندارد.');
                draggedTaskId = null;
                originalStatusId = null;
                return;
            }

            const taskEl = document.getElementById('kanban-task-' + draggedTaskId);
            if (!taskEl) return;

            if (taskEl.getAttribute('data-is-canceled') === '1') {
                alert('این گروه لغو شده است و تغییر وضعیت آن امکان‌پذیر نیست.');
                draggedTaskId = null;
                originalStatusId = null;
                return;
            }

            const fromZone = taskEl.parentElement;
            const isFromDelayed = (fromZone && fromZone.getAttribute('data-is-delayed') === '1') || taskEl.getAttribute('data-is-delayed') === '1';

            const taskIdToUpdate = draggedTaskId;
            const taskTitle = taskEl.getAttribute('data-task-title') || 'گروه';
            const isQueued = zone.getAttribute('data-is-queued') === '1';
            const isInProgress = zone.getAttribute('data-is-in-progress') === '1';
            const isCompleted = zone.getAttribute('data-is-completed') === '1';
            const isCanceled = zone.getAttribute('data-is-canceled') === '1';

            // If task is currently delayed, it can ONLY be moved to Completed or Canceled
            if (isFromDelayed && !isCompleted && !isCanceled) {
                alert(' این گروه در وضعیت تعویق قرار دارد و فقط امکان تغییر وضعیت آن به «تکمیل شده» یا «لغو شده» وجود دارد.');
                draggedTaskId = null;
                originalStatusId = null;
                return;
            }

            let checklistItems = [];
            try {
                checklistItems = JSON.parse(taskEl.getAttribute('data-checklist') || '[]');
            } catch (e) {
            }

            // Scenario 1: Dropped on "Completed"
            if (isCompleted) {
                if (!confirm(`آیا از تکمیل تمام کارهای گروه «${taskTitle}» اطمینان دارید؟`)) {
                    draggedTaskId = null;
                    originalStatusId = null;
                    return;
                }
                sendKanbanStatusUpdate(projectId, taskIdToUpdate, targetStatusId, null, zone, taskEl, fromZone);
                return;
            }

            // Scenario 2: Dropped on "Queued"
            if (isQueued) {
                if (!confirm(`آیا مایلید تمام کارهای گروه «${taskTitle}» ریست شده و گروه به وضعیت در صف بازگردد؟`)) {
                    draggedTaskId = null;
                    originalStatusId = null;
                    return;
                }
                sendKanbanStatusUpdate(projectId, taskIdToUpdate, targetStatusId, null, zone, taskEl, fromZone);
                return;
            }

            // Scenario 3: Dropped on "Canceled"
            if (isCanceled) {
                if (!confirm(`آیا از لغو گروه «${taskTitle}» اطمینان دارید؟\nاین عملیات غیرقابل بازگشت است و کارهای این گروه قفل خواهند شد.`)) {
                    draggedTaskId = null;
                    originalStatusId = null;
                    return;
                }
                sendKanbanStatusUpdate(projectId, taskIdToUpdate, targetStatusId, null, zone, taskEl, fromZone);
                return;
            }

            // Scenario 4: Dropped on "In Progress"
            if (isInProgress) {
                if (checklistItems.length === 0) {
                    sendKanbanStatusUpdate(projectId, taskIdToUpdate, targetStatusId, null, zone, taskEl, fromZone);
                    return;
                }
                // Open Subtask Selection Modal
                window.openKanbanInProgressModal({
                    projectId,
                    taskId: taskIdToUpdate,
                    taskTitle,
                    targetStatusId,
                    items: checklistItems,
                    zone,
                    taskEl,
                    fromZone
                });
                draggedTaskId = null;
                originalStatusId = null;
                return;
            }

            // Default / Other statuses
            sendKanbanStatusUpdate(projectId, taskIdToUpdate, targetStatusId, null, zone, taskEl, fromZone);
        }

        function sendKanbanStatusUpdate(projectId, taskId, targetStatusId, checklistDoneIds = null, zone, taskEl, fromZone) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const payload = {status_id: targetStatusId};
            if (checklistDoneIds !== null) {
                payload.checklist_done_ids = checklistDoneIds;
            }

            return fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'انتقال وضعیت مجاز نیست.');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const actualStatusId = data.task?.status_id || targetStatusId;
                        const targetDropZone = document.getElementById('kanban-drop-zone-' + actualStatusId) || zone;

                        // 1. Remove empty placeholder from target zone if present
                        if (targetDropZone) {
                            const placeholder = targetDropZone.querySelector('.empty-placeholder');
                            if (placeholder) placeholder.remove();
                            targetDropZone.appendChild(taskEl);
                        }

                        // 2. Update task element dataset & ondragstart attribute
                        taskEl.setAttribute('data-status-id', actualStatusId);
                        taskEl.setAttribute('ondragstart', `handleKanbanDragStart(event, ${taskId}, ${actualStatusId})`);

                        const isDelayed = data.task?.status?.is_delayed || (data.task?.status?.name === 'تعویق');
                        taskEl.setAttribute('data-is-delayed', isDelayed ? '1' : '0');

                        const isCanceled = data.task?.status?.is_canceled || (data.task?.status?.name === 'لغو شده');
                        if (isCanceled) {
                            taskEl.setAttribute('data-is-canceled', '1');
                            taskEl.setAttribute('draggable', 'false');
                            taskEl.classList.add('cursor-not-allowed', 'opacity-75');
                            taskEl.classList.remove('cursor-grab', 'active:cursor-grabbing');
                        }

                        if (data.task?.checklist_items) {
                            taskEl.setAttribute('data-checklist', JSON.stringify(data.task.checklist_items.map(i => ({
                                id: i.id,
                                title: i.title,
                                is_done: !!i.is_done
                            }))));
                        }

                        // 3. Update title line-through if completed
                        const titleEl = taskEl.querySelector('h5');
                        const isCompleted = data.task?.completed_at || (data.task?.status && (data.task.status.is_completed || data.task.status.name === 'تکمیل شده'));
                        if (titleEl) {
                            if (isCompleted) {
                                titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                            } else {
                                titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                            }
                        }

                        // 4. Update Kanban checklist badge
                        if (data.checklist_done !== undefined && data.checklist_total !== undefined) {
                            const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                            const doneFa = String(data.checklist_done).replace(/[0-9]/g, d => faDigits[d]);
                            const totalFa = String(data.checklist_total).replace(/[0-9]/g, d => faDigits[d]);
                            const text = `${doneFa} از ${totalFa}`;

                            const kanbanBadgeText = document.getElementById('kanban-checklist-badge-text-' + taskId);
                            if (kanbanBadgeText) kanbanBadgeText.textContent = text;
                            const kanbanBadge = document.getElementById('kanban-checklist-badge-' + taskId);
                            if (kanbanBadge) {
                                if (data.checklist_done === data.checklist_total && data.checklist_total > 0) {
                                    kanbanBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40';
                                } else {
                                    kanbanBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
                                }
                            }
                        }

                        // 5. If fromZone has no remaining cards, re-add empty placeholder
                        if (fromZone && fromZone !== targetDropZone) {
                            const remainingTasks = fromZone.querySelectorAll('[id^="kanban-task-"]');
                            if (remainingTasks.length === 0 && !fromZone.querySelector('.empty-placeholder')) {
                                const emptyDiv = document.createElement('div');
                                emptyDiv.className = 'py-10 text-center text-gray-400 text-xs empty-placeholder';
                                emptyDiv.textContent = 'کاری در این ستون نیست';
                                fromZone.appendChild(emptyDiv);
                            }
                        }

                        // 6. Update Kanban column counter badges
                        updateKanbanCounters();

                        // 7. Sync Task Tab Card Header & Checkboxes
                        if (data.task?.status) {
                            if (window.updateTaskStatusBadgeAndIcon) {
                                window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
                            } else {
                                const color = data.task.status.color || '#6366f1';
                                const statusBadge = document.getElementById('task-status-badge-' + taskId);
                                if (statusBadge) {
                                    statusBadge.style.backgroundColor = color + '18';
                                    statusBadge.style.color = color;
                                    statusBadge.style.borderColor = color + '33';
                                    const dot = statusBadge.querySelector('span:first-child');
                                    if (dot) dot.style.backgroundColor = color;
                                    const nameEl = document.getElementById('task-status-name-' + taskId);
                                    if (nameEl) nameEl.textContent = data.task.status.name;
                                }
                                const iconBox = document.getElementById('task-icon-box-' + taskId);
                                if (iconBox) {
                                    iconBox.style.background = color + '18';
                                    iconBox.style.borderColor = color + '33';
                                }
                                const iconDot = document.getElementById('task-icon-dot-' + taskId);
                                if (iconDot) {
                                    iconDot.style.background = color;
                                }
                            }
                        }

                        const taskCardTitle = document.getElementById('task-card-title-' + taskId);
                        if (taskCardTitle) {
                            if (isCompleted) {
                                taskCardTitle.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                            } else {
                                taskCardTitle.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                            }
                        }

                        if (typeof updateChecklistProgress === 'function') {
                            updateChecklistProgress(taskId, data.task_progress, data.checklist_done, data.checklist_total);
                        }

                        // Sync checkboxes inside task card
                        const items = data.task?.checklist_items || data.task?.checklistItems || [];
                        if (items.length > 0) {
                            items.forEach(ci => {
                                const cb = document.getElementById('checklist-checkbox-' + ci.id);
                                if (cb) {
                                    cb.checked = !!ci.is_done;
                                }
                                const titleSpan = document.getElementById('checklist-item-title-' + ci.id);
                                if (titleSpan) {
                                    if (ci.is_done) {
                                        titleSpan.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                                    } else {
                                        titleSpan.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                                    }
                                }
                            });
                        }
                        const taskCard = document.getElementById('task-card-' + taskId);
                        const isQueued = data.task?.status?.is_queued || (data.task?.status?.name && (data.task.status.name.includes('صف') || data.task.status.name.toLowerCase().includes('queue')));
                        if (isQueued && taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.checked = false;
                            });
                            taskCard.querySelectorAll('[id^="checklist-item-title-"]').forEach(span => {
                                span.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                            });
                        } else if (isCompleted && taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.checked = true;
                            });
                            taskCard.querySelectorAll('[id^="checklist-item-title-"]').forEach(span => {
                                span.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                            });
                        }

                    // If canceled, lock everything in task card
                    if (isCanceled) {
                        const cancelBtn = document.getElementById('task-cancel-btn-' + taskId);
                        if (cancelBtn) cancelBtn.remove();
                        const groupEditBtn = document.getElementById('task-edit-btn-' + taskId);
                        if (groupEditBtn) groupEditBtn.remove();
                        const groupDeleteForm = document.getElementById('task-delete-form-' + taskId);
                        if (groupDeleteForm) groupDeleteForm.remove();
                        document.querySelectorAll('.checklist-action-btn-' + taskId).forEach(btn => btn.remove());
                        const taskCard = document.getElementById('task-card-' + taskId);
                        if (taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.disabled = true;
                                cb.classList.add('opacity-50', 'cursor-not-allowed');
                            });
                            const quickAdd = document.getElementById('quick-add-container-' + taskId);
                            if (quickAdd) quickAdd.remove();
                        }
                    }

                    // 8. Update Dashboard KPI cards, Gantt chart, and Kanban chart live!
                    if (data.dashboard_stats && window.updateProjectDashboard) {
                        window.updateProjectDashboard(data.dashboard_stats);
                    } else if (window.refreshProjectDashboard) {
                        window.refreshProjectDashboard(projectId);
                    }

                    // 9. Show feedback notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {type: 'success', text: `وضعیت گروه «${data.task?.title || ''}» به‌روزرسانی شد.`}
                    }));
                }
            })
                .catch(err => {
                    alert('⚠️ ' + err.message);
                })
                .finally(() => {
                    draggedTaskId = null;
                    originalStatusId = null;
                });
        }

        function updateKanbanCounters() {
            document.querySelectorAll('[id^="kanban-drop-zone-"]').forEach(zone => {
                const statusId = zone.id.replace('kanban-drop-zone-', '');
                const count = zone.querySelectorAll('[id^="kanban-task-"]').length;
                const counterEl = document.getElementById('kanban-col-count-' + statusId);
                if (counterEl) {
                    const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const countFa = String(count).replace(/[0-9]/g, d => faDigits[d]);
                    counterEl.textContent = countFa + ' کار';
                }
            });
        }
    </script>
@endpush
