@extends('layouts.user')
@include('partials.jalali-date-picker')
@php
    $rawDueDaysVal = \Modules\Settings\Entities\Setting::where('key', 'installment_due_days')->value('value');
    $decodedDueDays = [];
    if ($rawDueDaysVal) {
        $decoded = json_decode($rawDueDaysVal, true);
        if (is_array($decoded)) {
            $decodedDueDays = array_map('intval', $decoded);
        } elseif (is_string($rawDueDaysVal)) {
            $parts = explode(',', $rawDueDaysVal);
            foreach ($parts as $p) {
                if (is_numeric(trim($p))) $decodedDueDays[] = intval(trim($p));
            }
        }
    }

    $rawRoundingMode = \Modules\Settings\Entities\Setting::where('key', 'installment_rounding_mode')->value('value');
    $roundingMode = 'none';
    if ($rawRoundingMode !== null) {
        $decoded = json_decode($rawRoundingMode, true);
        $roundingMode = is_string($decoded) ? $decoded : strval($rawRoundingMode);
    }
    $roundingMode = strtolower(trim($roundingMode));
    if (!in_array($roundingMode, ['none', 'up', 'down'], true)) {
        $roundingMode = 'none';
    }

    $rawRoundingFactor = \Modules\Settings\Entities\Setting::where('key', 'installment_rounding_factor')->value('value');
    $roundingFactor = 1000;
    if ($rawRoundingFactor !== null) {
        $decoded = json_decode($rawRoundingFactor, true);
        $roundingFactor = is_numeric($decoded)
            ? intval($decoded)
            : (is_numeric($rawRoundingFactor) ? intval($rawRoundingFactor) : 1000);
    }

    if (is_object($settings)) {
        $settings->installment_due_days = $decodedDueDays;
        $settings->installment_rounding_mode = $roundingMode;
        $settings->installment_rounding_factor = $roundingFactor;
    } elseif (is_array($settings)) {
        $settings['installment_due_days'] = $decodedDueDays;
        $settings['installment_rounding_mode'] = $roundingMode;
        $settings['installment_rounding_factor'] = $roundingFactor;
        $settings = [
            'installment_due_days' => $decodedDueDays,
            'installment_rounding_mode' => $roundingMode,
            'installment_rounding_factor' => $roundingFactor
        ];
    }
    $allWorkflows = \Modules\Workflows\Entities\Workflow::where('is_active', true)->get(['id', 'name']);
@endphp
@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .tooth-highlighted {
            fill: #10b981 !important;
            stroke: #047857 !important;
            stroke-width: 3px !important;
            filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.6));
        }

        .dark .tooth-highlighted {
            fill: #34d399 !important;
            stroke: #10b981 !important;
        }

        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }

        .tooth-selected {
            fill: #ffffff !important;
            stroke: #2563eb !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(37, 99, 235, 0.45));
        }

        .dark .tooth-selected {
            fill: #334155 !important;
            stroke: #3b82f6 !important;
        }

        .tooth-in-plan {
            fill: #d1fae5;
            stroke: #10b981;
        }

        .dark .tooth-in-plan {
            fill: #064e3b;
            stroke: #34d399;
        }

        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1;
        }

        .dark .tooth-unselected {
            fill: #334155 !important;
            stroke: #475569;
        }

        .tooth-unselected:hover {
            fill: #f8fafc !important;
            stroke: #3b82f6;
        }

        .dark .tooth-unselected:hover {
            fill: #1e293b !important;
            stroke: #60a5fa;
        }

        .svc-card {
            flex-shrink: 0;
            width: 180px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 14px 12px;
            cursor: pointer;
            background: #f8fafc;
            transition: border-color .15s, box-shadow .15s, transform .12s, background-color .15s;
            position: relative;
        }

        .dark .svc-card {
            background: #1e293b;
            border-color: #334155;
        }

        .svc-card:hover {
            border-color: #a5b4fc;
            background: #fff;
            box-shadow: 0 4px 20px rgba(99, 102, 241, .15);
            transform: translateY(-2px);
        }

        .dark .svc-card:hover {
            background: #273548;
            border-color: #475569;
        }

        .svc-card.svc-active {
            border-color: #6366f1;
            box-shadow: 0 4px 24px rgba(99, 102, 241, .25);
            background: linear-gradient(145deg, #eef2ff, #f5f3ff);
        }

        .dark .svc-card.svc-active {
            background: linear-gradient(145deg, rgba(99, 102, 241, .18), rgba(139, 92, 246, .1));
            border-color: #818cf8;
        }

        .svc-badge {
            position: absolute;
            top: -8px;
            left: -8px;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            background: #6366f1;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, .45);
            border: 2px solid #fff;
        }

        .dark .svc-badge {
            border-color: #1e293b;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim-slide-up { animation: slideUp .22s ease forwards; }
        .anim-fade-up { animation: fadeUp .18s ease forwards; }

        .plan-row:hover .row-del { opacity: 1; }
        .row-del { opacity: 0; transition: opacity .18s; }

        .sc-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .sc-thin::-webkit-scrollbar-track { background: transparent; }
        .sc-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .dark .sc-thin::-webkit-scrollbar-thumb { background: #475569; }

        .assign-panel {
            border: 2px solid #6366f1;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
        }

        .dark .assign-panel {
            background: #1e293b;
            border-color: #4f46e5;
        }

        .assign-panel-head {
            background: rgba(99, 102, 241, 0.07);
            border-bottom: 1px solid rgba(99, 102, 241, 0.15);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .dark .assign-panel-head {
            background: rgba(99, 102, 241, 0.12);
            border-bottom-color: rgba(99, 102, 241, 0.25);
        }

        .step-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 7px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dark .step-label {
            background: rgba(15, 23, 42, 0.3);
            border-bottom-color: #1e293b;
            color: #475569;
        }

        .group-preview-bar {
            padding: 10px 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        .dark .group-preview-bar {
            background: rgba(15, 23, 42, 0.3);
            border-top-color: #1e293b;
        }

        .group-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #6366f1;
            background: rgba(99, 102, 241, 0.07);
            border: 1px solid rgba(99, 102, 241, 0.15);
        }

        .dark .group-chip {
            color: #818cf8;
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .assign-footer {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        .dark .assign-footer {
            background: rgba(15, 23, 42, 0.4);
            border-top-color: #1e293b;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            z-index: 9999;
            padding: 12px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), opacity .3s;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast-success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast-error { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .toast-info { background: linear-gradient(135deg, #6366f1, #8b5cf6); }

        .inst-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .dark .inst-badge {
            background: rgba(99, 102, 241, 0.2);
            color: #818cf8;
        }

        /* Timeline premium styles */
        .timeline-container {
            position: relative;
        }
        .timeline-line {
            position: absolute;
            right: 21px; /* aligns perfectly with the center of the timeline-glow-dot */
            top: 1.25rem;
            bottom: 1.25rem;
            width: 2px;
            background: linear-gradient(to bottom, #6366f1 0%, #a5b4fc 50%, #e2e8f0 100%);
        }
        .dark .timeline-line {
            background: linear-gradient(to bottom, #4f46e5 0%, #312e81 50%, #374151 100%);
        }
        .timeline-glow-dot {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .timeline-node-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .timeline-node-card:hover {
            transform: translateX(-4px);
        }

        /* Financial Summary card premium styling */
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            transition: background-color 0.15s;
        }
        .receipt-row:nth-child(odd) {
            background-color: rgba(248, 250, 252, 0.8);
        }
        .dark .receipt-row:nth-child(odd) {
            background-color: rgba(30, 41, 59, 0.4);
        }
        .payable-spotlight {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(20, 184, 166, 0.04));
            border: 1px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05);
        }
        .dark .payable-spotlight {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.25), rgba(13, 148, 136, 0.1));
            border: 1px solid rgba(16, 185, 129, 0.25);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        .payment-pill-group {
            position: relative;
            background: rgba(241, 245, 249, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 0.375rem;
            border-radius: 1.25rem;
            display: flex;
            gap: 0.375rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        .dark .payment-pill-group {
            background: rgba(15, 23, 42, 0.65);
            border-color: rgba(51, 65, 85, 0.8);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .payment-pill-btn {
            position: relative;
            z-index: 10;
            flex: 1;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 700;
            border-radius: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .payment-pill-btn:active {
            transform: scale(0.97);
        }
        .tooth-selected-wf {
            fill: #6366f1 !important;
            stroke: #4f46e5 !important;
            stroke-width: 2px !important;
            opacity: 0.9;
        }
        .tooth-active-wf {
            fill: #10b981 !important;
            stroke: #059669 !important;
            stroke-width: 1.5px !important;
            opacity: 0.75;
        }
        .tooth-bound-wf {
            fill: #3b82f6 !important;
            stroke: #2563eb !important;
            stroke-width: 1.5px !important;
            opacity: 0.7;
        }
    </style>

    <div id="cure-toast" class="toast"></div>

    <div
        x-data="treatmentPlanApp(
            @js($servicesJs ?? []),
            @js($planJs ?? null),
            @js($isReadOnly ?? false),
            @js($clients ?? []),
            @js($settings ?? null),
            @js($installmentTypes ?? []),
            @js($assignableRolesWithUsers ?? []),
            @js($cureStatuses ?? []),
         )"
        x-cloak
        class="space-y-5 pb-20"
        dir="rtl"
    >
        @if(!empty($isSnapshotView) && $isSnapshotView)
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white p-6 shadow-xl border border-indigo-500/20 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <!-- Decorative Glow -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex items-center gap-4 relative">
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/10 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-200 border border-indigo-400/20 tracking-wide">نسخه تاریخچه</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/20">{{ $snapshotLabel ?? 'نامشخص' }}</span>
                    </div>
                    <h3 class="text-base font-black mt-1">حالت فقط خواندنی (مشاهده تاریخچه)</h3>
                    <p class="text-xs text-indigo-200/80 mt-0.5">
                        شما در حال مشاهده وضعیت طرح درمان در این مرحله خاص هستید. امکان ویرایش اطلاعات وجود ندارد.
                    </p>
                </div>
            </div>

            @if(isset($planJs['id']))
            <div class="shrink-0 relative">
                <a href="{{ route('user.booking.cure.edit', $planJs['id']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 active:scale-95 text-white text-xs font-black rounded-xl border border-white/10 backdrop-blur-md shadow-lg transition-all">
                    <svg class="w-4 h-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>بازگشت به نسخه فعلی و فعال</span>
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- ══════════════════ TOP ACTION BAR ══════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4
                    flex flex-col sm:flex-row sm:items-center justify-between gap-4">

            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl shrink-0 flex items-center justify-center"
                     style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                            @if($isReadOnly) مشاهده طرح درمان
                            @elseif($planJs) ویرایش طرح درمان
                            @else ایجاد طرح درمان
                            @endif
                        </h1>
                        <!-- Static status badge when in read-only mode -->
                        <span x-show="isReadOnly" class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wide"
                              :style="{ backgroundColor: getStatusColor(status) + '15', color: getStatusColor(status) }"
                              x-text="getStatusName(status)"></span>

                        <!-- Interactive Status Dropdown when in edit mode -->
                        <div x-show="!isReadOnly" class="relative" x-data="{ statusOpen: false }">
                            <button type="button" @click="statusOpen = !statusOpen"
                                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg border transition-all"
                                    :style="{ backgroundColor: getStatusColor(status) + '10', color: getStatusColor(status), borderColor: getStatusColor(status) + '30' }">
                                <span x-text="getStatusName(status)"></span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="statusOpen" @click.away="statusOpen = false"
                                 class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1"
                                 style="right: 0;"
                                 x-cloak>
                                <template x-for="st in availableStatusesForSelection()" :key="st.id">
                                    <button type="button" @click="status = st.id; statusOpen = false"
                                            class="w-full text-right px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between transition-colors"
                                            :class="status === st.id ? 'font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'text-gray-700 dark:text-gray-300'">
                                        <span x-text="st.name"></span>
                                        <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: st.color }"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-1.5 bg-gray-50 dark:bg-gray-800/40 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700/60 w-fit select-none">
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>دندان</span>
                        <span class="text-gray-300 dark:text-gray-600 font-normal">←</span>
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>سرویس</span>
                        <span class="text-gray-300 dark:text-gray-600 font-normal">←</span>
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>تنظیم برند</span>
                        <span class="text-gray-300 dark:text-gray-600 font-normal">←</span>
                        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>افزودن به طرح</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div x-show="!isReadOnly && ['draft', 'draft_direct'].includes(settings.cure.default_status)"
                     class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all"
                     :class="draftSaved
                         ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800/30'
                         : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800/30'">
                    <div class="w-2 h-2 rounded-full"
                         :class="draftSaved ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></div>
                    <span class="text-xs font-bold"
                          :class="draftSaved ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                          x-text="draftSaved ? 'پیش‌نویس ذخیره شده' : 'پیش‌نویس ذخیره نشده'"></span>
                </div>

                @if(isset($planJs['id']))
                <a href="{{ route('user.booking.cure.workflows', $planJs['id']) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl border border-indigo-200 dark:border-indigo-800
                          bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-sm font-bold
                          hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span>مسیر و بوم فرآیندها</span>
                </a>
                @endif

                <a href="{{ route('user.booking.cure.list') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                          bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium
                          hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    لیست طرح‌ها
                </a>

                <div class="relative" x-data="{ clientDdOpen: false, clientSearch: '' }">
                    <div @click="isReadOnly ? null : clientDdOpen = !clientDdOpen"
                         class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-all min-w-50"
                         :class="isReadOnly
                             ? 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-default text-gray-400 dark:text-gray-500'
                             : (clientDdOpen
                                 ? 'border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-100 dark:ring-indigo-900/30 bg-white dark:bg-gray-800 cursor-pointer'
                                 : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-500/50 cursor-pointer')">
                        <svg class="w-4 h-4 shrink-0"
                             :class="clientId ? 'text-emerald-500' : 'text-gray-400'"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <div class="flex-1 truncate font-semibold">
                            <template x-if="!clientId">
                                <span class="text-sm text-gray-400 dark:text-gray-500">انتخاب بیمار</span>
                            </template>
                            <template x-if="clientId">
                                <div class="flex flex-col text-right">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-100"
                                          x-text="clients.find(c => c.id === clientId)?.full_name || 'بیمار'"></span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal mt-0.5"
                                          x-show="clients.find(c => c.id === clientId)?.national_code || clients.find(c => c.id === clientId)?.case_number"
                                          x-text="(clients.find(c => c.id === clientId)?.case_number ? 'پرونده: ' + clients.find(c => c.id === clientId).case_number : '') + (clients.find(c => c.id === clientId)?.national_code ? ' | کد ملی: ' + clients.find(c => c.id === clientId).national_code : '')"></span>
                                </div>
                            </template>
                        </div>
                        <svg x-show="!isReadOnly" class="w-3.5 h-3.5 text-gray-400 transition-transform shrink-0"
                             :class="clientDdOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <div x-show="clientDdOpen"
                         @click.away="clientDdOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity:0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute z-50 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                                rounded-2xl shadow-2xl shadow-gray-200/50 dark:shadow-gray-900/50 overflow-hidden"
                         style="right: 0;">
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="relative">
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input x-model="clientSearch" @input.debounce.300ms="searchClientsBackend($el.value)" type="text" placeholder="جستجوی بیمار..." @click.stop
                                       class="w-full pr-9 pl-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600
                                              bg-gray-50 dark:bg-gray-700/50 text-gray-800 dark:text-gray-100
                                              placeholder-gray-400 focus:outline-none focus:border-indigo-400
                                              focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all"/>
                            </div>
                        </div>
                        <div class="max-h-56 overflow-y-auto sc-thin">
                            <!-- Loading Indicator -->
                            <div x-show="clientSearchLoading" class="py-3 text-center text-xs text-gray-400 dark:text-gray-500 flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                در حال جستجو...
                            </div>
                            <template x-for="client in filteredClients(clientSearch)" :key="client.id">
                                <button @click="clientId = client.id; patientName = client.full_name || ''; clientDdOpen = false; clientSearch = ''"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-right transition-all hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                        :class="clientId === client.id ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                                    <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center text-sm font-bold"
                                         :class="clientId === client.id ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                         x-text="client.full_name?.charAt(0) || '?'"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold truncate"
                                           :class="clientId === client.id ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-100'"
                                           x-text="client.full_name"></p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate"
                                           x-text="(client.phone ? 'تلفن: ' + client.phone : '') + (client.national_code ? ' | کد ملی: ' + client.national_code : '') + (client.case_number ? ' | پرونده: ' + client.case_number : '')"></p>
                                    </div>
                                    <svg x-show="clientId === client.id" class="w-5 h-5 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </template>
                            <template x-if="filteredClients(clientSearch).length === 0">
                                <div class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">مشتری‌ای پیدا نشد</div>
                            </template>
                        </div>
                        <div x-show="clientId" class="p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <button @click="clientId = null; patientName = ''; clientDdOpen = false; clientSearch = ''"
                                    class="w-full py-2 rounded-xl text-sm font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-all">
                                حذف مشتری انتخاب شده
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400" x-text="planItems.length"></span>
                    <span class="text-xs text-indigo-500 dark:text-indigo-400">مورد</span>
                </div>

                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="formatPrice(finalPayable)"></span>
                </div>

                <!-- Status transition moved to stepper card for better UI integration -->

                @canany(['booking.cure.create', 'booking.cure.edit', 'booking.cure.manage'])
                    <button x-show="!isReadOnly" @click="savePlan(status)"
                            :disabled="planItems.length === 0 || !clientId || isSaving || isReadOnly || !hasChanges() || !hasPermissionForCurrentStatus"
                            :class="(planItems.length === 0 || !clientId || isSaving || !hasChanges() || !hasPermissionForCurrentStatus) ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="isSaving ? 'در حال ذخیره…' : (existingPlan ? 'بروزرسانی طرح درمان' : 'ذخیره طرح درمان')"></span>
                    </button>
                @endcanany
            </div>
        </div>

        <div x-show="!isReadOnly && !hasPermissionForCurrentStatus" x-cloak class="mt-4 p-4 rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold">هشدار عدم دسترسی کافی:</span> شما مجاز به ثبت طرح درمان در وضعیت <span class="font-bold" x-text="'«' + getStatusName(status) + '»'"></span> نیستید. برای امکان ذخیره تغییرات، لطفا وضعیت طرح درمان را به وضعیت دیگری تغییر دهید.
            </div>
        </div>

        {{-- ══════════════════ SERVICE PICKER ══════════════════ --}}
        @if(!$isReadOnly)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span class="w-2 h-5 rounded-full bg-indigo-500 shrink-0"></span>
                            انتخاب سرویس
                            <span x-show="servicePlanCounts && Object.keys(servicePlanCounts).length > 0" class="text-xs text-indigo-500 dark:text-indigo-400 font-normal">
                                (<span x-text="Object.keys(servicePlanCounts).length"></span> سرویس انتخاب شده)
                            </span>
                        </h2>
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="relative">
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input x-model="serviceSearch" type="text" placeholder="جستجوی سرویس…"
                                       class="pr-9 pl-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 w-48 transition-all"/>
                            </div>
                        </div>
                    </div>
                    @if(count($categories ?? []) > 1)
                    <div class="flex gap-2 overflow-x-auto sc-thin pb-1">
                        <button @click="filterCategory = null"
                                :class="filterCategory === null ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300/40' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-4 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shrink-0">
                            همه
                        </button>
                        @foreach($categories ?? [] as $cat)
                            <button @click="filterCategory = {{ $cat->id }}"
                                    :class="filterCategory === {{ $cat->id }} ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300/40' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    class="px-4 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shrink-0">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="px-4 py-4 flex gap-3 overflow-x-auto sc-thin">
                    <template x-for="service in filteredServices" :key="service.id">
                        <div @click="selectService(service)" :class="['svc-card', selectedService && selectedService.id === service.id ? 'svc-active' : '']">
                            <span x-show="servicePlanCounts[service.id]" class="svc-badge" x-text="servicePlanCounts[service.id]"></span>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div :class="selectedService && selectedService.id === service.id ? 'bg-indigo-600 shadow-md shadow-indigo-200/60' : (servicePlanCounts[service.id] ? 'bg-emerald-500 shadow-md shadow-emerald-200/60' : 'bg-gray-200 dark:bg-gray-600')"
                                     class="w-6 h-6 rounded-full shrink-0 flex items-center justify-center transition-all mt-0.5">
                                    <svg x-show="selectedService && selectedService.id === service.id || servicePlanCounts[service.id]" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div x-show="service.custom_prices?.tabs?.length > 0" class="flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-700/40 shrink-0">
                                    <svg class="w-2.5 h-2.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-[9px] text-amber-600 dark:text-amber-400 font-bold" x-text="(service.custom_prices?.tabs?.length || 0) + ' تب'"></span>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 leading-tight mb-1 line-clamp-2" x-text="service.name"></p>
                        </div>
                    </template>
                    <template x-if="filteredServices.length === 0">
                        <div class="flex-1 py-10 text-center text-sm text-gray-400 dark:text-gray-500">سرویسی پیدا نشد</div>
                    </template>
                </div>
            </div>
        @endif

        {{-- نوار وضعیت (Status Stepper) --}}
        <div x-show="existingPlan" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-hidden relative">
            <div class="absolute inset-0 bg-gradient-to-l from-indigo-50/50 to-transparent dark:from-indigo-900/10 pointer-events-none"></div>

            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 relative z-10">
                <!-- Timeline Visual -->
                <div class="flex-1 overflow-x-auto sc-thin">
                    <div class="flex items-center min-w-[600px] justify-between relative px-4 py-4">
                        <!-- Progress line -->
                        <div class="absolute top-1/2 left-8 right-8 h-1 bg-gray-100 dark:bg-gray-700 rounded-full -translate-y-1/2 z-0 overflow-hidden">
                            <div class="h-full bg-gradient-to-l from-indigo-500 to-violet-500 rounded-full transition-all duration-500"
                                 :style="{ width: getStatusProgressPercent() + '%' }"></div>
                        </div>

                        <template x-for="(st, index) in cureStatuses.sort((a, b) => a.order - b.order)" :key="st.id">
                            <div class="flex flex-col items-center relative z-10 w-24">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm transition-all duration-500 shadow-sm"
                                     :class="st.id === status
                                         ? 'text-white border-none ring-4 ring-offset-2 ring-offset-white dark:ring-offset-gray-900 scale-110'
                                         : (isStatusPassed(st.id) ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800' : 'bg-white dark:bg-gray-800 text-gray-400 border-2 border-gray-100 dark:border-gray-700')"
                                     :style="st.id === status ? { backgroundColor: st.color, boxShadow: '0 0 0 4px ' + st.color + '25, 0 8px 20px ' + st.color + '40' } : {}">
                                    <svg x-show="isStatusPassed(st.id) && st.id !== status" class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-show="!isStatusPassed(st.id) || st.id === status" x-text="index + 1"></span>
                                </div>
                                <span class="text-[11px] font-black mt-3 transition-colors duration-300 text-center leading-tight"
                                      :class="st.id === status ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"
                                      x-text="st.name"></span>
                                <span class="text-[9px] font-medium text-gray-400 mt-0.5" x-show="st.id === status">وضعیت کنونی</span>
                            </div>
                        </template>
                    </div>
                </div>


            </div>
        </div>

        {{-- ══════════════════ MAIN AREA ══════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">

            {{-- LEFT COL --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dental chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 rounded-full bg-rose-500 shrink-0"></span>
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">نقشه دندانی</h2>
                            <span class="text-xs text-gray-400 dark:text-gray-500">(<span x-text="selectedTeeth.length"></span> انتخابی)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button x-show="!isReadOnly" @click="selectJaw('upper')" :class="preset==='upper' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک بالا</button>
                            <button x-show="!isReadOnly" @click="selectJaw('lower')" :class="preset==='lower' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک پایین</button>
                            <button x-show="!isReadOnly" @click="selectAllTeeth()" :class="preset==='all' ? 'bg-violet-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">همه</button>
                            <button x-show="!isReadOnly" @click="resetTeeth()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 transition-all">پاک‌سازی</button>
                        </div>
                    </div>
                    <div class="px-4 pt-4 pb-1 relative">
                        <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">انتخاب</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400" x-text="selectedTeeth.length"></span>
                        </div>
                        <x-booking::dental-chart/>
                    </div>
                    <div class="px-5 py-3.5 flex items-center gap-3 min-h-14 border-t border-gray-150 dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                        <template x-if="selectedTeeth.length > 0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌های انتخابی:</span>
                                <div class="inline-grid grid-cols-2 select-none">
                                    <!-- Row 1: UR | UL -->
                                    <!-- UR -->
                                    <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                        <template x-for="t in getQuadrantTeeth(selectedTeeth, 'UR')" :key="t">
                                            <div role="button" @click="toggle(t)"
                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                 :class="[getQuadrantClasses(t)]"
                                                 x-text="getToothLabel(t).num">
                                            </div>
                                        </template>
                                    </div>
                                    <!-- UL -->
                                    <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                        <template x-for="t in getQuadrantTeeth(selectedTeeth, 'UL')" :key="t">
                                            <div role="button" @click="toggle(t)"
                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                 :class="[getQuadrantClasses(t)]"
                                                 x-text="getToothLabel(t).num">
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Row 2: LR | LL -->
                                    <!-- LR -->
                                    <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[36px] min-h-[36px]">
                                        <template x-for="t in getQuadrantTeeth(selectedTeeth, 'LR')" :key="t">
                                            <div role="button" @click="toggle(t)"
                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                 :class="[getQuadrantClasses(t)]"
                                                 x-text="getToothLabel(t).num">
                                            </div>
                                        </template>
                                    </div>
                                    <!-- LL -->
                                    <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[36px] min-h-[36px]">
                                        <template x-for="t in getQuadrantTeeth(selectedTeeth, 'LL')" :key="t">
                                            <div role="button" @click="toggle(t)"
                                                 class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-0 border-solid rounded-none cursor-pointer"
                                                 :class="[getQuadrantClasses(t)]"
                                                 x-text="getToothLabel(t).num">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedTeeth.length === 0">
                            <span class="text-xs text-gray-400 dark:text-gray-500 self-center" x-text="isReadOnly ? 'هیچ دندانی انتخاب نشده است' : 'روی دندان کلیک کنید تا انتخاب شود'"></span>
                        </template>
                    </div>
                </div>

                {{-- Per-tooth assignment panel --}}
                <div x-show="selectedService !== null" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="assign-panel shadow-lg shadow-indigo-100/40 dark:shadow-indigo-900/20">
                    <div class="assign-panel-head">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-indigo-800 dark:text-indigo-200" x-text="selectedService?.name ?? ''"></p>
                                <p class="text-[11px] text-indigo-500 dark:text-indigo-400 mt-0.5" x-text="selectedService?.category_name ?? ''"></p>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400" x-text="selectedTeeth.length"></span>
                                <span class="text-[10px] text-indigo-500 dark:text-indigo-400">دندان</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تنظیم دقیق هر دندان</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="showPerToothDetail" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left:0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-gradient-to-r peer-checked:from-violet-500 peer-checked:to-indigo-600"></div>
                            </label>
                        </div>
                        <button @click="cancelAssignment()" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <template x-if="selectedTeeth.length === 0">
                        <div class="flex items-center gap-2 px-5 py-3 bg-rose-50 dark:bg-rose-900/10 border-b border-rose-100 dark:border-rose-900/20">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs text-rose-600 dark:text-rose-400 font-medium">لطفاً ابتدا دندان‌ها را از نقشه انتخاب کنید</span>
                        </div>
                    </template>

                    {{-- Batch apply --}}
                    <template x-if="selectedTeeth.length > 0 && (selectedService?.custom_prices?.tabs?.length > 0)">
                        <div class="border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/20">
                            <div class="relative overflow-hidden px-5 py-4 bg-white dark:bg-gray-800 flex items-center gap-4 border-b border-gray-100 dark:border-gray-700/50">
                                <div class="absolute -right-6 -top-6 w-28 h-28 bg-indigo-400/15 dark:bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                <div class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-200/50 dark:shadow-indigo-900/40 shrink-0 border border-indigo-400/50">
                                    <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-5 h-5 rounded-full bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 text-[10px] font-black border border-indigo-100 dark:border-indigo-700 shadow-sm z-10">۱</span>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="relative z-10 flex-1">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <h4 class="text-[14px] font-black text-gray-800 dark:text-gray-100 tracking-wide">اعمال سریع روی همه</h4>
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[9px] font-bold tracking-wider border border-indigo-100 dark:border-indigo-800/50">اختیاری</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">برندهای مورد نظر را از هر تب انتخاب کنید</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-6">
                                <template x-for="(tab, tIdx) in (selectedService?.custom_prices?.tabs ?? [])" :key="tIdx">
                                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">
                                        <div class="font-bold text-sm text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                                            <span x-text="tab.title || 'تب ' + (tIdx + 1)"></span>
                                        </div>
                                        <template x-for="(section, sIdx) in (tab.sections || [])" :key="sIdx">
                                            <div class="mb-5 last:mb-0">
                                                <div x-show="section.title" class="text-xs font-semibold text-violet-600 dark:text-violet-400 mb-2">
                                                    <span x-text="section.title"></span>
                                                </div>
                                                <div class="relative" x-data="{ ddOpen: false }">
                                                    <div @click="ddOpen = !ddOpen" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-sm cursor-pointer flex justify-between items-center">
                                                        <div class="truncate">
                                                            <template x-if="getSelectedCountForTabAndSection(tIdx, sIdx) === 0">
                                                                <span class="text-gray-500">انتخاب برند...</span>
                                                            </template>
                                                            <template x-if="getSelectedCountForTabAndSection(tIdx, sIdx) > 0">
                                                                <span class="font-bold text-indigo-600" x-text="getSelectedCountForTabAndSection(tIdx, sIdx) + ' برند انتخاب شده'"></span>
                                                            </template>
                                                        </div>
                                                        <svg class="w-4 h-4 transition-transform" :class="ddOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                    <div x-show="ddOpen" @click.away="ddOpen = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto sc-thin">
                                                        <template x-for="(brand, bIdx) in (section.brands || [])" :key="bIdx">
                                                            <label class="flex items-center gap-3 px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                                <input type="checkbox" :checked="isBrandSelectedInBatch(tIdx, sIdx, bIdx)" @change="toggleBatchBrand(tIdx, sIdx, bIdx)" class="w-5 h-5 text-indigo-600 rounded border-gray-300">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center gap-2">
                                                                        <p class="font-medium" x-text="brand.name"></p>
                                                                        <span x-show="brand.is_installment && getInstallmentPlansForBrand(selectedService?.id, tab.title, section.title, brand.name).length > 0" class="inst-badge">
                                                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                                            </svg>
                                                                            اقساطی
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500" x-text="(Number(brand.price)||0).toLocaleString('fa-IR') + ' ' + currencyLabel"></p>
                                                                    <template x-if="brand.is_installment && isBrandSelectedInBatch(tIdx, sIdx, bIdx)">
                                                                        <div class="mt-2 space-y-1">
                                                                            <template x-for="instPlan in getInstallmentPlansForBrand(selectedService?.id, tab.title, section.title, brand.name)" :key="instPlan.id">
                                                                                <div class="text-[10px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded-lg">
                                                                                    <span x-text="instPlan.title"></span>
                                                                                    <span class="mx-1">·</span>
                                                                                    <span x-text="getEffectiveDownPayment(instPlan, selectedService?.id, tab.title, section.title, brand.name) + '% پیش‌پرداخت'"></span>
                                                                                    <span class="mx-1">·</span>
                                                                                    <span x-text="getEffectiveMonths(instPlan, selectedService?.id, tab.title, section.title, brand.name) + ' ماه'"></span>
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </label>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <div class="flex items-center justify-between bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                                    <div>
                                        <span class="text-sm text-gray-500">مبلغ کل اعمال شده:</span>
                                        <span class="block text-xl font-black text-emerald-600" x-text="formatPrice((batchManualPrice * selectedTeeth.length) + (selectedService ? Number(selectedService.base_price) || 0 : 0))"></span>
                                    </div>
                                    <button @click="applyBatchToAll()" class="flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold">
                                        اعمال روی همه دندان‌ها
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Per-tooth rows --}}
                    <template x-if="selectedTeeth.length > 0 && showPerToothDetail">
                        <div class="bg-gray-50/30 dark:bg-gray-800/10 pb-4" x-transition:enter="transition-all duration-300" x-transition:enter-start="opacity-0 max-h-0 overflow-hidden" x-transition:enter-end="opacity-100 max-h-[2000px]">
                            <div class="relative overflow-hidden px-5 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-4">
                                <div class="absolute -right-6 -top-6 w-28 h-28 bg-violet-400/15 dark:bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                <div class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-lg shadow-violet-200/50 dark:shadow-violet-900/40 shrink-0 border border-violet-400/50">
                                    <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-5 h-5 rounded-full bg-white dark:bg-gray-800 text-violet-600 dark:text-violet-400 text-[10px] font-black border border-violet-100 dark:border-violet-700 shadow-sm z-10">۲</span>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                </div>
                                <div class="relative z-10 flex-1">
                                    <h4 class="text-[14px] font-black text-gray-800 dark:text-gray-100 tracking-wide">تنظیم دقیق هر دندان</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">برای هر دندان برندهای دلخواه را انتخاب کنید</p>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <template x-for="(assignment, aIdx) in perToothAssignments" :key="assignment.toothId">
                                    <div class="bg-white dark:bg-gray-800 border rounded-3xl p-5 transition-all hover:shadow-md" :class="assignment.modified ? 'border-violet-300 dark:border-violet-700/50 bg-violet-50/30' : 'border-gray-200 dark:border-gray-700'">
                                        <div class="flex items-center gap-4 mb-6">
                                            <div class="w-12 h-12 rounded-none flex items-center justify-center font-black text-xl bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border-0 shadow-sm relative shrink-0 border-solid" :class="getQuadrantClasses(assignment.toothId)">
                                                <span x-text="getToothLabel(assignment.toothId).num"></span>
                                                <span x-show="assignment.modified" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-violet-500 border-2 border-white dark:border-gray-800"></span>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">دندان</span>
                                                <span class="block text-lg font-bold" x-text="getToothLabel(assignment.toothId).num"></span>
                                            </div>
                                        </div>
                                        <div class="space-y-6">
                                            <template x-for="(tab, tIdx) in (selectedService?.custom_prices?.tabs ?? [])" :key="tIdx">
                                                <div class="border border-gray-100 dark:border-gray-700 rounded-2xl p-4">
                                                    <div class="font-bold text-sm text-violet-600 dark:text-violet-400 mb-4" x-text="tab.title || 'تب ' + (tIdx + 1)"></div>
                                                    <template x-for="(section, sIdx) in (tab.sections || [])" :key="sIdx">
                                                        <div class="mb-6 last:mb-0">
                                                            <div x-show="section.title" class="text-xs uppercase tracking-widest font-semibold text-violet-500 mb-2" x-text="section.title"></div>
                                                            <div class="relative" x-data="{ ddOpen: false }">
                                                                <div @click="ddOpen = !ddOpen" class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-sm cursor-pointer flex justify-between items-center hover:border-violet-400 transition-all">
                                                                    <div class="truncate">
                                                                        <template x-if="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) === 0">
                                                                            <span class="text-gray-400">انتخاب برند از این بخش...</span>
                                                                        </template>
                                                                        <template x-if="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) > 0">
                                                                            <span class="font-bold text-violet-600" x-text="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) + ' برند انتخاب شده'"></span>
                                                                        </template>
                                                                    </div>
                                                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="ddOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </div>
                                                                <div x-show="ddOpen" @click.away="ddOpen = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto sc-thin">
                                                                    <template x-for="(brand, bIdx) in (section.brands || [])" :key="bIdx">
                                                                        <label class="flex items-center gap-3 px-4 py-3 hover:bg-violet-50 dark:hover:bg-violet-900/20 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                                            <input type="checkbox" :checked="isBrandSelectedForTooth(aIdx, tIdx, sIdx, bIdx)" @change="toggleToothBrand(aIdx, tIdx, sIdx, bIdx)" class="w-5 h-5 text-violet-600 rounded border-gray-300">
                                                                            <div class="flex-1">
                                                                                <div class="flex items-center gap-2">
                                                                                    <p class="font-medium" x-text="brand.name"></p>
                                                                                    <span x-show="brand.is_installment && getInstallmentPlansForBrand(selectedService?.id, tab.title, section.title, brand.name).length > 0" class="inst-badge">اقساطی</span>
                                                                                </div>
                                                                                <p class="text-xs text-gray-500" x-text="(Number(brand.price)||0).toLocaleString('fa-IR') + ' ' + currencyLabel"></p>
                                                                            </div>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">مبلغ واحد این دندان</span>
                                            <div class="text-right">
                                                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400" x-text="formatPrice(assignment.price)"></span>
                                                <span class="text-xs text-gray-400" x-text="currencyLabel"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Group preview --}}
                    <template x-if="selectedTeeth.length > 0 && assignmentGroups.length > 0">
                        <div>
                            <div class="step-label">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                پیش‌نمایش آیتم‌های طرح (گروه‌بندی خودکار)
                            </div>
                            <div class="group-preview-bar">
                                <template x-if="assignmentGroups.length === 1">
                                    <span class="group-chip">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        همه دندان‌ها در یک آیتم ادغام می‌شوند
                                    </span>
                                </template>
                                <template x-if="assignmentGroups.length > 1">
                                    <template x-for="(grp, gIdx) in assignmentGroups" :key="gIdx">
                                        <span class="group-chip">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/></svg>
                                            <span>دندان‌های
                                                <template x-for="(t, ti) in grp.teeth" :key="t">
                                                    <span><span x-text="getToothLabel(t).num"></span><span x-show="ti < grp.teeth.length - 1">، </span></span>
                                                </template>
                                                ←
                                                <span x-text="grp.brands && grp.brands.length > 0 ? grp.brands.map(b => b.name).join(' + ') : 'قیمت پایه'"></span>
                                                (<span x-text="formatPrice(grp.price)"></span> <span x-text="currencyLabel"></span>)
                                            </span>
                                        </span>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- WARRANTY / GUARANTEE SECTION --}}
                    <template x-if="selectedTeeth.length > 0 && settings.cure.warranty_enabled">
                        <div>
                            <div class="step-label">
                                <svg class="w-3 h-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.950 11.950 0 0112 2.944a11.950 11.950 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                تنظیمات ضمانت و گارانتی
                            </div>
                            <div class="px-5 py-4 bg-gradient-to-b from-teal-50/40 to-gray-50/30 dark:from-teal-900/10 dark:to-gray-800/20 border-t border-teal-100/60 dark:border-teal-800/30">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">مدت ضمانت (ماه)</label>
                                        <div class="relative">
                                            <input type="number" x-model.number="warrantyMonths" :min="settings.cure.default_warranty_months || 0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100 text-center dir-ltr focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900/30 transition-all pl-12" placeholder="0">
                                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">ماه</div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1.5">
                                            <span x-show="settings.cure.default_warranty_months > 0">حداقل مدت مجاز: <span class="font-bold text-rose-500" x-text="settings.cure.default_warranty_months"></span> ماه · </span>۰ = بدون ضمانت
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">متن ضمانت</label>
                                        <input type="text" x-model="warrantyText" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900/30 transition-all" :placeholder="settings.cure.default_warranty_text || 'مثال: گارانتی تعویض رایگان تا ۶ ماه'">
                                        <p class="text-[10px] text-gray-400 mt-1.5">اگر خالی باشد، مدت ضمانت به صورت خودکار نمایش داده می‌شود.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Footer --}}
                    <div class="assign-footer">
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">مجموع این افزودنی:</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400" x-text="formatPrice(assignmentTotal)"></span>
                                <span class="text-xs text-gray-400" x-text="currencyLabel"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="cancelAssignment()" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm font-medium hover:border-gray-300 transition-all">انصراف</button>
                            <button @click="addToPlan()" :disabled="!canAdd" :class="!canAdd ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-xl hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'" class="flex items-center gap-2 px-5 py-2 rounded-xl text-white font-bold text-sm transition-all" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span x-text="assignmentGroups.length > 1 ? 'افزودن ' + assignmentGroups.length + ' آیتم به طرح' : 'افزودن به طرح'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Workflow Bindings and Tooth Details Panel -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-5 space-y-4 mt-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 rounded-full bg-indigo-500 shrink-0"></span>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm">گردش‌کارهای متصل</h3>
                        </div>
                        <button x-show="!isReadOnly" type="button" @click="openAddBindingModal('plan')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">
                            اتصال گردش‌کار جدید
                        </button>
                    </div>

                    <!-- Selected Tooth Detail Card (Slide-down/fade) -->
                    <div x-show="selectedToothForWorkflow" x-transition class="bg-indigo-50/50 dark:bg-indigo-950/15 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
                                <h4 class="font-black text-xs text-indigo-900 dark:text-indigo-200" x-text="'جزئیات و گردش‌کارهای دندان ' + (selectedToothForWorkflow ? getToothLabel(selectedToothForWorkflow).num : '')"></h4>
                            </div>
                            <button type="button" @click="selectedToothForWorkflow = null" class="text-xs font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">بستن</button>
                        </div>

                        <!-- Actions for this tooth -->
                        <div class="flex flex-wrap gap-2">
                            <button x-show="!isReadOnly" type="button" @click="openAddBindingModal('tooth', selectedToothForWorkflow)" class="px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900/25 transition-all">
                                + اتصال گردش‌کار به این دندان
                            </button>
                            <a x-show="existingPlan" :href="existingPlan ? '/user/booking/cure/' + existingPlan.id + '/workflows?tooth=' + selectedToothForWorkflow : '#'" class="px-2.5 py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold hover:bg-indigo-700 transition-all">
                                مشاهده بوم فرآیندهای دندان <span x-text="selectedToothForWorkflow ? getToothLabel(selectedToothForWorkflow).num : ''"></span>
                            </a>
                        </div>

                        <!-- Active instances for this tooth -->
                        <div x-show="existingPlan" class="space-y-1.5 mt-2">
                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 font-bold">فرآیندهای در حال اجرا روی این دندان:</p>
                            <div class="space-y-1">
                                <template x-for="winst in (existingPlan?.workflows || []).filter(w => w.tooth_context == selectedToothForWorkflow)" :key="winst.id">
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-2.5 rounded-xl">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="text-[11px] font-bold text-gray-800 dark:text-gray-200" x-text="winst.workflow_name"></span>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-500" x-text="winst.current_node_name || 'مرحله شروع'"></span>
                                    </div>
                                </template>
                                <template x-if="!(existingPlan?.workflows || []).some(w => w.tooth_context == selectedToothForWorkflow)">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 italic">هیچ فرآیند فعالی برای این دندان وجود ندارد.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Bindings Table/List -->
                    <div class="overflow-x-auto sc-thin">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                    <th class="py-2.5 font-bold">گردش‌کار</th>
                                    <th class="py-2.5 font-bold">سطح اتصال</th>
                                    <th class="py-2.5 font-bold">شرایط شروع</th>
                                    <th class="py-2.5 font-bold">اجرای خودکار</th>
                                    <th class="py-2.5 font-bold text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="binding in workflowBindings" :key="binding.id">
                                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                        <td class="py-3">
                                            <div class="font-bold text-gray-800 dark:text-gray-200" x-text="binding.workflow?.name"></div>
                                        </td>
                                        <td class="py-3">
                                            <span x-show="binding.scope === 'plan'" class="px-2 py-0.5 text-[10px] font-black bg-slate-50 dark:bg-slate-700/30 text-slate-600 dark:text-slate-400 rounded-md">کل طرح درمان</span>
                                            <span x-show="binding.scope === 'item'" class="px-2 py-0.5 text-[10px] font-black bg-purple-50 dark:bg-purple-900/10 text-purple-600 dark:text-purple-400 rounded-md">یک آیتم خاص</span>
                                            <span x-show="binding.scope === 'tooth'" class="px-2 py-0.5 text-[10px] font-black bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400 rounded-md" x-text="binding.tooth === 'all' ? 'همه دندان‌ها' : 'دندان ' + (binding.tooth ? getToothLabel(binding.tooth).num : '')"></span>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="st in (binding.trigger_statuses || [])" :key="st">
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400" x-text="getStatusName(st)"></span>
                                                </template>
                                                <template x-if="!(binding.trigger_statuses || []).length">
                                                    <span class="text-gray-400 dark:text-gray-500">همه وضعیت‌ها</span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span :class="binding.auto_trigger ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'" class="font-bold" x-text="binding.auto_trigger ? 'بله' : 'خیر'"></span>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button x-show="existingPlan" type="button" @click="triggerWorkflowBinding(binding.id)" title="اجرای دستی فرآیند" class="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                                <button x-show="!isReadOnly" type="button" @click="openEditBindingModal(binding)" title="ویرایش" class="p-1 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button x-show="!isReadOnly" type="button" @click="deleteWorkflowBinding(binding.id)" title="حذف" class="p-1 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="workflowBindings.length === 0">
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400 dark:text-gray-500 italic">
                                            هیچ گردش‌کاری به این طرح درمان متصل نشده است.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /left col --}}

            {{-- RIGHT COL --}}
            <div class="xl:col-span-1 sticky top-4 space-y-4">

                {{-- Plan Items --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col max-h-[650px]">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm">
                            <span class="w-2 h-5 rounded-full bg-violet-500 shrink-0"></span>
                            آیتم‌های طرح درمان
                            <span class="inline-flex items-center justify-center px-1.5 min-w-5 h-5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 text-[10px] font-bold" x-text="planItems.length"></span>
                        </h3>
                        <button x-show="planItems.length > 0 && !isReadOnly" @click="planItems = []" class="text-[11px] text-rose-500 hover:text-rose-700 font-medium transition-colors">پاک کردن همه</button>
                    </div>
                    <div x-show="planItems.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center px-4">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-400 dark:text-gray-500">طرح درمان شما خالی است</p>
                    </div>
                    <div x-show="planItems.length > 0" class="flex-1 overflow-y-auto sc-thin p-3 space-y-3">
                        <template x-for="(group, gIdx) in groupedPlanItems" :key="group.service.id">
                            <div class="bg-gray-50/70 dark:bg-gray-700/30 rounded-2xl border border-gray-200 dark:border-gray-600 overflow-hidden">
                                <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700/60 border-b border-gray-200/70 dark:border-gray-600 flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                                    <span class="font-bold text-sm text-gray-800 dark:text-gray-100" x-text="group.service.name"></span>
                                </div>
                                <div class="p-3 space-y-2">
                                    <template x-for="(item, idx) in group.items" :key="item.id">
                                        <div class="relative bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 group/item">
                                            <div class="absolute top-3 left-3 flex gap-1 opacity-0 group-hover/item:opacity-100 transition-all z-10">
                                                <button x-show="settings.cure.show_tooth_filter" @click.stop="toggleHighlightItem(item.id)" :class="highlightedItemId === item.id ? 'bg-emerald-600 text-white' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30'" class="p-2 rounded-lg transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5 16.477 5 20.268 7.943 21.542 12 20.268 16.057 16.477 19 12 19 7.523 19 3.732 16.057 2.458 12z"/></svg>
                                                </button>
                                                <button @click.stop="editItem(item.id)" title="ویرایش" x-show="!isReadOnly" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-indigo-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button @click.stop="removeItem(item.id)" title="حذف" x-show="!isReadOnly" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 text-rose-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold shrink-0">دندان‌ها:</span>
                                                <div class="inline-grid grid-cols-2 select-none">
                                                    <!-- Row 1: UR | UL -->
                                                    <!-- UR -->
                                                    <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[28px] min-h-[24px]">
                                                        <template x-for="t in getQuadrantTeeth(item.teeth, 'UR')" :key="t">
                                                            <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                        </template>
                                                    </div>
                                                    <!-- UL -->
                                                    <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[28px] min-h-[24px]">
                                                        <template x-for="t in getQuadrantTeeth(item.teeth, 'UL')" :key="t">
                                                            <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                        </template>
                                                    </div>

                                                    <!-- Row 2: LR | LL -->
                                                    <!-- LR -->
                                                    <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[28px] min-h-[24px]">
                                                        <template x-for="t in getQuadrantTeeth(item.teeth, 'LR')" :key="t">
                                                            <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                        </template>
                                                    </div>
                                                    <!-- LL -->
                                                    <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[28px] min-h-[24px]">
                                                        <template x-for="t in getQuadrantTeeth(item.teeth, 'LL')" :key="t">
                                                            <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <template x-if="item.brands && item.brands.length > 0">
                                                <div class="flex flex-wrap gap-1 mb-3">
                                                    <template x-for="b in item.brands" :key="b.name">
                                                        <span class="text-xs px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                                                            <span x-text="(b.sectionTitle ? b.sectionTitle + ' : ' : '') + b.name"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="settings.cure.warranty_enabled && item.warranty">
                                                <div class="flex items-center gap-2 mb-3"><span class="text-xs px-3 py-1 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-full flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.950 11.950 0 0112 2.944a11.950 11.950 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>

                                                        ضمانت: <span x-text="item.warranty"></span>
                                                    </span>
                                                </div>
                                            </template>

                                            <!-- نمایش قیمت پایه سرویس (فقط اگر بزرگتر از 0 باشد) -->
                                            <template x-if="item.base_price > 0">
                                                <div class="mb-2 flex items-center justify-between text-[11px] bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700 rounded-lg px-2.5 py-1.5">
                                                    <span class="text-slate-500 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            قیمت پایه سرویس
                                                    </span>
                                                    <span class="font-bold text-slate-600 dark:text-slate-300" x-text="formatPrice(item.base_price) + ' ' + currencyLabel"></span>
                                                </div>
                                            </template>

                                            <div class="flex justify-between items-end mt-2">
                                                <div>
                                                    <span class="text-xs text-gray-500" x-text="item.base_price > 0 ? 'قیمت برندها (واحد)' : 'قیمت واحد'"></span>
                                                    <div class="flex gap-1.5 items-baseline">
                                                        <span class="block text-lg font-bold text-emerald-600" x-text="formatPrice(item.price)"></span>
                                                        <span class="text-xs text-emerald-600 mt-1" x-text="currencyLabel"></span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs text-gray-500" x-text="useInstallment && selectedInstallmentOption && itemHasInstallmentBrands(item) ? 'مجموع (اقساطی)' : 'جمع'"></span>

                                                    <!-- نمایش حالت اقساطی (به صورت خودکار اگر قسطی انتخاب شده باشد) -->
                                                    <template x-if="useInstallment && selectedInstallmentOption && itemHasInstallmentBrands(item)">
                                                        <div class="text-right flex flex-col items-end">
                                                            <div class="flex gap-1.5 items-baseline">
                                                                <span class="block font-black text-xl text-indigo-600" x-text="formatPrice(getItemInstallmentInfo(item).totalPayable)"></span>
                                                                <span class="text-xs text-indigo-600 mt-1" x-text="currencyLabel"></span>
                                                            </div>
                                                            <div class="text-[10px] text-indigo-500 mt-0.5 flex items-center gap-1">
                                                                <span>پیش‌پرداخت:</span>
                                                                <b x-text="formatPrice(getItemInstallmentInfo(item).downPayment) + ' ' + currencyLabel"></b>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <!-- نمایش حالت نقدی -->
                                                    <template x-if="!(useInstallment && selectedInstallmentOption && itemHasInstallmentBrands(item))">
                                                        <div>
                                                            <div class="text-right flex gap-1.5 items-baseline">
                                                                <span class="block font-black text-xl text-violet-600" x-text="formatPrice(getItemDiscountedTotal(item))"></span>
                                                                <span class="text-xs text-violet-600 mt-1" x-text="currencyLabel"></span>
                                                            </div>
                                                            <template x-if="discountValue > 0 && (item.price * item.quantity) !== getItemDiscountedTotal(item)">
                                                                <div class="mt-0.5">
                                                                    <span class="text-[11px] text-gray-400 line-through" x-text="formatPrice(item.price * item.quantity) + ' ' + currencyLabel"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>{{-- /right col --}}
        </div>{{-- /main grid --}}

        <!-- اطلاعات تکمیلی و تاریخچه طرح درمان -->
        <div x-show="(assignableRoles && assignableRoles.length > 0) || !isReadOnly || notes || (snapshots && snapshots.length > 0)"
             class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mt-5">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gradient-to-l from-indigo-50/50 to-transparent dark:from-indigo-900/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <h2 class="font-bold text-gray-800 dark:text-white">جزئیات و تاریخچه تغییرات طرح درمان</h2>
                </div>
            </div>

            <div class="p-6">
                <div class="flex flex-col lg:flex-row gap-8 items-start">

                    <!-- ستون سمت راست: کادر درمان و یادداشت‌ها -->
                    <div class="flex-1 lg:max-w-md w-full space-y-6">
                        <!-- بخش انتساب کادر درمان -->
                        <div x-show="assignableRoles && assignableRoles.length > 0" class="space-y-4">
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 text-sm border-b border-gray-100 dark:border-gray-700 pb-2">
                                <span class="w-1.5 h-4 rounded-full bg-indigo-500 shrink-0"></span>
                                انتساب کادر درمان
                            </h3>
                            <div class="space-y-3.5">
                                <template x-for="role in assignableRoles" :key="role.role_id">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-500 mb-1.5" x-text="'انتخاب ' + role.role_label"></label>
                                        <div x-show="isReadOnly" class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700" x-text="getAssignedUserName(role.role_id)"></div>
                                        <div x-show="!isReadOnly"
                                             x-data="{
                                                 open: false,
                                                 search: '',
                                                 get selectedUserId() { return getAssignedUserId(role.role_id); },
                                                 get selectedUser() { return role.users.find(u => Number(u.id) === Number(this.selectedUserId)); },
                                                 get filteredUsers() {
                                                     if(!this.search) return role.users;
                                                     return role.users.filter(u => u.name.toLowerCase().includes(this.search.toLowerCase()));
                                                 }
                                             }"
                                             class="relative">

                                            <!-- Trigger -->
                                            <button type="button" @click="open = !open" @click.away="open = false"
                                                    class="w-full flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs shadow-sm hover:border-indigo-300 dark:hover:border-indigo-600 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30">

                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <template x-if="selectedUser">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0" x-text="selectedUser.name.charAt(0)"></div>
                                                            <span class="font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="selectedUser.name"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedUser">
                                                        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
                                                            <div class="w-6 h-6 rounded-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0">
                                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                            </div>
                                                            <span>انتخاب نشده</span>
                                                        </div>
                                                    </template>
                                                </div>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-cloak
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100"
                                                 x-transition:leave-end="opacity-0"
                                                 class="absolute z-50 mt-1.5 w-full rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl shadow-indigo-100/30 dark:shadow-black/50 overflow-hidden">

                                                <!-- Search -->
                                                <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80">
                                                    <div class="relative">
                                                        <input type="text" x-model="search" @click.stop placeholder="جستجوی همکار..."
                                                               class="w-full pl-2 pr-8 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 dark:text-gray-200 transition-all placeholder-gray-400">
                                                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                    </div>
                                                </div>

                                                <!-- Options list -->
                                                <div class="max-h-48 overflow-y-auto sc-thin p-1.5">
                                                    <!-- None option -->
                                                    <button type="button" @click="setAssignedUser(role.role_id, role.role_name, '', role.users); open = false; search = ''"
                                                            class="w-full text-right px-3 py-2 text-xs rounded-xl transition-all flex items-center justify-between border border-transparent"
                                                            :class="!selectedUserId ? 'bg-rose-50 border-rose-100 text-rose-700 dark:bg-rose-900/20 dark:border-rose-800/30 dark:text-rose-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0" :class="!selectedUserId ? 'bg-rose-100 text-rose-500 dark:bg-rose-900/40' : 'bg-gray-100 dark:bg-gray-700'">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                            </div>
                                                            <span class="font-medium">بدون انتساب</span>
                                                        </div>
                                                        <svg x-show="!selectedUserId" class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </button>

                                                    <!-- User options -->
                                                    <template x-for="u in filteredUsers" :key="u.id">
                                                        <button type="button" @click="setAssignedUser(role.role_id, role.role_name, u.id, role.users); open = false; search = ''"
                                                                class="w-full text-right px-3 py-2 mt-1 text-xs rounded-xl transition-all flex items-center justify-between border border-transparent"
                                                                :class="Number(selectedUserId) === Number(u.id) ? 'bg-indigo-50 border-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800/30 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-[10px] shrink-0"
                                                                     :class="Number(selectedUserId) === Number(u.id) ? 'bg-indigo-200 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200 shadow-inner' : 'bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-gray-300'"
                                                                     x-text="u.name.charAt(0)"></div>
                                                                <span x-text="u.name" class="font-medium"></span>
                                                            </div>
                                                            <svg x-show="Number(selectedUserId) === Number(u.id)" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </button>
                                                    </template>

                                                    <div x-show="filteredUsers.length === 0" class="py-4 text-center text-[11px] text-gray-400 dark:text-gray-500 font-medium">
                                                        کاربری یافت نشد
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- بخش یادداشت‌ها -->
                        <div x-show="!isReadOnly || notes" class="space-y-4">
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 text-sm border-b border-gray-100 dark:border-gray-700 pb-2">
                                <span class="w-1.5 h-4 rounded-full bg-teal-500 shrink-0"></span>
                                یادداشت‌ها
                                <span x-show="settings.cure.require_notes" class="text-rose-500 text-xs">*</span>
                            </h3>
                            <div>
                                <div x-show="isReadOnly" class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700 min-h-[120px]" x-text="notes"></div>
                                <textarea x-show="!isReadOnly" x-model="notes" rows="5" :required="settings.cure.require_notes" placeholder="توضیحات مربوط به طرح..." class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900/30 resize-none transition-all"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ستون سمت چپ: تاریخچه تغییرات وضعیت -->
                    <div x-show="snapshots && snapshots.length > 0" class="flex-[2] min-w-[320px] space-y-4">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 text-sm border-b border-gray-100 dark:border-gray-700 pb-2">
                            <span class="w-1.5 h-4 rounded-full bg-amber-500 shrink-0"></span>
                            تاریخچه تغییرات وضعیت
                        </h3>
                        <div class="timeline-container pr-8 space-y-6 max-h-[480px] overflow-y-auto sc-thin">
                            <!-- Vertical Timeline Line -->
                            <div class="timeline-line"></div>

                            <template x-for="(snap, idx) in snapshots" :key="snap.id">
                                <div class="relative" x-data="{ open: false }">
                                    <!-- Timeline Dot Node -->
                                    <div class="absolute -right-[19px] top-[18px] w-4 h-4 rounded-full border-2 border-white dark:border-gray-800 timeline-glow-dot z-10 transition-all duration-300"
                                         :style="{ backgroundColor: getStatusColor(snap.status_to), boxShadow: '0 0 0 4px ' + getStatusColor(snap.status_to) + '25' }"></div>

                                    <!-- Timeline Card Trigger -->
                                    <div class="timeline-node-card flex items-center justify-between gap-4 cursor-pointer p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 shadow-sm hover:shadow-md transition-all duration-300" @click="open = !open">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                                <template x-if="!snap.status_from">
                                                    <span class="flex flex-wrap items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                        <span>ایجاد اولیه طرح در وضعیت</span>
                                                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold shadow-sm"
                                                              :style="{ backgroundColor: getStatusColor(snap.status_to) + '15', color: getStatusColor(snap.status_to), border: '1px solid ' + getStatusColor(snap.status_to) + '30' }"
                                                              x-text="snap.status_to_label"></span>
                                                    </span>
                                                </template>
                                                <template x-if="snap.status_from && snap.status_from === snap.status_to">
                                                    <span class="flex flex-wrap items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                        <span>به‌روزرسانی طرح در وضعیت</span>
                                                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold shadow-sm"
                                                              :style="{ backgroundColor: getStatusColor(snap.status_to) + '15', color: getStatusColor(snap.status_to), border: '1px solid ' + getStatusColor(snap.status_to) + '30' }"
                                                              x-text="snap.status_to_label"></span>
                                                    </span>
                                                </template>
                                                <template x-if="snap.status_from && snap.status_from !== snap.status_to">
                                                    <span class="flex flex-wrap items-center gap-1.5">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                        <span>تغییر وضعیت از</span>
                                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-extrabold"
                                                              :style="{ backgroundColor: getStatusColor(snap.status_from) + '15', color: getStatusColor(snap.status_from) }"
                                                              x-text="snap.status_from_label || 'نامشخص'"></span>
                                                        <span class="text-gray-400 mx-0.5 font-normal">←</span>
                                                        <span>به</span>
                                                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold shadow-sm"
                                                              :style="{ backgroundColor: getStatusColor(snap.status_to) + '15', color: getStatusColor(snap.status_to), border: '1px solid ' + getStatusColor(snap.status_to) + '30' }"
                                                              x-text="snap.status_to_label"></span>
                                                    </span>
                                                </template>
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-gray-50 dark:bg-gray-700/60 text-[11px] text-gray-500 dark:text-gray-400">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                <span x-text="snap.changed_by_name"></span>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <a :href="'{{ route('user.booking.cure.snapshot', ['cure' => '__CURE__', 'snapshot' => '__SNAP__']) }}'.replace('__CURE__', existingPlan?.id).replace('__SNAP__', snap.id)" target="_blank" @click.stop class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition-all text-xs font-black shadow-sm active:scale-95">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                <span>مشاهده</span>
                                            </a>
                                            <span class="text-[11px] text-gray-400 font-medium" x-text="snap.display_date"></span>
                                            <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Mobile View Button -->
                                    <div x-show="open" class="sm:hidden mt-2 px-4">
                                        <a :href="'{{ route('user.booking.cure.snapshot', ['cure' => '__CURE__', 'snapshot' => '__SNAP__']) }}'.replace('__CURE__', existingPlan?.id).replace('__SNAP__', snap.id)" target="_blank" class="flex w-full justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-black text-sm border border-indigo-200">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <span>مشاهده کامل نسخه این وضعیت</span>
                                        </a>
                                    </div>

                                    <!-- Expanded detail sheet -->
                                    <div x-show="open" x-collapse class="mt-3 p-5 rounded-2xl bg-slate-50/30 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-700 shadow-inner space-y-6">

                                        <!-- Notes -->
                                        <div x-show="snap.notes" class="bg-indigo-50/50 dark:bg-indigo-950/20 p-4 rounded-xl border border-indigo-100/50 dark:border-indigo-900/30">
                                            <h4 class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 mb-1.5 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                توضیحات و یادداشت تغییرات
                                            </h4>
                                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium" x-text="snap.notes"></p>
                                        </div>

                                        <!-- Financial Summary Grid -->
                                        <div x-data="{
                                                get rawTotal() {
                                                    if (!snap.items) return 0;
                                                    return snap.items.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity || 1)), 0);
                                                },
                                                get discountVal() {
                                                    if (snap.discount_type === 'percent') {
                                                        return (this.rawTotal * Number(snap.discount_amount || 0)) / 100;
                                                    }
                                                    return Number(snap.discount_amount || 0);
                                                },
                                                get finalTotal() { return Math.max(0, this.rawTotal - this.discountVal); }
                                            }">

                                            <h4 class="text-xs font-black text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                                <span>خلاصه مالی و نحوه تسویه</span>
                                            </h4>

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                                                <!-- Raw Total -->
                                                <div class="bg-white dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                                                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">جمع کل خدمات</span>
                                                    <span class="font-mono text-base font-black text-gray-700 dark:text-gray-300 mt-1" x-text="formatPrice(rawTotal) + ' ' + currencyLabel"></span>
                                                </div>
                                                <!-- Discount -->
                                                <div class="bg-rose-50/30 dark:bg-rose-950/15 p-4 rounded-2xl border border-rose-100/50 dark:border-rose-900/20 flex flex-col justify-between">
                                                    <span class="text-[10px] font-bold text-rose-500 dark:text-rose-450 uppercase">تخفیف اعمال شده</span>
                                                    <span class="font-mono text-base font-black text-rose-600 dark:text-rose-400 mt-1" x-text="'−' + formatPrice(discountVal) + ' ' + currencyLabel"></span>
                                                </div>
                                                <!-- Final Payable -->
                                                <div class="payable-spotlight p-4 rounded-2xl flex flex-col justify-between relative overflow-hidden">
                                                    <div class="absolute -right-6 -top-6 w-16 h-16 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-xl pointer-events-none"></div>
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 relative">مبلغ نهایی قابل پرداخت</span>
                                                    <span class="font-mono text-lg font-black text-emerald-600 dark:text-emerald-400 mt-1 relative" x-text="formatPrice(finalTotal) + ' ' + currencyLabel"></span>
                                                </div>
                                            </div>

                                            <!-- Installment Details (If any) -->
                                            <template x-if="snap.installment_option_title || snap.installment_months > 0">
                                                <div class="bg-indigo-50/30 dark:bg-indigo-950/20 p-4 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/30 mb-5">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                        <span class="text-xs font-black text-indigo-800 dark:text-indigo-300" x-text="'طرح بازپرداخت اقساطی: ' + (snap.installment_option_title || 'سفارشی')"></span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400">
                                                            <span>پیش‌پرداخت:</span>
                                                            <span class="font-black text-indigo-600 dark:text-indigo-400" x-text="formatPrice(snap.installment_down_payment) + ' ' + currencyLabel"></span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400">
                                                            <span>مبلغ هر قسط:</span>
                                                            <span class="font-black text-indigo-600 dark:text-indigo-400" x-text="formatPrice(snap.installment_monthly_amount) + ' ' + currencyLabel"></span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400">
                                                            <span>مدت بازپرداخت:</span>
                                                            <span class="font-black text-indigo-600 dark:text-indigo-400" x-text="toFaDigits(snap.installment_months) + ' ماه'"></span>
                                                        </div>
                                                        <template x-if="snap.installment_fee_value > 0">
                                                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400">
                                                                <span>کارمزد تقسیط:</span>
                                                                <span class="font-black text-rose-500" x-text="formatPrice(snap.installment_fee_value) + ' ' + currencyLabel"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Services Table -->
                                        <div>
                                            <h4 class="text-xs font-black text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 112-2h2a2 2 0 012 2" /></svg>
                                                <span>سرویس‌های ثبت شده در این نسخه</span>
                                            </h4>
                                            <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                                                <table class="w-full text-right text-xs">
                                                    <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                                        <tr>
                                                            <th class="px-5 py-3 font-bold text-gray-600 dark:text-gray-400">سرویس / خدمت</th>
                                                            <th class="px-5 py-3 font-bold text-center text-gray-600 dark:text-gray-400">موقعیت دندان</th>
                                                            <th class="px-5 py-3 font-bold text-center text-gray-600 dark:text-gray-400">تعداد</th>
                                                            <th class="px-5 py-3 font-bold text-left text-gray-600 dark:text-gray-400">هزینه کل</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                        <template x-for="item in snap.items" :key="item.service_id">
                                                            <tr class="hover:bg-indigo-50/10 dark:hover:bg-indigo-950/5 transition-colors text-gray-700 dark:text-gray-300">
                                                                <td class="px-5 py-3.5 font-bold" x-text="item.service_name"></td>
                                                                <td class="px-5 py-3.5 text-center">
                                                                    <template x-if="item.teeth && item.teeth.length > 0">
                                                                        <div class="flex justify-center mt-1">
                                                                            <div class="inline-grid grid-cols-2 select-none">
                                                                                <!-- Row 1: UR | UL -->
                                                                                <!-- UR -->
                                                                                <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-700 pb-1 pl-2 flex items-center justify-end gap-1 min-w-[24px] min-h-[22px]">
                                                                                    <template x-for="t in getQuadrantTeeth(item.teeth, 'UR')" :key="t">
                                                                                        <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                                                    </template>
                                                                                </div>
                                                                                <!-- UL -->
                                                                                <div class="border-b-2 border-slate-300 dark:border-slate-700 pb-1 pr-2 flex items-center justify-start gap-1 min-w-[24px] min-h-[22px]">
                                                                                    <template x-for="t in getQuadrantTeeth(item.teeth, 'UL')" :key="t">
                                                                                        <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                                                    </template>
                                                                                </div>

                                                                                <!-- Row 2: LR | LL -->
                                                                                <!-- LR -->
                                                                                <div class="border-l-2 border-slate-300 dark:border-slate-700 pt-1 pl-2 flex items-center justify-end gap-1 min-w-[24px] min-h-[22px]">
                                                                                    <template x-for="t in getQuadrantTeeth(item.teeth, 'LR')" :key="t">
                                                                                        <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                                                    </template>
                                                                                </div>
                                                                                <!-- LL -->
                                                                                <div class="pt-1 pr-2 flex items-center justify-start gap-1 min-w-[24px] min-h-[22px]">
                                                                                    <template x-for="t in getQuadrantTeeth(item.teeth, 'LL')" :key="t">
                                                                                        <span class="inline-flex items-center justify-center w-6 h-6 text-[10px] font-black rounded-none border-0 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 transition-all border-solid" :class="getQuadrantClasses(t)" x-text="getToothLabel(t).num"></span>
                                                                                    </template>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </template>
                                                                    <span x-show="!item.teeth || item.teeth.length === 0" class="text-gray-400">-</span>
                                                                </td>
                                                                <td class="px-5 py-3.5 text-center font-mono font-bold text-gray-500 dark:text-gray-400" x-text="item.quantity"></td>
                                                                <td class="px-5 py-3.5 text-left font-mono font-black text-gray-800 dark:text-gray-300" x-text="formatPrice(item.price * item.quantity) + ' ' + currencyLabel"></td>
                                                            </tr>
                                                        </template>
                                                        <tr x-show="!snap.items || snap.items.length === 0">
                                                            <td colspan="4" class="px-5 py-8 text-center text-gray-400 italic">هیچ سرویسی در این نسخه ثبت نشده بود.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- ══════════════════ FINANCIAL SUMMARY (Compact) ══════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mt-5">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gradient-to-l from-emerald-50/50 to-transparent dark:from-emerald-900/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                    <h2 class="font-bold text-gray-800 dark:text-white">خلاصه مالی</h2>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 lg:grid-cols-[2fr_3fr] gap-5 items-start">

                    {{-- ══════ RIGHT: روش پرداخت ══════ --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">روش پرداخت</label>

                        <div class="payment-pill-group relative">
                            <!-- Sliding Indicator -->
                            <div class="absolute top-[6px] bottom-[6px] w-[calc(50%-8px)] rounded-xl transition-all duration-300 cubic-bezier(0.4, 0, 0.2, 1) z-0 shadow-md"
                                 :class="!useInstallment ? 'right-[6px] bg-emerald-600 shadow-emerald-500/25 dark:shadow-emerald-950/50' : 'left-[6px] bg-indigo-600 shadow-indigo-500/25 dark:shadow-indigo-950/50'">
                            </div>

                            <button type="button" @click="useInstallment = false; selectedInstallmentOptionId = null"
                                    class="payment-pill-btn"
                                    :class="!useInstallment ? 'text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                                <svg class="w-4 h-4 transition-transform duration-300" :class="!useInstallment ? 'scale-110' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="relative font-bold text-xs md:text-sm">نقدی</span>
                            </button>
                            <button type="button" @click="useInstallment = true"
                                    class="payment-pill-btn relative"
                                    :class="useInstallment ? 'text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                                <span x-show="installmentTypes.length > 0" class="absolute -top-1.5 -left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-rose-500 text-white border border-white dark:border-gray-800" x-text="installmentTypes.length"></span>
                                <svg class="w-4 h-4 transition-transform duration-300" :class="useInstallment ? 'scale-110' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="relative font-bold text-xs md:text-sm">اقساطی</span>
                            </button>
                        </div>

                        {{-- لیست طرح‌های قسطی --}}
                        <div x-show="useInstallment" x-transition class="space-y-1.5">
                            <!-- پیام در صورت خالی بودن طرح درمان -->
                            <template x-if="planItems.length === 0">
                                <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 dark:text-gray-400 text-center">
                                    ابتدا دندان‌ها و سرویس‌ها را به طرح درمان اضافه کنید.
                                </div>
                            </template>

                            <!-- پیام در صورتی که آیتم وجود دارد اما طرح اقساطی برای آن یافت نشد -->
                            <template x-if="planItems.length > 0 && eligibleInstallmentOptions.length === 0">
                                <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 text-[11px] text-amber-700 dark:text-amber-400">
                                    برندهای انتخاب‌شده مشمول اقساط نمی‌شوند یا مبلغ طرح در بازه مجاز نیست.
                                </div>
                            </template>

                            <!-- نمایش لیست طرح‌های اقساطی -->
                            <template x-if="eligibleInstallmentOptions.length > 0">
                                <div class="space-y-2 max-h-56 overflow-y-auto sc-thin pr-1">
                                    <template x-for="option in eligibleInstallmentOptions" :key="option.id">
                                        <div @click="selectedInstallmentOptionId = option.id"
                                             class="flex items-center justify-between gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition-all hover:shadow-sm"
                                             :class="selectedInstallmentOptionId === option.id ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/15 shadow-inner' : 'border-gray-100 dark:border-gray-700/60 hover:border-indigo-200 dark:hover:border-indigo-900 bg-white/40 dark:bg-gray-800/40'">

                                             <div class="flex-1 min-w-0">
                                                 <p class="font-bold text-gray-800 dark:text-white text-xs truncate" x-text="option.title || 'طرح بدون نام'"></p>
                                                 <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                     <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300">
                                                         پیش‌پرداخت: <b x-text="getPlanSummaryInfo(option).dp + '%'"></b>
                                                     </span>
                                                     <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300">
                                                         مدت: <b x-text="getPlanSummaryInfo(option).months + ' ماه'"></b>
                                                     </span>
                                                     <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                                         تقسیط: <b x-text="getPlanSummaryInfo(option).stages + ' مرحله'"></b>
                                                     </span>
                                                 </div>
                                             </div>

                                             <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                  :class="selectedInstallmentOptionId === option.id ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600'">
                                                 <svg x-show="selectedInstallmentOptionId === option.id" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                     <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                 </svg>
                                             </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ══════ LEFT: جزئیات روش انتخاب‌شده ══════ --}}
                    <div x-transition class="bg-gray-50/60 dark:bg-gray-900/20 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 min-h-[320px] flex flex-col justify-between transition-all">

                        {{-- حالت نقدی --}}
                        <template x-if="!useInstallment">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-450 font-bold mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="text-xs">جزئیات پرداخت نقدی</span>
                                </div>

                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700/80 overflow-hidden bg-white/40 dark:bg-gray-900/40 divide-y divide-gray-100 dark:divide-gray-800">
                                    <div class="receipt-row">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">جمع کل خدمات</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-200" x-text="formatPrice(totalPrice) + ' ' + currencyLabel"></span>
                                    </div>

                                    <div x-show="settings.cure.allow_discount" class="p-4 space-y-3 bg-white/30 dark:bg-gray-900/20">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">تخفیف طرح درمان</span>
                                            <div x-show="settings.cure.discount_type === 'both' && !isReadOnly" class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-0.5 border border-gray-200/50 dark:border-gray-700">
                                                <button type="button" @click="discountType = 'amount'" class="px-3 py-1 rounded-md text-[10px] font-bold transition-all" :class="discountType === 'amount' ? 'bg-white dark:bg-gray-700 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-gray-500 dark:text-gray-400'">مبلغ</button>
                                                <button type="button" @click="discountType = 'percent'" class="px-3 py-1 rounded-md text-[10px] font-bold transition-all" :class="discountType === 'percent' ? 'bg-white dark:bg-gray-700 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-gray-500 dark:text-gray-400'">درصد</button>
                                            </div>
                                        </div>
                                        <template x-if="!isReadOnly">
                                            <div>
                                                <div class="relative">
                                                    <input x-show="discountType === 'percent'" type="number" min="0" :max="settings.cure.max_discount_percent" x-model.number="discountAmount" @input="if (discountAmount > settings.cure.max_discount_percent) discountAmount = settings.cure.max_discount_percent; if (discountAmount < 0) discountAmount = 0;" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs text-gray-800 dark:text-gray-100 dir-ltr text-left focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-100 dark:focus:ring-rose-950/30 transition-all pl-12" placeholder="0">
                                                    <input x-show="discountType !== 'percent'" type="text" inputmode="numeric" x-model="discountAmountDisplay" @input="onDiscountAmountInput($event)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs text-gray-800 dark:text-gray-100 dir-ltr text-left focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-100 dark:focus:ring-rose-950/30 transition-all pl-12" placeholder="0">
                                                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-500 text-xs font-bold">
                                                        <span x-text="discountType === 'percent' ? '%' : currencyLabel"></span>
                                                    </div>
                                                </div>
                                                <p x-show="discountType === 'percent'" class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">حداکثر مجاز: <span class="font-bold text-rose-500" x-text="settings.cure.max_discount_percent"></span>%</p>
                                            </div>
                                        </template>
                                        <div x-show="isReadOnly" class="text-xs text-gray-600 dark:text-gray-300">
                                            <span x-text="discountType === 'percent' ? discountAmount + '%' : formatPrice(discountAmount) + ' ' + currencyLabel"></span>
                                        </div>
                                    </div>

                                    <div x-show="discountValue > 0" class="receipt-row text-xs bg-rose-50/40 dark:bg-rose-950/10">
                                        <span class="text-rose-500 font-bold">مبلغ تخفیف اعمال‌شده</span>
                                        <span class="font-bold text-rose-600 dark:text-rose-400" x-text="'−' + formatPrice(discountValue) + ' ' + currencyLabel"></span>
                                    </div>

                                    <div class="payable-spotlight p-4 rounded-b-2xl flex justify-between items-center relative overflow-hidden">
                                        <div class="absolute -right-6 -top-6 w-16 h-16 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-xl pointer-events-none"></div>
                                        <span class="text-emerald-750 dark:text-emerald-400 font-bold text-sm relative">مبلغ قابل پرداخت</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-black text-xl relative" x-text="formatPrice(finalPayable) + ' ' + currencyLabel"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- حالت قسطی، هنوز طرحی انتخاب نشده --}}
                        <template x-if="useInstallment && !selectedInstallmentOption">
                            <div class="h-full flex flex-col items-center justify-center text-center py-6 gap-2">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">یک طرح اقساطی را از سمت راست انتخاب کنید</p>
                            </div>
                        </template>

                        {{-- حالت قسطی، طرح انتخاب شده --}}
                        <template x-if="useInstallment && selectedInstallmentOption">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-bold" x-text="selectedInstallmentOption.title || 'صورت‌حساب قسطی'"></span>
                                </div>

                                {{-- انتخاب مدت بازپرداخت --}}
                                <div x-show="availableInstallmentMonths.length > 0" class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block mb-1.5">حداکثر مدت بازپرداخت (ماه)</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="m in availableInstallmentMonths" :key="m">
                                            <button type="button" @click="selectedInstallmentMonths = m" class="px-3 py-1.5 rounded-lg border-2 text-xs font-bold transition-all" :class="selectedInstallmentMonths === m ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 shadow-sm' : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-indigo-300'">
                                                <span x-text="toFa(m) + ' ماه'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- برندهای مشمول و غیرمشمول --}}
                                <div x-show="mixedPaymentBreakdown.covered.length > 0 || mixedPaymentBreakdown.uncovered.length > 0" class="space-y-2 p-2.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div x-show="mixedPaymentBreakdown.covered.length > 0">
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mb-1.5 block">مشمول اقساط</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in mixedPaymentBreakdown.covered" :key="'c-'+item.brandName">
                                                <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40">
                                                    <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400" x-text="item.brandName"></span>
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-300" x-text="formatPrice(item.price) + ' ' + currencyLabel"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div x-show="mixedPaymentBreakdown.uncovered.length > 0" class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-1">
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold mb-1.5 block">غیرمشمول (نقدی)</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in mixedPaymentBreakdown.uncovered" :key="'u-'+item.brandName">
                                                <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40">
                                                    <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-400" x-text="item.brandName"></span>
                                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-300" x-text="formatPrice(item.price) + ' ' + currencyLabel"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- خلاصه مالی --}}
                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700/80 overflow-hidden bg-white/40 dark:bg-gray-900/40 divide-y divide-gray-100 dark:divide-gray-800">
                                    <!-- مبلغ نقدی (غیرمشمول) -->
                                    <div x-show="uncoveredTotal > 0" class="receipt-row text-xs bg-amber-50/20 dark:bg-amber-950/5">
                                        <span class="text-amber-600 dark:text-amber-400">مبلغ نقدی (غیرمشمول)</span>
                                        <span class="font-bold text-amber-700 dark:text-amber-300" x-text="formatPrice(uncoveredTotal) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- مبلغ مشمول اقساط -->
                                    <div class="receipt-row text-xs">
                                        <span class="text-gray-500">مبلغ مشمول اقساط</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="formatPrice(installmentTotalAmount) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- پیش‌پرداخت -->
                                    <div class="receipt-row text-xs bg-indigo-50/10 dark:bg-indigo-950/5">
                                        <span class="text-indigo-600 dark:text-indigo-400">پیش‌پرداخت (<span x-text="effectiveDownPaymentPct + '%'"></span>)</span>
                                        <span class="font-bold text-indigo-700 dark:text-indigo-300" x-text="formatPrice(downPaymentAmount) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- مانده اصل (پس از کسر پیش‌پرداخت) -->
                                    <div class="receipt-row text-xs bg-blue-50/10 dark:bg-blue-950/5">
                                        <span class="text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            مانده اصل
                                        </span>
                                        <span class="font-black text-blue-600 dark:text-blue-400" x-text="formatPrice(installmentTotalAmount - downPaymentAmount) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- سود اقساط با جزئیات -->
                                    <div x-show="effectiveFeePct > 0 || annualFeePct > 0" class="receipt-row text-xs bg-rose-50/20 dark:bg-rose-950/5">
                                        <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1">
                                            <template x-if="effectiveMonths >= 12 && annualFeePct > 0">
                                                 <span>کارمزد سالانه (<span x-text="annualFeePct"></span>% × <span x-text="(effectiveMonths / 12).toFixed(1)"></span> سال)</span>
                                            </template>
                                            <template x-if="!(effectiveMonths >= 12 && annualFeePct > 0)">
                                                 <span>سود اقساط (<span x-text="effectiveFeePct"></span>% × <span x-text="effectiveMonths"></span> ماه)</span>
                                            </template>
                                        </span>
                                        <span class="font-bold text-rose-600 dark:text-rose-400" x-text="formatPrice(installmentFeeValue) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- جمع کل اقساط -->
                                    <div class="receipt-row text-xs bg-indigo-50/25 dark:bg-indigo-950/10">
                                         <span class="text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1.5">
                                             <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                             جمع کل اقساط
                                         </span>
                                         <span class="text-indigo-600 dark:text-indigo-400 font-black" x-text="formatPrice(targetChequesTotal) + ' ' + currencyLabel"></span>
                                    </div>
                                    <!-- مبلغ هر قسط -->
                                    <div class="payable-spotlight p-4 rounded-b-2xl flex justify-between items-center relative overflow-hidden">
                                        <div class="absolute -right-6 -top-6 w-16 h-16 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-xl pointer-events-none"></div>
                                        <div class="text-emerald-750 dark:text-emerald-400 font-bold text-sm flex items-center gap-1.5 relative">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            مبلغ هر قسط
                                        </div>
                                        <div class="text-left relative">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black text-xl" x-text="formatPrice(monthlyPaymentAmount) + ' ' + currencyLabel"></span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-400 block text-left" x-text="toFa(numberOfCheques) + ' قسط'"></span>
                                        </div>
                                    </div>
                                </div>
                                {{-- دکمه تاگل بخش چک‌ها --}}
                                <button type="button" @click="showChequeSection = !showChequeSection" class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold text-sm flex items-center justify-center gap-2 hover:shadow-lg transition-all mt-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span x-text="showChequeSection ? 'بستن بخش چک‌ها' : 'تایید و ساخت چک‌ها'"></span>
                                </button>

                                {{-- ══════ بخش مخفی چک‌ها ══════ --}}
                                <div x-show="showChequeSection" x-transition class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 space-y-4 mt-2">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                                        {{-- تعداد چک‌ها --}}
                                        <div x-show="selectedInstallmentMonths > 0" class="relative overflow-hidden rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800/30 bg-gradient-to-br from-indigo-50 to-violet-50/60 dark:from-indigo-900/15 dark:to-violet-900/10">
                                            <div class="absolute -left-6 -top-6 w-24 h-24 bg-indigo-300/10 dark:bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                            <div class="relative flex items-center justify-between mb-3">
                                                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                                                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-indigo-500/30">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </span>
                                                    تعداد چک‌ها
                                                </span>
                                                <span class="text-[10px] font-bold text-indigo-400 dark:text-indigo-400/80 bg-white/70 dark:bg-gray-900/40 px-2 py-0.5 rounded-full">از ۱ تا <span x-text="toFa(selectedInstallmentMonths)"></span> عدد</span>
                                            </div>
                                            <input type="number" min="1" :max="selectedInstallmentMonths" x-model.number="numberOfCheques"
                                                   class="relative w-full px-4 py-2.5 rounded-xl border-2 border-indigo-200 dark:border-indigo-700/50 bg-white dark:bg-gray-900 text-base text-indigo-700 dark:text-indigo-300 dir-ltr text-center focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-black shadow-sm">
                                            <p class="relative text-[10.5px] text-indigo-500/80 dark:text-indigo-400/70 mt-2.5 leading-relaxed flex items-center gap-1 flex-wrap">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>فرمول: (کل − پیش‌پرداخت + سود) ÷ <b x-text="numberOfCheques || '؟'"></b> =</span>
                                                <b class="text-indigo-700 dark:text-indigo-300" x-text="formatPrice(monthlyPaymentAmount) + ' ' + currencyLabel"></b>
                                            </p>
                                        </div>
                                        {{-- نام بانک --}}
                                        <div class="relative overflow-hidden rounded-2xl p-4 border border-emerald-100 dark:border-emerald-800/30 bg-gradient-to-br from-emerald-50 to-teal-50/60 dark:from-emerald-900/15 dark:to-teal-900/10">
                                            <div class="absolute -left-6 -top-6 w-24 h-24 bg-emerald-300/10 dark:bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                            <label class="relative text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5 mb-3">
                                                <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-500/30">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m16-11v11M8 14v3m4-3v3m4-3v3"/></svg>
                                                </span>
                                                نام بانک (برای چک‌های خودکار)
                                            </label>
                                            <div class="relative">
                                                <input type="text" x-model="chequeBankName"
                                                       class="w-full pl-4 pr-10 py-2.5 rounded-xl border-2 border-emerald-200 dark:border-emerald-700/50 bg-white dark:bg-gray-900 text-sm text-emerald-700 dark:text-emerald-300 font-bold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-900/30 transition-all shadow-sm"
                                                       placeholder="مثال: ملت، صادرات...">
                                                <svg class="absolute top-1/2 -translate-y-1/2 right-3 w-4 h-4 text-emerald-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </div>
                                            <p class="relative text-[10.5px] text-emerald-500/80 dark:text-emerald-400/70 mt-2.5">این نام برای تمام چک‌هایی که به‌صورت خودکار تولید می‌شوند ثبت خواهد شد.</p>
                                        </div>
                                    </div>

                                    {{-- تقویم جلالی --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">تاریخ شروع اقساط</label>
                                        <div class="max-w-md mx-auto">
                                            <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl p-2 shadow-sm border border-gray-200 dark:border-gray-700 mb-4">
                                                <button type="button" @click="!isReadOnly && prevInstMonth()" class="p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300" :disabled="isReadOnly">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                </button>
                                                <div class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="jalaliMonthNames[instCalMonth - 1] + ' ' + toFaDigits(instCalYear)"></div>
                                                <button type="button" @click="!isReadOnly && nextInstMonth()" class="p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300" :disabled="isReadOnly">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7 -7 7-7" /></svg>
                                                </button>
                                            </div>
                                            <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <div class="grid grid-cols-7 gap-2 mb-4 text-center text-xs font-bold text-gray-400" dir="rtl">
                                                    <template x-for="w in ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج']" :key="w">
                                                        <div class="py-2 border-b border-gray-100 dark:border-gray-800" x-text="w"></div>
                                                    </template>
                                                </div>
                                                <div class="grid grid-cols-7 gap-2 text-center">
                                                    <template x-for="(dayObj, idx) in instCalDays" :key="idx">
                                                        <div class="aspect-square">
                                                            <div x-show="dayObj.empty" class="w-full h-full"></div>
                                                            <button x-show="!dayObj.empty" type="button" @click="!isReadOnly && selectInstDate(dayObj)"
                                                                    class="w-full h-full aspect-square rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-300"
                                                                    :class="dayObj.selected ? 'bg-indigo-600 text-white shadow-lg scale-105 ring-2 ring-indigo-200' : (dayObj.valid ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 border border-gray-100 dark:border-gray-600 cursor-pointer' : 'bg-gray-50 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed')"
                                                                    :disabled="!dayObj.valid || isReadOnly" x-text="toFaDigits(dayObj.day)">
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- دکمه‌های تولید چک --}}
                                    <div class="flex flex-col gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <div x-show="!isReadOnly" class="grid grid-cols-2 gap-3">
                                            <button type="button" @click="generateCheques()" class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 hover:shadow-lg transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>تولید خودکار <span x-text="toFa(numberOfCheques)"></span> چک</span>
                                            </button>
                                            <button type="button" @click="showManualChequeForm = !showManualChequeForm" class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-700 border-2 border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-300 font-bold text-xs flex items-center justify-center gap-1.5 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span x-text="showManualChequeForm ? 'بستن فرم دستی' : 'ثبت چک دستی'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- فرم ثبت/ویرایش چک دستی --}}
                                    <div x-show="showManualChequeForm && !isReadOnly" x-transition class="bg-gray-50 dark:bg-gray-700/20 rounded-xl p-4 border-2 border-dashed border-indigo-200 dark:border-indigo-700 space-y-3">
                                        <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span x-text="editingManualChequeIndex === null ? 'ثبت چک دستی' : 'ویرایش چک دستی'"></span>
                                        </h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">شماره چک</label>
                                                <input type="text" x-model="manualChequeNumber" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:border-indigo-400 transition-all dir-ltr text-left" placeholder="123456">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">مبلغ چک</label>
                                                <div class="relative">
                                                    <input type="text" inputmode="numeric" x-model="manualChequeAmountDisplay" @input="onManualChequeAmountInput($event)"
                                                           class="w-full pl-14 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:border-indigo-400 transition-all dir-ltr text-left font-bold" placeholder="500,000">
                                                    <span class="absolute top-1/2 -translate-y-1/2 left-2 text-[10px] font-bold text-gray-400" x-text="currencyLabel"></span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">نام بانک</label>
                                                <input type="text" x-model="manualChequeBankName" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:border-indigo-400 transition-all" placeholder="ملت">
                                            </div>
                                            <div class="relative">
                                                <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 mb-1">تاریخ سررسید</label>
                                                <button type="button" @click="openManualCal()"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:outline-none focus:border-indigo-400 transition-all flex items-center justify-between"
                                                        :class="showManualChequeCalendar ? 'border-indigo-400 ring-1 ring-indigo-100' : ''">
                                                    <span x-text="manualChequeDate ? toFaDigits(manualChequeDate) : 'انتخاب تاریخ...'"></span>
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </button>
                                            </div>

                                            <!-- مودال مرکزی تقویم (برای جلوگیری از بریدگی) -->
                                            <div x-show="showManualChequeCalendar"
                                                 x-transition.opacity
                                                 class="fixed inset-0 z-[100] bg-black/50 flex items-center justify-center p-4"
                                                 @click.self="showManualChequeCalendar = false"
                                                 style="display: none;">

                                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-xs" x-transition.scale>

                                                    <!-- هدر تقویم -->
                                                    <div class="flex items-center justify-between mb-4">
                                                        <button type="button" @click="changeManualMonth(-1)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                        </button>
                                                        <div class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="jalaliMonthNames[manualChequeCalMonth - 1] + ' ' + toFaDigits(manualChequeCalYear)"></div>                                                        <button type="button" @click="changeManualMonth(1)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7 -7 7-7" /></svg>
                                                        </button>
                                                    </div>

                                                    <!-- نام روزهای هفته -->
                                                    <div class="grid grid-cols-7 gap-1 mb-2 text-center">
                                                        <template x-for="w in ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج']" :key="w">
                                                            <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 py-1" x-text="w"></div>
                                                        </template>
                                                    </div>

                                                    <!-- روزهای ماه -->
                                                    <div class="grid grid-cols-7 gap-1 text-center">
                                                        <template x-for="(dayObj, idx) in manualChequeCalDays" :key="idx">
                                                            <div class="aspect-square w-full">
                                                                <div x-show="dayObj.empty" class="w-full h-full"></div>
                                                                <button x-show="!dayObj.empty" type="button" @click="pickManualDate(dayObj)"
                                                                        class="w-full h-full flex items-center justify-center rounded-lg text-xs font-bold transition-all"
                                                                        :class="dayObj.selected
                                    ? 'bg-indigo-600 text-white shadow-lg scale-105'
                                    : (dayObj.valid
                                        ? 'text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 cursor-pointer'
                                        : 'text-gray-300 dark:text-gray-700 cursor-not-allowed')"
                                                                        :disabled="!dayObj.valid" x-text="toFaDigits(dayObj.day)">
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- دکمه بستن -->
                                                    <button type="button" @click="showManualChequeCalendar = false" class="w-full mt-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                                        بستن تقویم
                                                    </button>
                                                </div>
                                            </div>                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="editingManualChequeIndex === null ? addManualCheque() : saveManualChequeEdit()" class="flex-1 px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 hover:bg-emerald-700 transition-all">
                                                <svg x-show="editingManualChequeIndex === null" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <svg x-show="editingManualChequeIndex !== null" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span x-text="editingManualChequeIndex === null ? 'افزودن این چک به لیست' : 'تایید ویرایش'"></span>
                                            </button>
                                            <button x-show="editingManualChequeIndex !== null" type="button" @click="cancelManualChequeEdit()" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-bold text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                                                انصراف
                                            </button>
                                        </div>
                                    </div>

                                    {{-- لیست چک‌ها --}}
                                    <div x-show="generatedCheques.length > 0" class="mt-3 space-y-2 max-h-60 overflow-y-auto sc-thin pr-1">
                                        <div class="flex justify-between items-center px-2 mb-1 gap-2 flex-wrap">
                                            <span class="text-[10px] font-bold text-gray-500 shrink-0">لیست چک‌ها</span>
                                            <div class="flex gap-2 items-center text-[10px] shrink-0">
                                                <span class="text-indigo-500 whitespace-nowrap flex items-center gap-2">
                                                    <span>مجموع چک‌ها: <b x-text="formatPrice(totalChequesAmount) + ' ' + currencyLabel"></b></span>
                                                    <span x-show="Math.abs(targetChequesTotal - totalChequesAmount) > 1" class="text-rose-500">
                                                        (<span x-text="(targetChequesTotal - totalChequesAmount) > 0 ? 'کمبود:' : 'اضافه:'"></span>
                                                        <b x-text="formatPrice(Math.abs(targetChequesTotal - totalChequesAmount)) + ' ' + currencyLabel"></b>)
                                                    </span>
                                                </span>                                                <button x-show="!isReadOnly" type="button" @click="clearAllCheques()" class="flex items-center gap-1 px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-900/30 font-bold transition-all whitespace-nowrap">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    پاک کردن همه
                                                </button>
                                            </div>
                                        </div>
                                        <template x-for="(cheque, index) in generatedCheques" :key="cheque.id || index">
                                            <div class="flex items-start justify-between gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-300 transition-all group relative">
                                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5"
                                                         :class="cheque.isManual ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600' : 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600'">
                                                        <span x-text="cheque.isManual ? 'د' : toFaDigits(cheque.number)"></span>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5 flex-wrap">
                                                            <span x-text="cheque.isManual ? ('چک شماره ' + cheque.number) : ('قسط ' + toFaDigits(cheque.number))" class="truncate"></span>
                                                            <span x-show="cheque.number && !cheque.isManual" class="text-gray-400 font-normal shrink-0">از <span x-text="toFaDigits(cheque.total)"></span></span>
                                                        </p>
                                                        <div class="flex items-center flex-wrap gap-x-2 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                                            <span class="flex items-center gap-1">
                                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                <span x-text="cheque.display_date || cheque.date"></span>
                                                            </span>
                                                            <span x-show="cheque.bankName" class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-indigo-500 font-bold text-[10px] whitespace-nowrap">بانک <span x-text="cheque.bankName"></span></span>
                                                        </div>
                                                        <div x-show="!cheque.isManual" class="mt-1.5 flex items-center gap-1.5">
                                                            <span class="text-[10px] text-gray-400 shrink-0">شماره چک:</span>
                                                            <input type="text" x-model="cheque.chequeNumber" :disabled="isReadOnly"
                                                                   placeholder="اختیاری"
                                                                   class="w-28 px-2 py-1 text-[11px] rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 dir-ltr text-left focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-1.5 shrink-0">
                                                    <div class="text-left shrink-0">
                                                        <p class="text-sm font-black text-emerald-600 whitespace-nowrap" x-text="formatPrice(cheque.amount)"></p>
                                                        <p class="text-[10px] text-gray-400 text-left" x-text="currencyLabel"></p>
                                                    </div>
                                                    <div class="flex items-center gap-0.5">
                                                        <button x-show="!isReadOnly && cheque.isManual" @click="editManualCheque(index)" title="ویرایش" class="opacity-0 group-hover:opacity-100 transition-all text-indigo-400 hover:text-indigo-600 p-1">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        </button>
                                                        <button x-show="!isReadOnly" @click="removeCheque(index)" class="opacity-0 group-hover:opacity-100 transition-all text-rose-400 hover:text-rose-600 p-1">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <!-- Workflow Binding Modal -->
        <div x-show="bindingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="bindingModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl max-w-lg w-full overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base" x-text="editingBinding ? 'ویرایش اتصال گردش‌کار' : 'اتصال گردش‌کار جدید'"></h3>
                    <button type="button" @click="bindingModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-4 overflow-y-auto space-y-4 max-h-[500px] sc-thin" dir="rtl">
                    <!-- Workflow Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">انتخاب گردش‌کار <span class="text-rose-500">*</span></label>
                        <select :value="bindingForm.workflow_id" @change="bindingForm.workflow_id = $event.target.value" x-html="getWorkflowOptionsHtml()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500">
                        </select>
                    </div>

                    <!-- Help Guide banner -->
                    <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl space-y-2">
                        <p class="text-xs font-black text-indigo-900 dark:text-indigo-200">💡 راهنمای تعیین سطح اتصال:</p>
                        <ul class="text-[10px] text-indigo-700 dark:text-indigo-300 list-disc list-inside space-y-1.5 leading-relaxed">
                            <li><strong>سطح طرح درمان:</strong> گردش‌کار روی کل طرح درمان اجرا می‌شود (مانند ارسال پیامک خوش‌آمدگویی یا ثبت پرونده).</li>
                            <li><strong>سطح آیتم خاص:</strong> فرآیند فقط متصل به یک ردیف سرویس خاص است (مانند تخصیص وظیفه آماده‌سازی روکش برای یک آیتم درمان).</li>
                            <li><strong>سطح دندان خاص:</strong> فرآیند به صورت کاملاً مجزا برای هر دندان جداگانه اجرا و رهگیری می‌شود (مانند مراحل مختلف ایمپلنت روی دندان‌های ۱۲ و ۱۳ به طور مستقل).</li>
                        </ul>
                    </div>

                    <!-- Scope selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">سطح/محدوده اتصال <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all animate-none"
                                   :class="bindingForm.scope === 'plan' ? 'border-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/10 text-indigo-600 dark:text-indigo-400' : 'border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-200'">
                                <input type="radio" x-model="bindingForm.scope" value="plan" class="sr-only">
                                <span class="text-xs font-black">طرح درمان</span>
                                <span class="text-[9px] mt-1 text-center">اجرا برای کل طرح</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all animate-none"
                                   :class="bindingForm.scope === 'item' ? 'border-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/10 text-indigo-600 dark:text-indigo-400' : 'border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-200'">
                                <input type="radio" x-model="bindingForm.scope" value="item" class="sr-only">
                                <span class="text-xs font-black">آیتم خاص</span>
                                <span class="text-[9px] mt-1 text-center">اجرا روی یک آیتم درمان</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all animate-none"
                                   :class="bindingForm.scope === 'tooth' ? 'border-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/10 text-indigo-600 dark:text-indigo-400' : 'border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-200'">
                                <input type="radio" x-model="bindingForm.scope" value="tooth" class="sr-only">
                                <span class="text-xs font-black">دندان خاص</span>
                                <span class="text-[9px] mt-1 text-center">اجرا روی یک دندان خاص</span>
                            </label>
                        </div>
                    </div>

                    <!-- Scope specific details -->
                    <!-- Item Selection (For Item scope) -->
                    <div x-show="bindingForm.scope === 'item'">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">انتخاب آیتم طرح درمان</label>
                        <select :value="bindingForm.item_key" @change="bindingForm.item_key = $event.target.value" x-html="getItemOptionsHtml(true)" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500">
                        </select>
                    </div>

                    <!-- Tooth Selection details (For Tooth scope) -->
                    <div x-show="bindingForm.scope === 'tooth'">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">انتخاب آیتم طرح درمان مربوطه <span class="text-rose-500">*</span></label>
                        <select :value="bindingForm.item_key" @change="bindingForm.item_key = $event.target.value" x-html="getItemOptionsHtml(false)" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500">
                        </select>

                        <!-- Tooth checkboxes list -->
                        <div x-show="bindingForm.item_key" class="space-y-2 mt-3 bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700/60">
                            <label class="block text-[11px] font-black text-gray-600 dark:text-gray-400 mb-1">انتخاب دندان‌های هدف این گردش‌کار:</label>
                            <div class="flex flex-col gap-2">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-700 dark:text-indigo-400">
                                    <input type="checkbox" value="all"
                                           x-model="selectedTeethForBinding"
                                           @change="if (selectedTeethForBinding.includes('all')) selectedTeethForBinding = ['all']"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span>کل دندان‌های این آیتم (اجرا به صورت مجزا برای هر دندان)</span>
                                </label>

                                <div class="grid grid-cols-4 gap-2 mt-1 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <template x-for="t in (planItems.find(i => i.item_uuid === bindingForm.item_key)?.teeth || [])" :key="t">
                                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" :value="String(t)"
                                                   x-model="selectedTeethForBinding"
                                                   @change="selectedTeethForBinding = selectedTeethForBinding.filter(val => val !== 'all')"
                                                   class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span x-text="'دندان ' + getToothLabel(t).num"></span>
                                        </label>
                                    </template>
                                </div>

                                <template x-if="!(planItems.find(i => i.item_uuid === bindingForm.item_key)?.teeth || []).length">
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400 italic">این آیتم فاقد شماره دندان اختصاصی است. گردش‌کار به صورت عمومی روی آیتم اجرا خواهد شد.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Trigger statuses (Multiple checkbox select) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت‌های هدف برای اجرای خودکار</label>
                        <div class="grid grid-cols-2 gap-2.5 max-h-40 overflow-y-auto sc-thin p-1 border border-gray-100 dark:border-gray-800 rounded-xl">
                            <template x-for="st in cureStatuses" :key="st.id">
                                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" :value="st.id" x-model="bindingForm.trigger_statuses" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span x-text="st.name"></span>
                                </label>
                            </template>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">اگر هیچ وضعیتی انتخاب نشود، گردش‌کار در تمام تغییرات وضعیت اجرا خواهد شد.</p>
                    </div>

                    <!-- Previous status filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت قبلی طرح درمان (اختیاری)</label>
                        <select x-model="bindingForm.previous_status" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500">
                            <option value="">-- مهم نیست --</option>
                            <template x-for="st in cureStatuses" :key="st.id">
                                <option :value="st.id" x-text="st.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Minimum plan amount condition -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">حداقل مبلغ طرح درمان برای اجرا (تومان / ریال)</label>
                        <input type="number" x-model="bindingForm.min_amount" placeholder="مثال: 5,000,000" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Toggle switches -->
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">اجرای خودکار هنگام تغییر وضعیت</span>
                            <p class="text-[10px] text-gray-400 mt-0.5">در صورت غیرفعال بودن، فقط دستی اجرا می‌شود.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="bindingForm.auto_trigger" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-2 border-t border-gray-50 dark:border-gray-800/50 pt-3">
                        <div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">فعال بودن اتصال</span>
                            <p class="text-[10px] text-gray-400 mt-0.5">آیا این اتصال هم‌اکنون کار کند یا خیر.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="bindingForm.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
                    <button type="button" @click="bindingModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">انصراف</button>
                    <button type="button" @click="saveWorkflowBinding()" :disabled="isSubmittingBinding" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all disabled:opacity-50">
                        <span x-text="isSubmittingBinding ? 'در حال ثبت...' : 'ثبت اتصال'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>{{-- /x-data --}}

    <script>
        function showToast(msg, type = 'info', duration = 3000) {
            const el = document.getElementById('cure-toast');
            el.textContent = msg;
            el.className = `toast toast-${type}`;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => el.classList.add('show'));
            });
            setTimeout(() => el.classList.remove('show'), duration);
        }

        function treatmentPlanApp(services, existingPlan = null, isReadOnly = false, clients = [], appSettings = null, installmentTypesRaw = [], assignableRolesWithUsers = [], cureStatuses = []) {

            // استخراج امن روزهای مجاز از تنظیمات
            let rawDueDays = appSettings?.installment_due_days || appSettings?.cure_installment_due_days || [];
            if (typeof rawDueDays === 'string') {
                try { rawDueDays = JSON.parse(rawDueDays); } catch (e) {
                    rawDueDays = rawDueDays.split(',').map(d => parseInt(d, 10)).filter(d => !isNaN(d));
                }
            }
            if (!Array.isArray(rawDueDays)) rawDueDays = [];
            const parsedDueDays = rawDueDays.map(d => Number(d)).filter(d => d > 0);

            return {
                // ── Core state ──────────────────────────────────────────────
                isReadOnly: isReadOnly,
                isSaving: false,
                draftSaved: false,
                existingPlan: existingPlan,
                currentUserRoleIds: @js(auth()->user()->roles->pluck('id')->toArray()),
                assignableRoles: assignableRolesWithUsers,
                cureStatuses: cureStatuses,
                status: existingPlan?.status || appSettings?.cure_default_status || 'draft',
                assignedUsers: existingPlan?.assigned_users || [],
snapshots: existingPlan?.snapshots || [],

                // ── Workflow Bindings State ──
                workflowBindings: [],
                allWorkflows: @js($allWorkflows ?? []),
                bindingModalOpen: false,
                isSubmittingBinding: false,
                editingBinding: null,
                selectedToothForWorkflow: null, // used for specific tooth side-panel trigger
                selectedTeethForBinding: [], // selected teeth for the active binding modal

                // Form fields for new binding
                bindingForm: {
                    workflow_id: '',
                    scope: 'plan',
                    item_key: '',
                    tooth: '',
                    trigger_statuses: [],
                    previous_status: '',
                    min_amount: '',
                    auto_trigger: true,
                    is_active: true
                },

                getStatusProgressPercent() {
                    if (!this.cureStatuses || this.cureStatuses.length <= 1) return 0;
                    const sorted = [...this.cureStatuses].sort((a, b) => a.order - b.order);
                    const currentIndex = sorted.findIndex(s => s.id === this.status);
                    if (currentIndex === -1) return 0;
                    return (currentIndex / (sorted.length - 1)) * 100;
                },
                getStatusColor(statusId) {
                    const found = this.cureStatuses.find(s => s.id === statusId);
                    return found ? found.color : '#6b7280';
                },
                getStatusName(statusId) {
                    const found = this.cureStatuses.find(s => s.id === statusId);
                    return found ? found.name : statusId;
                },
                isStatusPassed(statusId) {
                    const currentSt = this.cureStatuses.find(s => s.id === this.status);
                    const checkSt = this.cureStatuses.find(s => s.id === statusId);
                    if (!currentSt || !checkSt) return false;
                    return checkSt.order < currentSt.order;
                },
                getAvailableTransitions() {
                    const userRoleIds = this.currentUserRoleIds || [];
                    const originalStatus = this.existingPlan?.status || this.settings?.cure?.default_status || 'draft';
                    return this.cureStatuses.filter(st => {
                        if (st.id === originalStatus) return false;

                        const allowedFrom = st.allowed_from || [];
                        if (allowedFrom.length > 0 && !allowedFrom.includes(originalStatus)) return false;

                        const allowedRoles = st.allowed_roles || [];
                        if (allowedRoles.length > 0) {
                            const intersection = allowedRoles.filter(rId => userRoleIds.map(Number).includes(Number(rId)));
                            if (intersection.length === 0) return false;
                        }

                        return true;
                    });
                },
                get hasPermissionForCurrentStatus() {
                    const currentStatusData = this.cureStatuses.find(s => s.id === this.status);
                    if (!currentStatusData) return true;
                    const allowedRoles = currentStatusData.allowed_roles || [];
                    if (allowedRoles.length > 0) {
                        const userRoleIds = this.currentUserRoleIds || [];
                        const intersection = allowedRoles.filter(rId => userRoleIds.map(Number).includes(Number(rId)));
                        if (intersection.length === 0) return false;
                    }
                    return true;
                },
                availableStatusesForSelection() {
                    const originalStatus = this.existingPlan?.status || this.settings?.cure?.default_status || 'draft';
                    const current = this.cureStatuses.find(s => s.id === originalStatus);
                    const transitions = this.getAvailableTransitions();
                    const list = [];
                    if (current) {
                        const userRoleIds = this.currentUserRoleIds || [];
                        const allowedRoles = current.allowed_roles || [];
                        let isAllowed = true;
                        if (allowedRoles.length > 0) {
                            const intersection = allowedRoles.filter(rId => userRoleIds.map(Number).includes(Number(rId)));
                            if (intersection.length === 0) isAllowed = false;
                        }
                        if (isAllowed) {
                            list.push(current);
                        }
                    }
                    transitions.forEach(tr => {
                        if (!list.some(item => item.id === tr.id)) {
                            list.push(tr);
                        }
                    });
                    return list;
                },
                getAssignedUserId(roleId) {
                    const found = this.assignedUsers.find(au => Number(au.role_id) === Number(roleId));
                    return found ? found.user_id : '';
                },
                getAssignedUserName(roleId) {
                    const found = this.assignedUsers.find(au => Number(au.role_id) === Number(roleId));
                    return found ? found.user_name : 'انتخاب نشده';
                },
                setAssignedUser(roleId, roleName, userId, usersList) {
                    this.assignedUsers = this.assignedUsers.filter(au => Number(au.role_id) !== Number(roleId));
                    if (userId) {
                        const user = usersList.find(u => Number(u.id) === Number(userId));
                        this.assignedUsers.push({
                            role_id: Number(roleId),
                            role_name: roleName,
                            user_id: Number(userId),
                            user_name: user ? user.name : ''
                        });
                    }
                },
                clients: clients,
                clientSearchLoading: false,
                clientId: null,
                patientName: '',
                planItems: [],
                notes: '',
                discountAmount: 0,
                discountAmountDisplay: '',
                discountType: 'amount',
                highlightedItemId: null,
                showPerToothDetail: false,
                editingItemId: null,

                // ── Teeth ────────────────────────────────────────────────────
                selectedTeeth: [],
                preset: 'none',
                upperJawIds: [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                lowerJawIds: [15,16,17,18,19,20,21,22,23,24,25,26,27,28],

                // ── Service picker ───────────────────────────────────────────
                services: services,
                selectedService: null,
                serviceSearch: '',
                filterCategory: null,
                perToothAssignments: [],
                batchBrandSelections: {},
                batchManualPrice: 0,
                warrantyMonths: 0,
                warrantyText: '',

                // ── Settings ─────────────────────────────────────────────────
                settings: {
                    currency: appSettings?.currency_unit || 'IRT',
                    tax_enabled: Number(appSettings?.tax_enabled) === 1,
                    tax_type: appSettings?.tax_type ?? 'PERCENT',
                    tax_amount: Number(appSettings?.tax_amount) || 0,
                    cure: {
                        default_status:           appSettings?.cure_default_status ?? 'draft',
                        allow_edit_confirmed:     Number(appSettings?.cure_allow_edit_confirmed) === 1,
                        allow_discount:           Number(appSettings?.cure_allow_discount) === 1,
                        discount_type:            appSettings?.cure_discount_type ?? 'both',
                        max_discount_percent:     Number(appSettings?.cure_max_discount_percent) ?? 100,
                        auto_tax:                 Number(appSettings?.cure_auto_tax) === 1,
                        warranty_enabled:         Number(appSettings?.cure_warranty_enabled) === 1,
                        default_warranty_months:  Number(appSettings?.cure_default_warranty_months) || 0,
                        default_warranty_text:    appSettings?.cure_default_warranty_text ?? '',
                        default_notes:            appSettings?.cure_default_notes ?? '',
                        require_notes:            Number(appSettings?.cure_require_notes) === 1,
                        tooth_numbering_system:   appSettings?.cure_tooth_numbering_system ?? 'universal',
                        auto_highlight_teeth:     Number(appSettings?.cure_auto_highlight_teeth) === 1,
                        show_tooth_filter:        Number(appSettings?.cure_show_tooth_filter) === 1,
                    },
                    rounding_mode: String(appSettings?.installment_rounding_mode ?? 'none').trim().toLowerCase(),
                    rounding_factor: Number(appSettings?.installment_rounding_factor) || 0,

                },

                // ── Installment ──────────────────────────────────────────────
                installmentTypes: Array.isArray(installmentTypesRaw) ? installmentTypesRaw : [],
                useInstallment: false,
                selectedInstallmentOptionId: null,
                selectedInstallmentMonths: null,
                numberOfCheques: 0,
                dueDays: parsedDueDays,
                selectedDueDay: parsedDueDays.length > 0 ? parsedDueDays[0] : null,
                installmentStartDate: '',
                generatedCheques: [],
                isInitializing: true,
                jalaliMonthNames: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'],
                instCalYear: null,
                instCalMonth: null,
                instCalDays: [],
                showChequeSection: false,
                chequeBankName: '',
                showManualChequeForm: false,
                manualChequeNumber: '',
                manualChequeAmount: 0,
                manualChequeAmountDisplay: '',
                manualChequeBankName: '',
                manualChequeDate: '',
                editingManualChequeIndex: null,
                showManualChequeCalendar: false,
                manualChequeCalYear: null,
                manualChequeCalMonth: null,
                manualChequeCalDays: [],

                makeBrandKey(serviceId, tabTitle, sectionTitle, brandName) {
                    const clean = (v) => String(v || '').trim();
                    return `${clean(serviceId)}__${clean(tabTitle)}__${clean(sectionTitle)}__${clean(brandName)}`;
                },

                isSingleChoiceType(type) {
                    const singleTypes = ['دو گزینه‌ای دارد یا ندارد', 'به ازای هر واحد', 'به ازای هر دندان', 'به ازای هر فک', 'به ازای کوادران', 'به ازای واحد دندان‌دار'];
                    return singleTypes.includes(type);
                },

                getEffectiveTierForBrand(plan, brandKey, amount) {
                    let tiers = plan.price_tiers;
                    if (typeof tiers === 'string') {
                        try { tiers = JSON.parse(tiers); } catch (e) { tiers = []; }
                    }
                    if (!Array.isArray(tiers)) {
                        tiers = Object.values(tiers);
                    }

                    const checkAmount = Number(amount) || 0;
                    let matchedTier = null;

                    if (Array.isArray(tiers) && tiers.length > 0) {
                        matchedTier = tiers.find(t => {
                            const min = Number(t.min_price) || 0;
                            const max = Number(t.max_price) || Infinity;
                            return checkAmount >= min && checkAmount <= max;
                        });
                    }

                    const extractConfig = (tierCfg) => {
                        if (!tierCfg) return null;
                        let downPaymentsMap = tierCfg.down_payments_map || {};
                        if (typeof downPaymentsMap === 'string') { try { downPaymentsMap = JSON.parse(downPaymentsMap); } catch (e) { downPaymentsMap = {}; } }

                        let feesMap = tierCfg.fees_map || {};
                        if (typeof feesMap === 'string') { try { feesMap = JSON.parse(feesMap); } catch (e) { feesMap = {}; } }

                        return {
                            max_months: Number(tierCfg.max_months) || 0,
                            payment_stages: Number(tierCfg.payment_stages) || 1,
                            down_payments_map: downPaymentsMap,
                            fees_map: feesMap,
                            down_payment: Number(tierCfg.down_payment) || 0,
                            annual_fee_percent: Number(tierCfg.annual_fee_percent) || 0
                        };
                    };

                    let defaultCfg = plan.default_tier_config;
                    if (typeof defaultCfg === 'string') {
                        try { defaultCfg = JSON.parse(defaultCfg); } catch (e) { defaultCfg = null; }
                    }

                    let configs = plan.brand_configs || {};
                    if (typeof configs === 'string') { try { configs = JSON.parse(configs); } catch (e) { configs = {}; } }

                    let brandCfg = configs[brandKey];
                    if (!brandCfg) {
                        const cleanStr = s => String(s || '').replace(/[\s_]+/g, '');
                        const parts = brandKey.split('__');
                        const matchKey = Object.keys(configs).find(k => {
                            const kParts = k.split('__');
                            return cleanStr(kParts[0]) === cleanStr(parts[0]) &&
                                cleanStr(kParts[1]) === cleanStr(parts[1]) &&
                                cleanStr(kParts[2]) === cleanStr(parts[2]) &&
                                cleanStr(kParts[3]) === cleanStr(parts[3]);
                        });
                        if (matchKey) brandCfg = configs[matchKey];
                    }

                    const isBasePrice = brandKey.includes('قیمت_پایه') || brandKey.includes('قیمت پایه');

                    if (!brandCfg && isBasePrice) {
                        const serviceId = brandKey.split('__')[0];
                        const fallbackKey = Object.keys(configs).find(k => k.startsWith(serviceId + '__') && configs[k]?.active !== false);
                        if (fallbackKey) {
                            brandCfg = configs[fallbackKey];
                        }
                    }

                    const isBrandActive = configs[brandKey] !== undefined ? configs[brandKey].active : (brandCfg !== undefined ? brandCfg.active : true);

                    if (!matchedTier) {
                        if (defaultCfg && Number(defaultCfg.max_months) > 0 && isBrandActive) {
                            return extractConfig(defaultCfg);
                        }
                        return null;
                    }

                    if (!brandCfg || !brandCfg.tiers) {
                        if (defaultCfg && Number(defaultCfg.max_months) > 0 && isBrandActive) {
                            return extractConfig(defaultCfg);
                        }
                        return null;
                    }

                    let tierCfg = brandCfg.tiers[matchedTier.id] || brandCfg.tiers[String(matchedTier.id)];

                    if (!tierCfg) {
                        if (defaultCfg && Number(defaultCfg.max_months) > 0 && isBrandActive) {
                            return extractConfig(defaultCfg);
                        }
                        return null;
                    }

                    const extracted = extractConfig(tierCfg);
                    if (extracted.max_months === 0 && defaultCfg && Number(defaultCfg.max_months) > 0 && isBrandActive) {
                        return extractConfig(defaultCfg);
                    }

                    return extracted;
                },
                isPlanApplicable(plan, amount) {
                    if (!amount || amount <= 0) return false;
                    const planBrands = this.getPlanInstallmentBrands();
                    if (planBrands.length === 0) return false;
                    return planBrands.some(b => this.getEffectiveTierForBrand(plan, b.key, amount) !== null);
                },

                get availableInstallmentMonths() {
                    if (!this.selectedInstallmentOption) return [];
                    const opt = this.selectedInstallmentOption;
                    const coveredBrands = this.getPlanInstallmentBrands().filter(b => {
                        return this.getEligibleBrandKeysForOption(opt).includes(b.key);
                    });

                    if (coveredBrands.length === 0) return [];

                    let commonMonths = null;
                    coveredBrands.forEach(b => {
                        const tier = this.getEffectiveTierForBrand(opt, b.key, this.finalPayable);
                        if (!tier || tier.payment_stages <= 0) {
                            commonMonths = new Set();
                            return;
                        }
                        const months = new Set();
                        for (let m = tier.payment_stages; m <= tier.max_months; m += tier.payment_stages) {
                            months.add(m);
                        }
                        if (commonMonths === null) {
                            commonMonths = months;
                        } else {
                            commonMonths = new Set([...commonMonths].filter(m => months.has(m)));
                        }
                    });

                    return commonMonths ? Array.from(commonMonths).sort((a, b) => a - b) : [];
                },

                getPlanInstallmentBrands() {
                    const result = [];
                    this.planItems.forEach(item => {
                        const itemBasePrice = Number(item.base_price) || 0;
                        const hasInstallmentBrands = (item.brands || []).some(b => b.is_installment);
                        if (hasInstallmentBrands && itemBasePrice > 0) {
                            result.push({
                                key: this.makeBrandKey(item.service.id, '', '', 'قیمت پایه'),
                                serviceId: item.service.id,
                                serviceName: item.service.name,
                                brandName: 'قیمت پایه',
                                price: itemBasePrice,
                                quantity: 1,
                            });
                        }

                        (item.brands || []).forEach(b => {
                            if (!b.is_installment) return;
                            const key = this.makeBrandKey(item.service.id, b.tabTitle || '', b.sectionTitle || '', b.name);
                            result.push({
                                key,
                                serviceId: item.service.id,
                                serviceName: item.service.name,
                                tabTitle: b.tabTitle || '',
                                sectionTitle: b.sectionTitle || '',
                                brandName: b.name,
                                price: Number(b.price) || 0,
                                quantity: item.quantity,
                            });
                        });
                    });
                    return result;
                },

                getInstallmentPlansForBrand(serviceId, tabTitle, sectionTitle, brandName) {
                    const key = this.makeBrandKey(serviceId, tabTitle, sectionTitle, brandName);
                    return this.installmentTypes.filter(opt => {
                        const cfg = opt.brand_configs?.[key];
                        return cfg && cfg.active;
                    });
                },

                getEffectiveDownPayment(plan, serviceId, tabTitle, sectionTitle, brandName) {
                    const key = this.makeBrandKey(serviceId, tabTitle, sectionTitle, brandName);
                    const tier = this.getEffectiveTierForBrand(plan, key, this.finalPayable);
                    if (!tier) return 0;

                    if (this.selectedInstallmentMonths && tier.down_payments_map) {
                        const specificDp = tier.down_payments_map[String(this.selectedInstallmentMonths)];
                        if (specificDp !== undefined && specificDp !== '') {
                            return Number(specificDp);
                        }
                    }
                    return Number(tier.down_payment) || 0;
                },

                getEffectiveMonths(plan, serviceId, tabTitle, sectionTitle, brandName) {
                    const key = this.makeBrandKey(serviceId, tabTitle, sectionTitle, brandName);
                    const tier = this.getEffectiveTierForBrand(plan, key, this.finalPayable);
                    return tier ? tier.max_months : 0;
                },

                getEffectiveInterval(plan, serviceId, tabTitle, sectionTitle, brandName) {
                    const key = this.makeBrandKey(serviceId, tabTitle, sectionTitle, brandName);
                    const tier = this.getEffectiveTierForBrand(plan, key, this.finalPayable);
                    return tier ? tier.payment_stages : 1;
                },

                getEffectivePrice(plan, serviceId, tabTitle, sectionTitle, brandName, basePrice) {
                    return Number(basePrice || 0);
                },

                itemHasInstallmentBrands(item) {
                    return (item.brands || []).some(b => b.is_installment);
                },

                getInstallmentPlansForItem(item) {
                    const plans = new Set();
                    (item.brands || []).forEach(b => {
                        if (!b.is_installment) return;
                        this.getInstallmentPlansForBrand(item.service.id, b.tabTitle || '', b.sectionTitle || '', b.name).forEach(p => plans.add(p));
                    });
                    return Array.from(plans);
                },

                get eligibleInstallmentOptions() {
                    if (!this.useInstallment || this.installmentTypes.length === 0) {
                        return [];
                    }
                    const planInstBrands = this.getPlanInstallmentBrands();
                    if (planInstBrands.length === 0) {
                        return [];
                    }

                    const eligible = this.installmentTypes.filter(opt => {
                        const eligibleKeys = this.getEligibleBrandKeysForOption(opt);
                        if (eligibleKeys.length === 0) return false;
                        return true;
                    });

                    return eligible;
                },

                get selectedInstallmentOption() {
                    if (!this.selectedInstallmentOptionId) return null;
                    return this.installmentTypes.find(o => o.id === this.selectedInstallmentOptionId) || null;
                },

                getEligibleBrandKeysForOption(option) {
                    const planBrands = this.getPlanInstallmentBrands();
                    if (planBrands.length === 0) return [];

                    let configs = option.brand_configs || {};
                    if (typeof configs === 'string') {
                        try { configs = JSON.parse(configs); } catch (e) { configs = {}; }
                    }

                    const eligibleKeys = [];
                    const eligibleServiceIds = new Set();

                    planBrands.forEach(b => {
                        if (b.brandName === 'قیمت پایه') return;

                        let cfg = configs[b.key];
                        if (!cfg) {
                            const cleanStr = s => String(s || '').replace(/[\s_]+/g, '');
                            const parts = b.key.split('__');
                            const matchKey = Object.keys(configs).find(k => {
                                const kParts = k.split('__');
                                return cleanStr(kParts[0]) === cleanStr(parts[0]) &&
                                    cleanStr(kParts[1]) === cleanStr(parts[1]) &&
                                    cleanStr(kParts[2]) === cleanStr(parts[2]) &&
                                    cleanStr(kParts[3]) === cleanStr(parts[3]);
                            });
                            if (matchKey) cfg = configs[matchKey];
                        }

                        const effectiveTier = this.getEffectiveTierForBrand(option, b.key, this.finalPayable);
                        const isActive = cfg !== undefined ? cfg.active : true;

                        if (isActive && effectiveTier !== null) {
                            eligibleKeys.push(b.key);
                            eligibleServiceIds.add(b.serviceId);
                        }
                    });

                    planBrands.forEach(b => {
                        if (b.brandName === 'قیمت پایه' && eligibleServiceIds.has(b.serviceId)) {
                            if (!eligibleKeys.includes(b.key)) {
                                eligibleKeys.push(b.key);
                            }
                        }
                    });

                    return eligibleKeys;
                },
                parseBrandKey(key) {
                    const parts = key.split('__');
                    return { serviceId: parts[0] || '', tabTitle: parts[1] || '', sectionTitle: parts[2] || '', brandName: parts[3] || '' };
                },

                getBrandNameFromKey(key) { return this.parseBrandKey(key).brandName; },

                getBrandServiceFromKey(key) {
                    const { serviceId, tabTitle, sectionTitle } = this.parseBrandKey(key);
                    const svc = this.services.find(s => String(s.id) === String(serviceId));
                    return [svc?.name, tabTitle, sectionTitle].filter(Boolean).join(' › ');
                },

                get installmentBrandBreakdown() {
                    if (!this.selectedInstallmentOption) return [];
                    const opt = this.selectedInstallmentOption;
                    const planBrands = this.getPlanInstallmentBrands();
                    if (planBrands.length === 0) return [];

                    const eligibleKeys = new Set(this.getEligibleBrandKeysForOption(opt));
                    const resultMap = {};
                    const selectedMonths = this.effectiveMonths;

                    if (eligibleKeys.size > 0) {
                        planBrands.filter(b => eligibleKeys.has(b.key)).forEach(b => {
                            const totalPrice = this.getEffectivePrice(opt, b.serviceId, b.tabTitle, b.sectionTitle, b.brandName, b.price) * b.quantity;
                            if (!resultMap[b.brandName]) {
                                const tier = this.getEffectiveTierForBrand(opt, b.key, this.finalPayable);
                                const downPct = tier && tier.down_payments_map && tier.down_payments_map[String(selectedMonths)] !== undefined
                                    ? Number(tier.down_payments_map[String(selectedMonths)]) : (tier ? Number(tier.down_payment) || 0 : 0);
                                const feePct = tier && tier.fees_map && tier.fees_map[String(selectedMonths)] !== undefined
                                    ? Number(tier.fees_map[String(selectedMonths)]) : 0;

                                const annualFeePct = tier ? Number(tier.annual_fee_percent) || 0 : 0;
                                const interval = tier ? tier.payment_stages : 1;
                                const installments = interval > 0 ? Math.ceil(selectedMonths / interval) : 0;

                                resultMap[b.brandName] = {
                                    brandName: b.brandName, price: 0, qty: 0,
                                    down_payment: downPct, fee: feePct, annual_fee: annualFeePct,
                                    interval: interval, installments: installments
                                };
                            }
                            resultMap[b.brandName].price += totalPrice;
                            resultMap[b.brandName].qty += b.quantity;
                        });
                        return Object.values(resultMap);
                    }
                    return [];
                },
                get installmentTotalAmount() {
                    if (!this.selectedInstallmentOption) return this.finalPayable;
                    const breakdown = this.installmentBrandBreakdown;
                    const raw = breakdown.length === 0 ? 0 : breakdown.reduce((s, r) => s + (r.price || 0), 0);
                    return this.roundAmount(raw);
                },

                get effectiveDownPaymentPct() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    if (bd.length === 0) return 0;
                    if (bd.length === 1) return bd[0].down_payment;
                    const totalPrice = bd.reduce((s, r) => s + r.price, 0);
                    return totalPrice === 0 ? 0 : Math.round(bd.reduce((s, r) => s + r.down_payment * r.price, 0) / totalPrice);
                },

                get effectiveFeePct() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    if (bd.length === 0) return 0;
                    const totalPrice = bd.reduce((s, r) => s + r.price, 0);
                    return totalPrice === 0 ? 0 : Math.round(bd.reduce((s, r) => s + r.fee * r.price, 0) / totalPrice);
                },
                get annualFeePct() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    if (bd.length === 0) return 0;
                    const totalPrice = bd.reduce((s, r) => s + r.price, 0);
                    return totalPrice === 0 ? 0 : Math.round(bd.reduce((s, r) => s + r.annual_fee * r.price, 0) / totalPrice);
                },

                get effectiveMonths() {
                    return this.selectedInstallmentMonths || (this.availableInstallmentMonths.length > 0 ? this.availableInstallmentMonths[this.availableInstallmentMonths.length - 1] : 0);
                },

                get effectiveInterval() {
                    if (!this.selectedInstallmentOption) return 1;
                    const bd = this.installmentBrandBreakdown;
                    return bd.length === 0 ? 1 : Math.max(...bd.map(r => r.interval || 1));
                },

                // فاصله واقعی بین چک‌ها (به ماه) که مستقیماً از روی تاریخ چک‌های واقعاً تولید/ثبت‌شده محاسبه می‌شود.
                // این مقدار را عمداً از این تاریخ‌ها می‌گیریم (نه از numberOfCheques/effectiveMonths) چون آن دو
                // متغیر reactive هستند و ممکن است بین لحظه تولید چک‌ها و لحظه ذخیره، توسط watcherها مقداردهی مجدد شوند؛
                // در حالی که آرایه generatedCheques خودش حقیقت واقعی چیزی است که نمایش داده و ذخیره می‌شود.
                get realIntervalMonths() {
                    const autoCheques = (this.generatedCheques || []).filter(c => !c.isManual && c.date);
                    if (autoCheques.length >= 2) {
                        const toAbsMonth = (dateStr) => {
                            const p = String(dateStr).split('/').map(Number);
                            if (p.length < 2 || p.some(isNaN)) return null;
                            return p[0] * 12 + p[1];
                        };
                        const first = toAbsMonth(autoCheques[0].date);
                        const last = toAbsMonth(autoCheques[autoCheques.length - 1].date);
                        if (first !== null && last !== null && autoCheques.length > 1) {
                            const span = last - first;
                            const computed = Math.round(span / (autoCheques.length - 1));
                            if (computed > 0) return computed;
                        }
                    }
                    // اگر هنوز چکی تولید نشده (مثلاً پیش از تولید، برای پیش‌نمایش فرمول)، از مدت کل ÷ تعداد چک استفاده می‌کنیم.
                    if (this.numberOfCheques > 0) {
                        const months = this.effectiveMonths || this.numberOfCheques;
                        return Math.max(1, Math.round(months / this.numberOfCheques));
                    }
                    return this.effectiveInterval || 1;
                },

                get installmentsCount() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    return bd.length === 0 ? 0 : Math.max(...bd.map(r => r.installments || 0));
                },

                get installmentFeeValue() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    if (bd.length === 0) return 0;

                    const months = this.effectiveMonths || 1;
                    const isAnnualActive = months >= 12;
                    const years = months / 12;

                    const rawFee = bd.reduce((s, r) => {
                        const remainingPrincipal = r.price - (r.price * (r.down_payment / 100));

                        if (isAnnualActive && r.annual_fee > 0) {
                            return s + (remainingPrincipal * (r.annual_fee / 100) * years);
                        }
                        else if (!isAnnualActive) {
                            return s + (remainingPrincipal * (r.fee / 100));
                        }
                        return s;
                    }, 0);

                    return this.roundAmount(rawFee);
                },
                get downPaymentAmount() {
                    if (!this.selectedInstallmentOption) return 0;
                    const bd = this.installmentBrandBreakdown;
                    if (bd.length === 0) return 0;
                    const raw = bd.reduce((s, r) => s + (r.price * r.down_payment / 100), 0);
                    return this.roundAmount(raw);
                },

                get monthlyPaymentAmount() {
                    if (!this.selectedInstallmentOption || this.numberOfCheques <= 0) return 0;
                    const remaining = Math.max(0, this.targetChequesTotal);
                    let rounded = this.roundAmount(remaining / this.numberOfCheques);

                    const chequeCountForBase = Math.max(0, this.numberOfCheques - 1);
                    const factor = Number(this.settings.rounding_factor) || 0;
                    if (chequeCountForBase > 0 && (rounded * chequeCountForBase) >= remaining) {
                        if (factor > 0) {
                            rounded = Math.max(factor, rounded - factor);
                            if ((rounded * chequeCountForBase) >= remaining) {
                                rounded = Math.max(0, Math.floor(remaining / this.numberOfCheques));
                            }
                        } else {
                            rounded = Math.max(0, Math.floor(remaining / this.numberOfCheques));
                        }
                    }

                    return Math.max(0, rounded);
                },

                get targetChequesTotal() {
                    if (!this.useInstallment || !this.selectedInstallmentOption) return 0;
                    const baseTotal = this.installmentTotalAmount - this.downPaymentAmount + this.installmentFeeValue;
                    return Math.max(0, this.roundAmount(baseTotal));
                },

                get totalChequesAmount() {
                    return this.generatedCheques.reduce((sum, c) => sum + Number(c.amount || 0), 0);
                },
                onManualChequeAmountInput(event) {
                    const raw = event.target.value.replace(/[^\d]/g, '');
                    this.manualChequeAmount = raw ? Number(raw) : 0;
                    this.manualChequeAmountDisplay = raw ? Number(raw).toLocaleString('en-US') : '';
                },
                onDiscountAmountInput(event) {
                    const raw = event.target.value.replace(/[^\d]/g, '');
                    this.discountAmount = raw ? Number(raw) : 0;
                    this.discountAmountDisplay = raw ? Number(raw).toLocaleString('en-US') : '';
                },
                addManualCheque() {
                    if (!this.manualChequeNumber || !this.manualChequeAmount || !this.manualChequeDate) {
                        showToast('لطفاً شماره، مبلغ و تاریخ چک دستی را وارد کنید', 'error');
                        return;
                    }

                    const newAmount = Number(this.manualChequeAmount);
                    const newTotal = this.totalChequesAmount + newAmount;

                    if (newTotal > this.targetChequesTotal) {
                        const remaining = this.targetChequesTotal - this.totalChequesAmount;
                        showToast(`مبلغ این چک از مانده باقی‌مانده بیشتر است! حداکثر مبلغ مجاز: ${this.formatPrice(remaining)} ${this.currencyLabel}`, 'error', 5000);
                        return;
                    }

                    this.generatedCheques.push({
                        id: Date.now() + Math.random(),
                        number: this.manualChequeNumber,
                        total: '-',
                        amount: newAmount,
                        date: this.manualChequeDate,
                        display_date: this.toFaDigits(this.manualChequeDate),
                        bankName: this.manualChequeBankName || '',
                        isManual: true
                    });

                    this.resetManualChequeForm();
                    this.showManualChequeForm = false;

                    const remainingAfter = this.targetChequesTotal - this.totalChequesAmount;
                    if (remainingAfter > 1) {
                        showToast(`چک ثبت شد. برای تسویه کامل، ${this.formatPrice(remainingAfter)} ${this.currencyLabel} دیگر نیاز است`, 'info', 4000);
                    } else {
                        showToast('چک دستی اضافه شد و مبلغ اقساط تسویه شد', 'success');
                    }
                },

                resetManualChequeForm() {
                    this.manualChequeNumber = '';
                    this.manualChequeAmount = 0;
                    this.manualChequeAmountDisplay = '';
                    this.manualChequeBankName = '';
                    this.manualChequeDate = '';
                    this.editingManualChequeIndex = null;
                },

                editManualCheque(index) {
                    const cheque = this.generatedCheques[index];
                    if (!cheque || !cheque.isManual) return;
                    this.editingManualChequeIndex = index;
                    this.manualChequeNumber = cheque.number || '';
                    this.manualChequeAmount = Number(cheque.amount) || 0;
                    this.manualChequeAmountDisplay = this.manualChequeAmount ? this.manualChequeAmount.toLocaleString('en-US') : '';
                    this.manualChequeBankName = cheque.bankName || '';
                    this.manualChequeDate = cheque.date || '';
                    this.showManualChequeForm = true;
                },

                saveManualChequeEdit() {
                    if (this.editingManualChequeIndex === null) return;
                    if (!this.manualChequeNumber || !this.manualChequeAmount || !this.manualChequeDate) {
                        showToast('لطفاً شماره، مبلغ و تاریخ چک دستی را وارد کنید', 'error');
                        return;
                    }

                    const oldAmount = Number(this.generatedCheques[this.editingManualChequeIndex].amount || 0);
                    const newAmount = Number(this.manualChequeAmount);
                    const currentTotalWithoutOld = this.totalChequesAmount - oldAmount;
                    const newTotal = currentTotalWithoutOld + newAmount;

                    if (newTotal > this.targetChequesTotal) {
                        const remaining = this.targetChequesTotal - currentTotalWithoutOld;
                        showToast(`مبلغ این چک از مانده باقی‌مانده بیشتر است! حداکثر مبلغ مجاز: ${this.formatPrice(remaining)} ${this.currencyLabel}`, 'error', 5000);
                        return;
                    }

                    const cheque = this.generatedCheques[this.editingManualChequeIndex];
                    cheque.number = this.manualChequeNumber;
                    cheque.amount = newAmount;
                    cheque.bankName = this.manualChequeBankName || '';
                    cheque.date = this.manualChequeDate;
                    cheque.display_date = this.toFaDigits(this.manualChequeDate);
                    this.generatedCheques = [...this.generatedCheques];

                    this.resetManualChequeForm();
                    this.showManualChequeForm = false;

                    const remainingAfter = this.targetChequesTotal - this.totalChequesAmount;
                    if (remainingAfter > 1) {
                        showToast(`چک ویرایش شد. برای تسویه کامل، ${this.formatPrice(remainingAfter)} ${this.currencyLabel} دیگر نیاز است`, 'info', 4000);
                    } else {
                        showToast('چک ویرایش شد و مبلغ اقساط تسویه شد', 'success');
                    }
                },

                removeCheque(index) {
                    this.generatedCheques.splice(index, 1);
                    if (this.editingManualChequeIndex === index) {
                        this.resetManualChequeForm();
                        this.showManualChequeForm = false;
                    }
                },

                clearAllCheques() {
                    if (this.generatedCheques.length === 0) return;
                    if (!confirm('آیا از حذف تمام چک‌ها اطمینان دارید؟')) return;
                    this.generatedCheques = [];
                    showToast('تمام چک‌ها حذف شدند', 'success');
                },

                get allCartBrands() {
                    const cart = [];
                    this.planItems.forEach(item => {
                        const itemBasePrice = Number(item.base_price) || 0;
                        if (item.brands && item.brands.length > 0) {
                            item.brands.forEach(b => {
                                cart.push({
                                    serviceId: item.service.id, serviceName: item.service.name,
                                    tabTitle: b.tabTitle || '', sectionTitle: b.sectionTitle || '', brandName: b.name,
                                    price: (Number(b.price) || 0) * item.quantity, qty: item.quantity, is_installment: !!b.is_installment
                                });
                            });
                            if (itemBasePrice > 0) {
                                cart.push({
                                    serviceId: item.service.id, serviceName: item.service.name,
                                    tabTitle: '', sectionTitle: '', brandName: 'قیمت پایه',
                                    price: itemBasePrice, qty: 1, is_installment: false
                                });
                            }
                        } else {
                            cart.push({
                                serviceId: item.service.id, serviceName: item.service.name,
                                tabTitle: '', sectionTitle: '', brandName: 'سرویس پایه',
                                price: (Number(item.price) || 0) * item.quantity, qty: item.quantity, is_installment: false
                            });
                        }
                    });
                    return cart;
                },

                get selectedPlanCoveredKeys() {
                    if (!this.selectedInstallmentOption) return new Set();
                    return new Set(this.getEligibleBrandKeysForOption(this.selectedInstallmentOption));
                },

                get mixedPaymentBreakdown() {
                    if (!this.useInstallment || !this.selectedInstallmentOption) return { covered: [], uncovered: [] };
                    const coveredMap = {}, uncoveredMap = {};

                    // Quick lookup: brand name -> fee/down-payment info from installmentBrandBreakdown
                    const feeInfoByBrand = {};
                    this.installmentBrandBreakdown.forEach(r => {
                        feeInfoByBrand[r.brandName] = {
                            downPayment: r.down_payment || 0,
                            feePercent: r.fee || 0,
                            interval: r.interval || 1,
                            installments: r.installments || 0,
                        };
                    });

                    this.planItems.forEach(item => {
                        let itemHasCoveredBrand = false;
                        const itemCoveredBrands = [], itemUncoveredBrands = [];

                        (item.brands || []).forEach(b => {
                            const key = this.makeBrandKey(item.service.id, b.tabTitle || '', b.sectionTitle || '', b.name);
                            const isCovered = this.selectedPlanCoveredKeys.has(key);
                            const totalPrice = (Number(b.price) || 0) * item.quantity;
                            if (isCovered) {
                                itemHasCoveredBrand = true;
                                itemCoveredBrands.push({ brandName: b.name, price: totalPrice });
                            } else {
                                itemUncoveredBrands.push({ brandName: b.name, price: totalPrice });
                            }
                        });

                        const itemBasePrice = (Number(item.base_price) || 0);

                        if (itemBasePrice > 0) {
                            if (itemHasCoveredBrand) itemCoveredBrands.push({ brandName: 'قیمت پایه', price: itemBasePrice });
                            else itemUncoveredBrands.push({ brandName: 'قیمت پایه', price: itemBasePrice });
                        } else if (!item.brands || item.brands.length === 0) {
                            const totalPrice = (Number(item.price) || 0) * item.quantity;
                            if (totalPrice > 0) itemUncoveredBrands.push({ brandName: item.service.name, price: totalPrice });
                        }

                        itemCoveredBrands.forEach(b => {
                            if (!coveredMap[b.brandName]) {
                                const feeInfo = feeInfoByBrand[b.brandName] || { downPayment: 0, feePercent: 0, interval: 1, installments: 0 };
                                coveredMap[b.brandName] = {
                                    brandName: b.brandName,
                                    price: 0,
                                    downPaymentPercent: feeInfo.downPayment,
                                    feePercent: feeInfo.feePercent,
                                    feeValue: 0,
                                    intervalMonths: feeInfo.interval,
                                    installmentsCount: feeInfo.installments,
                                };
                            }
                            coveredMap[b.brandName].price += b.price;
                            const remPrincipal = coveredMap[b.brandName].price - (coveredMap[b.brandName].price * (coveredMap[b.brandName].downPaymentPercent / 100));
                            coveredMap[b.brandName].feeValue = Math.round(remPrincipal * (coveredMap[b.brandName].feePercent / 100));                        });
                        itemUncoveredBrands.forEach(b => {
                            if (!uncoveredMap[b.brandName]) uncoveredMap[b.brandName] = { brandName: b.brandName, price: 0 };
                            uncoveredMap[b.brandName].price += b.price;
                        });
                    });

                    return { covered: Object.values(coveredMap), uncovered: Object.values(uncoveredMap) };
                },
                get uncoveredTotal() {
                    return this.mixedPaymentBreakdown.uncovered.reduce((s, b) => s + b.price, 0);
                },
                get totalCashToPayNow() {
                    if (!this.useInstallment || !this.selectedInstallmentOption) return this.finalPayable;
                    return this.downPaymentAmount + this.uncoveredTotal;
                },

                get currencyLabel() { return this.settings.currency === 'IRR' ? 'ریال' : 'تومان'; },

                get subtotal() {
                    return this.planItems.reduce((s, i) => {
                        return s + (i.price * i.quantity) + (i.base_price || 0);
                    }, 0);
                },
                get discountValue() {
                    const d = Number(this.discountAmount) || 0;
                    if (this.discountType === 'percent') return Math.min(this.subtotal, this.subtotal * d / 100);
                    return Math.min(this.subtotal, d);
                },

                get total() { return Math.max(0, this.subtotal - this.discountValue); },

                get taxValue() {
                    if (!this.settings.cure.auto_tax) return 0;
                    if (this.settings.tax_type === 'PERCENT') return this.total * (this.settings.tax_amount / 100);
                    return this.settings.tax_amount;
                },

                get totalWithTax() { return this.total + this.taxValue; },
                get totalPrice() { return this.subtotal; },
                get finalPayable() { return this.totalWithTax; },

                // ── توابع کمکی تقویم جلالی ──
                toFa(n) {
                    return new Intl.NumberFormat('fa-IR').format(n);
                },

                toFaDigits(n) {
                    return String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                },

                toEnglishDigits(str) {
                    if (str === null || str === undefined) return '';
                    return String(str)
                        .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
                },

                gregorianToJalali(gy, gm, gd) {
                    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
                    const gy2 = (gm > 2) ? (gy + 1) : gy;
                    let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
                    let jy = -1595 + (33 * Math.floor(days / 12053));
                    days %= 12053;
                    jy += 4 * Math.floor(days / 1461);
                    days %= 1461;
                    if (days > 365) {
                        jy += Math.floor((days - 1) / 365);
                        days = (days - 1) % 365;
                    }
                    let jm, jd;
                    if (days < 186) {
                        jm = 1 + Math.floor(days / 31);
                        jd = 1 + (days % 31);
                    } else {
                        jm = 7 + Math.floor((days - 186) / 30);
                        jd = 1 + ((days - 186) % 30);
                    }
                    return { jy, jm, jd };
                },

                jalaliToGregorian(jy, jm, jd) {
                    jy = Number(jy); jm = Number(jm); jd = Number(jd);
                    let gy = (jy <= 979) ? 621 : 1600;
                    jy -= (jy <= 979) ? 0 : 979;
                    let days = (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + 78 + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
                    gy += 400 * Math.floor(days / 146097);
                    days %= 146097;
                    if (days > 36524) {
                        gy += 100 * Math.floor(--days / 36524);
                        days %= 36524;
                        if (days >= 365) days++;
                    }
                    gy += 4 * Math.floor(days / 1461);
                    days %= 1461;
                    if (days > 365) {
                        gy += Math.floor((days - 1) / 365);
                        days = (days - 1) % 365;
                    }
                    let gd = days + 1;
                    let sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                    let gm;
                    for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
                    return new Date(gy, gm, gd);
                },

                getJalaliWeekday(jy, jm, jd) {
                    const date = this.jalaliToGregorian(jy, jm, jd);
                    let day = date.getDay(); // 0=Sunday, 6=Saturday
                    return (day + 1) % 7; // تبدیل به شنبه=0
                },

                getJalaliDaysInMonth(year, month) {
                    if (month <= 6) return 31;
                    if (month <= 11) return 30;
                    const breaks = [1, 5, 9, 13, 17, 22, 26, 30];
                    const y = year % 33;
                    return breaks.includes(y) ? 30 : 29;
                },

                buildInstCalendar() {
                    if (!this.instCalYear || !this.instCalMonth) return;
                    const maxDays = this.getJalaliDaysInMonth(this.instCalYear, this.instCalMonth);
                    const firstWeekday = this.getJalaliWeekday(this.instCalYear, this.instCalMonth, 1);

                    this.instCalDays = [];
                    for (let i = 0; i < firstWeekday; i++) {
                        this.instCalDays.push({ empty: true });
                    }

                    const allowedDays = Array.isArray(this.dueDays) ? this.dueDays.map(Number) : [];

                    const today = new Date();
                    const todayJalali = this.gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());

                    for (let d = 1; d <= maxDays; d++) {
                        let isPast = false;
                        if (this.instCalYear < todayJalali.jy) {
                            isPast = true;
                        } else if (this.instCalYear === todayJalali.jy) {
                            if (this.instCalMonth < todayJalali.jm) {
                                isPast = true;
                            } else if (this.instCalMonth === todayJalali.jm && d < todayJalali.jd) {
                                isPast = true;
                            }
                        }

                        const isDueDayValid = allowedDays.length > 0 ? allowedDays.includes(Number(d)) : true;
                        const valid = !isPast && isDueDayValid;
                        const dateStr = `${this.instCalYear}/${String(this.instCalMonth).padStart(2, '0')}/${String(d).padStart(2, '0')}`;
                        this.instCalDays.push({ day: d, valid: valid, date: dateStr, selected: this.installmentStartDate === dateStr });
                    }
                },

                initInstCalendar() {
                    if (this.installmentStartDate) {
                        const parts = this.installmentStartDate.split('/').map(Number);
                        if (parts.length === 3 && !parts.some(isNaN)) {
                            this.instCalYear = parts[0];
                            this.instCalMonth = parts[1];
                            this.buildInstCalendar();
                            return;
                        }
                    }

                    const today = new Date();
                    const currentJalali = this.gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
                    this.instCalYear = currentJalali.jy;
                    this.instCalMonth = currentJalali.jm;
                    this.buildInstCalendar();
                },
                prevInstMonth() {
                    this.instCalMonth--;
                    if (this.instCalMonth < 1) {
                        this.instCalMonth = 12;
                        this.instCalYear--;
                    }
                    this.buildInstCalendar();
                },

                nextInstMonth() {
                    this.instCalMonth++;
                    if (this.instCalMonth > 12) {
                        this.instCalMonth = 1;
                        this.instCalYear++;
                    }
                    this.buildInstCalendar();
                },

                selectInstDate(dayObj) {
                    if (!dayObj.valid) return;
                    this.installmentStartDate = dayObj.date;
                    this.selectedDueDay = dayObj.day;
                    this.buildInstCalendar();
                },
                updateManualCal() {
                    if (!this.manualChequeCalYear) return;
                    const maxDays = this.getJalaliDaysInMonth(this.manualChequeCalYear, this.manualChequeCalMonth);
                    const firstWeekday = this.getJalaliWeekday(this.manualChequeCalYear, this.manualChequeCalMonth, 1);
                    this.manualChequeCalDays = [];
                    for (let i = 0; i < firstWeekday; i++) this.manualChequeCalDays.push({ empty: true });

                    const today = new Date();
                    const todayJ = this.gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
                    const allowedDays = Array.isArray(this.dueDays) ? this.dueDays.map(Number) : [];

                    for (let d = 1; d <= maxDays; d++) {
                        let isPast = this.manualChequeCalYear < todayJ.jy ||
                            (this.manualChequeCalYear === todayJ.jy && this.manualChequeCalMonth < todayJ.jm) ||
                            (this.manualChequeCalYear === todayJ.jy && this.manualChequeCalMonth === todayJ.jm && d < todayJ.jd);

                        const dateStr = `${this.manualChequeCalYear}/${String(this.manualChequeCalMonth).padStart(2, '0')}/${String(d).padStart(2, '0')}`;
                        this.manualChequeCalDays.push({
                            day: d,
                            valid: !isPast && (allowedDays.length === 0 || allowedDays.includes(d)),
                            date: dateStr,
                            selected: this.manualChequeDate === dateStr
                        });
                    }
                },
                openManualCal() {
                    this.showManualChequeCalendar = true;
                    if (!this.manualChequeCalYear) {
                        if (this.manualChequeDate) {
                            const p = this.manualChequeDate.split('/').map(Number);
                            this.manualChequeCalYear = p[0];
                            this.manualChequeCalMonth = p[1];
                        } else {
                            const t = new Date();
                            const j = this.gregorianToJalali(t.getFullYear(), t.getMonth()+1, t.getDate());
                            this.manualChequeCalYear = j.jy;
                            this.manualChequeCalMonth = j.jm;
                        }
                    }
                    this.updateManualCal();
                },
                changeManualMonth(dir) {
                    this.manualChequeCalMonth += dir;
                    if (this.manualChequeCalMonth > 12) { this.manualChequeCalMonth = 1; this.manualChequeCalYear++; }
                    if (this.manualChequeCalMonth < 1) { this.manualChequeCalMonth = 12; this.manualChequeCalYear--; }
                    this.updateManualCal();
                },
                pickManualDate(day) {
                    if (!day.valid) return;
                    this.manualChequeDate = day.date;
                    this.showManualChequeCalendar = false;
                },

                generateCheques() {
                    this.generatedCheques = [];
                    if (!this.useInstallment || !this.selectedInstallmentOption) {
                        showToast('ابتدا طرح اقساطی را انتخاب کنید', 'error'); return;
                    }
                    if (this.numberOfCheques <= 0) {
                        showToast('تعداد چک‌ها را مشخص کنید', 'error'); return;
                    }
                    if (!this.installmentStartDate) {
                        showToast('لطفاً تاریخ شروع اقساط را از تقویم انتخاب کنید', 'error'); return;
                    }

                    const parts = this.installmentStartDate.split('/').map(p => parseInt(p, 10));
                    if (parts.length !== 3 || parts.some(isNaN)) {
                        showToast('فرمت تاریخ شروع نامعتبر است', 'error'); return;
                    }

                    let currentYear = parts[0];
                    let currentMonth = parts[1];
                    const startDay = parts[2];
                    const bankName = this.chequeBankName || '';

                    const targetTotal = Math.max(0, this.targetChequesTotal);
                    let useBaseAmount = this.monthlyPaymentAmount;
                    const intervalMonths = this.realIntervalMonths;

                    const chequeCountForBase = Math.max(0, this.numberOfCheques - 1);
                    let sumBaseAmounts = useBaseAmount * chequeCountForBase;
                    let lastChequeAmount;

                    if (chequeCountForBase > 0 && sumBaseAmounts >= targetTotal) {
                        useBaseAmount = Math.floor(targetTotal / this.numberOfCheques);
                        sumBaseAmounts = useBaseAmount * chequeCountForBase;
                        lastChequeAmount = Math.max(0, targetTotal - sumBaseAmounts);
                    } else {
                        lastChequeAmount = Math.max(0, targetTotal - sumBaseAmounts);
                    }

                    for (let i = 1; i <= this.numberOfCheques; i++) {
                        if (i > 1) {
                            currentMonth += intervalMonths;
                            while (currentMonth > 12) {
                                currentMonth -= 12;
                                currentYear++;
                            }
                        }

                        let newDay = startDay;
                        const maxDaysInMonth = this.getJalaliDaysInMonth(currentYear, currentMonth);
                        if (newDay > maxDaysInMonth) newDay = maxDaysInMonth;

                        const formattedMonth = String(currentMonth).padStart(2, '0');
                        const formattedDay = String(newDay).padStart(2, '0');
                        const formattedDate = `${currentYear}/${formattedMonth}/${formattedDay}`;
                        const displayDate = `${this.toFaDigits(newDay)} ${this.jalaliMonthNames[currentMonth - 1]} ${this.toFaDigits(currentYear)}`;

                        this.generatedCheques.push({
                            id: Date.now() + i,
                            number: i,
                            total: this.numberOfCheques,
                            amount: i === this.numberOfCheques ? lastChequeAmount : useBaseAmount,
                            date: formattedDate,
                            display_date: displayDate,
                            bankName: bankName,
                            chequeNumber: ''
                        });
                    }
                    showToast(this.numberOfCheques + ' چک با موفقیت تولید شد', 'success');
                },

                async init() {
                    this.warrantyMonths = this.settings.cure.default_warranty_months || 0;
                    this.warrantyText = this.settings.cure.default_warranty_text || '';
                    if (this.dueDays.length > 0) this.selectedDueDay = this.dueDays[0];

                    if (!existingPlan) {
                        this.notes = this.settings.cure.default_notes || '';
                    } else if (existingPlan.items && existingPlan.items.length > 0) {
                        this.clientId = existingPlan.client?.id || null;
                        this.patientName = existingPlan.patient_name || '';
                        this.notes = existingPlan.notes || this.settings.cure.default_notes || '';
                        this.discountAmount = Number(existingPlan.discount_amount) || 0;
                        this.discountType = existingPlan.discount_type || 'amount';

                        this.planItems = (existingPlan.items || []).map((item, index) => {
                            const svc = this.services.find(s => String(s.id) === String(item.service_id));
                            return {
                                id: Date.now() + index + Math.random(),
                                teeth: Array.isArray(item.teeth) ? item.teeth.map(Number) : [],
                                service: { id: item.service_id, name: item.service_name },
                                brands: Array.isArray(item.brands) ? item.brands : [],
                                brandSelections: item.brand_selections || item.brandSelections || {},
                                price: Number(item.price) || 0,
                                base_price: svc ? (Number(svc.base_price) || 0) : (Number(item.base_price) || 0),
                                quantity: Number(item.quantity) || (Array.isArray(item.teeth) ? item.teeth.length : 1),
                                warranty: item.warranty || null,
                                item_uuid: item.item_uuid || null,
                            };
                        });
                        this.draftSaved = !!(existingPlan && existingPlan.id);
                        if (existingPlan.installment_option_id) {
                            this.useInstallment = true;
                            this.selectedInstallmentOptionId = existingPlan.installment_option_id;
                            this.selectedInstallmentMonths = existingPlan.installment_months || null;
                            this.numberOfCheques = existingPlan.installment_count || existingPlan.installment_months || 0;
                            this.installmentStartDate = existingPlan.installment_start_date || '';
                            this.generatedCheques = existingPlan.generated_cheques || [];
                            if (this.isReadOnly) {
                                this.showChequeSection = true;
                            }
                        }
                    }
                    if (!this.settings.cure.allow_discount) this.discountAmount = 0;
                    else if (this.settings.cure.discount_type !== 'both') this.discountType = this.settings.cure.discount_type;

                    this.$watch('clientId', () => { this.draftSaved = false; });
                    this.$watch('discountAmount', () => {
                        this.draftSaved = false;
                        this.discountAmountDisplay = this.discountAmount ? Number(this.discountAmount).toLocaleString('en-US') : '';
                    });
                    this.$watch('discountType', () => { this.draftSaved = false; });
                    this.$watch('selectedService', val => { if (val) this.buildPerToothAssignments(); });
                    this.$watch('selectedInstallmentOptionId', (val) => {
                        if (this.isInitializing) return;
                        if (val) {
                            const months = this.availableInstallmentMonths;
                            this.selectedInstallmentMonths = months.length ? months[months.length - 1] : null;
                            this.numberOfCheques = this.selectedInstallmentMonths || 0;
                        } else {
                            this.selectedInstallmentMonths = null;
                            this.numberOfCheques = 0;
                        }
                        this.generatedCheques = [];
                    });
                    this.$watch('selectedInstallmentMonths', (val) => {
                        if (this.isInitializing) return;
                        this.numberOfCheques = val || 0;
                        this.generatedCheques = [];
                    });
                    this.$watch('numberOfCheques', () => {
                        if (this.isInitializing) return;
                        this.generatedCheques = [];
                    });
                    this.initInstCalendar();
                    this.initWorkflows();
                    this.$nextTick(() => { this.isInitializing = false; });
                },

                initWorkflows() {
                    if (this.existingPlan?.id) {
                        this.fetchWorkflowBindings();
                    }
                },

                fetchWorkflowBindings() {
                    fetch(`/user/booking/cure/${this.existingPlan.id}/workflow-bindings`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.workflowBindings = data.bindings || [];
                            }
                        })
                        .catch(err => console.error('Error fetching bindings:', err));
                },

                fetchWorkflows() {
                    fetch(`/user/booking/cure/${this.existingPlan.id}/workflows`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.workflows) {
                            this.existingPlan.workflows = data.workflows;
                        }
                    })
                    .catch(err => console.error('Error fetching workflows:', err));
                },

                getWorkflowOptionsHtml() {
                    let html = '<option value="">-- انتخاب کنید --</option>';
                    (this.allWorkflows || []).forEach(wf => {
                        html += `<option value="${wf.id}">${wf.name}</option>`;
                    });
                    return html;
                },

                getItemOptionsHtml(allowEmpty = false) {
                    let html = allowEmpty ? '<option value="">-- کل طرح / انتخاب نشده --</option>' : '<option value="">-- انتخاب کنید --</option>';
                    (this.planItems || []).forEach(item => {
                        const teethStr = item.teeth && item.teeth.length ? ' (دندان ' + item.teeth.join('، ') + ')' : '';
                        html += `<option value="${item.item_uuid}">${item.service.name}${teethStr}</option>`;
                    });
                    return html;
                },

                openAddBindingModal(scope = 'plan', toothNum = '', itemKey = '') {
                    this.editingBinding = null;
                    this.bindingForm = {
                        workflow_id: '',
                        scope: scope,
                        item_key: itemKey,
                        tooth: toothNum,
                        trigger_statuses: [],
                        previous_status: '',
                        min_amount: '',
                        auto_trigger: true,
                        is_active: true
                    };
                    if (toothNum) {
                        this.selectedTeethForBinding = [String(toothNum)];
                    } else {
                        this.selectedTeethForBinding = [];
                    }
                    this.bindingModalOpen = true;
                },

                openEditBindingModal(binding) {
                    this.editingBinding = binding;
                    this.bindingForm = {
                        workflow_id: binding.workflow_id,
                        scope: binding.scope,
                        item_key: binding.item_key || '',
                        tooth: binding.tooth || '',
                        trigger_statuses: binding.trigger_statuses || [],
                        previous_status: binding.previous_status || '',
                        min_amount: binding.min_amount || '',
                        auto_trigger: binding.auto_trigger,
                        is_active: binding.is_active
                    };
                    if (binding.tooth) {
                        this.selectedTeethForBinding = String(binding.tooth).split(',');
                    } else {
                        this.selectedTeethForBinding = [];
                    }
                    this.bindingModalOpen = true;
                },

                saveWorkflowBinding() {
                    if (!this.bindingForm.workflow_id) {
                        showToast('لطفاً یک گردش‌کار انتخاب کنید.', 'error');
                        return;
                    }

                    if (this.bindingForm.scope === 'tooth') {
                        if (this.selectedTeethForBinding.length === 0) {
                            showToast('لطفاً حداقل یک دندان یا گزینه "همه دندان‌ها" را انتخاب کنید.', 'error');
                            return;
                        }
                        this.bindingForm.tooth = this.selectedTeethForBinding.join(',');
                    } else if (this.bindingForm.scope === 'plan') {
                        this.bindingForm.item_key = '';
                        this.bindingForm.tooth = '';
                    } else if (this.bindingForm.scope === 'item') {
                        this.bindingForm.tooth = '';
                    }

                    const selectedWorkflow = this.allWorkflows.find(w => String(w.id) === String(this.bindingForm.workflow_id));
                    const bindingData = {
                        ...this.bindingForm,
                        workflow: selectedWorkflow
                    };

                    if (!this.existingPlan) {
                        // Local storage for new plans
                        if (this.editingBinding) {
                            const idx = this.workflowBindings.findIndex(b => b.id === this.editingBinding.id);
                            if (idx !== -1) {
                                this.workflowBindings[idx] = { ...this.editingBinding, ...bindingData };
                            }
                        } else {
                            bindingData.id = 'local_' + Date.now() + Math.random();
                            this.workflowBindings.push(bindingData);
                        }
                        this.bindingModalOpen = false;
                        this.editingBinding = null;
                        showToast('اتصال با موفقیت ثبت شد (پس از ذخیره طرح درمان نهایی می‌شود)', 'success');
                        return;
                    }

                    this.isSubmittingBinding = true;
                    const url = this.editingBinding
                        ? `/user/booking/cure/${this.existingPlan.id}/workflow-bindings/${this.editingBinding.id}`
                        : `/user/booking/cure/${this.existingPlan.id}/workflow-bindings`;
                    const method = this.editingBinding ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(this.bindingForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSubmittingBinding = false;
                        if (data.success) {
                            showToast(data.message, 'success');
                            this.bindingModalOpen = false;
                            this.fetchWorkflowBindings();
                            this.fetchWorkflows();
                        } else {
                            showToast(data.message || 'خطایی رخ داد.', 'error');
                        }
                    })
                    .catch(err => {
                        this.isSubmittingBinding = false;
                        console.error('Error saving binding:', err);
                        showToast('خطای شبکه رخ داد.', 'error');
                    });
                },

                deleteWorkflowBinding(bindingId) {
                    if (!confirm('آیا از حذف این اتصال گردش‌کار مطمئن هستید؟')) return;

                    if (!this.existingPlan) {
                        this.workflowBindings = this.workflowBindings.filter(b => b.id !== bindingId);
                        showToast('اتصال با موفقیت حذف شد', 'success');
                        return;
                    }

                    fetch(`/user/booking/cure/${this.existingPlan.id}/workflow-bindings/${bindingId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            this.fetchWorkflowBindings();
                            this.fetchWorkflows();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error deleting binding:', err);
                        showToast('خطای شبکه رخ داد.', 'error');
                    });
                },

                triggerWorkflowBinding(bindingId) {
                    fetch(`/user/booking/cure/${this.existingPlan.id}/workflow-bindings/${bindingId}/trigger`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            this.fetchWorkflowBindings();
                            this.fetchWorkflows();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error triggering binding:', err);
                        showToast('خطای شبکه رخ داد.', 'error');
                    });
                },

                workflowsCountForTooth(tooth, status = null) {
                    if (!this.existingPlan || !this.existingPlan.workflows) return 0;
                    return this.existingPlan.workflows.filter(w => {
                        const toothMatch = w.tooth_context == tooth;
                        const statusMatch = !status || w.status === status;
                        return toothMatch && statusMatch;
                    }).length;
                },

                bindingsCountForTooth(tooth) {
                    return this.workflowBindings.filter(b => b.scope === 'tooth' && b.tooth == tooth).length;
                },

                toggle(id) {
                    if (this.isReadOnly) {
                        if (this.selectedToothForWorkflow === id) {
                            this.selectedToothForWorkflow = null;
                        } else {
                            this.selectedToothForWorkflow = id;
                        }
                        return;
                    }
                    if (this.selectedTeeth.includes(id)) {
                        this.selectedTeeth = this.selectedTeeth.filter(t => t !== id);
                    } else {
                        this.selectedTeeth.push(id);
                        this.selectedTeeth.sort((a, b) => a - b);
                    }
                    this.preset = 'none';
                    if (this.selectedService !== null) this.buildPerToothAssignments();
                },

                is(id) {
                    if (this.isReadOnly) {
                        if (this.selectedToothForWorkflow === id) return 'tooth-path tooth-selected-wf';

                        const hasActive = this.workflowsCountForTooth(id, 'ACTIVE') > 0;
                        const hasBinding = this.bindingsCountForTooth(id) > 0;

                        if (hasActive) return 'tooth-path tooth-active-wf';
                        if (hasBinding) return 'tooth-path tooth-bound-wf';
                        if (this.settings.cure.auto_highlight_teeth && this.planItems.some(i => i.teeth.includes(id))) return 'tooth-path tooth-in-plan';
                        return 'tooth-path tooth-unselected';
                    }
                    if (this.highlightedItemId !== null) {
                        const highlightedItem = this.planItems.find(i => i.id === this.highlightedItemId);
                        return highlightedItem?.teeth?.includes(id) ? 'tooth-path tooth-highlighted' : 'tooth-path tooth-unselected';
                    }
                    if (this.selectedTeeth.includes(id)) return 'tooth-path tooth-selected';
                    if (this.settings.cure.auto_highlight_teeth && this.planItems.some(i => i.id !== this.editingItemId && i.teeth.includes(id))) return 'tooth-path tooth-in-plan';
                    return 'tooth-path tooth-unselected';
                },

                selectJaw(type) {
                    if (this.isReadOnly) return;
                    if (this.preset === type) { this.resetTeeth(); return; }
                    this.preset = type;
                    this.selectedTeeth = type === 'upper' ? [...this.upperJawIds] : [...this.lowerJawIds];
                    if (this.selectedService !== null) this.buildPerToothAssignments();
                },

                selectAllTeeth() {
                    if (this.isReadOnly) return;
                    if (this.preset === 'all') { this.resetTeeth(); return; }
                    this.preset = 'all';
                    this.selectedTeeth = [...this.upperJawIds, ...this.lowerJawIds];
                    if (this.selectedService !== null) this.buildPerToothAssignments();
                },

                resetTeeth() {
                    if (this.isReadOnly) return;
                    this.selectedTeeth = [];
                    this.preset = 'none';
                    if (this.selectedService !== null) this.buildPerToothAssignments();
                },

                toggleHighlightItem(itemId) {
                    if (this.highlightedItemId === itemId) {
                        this.highlightedItemId = null;
                        this.selectedTeeth = [];
                    } else {
                        const item = this.planItems.find(i => i.id === itemId);
                        if (item && item.teeth?.length) {
                            this.highlightedItemId = itemId;
                            this.selectedTeeth = [...item.teeth];
                            showToast('دندان‌های این آیتم برجسته شدند', 'info');
                        }
                    }
                },

                getToothLabel(id) {
                    const system = this.settings.cure.tooth_numbering_system;
                    const palmerMap = {1:{num:7,pos:'UR'},2:{num:6,pos:'UR'},3:{num:5,pos:'UR'},4:{num:4,pos:'UR'},5:{num:3,pos:'UR'},6:{num:2,pos:'UR'},7:{num:1,pos:'UR'},8:{num:1,pos:'UL'},9:{num:2,pos:'UL'},10:{num:3,pos:'UL'},11:{num:4,pos:'UL'},12:{num:5,pos:'UL'},13:{num:6,pos:'UL'},14:{num:7,pos:'UL'},15:{num:7,pos:'LR'},16:{num:6,pos:'LR'},17:{num:5,pos:'LR'},18:{num:4,pos:'LR'},19:{num:3,pos:'LR'},20:{num:2,pos:'LR'},21:{num:1,pos:'LR'},22:{num:1,pos:'LL'},23:{num:2,pos:'LL'},24:{num:3,pos:'LL'},25:{num:4,pos:'LL'},26:{num:5,pos:'LL'},27:{num:6,pos:'LL'},28:{num:7,pos:'LL'}};
                    const fdiMap = {1:{num:17,pos:'UR'},2:{num:16,pos:'UR'},3:{num:15,pos:'UR'},4:{num:14,pos:'UR'},5:{num:13,pos:'UR'},6:{num:12,pos:'UR'},7:{num:11,pos:'UR'},8:{num:21,pos:'UL'},9:{num:22,pos:'UL'},10:{num:23,pos:'UL'},11:{num:24,pos:'UL'},12:{num:25,pos:'UL'},13:{num:26,pos:'UL'},14:{num:27,pos:'UL'},15:{num:47,pos:'LR'},16:{num:46,pos:'LR'},17:{num:45,pos:'LR'},18:{num:44,pos:'LR'},19:{num:43,pos:'LR'},20:{num:42,pos:'LR'},21:{num:41,pos:'LR'},22:{num:31,pos:'LL'},23:{num:32,pos:'LL'},24:{num:33,pos:'LL'},25:{num:34,pos:'LL'},26:{num:35,pos:'LL'},27:{num:36,pos:'LL'},28:{num:37,pos:'LL'}};
                    return (system === 'fdi' ? fdiMap : palmerMap)[id] ?? { num: id, pos: 'UR' };
                },

                getQuadrantClasses(id) {
                    switch(this.getToothLabel(id).pos) {
                        case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        default: return '';
                    }
                },

                get groupedTeeth() {
                    const posOrder = { 'UR':1, 'UL':2, 'LR':3, 'LL':4 };
                    const sorted = [...this.selectedTeeth].sort((a,b) => posOrder[this.getToothLabel(a).pos] - posOrder[this.getToothLabel(b).pos]);
                    const groups = { 'UR':[], 'UL':[], 'LR':[], 'LL':[] };
                    sorted.forEach(t => groups[this.getToothLabel(t).pos].push(t));
                    return Object.entries(groups).filter(([,v]) => v.length > 0);
                },

                groupTeethList(teethArray) {
                    const posOrder = { 'UR':1, 'UL':2, 'LR':3, 'LL':4 };
                    const sorted = [...(teethArray || [])].map(Number).sort((a,b) => posOrder[this.getToothLabel(a).pos] - posOrder[this.getToothLabel(b).pos]);
                    const groups = { 'UR':[], 'UL':[], 'LR':[], 'LL':[] };
                    sorted.forEach(t => groups[this.getToothLabel(t).pos].push(t));
                    return Object.entries(groups).filter(([,v]) => v.length > 0);
                },

                getQuadrantTeeth(teethArray, pos) {
                    return (teethArray || [])
                        .map(Number)
                        .filter(t => this.getToothLabel(t).pos === pos)
                        .sort((a,b) => a - b);
                },

                get filteredServices() {
                    const q = this.serviceSearch.toLowerCase();
                    return this.services.filter(s => (!q || s.name.toLowerCase().includes(q)) && (this.filterCategory === null || (s.category_ids && s.category_ids.includes(this.filterCategory)) || s.category_id === this.filterCategory));
                },

                filteredClients(search = '') {
                    const q = search.toLowerCase().trim();
                    if (!q) return this.clients;
                    return this.clients.filter(c => 
                        (c.full_name && c.full_name.toLowerCase().includes(q)) || 
                        (c.phone && c.phone.includes(q)) || 
                        (c.email && c.email.toLowerCase().includes(q)) ||
                        (c.national_code && String(c.national_code).includes(q)) ||
                        (c.case_number && String(c.case_number).includes(q))
                    );
                },

                searchClientsBackend(query) {
                    const q = query.trim();
                    if (q.length < 2) {
                        return;
                    }
                    this.clientSearchLoading = true;
                    fetch(`/user/clients/search?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.results) {
                                const current = this.clients.find(c => c.id === this.clientId);
                                const newClients = data.results.map(c => ({
                                    id: c.id,
                                    full_name: c.full_name,
                                    phone: c.phone,
                                    email: c.email,
                                    national_code: c.national_code,
                                    case_number: c.case_number
                                }));
                                if (current && !newClients.some(c => c.id === current.id)) {
                                    newClients.push(current);
                                }
                                this.clients = newClients;
                            }
                        })
                        .catch(err => console.error(err))
                        .finally(() => {
                            this.clientSearchLoading = false;
                        });
                },

                selectService(service) {
                    if (this.isReadOnly) return;
                    if (this.selectedService && this.selectedService.id === service.id) { this.cancelAssignment(); return; }
                    this.selectedService = service;
                },

                cancelAssignment() {
                    this.selectedService = null;
                    this.perToothAssignments = [];
                    this.batchBrandSelections = {};
                    this.batchManualPrice = 0;
                    this.editingItemId = null;
                },

                get servicePlanCounts() {
                    const counts = {};
                    this.planItems.forEach(item => { counts[item.service.id] = (counts[item.service.id] || 0) + 1; });
                    return counts;
                },


                buildPerToothAssignments() {
                    if (this.isReadOnly) return;
                    const basePrice = Number(this.selectedService?.base_price) || 0;
                    const existing = this.perToothAssignments.reduce((acc, a) => { acc[a.toothId] = a; return acc; }, {});
                    this.perToothAssignments = this.selectedTeeth.map(id => existing[id] || { toothId: id, brands: [], brandSelections: {}, price: 0, modified: false });
                },

                isBrandSelectedForTooth(aIdx, tabIdx, sectionIdx, brandIdx) {
                    const a = this.perToothAssignments[aIdx];
                    return (a?.brandSelections?.[`${tabIdx}-${sectionIdx}`] || []).map(Number).includes(Number(brandIdx));
                },

                getToothSelectedCountForTabAndSection(aIdx, tabIdx, sectionIdx) {
                    const a = this.perToothAssignments[aIdx];
                    return (a?.brandSelections?.[`${tabIdx}-${sectionIdx}`] || []).length;
                },

                toggleToothBrand(aIdx, tabIdx, sectionIdx, brandIdx) {
                    const a = this.perToothAssignments[aIdx];
                    if (!a.brandSelections) a.brandSelections = {};
                    const key = `${tabIdx}-${sectionIdx}`;
                    let sel = [...(a.brandSelections[key] || [])].map(Number);
                    const section = this.selectedService?.custom_prices?.tabs?.[tabIdx]?.sections?.[sectionIdx];
                    const isSingle = this.isSingleChoiceType(section?.type);

                    if (isSingle) {
                        sel = sel.includes(Number(brandIdx)) ? [] : [Number(brandIdx)];
                    } else {
                        sel = sel.includes(Number(brandIdx)) ? sel.filter(i => i !== Number(brandIdx)) : [...sel, Number(brandIdx)];
                    }

                    a.brandSelections[key] = sel;
                    this.recalculateToothPrice(aIdx);
                },

                recalculateToothPrice(aIdx) {
                    const a = this.perToothAssignments[aIdx];
                    const basePrice = Number(this.selectedService?.base_price || 0);
                    let total = 0;
                    a.brands = [];
                    (this.selectedService?.custom_prices?.tabs ?? []).forEach((tab, tIdx) => {
                        const sections = tab.sections || [];
                        sections.forEach((section, sIdx) => {
                            const sel = ((a.brandSelections?.[`${tIdx}-${sIdx}`]) || []).map(Number);
                            sel.forEach(bIdx => {
                                const brand = section.brands?.[bIdx];
                                if (brand) {
                                    if (brand.price) total += Number(brand.price);
                                    a.brands.push({
                                        name: brand.name,
                                        price: Number(brand.price) || 0,
                                        sectionTitle: section.title,
                                        tabTitle: tab.title,
                                        is_installment: !!brand.is_installment,
                                    });
                                }
                            });
                        });
                    });
                    a.price = total;
                    a.modified = Object.values(a.brandSelections || {}).some(arr => arr?.length > 0);
                    this.perToothAssignments = [...this.perToothAssignments];
                },

                isBrandSelectedInBatch(tabIdx, sectionIdx, brandIdx) {
                    return (this.batchBrandSelections[`${tabIdx}-${sectionIdx}`] || []).map(Number).includes(Number(brandIdx));
                },

                toggleBatchBrand(tabIdx, sectionIdx, brandIdx) {
                    const key = `${tabIdx}-${sectionIdx}`;
                    let sel = [...(this.batchBrandSelections[key] || [])].map(Number);
                    const section = this.selectedService?.custom_prices?.tabs?.[tabIdx]?.sections?.[sectionIdx];
                    const isSingle = this.isSingleChoiceType(section?.type);

                    if (isSingle) {
                        sel = sel.includes(Number(brandIdx)) ? [] : [Number(brandIdx)];
                    } else {
                        sel = sel.includes(Number(brandIdx)) ? sel.filter(i => i !== Number(brandIdx)) : [...sel, Number(brandIdx)];
                    }

                    this.batchBrandSelections[key] = sel;
                    this.batchBrandSelections = { ...this.batchBrandSelections };
                    this.recalculateBatchPrice();
                },

                getSelectedCountForTabAndSection(tabIdx, sectionIdx) {
                    return (this.batchBrandSelections[`${tabIdx}-${sectionIdx}`] || []).length;
                },

                recalculateBatchPrice() {
                    const basePrice = Number(this.selectedService?.base_price || 0);
                    let total = 0;
                    (this.selectedService?.custom_prices?.tabs ?? []).forEach((tab, tIdx) => {
                        const sections = tab.sections || [];
                        sections.forEach((section, sIdx) => {
                            const sel = (this.batchBrandSelections[`${tIdx}-${sIdx}`] || []).map(Number);
                            sel.forEach(bIdx => {
                                const brand = section.brands?.[bIdx];
                                if (brand) total += Number(brand.price || 0);
                            });
                        });
                    });
                    this.batchManualPrice = total;
                },

                get hasBatchSelections() { return Object.values(this.batchBrandSelections || {}).some(arr => arr?.length > 0); },

                applyBatchToAll() {
                    if (!this.hasBatchSelections) { showToast('لطفاً حداقل یک برند انتخاب کنید', 'error'); return; }
                    this.perToothAssignments.forEach((_, aIdx) => {
                        this.perToothAssignments[aIdx].brandSelections = JSON.parse(JSON.stringify(this.batchBrandSelections));
                        this.recalculateToothPrice(aIdx);
                    });
                    this.perToothAssignments = [...this.perToothAssignments];
                    showToast(`برندهای انتخابی روی ${this.selectedTeeth.length} دندان اعمال شد`, 'success');
                },

                get assignmentGroups() {
                    const map = {};
                    this.perToothAssignments.forEach(a => {
                        const k = JSON.stringify(a.brandSelections || {}) + '||' + a.price;
                        if (!map[k]) map[k] = { brands: a.brands || [], brandSelections: a.brandSelections || {}, price: a.price, teeth: [] };
                        map[k].teeth.push(a.toothId);
                    });
                    return Object.values(map);
                },

                get assignmentTotal() {
                    const basePrice = Number(this.selectedService?.base_price || 0);
                    return this.perToothAssignments.reduce((s, a) => s + (Number(a.price) || 0), 0) + basePrice;
                },
                get groupedPlanItems() {
                    const groups = {};
                    this.planItems.forEach(item => {
                        if (!groups[item.service.id]) groups[item.service.id] = { service: item.service, items: [] };
                        groups[item.service.id].items.push(item);
                    });
                    return Object.values(groups);
                },

                get canAdd() { return !this.isReadOnly && this.selectedService !== null && this.selectedTeeth.length > 0; },

                addToPlan() {
                    if (this.isReadOnly || !this.canAdd) return;
                    const minMonths = Number(this.settings.cure.default_warranty_months) || 0;
                    if (this.settings.cure.warranty_enabled && this.warrantyMonths > 0 && this.warrantyMonths < minMonths) {
                        showToast(`حداقل مدت ضمانت ${minMonths} ماه است`, 'error'); return;
                    }

                    for (const grp of this.assignmentGroups) {
                        for (const t of grp.teeth) {
                            const curBrands = grp.brands?.map(b => b.name).sort().join(',') || '';
                            const dup = this.planItems.some(item => item.id !== this.editingItemId && item.service.id === this.selectedService.id && item.teeth.includes(t) && (item.brands?.map(b => b.name).sort().join(',') || '') === curBrands);
                            if (dup) { showToast(`دندان ${this.getToothLabel(t).num} قبلاً با همین مشخصات ثبت شده است`, 'error'); return; }
                        }
                    }

                    if (this.editingItemId) {
                        this.planItems = this.planItems.filter(i => i.id !== this.editingItemId);
                        this.editingItemId = null;
                    }

                    let warrantyStr = null;
                    if (this.settings.cure.warranty_enabled) {
                        warrantyStr = this.warrantyText || (this.warrantyMonths > 0 ? `${this.warrantyMonths} ماه ضمانت` : null);
                    }

                    const hasTabs = this.selectedService?.custom_prices?.tabs?.length > 0;
                    const fallbackPrice = hasTabs ? 0 : (Number(this.selectedService?.base_price) || 0);
                    const basePrice = Number(this.selectedService?.base_price) || 0;

                    this.assignmentGroups.forEach((grp, idx) => {
                        this.planItems.push({
                            id: Date.now() + Math.random(),
                            teeth: [...grp.teeth],
                            service: { id: this.selectedService.id, name: this.selectedService.name },
                            brands: grp.brands?.length > 0 ? grp.brands.map(b => ({ ...b })) : null,
                            brandSelections: JSON.parse(JSON.stringify(grp.brandSelections || {})),
                            price: Number(grp.price) || 0,
                            base_price: idx===0 ? basePrice :0,
                            quantity: grp.teeth.length,
                            warranty: warrantyStr,
                            item_uuid: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
                        });
                    });

                    this.selectedTeeth = [];
                    this.preset = 'none';
                    this.cancelAssignment();
                    this.draftSaved = false;
                    showToast('به طرح درمان اضافه شد', 'success');
                },

                removeItem(id) {
                    if (this.isReadOnly) return;
                    this.planItems = this.planItems.filter(i => i.id !== id);
                    this.draftSaved = false;
                    if (this.editingItemId === id) {
                        this.cancelAssignment();
                    }
                },

                editItem(id) {
                    if (this.isReadOnly) return;
                    const item = this.planItems.find(i => i.id === id);
                    if (!item) return;
                    this.selectedTeeth = [...item.teeth].map(Number);
                    this.preset = 'none';
                    this.selectedService = this.services.find(s => String(s.id) === String(item.service.id)) || null;
                    this.editingItemId = id;
                    setTimeout(() => {
                        this.buildPerToothAssignments();
                        const bs = item.brandSelections || {};
                        this.batchBrandSelections = JSON.parse(JSON.stringify(bs));
                        this.perToothAssignments.forEach((assignment, aIdx) => {
                            this.perToothAssignments[aIdx].brandSelections = JSON.parse(JSON.stringify(bs));
                            this.recalculateToothPrice(aIdx);
                        });
                        this.recalculateBatchPrice();
                        this.batchBrandSelections = { ...this.batchBrandSelections };
                        this.perToothAssignments = [...this.perToothAssignments];
                    }, 200);
                },

                getItemDiscountedTotal(item) {
                    const itemSub = (item.price * item.quantity) + (item.base_price || 0);
                    if (this.discountType === 'percent' && this.discountAmount > 0) return itemSub * (1 - this.discountAmount / 100);
                    if (this.discountType === 'amount' && this.discountValue > 0 && this.subtotal > 0) return itemSub - (this.discountValue * itemSub / this.subtotal);
                    return itemSub;
                },

                getPlanSummaryInfo(plan) {
                    const coveredBrands = this.getPlanInstallmentBrands().filter(b => {
                        return this.getEligibleBrandKeysForOption(plan).includes(b.key);
                    });
                    if (coveredBrands.length === 0) return { dp: 0, months: 0, stages: 1 };

                    let maxMonths = 0, maxStages = 1, dp = 0;
                    coveredBrands.forEach(b => {
                        const tier = this.getEffectiveTierForBrand(plan, b.key, this.finalPayable);
                        if (tier) {
                            maxMonths = Math.max(maxMonths, tier.max_months);
                            maxStages = Math.max(maxStages, tier.payment_stages);
                            if (dp === 0 && tier.down_payments_map) {
                                const lastMonth = Math.max(...Object.keys(tier.down_payments_map).map(Number));
                                dp = Number(tier.down_payments_map[String(lastMonth)]) || 0;
                            }
                        }
                    });
                    return { dp, months: maxMonths, stages: maxStages };
                },

                getItemInstallmentInfo(item) {
                    if (!this.useInstallment || !this.selectedInstallmentOption) {
                        return { totalPayable: 0, downPayment: 0, monthly: 0, count: 0 };
                    }

                    const opt = this.selectedInstallmentOption;
                    const coveredKeys = this.selectedPlanCoveredKeys || new Set();
                    const selectedMonths = this.effectiveMonths || 12;

                    let totalCash = 0;
                    let totalDownPayment = 0;
                    let totalRemaining = 0;
                    let isAnyBrandEligible = false;

                    (item.brands || []).forEach(b => {
                        const key = this.makeBrandKey(item.service.id, b.tabTitle || '', b.sectionTitle || '', b.name);
                        const isEligible = coveredKeys.has(key);
                        const brandPrice = Number(b.price || 0) * (item.quantity || 1);

                        if (isEligible) {
                            isAnyBrandEligible = true;
                            const effPrice = this.getEffectivePrice(opt, item.service.id, b.tabTitle, b.sectionTitle, b.name, b.price) * (item.quantity || 1);
                            const tier = this.getEffectiveTierForBrand(opt, key, this.finalPayable);
                            const effDownPct = tier?.down_payments_map?.[String(selectedMonths)] !== undefined
                                ? Number(tier.down_payments_map[String(selectedMonths)])
                                : (tier ? Number(tier.down_payment) || 0 : 0);

                            const dp = effPrice * (effDownPct / 100);
                            totalDownPayment += dp;
                            totalRemaining += effPrice - dp;
                        } else {
                            totalCash += brandPrice;
                        }
                    });

                    const basePrice = Number(item.base_price || 0);
                    if (basePrice > 0) {
                        if (isAnyBrandEligible) {
                            const baseKey = this.makeBrandKey(item.service.id, '', '', 'قیمت پایه');
                            const tier = this.getEffectiveTierForBrand(opt, baseKey, this.finalPayable);
                            const effDownPct = tier?.down_payments_map?.[String(selectedMonths)] !== undefined
                                ? Number(tier.down_payments_map[String(selectedMonths)])
                                : (tier ? Number(tier.down_payment) || 0 : 30);

                            const dp = basePrice * (effDownPct / 100);
                            totalDownPayment += dp;
                            totalRemaining += basePrice - dp;
                        } else {
                            totalCash += basePrice;
                        }
                    }

                    const count = this.installmentsCount || 3;
                    const months = this.effectiveMonths || 1;
                    const isAnnualActive = months >= 12 && this.annualFeePct > 0;

                    let itemFee = 0;
                    if (isAnnualActive) {
                        const years = months / 12;
                        itemFee = totalRemaining * (this.annualFeePct / 100) * years;
                    } else {
                        itemFee = totalRemaining * (this.effectiveFeePct / 100);
                    }

                    const remainingWithFee = totalRemaining + itemFee;
                    const monthly = count > 0 ? this.roundAmount(remainingWithFee / count) : 0;

                    const roundedDownPayment = this.roundAmount(totalDownPayment);
                    const roundedRemainingWithFee = this.roundAmount(remainingWithFee);

                    return {
                        totalPayable: roundedDownPayment + roundedRemainingWithFee + totalCash,
                        downPayment: roundedDownPayment + totalCash,
                        monthly: monthly,
                        count: count
                    };
                },
                roundAmount(amount) {
                    let mode = String(this.settings.rounding_mode || 'none').trim().toLowerCase();

                    if ((mode.startsWith('"') && mode.endsWith('"')) ||
                        (mode.startsWith("'") && mode.endsWith("'"))) {
                        mode = mode.slice(1, -1).trim();
                    }

                    const factor = Number(this.settings.rounding_factor) || 0;
                    amount = Number(amount) || 0;

                    if (!mode || mode === 'none' || !factor || factor <= 0) {
                        return Math.round(amount);
                    }

                    if (mode === 'up') {
                        return Math.ceil(amount / factor) * factor;
                    }

                    if (mode === 'down') {
                        return Math.floor(amount / factor) * factor;
                    }

                    return Math.round(amount);
                },
                formatPrice(n) {
                    return (Number(n) || 0).toLocaleString('fa-IR');
                },

                serializeItemsForComparison(items) {
                    if (!items) return '[]';
                    return JSON.stringify(items.map(item => ({
                        service_id: Number(item.service_id || item.service?.id),
                        teeth: [...(item.teeth || [])].map(Number).sort((a,b)=>a-b),
                        brands: (item.brands || []).map(b => ({
                            name: String(b.name || '').trim(),
                            price: Number(b.price) || 0,
                            tabTitle: String(b.tabTitle || '').trim(),
                            sectionTitle: String(b.sectionTitle || '').trim(),
                        })).sort((a,b)=>a.name.localeCompare(b.name)),
                        brand_selections: item.brand_selections || item.brandSelections || {},
                        price: Number(item.price) || 0,
                        quantity: Number(item.quantity) || 0,
                        warranty: item.warranty || null,
                    })));
                },

                hasChanges() {
                    if (!this.existingPlan) return true;

                    const originalStatus = this.existingPlan.status || this.settings?.cure?.default_status || 'draft';
                    const statusChanged = (this.status !== originalStatus);

                    const clientChanged = Number(this.clientId) !== Number(this.existingPlan.client?.id || 0);
                    const nameChanged = (this.patientName || '').trim() !== (this.existingPlan.patient_name || '').trim();
                    const notesChanged = (this.notes || '').trim() !== (this.existingPlan.notes || '').trim();
                    const discountAmountChanged = Number(this.discountAmount) !== Number(this.existingPlan.discount_amount || 0);
                    const discountTypeChanged = (this.discountType || 'amount') !== (this.existingPlan.discount_type || 'amount');

                    const currentUseInst = !!(this.useInstallment && this.selectedInstallmentOptionId);
                    const oldUseInst = !!this.existingPlan.installment_option_id;
                    const useInstChanged = currentUseInst !== oldUseInst;

                    let installmentDetailsChanged = false;
                    if (currentUseInst && !useInstChanged) {
                        const optionIdChanged = this.selectedInstallmentOptionId !== this.existingPlan.installment_option_id;
                        const monthsChanged = Number(this.selectedInstallmentMonths) !== Number(this.existingPlan.installment_months);
                        const chequesCountChanged = Number(this.numberOfCheques) !== Number(this.existingPlan.installment_count);
                        const startDateChanged = (this.installmentStartDate || '') !== (this.existingPlan.installment_start_date || '');

                        const serializeCheques = (cheques) => {
                            return JSON.stringify((cheques || []).map(c => ({
                                amount: Number(c.amount) || 0,
                                date: c.date || '',
                                bankName: c.bankName || '',
                                chequeNumber: c.chequeNumber || '',
                            })));
                        };
                        const chequesChanged = serializeCheques(this.generatedCheques) !== serializeCheques(this.existingPlan.generated_cheques);

                        installmentDetailsChanged = optionIdChanged || monthsChanged || chequesCountChanged || startDateChanged || chequesChanged;
                    }

                    const serializeUsers = (users) => {
                        return JSON.stringify((users || []).map(u => ({
                            role_id: Number(u.role_id),
                            user_id: Number(u.user_id),
                        })).sort((a,b) => a.role_id - b.role_id));
                    };
                    const usersChanged = serializeUsers(this.assignedUsers) !== serializeUsers(this.existingPlan.assigned_users);

                    const itemsChanged = this.serializeItemsForComparison(this.planItems) !== this.serializeItemsForComparison(this.existingPlan.items);

                    return statusChanged || clientChanged || nameChanged || notesChanged || discountAmountChanged || discountTypeChanged || useInstChanged || installmentDetailsChanged || usersChanged || itemsChanged;
                },

                async savePlan(status = 'draft', shouldRedirect = true) {
                    if (this.planItems.length === 0 || this.isSaving) return;
                    if (!this.clientId) { showToast('لطفاً ابتدا مشتری را انتخاب کنید', 'error'); return; }
                    if (this.useInstallment && this.selectedInstallmentOption) {
                        if (this.generatedCheques.length === 0) {
                            showToast('لطفاً ابتدا چک‌های اقساط را تولید یا ثبت کنید', 'error', 4000);
                            this.showChequeSection = true;
                            return;
                        }
                        const diff = this.targetChequesTotal - this.totalChequesAmount;
                        if (Math.abs(diff) > 1) {
                            if (diff > 0) {
                                showToast(`مجموع مبلغ چک‌ها به مبلغ کل نرسیده است! ${this.formatPrice(diff)} ${this.currencyLabel} کم دارید.`, 'error', 5000);
                            } else {
                                showToast(`مجموع مبلغ چک‌ها از مبلغ کل بیشتر است! ${this.formatPrice(Math.abs(diff))} ${this.currencyLabel} اضافه است.`, 'error', 5000);
                            }
                            this.showChequeSection = true;
                            return;
                        }
                    }
                    this.isSaving = true;

                    let finalTotal = this.finalPayable;
                    let instDownPayment = 0, instMonthly = 0, instCount = 0, instMonths = 0, instOptionId = null, instOptionTitle = null, instFeeValue = 0;

                    if (this.useInstallment && this.selectedInstallmentOption) {
                        instOptionId = this.selectedInstallmentOptionId;
                        instOptionTitle = this.selectedInstallmentOption?.title || null;
                        instDownPayment = this.downPaymentAmount || 0;
                        instMonthly = this.monthlyPaymentAmount || 0;
                        instCount = this.numberOfCheques || 0;
                        instMonths = this.effectiveMonths || 0;
                        instFeeValue = this.installmentFeeValue || 0;
                        finalTotal = instDownPayment + (this.totalChequesAmount || 0) + (this.uncoveredTotal || 0);
                    }

                    const payload = {
                        client_id: this.clientId,
                        patient_name: this.patientName?.trim() || '',
                        status: status,
                        notes: this.notes || null,
                        assigned_users: this.assignedUsers,
                        discount_amount: Number(this.discountAmount) || 0,
                        discount_type: this.discountType,
                        subtotal: this.subtotal,
                        discount_value: this.discountValue,
                        tax_value: this.taxValue,
                        total: finalTotal,
                        installment_option_id: instOptionId,
                        installment_option_title: instOptionTitle,
                        installment_down_payment: instDownPayment,
                        installment_monthly_amount: instMonthly,
                        installment_months: instMonths,
                        installment_count: instCount,
                        installment_fee_value: instFeeValue,
                        installment_due_day: this.selectedDueDay,
                        generated_cheques: this.generatedCheques,
                        final_payable: finalTotal,
                        installment_start_date: this.installmentStartDate,
                        installment_interval_months: this.realIntervalMonths,
                        installment_down_payment_percent: this.effectiveDownPaymentPct,
                        installment_fee_percent: this.effectiveFeePct,
                        installment_cash_now: this.totalCashToPayNow,
                        installment_uncovered_total: this.uncoveredTotal,
                        installment_breakdown: this.mixedPaymentBreakdown,
                        items: this.planItems.map(item => ({
                            service_id: item.service.id,
                            service_name: item.service.name,
                            teeth: item.teeth,
                            brands: item.brands || [],
                            brand_selections: item.brandSelections || {},
                            price: Number(item.price) || 0,
                            base_price: Number(item.base_price) || 0,
                            quantity: item.quantity,
                            warranty: item.warranty || null,
                            item_uuid: item.item_uuid || null,
                        })),
                        workflow_bindings: this.workflowBindings,
                    };

                    const isEdit = !!existingPlan;
                    const url = isEdit ? `{{ route('user.booking.cure.update', ':id') }}`.replace(':id', existingPlan.id) : `{{ route('user.booking.cure.store') }}`;

                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast('تغییرات با موفقیت ذخیره شد', 'success');
                            if (status === 'draft') this.draftSaved = true;

                            // Redirect to edit page for new plans
                            if (!isEdit && data.id) {
                                window.location.href = `{{ route('user.booking.cure.edit', ':id') }}`.replace(':id', data.id);
                                return;
                            }

                            if (data.redirect && shouldRedirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            showToast(data.message || 'خطا در ذخیره‌سازی', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },
            };
        }
    </script>
@endsection
