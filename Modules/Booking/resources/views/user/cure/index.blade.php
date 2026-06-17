@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
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
            box-shadow: 0 4px 20px rgba(99,102,241,.15);
            transform: translateY(-2px);
        }
        .dark .svc-card:hover {
            background: #273548;
            border-color: #475569;
        }
        .svc-card.svc-active {
            border-color: #6366f1;
            box-shadow: 0 4px 24px rgba(99,102,241,.25);
            background: linear-gradient(145deg,#eef2ff,#f5f3ff);
        }
        .dark .svc-card.svc-active {
            background: linear-gradient(145deg,rgba(99,102,241,.18),rgba(139,92,246,.1));
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
            box-shadow: 0 2px 8px rgba(99,102,241,.45);
            border: 2px solid #fff;
        }
        .dark .svc-badge { border-color: #1e293b; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-slide-up { animation: slideUp .22s ease forwards; }
        .anim-fade-up  { animation: fadeUp  .18s ease forwards; }
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
            background: rgba(99,102,241,0.07);
            border-bottom: 1px solid rgba(99,102,241,0.15);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .dark .assign-panel-head {
            background: rgba(99,102,241,0.12);
            border-bottom-color: rgba(99,102,241,0.25);
        }
        .batch-bar {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .dark .batch-bar {
            background: rgba(15,23,42,0.4);
            border-bottom-color: #1e293b;
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
            background: rgba(15,23,42,0.3);
            border-bottom-color: #1e293b;
            color: #475569;
        }
        .tooth-assign-row {
            display: grid;
            grid-template-columns: 48px 1fr 160px 80px;
            gap: 8px;
            align-items: center;
            padding: 8px 18px;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.1s;
        }
        .tooth-assign-row:last-of-type { border-bottom: none; }
        .tooth-assign-row:hover { background: #fafbff; }
        .dark .tooth-assign-row { border-bottom-color: #1e293b; }
        .dark .tooth-assign-row:hover { background: rgba(15,23,42,0.3); }
        .tooth-assign-row.is-modified { background: rgba(99,102,241,0.03); }
        .dark .tooth-assign-row.is-modified { background: rgba(99,102,241,0.06); }
        .tooth-chip-assign {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            position: relative;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            flex-shrink: 0;
        }
        .dark .tooth-chip-assign { border-color: #334155; background: #0f172a; color: #cbd5e1; }
        .modified-dot {
            position: absolute;
            top: 2px; right: 2px;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #6366f1;
        }
        .group-preview-bar {
            padding: 10px 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }
        .dark .group-preview-bar { background: rgba(15,23,42,0.3); border-top-color: #1e293b; }
        .group-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #6366f1;
            background: rgba(99,102,241,0.07);
            border: 1px solid rgba(99,102,241,0.15);
        }
        .dark .group-chip { color: #818cf8; background: rgba(99,102,241,0.12); border-color: rgba(99,102,241,0.2); }
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
        .dark .assign-footer { background: rgba(15,23,42,0.4); border-top-color: #1e293b; }
        .col-header-row {
            display: grid;
            grid-template-columns: 48px 1fr 160px 80px;
            gap: 8px;
            padding: 6px 18px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }
        .dark .col-header-row { background: rgba(15,23,42,0.2); border-bottom-color: #1e293b; }
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
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .3s;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast-success { background: linear-gradient(135deg,#10b981,#059669); }
        .toast-error   { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .toast-info    { background: linear-gradient(135deg,#6366f1,#8b5cf6); }

        /* New Workflow Styles */
        .bg-grid-pattern {
            background-size: 24px 24px;
            background-image: radial-gradient(circle, rgba(99, 102, 241, 0.15) 1px, transparent 1px);
        }
        .dark .bg-grid-pattern {
            background-image: radial-gradient(circle, rgba(99, 102, 241, 0.1) 1px, transparent 1px);
        }
        .edge-path-animated {
            stroke-dasharray: 6 6;
            animation: edgeFlow 1s linear infinite;
        }
        @keyframes edgeFlow {
            from { stroke-dashoffset: 12; }
            to { stroke-dashoffset: 0; }
        }
    </style>

    <div id="cure-toast" class="toast"></div>

    <div
        x-data="treatmentPlanApp(
            @js($servicesJs ?? []),
            @js($planJs ?? null),
            @js($isReadOnly ?? false),
            @js($clients ?? [])
        )"
        x-cloak
        class="space-y-5 pb-20"
        dir="rtl"
    >

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
                            @if($isReadOnly)
                                مشاهده طرح درمان
                            @elseif($planJs)
                                ویرایش طرح درمان
                            @else
                                ایجاد طرح درمان
                            @endif
                        </h1>
                        <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400
                                     text-[10px] font-bold rounded-md uppercase tracking-wide">پیش‌نویس</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">دندان → سرویس → تنظیم برند → افزودن به طرح</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div x-show="!isReadOnly" class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all"
                     :class="draftSaved
                         ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800/30'
                         : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800/30'">
                    <div class="w-2 h-2 rounded-full"
                         :class="draftSaved ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></div>
                    <span class="text-xs font-bold"
                          :class="draftSaved
                              ? 'text-emerald-600 dark:text-emerald-400'
                              : 'text-amber-600 dark:text-amber-400'"
                          x-text="draftSaved ? 'پیش‌نویس ذخیره شده' : 'پیش‌نویس ذخیره نشده'"></span>
                </div>

                {{-- Back to list --}}
                <a href="{{ route('user.booking.cure.list') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                          bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium
                          hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    لیست طرح‌ها
                </a>

                {{-- Client Dropdown --}}
                <div class="relative" x-data="{ clientDdOpen: false, clientSearch: '' }">
                    <div @click="isReadOnly ? null : clientDdOpen = !clientDdOpen"
                         class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-all min-w-50"
                         :class="isReadOnly
                             ? 'border-gray-100 dark:border-gray-750 bg-gray-50 dark:bg-gray-800/50 cursor-default'
                             : (clientDdOpen
                                 ? 'border-indigo-400 ring-2 ring-indigo-100 dark:ring-indigo-900/30 bg-white dark:bg-gray-700 cursor-pointer'
                                 : 'border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:border-indigo-300 cursor-pointer')">
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
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100"
                                      x-text="clients.find(c => c.id === clientId)?.full_name || 'مشتری'"></span>
                            </template>
                        </div>
                        <svg x-show="!isReadOnly" class="w-3.5 h-3.5 text-gray-400 transition-transform shrink-0"
                             :class="clientDdOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <svg x-show="clientId && !isReadOnly" class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <div x-show="clientDdOpen"
                         @click.away="clientDdOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                                </svg>
                                <input x-model="clientSearch" type="text" placeholder="انتخاب بیمار"
                                       @click.stop
                                       class="w-full pr-9 pl-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600
                                              bg-gray-50 dark:bg-gray-700/50 text-gray-800 dark:text-gray-100
                                              placeholder-gray-400 focus:outline-none focus:border-indigo-400
                                              focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all"/>
                            </div>
                        </div>
                        <div class="max-h-56 overflow-y-auto sc-thin">
                            <template x-for="client in filteredClients(clientSearch)" :key="client.id">
                                <button @click="clientId = client.id; patientName = client.full_name || patientName; clientDdOpen = false; clientSearch = ''"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-right transition-all
                                               hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border-b border-gray-50 dark:border-gray-700 last:border-0"
                                        :class="clientId === client.id ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                                    <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center text-sm font-bold"
                                         :class="clientId === client.id
                                             ? 'bg-indigo-600 text-white'
                                             : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                         x-text="client.full_name?.charAt(0) || '?'"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold truncate"
                                           :class="clientId === client.id ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-100'"
                                           x-text="client.full_name"></p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate"
                                           x-text="client.phone || client.email || ''"></p>
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
                            <button @click="clientId = null; clientDdOpen = false; clientSearch = ''"
                                    class="w-full py-2 rounded-xl text-sm font-medium text-rose-600 dark:text-rose-400
                                           bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-all">
                                حذف مشتری انتخاب شده
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Item count badge --}}
                <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl
                            border border-indigo-100 dark:border-indigo-800/30">
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400" x-text="planItems.length"></span>
                    <span class="text-xs text-indigo-500 dark:text-indigo-400">مورد</span>
                </div>

                {{-- Total badge --}}
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl
                            border border-emerald-100 dark:border-emerald-800/30">
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400"
                          x-text="formatPrice(total)"></span>
                    <span class="text-xs text-emerald-500 dark:text-emerald-400" x-text="currencyLabel"></span>
                </div>

                {{-- Save Draft — only if user has create or edit permission --}}
                @canany(['booking.cure.create', 'booking.cure.edit', 'booking.cure.manage'])
                    <button x-show="!isReadOnly" @click="savePlan('draft')"
                            :disabled="planItems.length === 0 || !clientId || isSaving || isReadOnly"
                            :class="(planItems.length === 0 || !clientId || isSaving) ? 'opacity-50 cursor-not-allowed' : 'hover:border-indigo-300 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span x-text="isSaving ? 'در حال ذخیره…' : 'ذخیره پیش‌نویس'"></span>
                    </button>
                @endcanany

                {{-- Confirm Plan — only if user has confirm permission --}}
                @canany(['booking.cure.confirm', 'booking.cure.manage'])
                    <button x-show="!isReadOnly" @click="savePlan('confirmed')"
                            :disabled="planItems.length === 0 || !clientId || isSaving || isReadOnly"
                            :class="(planItems.length === 0 || !clientId || isSaving || isReadOnly) ? 'opacity-50 cursor-not-allowed' :
                                    'hover:shadow-lg hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"
                            :title="isReadOnly ? 'ویرایش این طرح تأیید شده غیرفعال است' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        تأیید طرح
                    </button>
                @endcanany

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
                            <span x-show="servicePlanCounts && Object.keys(servicePlanCounts).length > 0"
                                  class="text-xs text-indigo-500 dark:text-indigo-400 font-normal">
                            (<span x-text="Object.keys(servicePlanCounts).length"></span> سرویس انتخاب شده)
                        </span>
                        </h2>
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="relative">
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                                </svg>
                                <input x-model="serviceSearch" type="text" placeholder="جستجوی سرویس…"
                                       class="pr-9 pl-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700
                                          bg-gray-50 dark:bg-gray-700/50 text-gray-800 dark:text-gray-100
                                          placeholder-gray-400 focus:outline-none focus:border-indigo-400
                                          focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 w-48 transition-all"/>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 overflow-x-auto sc-thin pb-1">
                        <button @click="filterCategory = null"
                                :class="filterCategory === null
                                ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300/40'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-4 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shrink-0">
                            همه
                        </button>
                        @foreach($categories ?? [] as $cat)
                            <button @click="filterCategory = {{ $cat->id }}"
                                    :class="filterCategory === {{ $cat->id }}
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300/40'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    class="px-4 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shrink-0">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="px-4 py-4 flex gap-3 overflow-x-auto sc-thin">
                    <template x-for="service in filteredServices" :key="service.id">
                        <div @click="selectService(service)"
                             :class="['svc-card', selectedService && selectedService.id === service.id ? 'svc-active' : '']">
                            <span x-show="servicePlanCounts[service.id]" class="svc-badge" x-text="servicePlanCounts[service.id]"></span>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div :class="selectedService && selectedService.id === service.id
                                     ? 'bg-indigo-600 shadow-md shadow-indigo-200/60'
                                     : (servicePlanCounts[service.id] ? 'bg-emerald-500 shadow-md shadow-emerald-200/60' : 'bg-gray-200 dark:bg-gray-600')"
                                     class="w-6 h-6 rounded-full shrink-0 flex items-center justify-center transition-all mt-0.5">
                                    <svg x-show="selectedService && selectedService.id === service.id || servicePlanCounts[service.id]"
                                         class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div x-show="service.custom_prices?.tabs?.length > 0"
                                     class="flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-700/40 shrink-0">
                                    <svg class="w-2.5 h-2.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-[9px] text-amber-600 dark:text-amber-400 font-bold"
                                          x-text="service.custom_prices.tabs.length + ' تب'"></span>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 leading-tight mb-1 line-clamp-2"
                               x-text="service.name"></p>
                        </div>
                    </template>
                    <template x-if="filteredServices.length === 0">
                        <div class="flex-1 py-10 text-center text-sm text-gray-400 dark:text-gray-500">سرویسی پیدا نشد</div>
                    </template>
                </div>
            </div>
        @endif

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
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                (<span x-text="selectedTeeth.length"></span> انتخابی)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button x-show="!isReadOnly" @click="selectJaw('upper')"
                                    :class="preset==='upper' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک بالا</button>
                            <button x-show="!isReadOnly" @click="selectJaw('lower')"
                                    :class="preset==='lower' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک پایین</button>
                            <button x-show="!isReadOnly" @click="selectAllTeeth()"
                                    :class="preset==='all' ? 'bg-violet-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">همه</button>
                            <button x-show="!isReadOnly" @click="resetTeeth()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600
                                           hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 transition-all">
                                پاک‌سازی
                            </button>
                            <button @click="toggleToothFilter()"
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2"
                                    :class="showOnlySelectedTeeth ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                <span x-text="showOnlySelectedTeeth ? 'نمایش همه' : 'فقط دندان‌های انتخاب شده'"></span>
                            </button>
                        </div>
                    </div>
                    <div class="px-4 pt-4 pb-1 relative">
                        <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur
                                    px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">انتخاب</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                  x-text="selectedTeeth.length"></span>
                        </div>
                        <x-booking::dental-chart/>
                    </div>
                    <div class="px-5 py-3 flex flex-wrap gap-1.5 min-h-12 border-t border-gray-50
                                dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="([pos, teeth], idx) in groupedTeeth" :key="pos">
                                <div class="flex items-center" :class="idx !== groupedTeeth.length - 1 ? 'border-l-2 border-gray-400 dark:border-gray-500 pl-2 ml-1' : ''">
                                    <template x-for="t in teeth" :key="t">
                                        <div role="button"
                                             @click="isReadOnly ? null : toggle(t)"
                                             class="inline-flex items-center justify-center w-8 h-8 m-0.5 bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 text-sm font-black transition-all border-solid"
                                             :class="[getQuadrantClasses(t), isReadOnly ? 'cursor-default pointer-events-none' : 'cursor-pointer']"
                                             x-text="getToothLabel(t).num">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <template x-if="selectedTeeth.length === 0">
                            <span class="text-xs text-gray-400 dark:text-gray-500 self-center"
                                  x-text="isReadOnly ? 'هیچ دندانی انتخاب نشده است' : 'روی دندان کلیک کنید تا انتخاب شود'">
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Per-tooth assignment panel --}}
                <div x-show="selectedService !== null"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="assign-panel shadow-lg shadow-indigo-100/40 dark:shadow-indigo-900/20">

                    <div class="assign-panel-head">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center"
                                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-indigo-800 dark:text-indigo-200"
                                   x-text="selectedService?.name ?? ''"></p>
                                <p class="text-[11px] text-indigo-500 dark:text-indigo-400 mt-0.5"
                                   x-text="selectedService?.category_name ?? ''"></p>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400"
                                      x-text="selectedTeeth.length"></span>
                                <span class="text-[10px] text-indigo-500 dark:text-indigo-400">دندان</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تنظیم دقیق هر دندان</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="showPerToothDetail" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-gradient-to-r peer-checked:from-violet-500 peer-checked:to-indigo-600"></div>
                            </label>
                        </div>
                        <button @click="cancelAssignment()"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
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
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
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
                                        <template x-for="(section, sIdx) in (tab.sections || [{title: '', brands: tab.brands || []}])" :key="sIdx">
                                            <div class="mb-5 last:mb-0">
                                                <div x-show="section.title" class="text-xs font-semibold text-violet-600 dark:text-violet-400 mb-2">
                                                    <span x-text="section.title"></span>
                                                </div>
                                                <div class="relative" x-data="{ ddOpen: false }">
                                                    <div @click="ddOpen = !ddOpen"
                                                         class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-sm cursor-pointer flex justify-between items-center">
                                                        <div class="truncate">
                                                            <template x-if="getSelectedCountForTabAndSection(tIdx, sIdx) === 0">
                                                                <span class="text-gray-500">انتخاب برند...</span>
                                                            </template>
                                                            <template x-if="getSelectedCountForTabAndSection(tIdx, sIdx) > 0">
                                                                <span class="font-bold text-indigo-600"
                                                                      x-text="getSelectedCountForTabAndSection(tIdx, sIdx) + ' برند انتخاب شده'"></span>
                                                            </template>
                                                        </div>
                                                        <svg class="w-4 h-4 transition-transform" :class="ddOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                    <div x-show="ddOpen" @click.away="ddOpen = false"
                                                         class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto sc-thin">
                                                        <template x-for="(brand, bIdx) in section.brands" :key="bIdx">
                                                            <label class="flex items-center gap-3 px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700 last:border-0">
                                                                <input type="checkbox"
                                                                       :checked="isBrandSelectedInBatch(tIdx, sIdx, bIdx)"
                                                                       @change="toggleBatchBrand(tIdx, sIdx, bIdx)"
                                                                       class="w-5 h-5 text-indigo-600 rounded border-gray-300">
                                                                <div class="flex-1">
                                                                    <p class="font-medium" x-text="brand.name"></p>
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
                                <div class="flex items-center justify-between bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                                    <div>
                                        <span class="text-sm text-gray-500">مبلغ کل اعمال شده:</span>
                                        <span class="block text-xl font-black text-emerald-600" x-text="formatPrice(batchManualPrice)"></span>
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
                        <div class="bg-gray-50/30 dark:bg-gray-800/10 pb-4"
                             x-transition:enter="transition-all duration-300"
                             x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                             x-transition:enter-end="opacity-100 max-h-[2000px]">
                            <div class="relative overflow-hidden px-5 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-4">
                                <div class="absolute -right-6 -top-6 w-28 h-28 bg-violet-400/15 dark:bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                <div class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-lg shadow-violet-200/50 dark:shadow-violet-900/40 shrink-0 border border-violet-400/50">
                                    <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-5 h-5 rounded-full bg-white dark:bg-gray-800 text-violet-600 dark:text-violet-400 text-[10px] font-black border border-violet-100 dark:border-violet-700 shadow-sm z-10">۲</span>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                </div>
                                <div class="relative z-10 flex-1">
                                    <h4 class="text-[14px] font-black text-gray-800 dark:text-gray-100 tracking-wide">تنظیم دقیق هر دندان</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">برای هر دندان برندهای دلخواه را انتخاب کنید</p>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <template x-for="(assignment, aIdx) in perToothAssignments" :key="assignment.toothId">
                                    <div class="bg-white dark:bg-gray-800 border rounded-3xl p-5 transition-all hover:shadow-md"
                                         :class="assignment.modified ? 'border-violet-300 dark:border-violet-700/50 bg-violet-50/30' : 'border-gray-200 dark:border-gray-700'">
                                        <div class="flex items-center gap-4 mb-6">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl bg-gray-50 dark:bg-gray-900 border-2 shadow-sm relative shrink-0"
                                                 :class="getQuadrantClasses(assignment.toothId)">
                                                <span class="text-gray-700 dark:text-gray-200" x-text="getToothLabel(assignment.toothId).num"></span>
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
                                                    <template x-for="(section, sIdx) in (tab.sections || [{title: '', brands: tab.brands || []}])" :key="sIdx">
                                                        <div class="mb-6 last:mb-0">
                                                            <div x-show="section.title" class="text-xs uppercase tracking-widest font-semibold text-violet-500 mb-2" x-text="section.title"></div>
                                                            <div class="relative" x-data="{ ddOpen: false }">
                                                                <div @click="ddOpen = !ddOpen"
                                                                     class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-sm cursor-pointer flex justify-between items-center hover:border-violet-400 transition-all">
                                                                    <div class="truncate">
                                                                        <template x-if="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) === 0">
                                                                            <span class="text-gray-400">انتخاب برند از این بخش...</span>
                                                                        </template>
                                                                        <template x-if="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) > 0">
                                                                            <span class="font-bold text-violet-600"
                                                                                  x-text="getToothSelectedCountForTabAndSection(aIdx, tIdx, sIdx) + ' برند انتخاب شده'"></span>
                                                                        </template>
                                                                    </div>
                                                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="ddOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </div>
                                                                <div x-show="ddOpen" @click.away="ddOpen = false"
                                                                     class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl max-h-60 overflow-y-auto sc-thin">
                                                                    <template x-for="(brand, bIdx) in (section.brands || [])" :key="bIdx">
                                                                        <label class="flex items-center gap-3 px-4 py-3 hover:bg-violet-50 dark:hover:bg-violet-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700 last:border-0">
                                                                            <input type="checkbox"
                                                                                   :checked="isBrandSelectedForTooth(aIdx, tIdx, sIdx, bIdx)"
                                                                                   @change="toggleToothBrand(aIdx, tIdx, sIdx, bIdx)"
                                                                                   class="w-5 h-5 text-violet-600 rounded border-gray-300">
                                                                            <div class="flex-1">
                                                                                <p class="font-medium" x-text="brand.name"></p>
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
                                                <span class="text-xl font-black text-emerald-600 dark:text-emerald-600" x-text="formatPrice(assignment.price)"></span>
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

                    {{-- Footer --}}
                    <div class="assign-footer">
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">مجموع این افزودنی:</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400"
                                      x-text="formatPrice(assignmentTotal)"></span>
                                <span class="text-xs text-gray-400" x-text="currencyLabel"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="cancelAssignment()"
                                    class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                                           text-gray-500 dark:text-gray-400 text-sm font-medium
                                           hover:border-gray-300 transition-all">
                                انصراف
                            </button>
                            <button @click="addToPlan()"
                                    :disabled="!canAdd"
                                    :class="!canAdd ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-xl hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'"
                                    class="flex items-center gap-2 px-5 py-2 rounded-xl text-white font-bold text-sm transition-all"
                                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span x-text="assignmentGroups.length > 1
                                    ? 'افزودن ' + assignmentGroups.length + ' آیتم به طرح'
                                    : 'افزودن به طرح'">
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>{{-- /left col --}}

            {{-- RIGHT COL --}}
            <div class="xl:col-span-1 sticky top-4 space-y-4">

                {{-- Plan Items --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col" style="max-height: 420px;">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm">
                            <span class="w-2 h-5 rounded-full bg-violet-500 shrink-0"></span>
                            آیتم‌های طرح درمان
                            <span class="inline-flex items-center justify-center px-1.5 min-w-5 h-5 rounded-full
                                         bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400
                                         text-[10px] font-bold"
                                  x-text="planItems.length"></span>
                        </h3>
                        <button x-show="planItems.length > 0 && !isReadOnly"
                                @click="planItems = []"
                                class="text-[11px] text-rose-500 hover:text-rose-700 font-medium transition-colors">
                            پاک کردن همه
                        </button>
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
                                                <button @click.stop="toggleHighlightItem(item.id)"
                                                        :class="highlightedItemId === item.id ? 'bg-emerald-600 text-white' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30'"
                                                        class="p-2 rounded-lg transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5 16.477 5 20.268 7.943 21.542 12 20.268 16.057 16.477 19 12 19 7.523 19 3.732 16.057 2.458 12z" />
                                                    </svg>
                                                </button>
                                                <button @click.stop="editItem(item.id)" title="ویرایش"
                                                        x-show="!isReadOnly"
                                                        class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-indigo-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </button>
                                                <button @click.stop="removeItem(item.id)" title="حذف"
                                                        x-show="!isReadOnly"
                                                        class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 text-rose-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 mb-3">
                                                <template x-for="t in item.teeth" :key="t">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-bold rounded-lg border-2 bg-white dark:bg-gray-900"
                                                          :class="getQuadrantClasses(t)"
                                                          x-text="getToothLabel(t).num"></span>
                                                </template>
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
                                            <div class="flex justify-between items-end mt-2">
                                                <div>
                                                    <span class="text-xs text-gray-500">قیمت واحد</span>
                                                    <span class="block text-lg font-bold text-emerald-600" x-text="formatPrice(item.price)"></span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs text-gray-500">جمع</span>
                                                    <span class="block font-black text-xl text-violet-600" x-text="formatPrice(item.price * item.quantity)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Financial Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm">
                            <span class="w-2 h-5 rounded-full bg-emerald-500 shrink-0"></span>
                            خلاصه مالی
                        </h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">جمع کل موارد:</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200"
                                  x-text="formatPrice(subtotal) + ' ' + currencyLabel"></span>
                        </div>
                        <div x-show="!isReadOnly">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">تخفیف</label>
                            <div class="flex items-center gap-2">
                                <select x-model="discountType"
                                        :disabled="isReadOnly"
                                        class="px-2 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600
                                               bg-white dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300
                                               focus:outline-none focus:border-rose-400 shrink-0
                                               disabled:opacity-60 disabled:cursor-not-allowed">
                                    <option value="amount" x-text="currencyLabel"></option>
                                </select>
                                <input x-model.number="discountAmount" type="number" min="0" step="1000"
                                       :max="discountType === 'amount' ? subtotal : 100"
                                       :disabled="isReadOnly"
                                       @change="sanitizeDiscount()"
                                       placeholder="0"
                                       class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200
                                              focus:outline-none focus:border-rose-400 focus:ring-2
                                              focus:ring-rose-50 dark:focus:ring-rose-900/30 text-left ltr
                                              disabled:opacity-60 disabled:cursor-not-allowed"/>
                                <span class="text-xs text-rose-500 font-bold shrink-0"
                                      x-text="'−' + formatPrice(discountValue)"></span>
                            </div>
                        </div>
                        <div x-show="isReadOnly && discountValue > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">تخفیف:</span>
                            <span class="font-bold text-rose-500" x-text="'−' + formatPrice(discountValue) + ' ' + currencyLabel"></span>
                        </div>
                        <div class="h-px bg-gray-100 dark:bg-gray-700"></div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-700 dark:text-gray-200 text-sm">قابل پرداخت:</span>
                            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400"
                                  x-text="formatPrice(total)"></span>
                        </div>
                        <p class="text-xs text-emerald-500 dark:text-emerald-600 text-left -mt-3" x-text="currencyLabel"></p>
                    </div>

                    {{-- Confirm button in summary card --}}
                    @canany(['booking.cure.confirm', 'booking.cure.manage'])
                        <div x-show="!isReadOnly" class="px-4 pb-4 space-y-2.5">
                            <button @click="savePlan('confirmed')"
                                    :disabled="planItems.length === 0 || !clientId || isSaving || isReadOnly"
                                    :class="(planItems.length === 0 || !clientId || isSaving || isReadOnly) ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:shadow-lg hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.01]'"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                           text-white font-bold text-sm transition-all"
                                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="isSaving ? 'در حال ذخیره…' : 'تأیید طرح'"></span>
                            </button>
                        </div>
                    @endcanany
                          {{-- Notes --}}
                <div x-show="!isReadOnly || notes" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 mb-3 text-sm">
                        <span class="w-2 h-5 rounded-full bg-teal-500 shrink-0"></span>
                        یادداشت‌ها
                    </h3>
                    <div x-show="isReadOnly" class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700" x-text="notes"></div>
                    <textarea x-show="!isReadOnly" x-model="notes" rows="3"
                              placeholder="توضیحات مربوط به طرح..."
                              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                     bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-800 dark:text-gray-100
                                     placeholder-gray-400 focus:outline-none focus:border-teal-400
                                    focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900/30
                                     resize-none transition-all"></textarea>
                </div>

                {{-- NEW ADVANCED WORKFLOW MANAGEMENT REDIRECT BUTTON --}}
                <template x-if="workflows && workflows.length > 0">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 anim-fade-up">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">مسیر هوشمند درمان</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشاهده و مدیریت وضعیت گردش‌کار و مراحل اجرایی فرآیند درمان</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('user.booking.cure.workflows', $planJs['id'] ?? '') }}" 
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md shadow-indigo-100 dark:shadow-indigo-950/40 hover:shadow-lg hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                مشاهده و مدیریت مسیر درمان
                            </a>
                        </div>
                    </div>
                </template>


            </div>
        </div>
    </div>

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

        function treatmentPlanApp(services, existingPlan = null, isReadOnly = false, clients = []) {
            return {
                isReadOnly: isReadOnly,
                highlightedItemId: null,
                clients: clients,
                clientId: null,
                settings: { currency: 'IRT' },
                workflows: existingPlan ? (existingPlan.workflows || []) : [],
                get currencyLabel() {
                    return this.settings.currency === 'IRR' ? 'ریال' : 'تومان';
                },

                isSaving: false,
                draftSaved: false,
                showPerToothDetail: false,
                showOnlySelectedTeeth: false,
                selectedTeeth: [],
                preset: 'none',
                upperJawIds: [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                lowerJawIds: [15,16,17,18,19,20,21,22,23,24,25,26,27,28],

                toggle(id) {
                    if (this.isReadOnly) return;
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
                    if (this.highlightedItemId !== null) {
                        return this.selectedTeeth.includes(id)
                            ? 'tooth-path tooth-highlighted'
                            : 'tooth-path tooth-unselected';
                    }
                    if (this.selectedTeeth.includes(id)) return 'tooth-path tooth-selected';
                    if (this.planItems.some(i => i.teeth.includes(id))) return 'tooth-path tooth-in-plan';
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

                toggleToothFilter() {
                    this.showOnlySelectedTeeth = !this.showOnlySelectedTeeth;
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
                    const mapping = {
                        1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                        5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                        8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                        12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                        15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                        19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                        22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                        26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                    };
                    return mapping[id] ?? { num: id, pos: 'UR' };
                },

                getQuadrantClasses(id) {
                    const tooth = this.getToothLabel(id);
                    switch(tooth.pos) {
                        case 'UR': return '!border-r-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'UL': return '!border-l-4 !border-t-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LR': return '!border-r-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        case 'LL': return '!border-l-4 !border-b-4 !border-cyan-600 dark:!border-cyan-600';
                        default:   return '';
                    }
                },

                get groupedTeeth() {
                    const sorted = [...this.selectedTeeth].sort((a, b) => {
                        const posOrder = { 'UR': 1, 'UL': 2, 'LR': 3, 'LL': 4 };
                        return posOrder[this.getToothLabel(a).pos] - posOrder[this.getToothLabel(b).pos];
                    });
                    const groups = { 'UR': [], 'UL': [], 'LR': [], 'LL': [] };
                    sorted.forEach(t => groups[this.getToothLabel(t).pos].push(t));
                    return Object.entries(groups).filter(([key, val]) => val.length > 0);
                },

                services: services,
                selectedService: null,
                serviceSearch: '',
                filterCategory: null,
                perToothAssignments: [],
                batchBrandSelections: {},
                batchManualPrice: 0,

                async init() {
                    if (existingPlan && existingPlan.items && existingPlan.items.length > 0) {
                        this.clientId       = existingPlan.client?.id || null;
                        this.patientName    = existingPlan.patient_name || '';
                        this.notes          = existingPlan.notes || '';
                        this.discountAmount = Number(existingPlan.discount_amount) || 0;
                        this.discountType   = existingPlan.discount_type || 'amount';
                        this.planItems = (existingPlan.items || []).map((item, index) => ({
                            id: Date.now() + index + Math.random(),
                            teeth: Array.isArray(item.teeth) ? item.teeth : [],
                            service: { id: item.service_id, name: item.service_name },
                            brands: Array.isArray(item.brands) ? item.brands : [],
                            brandSelections: item.brand_selections || item.brandSelections || {},
                            price: Number(item.price) || 0,
                            quantity: Number(item.quantity) || (Array.isArray(item.teeth) ? item.teeth.length : 1),
                        }));
                        this.draftSaved = !!(existingPlan && existingPlan.id);
                    }



                    this.$watch('clientId',        () => { this.draftSaved = false; });
                    this.$watch('discountAmount',   () => { this.draftSaved = false; });
                    this.$watch('discountType',     () => { this.draftSaved = false; });

                    try {
                        const res = await fetch('/user/booking/settings', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.settings.currency = data.currency_unit || 'IRT';
                        }
                    } catch (e) {}

                    this.$watch('selectedService', val => {
                        if (val) this.buildPerToothAssignments();
                    });
                },

                get filteredServices() {
                    const q = this.serviceSearch.toLowerCase();
                    return this.services.filter(s =>
                        (!q || s.name.toLowerCase().includes(q)) &&
                        (this.filterCategory === null || s.category_id === this.filterCategory)
                    );
                },

                filteredClients(search = '') {
                    const q = search.toLowerCase().trim();
                    if (!q) return this.clients;
                    return this.clients.filter(c =>
                        (c.full_name && c.full_name.toLowerCase().includes(q)) ||
                        (c.phone && c.phone.includes(q)) ||
                        (c.email && c.email.toLowerCase().includes(q))
                    );
                },

                selectService(service) {
                    if (this.isReadOnly) return;
                    if (this.selectedService && this.selectedService.id === service.id) {
                        this.cancelAssignment();
                        return;
                    }
                    this.selectedService = service;
                },

                cancelAssignment() {
                    this.selectedService      = null;
                    this.perToothAssignments  = [];
                    this.batchBrandSelections = {};
                    this.batchManualPrice     = 0;
                },

                get servicePlanCounts() {
                    const counts = {};
                    this.planItems.forEach(item => {
                        counts[item.service.id] = (counts[item.service.id] || 0) + 1;
                    });
                    return counts;
                },

                buildPerToothAssignments() {
                    if (this.isReadOnly) return;
                    const basePrice = Number(this.selectedService?.base_price) || 0;
                    const existing = this.perToothAssignments.reduce((acc, a) => { acc[a.toothId] = a; return acc; }, {});
                    this.perToothAssignments = this.selectedTeeth.map(id => {
                        if (existing[id]) return existing[id];
                        return { toothId: id, brands: [], brandSelections: {}, price: basePrice, modified: false };
                    });
                },

                flatBrandsForTab(tabIndex) {
                    const tab = this.selectedService?.custom_prices?.tabs?.[tabIndex];
                    if (!tab) return [];
                    const entries = [];
                    const sections = tab.sections ?? (tab.brands?.length ? [{ title: '', brands: tab.brands }] : []);
                    for (const section of sections) {
                        if (!section.brands?.length) continue;
                        if ((sections.length ?? 0) > 1 && section.title) {
                            entries.push({ type: 'header', label: section.title });
                        }
                        for (let bIdx = 0; bIdx < section.brands.length; bIdx++) {
                            entries.push({
                                type: 'brand',
                                key: `${tabIndex}__${section.title || 'default'}__${bIdx}`,
                                brand: section.brands[bIdx],
                                sectionTitle: section.title,
                                tabTitle: tab.title,
                            });
                        }
                    }
                    return entries;
                },

                isBrandSelectedForTooth(aIdx, tabIdx, sectionIdx, brandIdx) {
                    const assignment = this.perToothAssignments[aIdx];
                    if (!assignment.brandSelections) return false;
                    return (assignment.brandSelections[`${tabIdx}-${sectionIdx}`] || []).includes(brandIdx);
                },

                getToothSelectedCountForTabAndSection(aIdx, tabIdx, sectionIdx) {
                    const assignment = this.perToothAssignments[aIdx];
                    if (!assignment.brandSelections) return 0;
                    return (assignment.brandSelections[`${tabIdx}-${sectionIdx}`] || []).length;
                },

                toggleToothBrand(aIdx, tabIdx, sectionIdx, brandIdx) {
                    const assignment = this.perToothAssignments[aIdx];
                    if (!assignment.brandSelections) assignment.brandSelections = {};
                    const key = `${tabIdx}-${sectionIdx}`;
                    let selected = [...(assignment.brandSelections[key] || [])];
                    if (selected.includes(brandIdx)) {
                        selected = selected.filter(i => i !== brandIdx);
                    } else {
                        selected.push(brandIdx);
                    }
                    assignment.brandSelections[key] = selected;
                    this.recalculateToothPrice(aIdx);
                },

                recalculateToothPrice(aIdx) {
                    const assignment = this.perToothAssignments[aIdx];
                    let total = Number(this.selectedService?.base_price || 0);
                    assignment.brands = [];
                    (this.selectedService?.custom_prices?.tabs ?? []).forEach((tab, tIdx) => {
                        const sections = tab.sections || [{ title: '', brands: tab.brands || [] }];
                        sections.forEach((section, sIdx) => {
                            const selectedIndices = (assignment.brandSelections && assignment.brandSelections[`${tIdx}-${sIdx}`]) || [];
                            selectedIndices.forEach(bIdx => {
                                const brand = section.brands?.[bIdx];
                                if (brand) {
                                    if (brand.price) total += Number(brand.price);
                                    assignment.brands.push({
                                        name: brand.name,
                                        price: brand.price,
                                        sectionTitle: section.title,
                                        tabTitle: tab.title,
                                    });
                                }
                            });
                        });
                    });
                    assignment.price = total;
                    assignment.modified = Object.values(assignment.brandSelections || {}).some(arr => arr && arr.length > 0);
                    this.perToothAssignments = [...this.perToothAssignments];
                },

                isBrandSelectedInBatch(tabIdx, sectionIdx, brandIdx) {
                    return (this.batchBrandSelections[`${tabIdx}-${sectionIdx}`] || []).includes(brandIdx);
                },

                toggleBatchBrand(tabIdx, sectionIdx, brandIdx) {
                    const key = `${tabIdx}-${sectionIdx}`;
                    let selected = [...(this.batchBrandSelections[key] || [])];
                    if (selected.includes(brandIdx)) {
                        selected = selected.filter(i => i !== brandIdx);
                    } else {
                        selected.push(brandIdx);
                    }
                    this.batchBrandSelections[key] = selected;
                    this.recalculateBatchPrice();
                },

                getSelectedCountForTabAndSection(tabIdx, sectionIdx) {
                    return (this.batchBrandSelections[`${tabIdx}-${sectionIdx}`] || []).length;
                },

                recalculateBatchPrice() {
                    let total = Number(this.selectedService?.base_price || 0);
                    (this.selectedService?.custom_prices?.tabs ?? []).forEach((tab, tIdx) => {
                        const sections = tab.sections || [{ title: '', brands: tab.brands || [] }];
                        sections.forEach((section, sIdx) => {
                            const selectedIndices = this.batchBrandSelections[`${tIdx}-${sIdx}`] || [];
                            selectedIndices.forEach(bIdx => {
                                const brand = section.brands?.[bIdx];
                                if (brand) total += Number(brand.price || 0);
                            });
                        });
                    });
                    this.batchManualPrice = total;
                },

                get hasBatchSelections() {
                    return Object.values(this.batchBrandSelections || {}).some(arr => arr && arr.length > 0);
                },

                applyBatchToAll() {
                    if (!this.hasBatchSelections) {
                        showToast('لطفاً حداقل یک برند انتخاب کنید', 'error');
                        return;
                    }
                    this.perToothAssignments.forEach((assignment, aIdx) => {
                        assignment.brandSelections = JSON.parse(JSON.stringify(this.batchBrandSelections));
                        this.recalculateToothPrice(aIdx);
                    });
                    showToast(`برندهای انتخابی روی ${this.selectedTeeth.length} دندان اعمال شد`, 'success');
                },

                get assignmentGroups() {
                    const map = {};
                    this.perToothAssignments.forEach(a => {
                        const k = JSON.stringify(a.brandSelections || {}) + '||' + a.price;
                        if (!map[k]) {
                            map[k] = { brands: a.brands || [], brandSelections: a.brandSelections || {}, price: a.price, teeth: [] };
                        }
                        map[k].teeth.push(a.toothId);
                    });
                    return Object.values(map);
                },

                get assignmentTotal() {
                    return this.perToothAssignments.reduce((s, a) => s + (Number(a.price) || 0), 0);
                },

                planItems: [],
                patientName: '',
                notes: '',
                discountAmount: 0,
                discountType: 'amount',

                get groupedPlanItems() {
                    const groups = {};
                    this.planItems.forEach(item => {
                        if (!groups[item.service.id]) {
                            groups[item.service.id] = { service: item.service, items: [] };
                        }
                        groups[item.service.id].items.push(item);
                    });
                    return Object.values(groups);
                },

                get canAdd() {
                    return !this.isReadOnly && this.selectedService !== null && this.selectedTeeth.length > 0;
                },

                addToPlan() {
                    if (this.isReadOnly || !this.canAdd) return;
                    for (const grp of this.assignmentGroups) {
                        for (const t of grp.teeth) {
                            const currentBrands = grp.brands?.map(b => b.name).sort().join(',') || '';
                            const duplicate = this.planItems.some(item =>
                                item.service.id === this.selectedService.id &&
                                item.teeth.includes(t) &&
                                (item.brands?.map(b => b.name).sort().join(',') || '') === currentBrands
                            );
                            if (duplicate) {
                                showToast(`دندان ${this.getToothLabel(t).num} قبلاً با همین مشخصات ثبت شده است`, 'error');
                                return;
                            }
                        }
                    }
                    this.assignmentGroups.forEach(grp => {
                        this.planItems.push({
                            id: Date.now() + Math.random(),
                            teeth: [...grp.teeth],
                            service: { id: this.selectedService.id, name: this.selectedService.name },
                            brands: grp.brands && grp.brands.length > 0 ? [...grp.brands] : null,
                            brandSelections: JSON.parse(JSON.stringify(grp.brandSelections || {})),
                            price: Number(grp.price) || Number(this.selectedService?.base_price) || 0,
                            quantity: grp.teeth.length,
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
                },

                editItem(id) {
                    if (this.isReadOnly) return;
                    const item = this.planItems.find(i => i.id === id);
                    if (!item) return;
                    this.removeItem(id);
                    this.selectedTeeth = [...item.teeth];
                    this.preset = 'none';
                    this.selectedService = this.services.find(s => s.id === item.service.id) || null;
                    setTimeout(() => {
                        this.buildPerToothAssignments();
                        const brandSelections = item.brandSelections || {};
                        const isBatchMode = Object.keys(brandSelections).length > 0 &&
                            !this.selectedTeeth.some(t => Object.keys(brandSelections).some(k => k.includes(t)));
                        if (isBatchMode || Object.keys(brandSelections).length === 0) {
                            this.showPerToothDetail = false;
                            this.batchBrandSelections = JSON.parse(JSON.stringify(brandSelections));
                        } else {
                            this.showPerToothDetail = true;
                            this.perToothAssignments.forEach((assignment, aIdx) => {
                                if (item.teeth.includes(assignment.toothId)) {
                                    assignment.brandSelections = JSON.parse(JSON.stringify(brandSelections));
                                    this.recalculateToothPrice(aIdx);
                                }
                            });
                        }
                    }, 200);
                },

                async savePlan(status = 'draft') {
                    if (this.planItems.length === 0 || this.isSaving) return;
                    if (!this.clientId) {
                        showToast('لطفاً ابتدا مشتری را انتخاب کنید', 'error');
                        return;
                    }
                    if (this.isReadOnly) {
                        showToast('امکان ذخیره در حالت مشاهده وجود ندارد', 'error');
                        return;
                    }
                    this.isSaving = true;
                    const payload = {
                        client_id:       this.clientId,
                        patient_id:      {{ auth()->id() }},
                        patient_name:    this.patientName?.trim() || '',
                        status:          status,
                        notes:           this.notes || null,
                        discount_amount: Number(this.discountAmount) || 0,
                        discount_type:   this.discountType,
                        subtotal:        this.subtotal,
                        discount_value:  this.discountValue,
                        total:           this.total,
                        items: this.planItems.map(item => ({
                            service_id:       item.service.id,
                            service_name:     item.service.name,
                            teeth:            item.teeth,
                            brands:           item.brands || [],
                            brand_selections: item.brandSelections || {},
                            price:            Number(item.price) || 0,
                            quantity:         item.quantity,
                            subtotal:         Number(item.price) * item.quantity,
                        })),
                    };
                    const isEdit = !!existingPlan;
                    const url    = isEdit
                        ? `{{ route('user.booking.cure.update', ':id') }}`.replace(':id', existingPlan.id)
                        : `{{ route('user.booking.cure.store') }}`;
                    const method = isEdit ? 'PUT' : 'POST';
                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type':     'application/json',
                                'Accept':           'application/json',
                                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast(data.message || 'با موفقیت ذخیره شد', 'success');
                            if (status === 'draft') this.draftSaved = true;
                            if (data.redirect) window.location.href = data.redirect;
                        } else {
                            showToast(data.message || 'خطا در ذخیره‌سازی', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                get subtotal() {
                    return this.planItems.reduce((s, i) => s + i.price * i.quantity, 0);
                },
                get discountValue() {
                    const d = Number(this.discountAmount) || 0;
                    if (this.discountType === 'percent') return Math.min(this.subtotal, this.subtotal * d / 100);
                    return Math.min(this.subtotal, d);
                },
                get total() {
                    return Math.max(0, this.subtotal - this.discountValue);
                },
                sanitizeDiscount() {
                    if (this.discountType === 'amount') {
                        if (this.discountAmount > this.subtotal) this.discountAmount = this.subtotal;
                    } else {
                        if (this.discountAmount > 100) this.discountAmount = 100;
                    }
                },
                formatPrice(n) {
                    return (Number(n) || 0).toLocaleString('fa-IR');
                },
            };
        }
    </script>
@endsection
