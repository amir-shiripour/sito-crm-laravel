@extends('layouts.user')

@section('title', 'ویرایش گردش کار')

@section('content')
    @php
        $tokenOptions = [
            'client_name' => 'نام مشتری',
            'appointment_date_jalali' => 'تاریخ (شمسی)',
            'appointment_time_jalali' => 'ساعت',
            'appointment_datetime_jalali' => 'تاریخ و ساعت کامل',
            'service_name' => 'نام سرویس',
            'provider_name' => 'نام ارائه‌دهنده',
        ];

        // Merge with tokens from config if available
        if(isset($tokens)) {
            foreach($tokens as $group => $groupTokens) {
                foreach($groupTokens as $key => $label) {
                    $tokenOptions[$key] = $label;
                }
            }
        }
    @endphp

    <style>
        /* Premium Stage Cards and Timeline */
        .timeline-container {
            position: relative;
        }
        .timeline-line {
            position: absolute;
            top: 24px;
            bottom: 24px;
            right: 20px;
            width: 3px;
            background: repeating-linear-gradient(
                to bottom,
                #cbd5e1 0px,
                #cbd5e1 8px,
                transparent 8px,
                transparent 16px
            );
        }
        .dark .timeline-line {
            background: repeating-linear-gradient(
                to bottom,
                #475569 0px,
                #475569 8px,
                transparent 8px,
                transparent 16px
            );
        }
        .timeline-item {
            position: relative;
            padding-right: 48px;
        }
        .timeline-badge {
            position: absolute;
            top: 4px;
            right: 0px;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 3px solid #6366f1;
            color: #6366f1;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.15);
            z-index: 10;
            transition: all 0.3s ease;
        }
        .dark .timeline-badge {
            background: #1e293b;
            border-color: #818cf8;
            color: #818cf8;
            box-shadow: 0 4px 10px rgba(129, 140, 248, 0.1);
        }
        .timeline-item:hover .timeline-badge {
            transform: scale(1.1);
            background: #6366f1;
            color: #ffffff;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }
        .dark .timeline-item:hover .timeline-badge {
            background: #818cf8;
            color: #0f172a;
            box-shadow: 0 0 15px rgba(129, 140, 248, 0.3);
        }
        .premium-stage-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(229, 231, 235, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dark .premium-stage-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(71, 85, 105, 0.4);
        }
        .premium-stage-card:hover {
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 10px 20px -8px rgba(0, 0, 0, 0.04);
        }
    </style>

    <div class="max-w-5xl mx-auto space-y-8 pb-20">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>ویرایش گردش کار:</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-extrabold">{{ $workflow->name }}</span>
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مدیریت مراحل، اکشن‌ها و تنظیمات فعال‌سازی.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('workflows.manage')
                    <a href="{{ route('user.workflows.designer', $workflow) }}"
                       class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-xl shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        طراحی گرافیکی (بوم)
                    </a>
                @endcan
                <a href="{{ route('user.workflows.index') }}"
                   class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                    بازگشت
                </a>
                <form method="post" action="{{ route('user.workflows.destroy', $workflow) }}" onsubmit="return confirm('آیا مطمئن هستید؟ تمام مراحل و اکشن‌ها حذف خواهند شد.');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/40 dark:hover:bg-red-900/30 transition-colors">
                        حذف گردش کار
                    </button>
                </form>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 dark:bg-emerald-950/20 dark:border-emerald-800 dark:text-emerald-300 shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl dark:bg-red-950/20 dark:border-red-800 dark:text-red-300 shadow-sm">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Settings (Collapsible) --}}
        <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-all">
            <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100/50 dark:hover:bg-gray-700/30 transition-colors">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    تنظیمات عمومی و محرک‌های شروع
                </h2>
                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1">
                    <span x-text="open ? 'بستن تنظیمات' : 'مشاهده و ویرایش تنظیمات'"></span>
                    <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </span>
            </button>
            <div x-show="open" x-collapse class="p-6 border-t border-gray-200 dark:border-gray-700">
                @include('workflows::user.workflows._form', [
                    'workflow' => $workflow,
                    'action' => route('user.workflows.update', $workflow),
                    'method' => 'patch'
                ])
            </div>
        </div>

        {{-- Stages Section --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="w-1.5 h-8 bg-indigo-500 rounded-full"></span>
                    مراحل گردش کار (Stages)
                </h2>
            </div>

            {{-- Add New Stage Form (Collapsible) --}}
            <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
                <button @click="open = !open" class="w-full px-6 py-4.5 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100/30 dark:hover:bg-gray-700/50 transition-colors">
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        افزودن مرحله جدید به فرآیند
                    </span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="p-6 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/10">
                    <form method="post" action="{{ route('user.workflows.stages.store', $workflow) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-end">
                            <div class="lg:col-span-5 space-y-2">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">نام مرحله</label>
                                <input type="text" name="name" placeholder="مثلاً: ارسال پیامک تایید" required
                                       class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 transition-all py-2.5 px-4 shadow-sm text-sm">
                            </div>
                            <div class="lg:col-span-2 space-y-2">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">ترتیب نمایش</label>
                                <input type="number" name="sort_order" min="0" value="{{ $workflow->stages->count() + 1 }}"
                                       class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500/20 focus:ring-4 transition-all py-2.5 px-4 shadow-sm text-sm">
                            </div>
                            <div class="lg:col-span-3 flex flex-col gap-2.5 justify-end pb-1.5">
                                <label class="inline-flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                    <input type="checkbox" name="is_initial" value="1" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                    <span class="font-medium">نقطه شروع فرآیند (Initial)</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                    <input type="checkbox" name="is_final" value="1" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                    <span class="font-medium">نقطه پایان فرآیند (Final)</span>
                                </label>
                            </div>
                            <div class="lg:col-span-2">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    افزودن مرحله
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stages Timeline --}}
            <div class="timeline-container">
                @if($workflow->stages->count() > 1)
                    <div class="timeline-line"></div>
                @endif

                <div class="space-y-8">
                    @forelse($workflow->stages as $stage)
                        @php
                            $actionCounts = [
                                'SMS' => $stage->actions->where('action_type', \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS)->count(),
                                'TASK' => $stage->actions->where('action_type', \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK)->count(),
                                'FOLLOWUP' => $stage->actions->where('action_type', \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP)->count(),
                                'NOTIFICATION' => $stage->actions->where('action_type', \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION)->count(),
                            ];
                            $summaryParts = [];
                            if ($actionCounts['SMS'] > 0) $summaryParts[] = $actionCounts['SMS'] . ' پیامک';
                            if ($actionCounts['TASK'] > 0) $summaryParts[] = $actionCounts['TASK'] . ' وظیفه';
                            if ($actionCounts['FOLLOWUP'] > 0) $summaryParts[] = $actionCounts['FOLLOWUP'] . ' پیگیری';
                            if ($actionCounts['NOTIFICATION'] > 0) $summaryParts[] = $actionCounts['NOTIFICATION'] . ' اعلان';
                            $summaryText = count($summaryParts) > 0 ? implode(' و ', $summaryParts) : 'بدون عملیات';
                        @endphp

                        <div class="timeline-item group">
                            <div class="timeline-badge">{{ $stage->sort_order }}</div>

                            <div x-data="{ open: true }" class="premium-stage-card rounded-2xl shadow-sm overflow-hidden">
                                {{-- Stage Header --}}
                                <div class="bg-gray-50/50 dark:bg-gray-800/20 px-6 py-4.5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 cursor-pointer select-none" @click="open = !open">
                                        <div class="text-gray-400 transform transition-transform" :class="{'rotate-180': !open}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                                                {{ $stage->name }}
                                                @if($stage->is_initial)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">
                                                        شروع
                                                    </span>
                                                @endif
                                                @if($stage->is_final)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                        پایان
                                                    </span>
                                                @endif
                                            </h3>
                                            <p class="text-xs text-indigo-600/70 dark:text-indigo-400/70 font-semibold mt-1 flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $summaryText }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                        <form method="post" action="{{ route('user.workflows.stages.destroy', [$workflow, $stage]) }}" onsubmit="return confirm('آیا از حذف این مرحله اطمینان دارید؟ تمام اکشن‌های داخل آن نیز حذف خواهند شد.');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-350 bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/30 px-3 py-2 rounded-lg transition-colors shadow-sm">
                                                حذف مرحله
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div x-show="open" x-collapse class="p-6 bg-white dark:bg-gray-800">
                                    {{-- Actions List --}}
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-extrabold text-gray-400 dark:text-gray-500 tracking-wider uppercase flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                عملیات‌های مرحله
                                            </h4>
                                        </div>

                                        <div class="space-y-3.5 pr-4 border-r-2 border-gray-100 dark:border-gray-700/60 mr-1.5">
                                            @foreach($stage->actions as $action)
                                                <div class="relative pr-6">
                                                    {{-- Connector Line --}}
                                                    <div class="absolute top-6 right-0 w-6 h-px bg-gray-200 dark:bg-gray-700/80"></div>

                                                    @include('workflows::user.workflows._action-form', ['action' => $action, 'mode' => 'edit'])
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Add Action --}}
                                        <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700 pr-8">
                                            @include('workflows::user.workflows._action-form', ['action' => null, 'mode' => 'create'])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl shadow-sm">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-500 mb-4">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">هنوز مرحله‌ای تعریف نشده است</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">برای شروع فرآیند، اولین مرحله را با استفاده از دکمه بالا اضافه کنید.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
