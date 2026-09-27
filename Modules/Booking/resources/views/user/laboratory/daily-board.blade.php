<div>
    <div class="space-y-6">
        {{-- هدر برگه پیگیری روز --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        برگه پیگیری روزانه لابراتوار (Daily Board)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">
                        لیست بیماران نیازمند پیگیری در تاریخ امروز ({{ $todayJalali }}) و ثبت سریع نتایج تماس
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('user.booking.laboratory.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    بازگشت به کارتابل سفارشات
                </a>
            </div>
        </div>

        {{-- اعلان موفقیت --}}
        @if($toastSuccess)
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 p-4 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ $toastSuccess }}
                </div>
                <button type="button" wire:click="$set('toastSuccess', null)" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 text-base">&times;</button>
            </div>
        @endif

        {{-- فیلترها و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">نمایش:</span>
                <button type="button" wire:click="$set('filterOnlyPending', '1')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filterOnlyPending === '1' ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm shadow-amber-500/20' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    فقط نیازمند پیگیری امروز / تاخیرها
                </button>
                <button type="button" wire:click="$set('filterOnlyPending', '0')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filterOnlyPending === '0' ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-500/20' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    همه سفارشات در جریان
                </button>
            </div>

            <div class="w-full sm:w-72 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی نام بیمار، پرونده..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all pl-9">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- جدول پیگیری روز (دقیقاً مطابق فرمت عکس شماره ۳) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold">
                            <th class="py-3 px-4 w-16">ردیف</th>
                            <th class="py-3 px-4">بیمار (نام و شماره پرونده)</th>
                            <th class="py-3 px-4">لابراتوار و متد</th>
                            <th class="py-3 px-4">مرحله فعلی</th>
                            <th class="py-3 px-4">تاریخ امروز</th>
                            <th class="py-3 px-4 text-center">نیاز به پیگیری؟</th>
                            <th class="py-3 px-4">نتیجه پیگیری و توضیحات</th>
                            <th class="py-3 px-4 text-center">اقدام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/80 dark:divide-gray-700">
                        @forelse($rows as $index => $item)
                            @php
                                $order = $item->order;
                                $stage = $item->dueStage;
                                $isOverdue = $item->isOverdue;
                                $needsFollowup = $item->needsFollowup;
                                $todayLog = $item->todayLog;

                                $trClass = 'transition-all duration-150 ';
                                if ($isOverdue) {
                                    $trClass .= 'bg-rose-50/75 dark:bg-rose-950/35 border-r-4 border-rose-500 hover:bg-rose-50/90 dark:hover:bg-rose-950/45';
                                } elseif ($needsFollowup) {
                                    $trClass .= 'bg-amber-50/70 dark:bg-amber-950/30 border-r-4 border-amber-500 hover:bg-amber-50/90 dark:hover:bg-amber-950/40';
                                } else {
                                    $trClass .= 'hover:bg-gray-50/80 dark:hover:bg-gray-700/40';
                                }
                            @endphp

                            <tr class="{{ $trClass }}">
                                <td class="py-3.5 px-4 font-bold text-gray-700 dark:text-gray-300">
                                    {{ $index + 1 }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $order->patient_name }}</div>
                                    @if($order->patient_file_number)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">پرونده: {{ $order->patient_file_number }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->lab_partner_name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $order->category_label }}</span>
                                        @if($order->units_count > 1)
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">({{ $order->units_count }} واحد)</span>
                                        @endif
                                    </div>
                                    @if($order->has_teeth)
                                        @php
                                            $groupedTeeth = $order->grouped_teeth;
                                        @endphp
                                        <div class="inline-flex items-center gap-2 border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 px-2.5 py-1 rounded-xl shadow-2xs select-none mt-1">
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 pl-1.5 border-l border-slate-200 dark:border-slate-700">دندان:</span>
                                            <div class="inline-grid grid-cols-2 text-center align-middle">
                                                {{-- UR --}}
                                                <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-end gap-1 min-w-[20px] min-h-[16px]">
                                                    @foreach($groupedTeeth['UR'] as $t)
                                                        <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                    @endforeach
                                                </div>
                                                {{-- UL --}}
                                                <div class="border-b-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-start gap-1 min-w-[20px] min-h-[16px]">
                                                    @foreach($groupedTeeth['UL'] as $t)
                                                        <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                    @endforeach
                                                </div>
                                                {{-- LR --}}
                                                <div class="border-l-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-end gap-1 min-w-[20px] min-h-[16px]">
                                                    @foreach($groupedTeeth['LR'] as $t)
                                                        <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                    @endforeach
                                                </div>
                                                {{-- LL --}}
                                                <div class="py-0.5 px-1.5 flex items-center justify-start gap-1 min-w-[20px] min-h-[16px]">
                                                    @foreach($groupedTeeth['LL'] as $t)
                                                        <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($stage)
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $stage->stage_title }}</span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-0.5">موعد: {{ $stage->due_at_jalali }}</span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">تمام مراحل تکمیل شده</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $todayJalali }}
                                </td>

                                {{-- ستون نیاز به پیگیری بله یا خیر (محاسبه خودکار سیستم) --}}
                                <td class="py-3.5 px-4 text-center">
                                    @if($needsFollowup || $isOverdue)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 dark:border dark:border-rose-800/40 shadow-sm animate-pulse">
                                            بله
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-700/60 dark:text-gray-300">
                                            خیر
                                        </span>
                                    @endif
                                </td>

                                {{-- ستون نتیجه پیگیری دستی --}}
                                <td class="py-3.5 px-4">
                                    @if($todayLog && $todayLog->followup_result)
                                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-800 dark:text-gray-200">
                                            {{ $todayLog->followup_result }}
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-1">توسط: {{ $todayLog->operator?->name ?? 'سیستم' }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic">هنوز نتیجه‌ای برای امروز ثبت نشده است.</span>
                                    @endif
                                </td>

                                {{-- دکمه ثبت نتیجه --}}
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            wire:click="openLogModal({{ $order->id }}, {{ $stage?->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xs shadow-sm shadow-amber-500/20 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        ثبت نتیجه
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    عالی است! در حال حاضر هیچ سفارشی نیازمند پیگیری در امروز نیست.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- مودال ثبت نتیجه پیگیری --}}
    @if($showLogModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/70 dark:bg-gray-900/50">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        ثبت نتیجه پیگیری: {{ $patientTitle }}
                    </h3>
                    <button type="button" wire:click="$set('showLogModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold">&times;</button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">مرحله مربوطه:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $currentStageTitle }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1.5">نیاز به پیگیری مجدد؟</label>
                        <select wire:model="modalNeedsFollowup" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-amber-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-amber-500/20 transition-all">
                            <option value="1">بله (نیاز به پیگیری دارد)</option>
                            <option value="0">خیر (مرحله کامل شد / پاسخ قطعی دریافت شد)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1.5">نتیجه پیگیری و پاسخ لابراتوار / منشی *</label>
                        <textarea wire:model="modalResult" rows="3" placeholder="مثلاً: با لابراتوار تماس گرفته شد، اعلام کردند قالب ریخته شده و فردا ظهر ارسال می‌شود..."
                                  class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-amber-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-amber-500/20 transition-all"></textarea>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="$set('showLogModal', false)" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">انصراف</button>
                    <button type="button" wire:click="saveDailyLog" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all">ثبت و ذخیره گزارش</button>
                </div>
            </div>
        </div>
    @endif
</div>
