@php
    $modalProjectMembers = $project->members->map(function($m) {
        return $m->user ? [
            'id' => (string)$m->user->id,
            'name' => $m->user->name,
            'email' => $m->user->email,
        ] : null;
    })->filter()->values();

    $defaultChecklistStatus = \Modules\Projects\App\Http\Models\ProjectStatus::inProgressFor('checklist')
        ?? \Modules\Projects\App\Http\Models\ProjectStatus::defaultFor('checklist');
    $defaultChecklistStatusId = $defaultChecklistStatus ? (string)$defaultChecklistStatus->id : '';
@endphp

<div id="subtask-modal-manager"
     x-data="subtaskModalManager()"
     x-show="open"
     class="relative z-[9999]"
     role="dialog"
     aria-modal="true"
     style="display: none;"
     @keydown.escape.window="close()">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    {{-- Modal Wrapper --}}
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20" @click.self="close()">
        <div class="flex min-h-full items-end justify-center text-center sm:items-center" @click.self="close()">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-5">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2"
                        x-text="editing ? 'ویرایش کار' : 'تعریف کار جدید'"></h3>
                    <button type="button" @click="close()"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form Content --}}
                <form @submit.prevent="save()" class="space-y-4">
                    {{-- Canceled Alert --}}
                    <div x-show="isCanceled"
                         class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 text-xs text-amber-700 dark:text-amber-300 font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>این گروه لغو شده است و امکان تغییر یا ویرایش کارهای آن وجود ندارد.</span>
                    </div>

                    {{-- Error Alert --}}
                    <div x-show="errorMsg" x-cloak
                         class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-xs text-red-700 dark:text-red-300 font-bold"
                         x-text="errorMsg"></div>

                    {{-- Work Item Title --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان کار <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.title" :disabled="isCanceled" required
                               placeholder="مثال: طراحی پروتوتایپ صفحه نخست"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    {{-- 2 Columns: Status, Due Date --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت
                                کار</label>
                            <select x-model="form.status_id" :disabled="!editing || isCanceled"
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                                @foreach($checklistStatuses as $chkStatus)
                                    <option value="{{ $chkStatus->id }}">{{ $chkStatus->name }}</option>
                                @endforeach
                            </select>
                            <span x-show="!editing" class="text-[10px] text-gray-400 mt-1 block">وضعیت اولیه کار «در حال انجام» است.</span>
                        </div>

                        {{-- Due Date --}}
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ
                                سررسید</label>
                            <div class="relative flex items-center">
                                <input type="text" data-jdp name="subtask_due_date" x-model="form.due_date"
                                       :data-jdp-max-date="maxDueDate || ''"
                                       :disabled="isCanceled"
                                       placeholder="۱۴۰۴/۰۱/۰۱" autocomplete="off" readonly
                                       @focus="if(!isCanceled && window.jalaliDatepicker) { if(maxDueDate) $el.setAttribute('data-jdp-max-date', maxDueDate); else $el.removeAttribute('data-jdp-max-date'); jalaliDatepicker.updateOptions({maxDate: 'attr'}); jalaliDatepicker.show($el); }"
                                       @click="if(!isCanceled && window.jalaliDatepicker) { if(maxDueDate) $el.setAttribute('data-jdp-max-date', maxDueDate); else $el.removeAttribute('data-jdp-max-date'); jalaliDatepicker.updateOptions({maxDate: 'attr'}); jalaliDatepicker.show($el); }"
                                       @change="form.due_date = $event.target.value"
                                       @input="form.due_date = $event.target.value"
                                       class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                <button type="button" x-show="form.due_date && !isCanceled"
                                        @click.stop="form.due_date = ''"
                                        class="absolute left-2.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                        title="پاک کردن تاریخ">
                                    ✕
                                </button>
                            </div>
                            <template x-if="maxDueDate">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 block"
                                      x-text="'حداکثر تا: ' + maxDueDate + (maxDueDateLabel ? ' (' + maxDueDateLabel + ')' : '')"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Multi-Select Assignees (مسئولین کار) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            مسئولین کار (یک یا چند نفر) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" @click.outside="assigneeDropdownOpen = false">
                            <div @click="if(!isCanceled) assigneeDropdownOpen = !assigneeDropdownOpen"
                                 class="w-full min-h-[42px] rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2 flex flex-wrap items-center gap-1.5 cursor-pointer focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all">

                                <template x-for="m in getSelectedMemberObjects()" :key="m.id">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200/60 dark:border-indigo-800/40 shadow-2xs">
                                        <span
                                            class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-200 flex items-center justify-center text-[10px]"
                                            x-text="(m.name || '؟').charAt(0)"></span>
                                        <span x-text="m.name" class="max-w-[120px] truncate"></span>
                                        <button type="button"
                                                x-show="!isCanceled"
                                                @click.stop="removeAssignee(m.id)"
                                                class="text-indigo-400 hover:text-red-500 transition-colors p-0.5 rounded"
                                                title="حذف مسئول">
                                            ✕
                                        </button>
                                    </span>
                                </template>

                                <span x-show="form.assigned_to.length === 0"
                                      class="text-xs text-gray-400 dark:text-gray-500 pr-1 select-none">
                                    انتخاب مسئولین کار (حداقل یک نفر)...
                                </span>

                                <div class="mr-auto pl-2 text-gray-400">
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                         :class="assigneeDropdownOpen ? 'rotate-180' : ''" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown list --}}
                            <div x-show="assigneeDropdownOpen && !isCanceled" x-cloak x-transition
                                 class="absolute right-0 left-0 top-full mt-1.5 z-50 max-h-56 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-2 space-y-1">

                                <div class="sticky top-0 bg-white dark:bg-gray-800 pb-1.5 z-10">
                                    <input type="text" x-model="assigneeQuery" @click.stop
                                           placeholder="جستجوی کاربر در بین اعضای پروژه..."
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                </div>

                                <template x-for="m in getFilteredMembers()" :key="m.id">
                                    <label @click.stop="toggleAssignee(m.id)"
                                           class="w-full text-right px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center justify-between gap-2.5 cursor-pointer transition-colors"
                                           :class="isAssigneeSelected(m.id) ? 'bg-indigo-50/70 dark:bg-indigo-950/40 font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span
                                                class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0"
                                                x-text="(m.name || '؟').charAt(0)"></span>
                                            <div class="truncate text-right">
                                                <span class="block truncate font-bold" x-text="m.name"></span>
                                                <span x-show="m.email" class="text-[10px] text-gray-400 block truncate"
                                                      x-text="m.email"></span>
                                            </div>
                                        </div>
                                        <input type="checkbox" :checked="isAssigneeSelected(m.id)"
                                               @click.stop="toggleAssignee(m.id)"
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    </label>
                                </template>

                                <div x-show="getFilteredMembers().length === 0"
                                     class="p-3 text-center text-xs text-gray-400">
                                    کاربری با این مشخصات در اعضای پروژه یافت نشد.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات
                            تکمیلی</label>
                        <textarea x-model="form.description" :disabled="isCanceled" rows="2"
                                  placeholder="توضیحات در صورت نیاز..."
                                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white disabled:opacity-60 disabled:cursor-not-allowed"></textarea>
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <button type="button" x-show="editing && !isCanceled"
                                    @click="cancelCurrentItem()"
                                    class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all flex items-center gap-1.5"
                                    title="لغو کار (غیرقابل بازگشت)">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                لغو کار
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="close()"
                                    class="px-5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200">
                                انصراف
                            </button>
                            <button type="submit" x-show="!isCanceled" :disabled="saving || !form.title.trim()"
                                    class="px-6 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-1.5">
                                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span
                                    x-text="saving ? 'در حال ذخیره...' : (editing ? 'ذخیره تغییرات' : 'ایجاد کار')"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.__modalProjectMembers = @json($modalProjectMembers);

        function subtaskModalManager() {
            return {
                open: false,
                editing: false,
                saving: false,
                isCanceled: false,
                maxDueDate: '',
                maxDueDateLabel: '',
                projectId: null,
                taskId: null,
                itemId: null,
                groupTitle: '',
                errorMsg: '',
                projectMembers: window.__modalProjectMembers || [],
                assigneeDropdownOpen: false,
                assigneeQuery: '',
                defaultStatusId: '{{ $defaultChecklistStatusId }}',
                form: {
                    title: '',
                    description: '',
                    assigned_to: [],
                    due_date: '',
                    status_id: '{{ $defaultChecklistStatusId }}',
                },

                isAssigneeSelected(id) {
                    return (this.form.assigned_to || []).map(String).includes(String(id));
                },

                toggleAssignee(id) {
                    if (this.isCanceled) return;
                    const strId = String(id);
                    if (!Array.isArray(this.form.assigned_to)) {
                        this.form.assigned_to = [];
                    }
                    const idx = this.form.assigned_to.map(String).indexOf(strId);
                    if (idx > -1) {
                        this.form.assigned_to.splice(idx, 1);
                    } else {
                        this.form.assigned_to.push(strId);
                    }
                },

                removeAssignee(id) {
                    if (this.isCanceled) return;
                    const strId = String(id);
                    if (!Array.isArray(this.form.assigned_to)) {
                        this.form.assigned_to = [];
                    }
                    const idx = this.form.assigned_to.map(String).indexOf(strId);
                    if (idx > -1) {
                        this.form.assigned_to.splice(idx, 1);
                    }
                },

                getSelectedMemberObjects() {
                    const assignedStrs = (this.form.assigned_to || []).map(String);
                    return (this.projectMembers || []).filter(m => assignedStrs.includes(String(m.id)));
                },

                getFilteredMembers() {
                    if (!this.assigneeQuery.trim()) {
                        return this.projectMembers || [];
                    }
                    const q = this.assigneeQuery.toLowerCase().trim();
                    return (this.projectMembers || []).filter(m => (m.name && m.name.toLowerCase().includes(q)) || (m.email && m.email.toLowerCase().includes(q)));
                },

                openCreate(projectId, taskId, groupTitle, isCanceled = false, maxDueDate = '', maxDueDateLabel = '') {
                    this.reset();
                    this.projectId = projectId;
                    this.taskId = taskId;
                    this.groupTitle = groupTitle;
                    this.isCanceled = !!isCanceled;
                    this.maxDueDate = maxDueDate || '';
                    this.maxDueDateLabel = maxDueDateLabel || '';
                    this.editing = false;
                    this.open = true;
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                                maxDate: 'attr',
                                date: true,
                                time: false
                            });
                        }
                    });
                },

                async openEdit(projectId, taskId, itemId, groupTitle, preloadedData = null, isCanceled = false, maxDueDate = '', maxDueDateLabel = '') {
                    this.reset();
                    this.projectId = projectId;
                    this.taskId = taskId;
                    this.itemId = itemId;
                    this.groupTitle = groupTitle;
                    this.isCanceled = !!isCanceled;
                    this.maxDueDate = maxDueDate || '';
                    this.maxDueDateLabel = maxDueDateLabel || '';
                    this.editing = true;

                    if (preloadedData) {
                        this.isCanceled = this.isCanceled || !!(preloadedData.status?.is_canceled || preloadedData.is_canceled || (preloadedData.status?.name && preloadedData.status.name.includes('لغو')));
                        let assignedArr = [];
                        if (Array.isArray(preloadedData.assignees) && preloadedData.assignees.length > 0) {
                            assignedArr = preloadedData.assignees.map(a => String(a.id || a.user_id || a));
                        } else if (Array.isArray(preloadedData.assigned_to)) {
                            assignedArr = preloadedData.assigned_to.map(String);
                        } else if (preloadedData.assigned_to) {
                            assignedArr = [String(preloadedData.assigned_to)];
                        }

                        this.form = {
                            title: preloadedData.title || '',
                            description: preloadedData.description || '',
                            assigned_to: assignedArr,
                            due_date: preloadedData.due_date_jalali || '',
                            status_id: preloadedData.status_id ? String(preloadedData.status_id) : (preloadedData.status?.id ? String(preloadedData.status.id) : ''),
                        };
                    }

                    this.open = true;

                    try {
                        const res = await fetch(`/user/projects/projects/${projectId}/tasks/${taskId}/checklist/${itemId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.status && (data.status.is_canceled || (data.status.name && data.status.name.includes('لغو')))) {
                                this.isCanceled = true;
                            }
                            let assignedArr = [];
                            if (Array.isArray(data.assignees) && data.assignees.length > 0) {
                                assignedArr = data.assignees.map(a => String(a.id || a.user_id || a));
                            } else if (Array.isArray(data.assigned_to)) {
                                assignedArr = data.assigned_to.map(String);
                            } else if (data.assigned_to) {
                                assignedArr = [String(data.assigned_to)];
                            }

                            this.form = {
                                title: data.title || '',
                                description: data.description || '',
                                assigned_to: assignedArr,
                                due_date: data.due_date_jalali || (data.due_date ? data.due_date.substring(0, 10).replaceAll('-', '/') : ''),
                                status_id: data.status_id ? String(data.status_id) : (data.status?.id ? String(data.status.id) : ''),
                            };
                            if (data.task_due_date_jalali) {
                                this.maxDueDate = data.task_due_date_jalali;
                                this.maxDueDateLabel = 'سررسید گروه';
                            } else if (data.project_due_date_jalali) {
                                this.maxDueDate = data.project_due_date_jalali;
                                this.maxDueDateLabel = 'پایان پروژه';
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching checklist item:', e);
                    }

                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                                maxDate: 'attr',
                                date: true,
                                time: false
                            });
                        }
                    });
        },

        close() {
            if (window.jalaliDatepicker) {
                jalaliDatepicker.hide();
            }
            this.open = false;
        },

        reset() {
            this.editing = false;
            this.saving = false;
            this.isCanceled = false;
            this.maxDueDate = '';
            this.maxDueDateLabel = '';
            this.itemId = null;
            this.errorMsg = '';
            this.assigneeDropdownOpen = false;
            this.assigneeQuery = '';
            this.form = {
                title: '',
                description: '',
                assigned_to: [],
                due_date: '',
                status_id: this.defaultStatusId || ''
            };
        },

        async save() {
            if (this.isCanceled) {
                alert('این گروه لغو شده است و امکان تغییر یا ایجاد کار در آن وجود ندارد.');
                return;
            }
            if (!this.form.title.trim()) {
                this.errorMsg = 'وارد کردن عنوان کار الزامی است.';
                return;
            }
            if (!this.form.assigned_to || this.form.assigned_to.length === 0) {
                this.errorMsg = 'انتخاب حداقل یک مسئول کار الزامی است.';
                return;
            }

            if (this.maxDueDate && this.form.due_date) {
                const formDateClean = this.form.due_date.replace(/[^0-9]/g, '');
                const maxDateClean = this.maxDueDate.replace(/[^0-9]/g, '');
                if (formDateClean && maxDateClean && formDateClean > maxDateClean) {
                    this.errorMsg = `تاریخ سررسید کار نمی‌تواند پس از ${this.maxDueDate} (${this.maxDueDateLabel || 'سررسید گروه'}) باشد.`;
                    return;
                }
            }

            this.saving = true;
            this.errorMsg = '';

            const url = this.editing
                ? `/user/projects/projects/${this.projectId}/tasks/${this.taskId}/checklist/${this.itemId}`
                : `/user/projects/projects/${this.projectId}/tasks/${this.taskId}/checklist`;

            const method = this.editing ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorMsg = data.message || 'خطا در ذخیره کار';
                    this.saving = false;
                    return;
                }

                this.close();
                window.location.reload();
            } catch (e) {
                this.errorMsg = 'خطا در برقراری ارتباط با سرور';
                this.saving = false;
            }
        },

        async cancelCurrentItem() {
            if (!this.itemId) return;
            if (!confirm(`آیا از لغو کار «${this.form.title}» اطمینان دارید؟ این عملیات غیرقابل بازگشت است.`)) return;

            try {
                const res = await fetch(`/user/projects/projects/${this.projectId}/tasks/${this.taskId}/checklist/${this.itemId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.close();
                    if (window.cancelChecklistItemSuccessCallback) {
                        window.cancelChecklistItemSuccessCallback(this.taskId, this.itemId, data);
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'خطا در لغو کار');
                }
            } catch (e) {
                console.error('Error cancelling work item:', e);
                alert('خطا در برقراری ارتباط با سرور');
            }
        },
    };
}

window.openSubtaskCreate = function (projectId, taskId, groupTitle, isCanceled = false, maxDueDate = '', maxDueDateLabel = '') {
    const el = document.getElementById('subtask-modal-manager') || document.querySelector('[x-data*="subtaskModalManager"]');
    if (el && window.Alpine) {
        window.Alpine.$data(el).openCreate(projectId, taskId, groupTitle, isCanceled, maxDueDate, maxDueDateLabel);
    }
};

window.openSubtaskEdit = function (projectId, taskId, itemId, groupTitle, preloadedData = null, isCanceled = false, maxDueDate = '', maxDueDateLabel = '') {
    const el = document.getElementById('subtask-modal-manager') || document.querySelector('[x-data*="subtaskModalManager"]');
    if (el && window.Alpine) {
        window.Alpine.$data(el).openEdit(projectId, taskId, itemId, groupTitle, preloadedData, isCanceled, maxDueDate, maxDueDateLabel);
    }
};
</script>
@endpush
