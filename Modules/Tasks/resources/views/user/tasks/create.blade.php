@extends('layouts.user')

@section('content')
    <script>
        function setupMultiSelect(config) {
            return {
                name: config.name,
                allLabel: config.allLabel || null,
                options: config.options || [],
                selectedValues: [],
                search: '',
                open: false,
                formContext: null,

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);

                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                isSelected(value) {
                    value = String(value);
                    return this.selectedValues.includes(value);
                },

                toggle(value) {
                    value = String(value);

                    if (value === '__all__') {
                        this.selectedValues = this.isSelected('__all__') ? [] : ['__all__'];
                    } else {
                        this.selectedValues = this.selectedValues.filter(v => v !== '__all__');

                        if (this.isSelected(value)) {
                            this.selectedValues = this.selectedValues.filter(v => v !== value);
                        } else {
                            this.selectedValues.push(value);
                        }
                    }

                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                clearValue(value) {
                    value = String(value);
                    this.selectedValues = this.selectedValues.filter(v => v !== value);

                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                filteredOptions() {
                    const term = (this.search || '').toLowerCase();
                    let baseOpts = this.options || [];

                    const ctx = this.formContext || window.taskFormContext || {};

                    if (this.name === 'related_user_ids' && ctx.users && ctx.selectedRelatedUserRoleIds) {
                        const selectedRoles = (ctx.selectedRelatedUserRoleIds || [])
                            .filter(v => v !== '__all__')
                            .map(String);

                        if (selectedRoles.length > 0) {
                            const allUsers = ctx.users || [];
                            baseOpts = baseOpts.filter(opt => {
                                const user = allUsers.find(u => String(u.id) === String(opt.value));
                                if (!user) return false;
                                const roleIds = (user.role_ids || []).map(String);
                                return selectedRoles.some(rid => roleIds.includes(rid));
                            });
                        }
                    }

                    if (this.name === 'related_client_ids' && ctx.clients && ctx.selectedRelatedClientStatusIds) {
                        const selectedStatuses = (ctx.selectedRelatedClientStatusIds || [])
                            .filter(v => v !== '__all__')
                            .map(String);

                        if (selectedStatuses.length > 0) {
                            const allClients = ctx.clients || [];
                            baseOpts = baseOpts.filter(opt => {
                                const client = allClients.find(c => String(c.id) === String(opt.value));
                                if (!client) return false;
                                const clientStatusId = client.status_id != null ? String(client.status_id) : null;
                                if (!clientStatusId) return false;
                                return selectedStatuses.includes(clientStatusId);
                            });
                        }
                    }

                    return baseOpts.filter(o => (o.label || '').toLowerCase().includes(term));
                },
            };
        }
    </script>

    @php
        use Modules\Tasks\Entities\Task;
        use Illuminate\Support\Js;

        $currentUser    = auth()->user();
        $canAssign      = $canAssign ?? ($currentUser && ($currentUser->can('tasks.assign') || $currentUser->can('tasks.manage') || $currentUser->hasRole('super-admin')));

        $types          = $types ?? Task::typeOptions();
        $statuses       = $statuses ?? Task::statusOptions();
        $priorities     = $priorities ?? Task::priorityOptions();
        $users          = $users ?? collect();
        $roles          = $roles ?? collect();
        $clients        = $clients ?? collect();
        $clientStatuses = $clientStatuses ?? collect();

        $userOptions = $users->map(function ($u) {
            return [
                'id'       => $u->id,
                'name'     => $u->name,
                'email'    => $u->email ?? '',
                'role_ids' => $u->roles ? $u->roles->pluck('id')->map(fn ($id) => (string) $id)->values()->all() : [],
            ];
        })->values()->all();

        $clientOptions = $clients->map(function ($c) {
            return [
                'id'         => $c->id,
                'name'       => $c->full_name,
                'phone'      => $c->phone ?? '',
                'status_id' => $c->status_id ? (string) $c->status_id : null,
            ];
        })->values()->all();

        $roleOptions = $roles->map(fn($r) => [
            'value' => (string) $r->id,
            'label' => $r->name,
        ])->values()->all();

        $relatedUserSelectOptions = $users->map(function ($u) {
                return [
                    'value'     => (string) $u->id,
                    'label'     => $u->name . ($u->email ? ' (' . $u->email . ')' : ''),
                    'role_ids'  => $u->roles->pluck('id')->map(fn($id) => (string) $id)->all(),
                ];
            })->values()->all();

        $clientStatusOptions = $clientStatuses->map(fn($st) => [
            'value' => (string) $st->id,
            'label' => $st->label ?? $st->key,
        ])->values()->all();

        $userSelectOptions = collect($userOptions)->map(function ($u) {
            return [
                'value' => (string) $u['id'],
                'label' => $u['name'] . (!empty($u['email']) ? ' (' . $u['email'] . ')' : ''),
            ];
        })->all();

        $clientSelectOptions = collect($clientOptions)->map(function ($c) {
            return [
                'value' => (string) $c['id'],
                'label' => $c['name'] . (!empty($c['phone']) ? ' (' . $c['phone'] . ')' : ''),
            ];
        })->all();

        $alpineMainData = [
            'taskType'    => old('task_type', Task::TYPE_GENERAL),
            'assigneeMode' => old('assignee_mode', 'single_user'),
            'relatedTarget' => old('related_target', 'none'),
            'canAssign'   => (bool) $canAssign,
            'users'   => $userOptions,
            'clients' => $clientOptions,
            'assigneeSearch'       => '',
            'relatedUserSearch'    => '',
            'relatedClientSearch'  => '',
            'selectedRelatedUserRoleIds'    => collect(old('related_user_role_ids', []))->map(fn ($id) => (string) $id)->values()->all(),
            'selectedRelatedClientStatusIds'=> collect(old('related_client_status_ids', []))->map(fn ($id) => (string) $id)->values()->all(),
        ];
    @endphp

    <div class="w-full max-w-7xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-8 bg-emerald-500 rounded-full hidden sm:block"></span>
                    ایجاد وظیفه جدید
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:pr-4">
                    اطلاعات وظیفه را وارد کنید و مسئول انجام آن را مشخص نمایید.
                </p>
            </div>
            <a href="{{ route('user.tasks.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به لیست
            </a>
        </div>

        <form method="POST"
              action="{{ route('user.tasks.store') }}"
              class="space-y-8"
              x-data='@json($alpineMainData)'
              data-task-form-root
              x-init="window.taskFormContext = $data"
              @related_user_role_ids-changed="selectedRelatedUserRoleIds = $event.detail || []"
              @related_client_status_ids-changed="selectedRelatedClientStatusIds = $event.detail || []"
        >
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- ستون اصلی --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- کارت اطلاعات اصلی --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </span>
                            اطلاعات پایه
                        </h3>

                        <div class="space-y-4">
                            {{-- عنوان --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    عنوان وظیفه <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" value="{{ old('title') }}" required
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900 dark:focus:border-emerald-500/50"
                                       placeholder="عنوان وظیفه را وارد کنید...">
                                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- توضیحات --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    توضیحات تکمیلی
                                </label>
                                <textarea name="description" rows="4"
                                          class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900 dark:focus:border-emerald-500/50"
                                          placeholder="جزئیات بیشتر در مورد وظیفه...">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- کارت تنظیمات وضعیت و زمان --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            وضعیت و زمان‌بندی
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- نوع وظیفه --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    نوع وظیفه
                                </label>
                                <div class="relative">
                                    <select name="task_type" x-model="taskType"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        @foreach($types as $value => $label)
                                            @if($value !== Task::TYPE_SYSTEM)
                                                <option value="{{ $value }}" @selected(old('task_type', Task::TYPE_GENERAL) === $value)>{{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                @error('task_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- وضعیت --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    وضعیت
                                </label>
                                <div class="relative">
                                    <select name="status"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- اولویت --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    اولویت
                                </label>
                                <div class="relative">
                                    <select name="priority"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        @foreach($priorities as $value => $label)
                                            <option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                @error('priority') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- موعد انجام (شمسی) + ساعت --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    تاریخ و ساعت سررسید
                                </label>
                                <div class="flex gap-3">
                                    {{-- تاریخ --}}
                                    <div class="relative flex-1">
                                        <input id="due_at_view" name="due_at_view" type="text" data-jdp autocomplete="off"
                                               placeholder="انتخاب تاریخ..."
                                               value="{{ old('due_at_view') }}"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-center focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>

                                    {{-- ساعت (اختیاری) --}}
                                    <div class="relative w-32">
                                        <input type="text"
                                               data-jdp-only-time
                                               placeholder="00:00"
                                               name="due_time"
                                               value="{{ old('due_time') }}"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-center dir-ltr focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                    </div>
                                </div>
                                @error('due_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                @error('due_time') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ستون کناری --}}
                <div class="space-y-6">
                    {{-- کارت تعیین مسئول --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </span>
                            مسئول انجام کار
                        </h3>

                        {{-- پیام اتوماتیک --}}
                        @if($currentUser)
                            <div x-show="taskType === '{{ Task::TYPE_FOLLOW_UP }}' && !canAssign" x-cloak
                                 class="flex items-start gap-3 p-3 rounded-xl bg-blue-50 border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800/30">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-blue-800 dark:text-blue-200">
                                    مسئول این پیگیری به‌صورت خودکار <span class="font-bold">{{ $currentUser->name }}</span> خواهد بود.
                                </p>
                                <input type="hidden" name="assignee_id" value="{{ $currentUser->id }}">
                            </div>
                        @endif

                        <div x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)" x-cloak class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    روش انتخاب مسئول
                                </label>
                                <div class="relative">
                                    <select name="assignee_mode" x-model="assigneeMode"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        <option value="single_user">انتخاب کاربر مشخص</option>
                                        <option value="by_roles">انتخاب بر اساس نقش‌ها</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- حالت انتخاب چند کاربر مشخص --}}
                            <div x-show="assigneeMode === 'single_user'" x-cloak class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    کاربران مسئول
                                </label>
                                <div x-data="setupMultiSelect({
                                        name: 'assignee_user_ids',
                                        options: {{ Js::from($userSelectOptions) }},
                                        oldValues: {{ Js::from((array) old('assignee_user_ids', [])) }},
                                        allLabel: null
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                                @error('assignee_user_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- حالت نقش‌ها --}}
                            <div x-show="assigneeMode === 'by_roles'" x-cloak class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    نقش‌های مجاز
                                </label>
                                <div x-data="setupMultiSelect({ name: 'assignee_role_ids', options: {{ Js::from($roleOptions) }}, oldValues: {{ Js::from((array) old('assignee_role_ids', [])) }}, allLabel: 'همه نقش‌ها' })">
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت موجودیت مرتبط --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            </span>
                            ارتباط با موجودیت دیگر
                        </h3>

                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                نوع موجودیت
                            </label>
                            <div class="relative">
                                <select name="related_target" x-model="relatedTarget"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-pink-500 focus:bg-white focus:ring-2 focus:ring-pink-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                    <option value="none">هیچکدام</option>
                                    <option value="user">کاربر داخلی (User)</option>
                                    <option value="client">مشتری (Client)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- ارتباط با کاربر --}}
                        <div x-show="relatedTarget === 'user'" x-cloak class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">نقش‌های مرتبط</label>
                                <div x-data="setupMultiSelect({
                                        name: 'related_user_role_ids',
                                        options: {{ Js::from($roleOptions) }},
                                        oldValues: {{ Js::from((array) old('related_user_role_ids', [])) }},
                                        allLabel: 'همه نقش‌ها'
                                    })">
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">کاربران مرتبط</label>
                                <div x-data="setupMultiSelect({
                                        name: 'related_user_ids',
                                        options: {{ Js::from($relatedUserSelectOptions) }},
                                        oldValues: {{ Js::from((array) old('related_user_ids', [])) }},
                                        allLabel: null
                                    })">
                                    @include('tasks::partials.multi-select-template')
                                </div>
                                @error('related_user_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- ارتباط با مشتری --}}
                        <div x-show="relatedTarget === 'client'" x-cloak class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">وضعیت‌های مشتری</label>
                                <div x-data="setupMultiSelect({
                                        name: 'related_client_status_ids',
                                        options: {{ Js::from($clientStatusOptions) }},
                                        oldValues: {{ Js::from((array) old('related_client_status_ids', [])) }},
                                        allLabel: 'همه وضعیت‌ها'
                                    })">
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">مشتریان مرتبط</label>
                                <div x-data="setupMultiSelect({
                                        name: 'related_client_ids',
                                        options: {{ Js::from($clientSelectOptions) }},
                                        oldValues: {{ Js::from((array) old('related_client_ids', [])) }},
                                        allLabel: null
                                    })">
                                    @include('tasks::partials.multi-select-template')
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                                    لیست مشتریان بر اساس وضعیت‌های انتخاب شده فیلتر می‌شود.
                                </p>
                                @error('related_client_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- فوتر / دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.tasks.index') }}"
                   class="px-6 py-2.5 text-sm font-bold rounded-xl border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-800 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    انصراف
                </a>
                <button type="submit"
                        class="px-8 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all transform active:scale-95">
                    ذخیره وظیفه
                </button>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
