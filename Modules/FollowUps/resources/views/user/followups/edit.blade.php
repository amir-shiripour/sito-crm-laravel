@extends('layouts.user')

@section('content')
    {{-- همان کامپوننت singleSelect که در صفحه create استفاده کردیم --}}
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

    @php
        use Modules\Tasks\Entities\Task;
        use Illuminate\Support\Js;
        use Morilog\Jalali\Jalalian;

        $currentUser = auth()->user();

        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $users      = $users      ?? collect();
        $clients    = $clients    ?? collect();

        // گزینه‌های مسئول
        $userSelectOptions = $users->map(function ($u) {
            return [
                'value' => (string) $u->id,
                'label' => $u->name . ($u->email ? ' (' . $u->email . ')' : ''),
            ];
        })->values()->all();

        // گزینه‌های مشتری
        $clientSelectOptions = $clients->map(function ($c) {
            return [
                'value' => (string) $c->id,
                'label' => $c->full_name . ($c->phone ? ' (' . $c->phone . ')' : ''),
            ];
        })->values()->all();

        $relatedClient = $relatedClient ?? null;

        $initialRelatedId = old(
            'related_id',
            $relatedClient->id
                ?? ($followUp->related_type === Task::RELATED_TYPE_CLIENT ? $followUp->related_id : '')
        );

        $initialAssigneeId = old('assignee_id', $followUp->assignee_id ?? ($currentUser->id ?? ''));

        $initialDueAtView = old(
            'due_at_view',
            $followUp->due_at ? Jalalian::fromCarbon($followUp->due_at)->format('Y/m/d') : ''
        );

        $initialDueTime = old(
            'due_time',
            $followUp->due_at ? $followUp->due_at->format('H:i') : ''
        );

    @endphp

    <div class="w-full max-w-5xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-8 bg-amber-500 rounded-full hidden sm:block"></span>
                    ویرایش پیگیری
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:pr-4">
                    اطلاعات این پیگیری را ویرایش کنید.
                </p>
            </div>
            <a href="{{ route('user.followups.show', $followUp) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به جزئیات
            </a>
        </div>

        <form method="POST" action="{{ route('user.followups.update', $followUp) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- ستون اصلی --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- کارت اطلاعات اصلی --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </span>
                            اطلاعات پایه
                        </h3>

                        <div class="space-y-4">
                            {{-- عنوان --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    عنوان پیگیری <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title"
                                       value="{{ old('title', $followUp->title) }}" required
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-all focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900 dark:focus:border-amber-500/50">
                                @error('title')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- توضیحات --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    توضیحات تکمیلی
                                </label>
                                <textarea name="description" rows="4"
                                          class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-all focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900 dark:focus:border-amber-500/50"
                                          placeholder="شرح مختصری از این پیگیری بنویسید...">{{ old('description', $followUp->description) }}</textarea>
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
                            {{-- وضعیت --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    وضعیت
                                </label>
                                <div class="relative">
                                    <select name="status"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $followUp->status) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- اولویت --}}
                            <div>
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    اولویت
                                </label>
                                <div class="relative">
                                    <select name="priority"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm appearance-none cursor-pointer focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        @foreach($priorities as $value => $label)
                                            <option value="{{ $value }}" @selected(old('priority', $followUp->priority) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- موعد انجام (شمسی) + ساعت --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                    تاریخ و ساعت سررسید
                                </label>
                                <div class="flex gap-3">
                                    {{-- تاریخ --}}
                                    <div class="relative flex-1">
                                        <input type="text"
                                               name="due_at_view"
                                               data-jdp-only-date
                                               value="{{ $initialDueAtView }}"
                                               placeholder="انتخاب تاریخ..."
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-center focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>

                                    {{-- ساعت (اختیاری) --}}
                                    <div class="relative w-32">
                                        <input type="text"
                                               data-jdp-only-time
                                               name="due_time"
                                               placeholder="00:00"
                                               value="{{ $initialDueTime }}"
                                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-center dir-ltr focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 dark:bg-gray-900/50 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900">
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
                    {{-- کارت مسئول --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </span>
                            مسئول پیگیری
                        </h3>

                        @if(!empty($canAssign) && $canAssign)
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    انتخاب مسئول
                                </label>

                                <div
                                    x-data="singleSelect({
                                        options: {{ Js::from($userSelectOptions) }},
                                        initialValue: '{{ $initialAssigneeId }}',
                                        placeholder: 'جستجو و انتخاب...'
                                    })"
                                    class="relative"
                                >
                                    <input type="hidden" name="assignee_id" x-ref="hidden">

                                    <button type="button"
                                            @click="open = !open"
                                            class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 hover:bg-white hover:border-indigo-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:bg-gray-900/50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-900">
                                        <span x-text="selectedLabel" class="truncate"></span>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         @click.outside="open = false"
                                         class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                            <input type="text"
                                                   x-model="search"
                                                   placeholder="جستجو..."
                                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100">
                                        </div>
                                        <ul class="max-h-48 overflow-auto text-sm">
                                            <template x-for="opt in filteredOptions()" :key="opt.value">
                                                <li>
                                                    <button type="button"
                                                            @click="select(opt.value)"
                                                            class="w-full text-right px-3 py-2 hover:bg-indigo-50 text-gray-700 dark:text-gray-100 dark:hover:bg-indigo-900/30 transition-colors">
                                                        <span x-text="opt.label"></span>
                                                    </button>
                                                </li>
                                            </template>
                                            <template x-if="filteredOptions().length === 0">
                                                <li class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500 text-center">
                                                    موردی یافت نشد.
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>

                                <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed">
                                    فقط مدیران می‌توانند مسئول را تغییر دهند.
                                </p>
                            </div>
                        @else
                            @if($currentUser)
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100 dark:bg-gray-900/30 dark:border-gray-700/50">
                                    <div class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">مسئول فعلی</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ optional($followUp->assignee)->name ?? $currentUser->name }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- کارت موجودیت مرتبط (Client) --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </span>
                            مشتری مرتبط
                        </h3>

                        <input type="hidden" name="related_type" value="{{ \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT }}">

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                انتخاب {{ config('clients.labels.singular', 'مشتری') }}
                            </label>

                            <div
                                x-data="singleSelect({
                                    options: {{ Js::from($clientSelectOptions) }},
                                    initialValue: '{{ $initialRelatedId }}',
                                    placeholder: 'جستجو و انتخاب...'
                                })"
                                class="relative"
                            >
                                <input type="hidden" name="related_id" x-ref="hidden">

                                <button type="button"
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 hover:bg-white hover:border-pink-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all dark:bg-gray-900/50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-900">
                                    <span x-text="selectedLabel" class="truncate"></span>
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open"
                                     x-cloak
                                     @click.outside="open = false"
                                     class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                        <input type="text"
                                               x-model="search"
                                               placeholder="جستجو..."
                                               class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100">
                                    </div>
                                    <ul class="max-h-48 overflow-auto text-sm">
                                        <template x-for="opt in filteredOptions()" :key="opt.value">
                                            <li>
                                                <button type="button"
                                                        @click="select(opt.value)"
                                                        class="w-full text-right px-3 py-2 hover:bg-pink-50 text-gray-700 dark:text-gray-100 dark:hover:bg-pink-900/30 transition-colors">
                                                    <span x-text="opt.label"></span>
                                                </button>
                                            </li>
                                        </template>
                                        <template x-if="filteredOptions().length === 0">
                                            <li class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500 text-center">
                                                موردی یافت نشد.
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed">
                                اتصال به مشتری اختیاری است.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.followups.show', $followUp) }}"
                   class="px-6 py-2.5 text-sm font-bold rounded-xl border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-800 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    انصراف
                </a>
                <button type="submit"
                        class="px-8 py-2.5 text-sm font-bold rounded-xl bg-amber-500 text-white hover:bg-amber-600 shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 transition-all active:scale-95">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
