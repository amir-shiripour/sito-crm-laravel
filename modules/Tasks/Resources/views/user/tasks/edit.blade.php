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

                    // فیلتر کاربران مرتبط بر اساس نقش‌ها
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

                    // فیلتر مشتریان بر اساس وضعیت‌ها
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
        use Morilog\Jalali\Jalalian;

        /** @var \Modules\Tasks\Entities\Task $task */

        $currentUser    = auth()->user();
        $canAssign      = $canAssign ?? ($currentUser && ($currentUser->can('tasks.assign') || $currentUser->can('tasks.manage') || $currentUser->hasRole('super-admin')));

        $types          = $types ?? Task::typeOptions();
        $statuses       = $statuses ?? Task::statusOptions();
        $priorities     = $priorities ?? Task::priorityOptions();
        $users          = $users ?? collect();
        $roles          = $roles ?? collect();
        $clients        = $clients ?? collect();
        $clientStatuses = $clientStatuses ?? collect();

        $meta           = $task->meta ?? [];

        // تاریخ سررسید برای نمایش شمسی
        $dueAtView = old('due_at_view');
        if (! $dueAtView && $task->due_at) {
            try {
                $dueAtView = Jalalian::fromCarbon($task->due_at)->format('Y/m/d');
            } catch (\Throwable $e) {
                $dueAtView = $task->due_at->format('Y-m-d');
            }
        }

        // کاربران با نقش‌ها (برای فیلتر پویا)
        $userOptions = $users->map(function ($u) {
            return [
                'id'       => $u->id,
                'name'     => $u->name,
                'email'    => $u->email ?? '',
                'role_ids' => $u->roles ? $u->roles->pluck('id')->map(fn ($id) => (string) $id)->values()->all() : [],
            ];
        })->values()->all();

        // مشتری‌ها با وضعیت‌ها (برای فیلتر پویا)
        $clientOptions = $clients->map(function ($c) {
            return [
                'id'        => $c->id,
                'name'      => $c->full_name,
                'phone'     => $c->phone ?? '',
                'status_id' => $c->status_id ? (string) $c->status_id : null,
            ];
        })->values()->all();

        // گزینه‌های نقش‌ها
        $roleOptions = $roles->map(fn($r) => [
            'value' => (string) $r->id,
            'label' => $r->name,
        ])->values()->all();

        // گزینه‌های کاربران برای multi-select (label کامل)
        $userSelectOptions = collect($userOptions)->map(function ($u) {
            return [
                'value' => (string) $u['id'],
                'label' => $u['name'] . (!empty($u['email']) ? ' (' . $u['email'] . ')' : ''),
            ];
        })->all();

        // گزینه‌های مشتریان برای multi-select
        $clientSelectOptions = collect($clientOptions)->map(function ($c) {
            return [
                'value' => (string) $c['id'],
                'label' => $c['name'] . (!empty($c['phone']) ? ' (' . $c['phone'] . ')' : ''),
            ];
        })->all();

        // گزینه‌های وضعیت مشتری
        $clientStatusOptions = $clientStatuses->map(fn($st) => [
            'value' => (string) $st->id,
            'label' => $st->label ?? $st->key,
        ])->values()->all();

        // پیش‌فرض‌ها برای حالت‌های مختلف از meta + خود task
        $defaultAssigneeMode  = old('assignee_mode', $meta['assignee_mode'] ?? 'single_user');

        $defaultRelatedTarget = old('related_target',
            $meta['related_target']
            ?? ( $task->related_type === Task::RELATED_TYPE_USER
                    ? 'user'
                    : ( $task->related_type === Task::RELATED_TYPE_CLIENT
                        ? 'client'
                        : 'none'
                    )
               )
        );

        // انتخاب فعلی مسئول‌ها (در فرم edit این Task خاص) → خود assignee
        $defaultAssigneeUserIds = collect(old('assignee_user_ids', []))
            ->when(empty(old('assignee_user_ids', [])) && $task->assignee_id, function ($col) use ($task) {
                return collect([$task->assignee_id]);
            })
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        // نقش‌های مسئول از meta
        $defaultAssigneeRoleIds = collect(old('assignee_role_ids', $meta['assignee_role_ids'] ?? []))
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        // مرتبط با کاربر
        $defaultRelatedUserIds = collect(old('related_user_ids', $meta['related_user_ids'] ?? []))
            ->when(
                empty(old('related_user_ids', $meta['related_user_ids'] ?? []))
                && $task->related_type === Task::RELATED_TYPE_USER
                && $task->related_id,
                fn($col) => collect([$task->related_id])
            )
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        $defaultRelatedUserRoleIds = collect(old('related_user_role_ids', $meta['related_user_role_ids'] ?? []))
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        // مرتبط با مشتری
        $defaultRelatedClientIds = collect(old('related_client_ids', $meta['related_client_ids'] ?? []))
            ->when(
                empty(old('related_client_ids', $meta['related_client_ids'] ?? []))
                && $task->related_type === Task::RELATED_TYPE_CLIENT
                && $task->related_id,
                fn($col) => collect([$task->related_id])
            )
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        $defaultRelatedClientStatusIds = collect(old('related_client_status_ids', $meta['related_client_status_ids'] ?? []))
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        $alpineMainData = [
            'taskType'    => old('task_type', $task->task_type ?? Task::TYPE_GENERAL),
            'assigneeMode' => $defaultAssigneeMode,
            'relatedTarget' => $defaultRelatedTarget,
            'canAssign'   => (bool) $canAssign,

            'users'   => $userOptions,
            'clients' => $clientOptions,

            'assigneeSearch'       => '',
            'relatedUserSearch'    => '',
            'relatedClientSearch'  => '',

            'selectedRelatedUserRoleIds'     => $defaultRelatedUserRoleIds,
            'selectedRelatedClientStatusIds' => $defaultRelatedClientStatusIds,
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    ویرایش وظیفه
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    اطلاعات وظیفه را به‌روزرسانی کنید و مسئول انجام آن را تنظیم نمایید.
                </p>
            </div>
            <a href="{{ route('user.tasks.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                <span>بازگشت به لیست</span>
            </a>
        </div>

        <form method="POST"
              action="{{ route('user.tasks.update', $task) }}"
              class="space-y-8"
              x-data='@json($alpineMainData)'
              data-task-form-root
              x-init="window.taskFormContext = $data"
              @related_user_role_ids-changed="selectedRelatedUserRoleIds = $event.detail || []"
              @related_client_status_ids-changed="selectedRelatedClientStatusIds = $event.detail || []"
        >
            @csrf
            @method('PUT')

            {{-- کارت اطلاعات اصلی --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                    اطلاعات پایه
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- عنوان --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            عنوان وظیفه <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $task->title) }}"
                               required
                               class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:border-emerald-500">
                        @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- نوع وظیفه --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            نوع وظیفه
                        </label>
                        <select name="task_type" x-model="taskType"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($types as $value => $label)
                                @if($value !== Task::TYPE_SYSTEM)
                                    <option value="{{ $value }}"
                                        @selected(old('task_type', $task->task_type) === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('task_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- تاریخ سررسید --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            تاریخ سررسید
                        </label>
                        <div class="relative">
                            <input id="due_at_view"
                                   name="due_at_view"
                                   type="text"
                                   data-jdp
                                   autocomplete="off"
                                   placeholder="انتخاب تاریخ..."
                                   value="{{ old('due_at_view', $dueAtView) }}"
                                   class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        @error('due_at_view') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- وضعیت --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            وضعیت
                        </label>
                        <select name="status"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('status', $task->status) === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- اولویت --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            اولویت
                        </label>
                        <select name="priority"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('priority', $task->priority) === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- توضیحات --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            توضیحات تکمیلی
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  placeholder="جزئیات بیشتر در مورد وظیفه..."
                                  class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">{{ old('description', $task->description) }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- کارت تعیین مسئول --}}
            <div class="bg-gray-50/80 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-5 bg-indigo-500 rounded-full"></span>
                    مسئول انجام کار
                </h3>

                {{-- پیام اتوماتیک برای Follow-up بدون دسترسی assign (در حالت edit بیشتر جنبه‌ی اطلاع دارد) --}}
                @if($currentUser)
                    <div x-show="taskType === '{{ Task::TYPE_FOLLOW_UP }}' && !canAssign" x-cloak
                         class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 text-blue-800 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800/30">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">
                            در پیگیری‌ها، در صورت نداشتن مجوز تعیین مسئول، شما به‌عنوان مسئول این وظیفه در نظر گرفته می‌شوید.
                        </p>
                    </div>
                @endif

                <div x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)"
                     x-cloak class="space-y-5">
                    <div class="w-full sm:w-1/2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            روش انتخاب مسئول
                        </label>
                        <select name="assignee_mode" x-model="assigneeMode"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="single_user">انتخاب کاربر مشخص</option>
                            <option value="by_roles">انتخاب بر اساس نقش‌ها</option>
                        </select>
                    </div>

                    {{-- حالت انتخاب چند کاربر مشخص --}}
                    <div x-show="assigneeMode === 'single_user'" x-cloak class="space-y-2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            کاربران مسئول (امکان انتخاب چند نفر)
                        </label>

                        <div x-data="setupMultiSelect({
                                name: 'assignee_user_ids',
                                options: {{ Js::from($userSelectOptions) }},
                                oldValues: {{ Js::from($defaultAssigneeUserIds) }},
                                allLabel: null
                            })"
                        >
                            @include('tasks::partials.multi-select-template')
                        </div>

                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            در صورت انتخاب چند کاربر، برای هر کاربر یک وظیفه جداگانه ساخته شده است. در حالت ویرایش، شما این وظیفه را برای همین کاربر ویرایش می‌کنید.
                        </p>

                        @error('assignee_user_ids')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- حالت نقش‌ها --}}
                    <div x-show="assigneeMode === 'by_roles'" x-cloak>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            نقش‌های مجاز
                        </label>
                        <div
                            x-data="setupMultiSelect({
                                name: 'assignee_role_ids',
                                options: {{ Js::from($roleOptions) }},
                                oldValues: {{ Js::from($defaultAssigneeRoleIds) }},
                                allLabel: 'همه نقش‌ها'
                            })"
                        >
                            @include('tasks::partials.multi-select-template')
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت موجودیت مرتبط --}}
            <div class="bg-gray-50/80 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-1 h-5 bg-pink-500 rounded-full"></span>
                    ارتباط با موجودیت دیگر
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            نوع موجودیت
                        </label>
                        <select name="related_target" x-model="relatedTarget"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="none">هیچکدام</option>
                            <option value="user">کاربر داخلی (User)</option>
                            <option value="client">مشتری (Client)</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2 space-y-5">
                        {{-- ارتباط با کاربر --}}
                        <div x-show="relatedTarget === 'user'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                    نقش‌های مرتبط
                                </label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_user_role_ids',
                                        options: {{ Js::from($roleOptions) }},
                                        oldValues: {{ Js::from($defaultRelatedUserRoleIds) }},
                                        allLabel: 'همه نقش‌ها'
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                    کاربران مرتبط (امکان انتخاب چند کاربر)
                                </label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_user_ids',
                                        options: {{ Js::from($userSelectOptions) }},
                                        oldValues: {{ Js::from($defaultRelatedUserIds) }},
                                        allLabel: null
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                                @error('related_user_ids')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- ارتباط با مشتری --}}
                        <div x-show="relatedTarget === 'client'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                    وضعیت‌های مشتری
                                </label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_client_status_ids',
                                        options: {{ Js::from($clientStatusOptions) }},
                                        oldValues: {{ Js::from($defaultRelatedClientStatusIds) }},
                                        allLabel: 'همه وضعیت‌ها'
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                    مشتریان مرتبط (امکان انتخاب چند مشتری)
                                </label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_client_ids',
                                        options: {{ Js::from($clientSelectOptions) }},
                                        oldValues: {{ Js::from($defaultRelatedClientIds) }},
                                        allLabel: null
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>

                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    لیست مشتریان، بر اساس وضعیت‌های انتخاب شده در سمت چپ فیلتر می‌شود.
                                </p>

                                @error('related_client_ids')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- فوتر / دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.tasks.show', $task) }}"
                   class="px-6 py-2.5 text-sm font-medium rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    انصراف
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all transform active:scale-95">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
