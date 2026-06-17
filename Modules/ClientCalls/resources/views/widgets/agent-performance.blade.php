@php
    use Modules\ClientCalls\Entities\ClientCall;
    use Illuminate\Support\Facades\DB;

    $user = auth()->user();

    // ۱. گارد امنیتی شدید: فقط کاربران ارشد کلینیک اجازه مشاهده آمار کل پرسنل را دارند
    $hasAccess = $user->hasRole('super-admin') || $user->can('client-calls.view.all');

    $performances = collect();
    $topAgentId = null;

    if ($hasAccess) {
        $today = now()->toDateString();

        // واکشی داده‌های عملکردی پرسنل به همراه تفکیک وضعیت‌ها
        $performances = ClientCall::query()
            ->whereDate('call_date', $today)
            ->select(
                'user_id',
                DB::raw("count(*) as total_calls"),
                DB::raw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as success_calls"),
                DB::raw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_calls"),
                DB::raw("SUM(CASE WHEN status = 'planned' THEN 1 ELSE 0 END) as planned_calls")
            )
            ->groupBy('user_id')
            ->with('user:id,name,profile_photo_path')
            ->get()
            ->map(function($agent) {
                // محاسبه داینامیک نرخ موفقیت کارشناس
                $agent->success_rate = $agent->total_calls > 0
                    ? round(($agent->success_calls / $agent->total_calls) * 100)
                    : 0;
                return $agent;
            })
            // رتبه‌بندی پرسنل بر اساس کیفیت عملکرد (نرخ موفقیت) و سپس کمیت (تعداد تماس)
            ->sortByDesc(function($agent) {
                return [$agent->success_rate, $agent->total_calls];
            })
            ->values();

        // تعیین کارشناس برتر امروز کلینیک
        $firstAgent = $performances->first();
        if ($firstAgent && $firstAgent->success_calls > 0) {
            $topAgentId = $firstAgent->user_id;
        }
    }
@endphp

@if($hasAccess)
    <div id="crm-agent-performance-widget" class="flex flex-col relative overflow-hidden h-full">

        {{-- افکت گرافیکی پس‌زمینه کارت --}}
        <div class="absolute top-0 left-0 w-24 h-24 bg-indigo-500/5 rounded-br-full pointer-events-none"></div>

        {{-- هدر ویجت --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">
                        کارنامه عملکرد پرسنل امروز
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        رتبه‌بندی تیم ارتباط با {{ config('clients.labels.plural') }} بر اساس بازدهی
                    </p>
                </div>
            </div>
        </div>

        {{-- بدنه اصلی ویجت --}}
        <div class="p-4 flex flex-col gap-3.5 flex-1 overflow-y-auto max-h-[340px] scrollbar-thin">
            @if($performances->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center py-10 opacity-75">
                    <div class="w-12 h-12 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mb-2 border border-gray-100 dark:border-white/10">
                        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">امروز تماسی توسط پرسنل ثبت نشده است.</p>
                </div>
            @else
                @foreach($performances as $agent)
                    @php
                        $isTop = ($agent->user_id === $topAgentId);
                        $total = $agent->total_calls ?: 1;
                        $sPct = round(($agent->success_calls / $total) * 100);
                        $fPct = round(($agent->failed_calls / $total) * 100);
                        $pPct = 100 - ($sPct + $fPct);
                    @endphp
                    <div class="group relative flex flex-col gap-2 p-3 rounded-xl border border-gray-100 bg-white dark:border-white/10 dark:bg-white/5 transition-all duration-200 {{ $isTop ? 'ring-2 ring-amber-400/40 dark:ring-amber-500/30 bg-amber-50/10 dark:bg-amber-500/5' : '' }}">

                        {{-- اطلاعات هویتی و رتبه --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                {{-- آواتار کارشناس --}}
                                <div class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                                    {{ mb_substr($agent->user ? $agent->user->name : 'ک', 0, 1) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                    {{ $agent->user ? $agent->user->name : 'کاربر نامشخص' }}
                                </span>
                                </div>
                            </div>

                            {{-- بچ اختصاصی کارشناس برتر یا نرخ موفقیت --}}
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                @if($isTop)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-400 shadow-sm animate-bounce">
                                    ⭐ بازدهی برتر
                                </span>
                                @endif
                                <span class="text-xs font-mono font-bold text-gray-800 dark:text-gray-200">
                                {{ $agent->success_rate }}%
                            </span>
                            </div>
                        </div>

                        {{-- نوار پیشرفت تفکیکی تماس‌های کارشناس --}}
                        <div class="space-y-1">
                            <div class="flex h-1.5 w-full bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="bg-emerald-500" style="width: {{ $sPct }}%" title="موفق: {{ $agent->success_calls }}"></div>
                                <div class="bg-red-400" style="width: {{ $fPct }}%" title="ناموفق: {{ $agent->failed_calls }}"></div>
                                <div class="bg-gray-300 dark:bg-white/20" style="width: {{ $pPct }}%" title="برنامه‌ریزی شده: {{ $agent->planned_calls }}"></div>
                            </div>
                            <div class="flex items-center justify-between text-[9px] text-gray-400 dark:text-gray-500 font-medium px-0.5">
                                <div class="flex items-center gap-2">
                                    <span>کل: <strong class="text-gray-700 dark:text-gray-300">{{ $agent->total_calls }}</strong></span>
                                    <span class="text-emerald-500">موفق: <strong class="">{{ $agent->success_calls }}</strong></span>
                                </div>
                                <span class="text-[8px] opacity-75">خروجی زنده</span>
                            </div>
                        </div>

                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif
