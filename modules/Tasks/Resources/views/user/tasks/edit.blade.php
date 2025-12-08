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

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);
                },

                isSelected(value) {
                    value = String(value);
                    return this.selectedValues.includes(value);
                },

                toggle(value) {
                    value = String(value);
                    if (value === '__all__') {
                        this.selectedValues = this.isSelected('__all__') ? [] : ['__all__'];
                        return;
                    }
                    this.selectedValues = this.selectedValues.filter(v => v !== '__all__');
                    if (this.isSelected(value)) {
                        this.selectedValues = this.selectedValues.filter(v => v !== value);
                    } else {
                        this.selectedValues.push(value);
                    }
                },

                clearValue(value) {
                    value = String(value);
                    this.selectedValues = this.selectedValues.filter(v => v !== value);
                },

                filteredOptions() {
                    const term = this.search.toLowerCase();
                    return this.options.filter(o => (o.label || '').toLowerCase().includes(term));
                },
            };
        }
    </script>

    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    ویرایش وظیفه
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    اطلاعات وظیفه را به‌روزرسانی کنید.
                </p>
            </div>
            <a href="{{ route('user.tasks.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                <span>بازگشت به لیست</span>
            </a>
        </div>

        @php
            use Modules\Tasks\Entities\Task;
            use Illuminate\Support\Js;

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

            $userOptions = $users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email ?? '',
            ])->values()->all();

            $clientOptions = $clients->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->full_name,
                'phone' => $c->phone ?? '',
            ])->values()->all();

            $roleOptions = $roles->map(fn($r) => [
                'value' => (string) $r->id,
                'label' => $r->name,
            ])->values()->all();

            $clientStatusOptions = $clientStatuses->map(fn($st) => [
                'value' => (string) $st->id,
                'label' => $st->label ?? $st->key,
            ])->values()->all();

            $alpineMainData = [
                'taskType'          => old('task_type', $task->task_type ?? Task::TYPE_GENERAL),
                'assigneeMode'      => old('assignee_mode', $task->assignee_mode ?? 'single_user'),
                'relatedTarget'     => old('related_target', $task->related_target ?? 'none'),
                'canAssign'         => (bool) $canAssign,
                'users'             => $userOptions,
                'clients'           => $clientOptions,
                'assigneeSearch'    => '',
                'relatedUserSearch' => '',
                'relatedClientSearch' => '',
            ];
        @endphp

        <form method="POST"
              action="{{ route('user.tasks.update', $task) }}"
              class="space-y-8"
              x-data='@json($alpineMainData)'>
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
                        @error('title')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- نوع وظیفه --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">نوع وظیفه</label>
                        <select name="task_type" x-model="taskType"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($types as $value => $label)
                                @if($value !== Task::TYPE_SYSTEM)
                                    <option value="{{ $value }}" @selected(old('task_type', $task->task_type) === $value)>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('task_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- تاریخ سررسید --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">تاریخ سررسید</label>
                        <div class="relative">
                            <input id="due_at_view"
                                   name="due_at_view"
                                   type="text"
                                   data-jdp
                                   autocomplete="off"
                                   placeholder="انتخاب تاریخ..."
                                   value="{{ old('due_at_view', $task->due_at_view ?? '') }}"
                                   class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        @error('due_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- وضعیت --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">وضعیت</label>
                        <select name="status"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- اولویت --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">اولویت</label>
                        <select name="priority"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm transition-shadow focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- توضیحات --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">توضیحات تکمیلی</label>
                        <textarea name="description" rows="4"
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

                @if($currentUser)
                    <div x-show="taskType === '{{ Task::TYPE_FOLLOW_UP }}' && !canAssign" x-cloak
                         class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 text-blue-800 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800/30">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm">
                            مسئول این پیگیری به‌صورت خودکار
                            <span class="font-bold">{{ $currentUser->name }}</span>
                            خواهد بود.
                        </p>
                        <input type="hidden" name="assignee_id" value="{{ $currentUser->id }}">
                    </div>
                @endif

                <div x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)" x-cloak class="space-y-5">
                    <div class="w-full sm:w-1/2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">روش انتخاب مسئول</label>
                        <select name="assignee_mode" x-model="assigneeMode"
                                class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="single_user">انتخاب کاربر مشخص</option>
                            <option value="by_roles">انتخاب بر اساس نقش‌ها</option>
                        </select>
                    </div>

                    {{-- حالت کاربر تکی --}}
                    <div x-show="assigneeMode === 'single_user'" x-cloak>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">جستجوی کاربر</label>
                        <div class="relative">
                            <input type="text" x-model="assigneeSearch" placeholder="نام یا ایمیل کاربر..."
                                   class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white mb-2">

                            <select name="assignee_id" size="5"
                                    class="w-full rounded-xl border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                <template
                                    x-for="u in users.filter(user =>
                                        user.name.toLowerCase().includes(assigneeSearch.toLowerCase()) ||
                                        user.email.toLowerCase().includes(assigneeSearch.toLowerCase())
                                    )"
                                    :key="u.id"
                                >
                                    <option :value="u.id"
                                            x-text="u.name + (u.email ? ' (' + u.email + ')' : '')"
                                            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                    </option>
                                </template>
                            </select>
                        </div>
                        @error('assignee_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- حالت نقش‌ها --}}
                    <div x-show="assigneeMode === 'by_roles'" x-cloak>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">نقش‌های مجاز</label>
                        <div
                            x-data="setupMultiSelect({
                                name: 'assignee_role_ids',
                                options: {{ Js::from($roleOptions) }},
                                oldValues: {{ Js::from((array) old('assignee_role_ids', $task->assignee_role_ids ?? [])) }},
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
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">نوع موجودیت</label>
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
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">نقش‌های مرتبط</label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_user_role_ids',
                                        options: {{ Js::from($roleOptions) }},
                                        oldValues: {{ Js::from((array) old('related_user_role_ids', $task->related_user_role_ids ?? [])) }},
                                        allLabel: 'همه نقش‌ها'
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">جستجوی کاربر خاص</label>
                                <input type="text" x-model="relatedUserSearch" placeholder="جستجو..."
                                       class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white mb-2">
                                <select name="related_user_id" size="3"
                                        class="w-full rounded-xl border-gray-300 bg-white p-2 text-sm focus:border-pink-500 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                    <template
                                        x-for="u in users.filter(user =>
                                            user.name.toLowerCase().includes(relatedUserSearch.toLowerCase()) ||
                                            user.email.toLowerCase().includes(relatedUserSearch.toLowerCase())
                                        )"
                                        :key="u.id"
                                    >
                                        <option :value="u.id"
                                                x-text="u.name"
                                                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- ارتباط با مشتری --}}
                        <div x-show="relatedTarget === 'client'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">وضعیت‌های مشتری</label>
                                <div
                                    x-data="setupMultiSelect({
                                        name: 'related_client_status_ids',
                                        options: {{ Js::from($clientStatusOptions) }},
                                        oldValues: {{ Js::from((array) old('related_client_status_ids', $task->related_client_status_ids ?? [])) }},
                                        allLabel: 'همه وضعیت‌ها'
                                    })"
                                >
                                    @include('tasks::partials.multi-select-template')
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">جستجوی مشتری خاص</label>
                                <input type="text" x-model="relatedClientSearch" placeholder="نام یا شماره تماس..."
                                       class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white mb-2">
                                <select name="related_client_id" size="3"
                                        class="w-full rounded-xl border-gray-300 bg-white p-2 text-sm focus:border-pink-500 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                    <template
                                        x-for="c in clients.filter(client =>
                                            client.name.toLowerCase().includes(relatedClientSearch.toLowerCase()) ||
                                            client.phone.toLowerCase().includes(relatedClientSearch.toLowerCase())
                                        )"
                                        :key="c.id"
                                    >
                                        <option :value="c.id"
                                                x-text="c.name"
                                                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- فوتر --}}
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.tasks.show', $task) }}"
                   class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    مشاهده جزئیات وظیفه
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.tasks.index') }}"
                       class="px-6 py-2.5 text-sm font-medium rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        انصراف
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all transform active:scale-95">
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
