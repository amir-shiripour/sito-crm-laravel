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
    @endphp

    <div class="max-w-3xl mx-auto px-4 py-8">
        {{-- هدر --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    ویرایش پیگیری
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    اطلاعات این پیگیری را ویرایش کنید.
                </p>
            </div>
            <a href="{{ route('user.followups.show', $followUp) }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                بازگشت به جزئیات
            </a>
        </div>

        <form method="POST" action="{{ route('user.followups.update', $followUp) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- کارت اطلاعات اصلی --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items:center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    اطلاعات پایه پیگیری
                </h3>

                <div class="space-y-4">
                    {{-- عنوان --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            عنوان پیگیری <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title"
                               value="{{ old('title', $followUp->title) }}" required
                               class="w-full rounded-xl border-gray-300 bg:white px-4 py-2.5 text-sm transition-shadow focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:border-amber-500">
                        @error('title')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- توضیحات --}}
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            توضیحات
                        </label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-xl border-gray-300 bg:white px-4 py-2.5 text-sm transition-shadow focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:border-amber-500"
                                  placeholder="شرح مختصری از این پیگیری بنویسید...">{{ old('description', $followUp->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- وضعیت --}}
                        <div>
                            <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                وضعیت
                            </label>
                            <select name="status"
                                    class="w-full rounded-xl border-gray-300 bg:white px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $followUp->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- اولویت --}}
                        <div>
                            <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                اولویت
                            </label>
                            <select name="priority"
                                    class="w-full rounded-xl border-gray-300 bg:white px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', $followUp->priority) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- موعد انجام (شمسی) --}}
                        <div>
                            <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                                تاریخ سررسید
                            </label>
                            <input type="text"
                                   name="due_at_view"
                                   data-jdp-only-date
                                   value="{{ $initialDueAtView }}"
                                   placeholder="انتخاب تاریخ..."
                                   class="w-full rounded-xl border-gray-300 bg:white px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            @error('due_at')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت مسئول --}}
            <div class="bg-gray-50/80 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items:center gap-2">
                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                    مسئول پیگیری
                </h3>

                @if(!empty($canAssign) && $canAssign)
                    <div class="space-y-2">
                        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                            انتخاب مسئول
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
                                    class="w-full flex items:center justify-between rounded-xl border border-gray-300 bg:white px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-800">
                                <span x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-cloak
                                 @click.outside="open = false"
                                 class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg:white shadow-lg dark:bg-gray-900 dark:border-gray-700">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                    <input type="text"
                                           x-model="search"
                                           placeholder="جستجو..."
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                                </div>
                                <ul class="max-h-56 overflow-auto text-sm">
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
                                        <li class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">
                                            موردی یافت نشد.
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                @else
                    @if($currentUser)
                        <div
                            class="flex items:center gap-3 p-3 rounded-xl bg-blue-50 text-blue-800 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800/30">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">
                                شما مجوز تغییر مسئول را ندارید. مسئول فعلی:
                                <span class="font-bold">
                                    {{ optional($followUp->assignee)->name ?? $currentUser->name }}
                                </span>
                            </p>
                        </div>
                    @endif
                @endif
            </div>

            {{-- کارت موجودیت مرتبط (Client) --}}
            <div class="bg-gray-50/80 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items:center gap-2">
                    <span class="w-1 h-4 bg-pink-500 rounded-full"></span>
                    موجودیت مرتبط (مشتری)
                </h3>

                {{-- نوع موجودیت همیشه CLIENT است و نمایش داده نمی‌شود --}}
                <input type="hidden" name="related_type" value="{{ \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT }}">

                <div class="space-y-2">
                    <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">
                        انتخاب {{ config('clients.labels.singular', 'مشتری') }}
                    </label>

                    <div
                        x-data="singleSelect({
                            options: {{ Js::from($clientSelectOptions) }},
                            initialValue: '{{ $initialRelatedId }}',
                            placeholder: 'انتخاب {{ config('clients.labels.singular', 'مشتری') }}...'
                        })"
                        class="relative"
                    >
                        <input type="hidden" name="related_id" x-ref="hidden">

                        <button type="button"
                                @click="open = !open"
                                class="w-full flex items:center justify-between rounded-xl border border-gray-300 bg:white px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-800">
                            <span x-text="selectedLabel"></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-cloak
                             @click.outside="open = false"
                             class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg:white shadow-lg dark:bg-gray-900 dark:border-gray-700">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                <input type="text"
                                       x-model="search"
                                       placeholder="جستجو در نام/تلفن..."
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                            </div>
                            <ul class="max-h-56 overflow-auto text-sm">
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
                                    <li class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">
                                        مشتری‌ای یافت نشد.
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        این پیگیری به {{ config('clients.labels.singular', 'مشتری') }} انتخاب‌شده متصل خواهد شد.
                    </p>
                </div>
            </div>

            {{-- دکمه‌ها --}}
            <div class="flex items:center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('user.followups.show', $followUp) }}"
                   class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-300 text-gray-700 bg:white hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    انصراف
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-bold rounded-xl bg-amber-500 text-white hover:bg-amber-600 shadow-lg shadow-amber-500/30 transition-all">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
