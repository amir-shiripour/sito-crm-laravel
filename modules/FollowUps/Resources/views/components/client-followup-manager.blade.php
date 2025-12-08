{{-- resources/views/components/client-followup-manager.blade.php --}}

@props(['client'])

@php
    /** @var \Modules\Clients\Entities\Client $client */

    use Modules\FollowUps\Entities\FollowUp;
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

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
@endphp

@includeIf('partials.jalali-date-picker')

<div x-data="{ openFollowUpsModal: false }">
    {{-- دکمه باز کردن مودال --}}
    <button type="button"
            @click="openFollowUpsModal = true"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-[11px]
                   bg-white border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300
                   dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-600
                   transition-colors">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                     bg-amber-100 text-amber-700 text-[10px]
                     dark:bg-amber-900/40 dark:text-amber-300">
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
                                  d="M9 12l2 2 4-4M7 7h10a2 2 0 012 2v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            مدیریت پیگیری‌های {{ $client->full_name ?: $client->username }}
                        </h2>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            مجموع پیگیری‌های ثبت‌شده: <span class="font-semibold">{{ $followUpsCount }}</span>
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
                              d="M6 18L18 6M6 6l12 12" />
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
                        <form method="POST" action="{{ route('user.followups.store') }}" class="space-y-3">
                            @csrf

                            {{-- client به عنوان related --}}
                            <input type="hidden" name="related_type" value="{{ Task::RELATED_TYPE_CLIENT }}">
                            <input type="hidden" name="related_id" value="{{ $client->id }}">

                            {{-- مسئول: دیفالت کاربر فعلی (در کنترلر اگر خالی بود هم می‌توان هندل کرد) --}}

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
                                              dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    وضعیت
                                </label>
                                <select name="status"
                                        class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                               focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                               dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::STATUS_TODO)">
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
                                               dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                                    @foreach($priorityOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($value === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)">
                                        {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    تاریخ سررسید
                                </label>
                                <input type="text"
                                       name="due_at_view"
                                       data-jdp-only-date
                                       value="{{ $defaultJalaliToday }}"
                                       class="w-full h-9 rounded-xl border-gray-200 bg-gray-50 px-3 text-xs text-gray-900
                                              placeholder-gray-400
                                              focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                              dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900">
                            </div>

                            <div>
                                <label class="block mb-1 text-gray-600 dark:text-gray-300">
                                    توضیحات
                                </label>
                                <textarea name="description" rows="3"
                                          class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-900
                                                 placeholder-gray-400
                                                 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20
                                                 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-900"
                                          placeholder="شرح کوتاهی از پیگیری وارد کنید..."></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button"
                                        @click="openFollowUpsModal = false"
                                        class="px-3 py-1.5 rounded-xl border border-gray-300 text-[11px] text-gray-700 bg-white hover:bg-gray-50
                                               dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800
                                               transition-colors">
                                    بستن
                                </button>
                                <button type="submit"
                                        class="px-4 py-1.5 rounded-xl bg-amber-500 text-white text-[11px] font-medium
                                               hover:bg-amber-600 shadow-md shadow-amber-500/20
                                               transition-colors">
                                    ثبت پیگیری
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
                                          d="M13 5h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        @endcan
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/60 max-h-72 overflow-auto
                                dark:border-gray-800 dark:bg-gray-900/40">
                        @if($recentFollowUps->isEmpty())
                            <div class="flex flex-col items-center justify-center py-8 text-[11px] text-gray-500 dark:text-gray-400">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 8v4l3 3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 3a9 9 0 100 18 9 9 0 000-18z" />
                                </svg>
                                <p>هنوز پیگیری‌ای برای این مشتری ثبت نشده است.</p>
                            </div>
                        @else
                            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($recentFollowUps as $item)
                                    @php
                                        $statusLabel = $statusOptions[$item->status] ?? $item->status;
                                        $dateText    = $item->due_at
                                            ? Jalalian::fromCarbon($item->due_at)->format('Y/m/d')
                                            : '—';
                                    @endphp

                                    <li class="px-3 py-2.5 text-[11px] flex items-start justify-between gap-2">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border
                                                    {{ $item->status === Task::STATUS_DONE
                                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700'
                                                        : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700'
                                                    }}">
                                                    {{ $statusLabel }}
                                                </span>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 text-left dir-ltr">
                                                    {{ $dateText }}
                                                </div>
                                            </div>

                                            <div class="text-gray-700 dark:text-gray-200 truncate" title="{{ $item->title }}">
                                                {{ $item->title }}
                                            </div>
                                            @if($item->description)
                                                <div class="text-gray-500 dark:text-gray-300 line-clamp-2" title="{{ $item->description }}">
                                                    {{ $item->description }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
