@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        
        .sc-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .sc-thin::-webkit-scrollbar-track { background: transparent; }
        .sc-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark .sc-thin::-webkit-scrollbar-thumb { background: #475569; }
        
        .toast {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            z-index: 9999;
            background: #1e293b;
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.15);
            opacity: 0;
            pointer-events: none;
            transition: transform .25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity .25s;
        }
        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(-50%) translateY(0);
        }
        .toast-success { background: #10b981; }
        .toast-error { background: #ef4444; }
        
        /* Grid pattern for the canvas */
        .bg-grid-pattern {
            background-size: 20px 20px;
            background-image: 
                linear-gradient(to right, rgba(99, 102, 241, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(99, 102, 241, 0.05) 1px, transparent 1px);
        }
        .dark .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
        }
    </style>

    <div id="cure-toast" class="toast"></div>

    <div class="container mx-auto px-4 py-6" x-data="workflowManagerApp(@js($planJs))" x-cloak>
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('user.booking.cure.show', $cure->id) }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 transition-colors shadow-sm">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">گردش کار و مراحل اجرای سیستم</span>
                    <h1 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white mt-1">مدیریت وضعیت و مسیر درمان</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-2.5 shadow-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300">بیمار:</span>
                    <span class="text-sm font-black text-gray-850 dark:text-white" x-text="patientName"></span>
                </div>
                
                <a href="{{ route('user.booking.cure.show', $cure->id) }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition shadow-md shadow-indigo-100 dark:shadow-indigo-950/40">
                    مشاهده طرح درمان اصلی
                </a>
            </div>
        </div>

        <!-- Main Workspace Grid -->
        <div class="grid grid-cols-1 gap-6">
            
            <template x-if="workflows && workflows.length > 0">
                <div class="space-y-6">
                    <template x-for="(winst, wIdx) in workflows" :key="winst.id">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden anim-fade-up">

                            <!-- Workflow Instance Header -->
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-850 border-b border-gray-200 dark:border-gray-750 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-base text-gray-850 dark:text-gray-150 truncate" x-text="winst.workflow_name"></h4>
                                        <p class="text-[11px] text-gray-400 mt-1" x-text="'تاریخ شروع فرآیند: ' + winst.started_at"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 self-start sm:self-center">
                                    <span class="px-3 py-1 text-[11px] font-black rounded-lg uppercase tracking-wider border shadow-sm"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30': winst.status === 'ACTIVE',
                                              'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700': winst.status === 'COMPLETED',
                                              'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30': winst.status === 'CANCELED'
                                          }">
                                               <span x-show="winst.status === 'ACTIVE'" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                              <span x-text="winst.status === 'ACTIVE' ? 'در حال اجرا' : (winst.status === 'COMPLETED' ? 'تکمیل شده' : 'لغو شده')"></span>
                                          </span>
                                    </span>
                                    <button @click="collapsedInstances[winst.id] = !collapsedInstances[winst.id]"
                                            type="button"
                                            class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors"
                                            title="نمایش/پنهان‌سازی جزئیات مسیر">
                                        <svg class="w-4.5 h-4.5 transition-transform duration-200" :class="collapsedInstances[winst.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div x-show="!collapsedInstances[winst.id]" x-transition x-cloak>

                            <!-- Graphical Canvas Area -->
                            <template x-if="winst.nodes && winst.nodes.length > 0">
                                <div class="relative w-full bg-gray-50/50 dark:bg-gray-900/40 bg-grid-pattern overflow-auto sc-thin border-b border-gray-200 dark:border-gray-750" style="height: 440px;">
                                    <div :style="`position: relative; width: ${getWorkflowGeometry(winst).width}px; height: ${getWorkflowGeometry(winst).height}px;`" class="pointer-events-none">

                                        <!-- SVG for Edges -->
                                        <svg class="absolute inset-0 w-full h-full pointer-events-none" :view-box.camel="getWorkflowGeometry(winst).viewBox">
                                            <defs>
                                                <marker id="mini-arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                                                    <path d="M 0 0 L 10 5 L 0 10 z" class="fill-gray-400 dark:fill-gray-600" />
                                                </marker>
                                                <marker id="mini-arrow-selected" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                                                    <path d="M 0 0 L 10 5 L 0 10 z" class="fill-indigo-500 dark:fill-indigo-400" />
                                                </marker>
                                            </defs>

                                            <!-- Bezier Edges injected via x-html -->
                                            <g x-html="renderEdgesHtml(winst)"></g>
                                        </svg>

                                        <!-- HTML Node Cards Container overlay -->
                                        <div class="absolute inset-0 w-full h-full pointer-events-none">
                                            <template x-for="node in winst.nodes" :key="node.id">
                                                <!-- Modern Card Design for Nodes -->
                                                <div class="absolute w-52 pointer-events-auto rounded-xl shadow-sm border bg-white dark:bg-gray-800 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 overflow-hidden cursor-pointer"
                                                     :style="`left: ${(node.config?.x || 0) - getWorkflowGeometry(winst).minX}px; top: ${(node.config?.y || 0) - getWorkflowGeometry(winst).minY}px;`"
                                                     @click="selectWidgetNode(winst.id, node)"
                                                     :class="[
                                                         winst.current_node_id === node.id ? 'border-indigo-500 ring-2 ring-indigo-500/30 shadow-indigo-200/50 dark:shadow-indigo-900/30' : 'border-gray-200 dark:border-gray-700',
                                                         isWidgetNodeSelected(winst.id, node.id) && winst.current_node_id !== node.id ? 'border-violet-400 ring-2 ring-violet-400/20' : ''
                                                     ]">

                                                    <!-- Card Header -->
                                                    <div class="px-3 py-2 flex items-center gap-2 border-b border-gray-100 dark:border-gray-750"
                                                         :class="winst.current_node_id === node.id ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''">
                                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white shrink-0 shadow-inner"
                                                             :class="getNodeColorClass(node.type)">
                                                            <span x-html="getNodeIcon(node.type)"></span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-[11px] font-bold text-gray-800 dark:text-gray-100 truncate" x-text="node.name"></h4>
                                                            <p class="text-[9px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide" x-text="getNodeTypeName(node.type)"></p>
                                                        </div>
                                                        <!-- Pulse indicator for active node -->
                                                        <div x-show="winst.current_node_id === node.id" class="relative flex h-2.5 w-2.5 shrink-0 self-start mt-0.5">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-500"></span>
                                                        </div>
                                                    </div>

                                                    <!-- Card Body -->
                                                    <div class="px-3 py-2 bg-gray-50/50 dark:bg-gray-800/30">
                                                        <p x-show="node.type === 'ACTION'" class="text-[10px] text-gray-600 dark:text-gray-300 truncate">
                                                            <span class="opacity-70">وظیفه:</span> <span class="font-bold" x-text="node.config?.title || 'اقدام سیستمی'"></span>
                                                        </p>
                                                        <p x-show="node.type === 'CONDITION'" class="text-[10px] text-gray-600 dark:text-gray-300 truncate">
                                                            <span class="opacity-70">شرط:</span> <span class="font-mono text-[9px] px-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-200" x-text="node.config?.condition_expression || 'ساده'"></span>
                                                        </p>
                                                        <p x-show="node.type === 'START'" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">نقطه شروع فرآیند</p>
                                                        <p x-show="node.type === 'END'" class="text-[10px] text-rose-600 dark:text-rose-400 font-bold">پایان مسیر درمان</p>

                                                        <!-- Task Status Badge/Link -->
                                                        <template x-if="node.type === 'ACTION' && winst.tasks && winst.tasks.filter(t => String(t.node_id) === String(node.id)).length > 0">
                                                            <div class="mt-1.5 space-y-1">
                                                                <template x-for="task in winst.tasks.filter(t => String(t.node_id) === String(node.id))" :key="task.id">
                                                                    <div class="flex items-center justify-between gap-1 border-b border-gray-100/30 dark:border-gray-750/30 pb-0.5 last:border-b-0">
                                                                        <span class="text-[9px] text-gray-500 dark:text-gray-400 truncate max-w-[90px]" :title="task.title" x-text="task.title"></span>
                                                                        <a :href="`/user/tasks/${task.id}`"
                                                                           target="_blank"
                                                                           class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold border transition-colors hover:bg-gray-100 dark:hover:bg-gray-700/60"
                                                                           :class="{
                                                                               'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-800/40': task.status === 'DONE',
                                                                               'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-800/40': task.status === 'IN_PROGRESS',
                                                                               'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800/40': task.status === 'TODO',
                                                                               'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-800/40': task.status === 'CANCELED'
                                                                           }"
                                                                           x-text="task.status_label">
                                                                        </a>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>

                                                        <!-- Direct Node Action Controls -->
                                                        <div x-show="winst.current_node_id === node.id && winst.status === 'ACTIVE'" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60" @click.stop>
                                                            <template x-if="node.type === 'CONDITION'">
                                                                <div class="flex items-center gap-1.5 justify-center">
                                                                    <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), true)"
                                                                            class="flex-1 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition-all shadow-sm flex items-center justify-center gap-0.5">
                                                                        بله
                                                                    </button>
                                                                    <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), false)"
                                                                            class="flex-1 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold transition-all shadow-sm flex items-center justify-center gap-0.5">
                                                                        خیر
                                                                    </button>
                                                                </div>
                                                            </template>
                                                            <template x-if="node.type === 'ACTION'">
                                                                <button @click="advanceWorkflowInstance(winst.id)"
                                                                        class="w-full py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold transition-all shadow-sm flex items-center justify-center gap-0.5">
                                                                    اجرای گام و عبور
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                    </div>
                                </div>
                            </template>

                            <!-- Control Center Panel -->
                            <div class="m-5 relative overflow-hidden bg-white dark:bg-gray-850 rounded-2xl border border-gray-200 dark:border-gray-750 shadow-sm">
                                <div class="absolute inset-y-0 right-0 w-1.5" :class="isSelectedWidgetNodeActive(winst) ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <!-- Selected Node Details -->
                                    <div class="flex items-start gap-4 pl-2">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-inner"
                                             :class="getNodeColorClass(getSelectedWidgetNodeType(winst))">
                                            <span x-html="getNodeIcon(getSelectedWidgetNodeType(winst))" class="text-white"></span>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">گره انتخاب شده <span x-show="isSelectedWidgetNodeActive(winst)" class="text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded ml-1 text-[8px]">(گام فعلی)</span></p>
                                            <h4 class="text-base font-black text-gray-800 dark:text-gray-100" x-text="getSelectedWidgetNodeName(winst)"></h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">نوع عملیات: <span class="font-bold text-gray-700 dark:text-gray-300" x-text="getNodeTypeName(getSelectedWidgetNodeType(winst))"></span></p>
                                            
                                            <!-- If there's a task for this selected node, show it here! -->
                                            <template x-if="getSelectedWidgetNodeType(winst) === 'ACTION' && getSelectedWidgetNodeTasks(winst).length > 0">
                                                <div class="mt-2 text-xs space-y-1.5 w-full">
                                                    <span class="text-gray-400 block mb-1">تسک‌های متناظر این گام:</span>
                                                    <template x-for="task in getSelectedWidgetNodeTasks(winst)" :key="task.id">
                                                        <div class="flex items-center justify-between gap-3 bg-gray-50/70 dark:bg-gray-900/40 p-2 rounded-xl border border-gray-200 dark:border-gray-750">
                                                            <a :href="`/user/tasks/${task.id}`"
                                                               target="_blank"
                                                               class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors flex items-center gap-1 truncate"
                                                            >
                                                                <span x-text="task.title"></span>
                                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            </a>
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border shrink-0"
                                                                  :class="{
                                                                      'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-800/30': task.status === 'DONE',
                                                                      'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-800/30': task.status === 'IN_PROGRESS',
                                                                      'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800/30': task.status === 'TODO',
                                                                      'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-800/30': task.status === 'CANCELED'
                                                                  }"
                                                                  x-text="task.status_label"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Contextual Actions -->
                                    <div class="flex flex-wrap items-center gap-2 self-end md:self-center">
                                        <template x-if="winst.status === 'ACTIVE' && isSelectedWidgetNodeActive(winst)">
                                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                                <template x-if="getSelectedWidgetNodeType(winst) === 'CONDITION'">
                                                    <div class="flex items-center gap-2">
                                                        <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), true)"
                                                                class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            بله
                                                        </button>
                                                        <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), false)"
                                                                class="px-5 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            خیر
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="getSelectedWidgetNodeType(winst) !== 'CONDITION'">
                                                    <button @click="advanceWorkflowInstance(winst.id)"
                                                            class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-xs font-bold transition-all shadow-md shadow-indigo-100 dark:shadow-indigo-950/40 hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                                        </svg>
                                                        اجرای گام و عبور
                                                    </button>
                                                </template>
                                                <button @click="cancelWorkflowInstance(winst.id)"
                                                        title="لغو کامل مسیر درمان"
                                                        class="px-3 py-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="winst.status !== 'ACTIVE' || !isSelectedWidgetNodeActive(winst)">
                                            <button @click="restartWorkflowInstance(winst.id)"
                                                    class="w-full sm:w-auto px-6 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/>
                                                </svg>
                                                راه‌اندازی مجدد کل فرآیند
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            
            <template x-if="!workflows || workflows.length === 0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">هیچ گردش‌کاری آغاز نشده است</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">برای این طرح درمان هنوز مسیر فعالی شروع نشده یا پیکربندی نشده است.</p>
                </div>
            </template>

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

        function workflowManagerApp(existingPlan = null) {
            return {
                patientName: existingPlan?.patient_name || 'نامشخص',
                workflows: existingPlan ? (existingPlan.workflows || []) : [],
                selectedWidgetNodes: {},
                collapsedInstances: {},
                pollingInterval: null,

                async advanceWorkflowInstance(id) {
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast(data.message, 'success');
                            this.reloadWorkflows();
                        } else {
                            showToast(data.message || 'خطا در اجرای فرآیند', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    }
                },
                async advanceWorkflowInstanceWithChoice(id, varName, value) {
                    try {
                        const payload = {};
                        payload[varName] = value;
                        const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast(data.message, 'success');
                            this.reloadWorkflows();
                        } else {
                            showToast(data.message || 'خطا در اجرای فرآیند', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    }
                },
                getConditionVarName(winst) {
                    const activeNode = winst.nodes?.find(n => n.id === winst.current_node_id);
                    const expr = activeNode?.config?.condition_expression || '';
                    if (expr.includes('=')) {
                        return expr.split('=')[0].trim();
                    }
                    return 'condition_result';
                },
                async cancelWorkflowInstance(id) {
                    if (!confirm('آیا از لغو این فرآیند اطمینان دارید؟')) return;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast(data.message, 'success');
                            this.reloadWorkflows();
                        } else {
                            showToast(data.message || 'خطا در لغو فرآیند', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    }
                },
                async restartWorkflowInstance(id) {
                    if (!confirm('آیا از شروع مجدد فرآیند از نقطه شروع اطمینان دارید؟')) return;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/restart`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast(data.message, 'success');
                            this.reloadWorkflows();
                        } else {
                            showToast(data.message || 'خطا در راه‌اندازی فرآیند', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    }
                },
                // Helper functions for visually appealing nodes
                getNodeIcon(type) {
                    switch(type) {
                        case 'START': return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                        case 'END': return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
                        case 'ACTION': return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
                        case 'CONDITION': return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>';
                        default: return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    }
                },
                getNodeTypeName(type) {
                    switch(type) {
                        case 'START': return 'نقطه شروع';
                        case 'END': return 'پایان مسیر';
                        case 'ACTION': return 'اقدام / وظیفه';
                        case 'CONDITION': return 'ارزیابی شرط';
                        case 'SUB_WORKFLOW': return 'زیر‌فرآیند';
                        default: return 'گره سیستم';
                    }
                },
                getWorkflowGeometry(winst) {
                    if (!winst.nodes || winst.nodes.length === 0) {
                        return { minX: 0, minY: 0, width: 500, height: 320, viewBox: '0 0 500 320' };
                    }
                    let minX = Infinity, maxX = -Infinity;
                    let minY = Infinity, maxY = -Infinity;
                    winst.nodes.forEach(n => {
                        const x = Number(n.config?.x) || 0;
                        const y = Number(n.config?.y) || 0;
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    });
                    const paddingX = 80;
                    const paddingY = 60;
                    const computedMinX = minX - paddingX;
                    const computedMinY = minY - paddingY;
                    const computedWidth = (maxX - minX) + 208 + paddingX * 2; // w-52 = 208px
                    const computedHeight = (maxY - minY) + 72 + paddingY * 2;
                    return {
                        minX: computedMinX,
                        minY: computedMinY,
                        width: computedWidth,
                        height: computedHeight,
                        viewBox: `${computedMinX} ${computedMinY} ${computedWidth} ${computedHeight}`
                    };
                },
                renderEdgesHtml(winst) {
                    if (!winst.edges || winst.edges.length === 0) return '';
                    let html = '';
                    winst.edges.forEach(edge => {
                        const pathData = this.getMiniBezierPath(edge, winst.nodes);
                        const isActive = this.isEdgeConnectedToActiveNode(edge, winst);
                        const className = isActive 
                            ? 'stroke-indigo-500 dark:stroke-indigo-400' 
                            : 'stroke-gray-300 dark:stroke-gray-700';
                        const markerEnd = isActive ? 'url(#mini-arrow-selected)' : 'url(#mini-arrow)';
                        html += `<path d="${pathData}" stroke-width="2.5" fill="none" class="transition-colors duration-200 ${className}" marker-end="${markerEnd}"></path>`;
                    });
                    return html;
                },
                getMiniBezierPath(edge, nodes) {
                    const source = nodes.find(n => n.id === edge.source_id);
                    const target = nodes.find(n => n.id === edge.target_id);
                    if (!source || !target) return '';
                    const x1 = (Number(source.config?.x) || 0) + 104; // w-52 = 208 / 2 = 104 center
                    const y1 = (Number(source.config?.y) || 0) + 72; // approx 72 height
                    const x2 = (Number(target.config?.x) || 0) + 104;
                    const y2 = (Number(target.config?.y) || 0);
                    const controlY = y1 + (y2 - y1) / 2;
                    return `M ${x1} ${y1} C ${x1} ${controlY}, ${x2} ${controlY}, ${x2} ${y2}`;
                },
                isEdgeConnectedToActiveNode(edge, winst) {
                    return edge.source_id === winst.current_node_id || edge.target_id === winst.current_node_id;
                },
                getNodeColorClass(type) {
                    switch(type) {
                        case 'START': return 'bg-emerald-500 shadow-emerald-200 dark:shadow-none';
                        case 'END': return 'bg-rose-500 shadow-rose-200 dark:shadow-none';
                        case 'ACTION': return 'bg-indigo-500 shadow-indigo-200 dark:shadow-none';
                        case 'CONDITION': return 'bg-amber-500 shadow-amber-200 dark:shadow-none';
                        case 'SUB_WORKFLOW': return 'bg-purple-500 shadow-purple-200 dark:shadow-none';
                        default: return 'bg-gray-500';
                    }
                },
                selectWidgetNode(instanceId, node) {
                    this.selectedWidgetNodes = { ...this.selectedWidgetNodes, [instanceId]: node };
                },
                isWidgetNodeSelected(instanceId, nodeId) {
                    return this.selectedWidgetNodes[instanceId]?.id === nodeId;
                },
                getSelectedWidgetNodeName(winst) {
                    return this.selectedWidgetNodes[winst.id]?.name || winst.current_node_name || 'نامشخص';
                },
                getSelectedWidgetNodeType(winst) {
                    return this.selectedWidgetNodes[winst.id]?.type || winst.current_node_type || '';
                },
                isSelectedWidgetNodeActive(winst) {
                    const selected = this.selectedWidgetNodes[winst.id];
                    return !selected || selected.id === winst.current_node_id;
                },
                getSelectedWidgetNodeTasks(winst) {
                    const selectedNode = this.selectedWidgetNodes[winst.id] || winst.nodes?.find(n => n.id === winst.current_node_id);
                    if (!selectedNode || selectedNode.type !== 'ACTION') return [];
                    return winst.tasks?.filter(t => String(t.node_id) === String(selectedNode.id)) || [];
                },
                async reloadWorkflows() {
                    try {
                        const oldWorkflows = this.workflows;
                        const res = await fetch(window.location.pathname, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.workflows) {
                                this.workflows = data.workflows;
                                const newSelected = { ...this.selectedWidgetNodes };
                                this.workflows.forEach(inst => {
                                    const oldWorkflow = oldWorkflows ? oldWorkflows.find(w => w.id === inst.id) : null;
                                    const oldSelectedNodeId = this.selectedWidgetNodes[inst.id]?.id;
                                    const activeNode = inst.nodes?.find(n => n.id === inst.current_node_id);

                                    // If no previous selection, or if workflow current_node_id has changed,
                                    // or if the previously selected node was the active node (meaning we track the active node):
                                    if (!oldSelectedNodeId || (oldWorkflow && oldWorkflow.current_node_id !== inst.current_node_id) || (oldWorkflow && oldSelectedNodeId === oldWorkflow.current_node_id)) {
                                        if (activeNode) {
                                            newSelected[inst.id] = activeNode;
                                        } else if (inst.nodes?.length > 0) {
                                            newSelected[inst.id] = inst.nodes[0];
                                        }
                                    } else {
                                        // Keep manual selection if it still exists
                                        const nodeStillExists = inst.nodes?.find(n => n.id === oldSelectedNodeId);
                                        if (nodeStillExists) {
                                            newSelected[inst.id] = nodeStillExists;
                                        } else if (activeNode) {
                                            newSelected[inst.id] = activeNode;
                                        }
                                    }

                                    // Collapse by default if canceled and not set yet
                                    if (this.collapsedInstances[inst.id] === undefined) {
                                        this.collapsedInstances[inst.id] = (inst.status === 'CANCELED');
                                    }
                                });
                                this.selectedWidgetNodes = newSelected;
                            }
                        }
                    } catch (e) {}
                },
                
                init() {
                    // Preselect active node for all workflows and initialize collapsing
                    const newSelected = {};
                    this.workflows.forEach(inst => {
                        const activeNode = inst.nodes?.find(n => n.id === inst.current_node_id);
                        if (activeNode) {
                            newSelected[inst.id] = activeNode;
                        } else if (inst.nodes?.length > 0) {
                            newSelected[inst.id] = inst.nodes[0];
                        }
                        this.collapsedInstances[inst.id] = (inst.status === 'CANCELED');
                    });
                    this.selectedWidgetNodes = newSelected;

                    // Start polling interval for real-time updates (every 5 seconds)
                    this.pollingInterval = setInterval(() => {
                        this.reloadWorkflows();
                    }, 5000);
                }
            };
        }
    </script>
@endsection
