@php
    use Modules\ClientCalls\Entities\ClientCall;
    use Illuminate\Support\Facades\DB;

    $user = auth()->user();
    $today = now()->toDateString();

    // ۱. بررسی هوشمند سطح دسترسی جهت داینامیک کردن عنوان
    $hasGlobalView = $user->hasRole('super-admin') || $user->can('client-calls.view.all');
    $widgetTitle = $hasGlobalView
        ? 'گزارش تماس‌های امروز ' . config('clients.labels.plural')
        : 'گزارش تماس‌های امروز من';

    // ۲. پایه‌ریزی مستقیم کوئری برای دور زدن تداخل اولویت پرمیشن‌های اسکوپ مدل
    if ($hasGlobalView) {
        $baseQuery = ClientCall::query()->whereDate('call_date', $today);
    } else {
        // اگر دسترسی مشاهده همه فعال نیست، ویجت کاملاً شخصی شده و فقط تماس‌های شخص کاربر را فیلتر می‌کند
        $baseQuery = ClientCall::query()
            ->whereDate('call_date', $today)
            ->where('user_id', $user->id);
    }

    // --- تب اول: آمار بر اساس تعداد تماس‌ها (Call-Based) ---
    $totalCallsCount = (clone $baseQuery)->count();
    $callsAnswered   = (clone $baseQuery)->where('status', 'done')->count();
    $callsUnanswered = (clone $baseQuery)->where('status', 'failed')->count();
    $callsPlanned    = (clone $baseQuery)->where('status', 'planned')->count();

    // --- تب دوم: آمار بر اساس بیماران یکتا (Client-Based) ---
    $totalUniqueClients = (clone $baseQuery)->distinct('client_id')->count('client_id');
    $clientsAnswered    = (clone $baseQuery)->where('status', 'done')->distinct('client_id')->count('client_id');

    // اصلاح و امن‌سازی کامل ساب‌کوئری برای جلوگیری از نشت اطلاعات عمومی کلینیک
    $clientsUnanswered  = (clone $baseQuery)
        ->where('status', 'failed')
        ->whereNotIn('client_id', function ($query) use ($today, $user, $hasGlobalView) {
            $query->select('client_id')
                ->from('client_calls')
                ->whereDate('call_date', $today)
                ->where('status', 'done');

            if (!$hasGlobalView) {
                $query->where('user_id', $user->id);
            }
        })
        ->distinct('client_id')
        ->count('client_id');

    // --- نرخ موفقیت ارتباطات کلینیک ---
    $successRate = $totalCallsCount > 0 ? round(($callsAnswered / $totalCallsCount) * 100) : 0;

    // --- عارضه‌یابی و استخراج بیشترین علت تماس‌های امروز ---
    $topReason = (clone $baseQuery)
        ->select('reason', DB::raw('count(*) as qty'))
        ->groupBy('reason')
        ->orderByDesc('qty')
        ->first();
    $topReasonText = $topReason ? $topReason->reason : 'ثبت نشده';
@endphp

<div id="client-calls-pulse-widget"
     class="flex flex-col relative overflow-hidden h-full"
     x-data="{ activeTab: 'calls' }">

    {{-- هدر ویجت و آیکون اختصاصی --}}
    <div class="flex flex-col gap-3 p-4 border-b border-gray-100 dark:border-white/10">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">
                        {{ $widgetTitle }}
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        تحلیل زنده خطوط ارتباطی و وضعیت پاسخ‌دهی {{ config('clients.labels.plural') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- کنترل‌کننده تب‌ها --}}
        <div class="flex items-center gap-2 p-1 bg-gray-100/80 dark:bg-white/5 rounded-xl mt-1">
            <button @click="activeTab = 'calls'"
                    :class="activeTab === 'calls' ? 'bg-white dark:bg-white/10 shadow-sm text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="flex-1 text-[11px] py-2 rounded-lg transition-all flex items-center justify-center gap-1.5">
                📊 بر اساس تعداد تماس‌ها
                <span class="px-1.5 py-0.5 rounded-md bg-gray-200/50 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-[10px]">{{ $totalCallsCount }}</span>
            </button>
            <button @click="activeTab = 'clients'"
                    :class="activeTab === 'clients' ? 'bg-white dark:bg-white/10 shadow-sm text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="flex-1 text-[11px] py-2 rounded-lg transition-all flex items-center justify-center gap-1.5">
                👥 بر اساس پوشش {{ config('clients.labels.plural') }}
                <span class="px-1.5 py-0.5 rounded-md bg-gray-200/50 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-[10px]">{{ $totalUniqueClients }}</span>
            </button>
        </div>
    </div>

    {{-- بدنه محتوایی تب‌ها --}}
    <div class="p-4 flex flex-col gap-4">

        {{-- نمایش محتوای تب اول: آمار تماس‌ها --}}
        <div x-show="activeTab === 'calls'" x-transition.opacity.duration.200ms class="grid grid-cols-3 gap-3">
            <div class="p-3 rounded-xl bg-gray-50/80 dark:bg-white/5 border border-gray-100 dark:border-white/10 text-center">
                <div class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">کل تماس‌ها</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalCallsCount }}</div>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-500/10 border border-emerald-100/50 dark:border-emerald-500/20 text-center">
                <div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mb-1">پاسخ داده شده</div>
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $callsAnswered }}</div>
            </div>
            <div class="p-3 rounded-xl bg-red-50/50 dark:bg-red-500/10 border border-red-100/50 dark:border-red-500/20 text-center">
                <div class="text-[10px] font-semibold text-red-600 dark:text-red-400 mb-1">پاسخ داده نشده</div>
                <div class="text-lg font-bold text-red-700 dark:text-red-300">{{ $callsUnanswered }}</div>
            </div>
        </div>

        {{-- نمایش محتوای تب دوم: آمار بیماران --}}
        <div x-show="activeTab === 'clients'" x-transition.opacity.duration.200ms style="display: none;" class="grid grid-cols-3 gap-3">
            <div class="p-3 rounded-xl bg-gray-50/80 dark:bg-white/5 border border-gray-100 dark:border-white/10 text-center">
                <div class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ config('clients.labels.plural') }} درگیر</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalUniqueClients }}</div>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-500/10 border border-emerald-100/50 dark:border-emerald-500/20 text-center">
                <div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mb-1">{{ config('clients.labels.singular') }} پاسخ‌داده</div>
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $clientsAnswered }}</div>
            </div>
            <div class="p-3 rounded-xl bg-red-50/50 dark:bg-red-500/10 border border-red-100/50 dark:border-red-500/20 text-center">
                <div class="text-[10px] font-semibold text-red-600 dark:text-red-400 mb-1">{{ config('clients.labels.singular') }} بی‌پاسخ</div>
                <div class="text-lg font-bold text-red-700 dark:text-red-300">{{ $clientsUnanswered }}</div>
            </div>
        </div>

        {{-- بخش نوار پیشرفت ترکیبی --}}
        @php
            $totalCalls = $totalCallsCount ?: 1;
            $donePct = round(($callsAnswered / $totalCalls) * 100);
            $failedPct = round(($callsUnanswered / $totalCalls) * 100);
            $plannedPct = round(($callsPlanned / $totalCalls) * 100);
        @endphp
        <div class="mt-1">
            <div class="flex justify-between items-center text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">
                <span>وضعیت کلی خطوط امروز</span>
                <span class="text-blue-600 dark:text-blue-400">نرخ پاسخ‌دهی: {{ $successRate }}%</span>
            </div>
            <div class="flex h-2 w-full bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                <div class="bg-emerald-500 transition-all duration-550" style="width: {{ $donePct }}%"></div>
                <div class="bg-red-500 transition-all duration-550" style="width: {{ $failedPct }}%"></div>
                <div class="bg-amber-400 transition-all duration-550" style="width: {{ $plannedPct }}%"></div>
            </div>
        </div>

        {{-- ساب‌فوتر هوشمند --}}
        <div class="pt-3 border-t border-gray-100 dark:border-white/10 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 bg-gray-50/30 dark:bg-white/5 -mx-4 -mb-4 px-4 py-3 rounded-b-2xl">
            <div class="flex items-center gap-1.5 min-w-0">
                <span>🎯 تمرکز امروز:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[180px]" title="{{ $topReasonText }}">
                    {{ $topReasonText }}
                </span>
            </div>
            <div class="flex-shrink-0 flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>سیستم فعال است</span>
            </div>
        </div>

    </div>
</div>
