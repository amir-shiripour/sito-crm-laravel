@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        
        .sc-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .sc-thin::-webkit-scrollbar-track { background: transparent; }
        .sc-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark .sc-thin::-webkit-scrollbar-thumb { background: #334155; }
        
        .toast {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            z-index: 9999;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.15);
            opacity: 0;
            pointer-events: none;
            transition: transform .25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity .25s;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(-50%) translateY(0);
        }
        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
        
        /* Modern Dot Grid Pattern - Adjusted for scaling */
        .bg-grid-pattern {
            background-size: 24px 24px;
            background-image: radial-gradient(circle, rgba(99, 102, 241, 0.25) 1.5px, transparent 1.5px);
        }
        .dark .bg-grid-pattern {
            background-image: radial-gradient(circle, rgba(99, 102, 241, 0.15) 1.5px, transparent 1.5px);
        }
        
        /* Glassmorphism for nodes */
        .glass-node {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glass-node {
            background: rgba(30, 41, 59, 0.85);
        }
        
        /* Grab cursors */
        .canvas-container { cursor: grab; }
        .canvas-container:active { cursor: grabbing; }
    </style>

    <div id="cure-toast" class="toast"></div>

    <div class="container mx-auto px-4 py-6 max-w-7xl" x-data="workflowManagerApp(@js($planJs))" x-cloak>
        <!-- Page Header -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-5 mb-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.booking.cure.show', $cure->id) }}" class="w-11 h-11 rounded-xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-900/50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors shadow-sm border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <span class="text-[11px] font-black text-indigo-500 dark:text-indigo-400 uppercase tracking-widest">گراف اجرایی سیستم</span>
                    <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white mt-0.5 bg-clip-text text-transparent bg-gradient-to-l from-slate-900 to-slate-700 dark:from-white dark:to-slate-300">مدیریت وضعیت و مسیر درمان</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-5 py-2.5 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-3 shadow-inner">
                    <div class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">بیمار:</span>
                    <span class="text-sm font-black text-slate-800 dark:text-slate-100" x-text="patientName"></span>
                </div>
                
                <a href="{{ route('user.booking.cure.show', $cure->id) }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md shadow-indigo-200 dark:shadow-indigo-900/40 hover:-translate-y-0.5">
                    طرح درمان اصلی
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap gap-4 items-center">
                <!-- Tooth Filter -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">فیلتر دندان:</span>
                    <select x-model="selectedToothFilter" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-bold px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none">
                        <option value="all">همه دندان‌ها و کل طرح</option>
                        <option value="none">بدون دندان (فقط کل طرح)</option>
                        <template x-for="t in uniqueTeeth" :key="t">
                            <option :value="t" x-text="'دندان ' + getToothLabel(t).num"></option>
                        </template>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">وضعیت فرآیند:</span>
                    <select x-model="selectedStatusFilter" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-bold px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="ACTIVE">در حال اجرا</option>
                        <option value="COMPLETED">تکمیل شده</option>
                        <option value="CANCELED">لغو شده</option>
                    </select>
                </div>
            </div>
            
            <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                تعداد فرآیندها: <span class="text-indigo-600 dark:text-indigo-400 font-extrabold" x-text="filteredWorkflows.length"></span> از <span x-text="workflows.length"></span>
            </div>
        </div>

        <!-- Main Workspace Grid -->
        <div class="grid grid-cols-1 gap-6">
            
            <template x-if="filteredWorkflows && filteredWorkflows.length > 0">
                <div class="space-y-8">
                    <template x-for="(winst, wIdx) in filteredWorkflows" :key="winst.id">
                        <div class="bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">

                            <!-- Workflow Instance Header -->
                            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800" @click="collapsedInstances[winst.id] = !collapsedInstances[winst.id]">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/40 dark:to-indigo-800/40 text-indigo-600 dark:text-indigo-400 shadow-inner">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-black text-lg text-slate-800 dark:text-slate-100 truncate tracking-tight" x-text="winst.workflow_name"></h4>
                                            <template x-if="winst.tooth_context">
                                                <span class="px-2 py-0.5 text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 rounded-md border border-blue-100 dark:border-blue-800/40" x-text="'دندان ' + getToothLabel(winst.tooth_context).num"></span>
                                            </template>
                                            <template x-if="winst.item_context && winst.item_context.service_name">
                                                <span class="px-2 py-0.5 text-[10px] font-black bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400 rounded-md border border-purple-100 dark:border-purple-800/40" x-text="winst.item_context.service_name"></span>
                                            </template>
                                            <template x-if="!winst.tooth_context && (!winst.item_context || !winst.item_context.service_name)">
                                                <span class="px-2 py-0.5 text-[10px] font-black bg-slate-50 text-slate-600 dark:bg-slate-700/20 dark:text-slate-400 rounded-md border border-slate-100 dark:border-slate-700/40">کل طرح درمان</span>
                                            </template>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 font-medium flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span x-text="'تاریخ شروع فرآیند: ' + winst.started_at"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 self-start sm:self-center" @click.stop>
                                    <div class="px-3 py-1.5 text-[11px] font-black rounded-lg uppercase tracking-wider flex items-center gap-2"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400': winst.status === 'ACTIVE',
                                              'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300': winst.status === 'COMPLETED',
                                              'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400': winst.status === 'CANCELED'
                                          }">
                                          <div x-show="winst.status === 'ACTIVE'" class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                          </div>
                                          <span x-text="winst.status === 'ACTIVE' ? 'در حال اجرا' : (winst.status === 'COMPLETED' ? 'تکمیل شده' : 'لغو شده')"></span>
                                    </div>
                                    <button @click="collapsedInstances[winst.id] = !collapsedInstances[winst.id]"
                                            type="button"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                            title="نمایش/پنهان‌سازی">
                                        <svg class="w-5 h-5 transition-transform duration-300" :class="collapsedInstances[winst.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div x-show="!collapsedInstances[winst.id]" x-transition x-cloak>

                            <!-- Graphical Canvas Area (Pan & Zoom Container) -->
                            <template x-if="winst.nodes && winst.nodes.length > 0">
                                <div class="relative w-full overflow-hidden bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200/60 dark:border-slate-700/60 canvas-container" 
                                     style="height: 540px;"
                                     @mousedown="startDrag($event, winst.id)"
                                     @mousemove="doDrag($event)"
                                     @mouseup="stopDrag()"
                                     @mouseleave="stopDrag()"
                                     @wheel.prevent="doZoom($event, winst.id)">
                                     
                                    <!-- Zoom Controls Widget -->
                                    <div class="absolute bottom-6 right-6 z-50 flex flex-col gap-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md p-1.5 rounded-xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg" @mousedown.stop @wheel.stop>
                                        <button @click="zoomIn(winst.id)" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="بزرگ‌نمایی">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <div class="h-px bg-slate-200 dark:bg-slate-700 mx-2"></div>
                                        <button @click="resetZoom(winst.id)" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 font-black text-[10px] hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="تنظیم مجدد">
                                            <span x-text="Math.round(canvasStates[winst.id].scale * 100) + '%'"></span>
                                        </button>
                                        <div class="h-px bg-slate-200 dark:bg-slate-700 mx-2"></div>
                                        <button @click="zoomOut(winst.id)" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="کوچک‌نمایی">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                        </button>
                                    </div>

                                    <!-- The Transformable Canvas -->
                                    <div class="absolute bg-grid-pattern transform-gpu"
                                         :style="`
                                            width: ${getWorkflowGeometry(winst).width}px; 
                                            height: ${getWorkflowGeometry(winst).height}px;
                                            transform: translate(${canvasStates[winst.id].panX}px, ${canvasStates[winst.id].panY}px) scale(${canvasStates[winst.id].scale});
                                            transform-origin: 0 0;
                                            transition: ${isDragging ? 'none' : 'transform 0.15s ease-out'};
                                            will-change: transform;
                                         `">

                                        <!-- SVG for Edges -->
                                        <svg class="absolute inset-0 w-full h-full pointer-events-none drop-shadow-sm" :view-box.camel="getWorkflowGeometry(winst).viewBox">
                                            <defs>
                                                <marker id="mini-arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                                                    <path d="M 0 0 L 10 5 L 0 10 z" class="fill-slate-300 dark:fill-slate-600" />
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
                                                <!-- Modern Glass Card Design for Nodes -->
                                                <div class="absolute w-56 pointer-events-auto rounded-2xl shadow-sm border glass-node transition-all duration-300 hover:shadow-xl hover:-translate-y-1 overflow-hidden cursor-pointer"
                                                     :style="`left: ${(node.config?.x || 0) - getWorkflowGeometry(winst).minX}px; top: ${(node.config?.y || 0) - getWorkflowGeometry(winst).minY}px;`"
                                                     @mousedown.stop
                                                     @click.stop="selectWidgetNode(winst.id, node)"
                                                     :class="[
                                                         winst.current_node_id === node.id ? 'border-indigo-500 ring-4 ring-indigo-500/20 shadow-indigo-200/50 dark:shadow-indigo-900/30' : 'border-slate-200/80 dark:border-slate-700/80',
                                                         isWidgetNodeSelected(winst.id, node.id) && winst.current_node_id !== node.id ? 'border-violet-400 ring-2 ring-violet-400/20' : ''
                                                     ]">

                                                    <!-- Card Header -->
                                                    <div class="px-4 py-3 flex items-center gap-3 border-b border-slate-100/50 dark:border-slate-700/50"
                                                         :class="winst.current_node_id === node.id ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''">
                                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white shrink-0 shadow-inner"
                                                             :class="getNodeColorClass(node.type)">
                                                            <span x-html="getNodeIcon(node.type)"></span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-xs font-black text-slate-800 dark:text-slate-100 truncate" x-text="node.name"></h4>
                                                            <p class="text-[9px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest mt-0.5" x-text="getNodeTypeName(node.type)"></p>
                                                        </div>
                                                        <!-- Pulse indicator for active node -->
                                                        <div x-show="winst.current_node_id === node.id" class="relative flex h-2.5 w-2.5 shrink-0 self-start mt-1">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-500"></span>
                                                        </div>
                                                    </div>

                                                    <!-- Card Body -->
                                                    <div class="px-4 py-3 bg-slate-50/30 dark:bg-slate-800/30">
                                                        <p x-show="node.type === 'ACTION'" class="text-[11px] text-slate-600 dark:text-slate-300 truncate">
                                                            <span class="opacity-70 font-medium">وظیفه:</span> <span class="font-black" x-text="node.config?.title || 'اقدام سیستمی'"></span>
                                                        </p>
                                                        <p x-show="node.type === 'CONDITION'" class="text-[11px] text-slate-600 dark:text-slate-300 truncate">
                                                            <span class="opacity-70 font-medium">شرط:</span> <span class="font-mono text-[10px] px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 rounded text-slate-800 dark:text-slate-200" x-text="node.config?.condition_expression || 'ساده'"></span>
                                                        </p>
                                                        <p x-show="node.type === 'START'" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-black">نقطه شروع فرآیند</p>
                                                        <p x-show="node.type === 'END'" class="text-[11px] text-rose-600 dark:text-rose-400 font-black">پایان مسیر درمان</p>

                                                        <!-- Task Status Badges -->
                                                        <template x-if="node.type === 'ACTION' && winst.tasks && winst.tasks.filter(t => String(t.node_id) === String(node.id)).length > 0">
                                                            <div class="mt-2.5 space-y-1.5">
                                                                <template x-for="task in winst.tasks.filter(t => String(t.node_id) === String(node.id))" :key="task.id">
                                                                    <div class="flex items-center justify-between gap-2 border-b border-slate-100/30 dark:border-slate-750/30 pb-1.5 last:border-b-0 last:pb-0">
                                                                        <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 truncate max-w-[100px]" :title="task.title" x-text="task.title"></span>
                                                                        <a :href="`/user/tasks/${task.id}`"
                                                                           target="_blank"
                                                                           class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black border transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
                                                                           :class="{
                                                                               'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/40': task.status === 'DONE',
                                                                               'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/40': task.status === 'IN_PROGRESS',
                                                                               'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/40': task.status === 'TODO',
                                                                               'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800/40': task.status === 'CANCELED'
                                                                           }"
                                                                           x-text="task.status_label">
                                                                        </a>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>

                                                        <!-- Direct Node Action Controls -->
                                                        <div x-show="winst.current_node_id === node.id && winst.status === 'ACTIVE'" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/60" @click.stop>
                                                            <template x-if="node.type === 'CONDITION'">
                                                                <div class="flex items-center gap-2 justify-center">
                                                                    <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), true)"
                                                                            class="flex-1 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black transition-all shadow-sm flex items-center justify-center gap-1 hover:-translate-y-0.5">
                                                                        بله
                                                                    </button>
                                                                    <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), false)"
                                                                            class="flex-1 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-black transition-all shadow-sm flex items-center justify-center gap-1 hover:-translate-y-0.5">
                                                                        خیر
                                                                    </button>
                                                                </div>
                                                            </template>
                                                            <template x-if="node.type === 'ACTION'">
                                                                <button @click="advanceWorkflowInstance(winst.id)"
                                                                        class="w-full py-1.5 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-[11px] font-black transition-all shadow-sm flex items-center justify-center gap-1 hover:-translate-y-0.5">
                                                                    تایید و عبور
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

                            <!-- Command Center Panel -->
                            <div class="p-6 bg-slate-50 dark:bg-slate-900/30 border-t border-slate-200/60 dark:border-slate-700/60">
                                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden relative">
                                    <div class="absolute inset-y-0 right-0 w-1.5" :class="isSelectedWidgetNodeActive(winst) ? 'bg-indigo-500' : 'bg-slate-300 dark:bg-slate-600'"></div>
                                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                        <!-- Selected Node Details -->
                                        <div class="flex items-start gap-5 pl-2 w-full md:w-auto">
                                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-inner"
                                                 :class="getNodeColorClass(getSelectedWidgetNodeType(winst))">
                                                <span x-html="getNodeIcon(getSelectedWidgetNodeType(winst))" class="text-white w-6 h-6 flex items-center justify-center"></span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">گره انتخاب شده</p>
                                                    <span x-show="isSelectedWidgetNodeActive(winst)" class="text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded text-[9px] font-black shadow-sm">گام فعلی</span>
                                                </div>
                                                <h4 class="text-lg font-black text-slate-800 dark:text-slate-100" x-text="getSelectedWidgetNodeName(winst)"></h4>
                                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">نوع عملیات: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="getNodeTypeName(getSelectedWidgetNodeType(winst))"></span></p>
                                                
                                                <!-- Contextual Tasks List -->
                                                <template x-if="getSelectedWidgetNodeType(winst) === 'ACTION' && getSelectedWidgetNodeTasks(winst).length > 0">
                                                    <div class="mt-4 text-xs space-y-2 w-full max-w-md">
                                                        <span class="text-slate-400 font-bold block mb-2 text-[11px]">وظایف (تسک‌های) مرتبط با این گام:</span>
                                                        <template x-for="task in getSelectedWidgetNodeTasks(winst)" :key="task.id">
                                                            <div class="flex items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm transition-colors hover:border-slate-300 dark:hover:border-slate-600">
                                                                <a :href="`/user/tasks/${task.id}`"
                                                                   target="_blank"
                                                                   class="font-black text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors flex items-center gap-1.5 truncate"
                                                                >
                                                                    <span x-text="task.title"></span>
                                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                                </a>
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black border shrink-0"
                                                                      :class="{
                                                                          'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/40': task.status === 'DONE',
                                                                          'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/40': task.status === 'IN_PROGRESS',
                                                                          'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/40': task.status === 'TODO',
                                                                          'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800/40': task.status === 'CANCELED'
                                                                      }"
                                                                      x-text="task.status_label"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Contextual Actions -->
                                        <div class="flex flex-wrap items-center gap-3 self-end md:self-center w-full md:w-auto">
                                            <button @click="openHistoryModal(winst.id)"
                                                    title="مشاهده تاریخچه تغییرات"
                                                    class="px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-black transition-all shadow-sm flex items-center gap-1.5 border border-slate-200 dark:border-slate-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                تاریخچه
                                            </button>

                                            <template x-if="winst.status === 'ACTIVE' && isSelectedWidgetNodeActive(winst)">
                                                <div class="flex items-center gap-3 w-full sm:w-auto">
                                                    <template x-if="getSelectedWidgetNodeType(winst) === 'CONDITION'">
                                                        <div class="flex items-center gap-2 w-full sm:w-auto">
                                                            <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), true)"
                                                                    class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition-all shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                                بله
                                                            </button>
                                                            <button @click="advanceWorkflowInstanceWithChoice(winst.id, getConditionVarName(winst), false)"
                                                                    class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black transition-all shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                                خیر
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="getSelectedWidgetNodeType(winst) !== 'CONDITION'">
                                                        <button @click="advanceWorkflowInstance(winst.id)"
                                                                class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-xs font-black transition-all shadow-md shadow-indigo-200 dark:shadow-indigo-900/40 hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                                            </svg>
                                                            اجرای گام و عبور
                                                        </button>
                                                    </template>
                                                    <button @click="goBackInstance(winst.id)"
                                                            title="بازگشت به مرحله قبل"
                                                            class="px-4 py-3 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/40 text-amber-600 dark:text-amber-400 text-xs font-black transition-all shadow-sm flex items-center gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                                        بازگشت
                                                    </button>
                                                    <button @click="cancelWorkflowInstance(winst.id)"
                                                            title="لغو کامل مسیر درمان"
                                                            class="px-4 py-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 text-xs font-black transition-all shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>

                                            <template x-if="winst.status !== 'ACTIVE' || !isSelectedWidgetNodeActive(winst)">
                                                <button @click="restartWorkflowInstance(winst.id)"
                                                        class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-black transition-all flex items-center justify-center gap-2 shadow-sm">
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
                        </div>
                    </template>
                </div>
            </template>
            
            <template x-if="!workflows || workflows.length === 0">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-16 text-center">
                    <div class="w-20 h-20 mx-auto bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-slate-200 mb-2">هیچ گردش‌کاری آغاز نشده است</h3>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 max-w-md mx-auto">برای این طرح درمان هنوز مسیر فعالی شروع نشده یا پیکربندی گرافیکی برای آن ترسیم نشده است.</p>
                </div>
            </template>

            <!-- History Modal -->
            <template x-teleport="body">
                <div x-show="historyModalOpen" 
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                     style="display: none;">
            <div x-show="historyModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                 @click="historyModalOpen = false"></div>

            <div x-show="historyModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-700/60 w-full max-w-2xl flex flex-col max-h-full">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between shrink-0">
                    <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        تاریخچه تغییرات مسیر درمان
                    </h3>
                    <button @click="historyModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6">
                    <template x-if="activeHistoryLogs.length === 0">
                        <div class="text-center py-8">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">تاریخچه‌ای برای این فرآیند ثبت نشده است.</p>
                        </div>
                    </template>
                    
                    <template x-if="activeHistoryLogs.length > 0">
                        <div class="relative before:absolute before:inset-y-0 before:right-[15px] before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                            <template x-for="(log, idx) in activeHistoryLogs" :key="log.id">
                                <div class="relative flex gap-4 mb-6 last:mb-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 border-4 border-white dark:border-slate-800 z-10"
                                         :class="{
                                             'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400': log.transition_type === 'AUTO',
                                             'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400': log.transition_type === 'MANUAL',
                                             'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400': log.transition_type === 'GOBACK',
                                             'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400': !['AUTO','MANUAL','GOBACK'].includes(log.transition_type)
                                         }">
                                        <template x-if="log.transition_type === 'AUTO'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></template>
                                        <template x-if="log.transition_type === 'MANUAL'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></template>
                                        <template x-if="log.transition_type === 'GOBACK'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></template>
                                        <template x-if="!['AUTO','MANUAL','GOBACK'].includes(log.transition_type)"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></template>
                                    </div>
                                    <div class="flex-1 bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-4">
                                        <div class="flex justify-between items-start gap-4 mb-2">
                                            <div class="text-sm font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                <span x-text="log.user_name"></span>
                                                <span class="px-2 py-0.5 rounded text-[9px] font-black"
                                                      :class="{
                                                          'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400': log.transition_type === 'AUTO',
                                                          'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400': log.transition_type === 'MANUAL',
                                                          'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': log.transition_type === 'GOBACK'
                                                      }"
                                                      x-text="log.transition_type === 'GOBACK' ? 'بازگشت' : (log.transition_type === 'AUTO' ? 'سیستمی' : 'دستی')">
                                                </span>
                                            </div>
                                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400" dir="ltr" x-text="log.run_at"></div>
                                        </div>
                                        <div class="text-xs text-slate-600 dark:text-slate-300 font-medium flex items-center flex-wrap gap-2 leading-relaxed">
                                            <span>انتقال از مرحله:</span>
                                            <span class="font-black bg-white dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700" x-text="getNodeNameForHistory(log.from_node_id)"></span>
                                            <svg class="w-3.5 h-3.5 text-slate-400 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                            <span>به مرحله:</span>
                                            <span class="font-black bg-white dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700" x-text="getNodeNameForHistory(log.to_node_id)"></span>
                                        </div>
                                    </div>
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
                toothSystem: existingPlan?.tooth_numbering_system || 'palmer',
                getToothLabel(id) {
                    const palmerMap = {1:{num:7,pos:'UR'},2:{num:6,pos:'UR'},3:{num:5,pos:'UR'},4:{num:4,pos:'UR'},5:{num:3,pos:'UR'},6:{num:2,pos:'UR'},7:{num:1,pos:'UR'},8:{num:1,pos:'UL'},9:{num:2,pos:'UL'},10:{num:3,pos:'UL'},11:{num:4,pos:'UL'},12:{num:5,pos:'UL'},13:{num:6,pos:'UL'},14:{num:7,pos:'UL'},15:{num:7,pos:'LR'},16:{num:6,pos:'LR'},17:{num:5,pos:'LR'},18:{num:4,pos:'LR'},19:{num:3,pos:'LR'},20:{num:2,pos:'LR'},21:{num:1,pos:'LR'},22:{num:1,pos:'LL'},23:{num:2,pos:'LL'},24:{num:3,pos:'LL'},25:{num:4,pos:'LL'},26:{num:5,pos:'LL'},27:{num:6,pos:'LL'},28:{num:7,pos:'LL'}};
                    const fdiMap = {1:{num:17,pos:'UR'},2:{num:16,pos:'UR'},3:{num:15,pos:'UR'},4:{num:14,pos:'UR'},5:{num:13,pos:'UR'},6:{num:12,pos:'UR'},7:{num:11,pos:'UR'},8:{num:21,pos:'UL'},9:{num:22,pos:'UL'},10:{num:23,pos:'UL'},11:{num:24,pos:'UL'},12:{num:25,pos:'UL'},13:{num:26,pos:'UL'},14:{num:27,pos:'UL'},15:{num:47,pos:'LR'},16:{num:46,pos:'LR'},17:{num:45,pos:'LR'},18:{num:44,pos:'LR'},19:{num:43,pos:'LR'},20:{num:42,pos:'LR'},21:{num:41,pos:'LR'},22:{num:31,pos:'LL'},23:{num:32,pos:'LL'},24:{num:33,pos:'LL'},25:{num:34,pos:'LL'},26:{num:35,pos:'LL'},27:{num:36,pos:'LL'},28:{num:37,pos:'LL'}};
                    return (this.toothSystem === 'fdi' ? fdiMap : palmerMap)[id] ?? { num: id, pos: 'UR' };
                },
                workflows: existingPlan ? (existingPlan.workflows || []) : [],
                selectedWidgetNodes: {},
                collapsedInstances: {},
                pollingInterval: null,
                selectedToothFilter: new URLSearchParams(window.location.search).get('tooth') || 'all',
                selectedStatusFilter: 'all',

                get filteredWorkflows() {
                    return this.workflows.filter(w => {
                        const toothMatch = this.selectedToothFilter === 'all' || 
                            (this.selectedToothFilter === 'none' && !w.tooth_context) ||
                            (w.tooth_context == this.selectedToothFilter);
                        const statusMatch = this.selectedStatusFilter === 'all' || 
                            w.status === this.selectedStatusFilter;
                        return toothMatch && statusMatch;
                    });
                },

                get uniqueTeeth() {
                    const teeth = new Set();
                    this.workflows.forEach(w => {
                        if (w.tooth_context) {
                            teeth.add(w.tooth_context);
                        }
                    });
                    return Array.from(teeth).sort();
                },
                
                // History Modal State
                historyModalOpen: false,
                activeHistoryLogs: [],
                activeWorkflowForHistory: null,
                
                // Pan and Zoom State
                canvasStates: {},
                isDragging: false,
                currentDragId: null,
                startX: 0,
                startY: 0,
                
                initCanvasState(winstId) {
                    if (!this.canvasStates[winstId]) {
                        this.canvasStates[winstId] = {
                            scale: 1,
                            panX: 0,
                            panY: 0
                        };
                    }
                },
                
                // --- Pan Logic ---
                startDrag(e, winstId) {
                    // Only start drag if left mouse button is pressed and we're not clicking on a node (event propagation stopped on nodes)
                    if (e.button !== 0) return;
                    this.isDragging = true;
                    this.currentDragId = winstId;
                    this.startX = e.clientX - this.canvasStates[winstId].panX;
                    this.startY = e.clientY - this.canvasStates[winstId].panY;
                },
                doDrag(e) {
                    if (!this.isDragging || !this.currentDragId) return;
                    
                    // Use requestAnimationFrame for smoother rendering
                    window.requestAnimationFrame(() => {
                        if (!this.isDragging) return;
                        this.canvasStates[this.currentDragId].panX = e.clientX - this.startX;
                        this.canvasStates[this.currentDragId].panY = e.clientY - this.startY;
                    });
                },
                stopDrag() {
                    this.isDragging = false;
                    this.currentDragId = null;
                },
                
                // --- Zoom Logic ---
                doZoom(e, winstId) {
                    const zoomSensitivity = 0.001;
                    const delta = -e.deltaY * zoomSensitivity;
                    this._applyZoom(winstId, delta, e.clientX, e.clientY);
                },
                zoomIn(winstId) {
                    this._applyZoomCenter(winstId, 0.15);
                },
                zoomOut(winstId) {
                    this._applyZoomCenter(winstId, -0.15);
                },
                resetZoom(winstId) {
                    this.canvasStates[winstId].scale = 1;
                    this.canvasStates[winstId].panX = 0;
                    this.canvasStates[winstId].panY = 0;
                },
                
                _applyZoomCenter(winstId, delta) {
                    // Approximate center zoom by using a fixed center offset
                    const state = this.canvasStates[winstId];
                    let newScale = state.scale + delta;
                    newScale = Math.min(Math.max(0.3, newScale), 3); // min 30%, max 300%
                    
                    if (newScale !== state.scale) {
                        // Very basic center approximation for button zooms
                        const scaleRatio = newScale / state.scale;
                        state.panX = state.panX * scaleRatio;
                        state.panY = state.panY * scaleRatio;
                        state.scale = newScale;
                    }
                },
                
                _applyZoom(winstId, delta, mouseX, mouseY) {
                    const state = this.canvasStates[winstId];
                    let newScale = state.scale + delta;
                    newScale = Math.min(Math.max(0.3, newScale), 3); // min 30%, max 300%

                    if (newScale !== state.scale) {
                        const scaleRatio = newScale / state.scale;
                        
                        // We need to keep the point under the mouse at the same screen position
                        // To do this mathematically, the new pan is:
                        // newPan = mousePos - (mousePos - oldPan) * scaleRatio
                        
                        // Since we don't have the exact bounding box of the canvas readily available in this event,
                        // this is a simplified calculation that works well for relative positioning.
                        // For perfectly accurate zoom-to-mouse, we'd need container bounds.
                        
                        state.panX = mouseX - (mouseX - state.panX) * scaleRatio;
                        state.panY = mouseY - (mouseY - state.panY) * scaleRatio;
                        state.scale = newScale;
                    }
                },

                // --- Workflow Actions ---
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
                async goBackInstance(id) {
                    if (!confirm('آیا از بازگشت به مرحله قبل اطمینان دارید؟ توجه کنید وظایف فعلی لغو خواهند شد.')) return;
                    try {
                        const res = await fetch(`/user/workflows/instances/${id}/go-back`, {
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
                            showToast(data.message || 'خطا در بازگشت به مرحله قبل', 'error');
                        }
                    } catch (e) {
                        showToast('خطا در اتصال به سرور', 'error');
                    }
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
                openHistoryModal(winstId) {
                    const winst = this.workflows.find(w => w.id === winstId);
                    if (winst) {
                        this.activeHistoryLogs = winst.logs || [];
                        this.activeWorkflowForHistory = winst;
                        this.historyModalOpen = true;
                    }
                },
                getNodeNameForHistory(nodeId) {
                    if (!nodeId) return 'شروع';
                    if (!this.activeWorkflowForHistory || !this.activeWorkflowForHistory.nodes) return nodeId;
                    const node = this.activeWorkflowForHistory.nodes.find(n => String(n.id) === String(nodeId));
                    return node ? node.name : nodeId;
                },
                getNodeIcon(type) {
                    switch(type) {
                        case 'START': return '<svg class="w-full h-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                        case 'END': return '<svg class="w-full h-full p-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
                        case 'ACTION': return '<svg class="w-full h-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
                        case 'CONDITION': return '<svg class="w-full h-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>';
                        default: return '<svg class="w-full h-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
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
                getNodeColorClass(type) {
                    switch(type) {
                        case 'START': return 'bg-emerald-500 shadow-md shadow-emerald-500/30';
                        case 'END': return 'bg-rose-500 shadow-md shadow-rose-500/30';
                        case 'ACTION': return 'bg-indigo-500 shadow-md shadow-indigo-500/30';
                        case 'CONDITION': return 'bg-amber-500 shadow-md shadow-amber-500/30';
                        case 'SUB_WORKFLOW': return 'bg-purple-500 shadow-md shadow-purple-500/30';
                        default: return 'bg-slate-500 shadow-md shadow-slate-500/30';
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
                    const paddingX = 120;
                    const paddingY = 100;
                    const computedMinX = minX - paddingX;
                    const computedMinY = minY - paddingY;
                    const computedWidth = (maxX - minX) + 224 + paddingX * 2; // w-56 = 224px
                    const computedHeight = (maxY - minY) + 80 + paddingY * 2;
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
                            : 'stroke-slate-300 dark:stroke-slate-700';
                        const markerEnd = isActive ? 'url(#mini-arrow-selected)' : 'url(#mini-arrow)';
                        html += `<path d="${pathData}" stroke-width="2.5" fill="none" class="transition-colors duration-300 ${className}" marker-end="${markerEnd}"></path>`;
                    });
                    return html;
                },
                getMiniBezierPath(edge, nodes) {
                    const source = nodes.find(n => n.id === edge.source_id);
                    const target = nodes.find(n => n.id === edge.target_id);
                    if (!source || !target) return '';
                    const x1 = (Number(source.config?.x) || 0) + 112; // w-56 = 224 / 2 = 112 center
                    const y1 = (Number(source.config?.y) || 0) + 80; // approx 80 height
                    const x2 = (Number(target.config?.x) || 0) + 112;
                    const y2 = (Number(target.config?.y) || 0);
                    const controlY = y1 + (y2 - y1) / 2;
                    return `M ${x1} ${y1} C ${x1} ${controlY}, ${x2} ${controlY}, ${x2} ${y2}`;
                },
                isEdgeConnectedToActiveNode(edge, winst) {
                    const currentNode = winst.nodes?.find(n => n.id === winst.current_node_id);
                    if (currentNode && currentNode.type === 'START') {
                        return edge.source_id === winst.current_node_id;
                    }
                    return edge.target_id === winst.current_node_id;
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

                                    // Initialize pan/zoom states for new workflows
                                    this.initCanvasState(inst.id);

                                    // If no previous selection, or if workflow current_node_id has changed,
                                    // or if the previously selected node was the active node:
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
                    // Preselect active node for all workflows and initialize collapsing and states
                    const newSelected = {};
                    this.workflows.forEach(inst => {
                        this.initCanvasState(inst.id);
                        
                        const activeNode = inst.nodes?.find(n => n.id === inst.current_node_id);
                        if (activeNode) {
                            newSelected[inst.id] = activeNode;
                            
                            // Optional: auto-center on active node initially.
                            // To keep it simple, we let the user pan, or start at 0,0.
                            // We start at 0,0 by default as the canvas geometry already accounts for layout bounds.

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
