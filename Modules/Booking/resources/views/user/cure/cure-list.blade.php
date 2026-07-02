@php
    use Morilog\Jalali\Jalalian;

    // Tooth Mapping Logic for Styling (Same as index.blade.php JS)
    $numberingSystem = $settings->cure_tooth_numbering_system ?? 'universal';

    $palmerMap = [
        1 => ['num' => 7, 'pos' => 'UR'], 2 => ['num' => 6, 'pos' => 'UR'], 3 => ['num' => 5, 'pos' => 'UR'], 4 => ['num' => 4, 'pos' => 'UR'],
        5 => ['num' => 3, 'pos' => 'UR'], 6 => ['num' => 2, 'pos' => 'UR'], 7 => ['num' => 1, 'pos' => 'UR'],
        8 => ['num' => 1, 'pos' => 'UL'], 9 => ['num' => 2, 'pos' => 'UL'], 10 => ['num' => 3, 'pos' => 'UL'], 11 => ['num' => 4, 'pos' => 'UL'],
        12 => ['num' => 5, 'pos' => 'UL'], 13 => ['num' => 6, 'pos' => 'UL'], 14 => ['num' => 7, 'pos' => 'UL'],
        15 => ['num' => 7, 'pos' => 'LR'], 16 => ['num' => 6, 'pos' => 'LR'], 17 => ['num' => 5, 'pos' => 'LR'], 18 => ['num' => 4, 'pos' => 'LR'],
        19 => ['num' => 3, 'pos' => 'LR'], 20 => ['num' => 2, 'pos' => 'LR'], 21 => ['num' => 1, 'pos' => 'LR'],
        22 => ['num' => 1, 'pos' => 'LL'], 23 => ['num' => 2, 'pos' => 'LL'], 24 => ['num' => 3, 'pos' => 'LL'], 25 => ['num' => 4, 'pos' => 'LL'],
        26 => ['num' => 5, 'pos' => 'LL'], 27 => ['num' => 6, 'pos' => 'LL'], 28 => ['num' => 7, 'pos' => 'LL']
    ];

    $fdiMap = [
        1 => ['num' => 17, 'pos' => 'UR'], 2 => ['num' => 16, 'pos' => 'UR'], 3 => ['num' => 15, 'pos' => 'UR'], 4 => ['num' => 14, 'pos' => 'UR'],
        5 => ['num' => 13, 'pos' => 'UR'], 6 => ['num' => 12, 'pos' => 'UR'], 7 => ['num' => 11, 'pos' => 'UR'],
        8 => ['num' => 21, 'pos' => 'UL'], 9 => ['num' => 22, 'pos' => 'UL'], 10 => ['num' => 23, 'pos' => 'UL'], 11 => ['num' => 24, 'pos' => 'UL'],
        12 => ['num' => 25, 'pos' => 'UL'], 13 => ['num' => 26, 'pos' => 'UL'], 14 => ['num' => 27, 'pos' => 'UL'],
        15 => ['num' => 47, 'pos' => 'LR'], 16 => ['num' => 46, 'pos' => 'LR'], 17 => ['num' => 45, 'pos' => 'LR'], 18 => ['num' => 44, 'pos' => 'LR'],
        19 => ['num' => 43, 'pos' => 'LR'], 20 => ['num' => 42, 'pos' => 'LR'], 21 => ['num' => 41, 'pos' => 'LR'],
        22 => ['num' => 31, 'pos' => 'LL'], 23 => ['num' => 32, 'pos' => 'LL'], 24 => ['num' => 33, 'pos' => 'LL'], 25 => ['num' => 34, 'pos' => 'LL'],
        26 => ['num' => 35, 'pos' => 'LL'], 27 => ['num' => 36, 'pos' => 'LL'], 28 => ['num' => 37, 'pos' => 'LL']
    ];

    $toothMap = $numberingSystem === 'fdi' ? $fdiMap : $palmerMap;

    if (!function_exists('getCureQuadrantClasses')) {
        function getCureQuadrantClasses($pos) {
            switch($pos) {
                case 'UR': return 'border-r-4 border-t-4 border-cyan-600 dark:border-cyan-600';
                case 'UL': return 'border-l-4 border-t-4 border-cyan-600 dark:border-cyan-600';
                case 'LR': return 'border-r-4 border-b-4 border-cyan-600 dark:border-cyan-600';
                case 'LL': return 'border-l-4 border-b-4 border-cyan-600 dark:border-cyan-600';
                default: return '';
            }
        }
    }
@endphp

@extends('layouts.user')

@section('content')
    <div class="space-y-6" dir="rtl">

        {{-- ══ Header & Stats ══ --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">کل طرح‌ها</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totalCount) }}</span>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400">تأیید شده</span>
                    <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($statusCounts['confirmed'] ?? 0) }}</span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-100 dark:border-amber-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-amber-600 dark:text-amber-400">پیش‌نویس</span>
                    <span class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-1">{{ number_format($statusCounts['draft'] ?? 0) }}</span>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800 shadow-sm flex flex-col justify-center">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400">جمع کل</span>
                    <span class="text-xl font-bold text-indigo-700 dark:text-indigo-300 mt-1">{{ number_format($totalAmount) }}</span>
                </div>
            </div>

            <div class="flex flex-col justify-center items-end gap-3">
                @can('booking.cure.create')
                    <a href="{{ route('user.booking.cure.index') }}"
                       class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        طرح درمان جدید
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-700/70 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-100 px-4 py-3 shadow-sm">
                <span class="text-xl">✓</span>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ══ Filters ══ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('user.booking.cure.list') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">جستجو</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="نام بیمار..."
                           class="p-2 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</label>
                    <select name="status" class="p-2 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($settings->cure_statuses ?? [] as $st)
                            <option value="{{ $st['id'] }}" {{ request('status') === $st['id'] ? 'selected' : '' }}>{{ $st['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">مرتب‌سازی</label>
                    <select name="sort" class="w-full p-2 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="newest"     {{ request('sort', 'newest') === 'newest'     ? 'selected' : '' }}>جدیدترین</option>
                        <option value="oldest"     {{ request('sort') === 'oldest'               ? 'selected' : '' }}>قدیمی‌ترین</option>
                        <option value="total_desc" {{ request('sort') === 'total_desc'           ? 'selected' : '' }}>بیشترین مبلغ</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 font-medium text-sm transition py-2">
                        اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>

        {{-- ══ Plans List ══ --}}
        <div class="space-y-4">
            @forelse($plans as $plan)
                @php
                    $clientName    = $plan->client?->full_name ?? $plan->patient_name ?? 'بدون نام';
                    $createdBy     = $plan->creator?->name ?? 'نامشخص';
                    $itemCount     = is_array($plan->items) ? count($plan->items) : 0;
                    $teethCount    = collect($plan->items ?? [])->sum(fn($i) => count($i['teeth'] ?? []));

                    $currency      = ($settings->currency_unit ?? $plan->currency) === 'IRR' ? 'ریال' : 'تومان';
                    $createdJalali = Jalalian::fromDateTime($plan->created_at)->format('Y/m/d');

                    $statusMeta = ['label' => $plan->status, 'color' => '#6b7280', 'class' => ''];
                    if (!empty($settings->cure_statuses)) {
                        foreach ($settings->cure_statuses as $st) {
                            if ($st['id'] === $plan->status) {
                                $statusMeta = [
                                    'label' => $st['name'],
                                    'color' => $st['color'] ?? '#6b7280',
                                    'class' => '',
                                ];
                                break;
                            }
                        }
                    } else {
                        if ($plan->status === 'draft') {
                            $statusMeta = ['label' => 'پیش‌نویس', 'color' => '#f59e0b', 'class' => ''];
                        } elseif ($plan->status === 'confirmed') {
                            $statusMeta = ['label' => 'تأیید شده', 'color' => '#10b981', 'class' => ''];
                        }
                    }
                    $initial    = mb_substr($clientName, 0, 1);

                    $canEdit = auth()->user()->can('booking.cure.edit') || auth()->user()->can('booking.cure.manage');
                    if ($plan->status === 'confirmed') {
                        $canEdit = $canEdit
                            && ($settings->cure_allow_edit_confirmed ?? false)
                            && (auth()->user()->can('booking.cure.edit.confirmed') || auth()->user()->can('booking.cure.manage'));
                    }

                    $hasWarranty = collect($plan->items ?? [])->filter(fn($i) => !empty($i['warranty']))->isNotEmpty();
                    $warrantyLabels = collect($plan->items ?? [])
                        ->map(fn($i) => $i['warranty'] ?? null)
                        ->filter()
                        ->unique()
                        ->values();

                    // Extract Installment Data
                    $isInstallment = !empty($plan->installment_option_id);
                    $instTitle = $plan->installment_option_title ?? 'طرح اقساطی';
                    $instDownPayment = $plan->installment_down_payment ?? 0;
                    $instMonthly = $plan->installment_monthly_amount ?? 0;
                    $instCount = $plan->installment_count ?? 0; // تعداد چک‌ها
                    $instMonths = $plan->installment_months ?? 0; // مدت زمان
                    $instFee = $plan->installment_fee_value ?? 0;
                    $instInterval = $plan->installment_interval_months ?? ($instCount > 0 ? round($instMonths / $instCount) : 1);

                    $instStartDate = $plan->installment_start_date ?? null;
                    $instDueDay = $plan->installment_due_day ?? null;
                    $instDpPercent = $plan->installment_down_payment_percent ?? 0;
                    $instFeePercent = $plan->installment_fee_percent ?? 0;
                    $instCashNow = $plan->installment_cash_now ?? 0;
                    $instUncoveredTotal = $plan->installment_uncovered_total ?? 0;

                    $instBreakdown = $plan->installment_breakdown;
                    if (is_string($instBreakdown)) $instBreakdown = json_decode($instBreakdown, true);

                    $generatedCheques = $plan->generated_cheques;
                    if (is_string($generatedCheques)) $generatedCheques = json_decode($generatedCheques, true);
                @endphp

                <div x-data="{ expanded: false }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:shadow-md">

                    {{-- Card Header --}}
                    <div @click="expanded = !expanded"
                         class="cursor-pointer px-5 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-black shrink-0
                                        bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900/40 dark:to-violet-900/40
                                        text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50">
                                {{ $initial }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                                    {{ $clientName }}
                                    @if($isInstallment)
                                        <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700 font-black">اقساطی</span>
                                    @endif
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>#{{ $plan->id }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    <span>{{ $createdJalali }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    <span>{{ $createdBy }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    <span>{{ $itemCount }} آیتم · {{ $teethCount }} دندان</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="hidden sm:inline-flex px-3 py-1 rounded-full text-[11px] font-bold border"
                                  style="background-color: {{ $statusMeta['color'] }}15; color: {{ $statusMeta['color'] }}; border-color: {{ $statusMeta['color'] }}30;">
                                {{ $statusMeta['label'] }}
                            </span>
                            <div class="text-left hidden md:block">
                                <div class="text-[10px] text-gray-400">{{ $isInstallment ? 'مبلغ نقدی/پیش‌پرداخت' : 'قابل پرداخت' }}</div>
                                <div class="text-sm font-black {{ $isInstallment ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ number_format($isInstallment ? $instCashNow : $plan->total) }}
                                    <span class="text-[10px] font-normal text-gray-400">{{ $currency }}</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                 :class="expanded ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Expandable Content --}}
                    <div x-show="expanded"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2">

                        <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 p-5 space-y-5">

                            {{-- Financial Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">مجموع خدمات</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($plan->subtotal) }}
                                        <span class="text-[10px] text-gray-400 font-normal">{{ $currency }}</span>
                                    </span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">تخفیف</span>
                                    <span class="text-sm font-bold text-rose-600 dark:text-rose-400">
                                        @if($plan->discount_value > 0) −{{ number_format($plan->discount_value) }} @else ۰ @endif
                                    </span>
                                </div>
                                @if($isInstallment)
                                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-3 border border-indigo-100 dark:border-indigo-800">
                                        <span class="text-[10px] text-indigo-500 dark:text-indigo-400 block">مبلغ قابل پرداخت (اقساطی)</span>
                                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ number_format($plan->total) }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal">{{ $currency }}</span>
                                    </div>
                                @else
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-3 border border-emerald-100 dark:border-emerald-800">
                                        <span class="text-[10px] text-emerald-500 dark:text-emerald-400 block">مبلغ قابل پرداخت (نقدی)</span>
                                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($plan->total) }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal">{{ $currency }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Installment Breakdown & Cheques --}}
                            @if($isInstallment)
                                <div class="bg-indigo-50/40 dark:bg-indigo-900/10 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/30 space-y-4">
                                    <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-800/30 pb-2">
                                        <h4 class="text-sm font-bold text-indigo-800 dark:text-indigo-200 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            جزئیات طرح اقساطی: {{ $instTitle }}
                                        </h4>
                                        @if($instStartDate)
                                            <div class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                                شروع: {{ $instStartDate }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                            <span class="text-[9px] text-gray-500 block">پیش‌پرداخت ({{ $instDpPercent }}%)</span>
                                            <span class="text-xs font-bold text-amber-600">{{ number_format($instDownPayment) }} {{ $currency }}</span>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                            <span class="text-[9px] text-gray-500 block">مبلغ هر چک</span>
                                            <span class="text-xs font-bold text-emerald-600">{{ number_format($instMonthly) }} {{ $currency }}</span>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                            <span class="text-[9px] text-gray-500 block">تعداد چک‌ها / مدت</span>
                                            <span class="text-xs font-bold text-indigo-600">{{ $instCount }} چک (هر {{ $instInterval }} ماه یک‌بار، مجموعاً {{ $instMonths }} ماه)</span>
                                        </div>
                                        @if($instFee > 0)
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                                <span class="text-[9px] text-gray-500 block">سود اقساط ({{ $instFeePercent }}%)</span>
                                                <span class="text-xs font-bold text-rose-600">{{ number_format($instFee) }} {{ $currency }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($instBreakdown['covered']) || !empty($instBreakdown['uncovered']) || $instUncoveredTotal > 0)
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                            @if(!empty($instBreakdown['covered']))
                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block mb-1.5">مشمول اقساط</span>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach($instBreakdown['covered'] as $cov)
                                                            @php $covBrandName = $cov['brandName'] ?? $cov['brand_name'] ?? 'نامشخص'; @endphp
                                                            <span class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 rounded-md px-2 py-0.5 font-medium">
                                                                {{ $covBrandName }}: {{ number_format($cov['price'] ?? 0) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            @if(!empty($instBreakdown['uncovered']))
                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block mb-1.5">
                                                        غیرمشمول (نقدی)
                                                    </span>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach($instBreakdown['uncovered'] as $uncov)
                                                            @php $uncovBrandName = $uncov['brandName'] ?? $uncov['brand_name'] ?? 'نامشخص'; @endphp
                                                            <span class="inline-flex items-center gap-1 text-[10px] bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 rounded-md px-2 py-0.5 font-medium">
                                                                {{ $uncovBrandName }}: {{ number_format($uncov['price'] ?? 0) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Cheques List --}}
                                    @if(!empty($generatedCheques))
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700 mt-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block">جدول زمانی چک‌ها</span>
                                                <span class="text-[10px] text-gray-500 font-medium">جمع چک‌ها: {{ number_format(collect($generatedCheques)->sum('amount')) }} {{ $currency }}</span>
                                            </div>
                                            <div class="flex flex-col gap-1.5 max-h-48 overflow-y-auto sc-thin pr-1">
                                                @foreach($generatedCheques as $cheque)
                                                    @php $isManual = $cheque['isManual'] ?? false; @endphp
                                                    <div class="flex items-center justify-between p-2 rounded-lg {{ $isManual ? 'bg-emerald-50/50 dark:bg-emerald-900/10 border border-dashed border-emerald-200 dark:border-emerald-800/40' : 'bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700' }}">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-7 h-7 rounded-md {{ $isManual ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600' }} text-[10px] flex items-center justify-center font-black">
                                                                @if($isManual) د @else {{ $cheque['number'] ?? '-' }} @endif
                                                            </div>
                                                            <div>
                                                                <p class="text-[11px] font-bold text-gray-700 dark:text-gray-200">
                                                                    @if($isManual)
                                                                        چک دستی {{ isset($cheque['number']) ? '(شماره: '.$cheque['number'].')' : '' }}
                                                                    @else
                                                                        قسط {{ $cheque['number'] ?? '-' }} از {{ $cheque['total'] ?? '-' }}
                                                                    @endif
                                                                </p>
                                                                <p class="text-[10px] text-indigo-500 dark:text-indigo-400 mt-0.5 flex items-center flex-wrap gap-1.5">
                                                                    <span>{{ $cheque['display_date'] ?? $cheque['date'] ?? '-' }}</span>
                                                                    @if(!empty($cheque['bankName']))
                                                                        <span class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold">بانک {{ $cheque['bankName'] }}</span>
                                                                    @endif
                                                                    @if(!empty($cheque['chequeNumber']))
                                                                        <span class="px-1 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 font-bold">شماره چک: {{ $cheque['chequeNumber'] }}</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="text-left">
                                                            <p class="text-xs font-black text-emerald-600">{{ number_format($cheque['amount'] ?? 0) }}</p>
                                                            <p class="text-[9px] text-gray-400">{{ $currency }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Items Details --}}
                            @if(is_array($plan->items) && count($plan->items) > 0)
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                        <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300">جزئیات آیتم‌های طرح درمان</h4>
                                        <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-medium px-2 py-0.5 rounded-md">
                                            {{ $itemCount }} ردیف خدماتی
                                        </span>
                                    </div>
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @foreach($plan->items as $item)
                                            @php
                                                $serviceName    = $item['service_name'] ?? $item['name'] ?? 'سرویس نامشخص';
                                                $price          = $item['price'] ?? 0;
                                                $qty            = $item['quantity'] ?? 1;
                                                $subtotal       = $item['subtotal'] ?? ($price * $qty);
                                                $discountedSubtotal = $subtotal;
                                                $planSubtotal = $plan->subtotal ?? 0;
                                                $planDiscountType = $plan->discount_type ?? 'amount';
                                                $planDiscountAmount = $plan->discount_amount ?? 0;
                                                $planDiscountValue = $plan->discount_value ?? 0;

                                                if ($planDiscountType === 'percent' && $planDiscountAmount > 0) {
                                                    $discountedSubtotal = $subtotal * (1 - $planDiscountAmount / 100);
                                                } elseif ($planDiscountType === 'amount' && $planDiscountValue > 0 && $planSubtotal > 0) {
                                                    $proportion = $subtotal / $planSubtotal;
                                                    $discountedSubtotal = $subtotal - ($planDiscountValue * $proportion);
                                                }
                                                $discountedSubtotal = max(0, round($discountedSubtotal));
                                                $hasItemDiscount = abs($subtotal - $discountedSubtotal) > 0;
                                                $rawTeeth       = $item['teeth'] ?? [];
                                                $brands         = $item['brands'] ?? [];
                                                $categoryValue  = $item['category_name'] ?? $item['category'] ?? null;
                                                $guaranteeValue = $item['warranty'] ?? null;
                                            @endphp
                                            <div class="px-4 py-3 flex flex-col gap-2 hover:bg-gray-50/50 dark:hover:bg-gray-900/40 transition">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $serviceName }}</p>
                                                        <span class="text-[10px] text-gray-400">({{ $qty }} عدد)</span>
                                                    </div>
                                                    <div class="text-left">
                                                        @if($hasItemDiscount)
                                                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                                {{ number_format($discountedSubtotal) }}
                                                                <span class="text-[10px] text-gray-400 font-normal mr-0.5">{{ $currency }}</span>
                                                            </span>
                                                            <span class="text-[10px] text-gray-400 line-through block sm:inline sm:mr-2">
                                                                {{ number_format($subtotal) }}
                                                            </span>
                                                        @else
                                                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                                {{ number_format($subtotal) }}
                                                                <span class="text-[10px] text-gray-400 font-normal mr-0.5">{{ $currency }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-1.5 items-center pr-3">
                                                    @foreach($brands as $brand)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                                            {{ !empty($brand['sectionTitle']) ? $brand['sectionTitle'].': ' : '' }}<span class="font-bold">{{ $brand['name'] ?? 'نامشخص' }}</span>
                                                        </span>
                                                    @endforeach
                                                    @if($categoryValue)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-purple-50 text-purple-700 border border-purple-100 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800">
                                                            گروه: {{ $categoryValue }}
                                                        </span>
                                                    @endif
                                                    @if($guaranteeValue)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-teal-50 text-teal-700 border border-teal-100 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                            ضمانت: {{ $guaranteeValue }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($rawTeeth) && is_array($rawTeeth))
                                                    @php
                                                        $groupedTeeth = ['UR' => [], 'UL' => [], 'LR' => [], 'LL' => []];
                                                        foreach ($rawTeeth as $tooth) {
                                                            $toothId = is_array($tooth) ? ($tooth['number'] ?? array_values($tooth)[0]) : $tooth;
                                                            $toothInfo = $toothMap[$toothId] ?? ['num' => $toothId, 'pos' => 'UR'];
                                                            $groupedTeeth[$toothInfo['pos']][] = [
                                                                'num' => $toothInfo['num'],
                                                                'quadClass' => getCureQuadrantClasses($toothInfo['pos'])
                                                            ];
                                                        }
                                                    @endphp
                                                    <div class="flex items-center gap-2 pr-3 mt-1.5">
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌ها:</span>
                                                        <div class="inline-grid grid-cols-2 select-none">
                                                            <!-- Row 1: UR | UL -->
                                                            <!-- UR -->
                                                            <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[28px]">
                                                                @foreach($groupedTeeth['UR'] as $toothData)
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border-solid {{ $toothData['quadClass'] }}">
                                                                        {{ $toothData['num'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                            <!-- UL -->
                                                            <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[28px]">
                                                                @foreach($groupedTeeth['UL'] as $toothData)
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border-solid {{ $toothData['quadClass'] }}">
                                                                        {{ $toothData['num'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>

                                                            <!-- Row 2: LR | LL -->
                                                            <!-- LR -->
                                                            <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[28px]">
                                                                @foreach($groupedTeeth['LR'] as $toothData)
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border-solid {{ $toothData['quadClass'] }}">
                                                                        {{ $toothData['num'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                            <!-- LL -->
                                                            <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[28px]">
                                                                @foreach($groupedTeeth['LL'] as $toothData)
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border-solid {{ $toothData['quadClass'] }}">
                                                                        {{ $toothData['num'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Notes & Actions --}}
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                                @if($plan->notes)
                                    <div class="flex-1 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $plan->notes }}</span>
                                    </div>
                                @else
                                    <div></div>
                                @endif

                                <div class="flex items-center gap-2 shrink-0">
                                    @canany(['booking.cure.view', 'booking.cure.view.all', 'booking.cure.view.own', 'booking.cure.manage'])
                                        <a href="{{ route('user.booking.cure.show', $plan) }}"
                                           class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:hover:bg-gray-700 transition font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            مشاهده
                                        </a>
                                    @endcanany

                                    @if(class_exists(\Modules\ContractForge\App\Models\Contract::class))
                                        @php
                                            $firstContract = \Modules\ContractForge\App\Models\Contract::where('contractable_type', get_class($plan))->where('contractable_id', $plan->id)->first();
                                        @endphp
                                        @if($firstContract)
                                            <a href="{{ route('user.contracts.show', $firstContract->id) }}"
                                               class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-100 dark:bg-teal-900/25 dark:text-teal-400 dark:hover:bg-teal-900/40 transition font-medium">
                                                مشاهده قرارداد
                                            </a>
                                        @else
                                            @if(auth()->user()->can('contractforge.manage'))
                                                @php
                                                    $matchingTemplate = \Modules\ContractForge\Services\ContractEngine::findMatchingTemplate('treatment_plan', $plan);
                                                @endphp
                                                @if($matchingTemplate)
                                                    <form action="{{ route('user.contracts.generate_manual') }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="template_id" value="{{ $matchingTemplate->id }}">
                                                        <input type="hidden" name="entity_id" value="{{ $plan->id }}">
                                                        <input type="hidden" name="entity_type" value="treatment_plan">
                                                        <button type="submit" class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/25 dark:text-amber-400 dark:hover:bg-amber-900/40 transition font-medium">
                                                            صدور قرارداد
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endif
                                    @endif

                                    @if($canEdit)
                                        <a href="{{ route('user.booking.cure.edit', $plan) }}"
                                           class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition font-medium shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            ویرایش
                                        </a>
                                    @endif

                                    @canany(['booking.cure.delete', 'booking.cure.manage'])
                                        <form method="POST" action="{{ route('user.booking.cure.destroy', $plan) }}" onsubmit="return confirm('آیا از حذف این طرح درمان اطمینان دارید؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1.5 px-4 py-2 text-xs rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/30 transition font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                حذف
                                            </button>
                                        </form>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm py-16 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-600 dark:text-gray-400 mb-1">هیچ طرح درمانی یافت نشد</h3>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-6">شما هنوز طرح درمانی ثبت نکرده‌اید.</p>
                    @can('booking.cure.create')
                        <a href="{{ route('user.booking.cure.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            ایجاد اولین طرح
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        <div class="flex justify-end">
            {{ $plans->links() }}
        </div>
    </div>
@endsection
