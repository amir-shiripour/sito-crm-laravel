@php
    /** @var \Illuminate\Support\Collection|\Modules\Booking\Entities\LaboratoryOrder[] $clientLabOrders */
    /** @var \Modules\Clients\Entities\Client $client */
    use Morilog\Jalali\Jalalian;

    $totalLabCount = $clientLabOrders->count();
    $inProgressCount = $clientLabOrders->whereIn('status', ['registered', 'sent_to_lab', 'in_progress', 'returned_to_lab'])->count();
    $receivedCount = $clientLabOrders->where('status', 'received')->count();
    $deliveredCount = $clientLabOrders->where('status', 'delivered')->count();

    $statusBadges = [
        'draft' => ['label' => 'پیش‌نویس', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700'],
        'registered' => ['label' => 'ثبت شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-800/60'],
        'sent_to_lab' => ['label' => 'ارسال به لابراتوار', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800/60'],
        'in_progress' => ['label' => 'در حال ساخت', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60'],
        'received' => ['label' => 'دریافت شده از لابراتوار', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60'],
        'delivered' => ['label' => 'تحویل به بیمار', 'class' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border-teal-200 dark:border-teal-800/60'],
        'returned_to_lab' => ['label' => 'مرجوع به لابراتوار', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800/60'],
        'canceled' => ['label' => 'لغو شده', 'class' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700'],
    ];
@endphp

<div class="space-y-6">
    {{-- Header & Stats --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                سفارشات و پروتزهای لابراتوار
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                مدیریت مراحل ساخت، تاریخ‌های تحویل و پیگیری هماهنگی لابراتوار این پرونده
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('user.booking.laboratory.daily-board') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-300 dark:border-slate-700 rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                کارتابل پیگیری روزانه
            </a>
            <a href="{{ route('user.booking.laboratory.index', ['client_id' => $client->id, 'action' => 'create']) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 rounded-xl shadow-xs shadow-indigo-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت سفارش جدید
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/40">
            <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">کل سفارشات</span>
            <span class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $totalLabCount }}</span>
        </div>
        <div class="p-3.5 rounded-xl border border-indigo-200/70 dark:border-indigo-900/40 bg-indigo-50/40 dark:bg-indigo-950/20">
            <span class="text-xs text-indigo-700 dark:text-indigo-300 block mb-1">در حال ساخت و پیگیری</span>
            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $inProgressCount }}</span>
        </div>
        <div class="p-3.5 rounded-xl border border-emerald-200/70 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-950/20">
            <span class="text-xs text-emerald-700 dark:text-emerald-300 block mb-1">دریافت شده (آماده تحویل)</span>
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $receivedCount }}</span>
        </div>
        <div class="p-3.5 rounded-xl border border-teal-200/70 dark:border-teal-900/40 bg-teal-50/40 dark:bg-teal-950/20">
            <span class="text-xs text-teal-700 dark:text-teal-300 block mb-1">تحویل شده به بیمار</span>
            <span class="text-xl font-bold text-teal-600 dark:text-teal-400">{{ $deliveredCount }}</span>
        </div>
    </div>

    {{-- Orders List --}}
    @if($clientLabOrders->count() > 0)
        <div class="space-y-4">
            @foreach($clientLabOrders as $order)
                @php
                    $badge = $statusBadges[$order->status] ?? ['label' => $order->status, 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'];
                    $stages = $order->stages ?? collect([]);
                    $completedStagesCount = $stages->where('is_completed', true)->count();
                    $totalStagesCount = $stages->count();
                    $progressPercent = $totalStagesCount > 0 ? round(($completedStagesCount / $totalStagesCount) * 100) : 0;
                    $expectedDate = $order->expected_delivery_date ? Jalalian::fromDateTime($order->expected_delivery_date)->format('Y/m/d') : 'تعیین نشده';
                    $sentDate = $order->order_date ? Jalalian::fromDateTime($order->order_date)->format('Y/m/d') : 'تعیین نشده';
                @endphp
                <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/50 shadow-xs hover:border-indigo-300 dark:hover:border-indigo-800/70 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                <span class="text-xs text-slate-400 dark:text-slate-500">شماره سفارش:</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                    {{ $order->order_number }}
                                </span>
                                <span class="text-xs text-slate-400">•</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ $order->prosthesisType->name ?? 'پروتز' }}
                                </span>
                                @if($order->laboratory)
                                    <span class="text-xs text-slate-400">•</span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400">
                                        لابراتوار: <strong class="text-slate-800 dark:text-slate-200">{{ $order->laboratory->name }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-y-1.5 gap-x-4 text-xs text-slate-500 dark:text-slate-400 pt-1">
                                <div>
                                    <span class="text-slate-400 dark:text-slate-500">پزشک معالج:</span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $order->doctor->name ?? 'ثبت نشده' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 dark:text-slate-500">تاریخ ارسال:</span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $sentDate }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 dark:text-slate-500">موعد تحویل:</span>
                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $expectedDate }}</span>
                                </div>
                                @if(!empty($order->tooth_numbers))
                                    <div>
                                        <span class="text-slate-400 dark:text-slate-500">شماره دندان‌ها:</span>
                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">
                                            {{ is_array($order->tooth_numbers) ? implode(', ', $order->tooth_numbers) : $order->tooth_numbers }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
                            @if($order->status === 'received')
                                <a href="{{ route('user.booking.appointments.create', [
                                    'client_id' => $client->id,
                                    'doctor_id' => $order->doctor_id,
                                    'notes' => 'تحویل پروتز سفارش ' . $order->order_number . ' (' . ($order->prosthesisType->name ?? 'لابراتوار') . ')'
                                ]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-700/60 rounded-xl transition-colors shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    رزرو نوبت تحویل
                                </a>
                            @endif

                            <a href="{{ route('user.booking.laboratory.daily-board', ['search' => $order->order_number]) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-colors">
                                مشاهده جزئیات
                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Progress Bar if stages exist --}}
                    @if($totalStagesCount > 0)
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/70">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1.5">
                                <span>پیشرفت مراحل کاری: <strong class="text-slate-700 dark:text-slate-300">{{ $completedStagesCount }} از {{ $totalStagesCount }} مرحله</strong></span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $progressPercent }}٪</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full transition-all duration-300"
                                     style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">هیچ سفارش لابراتواری برای این پرونده یافت نشد</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
                برای ثبت روکش، بریج، ایمپلنت یا پروتزهای متحرک این بیمار می‌توانید از دکمه زیر اقدام کنید.
            </p>
            <a href="{{ route('user.booking.laboratory.index', ['client_id' => $client->id, 'action' => 'create']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت اولین سفارش لابراتوار
            </a>
        </div>
    @endif
</div>
