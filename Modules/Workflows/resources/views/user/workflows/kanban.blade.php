@extends('layouts.user')

@section('title', 'بوم مسیر بیماران (Kanban)')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .kanban-scroll::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .kanban-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .kanban-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .dark .kanban-scroll::-webkit-scrollbar-thumb {
            background: #475569;
        }
    </style>

    <div class="space-y-6 pb-10" x-data="workflowKanbanApp(@js($selectedWorkflowId))" x-init="init()" x-cloak>
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">بوم مسیر بیماران (Kanban)</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">پیگیری و مدیریت وضعیت بیماران در طول مراحل مختلف گردش‌کارهای فعال.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('user.workflows.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition-colors">
                    لیست گردش‌کارها
                </a>
            </div>
        </div>

        <!-- Toolbar / Workflow Selector -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 shrink-0">انتخاب گردش‌کار:</span>
                    <select @change="changeWorkflow($event.target.value)" class="w-full sm:w-80 h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900/40 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition-all focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-gray-300 dark:hover:border-gray-600">
                        <option value="">— انتخاب کنید —</option>
                        @foreach($workflows as $wf)
                            <option value="{{ $wf->id }}" @selected($selectedWorkflowId == $wf->id)>{{ $wf->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                @if($selectedWorkflow)
                    <div class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        تعداد کل بیماران در جریان: <span class="text-indigo-600 dark:text-indigo-400 font-extrabold">{{ count($instances) }}</span> نفر
                    </div>
                @endif
            </div>
        </div>

        @if(!$selectedWorkflow)
            <!-- Empty State -->
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ گردش‌کاری انتخاب نشده است</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">برای مشاهده بوم مسیر درمان و وضعیت بیماران، لطفاً یک گردش‌کار فعال را از منوی بالا انتخاب کنید.</p>
            </div>
        @else
            <!-- Kanban Board -->
            <div class="flex gap-5 overflow-x-auto pb-6 kanban-scroll -mx-4 px-4 min-h-[600px] items-start">
                @foreach($columns as $col)
                    @php
                        $colInstances = $instances->where('current_node_id', $col->id);
                        $colColor = 'bg-slate-500';
                        if ($col->type === 'ACTION') $colColor = 'bg-indigo-500';
                        if ($col->type === 'CONDITION') $colColor = 'bg-amber-500';
                        if ($col->type === 'SUB_WORKFLOW') $colColor = 'bg-purple-500';
                    @endphp
                    <div class="w-80 shrink-0 bg-gray-50 dark:bg-slate-900/50 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-4 max-h-[800px] flex flex-col">
                        <!-- Column Header -->
                        <div class="flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $colColor }}"></span>
                                <h3 class="text-xs font-black text-gray-900 dark:text-white" title="{{ $col->name }}">{{ Str::limit($col->name, 25) }}</h3>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-800 text-[10px] font-bold text-gray-600 dark:text-gray-400">{{ count($colInstances) }}</span>
                        </div>

                        <!-- Cards Container -->
                        <div class="space-y-3 overflow-y-auto sc-thin flex-1 pr-1">
                            @if(count($colInstances) === 0)
                                <div class="text-center py-8 text-[11px] text-gray-400 dark:text-gray-500 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl bg-white dark:bg-gray-800/40">
                                    بیماری در این گام وجود ندارد
                                </div>
                            @else
                                @foreach($colInstances as $inst)
                                    @php
                                        $subject = $inst->subject;
                                        $clientName = 'نامشخص';
                                        $clientPhone = '';
                                        $detailsLink = '#';
                                        $clientCaseNumber = '';
                                        $clientNationalCode = '';

                                        if ($inst->related_type === 'CLIENT' && $subject) {
                                            $clientName = $subject->full_name;
                                            $clientPhone = $subject->phone;
                                            $clientCaseNumber = $subject->case_number;
                                            $clientNationalCode = $subject->national_code;
                                            $detailsLink = route('user.clients.show', $subject->id);
                                        } elseif ($inst->related_type === 'APPOINTMENT' && $subject) {
                                            $clientName = $subject->client?->full_name ?? 'نامشخص';
                                            $clientPhone = $subject->client?->phone ?? '';
                                            $clientCaseNumber = $subject->client?->case_number ?? '';
                                            $clientNationalCode = $subject->client?->national_code ?? '';
                                            $detailsLink = route('user.clients.show', $subject->client_id);
                                        } elseif ($inst->related_type === 'TREATMENT_PLAN' && $subject) {
                                            $clientName = $subject->client?->full_name ?? 'نامشخص';
                                            $clientPhone = $subject->client?->phone ?? '';
                                            $clientCaseNumber = $subject->client?->case_number ?? '';
                                            $clientNationalCode = $subject->client?->national_code ?? '';
                                            $detailsLink = route('user.clients.show', $subject->client_id);
                                        }
                                    @endphp
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-3 hover:shadow-md transition-shadow">
                                        <!-- Card Header (Subject Title) -->
                                        <div class="flex justify-between items-start">
                                            <a href="{{ $detailsLink }}" class="text-xs font-extrabold text-gray-900 dark:text-white hover:text-indigo-600 transition-colors" target="_blank">
                                                {{ $clientName }}
                                            </a>
                                            <span class="text-[9px] text-gray-400 dark:text-gray-500 font-mono">#{{ $inst->id }}</span>
                                        </div>

                                        <!-- Card Info (Case info / Contact) -->
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 space-y-1">
                                            @if($clientPhone)
                                                <div class="flex items-center gap-1.5 dir-ltr justify-end">
                                                    <span>{{ $clientPhone }}</span>
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                </div>
                                            @endif
                                            @if($clientCaseNumber)
                                                <div class="flex items-center gap-1.5 justify-end">
                                                    <span>شماره پرونده: {{ $clientCaseNumber }}</span>
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </div>
                                            @endif
                                            @if($clientNationalCode)
                                                <div class="flex items-center gap-1.5 justify-end">
                                                    <span>کد ملی: {{ $clientNationalCode }}</span>
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 2-2 2h4c-1.5 0-2-1.116-2-2z"/></svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Started At -->
                                        <div class="text-[9px] text-gray-400 dark:text-gray-500 pt-1 flex justify-between items-center">
                                            <span>زمان ورود به مسیر:</span>
                                            <span>{{ $inst->started_at ? \Morilog\Jalali\Jalalian::fromCarbon($inst->started_at)->format('Y/m/d H:i') : '—' }}</span>
                                        </div>

                                        <!-- Actions Container -->
                                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/80 flex flex-col gap-2">
                                            @if($col->type === 'CONDITION')
                                                @php
                                                    $expr = $col->config['condition_expression'] ?? '';
                                                    $varName = 'condition_result';
                                                    if (str_contains($expr, '=')) {
                                                        $varName = trim(explode('=', $expr, 2)[0]);
                                                    }
                                                @endphp
                                                <div class="bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-lg p-2 space-y-1.5">
                                                    <div class="text-[10px] font-black text-amber-700 dark:text-amber-400">تصمیم‌گیری شرط:</div>
                                                    <div class="flex gap-1.5">
                                                        <button @click="advanceWithChoice({{ $inst->id }}, '{{ $varName }}', 1)" :disabled="actionLoading" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold transition-all">
                                                            بله
                                                        </button>
                                                        <button @click="advanceWithChoice({{ $inst->id }}, '{{ $varName }}', 0)" :disabled="actionLoading" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[10px] font-bold transition-all">
                                                            خیر
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <button @click="advance({{ $inst->id }})" :disabled="actionLoading" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black transition-all flex justify-center items-center gap-1">
                                                    گام بعدی
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                                </button>
                                            @endif
                                            
                                            <div class="flex gap-2">
                                                <button @click="goBack({{ $inst->id }})" :disabled="actionLoading" class="flex-1 py-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded text-[9px] font-bold transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    گام قبلی
                                                </button>
                                                <button @click="cancel({{ $inst->id }})" :disabled="actionLoading" class="px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 border border-red-100 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30 rounded text-[9px] font-bold transition-all">
                                                    لغو
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function workflowKanbanApp(selectedWorkflowId) {
            return {
                selectedWorkflowId: selectedWorkflowId,
                actionLoading: false,
                
                init() {
                    // Nothing needed for init right now
                },
                
                changeWorkflow(wfId) {
                    if (wfId) {
                        window.location.href = `{{ route('user.workflows.kanban') }}?workflow_id=${wfId}`;
                    } else {
                        window.location.href = `{{ route('user.workflows.kanban') }}`;
                    }
                },
                
                async advance(id) {
                    this.actionLoading = true;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.actionLoading = false;
                    }
                },

                async advanceWithChoice(id, varName, value) {
                    this.actionLoading = true;
                    try {
                        const payload = {};
                        payload[varName] = value;
                        const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.actionLoading = false;
                    }
                },

                async goBack(id) {
                    if (!confirm('آیا از بازگشت به مرحله قبل اطمینان دارید؟')) return;
                    this.actionLoading = true;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/go-back`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.actionLoading = false;
                    }
                },

                async cancel(id) {
                    if (!confirm('آیا از لغو این گردش‌کار اطمینان دارید؟')) return;
                    this.actionLoading = true;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            window.location.reload();
                        }
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.actionLoading = false;
                    }
                }
            };
        }
    </script>
@endsection
