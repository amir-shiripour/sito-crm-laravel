@extends('layouts.user')

@section('content')
@php
    $defaultTokens = [
        'appointment' => [
            'client_name' => 'نام مشتری',
            'client_phone' => 'شماره مشتری',
            'service_name' => 'نام سرویس',
            'provider_name' => 'نام پزشک',
            'appointment_date_jalali' => 'تاریخ نوبت (شمسی)',
            'appointment_time_jalali' => 'ساعت نوبت',
            'appointment_datetime_jalali' => 'تاریخ و ساعت کامل',
            'payment_link' => 'لینک پرداخت',
        ],
        'treatment_plan' => [
            'plan_id' => 'شناسه طرح درمان',
            'patient_name' => 'نام بیمار',
            'status' => 'شناسه وضعیت',
            'status_label' => 'نام وضعیت طرح درمان',
            'total' => 'مبلغ کل',
            'final_payable' => 'مبلغ قابل پرداخت',
            'currency' => 'واحد پول',
            'client_phone' => 'شماره بیمار',
            'creator_name' => 'ثبت کننده طرح',
            'creator_phone' => 'تلفن ثبت کننده',
        ]
    ];

    if (isset($cureRoles)) {
        foreach ($cureRoles as $role) {
            $roleSlug = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/u', '_', $role->name);
            $roleSlug = trim(preg_replace('/_+/', '_', $roleSlug), '_');
            if (empty($roleSlug)) {
                $roleSlug = 'role_' . $role->id;
            }
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_name"] = "نام «{$role->name}»";
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_phone"] = "تلفن «{$role->name}»";
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_all_names"] = "همه «{$role->name}»ها";
        }
    }

    $configTokens = config('workflows.tokens', []);
    $groupedTokens = $defaultTokens;
    foreach ($configTokens as $group => $tokens) {
        if (!isset($groupedTokens[$group])) {
            $groupedTokens[$group] = [];
        }
        foreach ($tokens as $key => $label) {
            $groupedTokens[$group][$key] = $label;
        }
    }
@endphp
<div class="h-screen flex flex-col overflow-hidden bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-white"
     x-data="workflowDesigner()"
     x-init="initDesigner()">

    <!-- Top Header -->
    <div class="flex items-center justify-between px-5 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm z-20 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('user.workflows.index') }}"
               class="flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت
            </a>
            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-none mb-0.5">طراح گردش‌کار</p>
                    <h1 class="text-sm font-bold text-gray-900 dark:text-white leading-none">{{ $workflow->name }}</h1>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="loadImplantTemplate()"
                    class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-semibold border border-amber-200 dark:border-amber-500/30 flex items-center gap-1.5 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                الگوی ایمپلنت
            </button>
            <button @click="saveGraph()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm shadow-indigo-500/30 flex items-center gap-1.5 transition-all active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                ذخیره
            </button>
        </div>
    </div>

    <!-- Main Workspace: Left Catalog + Canvas (full width) -->
    <div class="flex-1 flex overflow-hidden">

        <!-- Left Sidebar: Node Catalog -->
        <div class="w-60 bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 flex flex-col overflow-y-auto shrink-0">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">افزودن گره</p>
                <div class="space-y-2">
                    <button @click="addNode('START')"
                            class="w-full p-3 flex items-center gap-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/5 dark:hover:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl text-emerald-800 dark:text-emerald-400 text-xs font-semibold transition-all text-right">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-sm">▶</span>
                        شروع (START)
                    </button>
                    <button @click="addNode('ACTION')"
                            class="w-full p-3 flex items-center gap-3 bg-blue-50 hover:bg-blue-100 dark:bg-blue-500/5 dark:hover:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl text-blue-800 dark:text-blue-400 text-xs font-semibold transition-all text-right">
                        <span class="w-7 h-7 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-sm">✓</span>
                        عملیات (ACTION)
                    </button>
                    <button @click="addNode('CONDITION')"
                            class="w-full p-3 flex items-center gap-3 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/5 dark:hover:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-amber-800 dark:text-amber-400 text-xs font-semibold transition-all text-right">
                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-sm">?</span>
                        شرطی (CONDITION)
                    </button>
                    <button @click="addNode('SUB_WORKFLOW')"
                            class="w-full p-3 flex items-center gap-3 bg-purple-50 hover:bg-purple-100 dark:bg-purple-500/5 dark:hover:bg-purple-500/10 border border-purple-200 dark:border-purple-500/20 rounded-xl text-purple-800 dark:text-purple-400 text-xs font-semibold transition-all text-right">
                        <span class="w-7 h-7 rounded-lg bg-purple-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-sm">⚙</span>
                        زیرفرآیند (SUB)
                    </button>
                    <button @click="addNode('END')"
                            class="w-full p-3 flex items-center gap-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/5 dark:hover:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-xl text-rose-800 dark:text-rose-400 text-xs font-semibold transition-all text-right">
                        <span class="w-7 h-7 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-sm">■</span>
                        پایان (END)
                    </button>
                </div>
            </div>

            <!-- Active Connections -->
            <div class="p-4 flex-1">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">اتصالات فعال</p>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    <template x-for="(edge, idx) in edges" :key="idx">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 text-[10px]">
                            <span x-text="`${getNodeName(edge.source_id)} ➔ ${getNodeName(edge.target_id)}`" class="truncate text-gray-600 dark:text-gray-300 font-mono"></span>
                            <button @click="removeEdge(idx)" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 mr-1.5 font-bold shrink-0 transition-colors">✕</button>
                        </div>
                    </template>
                    <template x-if="edges.length === 0">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 text-center py-2">هیچ اتصالی ثبت نشده</p>
                    </template>
                </div>
            </div>

            <!-- Hint -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed">برای ویرایش یک گره، روی آن کلیک کنید. برای اتصال، از پورت پایین گره بکشید.</p>
            </div>
        </div>

        <!-- Center Viewport (full canvas) -->
        <div class="flex-1 relative overflow-hidden bg-gray-100 dark:bg-gray-950 select-none cursor-grab"
             id="canvas-viewport"
             dir="ltr"
             @wheel.prevent="onWheel($event)"
             @mousedown="startPan($event)"
             @mousemove="onMouseMove($event)"
             @mouseup="onMouseUp($event)"
             @mouseleave="onMouseLeave($event)">

            <!-- Infinite Canvas Area -->
            <div class="absolute top-0 left-0 w-[5000px] h-[5000px] origin-top-left pointer-events-none"
                 :style="`transform: translate(${pan.x}px, ${pan.y}px) scale(${zoom});`"
                 id="canvas-container">

                <!-- Grid Background -->
                <div class="absolute -inset-[5000px] bg-[radial-gradient(#d1d5db_1.5px,transparent_1.5px)] dark:bg-[radial-gradient(#374151_1.5px,transparent_1.5px)] [background-size:24px_24px] pointer-events-none z-0 opacity-60"></div>

                <!-- Content wrapper -->
                <div class="absolute inset-0 pointer-events-none z-10">
                    <!-- SVG Defs -->
                    <svg class="absolute w-0 h-0 pointer-events-none">
                        <defs>
                            <marker id="arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" class="fill-gray-400 dark:fill-gray-500" />
                            </marker>
                            <marker id="arrow-selected" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" class="fill-indigo-500" />
                            </marker>
                        </defs>
                    </svg>

                    <!-- Edge Connections -->
                    <div class="absolute inset-0 pointer-events-none z-0">
                        <template x-for="(edge, idx) in edges" :key="idx">
                            <svg class="absolute inset-0 w-full h-full pointer-events-none">
                                <g class="pointer-events-auto cursor-pointer" @click="selectEdge(idx)">
                                    <path :d="getBezierPath(edge)"
                                          stroke-width="2.5"
                                          fill="none"
                                          :class="selectedEdgeIdx === idx ? 'stroke-indigo-500' : 'stroke-gray-300 dark:stroke-gray-600 hover:stroke-indigo-400'"
                                          :marker-end="selectedEdgeIdx === idx ? 'url(#arrow-selected)' : 'url(#arrow)'" />
                                    <text x-show="edge.condition" :x="getEdgeLabelX(edge)" :y="getEdgeLabelY(edge)"
                                          fill="currentColor"
                                          class="text-[10px] font-bold text-gray-500 dark:text-gray-400"
                                          text-anchor="middle"
                                          x-text="edge.condition"></text>
                                </g>
                            </svg>
                        </template>
                    </div>

                    <!-- Temp connecting line -->
                    <div class="absolute inset-0 pointer-events-none z-0" x-show="connectingSource">
                        <svg class="absolute inset-0 w-full h-full pointer-events-none">
                            <line :x1="connectingSource ? connectingSource.x + 112 : 0"
                                  :y1="connectingSource ? connectingSource.y + 100 : 0"
                                  :x2="mousePos.x" :y2="mousePos.y"
                                  stroke="#6366f1" stroke-width="2" stroke-dasharray="5,5" />
                        </svg>
                    </div>

                    <!-- Render Nodes -->
                    <div class="absolute inset-0 pointer-events-none z-10">
                        <template x-for="(node, index) in nodes" :key="node.id">
                            <div class="absolute w-56 rounded-xl border-2 pointer-events-auto select-none shadow-md hover:shadow-lg transition-all duration-150"
                                 :class="[
                                     selectedNodeId === node.id
                                         ? 'border-indigo-500 ring-4 ring-indigo-500/20 bg-white dark:bg-gray-800 shadow-indigo-200/50 dark:shadow-indigo-900/50'
                                         : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
                                 ]"
                                 :style="`left: ${node.x}px; top: ${node.y}px;`"
                                 @mousedown="startDrag($event, node)"
                                 @click.stop="openNodeEditor(node)">

                                <!-- Header -->
                                <div class="px-3.5 py-2.5 rounded-t-xl font-bold text-xs flex items-center justify-between text-white"
                                     :class="getNodeColorClass(node.type)">
                                    <span x-text="node.name" class="truncate"></span>
                                    <span class="opacity-70 uppercase text-[8px] shrink-0 ml-1" x-text="node.type"></span>
                                </div>

                                <!-- Body -->
                                <div class="p-3 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                    <template x-if="node.type === 'ACTION'">
                                        <div>
                                            <template x-if="!node.config.action_type || node.config.action_type === 'TASK'">
                                                <div>
                                                    <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block mb-1">وظایف تعریف‌شده:</span>
                                                    <div class="space-y-0.5 max-h-16 overflow-y-auto">
                                                        <template x-for="(t, tIdx) in (node.config.tasks || [])" :key="t.id">
                                                            <div class="text-[9px] text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                                                <span class="w-1 h-1 rounded-full bg-blue-500 shrink-0"></span>
                                                                <span class="font-semibold truncate max-w-[110px]" x-text="t.title || 'وظیفه ' + (tIdx + 1)"></span>
                                                                <span class="opacity-50 truncate" x-text="getAssigneeLabel(t)"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="node.config.action_type === 'SMS'">
                                                <div>
                                                    <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block mb-1">ارسال پیامک:</span>
                                                    <div class="text-[9px] text-gray-700 dark:text-gray-300 truncate" x-text="node.config.sms_message || 'بدون متن'"></div>
                                                </div>
                                            </template>
                                            <template x-if="node.config.action_type === 'FOLLOWUP'">
                                                <div>
                                                    <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block mb-1">پیگیری:</span>
                                                    <div class="text-[9px] text-gray-700 dark:text-gray-300 truncate" x-text="node.config.followup_title || 'بدون عنوان'"></div>
                                                </div>
                                            </template>
                                            <template x-if="node.config.action_type === 'NOTIFICATION'">
                                                <div>
                                                    <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block mb-1">اعلان سیستم:</span>
                                                    <div class="text-[9px] text-gray-700 dark:text-gray-300 truncate" x-text="node.config.notification_title || 'بدون عنوان'"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="node.type === 'CONDITION'">
                                        <div>
                                            <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block">شرط انتقال:</span>
                                            <span class="text-gray-700 dark:text-gray-300 font-mono text-[9px]" x-text="node.config.condition_expression || 'ثبت نشده'"></span>
                                        </div>
                                    </template>
                                    <template x-if="node.type === 'SUB_WORKFLOW'">
                                        <div>
                                            <span class="font-semibold text-[9px] text-gray-400 dark:text-gray-500 block">زیرفرآیند:</span>
                                            <span class="text-gray-700 dark:text-gray-300 text-[9px]" x-text="getSubWorkflowName(node.config.child_workflow_id)"></span>
                                        </div>
                                    </template>
                                    <template x-if="node.type === 'START'">
                                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-semibold">نقطه ورود فرآیند</span>
                                    </template>
                                    <template x-if="node.type === 'END'">
                                        <span class="text-[9px] text-rose-600 dark:text-rose-400 font-semibold">پایان‌دهنده مسیر</span>
                                    </template>
                                </div>

                                <!-- Edit hint badge -->
                                <div class="absolute top-1.5 left-1.5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    <span class="text-[8px] bg-indigo-600 text-white px-1.5 py-0.5 rounded-full">ویرایش</span>
                                </div>

                                <!-- Top Input Port (Except for START) -->
                                <template x-if="node.type !== 'START'">
                                    <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full border-2 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 flex items-center justify-center hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 cursor-pointer z-20 transition-all"
                                         @mouseup.stop="connectPort(node)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                                    </div>
                                </template>

                                <!-- Bottom Output Port (Except for END) -->
                                <template x-if="node.type !== 'END'">
                                    <div class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full border-2 bg-white dark:bg-gray-900 border-indigo-400 flex items-center justify-center hover:bg-indigo-100 dark:hover:bg-indigo-950/50 cursor-pointer z-20 transition-all"
                                         @mousedown.stop="startConnect(node)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Zoom Controls -->
            <div class="absolute bottom-5 right-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg flex items-center gap-0.5 p-1.5 z-30 pointer-events-auto">
                <button @click="zoomOut()" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition font-bold text-lg">-</button>
                <span x-text="`${Math.round(zoom * 100)}%`" class="text-xs font-bold px-1.5 text-gray-600 dark:text-gray-300 w-12 text-center select-none"></span>
                <button @click="zoomIn()" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition font-bold text-lg">+</button>
                <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                <button @click="resetZoom()" class="px-2 py-1 text-[10px] font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Reset</button>
            </div>

            <!-- Canvas empty state hint -->
            <template x-if="nodes.length === 0">
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-200/80 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">بوم خالی است</p>
                        <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">از منوی چپ گره اضافه کنید یا الگوی آماده بارگذاری کنید</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ==================== NODE EDITOR MODAL ==================== -->
    <div x-show="nodeEditorOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="closeNodeEditor()">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm"
         @click="closeNodeEditor()"></div>

    <!-- Modal Panel -->
    <template x-if="nodeEditorOpen">
    <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @click.stop>

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold"
                     :class="editingNode ? getNodeColorClass(editingNode.type) : 'bg-gray-400'">
                    <template x-if="editingNode && editingNode.type === 'START'"><span>▶</span></template>
                    <template x-if="editingNode && editingNode.type === 'END'"><span>■</span></template>
                    <template x-if="editingNode && editingNode.type === 'ACTION'"><span>✓</span></template>
                    <template x-if="editingNode && editingNode.type === 'CONDITION'"><span>?</span></template>
                    <template x-if="editingNode && editingNode.type === 'SUB_WORKFLOW'"><span>⚙</span></template>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white" x-text="editingNode ? 'تنظیمات گره: ' + editingNode.name : 'تنظیمات گره'"></h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500" x-text="editingNode?.type || ''"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="deleteEditingNode()"
                        class="px-3 py-1.5 text-xs bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg border border-red-100 dark:border-red-500/20 font-semibold transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    حذف گره
                </button>
                <button @click="closeNodeEditor()"
                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6" x-show="editingNode">

            <!-- Common: Node Name -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام گره <span class="text-red-500">*</span></label>
                <input type="text" x-model="editingNode.name"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all outline-none"
                       placeholder="نام گره را وارد کنید...">
            </div>

            <!-- ====== ACTION Settings ====== -->
            <template x-if="editingNode && editingNode.type === 'ACTION'">
                <div class="space-y-4">
                    <!-- Action Type Selector -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع عملیات</label>
                        <select x-model="editingNode.config.action_type"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all outline-none">
                            <option value="TASK">تعریف وظیفه (Task)</option>
                            <option value="SMS">ارسال پیامک (SMS)</option>
                            <option value="FOLLOWUP">ایجاد پیگیری (FollowUp)</option>
                            <option value="NOTIFICATION">ارسال اعلان سیستم (Notification)</option>
                        </select>
                    </div>

                    <!-- ====== TASK ====== -->
                    <template x-if="!editingNode.config.action_type || editingNode.config.action_type === 'TASK'">
                        <div class="space-y-4">
                            <!-- Section header -->
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">✓</span>
                                    لیست وظایف این گام
                                </h3>
                                <button @click="addTask(editingNode)" type="button"
                                        class="px-3 py-1.5 text-xs bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-lg font-semibold border border-indigo-100 dark:border-indigo-500/20 transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    افزودن وظیفه
                                </button>
                            </div>

                            <!-- Task List -->
                            <div class="space-y-4">
                        <template x-for="(task, taskIndex) in (editingNode.config.tasks || [])" :key="task.id">
                            <div x-data="{ isExpanded: true }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <!-- Task header -->
                                <div @click="isExpanded = !isExpanded" class="px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3 cursor-pointer select-none">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0" x-text="taskIndex + 1"></span>
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="task.title || 'وظیفه جدید'"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click.stop="removeTask(editingNode, taskIndex)" type="button"
                                                class="p-1.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="isExpanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>

                                <!-- Task fields -->
                                <div x-show="isExpanded" class="p-4 space-y-4">
                                    <!-- Title -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">عنوان وظیفه <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="task.title"
                                               placeholder="مثلا: تماس با بیمار یا ارزیابی {client_name}" required
                                               class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all outline-none">
                                    </div>

                                    <!-- Priority -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">اولویت</label>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <template x-for="p in [{value:'LOW',label:'کم',color:'text-green-700 bg-green-50 border-green-200 dark:text-green-400 dark:bg-green-500/10 dark:border-green-500/30'},{value:'MEDIUM',label:'معمولی',color:'text-blue-700 bg-blue-50 border-blue-200 dark:text-blue-400 dark:bg-blue-500/10 dark:border-blue-500/30'},{value:'HIGH',label:'زیاد',color:'text-orange-700 bg-orange-50 border-orange-200 dark:text-orange-400 dark:bg-orange-500/10 dark:border-orange-500/30'},{value:'CRITICAL',label:'بحرانی',color:'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-500/10 dark:border-red-500/30'}]" :key="p.value">
                                                <button type="button"
                                                        @click="task.priority = p.value"
                                                        :class="[p.color, task.priority === p.value ? 'ring-2 ring-offset-1 ring-indigo-500 font-bold' : 'font-medium']"
                                                        class="px-2 py-1.5 text-[10px] rounded-lg border transition-all text-center">
                                                    <span x-text="p.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Description -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">توضیحات</label>
                                        <textarea x-model="task.description" rows="2"
                                                  placeholder="شرح وظیفه و دستورالعمل‌ها..."
                                                  class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all outline-none"></textarea>
                                    </div>

                                    <!-- Assignee Target Select -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">مسئول انجام وظیفه</label>
                                        <select x-model="task.assignee_target" @change="if (task.assignee_target === 'ROLE') { task.assignee_mode = 'by_roles'; } else { task.assignee_mode = 'single_user'; }"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                            <option value="CURRENT_USER">کاربر فعلی سیستم</option>
                                            <option value="APPOINTMENT_PROVIDER">پزشک نوبت مربوطه</option>
                                            <option value="SPECIFIC_USER">کاربر خاص سیستم (انتخاب کاربر)</option>
                                            <option value="ROLE">بر اساس نقش عمومی سیستم (انتخاب نقش)</option>
                                            <optgroup label="طرح درمان">
                                                <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                                <option value="TREATMENT_PLAN_CLIENT_ASSIGNEE">بیمار طرح درمان</option>
                                                @if(isset($cureRoles))
                                                    @foreach($cureRoles as $role)
                                                        <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                                    @endforeach
                                                @endif
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- User Selector (searchable) -->
                                    <div x-show="task.assignee_target === 'SPECIFIC_USER'" class="relative"
                                         x-data="{
                                             open: false,
                                             search: '',
                                             dropUp: false,
                                             calculatePosition() {
                                                 this.$nextTick(() => {
                                                     const btn = this.$refs.btn;
                                                     const drop = this.$refs.drop;
                                                     if (!btn || !drop) return;
                                                     const btnRect = btn.getBoundingClientRect();
                                                     const dropHeight = drop.getBoundingClientRect().height;
                                                     const spaceBelow = window.innerHeight - btnRect.bottom;
                                                     const spaceAbove = btnRect.top;
                                                     this.dropUp = (spaceBelow < dropHeight && spaceAbove > spaceBelow);
                                                 });
                                             },
                                             get filtered() {
                                                 const q = this.search.toLowerCase();
                                                 return (window._wfUsers || []).filter(u => !q || u.name.toLowerCase().includes(q));
                                             },
                                             get selectedName() {
                                                 const u = (window._wfUsers || []).find(u => u.id == task.assignee_id);
                                                 return u ? u.name : null;
                                             }
                                         }">
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">کاربر مسئول انجام</label>
                                        <!-- Trigger button -->
                                        <button type="button" x-ref="btn" @click="open = !open; if(open) calculatePosition()"
                                                class="w-full flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-right transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none">
                                            <span :class="selectedName ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"
                                                  x-text="selectedName || 'انتخاب کاربر...'"></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <!-- Dropdown -->
                                        <div x-show="open" x-ref="drop" @click.away="open = false; search = ''"
                                             x-cloak
                                             :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
                                             class="absolute z-50 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0">
                                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                                <div class="relative">
                                                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                    <input type="text" x-model="search" placeholder="جستجوی کاربر..."
                                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 pr-8 text-xs text-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">
                                                </div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto py-1">
                                                <button type="button" @click="task.assignee_id = ''; open = false; search = ''"
                                                        :class="!task.assignee_id ? 'bg-gray-50 dark:bg-gray-800 font-semibold text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                                                        class="w-full text-right px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                    هیچکدام (بدون مسئول)
                                                </button>
                                                <template x-for="u in filtered" :key="u.id">
                                                    <button type="button"
                                                            @click="task.assignee_id = u.id; open = false; search = ''"
                                                            :class="task.assignee_id == u.id ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                                            class="w-full text-right px-3 py-2 text-xs transition-colors flex items-center justify-between gap-2">
                                                        <span x-text="u.name"></span>
                                                        <svg x-show="task.assignee_id == u.id" class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </template>
                                                <template x-if="filtered.length === 0">
                                                    <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-3">کاربری یافت نشد</p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Role Selector (searchable) -->
                                    <div x-show="task.assignee_target === 'ROLE'" class="relative"
                                         x-data="{
                                             open: false,
                                             search: '',
                                             dropUp: false,
                                             calculatePosition() {
                                                 this.$nextTick(() => {
                                                     const btn = this.$refs.btn;
                                                     const drop = this.$refs.drop;
                                                     if (!btn || !drop) return;
                                                     const btnRect = btn.getBoundingClientRect();
                                                     const dropHeight = drop.getBoundingClientRect().height;
                                                     const spaceBelow = window.innerHeight - btnRect.bottom;
                                                     const spaceAbove = btnRect.top;
                                                     this.dropUp = (spaceBelow < dropHeight && spaceAbove > spaceBelow);
                                                 });
                                             },
                                             get filtered() {
                                                 const q = this.search.toLowerCase();
                                                 return (window._wfRoles || []).filter(r => !q || r.name.toLowerCase().includes(q));
                                             },
                                             get selectedName() {
                                                 const r = (window._wfRoles || []).find(r => r.id == task.role_id);
                                                 return r ? r.name : null;
                                             }
                                         }">
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">نقش مسئول انجام</label>
                                        <button type="button" x-ref="btn" @click="open = !open; if(open) calculatePosition()"
                                                class="w-full flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-right transition-all focus:ring-2 focus:ring-indigo-500/20 outline-none">
                                            <span :class="selectedName ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"
                                                  x-text="selectedName || 'انتخاب نقش...'"></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="open" x-ref="drop" @click.away="open = false; search = ''"
                                             x-cloak
                                             :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
                                             class="absolute z-50 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0">
                                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                                <div class="relative">
                                                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                    <input type="text" x-model="search" placeholder="جستجوی نقش..."
                                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 pr-8 text-xs text-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">
                                                </div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto py-1">
                                                <button type="button" @click="task.role_id = ''; open = false; search = ''"
                                                        :class="!task.role_id ? 'bg-gray-50 dark:bg-gray-800 font-semibold text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                                                        class="w-full text-right px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                    بدون نقش مشخص
                                                </button>
                                                <template x-for="r in filtered" :key="r.id">
                                                    <button type="button"
                                                            @click="task.role_id = r.id; open = false; search = ''"
                                                            :class="task.role_id == r.id ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                                            class="w-full text-right px-3 py-2 text-xs transition-colors flex items-center justify-between gap-2">
                                                        <span x-text="r.name"></span>
                                                        <svg x-show="task.role_id == r.id" class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </template>
                                                <template x-if="filtered.length === 0">
                                                    <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-3">نقشی یافت نشد</p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Due Offset & Auto Advance -->
                                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5">سررسید (روز پس از شروع)</label>
                                            <div class="relative">
                                                <input type="number" min="0" x-model.number="task.offset_days"
                                                       class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all outline-none">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none">روز</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col justify-end">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <div class="relative">
                                                    <input type="checkbox" x-model="task.auto_advance" :id="'task_auto_' + task.id" class="sr-only peer">
                                                    <div class="w-10 h-5 rounded-full border-2 transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600"
                                                         @click="task.auto_advance = !task.auto_advance"></div>
                                                    <div class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                                                         :class="task.auto_advance ? '-translate-x-5' : ''"
                                                         @click="task.auto_advance = !task.auto_advance"></div>
                                                </div>
                                                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 leading-tight select-none">انتقال خودکار پس از انجام</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty tasks hint -->
                        <template x-if="(editingNode?.config?.tasks || []).length === 0">
                            <div class="text-center py-6 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-400 dark:text-gray-500">هیچ وظیفه‌ای تعریف نشده</p>
                                <button type="button" @click="addTask(editingNode)"
                                        class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">+ افزودن اولین وظیفه</button>
                            </div>
                        </template>
                            </div>
                        </div>
                    </template>

                    <!-- ====== SMS ====== -->
                    <template x-if="editingNode.config.action_type === 'SMS'">
                        <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">هدف ارسال (گیرنده)</label>
                                    <select x-model="editingNode.config.sms_target"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none">
                                        <option value="APPOINTMENT_CLIENT">بیمار نوبت</option>
                                        <option value="APPOINTMENT_PROVIDER">پزشک نوبت</option>
                                        <option value="STATEMENT_PROVIDER">ارائه‌دهنده صورت وضعیت</option>
                                        <option value="SPECIFIC_USER">کاربر خاص سیستم</option>
                                        <option value="CUSTOM_PHONE">شماره دلخواه</option>
                                        <optgroup label="طرح درمان">
                                            <option value="TREATMENT_PLAN_CLIENT">بیمار طرح درمان</option>
                                            <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                            @if(isset($cureRoles))
                                                @foreach($cureRoles as $role)
                                                    <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                                @endforeach
                                            @endif
                                        </optgroup>
                                    </select>
                                    
                                    <div class="mt-2 space-y-2">
                                        <div x-show="editingNode.config.sms_target === 'CUSTOM_PHONE' || editingNode.config.sms_target === 'CUSTOM'">
                                            <input type="text" x-model="editingNode.config.sms_phone" placeholder="شماره موبایل (مثلاً ...0912)"
                                                   class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                                        </div>
                                        <div x-show="editingNode.config.sms_target === 'SPECIFIC_USER'">
                                            <select x-model="editingNode.config.sms_target_user_id"
                                                    class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                                                <option value="">انتخاب کاربر...</option>
                                                <template x-for="u in users" :key="u.id">
                                                    <option :value="u.id" x-text="u.name" :selected="editingNode.config.sms_target_user_id == u.id"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاخیر ارسال (دقیقه)</label>
                                    <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-900">
                                        <input type="number" x-model.number="editingNode.config.sms_offset_minutes"
                                               class="block w-full border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 text-left focus:ring-0 focus:outline-none text-sm font-semibold" dir="ltr">
                                        <span class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-3 py-2 text-xs font-bold border-r border-gray-200 dark:border-gray-700 flex items-center">دقیقه</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50/50 dark:bg-gray-900/10 border border-gray-200 dark:border-gray-800 rounded-xl p-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد الگوی سامانه پیامک (Pattern Code)</label>
                                    <input type="text" x-model="editingNode.config.sms_pattern_key" placeholder="مثال: 34567 (در صورت پیامک معمولی، خالی بگذارید)"
                                           class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">پارامترهای الگو (به ترتیب {0}, {1}, ...)</label>
                                    <div class="space-y-2">
                                        <template x-for="(param, idx) in (editingNode.config.sms_params || [])" :key="idx">
                                            <div class="flex gap-2 items-center">
                                                <span class="text-xs text-gray-400 w-8 text-center font-bold" x-text="'{' + idx + '}'"></span>
                                                <select x-model="editingNode.config.sms_params[idx]" class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                                                    <option value="">-- انتخاب مقدار --</option>
                                                    <template x-for="group in Object.keys(tokenGroups)" :key="group">
                                                        <optgroup :label="group === 'appointment' ? 'مشخصات نوبت' : (group === 'statement' ? 'صورت وضعیت مالی' : (group === 'treatment_plan' ? 'طرح درمان' : group))">
                                                            <template x-for="tokenKey in Object.keys(tokenGroups[group])" :key="tokenKey">
                                                                <option :value="tokenKey" x-text="tokenGroups[group][tokenKey]" :selected="param === tokenKey"></option>
                                                            </template>
                                                        </optgroup>
                                                    </template>
                                                </select>
                                                <button type="button" @click="editingNode.config.sms_params.splice(idx, 1)" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="if(!editingNode.config.sms_params) editingNode.config.sms_params = []; editingNode.config.sms_params.push('')" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 font-bold mt-1">
                                        + افزودن پارامتر بعدی
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">متن پیام (در صورت عدم استفاده از الگو)</label>
                                <div class="bg-gray-50/80 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5">
                                    <span class="text-[11px] font-bold text-indigo-600/70 dark:text-indigo-400/70 tracking-wide block mb-2">کتابخانه توکن‌های پویا (جهت درج کلیک کنید)</span>
                                    <div class="space-y-3 max-h-36 overflow-y-auto pr-1">
                                        <template x-for="group in Object.keys(tokenGroups)" :key="group">
                                            <div class="space-y-1">
                                                <div class="text-[9px] font-extrabold text-gray-400 dark:text-gray-500 border-b border-gray-200/50 dark:border-gray-700 pb-0.5 mb-1" 
                                                     x-text="group === 'appointment' ? 'اطلاعات نوبت‌دهی بیمار' : (group === 'statement' ? 'اطلاعات صورت وضعیت مالی' : (group === 'treatment_plan' ? 'اطلاعات طرح درمان' : group))">
                                                </div>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <template x-for="tokenKey in Object.keys(tokenGroups[group])" :key="tokenKey">
                                                        <button type="button" @click="insertToken(tokenKey, 'sms')" 
                                                                class="text-[9px] font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-700 transition-all shadow-sm"
                                                                x-text="tokenGroups[group][tokenKey]">
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <textarea id="sms_message_textarea" x-model="editingNode.config.sms_message" rows="3.5"
                                          class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-3 px-4 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                          placeholder="متن پیامک خود را در اینجا بنویسید..."></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                                <label class="text-[11px] font-bold text-gray-600 dark:text-gray-400">انجام خودکار و عبور (بدون نیاز به تایید)</label>
                                <button type="button" @click="editingNode.config.auto_advance = !editingNode.config.auto_advance"
                                        :class="editingNode.config.auto_advance ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                          :class="editingNode.config.auto_advance ? 'translate-x-4' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- ====== FOLLOWUP ====== -->
                    <template x-if="editingNode.config.action_type === 'FOLLOWUP'">
                        <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان پیگیری</label>
                                    <input type="text" x-model="editingNode.config.followup_title"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">منتسب به</label>
                                    <select x-model="editingNode.config.followup_assignee_target"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                        <option value="CURRENT_USER">کاربر فعلی سیستم</option>
                                        <option value="APPOINTMENT_PROVIDER">پزشک نوبت مربوطه</option>
                                        <option value="SPECIFIC_USER">کاربر خاص مشخص شده</option>
                                        <optgroup label="طرح درمان">
                                            <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                            <option value="TREATMENT_PLAN_CLIENT_ASSIGNEE">بیمار طرح درمان</option>
                                            @if(isset($cureRoles))
                                                @foreach($cureRoles as $role)
                                                    <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                                @endforeach
                                            @endif
                                        </optgroup>
                                    </select>
                                    <div x-show="editingNode.config.followup_assignee_target === 'SPECIFIC_USER'" class="mt-2">
                                        <select x-model="editingNode.config.followup_assignee_id"
                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                            <option value="">انتخاب کاربر...</option>
                                            <template x-for="u in users" :key="u.id">
                                                <option :value="u.id" x-text="u.name" :selected="editingNode.config.followup_assignee_id == u.id"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاخیر ایجاد (روز)</label>
                                    <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-900">
                                        <input type="number" min="0" x-model.number="editingNode.config.followup_offset_days"
                                               class="block w-full border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 text-left focus:ring-0 focus:outline-none text-sm font-semibold" dir="ltr">
                                        <span class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-3 py-2 text-xs font-bold border-r border-gray-200 dark:border-gray-700 flex items-center">روز</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">اولویت انجام</label>
                                    <select x-model="editingNode.config.followup_priority"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                        <option value="LOW">کم</option>
                                        <option value="MEDIUM">معمولی</option>
                                        <option value="HIGH">زیاد</option>
                                        <option value="CRITICAL">بحرانی</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت اولیه</label>
                                    <select x-model="editingNode.config.followup_status"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                        <option value="TODO">در صف انجام</option>
                                        <option value="IN_PROGRESS">در حال انجام</option>
                                        <option value="DONE">انجام شده</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات</label>
                                <textarea x-model="editingNode.config.followup_description" rows="3"
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2.5 text-sm resize-none focus:ring-2 focus:ring-indigo-500/30 outline-none"></textarea>
                            </div>
                        </div>
                    </template>

                    <!-- ====== NOTIFICATION ====== -->
                    <template x-if="editingNode.config.action_type === 'NOTIFICATION'">
                        <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گیرنده اعلان</label>
                                    <select x-model="editingNode.config.notification_target"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                        <option value="APPOINTMENT_CLIENT">بیمار نوبت</option>
                                        <option value="APPOINTMENT_PROVIDER">پزشک نوبت</option>
                                        <option value="SPECIFIC_USER">کاربر خاص سیستم</option>
                                        <optgroup label="طرح درمان">
                                            <option value="TREATMENT_PLAN_CLIENT">بیمار طرح درمان</option>
                                            <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                            @if(isset($cureRoles))
                                                @foreach($cureRoles as $role)
                                                    <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                                @endforeach
                                            @endif
                                        </optgroup>
                                    </select>
                                    <div x-show="editingNode.config.notification_target === 'SPECIFIC_USER'" class="mt-2">
                                        <select x-model="editingNode.config.notification_target_user_id"
                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                                            <option value="">انتخاب کاربر...</option>
                                            <template x-for="u in users" :key="u.id">
                                                <option :value="u.id" x-text="u.name" :selected="editingNode.config.notification_target_user_id == u.id"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاخیر ارسال (دقیقه)</label>
                                    <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-900">
                                        <input type="number" x-model.number="editingNode.config.notification_offset_minutes"
                                               class="block w-full border-0 bg-transparent text-gray-900 dark:text-white py-2 px-3 text-left focus:ring-0 focus:outline-none text-sm font-semibold" dir="ltr">
                                        <span class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-3 py-2 text-xs font-bold border-r border-gray-200 dark:border-gray-700 flex items-center">دقیقه</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان اعلان</label>
                                <input type="text" x-model="editingNode.config.notification_title"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">متن اعلان</label>
                                <div class="bg-gray-50/80 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5">
                                    <span class="text-[11px] font-bold text-indigo-650/70 dark:text-indigo-400/70 tracking-wide block mb-2">کتابخانه توکن‌های پویا (جهت درج کلیک کنید)</span>
                                    <div class="space-y-3 max-h-36 overflow-y-auto pr-1">
                                        <template x-for="group in Object.keys(tokenGroups)" :key="group">
                                            <div class="space-y-1">
                                                <div class="text-[9px] font-extrabold text-gray-400 dark:text-gray-500 border-b border-gray-200/50 dark:border-gray-700 pb-0.5 mb-1" 
                                                     x-text="group === 'appointment' ? 'اطلاعات نوبت‌دهی بیمار' : (group === 'statement' ? 'اطلاعات صورت وضعیت مالی' : (group === 'treatment_plan' ? 'اطلاعات طرح درمان' : group))">
                                                </div>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <template x-for="tokenKey in Object.keys(tokenGroups[group])" :key="tokenKey">
                                                        <button type="button" @click="insertToken(tokenKey, 'notification')" 
                                                                class="text-[9px] font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-700 transition-all shadow-sm"
                                                                x-text="tokenGroups[group][tokenKey]">
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <textarea id="notification_message_textarea" x-model="editingNode.config.notification_message" rows="3"
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-2.5 text-sm resize-none focus:ring-2 focus:ring-indigo-500/30 outline-none"></textarea>
                            </div>
                        </div>
                    </template>

                </div>
            </template>

            <!-- ====== CONDITION Settings ====== -->
            <template x-if="editingNode && editingNode.type === 'CONDITION'">
                <div class="rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/5 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-base font-bold">?</div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300">گره شرطی دوگانه (بله / خیر)</h4>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1 leading-relaxed">
                                در زمان اجرای این گره، وضعیت مسیر توسط ادمین مشخص می‌شود. لطفاً در اتصالات خروجی، مسیرهای «بله» و «خیر» را تنظیم کنید.
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ====== SUB_WORKFLOW Settings ====== -->
            <template x-if="editingNode && editingNode.type === 'SUB_WORKFLOW'">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">گردش‌کار فرزند (زیرفرآیند)</label>
                    <div class="relative"
                         x-data="{
                             open: false,
                             search: '',
                             dropUp: false,
                             calculatePosition() {
                                 this.$nextTick(() => {
                                     const btn = this.$refs.btn;
                                     const drop = this.$refs.drop;
                                     if (!btn || !drop) return;
                                     const btnRect = btn.getBoundingClientRect();
                                     const dropHeight = drop.getBoundingClientRect().height;
                                     const spaceBelow = window.innerHeight - btnRect.bottom;
                                     const spaceAbove = btnRect.top;
                                     this.dropUp = (spaceBelow < dropHeight && spaceAbove > spaceBelow);
                                 });
                             },
                             get filtered() {
                                 const q = this.search.toLowerCase();
                                 return (window._wfSubWorkflows || []).filter(s => !q || s.name.toLowerCase().includes(q));
                             },
                             get selectedName() {
                                 const s = (window._wfSubWorkflows || []).find(s => s.id == editingNode.config.child_workflow_id);
                                 return s ? s.name : null;
                             }
                         }">
                        <button type="button" x-ref="btn" @click="open = !open; if(open) calculatePosition()"
                                class="w-full flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-right transition-all focus:ring-2 focus:ring-indigo-500/20 outline-none">
                            <span :class="selectedName ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"
                                  x-text="selectedName || 'انتخاب گردش‌کار فرزند...'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <!-- Dropdown -->
                        <div x-show="open" x-ref="drop"
                             x-cloak
                             :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
                             class="absolute z-50 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 shadow-xl overflow-hidden"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                <input type="text" x-model="search" placeholder="جستجو..."
                                       class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">
                            </div>
                            <div class="max-h-48 overflow-y-auto py-1">
                                <button type="button" @click="editingNode.config.child_workflow_id = ''; open = false"
                                        class="w-full text-right px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    هیچکدام
                                </button>
                                <template x-for="s in filtered" :key="s.id">
                                    <button type="button"
                                            @click="editingNode.config.child_workflow_id = s.id; open = false"
                                            :class="editingNode.config.child_workflow_id == s.id ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                            class="w-full text-right px-3 py-2 text-xs transition-colors flex items-center justify-between gap-2">
                                        <span x-text="s.name"></span>
                                        <svg x-show="editingNode.config.child_workflow_id == s.id" class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                                <template x-if="filtered.length === 0">
                                    <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-3">موردی یافت نشد</p>
                                </template>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500">گردش‌کاری که پس از رسیدن به این گره، به‌صورت زیرفرآیند اجرا می‌شود.</p>
                </div>
            </template>

            <!-- START/END info -->
            <template x-if="editingNode && (editingNode.type === 'START' || editingNode.type === 'END')">
                <div class="rounded-xl border p-4"
                     :class="editingNode.type === 'START' ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/5' : 'border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/5'">
                    <p class="text-sm font-semibold" :class="editingNode.type === 'START' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'"
                       x-text="editingNode.type === 'START' ? 'این گره نقطه ورود فرآیند است.' : 'این گره پایان‌دهنده مسیر است.'"></p>
                    <p class="text-xs mt-1" :class="editingNode.type === 'START' ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500'">
                        هر گردش‌کار باید دقیقاً یک گره شروع و حداقل یک گره پایان داشته باشد.
                    </p>
                </div>
            </template>

        </div>

        <!-- Modal Footer -->
        <div class="shrink-0 px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
            <p class="text-[10px] text-gray-400 dark:text-gray-500">تغییرات بلادرنگ اعمال می‌شود. برای ذخیره دائمی «ذخیره» را بزنید.</p>
            <button @click="closeNodeEditor()"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-500/30 transition-all active:scale-95">
                بستن
            </button>
        </div>
    </div>
    </template>
    </div>

    <!-- ==================== EDGE EDITOR MODAL ==================== -->
    <div x-show="edgeEditorOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="edgeEditorOpen = false; selectedEdgeIdx = null">
    <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm" @click="edgeEditorOpen = false; selectedEdgeIdx = null"></div>
    <template x-if="edgeEditorOpen">
    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop>
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات اتصال</h2>
            </div>
            <div class="flex items-center gap-2">
                <button @click="selectedEdgeIdx !== null && removeEdge(selectedEdgeIdx)"
                        class="px-3 py-1.5 text-xs bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg border border-red-100 dark:border-red-500/20 font-semibold transition-all">
                    حذف اتصال
                </button>
                <button @click="edgeEditorOpen = false; selectedEdgeIdx = null"
                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="p-5 space-y-4" x-show="selectedEdgeIdx !== null && edges[selectedEdgeIdx]">
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 font-mono"
                     x-text="selectedEdgeIdx !== null && edges[selectedEdgeIdx] ? getNodeName(edges[selectedEdgeIdx].source_id) + ' ➔ ' + getNodeName(edges[selectedEdgeIdx].target_id) : ''"></div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">شرط فعالسازی مسیر</label>
                <template x-if="selectedEdgeIdx !== null && edges[selectedEdgeIdx] && nodes.find(n => n.id === edges[selectedEdgeIdx].source_id)?.type === 'CONDITION'">
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="opt in [{value:'',label:'پیش‌فرض'},{value:'بله',label:'بله ✓'},{value:'خیر',label:'خیر ✗'}]" :key="opt.value">
                            <button type="button"
                                    @click="edges[selectedEdgeIdx].condition = opt.value"
                                    :class="edges[selectedEdgeIdx].condition === opt.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                                    class="py-2 text-xs font-semibold rounded-lg border transition-all"
                                    x-text="opt.label">
                            </button>
                        </template>
                    </div>
                </template>
                <template x-if="selectedEdgeIdx !== null && edges[selectedEdgeIdx] && nodes.find(n => n.id === edges[selectedEdgeIdx].source_id)?.type !== 'CONDITION'">
                    <input type="text"
                           x-model="edges[selectedEdgeIdx].condition"
                           placeholder="خالی = بدون شرط / true / false"
                           class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                </template>
                <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500">اگر شرط خالی باشد، انتقال خودکار و بدون قید است.</p>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end bg-gray-50/50 dark:bg-gray-900/50">
            <button @click="edgeEditorOpen = false; selectedEdgeIdx = null"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all active:scale-95">بستن</button>
        </div>
    </div>
    </template>
    </div>
</div>

<script>
function workflowDesigner() {
    return {
        nodes: [],
        edges: [],

        selectedNodeId: null,
        selectedNode: null,
        selectedEdgeIdx: null,

        // Modal states
        nodeEditorOpen: false,
        edgeEditorOpen: false,
        editingNode: null,

        draggingNode: null,
        dragOffset: { x: 0, y: 0 },
        hasDragged: false,

        connectingSource: null,
        mousePos: { x: 0, y: 0 },

        zoom: 1.0,
        pan: { x: 0, y: 0 },
        isPanning: false,
        panStart: { x: 0, y: 0 },

        roles: @json($roles),
        subWorkflows: @json($subWorkflows),
        users: @json($users),
        tokenGroups: @json($groupedTokens),

        initDesigner() {
            window._wfUsers = this.users;
            window._wfRoles = this.roles;
            window._wfSubWorkflows = this.subWorkflows;
            const backendNodes = @json($workflow->nodes);
            const backendEdges = @json($workflow->edges);

            this.nodes = backendNodes.map(node => {
                const config = node.config || {};
                let tasks = (config.tasks || []).map(t => {
                    return {
                        id: t.id || 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        title: t.title || '',
                        description: t.description || '',
                        priority: t.priority || 'MEDIUM',
                        assignee_mode: t.assignee_mode || (t.role_id ? 'by_roles' : 'single_user'),
                        assignee_target: t.assignee_target || (t.assignee_mode === 'by_roles' ? 'ROLE' : (t.assignee_id ? 'SPECIFIC_USER' : 'CURRENT_USER')),
                        role_id: t.role_id || '',
                        assignee_id: t.assignee_id || '',
                        offset_days: parseInt(t.offset_days) || 0,
                        auto_advance: t.hasOwnProperty('auto_advance') ? !!t.auto_advance : true
                    };
                });
                if (node.type === 'ACTION' && tasks.length === 0) {
                    tasks = [{
                        id: 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        title: config.title || node.name || '',
                        description: config.description || '',
                        priority: config.priority || 'MEDIUM',
                        assignee_mode: config.assignee_mode || (config.role_id ? 'by_roles' : 'single_user'),
                        assignee_target: config.assignee_target || (config.role_id ? 'ROLE' : (config.assignee_id ? 'SPECIFIC_USER' : 'CURRENT_USER')),
                        role_id: config.role_id || '',
                        assignee_id: config.assignee_id || '',
                        offset_days: parseInt(config.offset_days) || 0,
                        auto_advance: config.hasOwnProperty('auto_advance') ? !!config.auto_advance : true
                    }];
                }
                return {
                    id: node.id,
                    name: node.name,
                    type: node.type,
                    x: parseFloat(config.x) || 300,
                    y: parseFloat(config.y) || 100,
                    config: {
                        role_id: config.role_id || '',
                        title: config.title || '',
                        description: config.description || '',
                        offset_days: parseInt(config.offset_days) || 0,
                        condition_expression: config.condition_expression || '',
                        child_workflow_id: config.child_workflow_id || '',
                        auto_advance: config.hasOwnProperty('auto_advance') ? !!config.auto_advance : true,
                        action_type: config.action_type || (node.type === 'ACTION' ? 'TASK' : ''),
                        
                        // SMS config:
                        sms_target: config.sms_target || 'APPOINTMENT_CLIENT',
                        sms_target_user_id: config.sms_target_user_id || '',
                        sms_phone: config.sms_phone || '',
                        sms_offset_minutes: parseInt(config.sms_offset_minutes) || 0,
                        sms_pattern_key: config.sms_pattern_key || '',
                        sms_params: config.sms_params || [''],
                        sms_message: config.sms_message || '',
                        
                        // Followup config:
                        followup_title: config.followup_title || '',
                        followup_offset_days: parseInt(config.followup_offset_days) || 0,
                        followup_description: config.followup_description || '',
                        followup_priority: config.followup_priority || 'HIGH',
                        followup_assignee_target: config.followup_assignee_target || 'CURRENT_USER',
                        followup_assignee_id: config.followup_assignee_id || '',
                        followup_status: config.followup_status || 'TODO',
                        
                        // Notification config:
                        notification_target: config.notification_target || 'APPOINTMENT_CLIENT',
                        notification_target_user_id: config.notification_target_user_id || '',
                        notification_title: config.notification_title || '',
                        notification_message: config.notification_message || '',
                        notification_offset_minutes: parseInt(config.notification_offset_minutes) || 0,
                        
                        tasks: tasks
                    }
                };
            });

            this.edges = backendEdges.map(edge => ({
                source_id: edge.source_node_id,
                target_id: edge.target_node_id,
                condition: edge.condition || ''
            }));

            this.$nextTick(() => {
                if (this.nodes.length > 0) {
                    let minX = Infinity, maxX = -Infinity;
                    let minY = Infinity, maxY = -Infinity;
                    this.nodes.forEach(n => {
                        if (n.x < minX) minX = n.x;
                        if (n.x > maxX) maxX = n.x;
                        if (n.y < minY) minY = n.y;
                        if (n.y > maxY) maxY = n.y;
                    });
                    const centerX = (minX + maxX) / 2;
                    const viewport = document.getElementById('canvas-viewport');
                    if (viewport) {
                        const rect = viewport.getBoundingClientRect();
                        this.pan.x = (rect.width / 2) - (centerX + 112);
                        this.pan.y = 50;
                    }
                }
            });
        },

        openNodeEditor(node) {
            // Don't open editor if user was dragging
            if (this.hasDragged) {
                this.hasDragged = false;
                return;
            }
            this.selectedEdgeIdx = null;
            this.edgeEditorOpen = false;
            this.selectedNodeId = node.id;
            this.selectedNode = node;
            this.editingNode = node;
            this.nodeEditorOpen = true;
        },

        closeNodeEditor() {
            this.nodeEditorOpen = false;
            // Keep selectedNodeId for highlighting
        },

        insertToken(token, field) {
            let textarea = null;
            if (field === 'sms') {
                textarea = document.getElementById('sms_message_textarea');
            } else if (field === 'notification') {
                textarea = document.getElementById('notification_message_textarea');
            }
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);

            const insertedText = '{' + token + '}';
            const newValue = before + insertedText + after;
            
            if (field === 'sms') {
                this.editingNode.config.sms_message = newValue;
            } else if (field === 'notification') {
                this.editingNode.config.notification_message = newValue;
            }

            this.$nextTick(() => {
                textarea.value = newValue;
                textarea.selectionStart = textarea.selectionEnd = start + insertedText.length;
                textarea.focus();
            });
        },

        deleteEditingNode() {
            if (!this.editingNode) return;
            if (!confirm('آیا از حذف این گره و تمام اتصالات آن مطمئن هستید؟')) return;
            this.deleteNode(this.editingNode.id);
            this.nodeEditorOpen = false;
            this.editingNode = null;
        },

        addTask(node) {
            if (!node.config.tasks) node.config.tasks = [];
            node.config.tasks.push({
                id: 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                title: '',
                description: '',
                priority: 'MEDIUM',
                assignee_mode: 'single_user',
                assignee_target: 'CURRENT_USER',
                role_id: '',
                assignee_id: '',
                offset_days: 0,
                auto_advance: true
            });
        },

        removeTask(node, index) {
            if (node.config.tasks && node.config.tasks.length > 1) {
                node.config.tasks.splice(index, 1);
            } else {
                alert('گروه عملیات باید حداقل دارای یک وظیفه باشد.');
            }
        },

        getUserName(userId) {
            if (!userId) return 'ثبت نشده';
            const user = this.users.find(u => u.id == userId);
            return user ? user.name : 'نامعلوم';
        },

        loadImplantTemplate() {
            if (this.nodes.length > 0 && !confirm('آیا مطمئن هستید که می‌خواهید الگوی روند درمان ایمپلنت را بارگذاری کنید؟ تغییرات فعلی بوم پاک خواهند شد.')) {
                return;
            }

            this.nodes = [
                { id: 'node_start', name: 'شروع: ویزیت + طرح درمان', type: 'START', x: 300, y: 50, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [] } },
                { id: 'node_needs_other', name: 'نیاز به سایر درمان‌ها؟', type: 'CONDITION', x: 300, y: 180, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: 'needs_other_treatments=true', child_workflow_id: '', auto_advance: true, tasks: [] } },
                { id: 'node_refer_clinic', name: 'ارجاع به کلینیک', type: 'ACTION', x: 580, y: 180, config: { role_id: '', title: 'ارجاع بیمار به کلینیک', description: 'بیمار برای انجام درمان‌های پیش‌نیاز ارجاع داده شده.', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_1', title: 'ارجاع بیمار به کلینیک', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_scan', name: 'تهیه اسکن + سی‌تی‌اسکن', type: 'ACTION', x: 300, y: 310, config: { role_id: '', title: 'تهیه اسکن داخل دهانی و CBCT', description: 'اسکن داخل دهانی و رادیوگرافی CBCT تهیه شود.', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_2', title: 'تهیه اسکن داخل دهانی و CBCT', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_bone_graft', name: 'پیوند استخوان وسیع؟', type: 'CONDITION', x: 300, y: 440, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: 'needs_bone_graft=true', child_workflow_id: '', auto_advance: true, tasks: [] } },
                { id: 'node_refer_bone', name: 'ارجاع جهت پیوند استخوان', type: 'ACTION', x: 580, y: 440, config: { role_id: '', title: 'ارجاع جهت پیوند استخوان', description: 'ارجاع به جراح فک و صورت.', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_3', title: 'ارجاع بیمار جهت پیوند استخوان', description: '', priority: 'HIGH', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_implant', name: 'نوبت کاشت ایمپلنت', type: 'ACTION', x: 300, y: 570, config: { role_id: '', title: 'کاشت ایمپلنت', description: 'نوبت‌دهی و انجام کاشت پایه ایمپلنت.', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_4', title: 'کاشت ایمپلنت', description: '', priority: 'HIGH', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_healing', name: 'هیلینگ (Healing)', type: 'ACTION', x: 300, y: 700, config: { role_id: '', title: 'دوره بهبود هیلینگ', description: 'بستن پیچ هیلینگ و انتظار.', offset_days: 90, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_5', title: 'دوره بهبود هیلینگ', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 90, auto_advance: true }] } },
                { id: 'node_healing_status', name: 'وضعیت هیلینگ؟', type: 'CONDITION', x: 300, y: 830, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: 'healing_status=closed', child_workflow_id: '', auto_advance: true, tasks: [] } },
                { id: 'node_close_healing', name: 'نوبت بستن هیلینگ', type: 'ACTION', x: 580, y: 830, config: { role_id: '', title: 'نوبت بستن مجدد هیلینگ', description: '', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_6', title: 'نوبت بستن مجدد هیلینگ', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_gum_graft', name: 'نیاز به پیوند لثه؟', type: 'CONDITION', x: 300, y: 960, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: 'needs_gum_graft=true', child_workflow_id: '', auto_advance: true, tasks: [] } },
                { id: 'node_refer_gum', name: 'ارجاع به متخصص لثه', type: 'ACTION', x: 580, y: 960, config: { role_id: '', title: 'ارجاع جهت پیوند لثه', description: '', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_7', title: 'ارجاع به پریودنتیست', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_crown', name: 'روکش یا اوردنچر', type: 'ACTION', x: 300, y: 1090, config: { role_id: '', title: 'ساخت و نصب پروتز', description: 'قالب‌گیری و ساخت روکش نهایی.', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [{ id: 'task_' + Date.now() + '_8', title: 'ساخت و نصب پروتز (روکش/اوردنچر)', description: '', priority: 'MEDIUM', assignee_mode: 'single_user', role_id: '', assignee_id: '', offset_days: 0, auto_advance: true }] } },
                { id: 'node_end', name: 'پایان فرآیند', type: 'END', x: 300, y: 1220, config: { role_id: '', title: '', description: '', offset_days: 0, condition_expression: '', child_workflow_id: '', auto_advance: true, tasks: [] } }
            ];

            this.edges = [
                { source_id: 'node_start', target_id: 'node_needs_other', condition: '' },
                { source_id: 'node_needs_other', target_id: 'node_refer_clinic', condition: 'بله' },
                { source_id: 'node_needs_other', target_id: 'node_scan', condition: 'خیر' },
                { source_id: 'node_refer_clinic', target_id: 'node_scan', condition: '' },
                { source_id: 'node_scan', target_id: 'node_bone_graft', condition: '' },
                { source_id: 'node_bone_graft', target_id: 'node_refer_bone', condition: 'بله' },
                { source_id: 'node_bone_graft', target_id: 'node_implant', condition: 'خیر' },
                { source_id: 'node_refer_bone', target_id: 'node_implant', condition: '' },
                { source_id: 'node_implant', target_id: 'node_healing', condition: '' },
                { source_id: 'node_healing', target_id: 'node_healing_status', condition: '' },
                { source_id: 'node_healing_status', target_id: 'node_gum_graft', condition: 'بسته شد' },
                { source_id: 'node_healing_status', target_id: 'node_close_healing', condition: 'بسته نشد' },
                { source_id: 'node_close_healing', target_id: 'node_gum_graft', condition: '' },
                { source_id: 'node_gum_graft', target_id: 'node_refer_gum', condition: 'بله' },
                { source_id: 'node_gum_graft', target_id: 'node_crown', condition: 'خیر' },
                { source_id: 'node_refer_gum', target_id: 'node_crown', condition: '' },
                { source_id: 'node_crown', target_id: 'node_end', condition: '' }
            ];

            this.selectedNodeId = null;
            this.selectedNode = null;
            this.editingNode = null;
            this.nodeEditorOpen = false;
            this.selectedEdgeIdx = null;
            this.edgeEditorOpen = false;
            this.zoom = 1.0;
            this.pan = { x: 0, y: 0 };

            this.$nextTick(() => {
                const viewport = document.getElementById('canvas-viewport');
                if (viewport) {
                    const rect = viewport.getBoundingClientRect();
                    this.pan.x = (rect.width / 2) - 440;
                    this.pan.y = 40;
                }
            });
        },

        addNode(type) {
            const count = this.nodes.filter(n => n.type === type).length + 1;
            const tempId = 'temp_' + Date.now();
            let name = '';
            switch (type) {
                case 'START': name = 'شروع فرآیند'; break;
                case 'END': name = 'پایان فرآیند'; break;
                case 'ACTION': name = 'اقدام ' + count; break;
                case 'CONDITION': name = 'شرط ' + count; break;
                case 'SUB_WORKFLOW': name = 'زیرفرآیند ' + count; break;
            }

            const newNode = {
                id: tempId,
                name: name,
                type: type,
                x: 200 + (this.nodes.length * 40) % 300,
                y: 200 + (this.nodes.length * 40) % 300,
                config: {
                    role_id: '',
                    title: '',
                    description: '',
                    offset_days: 0,
                    condition_expression: '',
                    child_workflow_id: '',
                    auto_advance: true,
                    action_type: type === 'ACTION' ? 'TASK' : '',
                    followup_title: '',
                    followup_description: '',
                    followup_offset_days: 0,
                    sms_message: '',
                    notification_title: '',
                    notification_message: '',
                    tasks: type === 'ACTION' ? [{
                        id: 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        title: name,
                        description: '',
                        priority: 'MEDIUM',
                        assignee_mode: 'single_user',
                        role_id: '',
                        assignee_id: '',
                        offset_days: 0,
                        auto_advance: true
                    }] : []
                }
            };

            this.nodes.push(newNode);

            // Auto-open editor for new nodes
            this.$nextTick(() => {
                this.openNodeEditor(newNode);
            });
        },

        deleteNode(nodeId) {
            this.nodes = this.nodes.filter(n => n.id !== nodeId);
            this.edges = this.edges.filter(e => e.source_id !== nodeId && e.target_id !== nodeId);
            this.selectedNodeId = null;
            this.selectedNode = null;
        },

        selectNode(node) {
            this.selectedEdgeIdx = null;
            this.edgeEditorOpen = false;
            this.selectedNodeId = node.id;
            this.selectedNode = node;
        },

        selectEdge(idx) {
            this.selectedNodeId = null;
            this.selectedNode = null;
            this.nodeEditorOpen = false;
            this.selectedEdgeIdx = idx;
            this.edgeEditorOpen = true;
        },

        startDrag(e, node) {
            if (e.target.closest('.cursor-pointer')) return;
            this.draggingNode = node;
            this.hasDragged = false;
            const rect = document.getElementById('canvas-viewport').getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            const canvasMouseX = (mouseX - this.pan.x) / this.zoom;
            const canvasMouseY = (mouseY - this.pan.y) / this.zoom;
            this.dragOffset.x = canvasMouseX - node.x;
            this.dragOffset.y = canvasMouseY - node.y;
        },

        onMouseMove(e) {
            const rect = document.getElementById('canvas-viewport').getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            const canvasMouseX = (mouseX - this.pan.x) / this.zoom;
            const canvasMouseY = (mouseY - this.pan.y) / this.zoom;
            this.mousePos.x = canvasMouseX;
            this.mousePos.y = canvasMouseY;

            if (this.draggingNode) {
                this.draggingNode.x = canvasMouseX - this.dragOffset.x;
                this.draggingNode.y = canvasMouseY - this.dragOffset.y;
                this.hasDragged = true;
            } else if (this.isPanning) {
                this.pan.x = e.clientX - this.panStart.x;
                this.pan.y = e.clientY - this.panStart.y;
            }
        },

        onMouseUp(e) {
            this.draggingNode = null;
            if (this.isPanning) {
                this.isPanning = false;
                document.getElementById('canvas-viewport').classList.replace('cursor-grabbing', 'cursor-grab');
            }
        },

        onMouseLeave(e) {
            this.draggingNode = null;
            if (this.isPanning) {
                this.isPanning = false;
                document.getElementById('canvas-viewport').classList.replace('cursor-grabbing', 'cursor-grab');
            }
        },

        startPan(e) {
            if (e.target.closest('.pointer-events-auto')) return;
            this.isPanning = true;
            this.panStart.x = e.clientX - this.pan.x;
            this.panStart.y = e.clientY - this.pan.y;
            document.getElementById('canvas-viewport').classList.replace('cursor-grab', 'cursor-grabbing');
        },

        onWheel(e) {
            const rect = document.getElementById('canvas-viewport').getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            const zoomIntensity = 0.08;
            const delta = e.deltaY < 0 ? 1 : -1;
            const nextZoom = Math.min(Math.max(this.zoom + delta * zoomIntensity, 0.25), 2.5);
            this.pan.x = mouseX - (mouseX - this.pan.x) * (nextZoom / this.zoom);
            this.pan.y = mouseY - (mouseY - this.pan.y) * (nextZoom / this.zoom);
            this.zoom = nextZoom;
        },

        zoomIn() {
            const nextZoom = Math.min(this.zoom + 0.15, 2.5);
            this.pan.x = 400 - (400 - this.pan.x) * (nextZoom / this.zoom);
            this.pan.y = 300 - (300 - this.pan.y) * (nextZoom / this.zoom);
            this.zoom = nextZoom;
        },

        zoomOut() {
            const nextZoom = Math.max(this.zoom - 0.15, 0.25);
            this.pan.x = 400 - (400 - this.pan.x) * (nextZoom / this.zoom);
            this.pan.y = 300 - (300 - this.pan.y) * (nextZoom / this.zoom);
            this.zoom = nextZoom;
        },

        resetZoom() {
            this.zoom = 1.0;
            this.pan = { x: 0, y: 0 };
        },

        startConnect(node) {
            this.connectingSource = node;
        },

        connectPort(targetNode) {
            if (this.connectingSource && this.connectingSource.id !== targetNode.id) {
                const exists = this.edges.some(e => e.source_id === this.connectingSource.id && e.target_id === targetNode.id);
                if (!exists) {
                    this.edges.push({ source_id: this.connectingSource.id, target_id: targetNode.id, condition: '' });
                }
            }
            this.connectingSource = null;
        },

        removeEdge(idx) {
            this.edges.splice(idx, 1);
            this.selectedEdgeIdx = null;
            this.edgeEditorOpen = false;
        },

        getNodeName(id) {
            const node = this.nodes.find(n => n.id === id);
            return node ? node.name : 'نامعلوم';
        },

        getRoleName(roleId) {
            if (!roleId) return 'ثبت نشده';
            const role = this.roles.find(r => r.id == roleId);
            return role ? role.name : 'نامعلوم';
        },

        getAssigneeLabel(t) {
            const target = t.assignee_target || 'CURRENT_USER';
            if (target === 'SPECIFIC_USER') {
                return this.getUserName(t.assignee_id);
            }
            if (target === 'ROLE') {
                return 'نقش: ' + this.getRoleName(t.role_id);
            }
            if (target === 'CURRENT_USER') {
                return 'کاربر فعلی';
            }
            if (target === 'APPOINTMENT_PROVIDER') {
                return 'پزشک نوبت';
            }
            if (target === 'TREATMENT_PLAN_CREATOR') {
                return 'ثبت‌کننده طرح';
            }
            if (target === 'TREATMENT_PLAN_CLIENT_ASSIGNEE') {
                return 'بیمار طرح';
            }
            if (target.startsWith('TREATMENT_PLAN_ROLE_')) {
                const roleId = target.replace('TREATMENT_PLAN_ROLE_', '');
                return 'طرح: ' + this.getRoleName(roleId);
            }
            return 'نامعلوم';
        },

        getSubWorkflowName(subId) {
            if (!subId) return 'ثبت نشده';
            const sub = this.subWorkflows.find(s => s.id == subId);
            return sub ? sub.name : 'نامعلوم';
        },

        getNodeColorClass(type) {
            switch (type) {
                case 'START': return 'bg-emerald-500';
                case 'END': return 'bg-rose-500';
                case 'ACTION': return 'bg-blue-500';
                case 'CONDITION': return 'bg-amber-500';
                case 'SUB_WORKFLOW': return 'bg-purple-500';
                default: return 'bg-gray-500';
            }
        },

        getBezierPath(edge) {
            const source = this.nodes.find(n => n.id === edge.source_id);
            const target = this.nodes.find(n => n.id === edge.target_id);
            if (!source || !target) return '';
            const x1 = source.x + 112;
            const y1 = source.y + 100;
            const x2 = target.x + 112;
            const y2 = target.y;
            const controlOffset = Math.abs(y1 - y2) * 0.5;
            return `M ${x1} ${y1} C ${x1} ${y1 + controlOffset}, ${x2} ${y2 - controlOffset}, ${x2} ${y2}`;
        },

        getEdgeLabelX(edge) {
            const source = this.nodes.find(n => n.id === edge.source_id);
            const target = this.nodes.find(n => n.id === edge.target_id);
            if (!source || !target) return 0;
            return (source.x + target.x + 224) / 2;
        },

        getEdgeLabelY(edge) {
            const source = this.nodes.find(n => n.id === edge.source_id);
            const target = this.nodes.find(n => n.id === edge.target_id);
            if (!source || !target) return 0;
            return ((source.y + 100) + target.y) / 2 - 5;
        },

        saveGraph() {
            const payload = {
                nodes: this.nodes.map(n => ({
                    id: n.id,
                    name: n.name,
                    type: n.type,
                    x: n.x,
                    y: n.y,
                    config: n.config
                })),
                edges: this.edges
            };

            axios.post('{{ route("user.workflows.save-graph", $workflow) }}', payload)
                .then(response => {
                    if (response.data.success) {
                        // Show a nicer toast-style notification instead of alert
                        const toast = document.createElement('div');
                        toast.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[100] px-5 py-3 bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-xl flex items-center gap-2 transition-all';
                        toast.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' + response.data.message;
                        document.body.appendChild(toast);
                        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2500);
                        setTimeout(() => window.location.reload(), 2800);
                    } else {
                        alert('خطا در ذخیره‌سازی: ' + response.data.message);
                    }
                })
                .catch(error => {
                    if (error.response && error.response.data && error.response.data.message) {
                        alert('خطا: ' + error.response.data.message);
                    } else {
                        alert('خطایی در ارتباط با سرور رخ داده است.');
                    }
                });
        }
    };
}
</script>
@endsection
