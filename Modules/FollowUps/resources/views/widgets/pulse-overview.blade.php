@php
    use Modules\FollowUps\Entities\FollowUp;
    use Modules\Tasks\Entities\Task;
    use Modules\Clients\Entities\Client;
    use Morilog\Jalali\Jalalian;

    $user = auth()->user();
    $todayStart = now()->startOfDay();
    $todayEnd = now()->endOfDay();

    // ۱. تعیین عنوان و پایه کوئری با رعایت پرمیشن‌ها
    $hasGlobalView = $user->hasRole('super-admin') || $user->can('followups.view.all');
    $widgetTitle = $hasGlobalView ? 'نبض کل پیگیری‌ها' : 'نبض پیگیری‌های من';

    $baseQuery = FollowUp::query()->where('related_type', Task::RELATED_TYPE_CLIENT);

    if (!$hasGlobalView) {
        $baseQuery->where(function($q) use ($user) {
            $hasAppliedCondition = false;
            if ($user->can('followups.view.assigned')) {
                $q->orWhere('assignee_id', $user->id);
                $hasAppliedCondition = true;
            }
            if ($user->can('followups.view.own')) {
                $q->orWhere('creator_id', $user->id);
                $hasAppliedCondition = true;
            }
            if (!$hasAppliedCondition) {
                $q->where('assignee_id', $user->id);
            }
        });
    }

    // ۲. محاسبه پیگیری‌های معوقه (گذشته)
    $overdueCount = (clone $baseQuery)
        ->where('due_at', '<', $todayStart)
        ->where('status', '!=', 'completed')
        ->count();

    // ۳. محاسبه پیگیری‌های امروز
    $todayQuery = (clone $baseQuery)->whereBetween('due_at', [$todayStart, $todayEnd]);
    $todayTotal = (clone $todayQuery)->count();
    $todayCompleted = (clone $todayQuery)->where('status', 'completed')->count();
    $todayPending = $todayTotal - $todayCompleted;

    // ۴. چیدمان معکوس و داینامیک نمودار بر اساس هفته ایرانی با استفاده از Carbon لاراویل
    $carbonToday = now();

    // در کربن یکشنبه = 0 و شنبه = 6 است. با این فرمول شنبه را 0 و جمعه را 6 می‌کنیم:
    $weekDayIndex = ($carbonToday->dayOfWeek + 1) % 7;

    // پیدا کردن تاریخ میلادی اولین شنبه این هفته
    $saturdayGregorian = $carbonToday->copy()->subDays($weekDayIndex)->startOfDay();

    $upcomingLoad = [];
    for ($i = 0; $i < 7; $i++) {
        $currentDayLoop = $saturdayGregorian->copy()->addDays($i);

        // کوئری دقیق بازه همان روز بر اساس تاریخ میلادی ذخیره شده در دیتابیس
        $dayCount = (clone $baseQuery)
            ->whereBetween('due_at', [$currentDayLoop->copy()->startOfDay(), $currentDayLoop->copy()->endOfDay()])
            ->count();

        $jalaliInstance = Jalalian::fromCarbon($currentDayLoop);
        $fullDayName = $jalaliInstance->format('l');

        $upcomingLoad[] = [
            'count'      => $dayCount,
            'is_today'   => ($i === $weekDayIndex),
            'day_name'   => $fullDayName,
            'short_name' => str_replace(
                ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'],
                ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                $fullDayName
            )
        ];
    }

    $maxLoad = max(array_column($upcomingLoad, 'count')) ?: 1;
@endphp
<div id="followups-pulse-widget" class="flex flex-col relative overflow-hidden h-full">

    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-white/10">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">
                    {{ $widgetTitle }}
                </h2>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                    وضعیت کارهای امروز و ارتباط با {{ config('clients.labels.plural') }}
                </p>
            </div>
        </div>
    </div>

    {{-- محتوا --}}
    <div class="p-4 flex flex-col gap-4">

        {{-- هشدار پیگیری‌های عقب افتاده --}}
        @if($overdueCount > 0)
            <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 rounded-xl">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-xs font-bold text-red-700 dark:text-red-400">پیگیری‌های معوقه (گذشته)</span>
                </div>
                <span class="px-2 py-0.5 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 rounded-md text-xs font-bold">
                    {{ $overdueCount }} مورد
                </span>
            </div>
        @endif

        {{-- کارنامه پیشرفت امروز --}}
        <div class="flex items-center gap-4 p-3.5 bg-gray-50/50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/10">
            <div class="flex-1">
                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-semibold mb-1">پیشرفت امروز شما</div>
                <div class="flex items-end gap-1.5">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none">{{ $todayCompleted }}</span>
                    <span class="text-xs text-gray-500 mb-0.5">/ {{ $todayTotal }}</span>
                </div>
            </div>

            <div class="w-14 h-16 relative flex items-center justify-center">
                @php
                    $percentage = $todayTotal > 0 ? round(($todayCompleted / $todayTotal) * 100) : 0;
                    $dasharray = 2 * pi() * 22;
                    $dashoffset = $todayTotal > 0 ? ($dasharray * ((100 - $percentage) / 100)) : $dasharray;
                @endphp
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="28" cy="32" r="22" stroke="currentColor" stroke-width="5" fill="transparent" class="text-gray-200 dark:text-white/10" />
                    <circle cx="28" cy="32" r="22" stroke="currentColor" stroke-width="5" fill="transparent" class="{{ $percentage == 100 && $todayTotal > 0 ? 'text-emerald-500' : 'text-indigo-500' }} transition-all duration-1000" stroke-dasharray="{{ $dasharray }}" stroke-dashoffset="{{ $dashoffset }}" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">{{ $percentage }}%</span>
                </div>
            </div>
        </div>

        {{-- نمودار میله‌ای کاملاً منطبق با تقویم شمسی ایران (شنبه تا جمعه) --}}
        <div class="mt-1">
            <div class="text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-3">تراکم کاری این هفته (شنبه تا جمعه)</div>

            <div class="flex items-end justify-between h-20 px-1 bg-gray-50/30 dark:bg-white/5 p-2 rounded-xl border border-gray-100/50 dark:border-white/10">
                @foreach($upcomingLoad as $data)
                    @php
                        $heightPct = ($data['count'] / $maxLoad) * 100;
                    @endphp
                    <div class="flex flex-col items-center gap-1 group cursor-pointer relative">
                        {{-- تولتیپ نمایش تعداد روی هاور --}}
                        <span class="text-[9px] text-gray-500 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-4 font-bold">{{ $data['count'] }}</span>

                        {{-- بدنه اصلی میله نمودار --}}
                        <div class="w-6 bg-gray-100 dark:bg-white/10 rounded-t-md relative overflow-hidden h-11 {{ $data['is_today'] ? 'ring-2 ring-indigo-500/50 ring-offset-2 dark:ring-offset-transparent' : '' }}">
                            <div class="absolute bottom-0 w-full rounded-t-md transition-all duration-500 {{ $data['is_today'] ? 'bg-gradient-to-t from-indigo-600 to-indigo-400' : 'bg-indigo-400/70 dark:bg-indigo-500/50' }}" style="height: {{ max($heightPct, $data['count'] > 0 ? 15 : 0) }}%"></div>
                        </div>

                        {{-- برچسب روزهای هفته (روز فعلی بولد و رنگی رندر می‌شود) --}}
                        <span class="text-[9px] mt-0.5 font-semibold {{ $data['is_today'] ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-500/20 px-1 rounded' : 'text-gray-500 dark:text-gray-400' }}" title="{{ $data['day_name'] }}">
                            {{ $data['short_name'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
