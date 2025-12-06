@extends('layouts.user')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-6">
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
            ایجاد وظیفه جدید
        </h1>

        @php
            use Modules\Tasks\Entities\Task;

            $currentUser    = auth()->user();

            $canAssign      = $canAssign
                ?? ($currentUser
                    && (
                        $currentUser->can('tasks.assign') ||
                        $currentUser->can('tasks.manage') ||
                        $currentUser->hasRole('super-admin')
                    ));

            // از داده‌های پاس‌شده از کنترلر استفاده می‌کنیم؛ در صورت نبود، از مدل
            $types          = $types          ?? Task::typeOptions();
            $statuses       = $statuses       ?? Task::statusOptions();
            $priorities     = $priorities     ?? Task::priorityOptions();
            $users          = $users          ?? collect();
            $roles          = $roles          ?? collect();
            $clients        = $clients        ?? collect();
            $clientStatuses = $clientStatuses ?? collect();

            $userOptions = $users->map(function ($u) {
                return [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email ?? '',
                ];
            });

            $clientOptions = $clients->map(function ($c) {
                return [
                    'id'    => $c->id,
                    'name'  => $c->full_name,
                    'phone' => $c->phone ?? '',
                ];
            });

            $alpineData = [
                'taskType'            => old('task_type', Task::TYPE_GENERAL),
                'assigneeMode'        => old('assignee_mode', 'single_user'),
                'relatedTarget'       => old('related_target', 'none'),
                'canAssign'           => (bool) $canAssign,
                'users'               => $userOptions,
                'clients'             => $clientOptions,
                'assigneeSearch'      => '',
                'relatedUserSearch'   => '',
                'relatedClientSearch' => '',
            ];
        @endphp

        <form method="POST"
              action="{{ route('user.tasks.store') }}"
              class="space-y-6"
              x-data='@json($alpineData)'>
            @csrf

            {{-- عنوان + نوع وظیفه --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">عنوان</label>
                    <input type="text"
                           name="title"
                           class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                              focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                              dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                           value="{{ old('title') }}"
                           required>
                    @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                        نوع وظیفه
                    </label>
                    <select name="task_type"
                            x-model="taskType"
                            class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                               dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @foreach($types as $value => $label)
                            @if($value === Task::TYPE_SYSTEM)
                                {{-- نوع سیستمی فقط توسط Workflow ساخته می‌شود، در فرم دستی نمایش نده --}}
                                @continue
                            @endif
                            <option value="{{ $value }}" @selected(old('task_type', Task::TYPE_GENERAL) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('task_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- توضیحات --}}
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                    توضیحات
                </label>
                <textarea name="description"
                          rows="3"
                          class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                             focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                             dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- وضعیت + اولویت --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                        وضعیت
                    </label>
                    <select name="status"
                            class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                               dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                        اولویت
                    </label>
                    <select name="priority"
                            class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                               dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- بخش مسئول (Assignee) --}}
            <div class="border border-gray-100 dark:border-gray-700 rounded-2xl p-4 bg-gray-50/60 dark:bg-gray-900/40 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        مسئول وظیفه
                    </h2>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                        در پیگیری‌ها (Follow-up) در صورت نداشتن دسترسی، مسئول به صورت خودکار خود شما خواهد بود.
                    </span>
                </div>

                {{-- در حالت Follow-up و نداشتن دسترسی برای تعیین مسئول --}}
                @if($currentUser)
                    <div x-show="taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign === false" x-cloak
                         class="text-xs text-gray-700 dark:text-gray-300 bg-white/60 dark:bg-gray-800/70 rounded-xl px-3 py-2">
                        <p>
                            مسئول این پیگیری به‌صورت خودکار
                            <span class="font-semibold">{{ $currentUser->name }}</span>
                            خواهد بود.
                        </p>
                        <input type="hidden" name="assignee_id" value="{{ $currentUser->id }}">
                    </div>
                @endif

                {{-- در حالت عمومی، یا Follow-up به‌همراه دسترسی تعیین مسئول --}}
                <div x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)"
                     x-cloak
                     class="space-y-3">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                روش انتخاب مسئول
                            </label>
                            <select name="assignee_mode"
                                    x-model="assigneeMode"
                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                       focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                       dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <option value="single_user">انتخاب یک کاربر مشخص</option>
                                <option value="by_roles">بر اساس نقش‌ها</option>
                            </select>
                        </div>
                    </div>

                    {{-- انتخاب کاربر مشخص --}}
                    <div x-show="assigneeMode === 'single_user'" x-cloak class="space-y-2">
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                            کاربر مسئول
                        </label>

                        <div class="space-y-2">
                            <input type="text"
                                   x-model="assigneeSearch"
                                   placeholder="جستجو بر اساس نام یا ایمیل..."
                                   class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs
                                      focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500
                                      dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">

                            <select name="assignee_id"
                                    size="5"
                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                       dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <template x-for="u in users.filter(user =>
                                    user.name.toLowerCase().includes(assigneeSearch.toLowerCase()) ||
                                    user.email.toLowerCase().includes(assigneeSearch.toLowerCase())
                                )"
                                          :key="u.id">
                                    <option :value="u.id"
                                            x-text="u.name + (u.email ? ' (' + u.email + ')' : '')"
                                    ></option>
                                </template>
                            </select>
                        </div>

                        @error('assignee_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- انتخاب بر اساس نقش --}}
                    <div x-show="assigneeMode === 'by_roles'" x-cloak class="space-y-2">
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                            نقش‌های مجاز به انجام این وظیفه
                        </label>
                        <select name="assignee_role_ids[]" multiple
                                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    @selected(in_array($role->id, (array) old('assignee_role_ids', [])))>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            منطق تبدیل نقش‌ها به کاربران مسئول (مثلاً ساخت وظیفه برای هر کاربرِ دارای نقش)
                            را می‌توانید بعداً در سرویس/کنترلر ماژول وظایف پیاده‌سازی کنید.
                        </p>
                    </div>
                </div>
            </div>

            {{-- موجودیت مرتبط (Related Entity) --}}
            <div class="border border-gray-100 dark:border-gray-700 rounded-2xl p-4 bg-gray-50/60 dark:bg-gray-900/40 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        موجودیت مرتبط
                    </h2>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                        می‌توانید این وظیفه را به یک کاربر یا مشتری خاص وصل کنید.
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                            نوع موجودیت
                        </label>
                        <select name="related_target"
                                x-model="relatedTarget"
                                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                   focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                   dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="none">بدون موجودیت مرتبط</option>
                            <option value="user">کاربر (User)</option>
                            <option value="client">مشتری (Client)</option>
                        </select>
                    </div>
                </div>

                {{-- موجودیت مرتبط: کاربران --}}
                <div x-show="relatedTarget === 'user'" x-cloak class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                نقش‌های مرتبط
                            </label>
                            <select name="related_user_role_ids[]" multiple
                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                       dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        @selected(in_array($role->id, (array) old('related_user_role_ids', [])))>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                انتخاب کاربر مشخص
                            </label>
                            <div class="space-y-2">
                                <input type="text"
                                       x-model="relatedUserSearch"
                                       placeholder="جستجو بر اساس نام یا ایمیل..."
                                       class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs
                                          focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500
                                          dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">

                                <select name="related_user_id"
                                        size="5"
                                        class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                           dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    <template x-for="u in users.filter(user =>
                                        user.name.toLowerCase().includes(relatedUserSearch.toLowerCase()) ||
                                        user.email.toLowerCase().includes(relatedUserSearch.toLowerCase())
                                    )"
                                              :key="u.id">
                                        <option :value="u.id"
                                                x-text="u.name + (u.email ? ' (' + u.email + ')' : '')"
                                        ></option>
                                    </template>
                                </select>
                            </div>
                            @error('related_user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- موجودیت مرتبط: مشتریان --}}
                <div x-show="relatedTarget === 'client'" x-cloak class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                وضعیت‌های مشتری
                            </label>
                            <select name="related_client_status_ids[]" multiple
                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                       dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                @foreach($clientStatuses as $st)
                                    <option value="{{ $st->id }}"
                                        @selected(in_array($st->id, (array) old('related_client_status_ids', [])))>
                                        {{ $st->label ?? $st->key }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                می‌توانید منطق ساخت وظیفه برای همه مشتریان با این وضعیت‌ها را بعداً
                                در سرویس ماژول وظایف پیاده کنید.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                انتخاب مشتری مشخص
                            </label>
                            <div class="space-y-2">
                                <input type="text"
                                       x-model="relatedClientSearch"
                                       placeholder="جستجو بر اساس نام یا شماره تماس..."
                                       class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs
                                          focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500
                                          dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">

                                <select name="related_client_id"
                                        size="5"
                                        class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
                                           dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    <template x-for="c in clients.filter(client =>
                                        client.name.toLowerCase().includes(relatedClientSearch.toLowerCase()) ||
                                        client.phone.toLowerCase().includes(relatedClientSearch.toLowerCase())
                                    )"
                                              :key="c.id">
                                        <option :value="c.id"
                                                x-text="c.name + (c.phone ? ' (' + c.phone + ')' : '')"
                                        ></option>
                                    </template>
                                </select>
                            </div>
                            @error('related_client_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- تاریخ سررسید (Jalali Datepicker) --}}
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                    تاریخ سررسید
                </label>
                <div class="relative">
                    <input
                        id="due_at_view"
                        name="due_at_view" {{-- 👈 اضافه شد --}}
                        type="text"
                        class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm
               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
               dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                        data-jdp
                        autocomplete="off"
                        placeholder="تاریخ را انتخاب کنید"
                    >
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    تاریخ به‌صورت شمسی انتخاب می‌شود، قبل از ذخیره به میلادی تبدیل می‌شود.
                </p>
                @error('due_at')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div class="flex items-center justify-end gap-2 pt-4">
                <a href="{{ route('user.tasks.index') }}"
                   class="px-4 py-2 text-sm rounded-xl border border-gray-300 text-gray-700 bg-white
                      hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    انصراف
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-xl bg-emerald-600 text-white hover:bg-emerald-700
                           shadow-sm hover:shadow-md transition">
                    ذخیره
                </button>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
