{{-- modules/FollowUps/resources/views/components/client-followup-manager.blade.php --}}

@props(['client'])

@once
<script>
    function singleSelect(config) {
        return {
            open: false,
            search: '',
            options: config.options || [],
            selectedValue: config.initialValue ? String(config.initialValue) : '',
            placeholder: config.placeholder || 'انتخاب کنید',

            init() {
                if (this.selectedValue && this.$refs.hidden) {
                    this.$refs.hidden.value = this.selectedValue;
                }
                const form = this.$el.closest('form');
                if (form) {
                    form.addEventListener('reset', () => {
                        this.selectedValue = config.initialValue ? String(config.initialValue) : '';
                        if (this.$refs.hidden) {
                            this.$refs.hidden.value = this.selectedValue;
                        }
                    });
                }
            },

            get selectedOption() {
                return this.options.find(o => String(o.value) === String(this.selectedValue)) || null;
            },

            get selectedLabel() {
                return this.selectedOption ? this.selectedOption.label : this.placeholder;
            },

            select(value) {
                this.selectedValue = String(value);
                if (this.$refs.hidden) {
                    this.$refs.hidden.value = this.selectedValue;
                }
                this.search = '';
                this.open = false;
            },

            filteredOptions() {
                const term = (this.search || '').toLowerCase();
                if (!term) return this.options;
                return this.options.filter(o => (o.label || '').toLowerCase().includes(term));
            }
        }
    }

    window.clientFollowUpManager = function (followUpsCount, recentFollowUps, clientId, statusOptions, defaultJalaliToday) {
        return {
            openFollowUpsModal: false,
            submitting: false,
            followUpsCount: followUpsCount,
            recentFollowUps: recentFollowUps,
            clientId: clientId,
            statusOptions: statusOptions,
            defaultJalaliToday: defaultJalaliToday,

            handleOpenFollowUp(event) {
                if (event.detail.clientId == clientId) {
                    this.openFollowUpsModal = true;
                }
            },

            async handleSubmit(event) {
                this.submitting = true;

                const form = event.target;
                const formData = new FormData(form);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('خطا در ثبت پیگیری');
                    }

                    const data = await response.json();

                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { type: 'success', text: data.message || 'پیگیری با موفقیت ثبت شد' }
                    }));

                    // Increment the counter reactively
                    this.followUpsCount = (this.followUpsCount || 0) + 1;

                    // Unshift new follow-up to history list reactively
                    const statusKey = formData.get('status') || 'todo';
                    this.recentFollowUps.unshift({
                        title: formData.get('title'),
                        description: formData.get('description'),
                        status: statusKey,
                        status_label: statusOptions[statusKey] || statusKey,
                        date_text: formData.get('due_at_view') || '—',
                    });

                    if (this.recentFollowUps.length > 5) {
                        this.recentFollowUps.pop();
                    }

                    // reset form
                    form.reset();

                    // reset date to today
                    if (form.due_at_view) {
                        form.due_at_view.value = defaultJalaliToday;
                    }

                } catch (e) {
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { type: 'error', text: 'خطا در ثبت پیگیری. دوباره تلاش کنید.' }
                    }));
                } finally {
                    this.submitting = false;
                }
            }
        };
    };
</script>
@endonce

@php
    /** @var \Modules\Clients\Entities\Client $client */

    use Modules\FollowUps\Entities\FollowUp;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;
    use Illuminate\Support\Js;
    use App\Models\User;

    $currentUser = auth()->user();

    // همه پیگیری‌های مرتبط با این کلاینت
    $followUpsQuery = FollowUp::query()
        ->where('related_type', Task::RELATED_TYPE_CLIENT)
        ->where('related_id', $client->id)
        ->orderByDesc('due_at')
        ->orderByDesc('created_at');

    $followUps      = $followUpsQuery->get();
    $followUpsCount = $followUps->count();

    $recentFollowUps = $followUps->take(5);

    // تاریخ امروز به شمسی برای مقدار اولیه
    $defaultJalaliToday = Jalalian::fromCarbon(now())->format('Y/m/d');

    $statusOptions   = Task::statusOptions();
    $priorityOptions = Task::priorityOptions();

    // آیا کاربر حق انتخاب مسئول دارد؟
    $canAssignFollowup = $currentUser && (
        $currentUser->can('followups.manage')
        || $currentUser->hasRole('super-admin')
        || $currentUser->can('tasks.assign')
        || $currentUser->can('tasks.manage')
    );

    // لیست کاربران فقط اگر نیاز است
    $users = $canAssignFollowup
        ? User::select('id', 'name', 'email')->orderBy('name')->get()
        : collect($currentUser ? [$currentUser] : []);

    $userSelectOptions = $users->map(function ($u) {
        return [
            'value' => (string) $u->id,
            'label' => $u->name . ($u->email ? ' (' . $u->email . ')' : ''),
        ];
    })->values()->all();

    $initialAssigneeId = old('assignee_id', optional($currentUser)->id);

    $recentFollowUpsMapped = $recentFollowUps->map(function ($item) use ($statusOptions) {
        return [
            'title' => $item->title,
            'description' => $item->description,
            'status' => $item->status,
            'status_label' => $statusOptions[$item->status] ?? $item->status,
            'date_text' => $item->due_at ? \Morilog\Jalali\Jalalian::fromCarbon($item->due_at)->format('Y/m/d') : '—',
        ];
    })->values()->all();
@endphp

@includeIf('partials.jalali-date-picker')

<div x-data="clientFollowUpManager(
    {{ $followUpsCount }},
    {{ Js::from($recentFollowUpsMapped) }},
    {{ $client->id }},
    {{ Js::from($statusOptions) }},
    {{ Js::from($defaultJalaliToday) }}
)" @open-client-followup.window="handleOpenFollowUp($event)">
    {{-- دکمه باز کردن مودال --}}
    <button type="button"
            @click="openFollowUpsModal = true"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-[11px]
                   bg-white border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300
                   dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-600
                   transition-colors">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                     bg-amber-100 text-amber-700 text-[10px]
                     dark:bg-amber-900/40 dark:text-amber-300"
              x-text="followUpsCount">
            {{ $followUpsCount }}
        </span>
        <span>مدیریت پیگیری‌ها</span>
    </button>

    {{-- مودال --}}
    <div x-show="openFollowUpsModal"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-40 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">
        <div class="absolute inset-0" @click="openFollowUpsModal = false"></div>

        <div class="relative z-50 w-full max-w-4xl mx-auto
                    bg-white rounded-2xl shadow-2xl border border-gray-200
                    dark:bg-gray-900 dark:border-gray-700">

            {{-- هدر --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-2xl
                                bg-amber-50 text-amber-600
                                dark:bg-amber-900/40 dark:text-amber-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                  d="M9 12l2 2 4-4M7 7h10a2 2 0 012 2v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            مدیریت پیگیری‌های {{ $client->full_name ?: $client->username }}
                        </h2>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            مجموع پیگیری‌های ثبت‌شده: <span class="font-semibold" x-text="followUpsCount">{{ $followUpsCount }}</span>
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="openFollowUpsModal = false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl
                               text-gray-400 hover:text-gray-700 hover:bg-gray-100
                               dark:text-gray-500 dark:hover:text-gray-200 dark:hover:bg-gray-800
                               transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- بدنه: فرم + تاریخچه --}}
            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- فرم ثبت پیگیری جدید --}}
                <div class="md:col-span-4 space-y-3 text-[11px]">
                    <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                        ثبت پیگیری جدید
                    </h3>

                    @can('followups.create')
                        <form method="POST"
                              action="{{ route('user.followups.quick-store') }}"
                              class="space-y-3"
                              @submit.prevent="handleSubmit($event)">
                            @csrf

                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            {{-- client به عنوان related --}}
                            <input type="hidden" name="related_type" value="{{ Task::RELATED_TYPE_CLIENT }}">
                            <input type="hidden" name="related_id" value="{{ $client->id }}">

                            {{-- مسئول --}}
                            @if($currentUser)
                                @if($canAssignFollowup)
                                    <div>
                                        <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                            مسئول پیگیری
                                        </label>

                                        <div
                                            x-data="singleSelect({
                        options: {{ Js::from($userSelectOptions) }},
                        initialValue: '{{ $initialAssigneeId }}',
                        placeholder: 'انتخاب مسئول...'
                    })"
                                            class="relative"
                                        >
                                            <input type="hidden" name="assignee_id" x-ref="hidden">

                                            <button type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-750">
                                                <span x-text="selectedLabel"></span>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="1.5"
                                                          d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="open"
                                                 x-cloak
                                                 @click.outside="open = false"
                                                 class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:bg-gray-800 dark:border-gray-700">
                                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                                    <input type="text"
                                                           x-model="search"
                                                           placeholder="جستجو..."
                                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-[11px] text-gray-700 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                                </div>
                                                <ul class="max-h-56 overflow-auto text-xs">
                                                    <template x-for="opt in filteredOptions()" :key="opt.value">
                                                        <li>
                                                            <button type="button"
                                                                    @click="select(opt.value)"
                                                                    class="w-full text-right px-3 py-1.5 hover:bg-gray-50 text-gray-700 dark:text-gray-100 dark:hover:bg-gray-900">
                                                                <span x-text="opt.label"></span>
                                                            </button>
                                                        </li>
                                                    </template>
                                                    <template x-if="filteredOptions().length === 0">
                                                        <li class="px-3 py-2 text-[11px] text-gray-400 dark:text-gray-500">
                                                            موردی یافت نشد.
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>

                                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                                            در صورت عدم تغییر، مسئول پیش‌فرض: {{ $currentUser->name }}
                                        </p>
                                    </div>
                                @else
                                    <input type="hidden" name="assignee_id" value="{{ $currentUser->id }}">
                                    <div
                                        class="flex items-center gap-2 p-2.5 mt-1 rounded-xl bg-blue-50 text-blue-800 border border-blue-100 text-[11px]
                                        dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800/30">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p>
                                            شما مجوز انتخاب مسئول را ندارید. این پیگیری به‌صورت خودکار به نام
                                            <span class="font-semibold">{{ $currentUser->name }}</span> ثبت می‌شود.
                                        </p>
                                    </div>
                                @endif
                            @endif

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    عنوان پیگیری <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="title"
                                       required
                                       class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                              placeholder-gray-400
                                              focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                              dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20">
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    وضعیت
                                </label>
                                <select name="status"
                                        class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                               focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                               dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::STATUS_TODO) class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    اولویت
                                </label>
                                <select name="priority"
                                        class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                               focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                               dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20">
                                    @foreach($priorityOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM) class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    تاریخ و ساعت سررسید
                                </label>
                                <div class="flex gap-2">
                                    <input type="text"
                                           name="due_at_view"
                                           data-jdp-only-date
                                           data-jdp-min-date="today"
                                           value="{{ $defaultJalaliToday }}"
                                           class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                              focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                              dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20"
                                    >

                                    <input type="text"
                                       data-jdp-only-time
                                       placeholder="00:00"
                                       name="due_time"
                                       value="{{ old('due_time') }}"
                                       class="w-24 h-9 rounded-xl border-gray-200 bg-gray-50 px-2 text-xs text-center text-gray-900
                                          focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                          dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    توضیحات
                                </label>
                                <textarea name="description" rows="3"
                                          class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                                 placeholder-gray-400
                                                 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                                 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-750 dark:focus:ring-amber-500/20"
                                          placeholder="شرح کوتاهی از پیگیری وارد کنید..."></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button"
                                        @click="openFollowUpsModal = false"
                                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-[11px] text-gray-700 bg-white hover:bg-gray-50
                                               dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700
                                               transition-colors">
                                    بستن
                                </button>
                                <button type="submit"
                                        :disabled="submitting"
                                        class="px-4 py-1.5 rounded-xl bg-amber-500 text-white text-[11px] font-medium
                                               hover:bg-amber-600 shadow-md shadow-amber-500/20
                                               transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span x-show="!submitting">ثبت پیگیری</span>
                                    <span x-show="submitting" class="flex items-center gap-1">
                                        <svg class="w-3 h-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2
                                                     5.291A7.962 7.962 0 014 12H0c0
                                                     3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>در حال ثبت...</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            شما مجوز ثبت پیگیری برای این مشتری را ندارید.
                        </div>
                    @endcan
                </div>

                {{-- ستون تاریخچه پیگیری‌های اخیر --}}
                <div class="md:col-span-8 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            پیگیری‌های اخیر
                        </h3>
                        @can('followups.view')
                            <a href="{{ route('user.followups.index', [
                                        'related_type' => Task::RELATED_TYPE_CLIENT,
                                        'related_id'   => $client->id,
                                    ]) }}"
                               class="inline-flex items-center gap-1 text-[11px] text-amber-600 hover:text-amber-700
                                      dark:text-amber-400 dark:hover:text-amber-300"
                               target="_blank">
                                <span>مشاهده در صفحه کامل</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                          d="M13 5h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endcan
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/60 max-h-72 overflow-auto
                                dark:border-gray-800 dark:bg-gray-900/40">
                        <div x-show="recentFollowUps.length === 0"
                             class="flex flex-col items-center justify-center py-8 text-[11px] text-gray-500 dark:text-gray-400">
                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 8v4l3 3"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                            </svg>
                            <p>هنوز پیگیری‌ای برای این مشتری ثبت نشده است.</p>
                        </div>
                        <ul x-show="recentFollowUps.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="item in recentFollowUps">
                                <li class="px-3 py-2.5 text-[11px] flex items-start justify-between gap-2">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border"
                                                :class="item.status === 'done'
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700'
                                                    : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700'"
                                                x-text="item.status_label">
                                            </span>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400 text-left dir-ltr" x-text="item.date_text">
                                            </div>
                                        </div>

                                        <div class="text-gray-700 dark:text-gray-200 truncate"
                                             :title="item.title" x-text="item.title">
                                        </div>
                                        <div x-show="item.description" class="text-gray-500 dark:text-gray-300 line-clamp-2"
                                             :title="item.description" x-text="item.description">
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
