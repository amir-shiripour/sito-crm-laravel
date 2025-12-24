{{-- modules/FollowUps/resources/views/user/widgets/quick-followup.blade.php --}}

@php
    use Morilog\Jalali\Jalalian;
    use Modules\Tasks\Entities\Task;
    use App\Models\User;
    use Illuminate\Support\Js;

    $todayJalali = Jalalian::fromCarbon(now())->format('Y/m/d');

    $statusOptions   = Task::statusOptions();
    $priorityOptions = Task::priorityOptions();

    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $currentUser = auth()->user();

    $canAssign = $currentUser && (
        $currentUser->can('tasks.assign') ||
        $currentUser->can('tasks.manage') ||
        $currentUser->hasRole('super-admin') ||
        $currentUser->can('followups.manage')
    );

    // لیست کاربران برای انتخاب مسئول (در صورت داشتن دسترسی)
    $assigneeUsers = $canAssign
        ? User::select('id', 'name', 'email')->orderBy('name')->get()
        : collect();

    // گزینه‌ها برای singleSelect
    $assigneeOptions = $assigneeUsers->map(function ($u) {
        return [
            'value' => (string) $u->id,
            'label' => $u->name . ($u->email ? ' ('.$u->email.')' : ''),
        ];
    })->values()->all();

    $initialAssigneeId = optional($currentUser)->id;
@endphp

@includeIf('partials.jalali-date-picker')

{{-- کامپوننت singleSelect برای دراپ‌داون سرچ‌دار مسئول --}}
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
</script>

<div x-data="quickFollowupWidget()"
     class="h-full space-y-4 text-sm text-gray-800 dark:text-gray-200">

    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3M5 21h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 13h6m-6 4h3"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">ثبت پیگیری سریع</h2>
        </div>
    </div>

    <form method="POST"
          action="{{ route('user.followups.quick-store') }}"
          class="space-y-4"
          @submit.prevent="handleSubmit($event)">
        @csrf

        {{-- انتخاب مشتری --}}
        <div class="relative z-20">
            <label class="{{ $labelClass }}">مشتری <span class="text-red-500">*</span></label>

            {{-- سرچ --}}
            <div class="relative" x-show="!selectedClient">
                <input type="text"
                       x-model="searchQuery"
                       @input.debounce.400ms="searchClients"
                       placeholder="جستجوی نام، شماره تماس..."
                       class="{{ $inputClass }} pr-9">
                <div class="absolute right-3 top-2.5 text-gray-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- مشتری انتخاب شده --}}
            <template x-if="selectedClient">
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800 animate-in fade-in slide-in-from-top-1">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center text-xs font-bold dark:bg-emerald-800 dark:text-emerald-200">
                            <span x-text="(selectedClient.full_name || selectedClient.username).charAt(0)"></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200" x-text="selectedClient.full_name || selectedClient.username"></span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 dir-ltr text-right" x-text="selectedClient.phone"></span>
                        </div>
                    </div>
                    <button type="button" @click="clearSelected()" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-100 dark:text-emerald-400 dark:hover:bg-emerald-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            {{-- دراپ‌داون نتایج --}}
            <div x-show="showResults"
                 x-transition
                 @click.away="showResults = false"
                 class="absolute top-full left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800 z-50 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">

                <template x-if="loading">
                    <div class="p-4 text-center text-xs text-gray-500">
                        <svg class="animate-spin h-4 w-4 mx-auto mb-2 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال جستجو...
                    </div>
                </template>

                <template x-if="!loading && results.length === 0">
                    <div class="p-3 text-center text-xs text-gray-500">موردی یافت نشد.</div>
                </template>

                <template x-for="client in results" :key="client.id">
                    <button type="button"
                            @click="selectClient(client)"
                            class="w-full flex items-center justify-between px-4 py-3 text-right hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                        <span class="text-xs font-medium text-gray-900 dark:text-white" x-text="client.full_name || client.username"></span>
                        <span class="text-[10px] text-gray-500 dir-ltr" x-text="client.phone"></span>
                    </button>
                </template>
            </div>

            <input type="hidden" name="client_id" :value="selectedClient?.id">
            <p class="text-[10px] text-red-500 mt-1" x-show="submitError && !selectedClient" x-transition>
                لطفاً یک مشتری را انتخاب کنید.
            </p>
        </div>

        {{-- عنوان و تاریخ --}}
        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="{{ $labelClass }}">عنوان پیگیری <span class="text-red-500">*</span></label>
                <input type="text" name="title" placeholder="مثلاً: پیگیری ارسال پیش‌فاکتور" required class="{{ $inputClass }}">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelClass }}">تاریخ و ساعت سررسید</label>
                    <div class="flex gap-2">
                        <input type="text"
                               name="due_at_view"
                               data-jdp-only-date
                               value="{{ $todayJalali }}"
                               class="{{ $inputClass }} text-center flex-1">

                        <input type="text"
                               data-jdp-only-time
                               placeholder="00:00"
                               name="due_time"
                               value="{{ old('due_time') }}"
                               class="w-24 rounded-xl border-gray-200 bg-gray-50 px-2 py-2.5 text-xs font-medium text-gray-900 text-center focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900">
                    </div>
                </div>
                <div>
                    <label class="{{ $labelClass }}">اولویت</label>
                    <select name="priority" class="{{ $inputClass }} appearance-none">
                        @foreach($priorityOptions as $value => $label)
                            <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- مسئول پیگیری --}}
        <div class="grid grid-cols-1 gap-3">
            @if($canAssign && $assigneeUsers->isNotEmpty())
                <div>
                    <label class="{{ $labelClass }}">مسئول پیگیری</label>

                    <div
                        x-data="singleSelect({
                            options: {{ Js::from($assigneeOptions) }},
                            initialValue: '{{ (string) $initialAssigneeId }}',
                            placeholder: 'انتخاب مسئول...'
                        })"
                        class="relative"
                    >
                        <input type="hidden" name="assignee_id" x-ref="hidden">

                        <button type="button"
                                @click="open = !open"
                                class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs text-gray-700 hover:bg-gray-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-800">
                            <span x-text="selectedLabel"></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-cloak
                             @click.outside="open = false"
                             class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:bg-gray-900 dark:border-gray-700">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                <input type="text"
                                       x-model="search"
                                       placeholder="جستجو..."
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-[11px] text-gray-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                            </div>
                            <ul class="max-h-56 overflow-auto text-xs">
                                <template x-for="opt in filteredOptions()" :key="opt.value">
                                    <li>
                                        <button type="button"
                                                @click="select(opt.value)"
                                                class="w-full text-right px-3 py-2 hover:bg-gray-50 text-gray-700 dark:text-gray-100 dark:hover:bg-gray-800">
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
                        در صورت نیاز می‌توانید پیگیری را به کاربر دیگری واگذار کنید.
                    </p>
                </div>
            @else
                {{-- اگر اجازه‌ی تغییر مسئول ندارد → خودش مسئول است --}}
                @if($currentUser)
                    <input type="hidden" name="assignee_id" value="{{ $currentUser->id }}">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 border border-blue-100 text-[11px] text-blue-800 dark:bg-blue-900/20 dark:border-blue-800/40 dark:text-blue-200">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            مسئول این پیگیری به‌صورت خودکار
                            <span class="font-semibold">{{ $currentUser->name }}</span>
                            خواهد بود.
                        </span>
                    </div>
                @endif
            @endif
        </div>

        {{-- وضعیت و توضیحات --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $labelClass }}">وضعیت</label>
                <select name="status" class="{{ $inputClass }} appearance-none">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::STATUS_TODO)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">توضیح کوتاه</label>
                <input type="text" name="description" placeholder="توضیح کوتاه..." class="{{ $inputClass }}">
            </div>
        </div>

        {{-- دکمه ثبت --}}
        <div class="pt-2">
            <button type="submit"
                    :disabled="submitting"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-white shadow-lg shadow-amber-500/30 hover:bg-amber-600 hover:shadow-amber-500/40 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                <span x-show="!submitting">ثبت پیگیری</span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    در حال ثبت
                </span>
            </button>
        </div>
    </form>
</div>

<script>
    function quickFollowupWidget() {
        return {
            searchQuery: '',
            results: [],
            loading: false,
            showResults: false,
            selectedClient: null,
            submitError: false,
            submitting: false,

            async searchClients() {
                if (this.searchQuery.length < 2) {
                    this.results = [];
                    this.showResults = false;
                    return;
                }

                this.loading = true;
                this.showResults = true;

                try {
                    const response = await fetch(`{{ route('user.clients.search') }}?q=${encodeURIComponent(this.searchQuery)}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('خطا در ارتباط');

                    const data = await response.json();
                    this.results = data.data || data;
                } catch (e) {
                    console.error(e);
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            selectClient(client) {
                this.selectedClient = client;
                this.showResults = false;
                this.results = [];
                this.searchQuery = '';
            },

            clearSelected() {
                this.selectedClient = null;
            },

            notify(type, text) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type, text } }));
            },

            async handleSubmit(event) {
                this.submitError = false;

                if (!this.selectedClient) {
                    this.submitError = true;
                    this.notify('error', 'لطفاً یک مشتری را انتخاب کنید');
                    return;
                }

                this.submitting = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('client_id', this.selectedClient.id);

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

                    if (!response.ok) throw new Error('خطا در ثبت');

                    const data = await response.json();
                    this.notify('success', data.message || 'پیگیری با موفقیت ثبت شد');

                    // فرم رو ریست کن، ولی مشتری انتخاب‌شده رو نگه دار
                    const currentClient = this.selectedClient;
                    form.reset();
                    this.selectedClient = currentClient;

                    // تاریخ رو به امروز برگردون
                    if (form.due_at_view) {
                        form.due_at_view.value = '{{ $todayJalali }}';
                    }

                } catch (e) {
                    this.notify('error', 'خطا در عملیات. مجدد تلاش کنید.');
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
