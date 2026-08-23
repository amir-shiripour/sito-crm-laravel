@php
    use Carbon\Carbon;
    use Morilog\Jalali\Jalalian;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0,9), $persian, (string)$str);
    };

    $formatAgo = function($date) {
        if (!$date) return '';
        if (class_exists(Jalalian::class)) {
            return Jalalian::fromCarbon($date)->ago();
        }
        return $date instanceof Carbon ? $date->diffForHumans() : (string)$date;
    };

    $formatJalaliDate = function($date) use ($faNum) {
        if (!$date) return '';
        try {
            if (class_exists(Jalalian::class)) {
                return $faNum(Jalalian::fromCarbon($date)->format('Y/m/d'));
            }
            if (function_exists('jdate')) {
                return $faNum(jdate($date)->format('Y/m/d'));
            }
        } catch (\Throwable) {
            return $date instanceof Carbon ? $date->format('Y/m/d') : (string)$date;
        }
        return (string)$date;
    };

    $formatJalaliTime = function($date) use ($faNum) {
        if (!$date) return '';
        try {
            if (class_exists(Jalalian::class)) {
                return $faNum(Jalalian::fromCarbon($date)->format('H:i'));
            }
            if (function_exists('jdate')) {
                return $faNum(jdate($date)->format('H:i'));
            }
        } catch (\Throwable) {
            return $date instanceof Carbon ? $date->format('H:i') : (string)$date;
        }
        return (string)$date;
    };

    $colorMap = [
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40',
        'blue'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40',
        'violet'  => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300 border border-violet-200 dark:border-violet-800/40',
        'rose'    => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40',
        'indigo'  => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40',
        'sky'     => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 border border-sky-200 dark:border-sky-800/40',
        'amber'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40',
        'teal'    => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 border border-teal-200 dark:border-teal-800/40',
        'orange'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 border border-orange-200 dark:border-orange-800/40',
        'cyan'    => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/40',
        'red'     => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/40',
        'lime'    => 'bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300 border border-lime-200 dark:border-lime-800/40',
        'gray'    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600',
    ];

    $activitiesDataForJs = $project->activities->map(function($act) {
        $action = $act->action;
        $group = match(true) {
            str_starts_with($action, 'project.') => 'project',
            str_starts_with($action, 'phase.') => 'phase',
            str_starts_with($action, 'task.') => 'task',
            str_starts_with($action, 'checklist.') => 'checklist',
            str_starts_with($action, 'member.') => 'member',
            str_starts_with($action, 'document.') => 'document',
            str_starts_with($action, 'comment.') => 'comment',
            str_starts_with($action, 'message.') => 'message',
            str_starts_with($action, 'time') => 'time',
            default => 'other'
        };

        return [
            'id' => $act->id,
            'user_id' => $act->user_id,
            'user_name' => $act->user?->name ?? 'کاربر سیستم',
            'action' => $action,
            'group' => $group,
            'action_label' => $act->actionLabel(),
            'subject' => $act->subject ?? '',
        ];
    })->values();

    $activityUsers = $project->activities->pluck('user')->filter()->unique('id')->values();
    if ($project->createdBy && !$activityUsers->contains('id', $project->createdBy->id)) {
        $activityUsers->push($project->createdBy);
    }
    foreach ($project->members as $m) {
        if ($m->user && !$activityUsers->contains('id', $m->user->id)) {
            $activityUsers->push($m->user);
        }
    }
@endphp

<div class="space-y-6"
     x-data="projectActivityTabManager({
         activities: {{ json_encode($activitiesDataForJs) }},
         currentUserId: {{ auth()->id() ?? 0 }}
     })">

    {{-- Header with Search & Filter Controls --}}
    <div
        class="bg-white dark:bg-gray-800/80 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>تاریخچه و گزارش فعالیت‌های پروژه</span>
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">رهگیری کامل و زمانی رویدادها، تغییر وضعیت‌ها، پیام‌ها و کارهای
                پروژه</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Search & Filter Toggle Button --}}
            <button type="button"
                    @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $refs.activitySearchInput?.focus())"
                    :class="searchOpen || hasActiveFilters ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all select-none cursor-pointer"
                    title="جستجو و فیلتر پیشرفته فعالیت‌ها">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>جستجو و فیلتر</span>
                <span x-show="hasActiveFilters" x-cloak class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            </button>

            {{-- Counter Badge --}}
            <span
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span x-text="faNum(visibleCount) + ' از ' + faNum(activities.length) + ' رویداد'">
                    {{ $faNum($project->activities->count()) }} رویداد
                </span>
            </span>
        </div>
    </div>

    {{-- Expandable Search & Filter Drawer --}}
    <div x-show="searchOpen || hasActiveFilters"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-99"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-99"
         class="bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 rounded-3xl p-5 space-y-4 shadow-sm">

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-ref="activitySearchInput"
                       x-model="searchQuery"
                       placeholder="جستجو در موضوع فعالیت، عنوان کار یا نام کاربر..."
                       class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 pr-10 pl-8 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white placeholder-gray-400">
                <button type="button"
                        x-show="searchQuery"
                        @click="searchQuery = ''; $refs.activitySearchInput?.focus()"
                        class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    ✕
                </button>
            </div>

            {{-- Filter by User --}}
            <div class="sm:w-52">
                <select x-model="userFilter"
                        class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold">
                    <option value="all">همه کاربران ({{ $faNum($activityUsers->count()) }})</option>
                    @if(auth()->check())
                        <option value="me">فعالیت‌های خودم</option>
                    @endif
                    @foreach($activityUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Reset Button --}}
            <button type="button"
                    x-show="hasActiveFilters"
                    @click="clearFilters()"
                    class="px-4 py-2.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 transition-all shrink-0 cursor-pointer">
                پاک کردن فیلترها
            </button>
        </div>

        {{-- Category Pills --}}
        <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100 dark:border-gray-700/50 text-xs">
            <span class="text-gray-400 font-bold ml-2">دسته‌بندی:</span>
            <button type="button" @click="categoryFilter = 'all'"
                    :class="categoryFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                همه
            </button>
            <button type="button" @click="categoryFilter = 'task'"
                    :class="categoryFilter === 'task' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                گروه‌ها و کارها
            </button>
            <button type="button" @click="categoryFilter = 'phase'"
                    :class="categoryFilter === 'phase' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                فازها
            </button>
            <button type="button" @click="categoryFilter = 'message'"
                    :class="categoryFilter === 'message' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                پیام‌ها و چت
            </button>
            <button type="button" @click="categoryFilter = 'comment'"
                    :class="categoryFilter === 'comment' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                کامنت‌ها
            </button>
            <button type="button" @click="categoryFilter = 'document'"
                    :class="categoryFilter === 'document' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                اسناد
            </button>
            <button type="button" @click="categoryFilter = 'member'"
                    :class="categoryFilter === 'member' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1 rounded-xl transition-all cursor-pointer font-bold">
                اعضای تیم
            </button>
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div id="activity-timeline-container"
         class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        @if($project->activities->isEmpty())
            <div class="py-16 text-center">
                <div
                    class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-gray-400 text-sm font-bold">هنوز فعالیتی برای این پروژه ثبت نشده است.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @foreach($project->activities as $activity)
                    @php
                        $color      = $activity->actionColor();
                        $colorClass = $colorMap[$color] ?? $colorMap['gray'];
                    @endphp
                    <li x-show="isItemVisible({{ $activity->id }})"
                        x-transition
                        id="activity-row-{{ $activity->id }}"
                        class="flex items-start gap-4 p-4 sm:p-5 hover:bg-gray-50/60 dark:hover:bg-gray-900/20 transition-colors">
                        {{-- Color Dot / Icon --}}
                        <span
                            class="mt-0.5 w-9 h-9 rounded-2xl flex items-center justify-center shrink-0 {{ $colorClass }} shadow-2xs font-bold text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-current"></span>
                        </span>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg {{ $colorClass }}">
                                        {{ $activity->actionLabel() }}
                                    </span>
                                    @if($activity->subject)
                                        <p class="text-sm text-gray-800 dark:text-gray-200 font-bold leading-snug">
                                            {{ $activity->subject }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-gray-100/90 dark:bg-gray-700/60 text-[11px] font-bold text-gray-700 dark:text-gray-200 border border-gray-200/70 dark:border-gray-600/60 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $formatJalaliDate($activity->created_at) }}</span>
                                        <span class="text-gray-300 dark:text-gray-600 font-normal">|</span>
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span dir="ltr">{{ $formatJalaliTime($activity->created_at) }}</span>
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-medium hidden sm:inline-block">
                                        ({{ $formatAgo($activity->created_at) }})
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                @if($activity->user)
                                    <span class="flex items-center gap-1 font-medium text-gray-600 dark:text-gray-300">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $activity->user->name }}
                                    </span>
                                @endif
                                @if($activity->task)
                                    <span
                                        class="flex items-center gap-1 text-indigo-500 dark:text-indigo-400 font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                        گروه: {{ $activity->task->title }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Empty Filter Search State --}}
            <div x-show="activities.length > 0 && visibleCount === 0" x-cloak
                 class="py-12 px-4 text-center space-y-3">
                <div
                    class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="text-xs font-bold text-gray-700 dark:text-gray-300">هیچ فعالیتی با معیارهای جستجو یا
                    فیلترهای انتخابی یافت نشد.
                </div>
                <button type="button" @click="clearFilters()"
                        class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100 transition-all cursor-pointer">
                    پاک کردن فیلترها و نمایش همه
                </button>
            </div>

            {{-- Pagination Bar --}}
            <div x-show="visibleCount > 0" x-cloak
                 class="p-4 sm:p-5 border-t border-gray-100 dark:border-gray-700/50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-800/40">

                {{-- Page Info & Per Page selector --}}
                <div class="flex flex-wrap items-center justify-between sm:justify-start gap-4 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    <div>
                        <span>نمایش</span>
                        <strong class="font-bold text-gray-800 dark:text-gray-200" x-text="faNum(pageStart)"></strong>
                        <span>تا</span>
                        <strong class="font-bold text-gray-800 dark:text-gray-200" x-text="faNum(pageEnd)"></strong>
                        <span>از مجموع</span>
                        <strong class="font-bold text-indigo-600 dark:text-indigo-400" x-text="faNum(visibleCount)"></strong>
                        <span>رویداد</span>
                    </div>

                    <div class="flex items-center gap-1.5 border-r border-gray-200 dark:border-gray-700 pr-4">
                        <span>تعداد در صفحه:</span>
                        <select x-model.number="perPage" @change="currentPage = 1"
                                class="rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer shadow-2xs">
                            <option :value="10">۱۰</option>
                            <option :value="15">۱۵</option>
                            <option :value="25">۲۵</option>
                            <option :value="50">۵۰</option>
                            <option :value="100">۱۰۰</option>
                        </select>
                    </div>
                </div>

                {{-- Page navigation controls --}}
                <div x-show="totalPages > 1" class="flex items-center justify-center md:justify-end gap-1.5">
                    {{-- Prev Button --}}
                    <button type="button"
                            @click="prevPage()"
                            :disabled="currentPage === 1"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>قبلی</span>
                    </button>

                    {{-- Page Number Pills --}}
                    <div class="flex items-center gap-1">
                        <template x-for="(p, idx) in pageNumbers" :key="idx">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 font-bold select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button"
                                            @click="goToPage(p)"
                                            :class="currentPage === p ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                            class="w-8 h-8 rounded-xl text-xs font-bold transition-all flex items-center justify-center cursor-pointer shadow-2xs"
                                            x-text="faNum(p)">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Next Button --}}
                    <button type="button"
                            @click="nextPage()"
                            :disabled="currentPage === totalPages"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-2xs cursor-pointer">
                        <span>بعدی</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function projectActivityTabManager(config = {}) {
        const rawActivities = config.activities || [];
        const currentUserId = config.currentUserId || 0;

        return {
            activities: rawActivities,
            searchOpen: false,
            searchQuery: '',
            userFilter: 'all',
            categoryFilter: 'all',
            currentPage: 1,
            perPage: 15,

            init() {
                this.$watch('searchQuery', () => { this.currentPage = 1; });
                this.$watch('userFilter', () => { this.currentPage = 1; });
                this.$watch('categoryFilter', () => { this.currentPage = 1; });
            },

            get hasActiveFilters() {
                return this.searchQuery.trim() !== '' || this.userFilter !== 'all' || this.categoryFilter !== 'all';
            },

            clearFilters() {
                this.searchQuery = '';
                this.userFilter = 'all';
                this.categoryFilter = 'all';
                this.currentPage = 1;
            },

            get visibleItems() {
                return this.activities.filter(item => {
                    if (this.categoryFilter !== 'all' && item.group !== this.categoryFilter) {
                        return false;
                    }

                    if (this.userFilter === 'me') {
                        if (item.user_id !== currentUserId) return false;
                    } else if (this.userFilter !== 'all') {
                        if (String(item.user_id) !== String(this.userFilter)) return false;
                    }

                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase().trim();
                        const inSubject = (item.subject || '').toLowerCase().includes(q);
                        const inLabel = (item.action_label || '').toLowerCase().includes(q);
                        const inUser = (item.user_name || '').toLowerCase().includes(q);
                        if (!inSubject && !inLabel && !inUser) return false;
                    }

                    return true;
                });
            },

            get visibleCount() {
                return this.visibleItems.length;
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.visibleItems.length / this.perPage));
            },

            get pageStart() {
                if (this.visibleCount === 0) return 0;
                return (this.currentPage - 1) * this.perPage + 1;
            },

            get pageEnd() {
                return Math.min(this.currentPage * this.perPage, this.visibleCount);
            },

            get paginatedItems() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.visibleItems.slice(start, start + this.perPage);
            },

            get pageNumbers() {
                const total = this.totalPages;
                const current = this.currentPage;
                if (total <= 7) {
                    return Array.from({ length: total }, (_, i) => i + 1);
                }
                const pages = [];
                if (current <= 3) {
                    pages.push(1, 2, 3, 4, '...', total);
                } else if (current >= total - 2) {
                    pages.push(1, '...', total - 3, total - 2, total - 1, total);
                } else {
                    pages.push(1, '...', current - 1, current, current + 1, '...', total);
                }
                return pages;
            },

            goToPage(p) {
                if (p >= 1 && p <= this.totalPages) {
                    this.currentPage = p;
                    const container = document.getElementById('activity-timeline-container');
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.goToPage(this.currentPage + 1);
                }
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.goToPage(this.currentPage - 1);
                }
            },

            isItemVisible(id) {
                return this.paginatedItems.some(a => a.id === id);
            },

            faNum(str) {
                if (str === null || str === undefined) return '';
                const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                return String(str).replace(/[0-9]/g, function(w) {
                    return persian[+w];
                });
            }
        };
    }
</script>
