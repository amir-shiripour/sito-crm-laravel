@php
    $user = auth()->user();
    $canEdit = $user && ($user->can('workflows.edit') || $user->hasRole('super-admin'));
    $canManage = $user && ($user->can('workflows.manage') || $user->hasRole('super-admin'));
@endphp

<div id="patient-journey-canvas-widget" 
     x-data="patientJourneyCanvasWidget()" 
     class="w-full bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-800/50 shadow-lg rounded-3xl p-6 transition-all duration-300 relative overflow-hidden"
     lang="fa" 
     dir="rtl">

    <!-- background gradient glow -->
    <div class="absolute -top-40 -left-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md shadow-indigo-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100">بوم مسیر گردش‌کار بیماران</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    به‌روزرسانی خودکار و آنلاین فعال است
                </p>
            </div>
        </div>

        <!-- Header Filters & Refresh -->
        <div class="flex items-center gap-2 self-end md:self-auto">
            <button @click="loadData(false)" 
                    :disabled="loading"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    title="به‌روزرسانی">
                <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <!-- Active -->
        <div @click="filters.status = 'ACTIVE'; filters.page = 1; loadData()" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200 flex items-center justify-between"
             :class="filters.status === 'ACTIVE' 
                ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900 shadow-sm ring-1 ring-indigo-500/20' 
                : 'bg-slate-50/30 dark:bg-slate-900/30 border-slate-200/60 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-900'">
            <div class="space-y-1">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">گردش‌کارهای فعال</span>
                <h4 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums" x-text="stats.active">0</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <!-- Completed -->
        <div @click="filters.status = 'COMPLETED'; filters.page = 1; loadData()" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200 flex items-center justify-between"
             :class="filters.status === 'COMPLETED' 
                ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900 shadow-sm ring-1 ring-emerald-500/20' 
                : 'bg-slate-50/30 dark:bg-slate-900/30 border-slate-200/60 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-900'">
            <div class="space-y-1">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">تکمیل شده</span>
                <h4 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums" x-text="stats.completed">0</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Canceled -->
        <div @click="filters.status = 'CANCELED'; filters.page = 1; loadData()" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200 flex items-center justify-between col-span-2 md:col-span-1"
             :class="filters.status === 'CANCELED' 
                ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900 shadow-sm ring-1 ring-rose-500/20' 
                : 'bg-slate-50/30 dark:bg-slate-900/30 border-slate-200/60 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-900'">
            <div class="space-y-1">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">لغو شده</span>
                <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 tabular-nums" x-text="stats.canceled">0</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Interactive Filters Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6 bg-slate-50/50 dark:bg-slate-900/20 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
        <!-- Search bar -->
        <div class="relative">
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" 
                   x-model="filters.q" 
                   @input.debounce.400ms="filters.page = 1; loadData()" 
                   placeholder="جستجوی نام، تلفن، کدملی یا پرونده..." 
                   class="w-full pr-9 pl-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" />
        </div>

        <!-- Workflow dropdown selector -->
        <div>
            <select x-model="filters.workflow_id" 
                    @change="filters.page = 1; loadData()"
                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                <option value="">همه گردش‌کارها</option>
                <template x-for="wf in workflows" :key="wf.id">
                    <option :value="wf.id" x-text="wf.name"></option>
                </template>
            </select>
        </div>

        <!-- Status Filter Select -->
        <div>
            <select x-model="filters.status" 
                    @change="filters.page = 1; loadData()"
                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                <option value="ACTIVE">فقط فعال</option>
                <option value="COMPLETED">فقط تکمیل شده</option>
                <option value="CANCELED">فقط لغو شده</option>
            </select>
        </div>
    </div>

    <!-- Content Area -->
    <div class="relative min-h-[250px]">
        <!-- Loading State Shimmer -->
        <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-[1px] flex items-center justify-center z-20 rounded-2xl">
            <div class="flex flex-col items-center gap-3">
                <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">در حال دریافت اطلاعات...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="instances.length === 0 && !loading" class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800/50 text-slate-400 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2M9 5h6" />
                </svg>
            </div>
            <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300">هیچ گردش‌کاری یافت نشد</h5>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-[280px]">هیچ گردش‌کاری متناسب با فیلترها و جستجوی فعلی یافت نشد.</p>
        </div>

        <!-- Instances List -->
        <div x-show="instances.length > 0" class="space-y-3.5">
            <template x-for="inst in instances" :key="inst.id">
                <div class="border rounded-2xl transition-all duration-300"
                     :class="expandedInstanceId === inst.id 
                        ? 'border-indigo-200 dark:border-indigo-950 bg-indigo-50/10 dark:bg-slate-900/40 shadow-sm' 
                        : 'border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-900/20'">
                    
                    <!-- Row Summary -->
                    <div @click="toggleExpand(inst.id)" 
                         class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer select-none">
                        
                        <!-- Client Info & Avatar -->
                        <div class="flex items-center gap-3 min-w-[200px]">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white"
                                 :class="getClientBg(inst)">
                                <span x-text="getClientInitials(inst)"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="inst.client ? inst.client.full_name : 'بیمار ثبت نشده'"></span>
                                    <template x-if="inst.client && inst.client.case_number">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="'پرونده: ' + inst.client.case_number"></span>
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5 tabular-nums" x-text="inst.client ? inst.client.phone : '—'"></span>
                            </div>
                        </div>

                        <!-- Workflow & Node Status -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between md:justify-start gap-4 mb-2">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="inst.workflow_name"></span>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold"
                                      :class="getNodeTypeBadgeClass(inst.current_node_type)"
                                      x-text="getNodeTypeLabel(inst.current_node_type)"></span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full max-w-[250px] flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-l from-indigo-500 to-purple-500 transition-all duration-500"
                                         :style="'width: ' + getProgressPercent(inst) + '%'"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 tabular-nums" x-text="getProgressText(inst)"></span>
                            </div>
                        </div>

                        <!-- Action Metadata & Expand Chevron -->
                        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-none pt-3 md:pt-0 border-slate-100 dark:border-slate-800/85">
                            <div class="text-left md:text-right">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">گام فعلی:</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="inst.current_node_name || 'پایان فرآیند'"></span>
                            </div>

                            <div class="p-1 rounded-lg border border-slate-200/50 dark:border-slate-800/50 text-slate-400 bg-slate-50 dark:bg-slate-900 transition-transform duration-300"
                                 :class="expandedInstanceId === inst.id ? 'rotate-180 text-indigo-500 border-indigo-200' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Row Expandable Details -->
                    <div x-show="expandedInstanceId === inst.id" 
                         x-collapse
                         class="border-t border-slate-100 dark:border-indigo-950/30 p-5 space-y-6">
                        
                        <!-- Client Full Card (For identity confirmation) -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">کد ملی بیمار:</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 tabular-nums" x-text="inst.client && inst.client.national_code ? inst.client.national_code : 'ثبت نشده'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">شماره پرونده:</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 tabular-nums" x-text="inst.client && inst.client.case_number ? inst.client.case_number : 'بدون شماره'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">شماره موبایل:</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 tabular-nums" x-text="inst.client ? inst.client.phone : '—'"></span>
                            </div>
                            <div class="flex items-end justify-start md:justify-end">
                                <template x-if="inst.client">
                                    <a :href="'/user/clients/' + inst.client.id" 
                                       class="inline-flex items-center gap-1 text-[10px] font-black text-indigo-500 hover:text-indigo-600 transition-colors">
                                        مشاهده پرونده کامل
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- 1. Visual Stepper Timeline -->
                        <div class="py-4 overflow-x-auto">
                            <div class="min-w-[650px] flex items-center justify-between relative px-6">
                                <!-- Background connecting line -->
                                <div class="absolute top-[21px] left-8 right-8 h-[2px] bg-slate-100 dark:bg-slate-800 z-0"></div>

                                <template x-for="(step, idx) in getStepperPath(inst)" :key="step.id">
                                    <div class="flex flex-col items-center text-center relative z-10 flex-1">
                                        <!-- Node icon / indicator -->
                                        <div class="w-11 h-11 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                             :class="step.status === 'completed' 
                                                ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/10' 
                                                : step.status === 'active' 
                                                    ? 'bg-indigo-600 border-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-md shadow-indigo-600/20 animate-pulse' 
                                                    : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-400'">
                                            
                                            <!-- Completed Tick -->
                                            <template x-if="step.status === 'completed'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>

                                            <!-- Active Pulsing -->
                                            <template x-if="step.status === 'active'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </template>

                                            <!-- Future dashed border -->
                                            <template x-if="step.status === 'next'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Node detail labels -->
                                        <div class="mt-2.5 max-w-[120px]">
                                            <span class="text-xs font-bold block"
                                                  :class="step.status === 'active' ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300'"
                                                  x-text="step.name"></span>
                                            
                                            <!-- Completed Metadata -->
                                            <template x-if="step.status === 'completed'">
                                                <div class="mt-1">
                                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 block truncate" :title="step.user" x-text="step.user"></span>
                                                    <span class="text-[8px] text-slate-400 dark:text-slate-500 block mt-0.5 tabular-nums" x-text="step.date"></span>
                                                </div>
                                            </template>

                                            <!-- Condition indicator -->
                                            <template x-if="step.status === 'next' && step.condition">
                                                <span class="mt-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[8px] font-black text-slate-500 dark:text-slate-400 truncate block"
                                                      :title="'در صورت: ' + step.condition" 
                                                      x-text="step.condition"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 2. Active step detail interaction -->
                        <template x-if="inst.status === 'ACTIVE'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 rounded-2xl border border-indigo-100/50 dark:border-indigo-950/20 bg-indigo-50/5 dark:bg-indigo-950/5">
                                
                                <!-- Tasks List (Left / Right Column depending on RTL) -->
                                <div class="space-y-4">
                                    <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                                        </svg>
                                        کارهای گام فعال
                                    </h4>

                                    <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                                        <template x-for="task in getActiveTasks(inst)" :key="task.id">
                                            <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900 transition-all duration-200"
                                                 :class="task.status === 'DONE' ? 'opacity-60 bg-slate-50/50 dark:bg-slate-900/50' : ''">
                                                
                                                <input type="checkbox" 
                                                       :id="'task-' + task.id"
                                                       :checked="task.status === 'DONE'"
                                                       @change="toggleTaskStatus(task.id)"
                                                       :disabled="!canEdit"
                                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500/20 mt-0.5 cursor-pointer disabled:opacity-50" />
                                                
                                                <div class="flex-1">
                                                    <label :for="'task-' + task.id" 
                                                            class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer"
                                                           :class="task.status === 'DONE' ? 'line-through text-slate-400 dark:text-slate-500' : ''"
                                                           x-text="task.title"></label>
                                                    <div class="flex items-center gap-3 mt-1 text-[9px] text-slate-400 dark:text-slate-500">
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                            مسئول: <span x-text="task.assignee_name"></span>
                                                        </span>
                                                        <template x-if="task.auto_advance === false || task.auto_advance === 'false'">
                                                            <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 font-black">نیاز به تایید دستی</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <!-- No tasks placeholder -->
                                        <template x-if="getActiveTasks(inst).length === 0">
                                            <div class="text-center py-6 text-slate-400 dark:text-slate-500">
                                                <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-xs font-medium">هیچ کاری برای این مرحله تعریف نشده است.</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Condition Choice Block / Advance confirmation -->
                                <div class="flex flex-col justify-between border-t md:border-t-0 md:border-r border-slate-100 dark:border-slate-800 pt-4 md:pt-0 md:pr-6">
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l3 3-3 3m5 0h3" />
                                            </svg>
                                            عملیات گام فعال
                                        </h4>

                                        <!-- Render Condition choice buttons -->
                                        <template x-if="inst.current_node_type === 'CONDITION'">
                                            <div class="space-y-3.5 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/85">
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    شرط فعلی: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="inst.current_node_name"></span>
                                                </div>
                                                <p class="text-[11px] font-bold text-purple-600 dark:text-purple-400">گزینه خروجی مورد نظر را انتخاب کنید تا پرونده به گام متناظر منتقل شود:</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="opt in getConditionOptions(inst)" :key="opt.label">
                                                        <button @click="advanceWithChoice(inst.id, getConditionVar(inst), opt.value)"
                                                                :disabled="actionLoading || !canEdit"
                                                                class="px-4 py-2 text-xs font-black rounded-xl text-white shadow-sm transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50"
                                                                :class="opt.value === 0 
                                                                    ? 'bg-gradient-to-br from-rose-500 to-red-600 hover:shadow-red-500/10' 
                                                                    : 'bg-gradient-to-br from-emerald-500 to-teal-600 hover:shadow-emerald-500/10'"
                                                                x-text="opt.label"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Render Manual Confirm buttons (when auto-advance is disabled and tasks are completed) -->
                                        <template x-if="inst.current_node_type !== 'CONDITION'">
                                            <div class="space-y-3">
                                                <template x-if="!isCurrentNodeAutoAdvance(inst)">
                                                    <div class="p-3 bg-amber-50/40 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-950/20 rounded-xl text-[10px] text-amber-700 dark:text-amber-400 font-bold leading-relaxed">
                                                        توجه: انتقال خودکار برای این گام غیرفعال است. جهت عبور به گام بعدی، پس از انجام کارها، روی دکمه تایید دستی کلیک کنید.
                                                    </div>
                                                </template>

                                                <!-- Confirm & Go to Next step (always show for manual advance steps) -->
                                                <template x-if="!isCurrentNodeAutoAdvance(inst)">
                                                    <button @click="advance(inst.id)"
                                                            :disabled="hasPendingTasks(inst) || actionLoading || !canEdit"
                                                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-black rounded-xl bg-gradient-to-l from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white shadow-md shadow-indigo-500/10 transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:transform-none disabled:shadow-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                        </svg>
                                                        تایید و رفتن به گام بعد
                                                    </button>
                                                </template>

                                                <template x-if="isCurrentNodeAutoAdvance(inst) && hasPendingTasks(inst)">
                                                    <div class="p-3 bg-indigo-50/20 dark:bg-slate-900/40 border border-indigo-100/50 dark:border-slate-800 rounded-xl text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                                        ⏳ منتظر انجام کارهای بالا هستیم. پس از تیک خوردن تمام کارهای گام، فرآیند به صورت خودکار به مرحله بعد هدایت خواهد شد.
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Navigation Action Buttons (Restart, Cancel, Back) -->
                                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                                        <button @click="goBack(inst.id)"
                                                :disabled="actionLoading || !canEdit"
                                                class="flex-1 px-3 py-2 text-[10px] font-black rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors disabled:opacity-50">
                                            بازگشت به گام قبل
                                        </button>
                                        
                                        <button @click="cancelInstance(inst.id)"
                                                :disabled="actionLoading || !canManage"
                                                 class="px-3 py-2 text-[10px] font-black rounded-xl border border-rose-200/50 text-rose-500 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/10 transition-colors disabled:opacity-50">
                                            لغو گردش‌کار
                                        </button>

                                        <button @click="restartInstance(inst.id)"
                                                :disabled="actionLoading || !canManage"
                                                class="px-3 py-2 text-[10px] font-black rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors disabled:opacity-50">
                                            راه‌اندازی مجدد
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- History Log Trail (Audit Log) -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                تاریخچه و ردگیری مراحل
                            </h4>

                            <div class="space-y-2 pl-2">
                                <template x-for="log in inst.logs" :key="log.id">
                                    <div class="flex items-start gap-3 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mt-1.5"></div>
                                        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between gap-1">
                                            <div>
                                                انتقال به گام: 
                                                <span class="font-extrabold text-slate-800 dark:text-slate-300" x-text="getNodeName(inst, log.to_node_id)"></span>
                                                <template x-if="log.from_node_id">
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500" x-text="'(از: ' + getNodeName(inst, log.from_node_id) + ')'"></span>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-3 text-[10px] text-slate-400 dark:text-slate-500">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    توسط: <span class="font-bold" x-text="log.user_name"></span>
                                                </span>
                                                <span class="tabular-nums" x-text="formatDate(log.run_at)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="inst.logs.length === 0">
                                    <span class="text-xs text-slate-400 dark:text-slate-500">هیچ تاریخچه‌ای ثبت نشده است.</span>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination Controls -->
        <div x-show="pagination.last_page > 1 && !loading" class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 mt-6 pt-4">
            <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 tabular-nums">
                نمایش صفحه <span x-text="pagination.current_page"></span> از <span x-text="pagination.last_page"></span> (کل موارد: <span x-text="pagination.total"></span>)
            </div>
            
            <div class="flex items-center gap-1">
                <!-- Prev Button -->
                <button @click="filters.page--; loadData()"
                        :disabled="pagination.current_page === 1"
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                
                <!-- Next Button -->
                <button @click="filters.page++; loadData()"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function patientJourneyCanvasWidget() {
        return {
            loading: false,
            actionLoading: false,
            instances: [],
            stats: { active: 0, completed: 0, canceled: 0 },
            workflows: [],
            filters: {
                q: '',
                workflow_id: '',
                status: 'ACTIVE',
                page: 1
            },
            pagination: {
                total: 0,
                per_page: 10,
                current_page: 1,
                last_page: 1
            },
            expandedInstanceId: null,
            pollingInterval: null,
            canEdit: {{ $canEdit ? 'true' : 'false' }},
            canManage: {{ $canManage ? 'true' : 'false' }},

            init() {
                this.loadData();
                // Setup silent polling every 15 seconds to make it real-time
                this.pollingInterval = setInterval(() => {
                    this.loadData(true);
                }, 15000);
            },

            destroy() {
                if (this.pollingInterval) {
                    clearInterval(this.pollingInterval);
                }
            },

            loadData(silent = false) {
                if (!silent) this.loading = true;
                
                const url = `/user/workflows/canvas-data?q=${encodeURIComponent(this.filters.q)}&workflow_id=${this.filters.workflow_id}&status=${this.filters.status}&page=${this.filters.page}`;
                
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.instances = data.instances;
                        this.stats = data.stats;
                        this.workflows = data.workflows;
                        this.pagination = data.pagination;
                    }
                })
                .catch(err => console.error(err))
                .finally(() => {
                    this.loading = false;
                });
            },

            toggleExpand(id) {
                this.expandedInstanceId = this.expandedInstanceId === id ? null : id;
            },

            getClientInitials(inst) {
                if (!inst.client) return '—';
                const parts = inst.client.full_name.trim().split(/\s+/);
                return parts.map(p => p[0]).slice(0, 2).join('');
            },

            getClientBg(inst) {
                if (!inst.client) return 'bg-slate-400';
                const id = inst.client.id;
                const colors = [
                    'bg-gradient-to-br from-indigo-500 to-purple-600',
                    'bg-gradient-to-br from-emerald-500 to-teal-600',
                    'bg-gradient-to-br from-amber-500 to-orange-600',
                    'bg-gradient-to-br from-rose-500 to-red-600',
                    'bg-gradient-to-br from-cyan-500 to-blue-600',
                    'bg-gradient-to-br from-purple-500 to-pink-600'
                ];
                return colors[id % colors.length];
            },

            getNodeTypeLabel(type) {
                if (type === 'START') return 'شروع';
                if (type === 'END') return 'پایان';
                if (type === 'ACTION') return 'اقدام / وظیفه';
                if (type === 'CONDITION') return 'بررسی شرط';
                if (type === 'SUB_WORKFLOW') return 'زیر فرآیند';
                return type || 'نامشخص';
            },

            getNodeTypeBadgeClass(type) {
                if (type === 'START') return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200/30';
                if (type === 'END') return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                if (type === 'ACTION') return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 border border-indigo-200/30';
                if (type === 'CONDITION') return 'bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400 border border-purple-200/30';
                return 'bg-slate-50 text-slate-500 dark:bg-slate-900 dark:text-slate-400';
            },

            getProgressPercent(inst) {
                const total = (inst.nodes || []).filter(n => n.type !== 'START' && n.type !== 'END').length;
                if (total === 0) return 100;
                
                const stepper = this.getStepperPath(inst);
                const completed = stepper.filter(s => s.status === 'completed').length;
                
                return Math.min(100, Math.round((completed / total) * 100));
            },

            getProgressText(inst) {
                const total = (inst.nodes || []).filter(n => n.type !== 'START' && n.type !== 'END').length;
                const stepper = this.getStepperPath(inst);
                const completed = stepper.filter(s => s.status === 'completed').length;
                return completed + ' / ' + total;
            },

            getStepperPath(inst) {
                const path = [];
                const visitedIds = new Set();
                
                // Chronological order (reverse of inst.logs since backend returns desc)
                const chronologicalLogs = [...(inst.logs || [])].reverse();
                chronologicalLogs.forEach(log => {
                    if (log.to_node_id && log.to_node_id !== inst.current_node_id && !visitedIds.has(log.to_node_id)) {
                        const node = (inst.nodes || []).find(n => String(n.id) === String(log.to_node_id));
                        if (node && node.type !== 'START' && node.type !== 'END') {
                            path.push({
                                id: node.id,
                                name: node.name,
                                type: node.type,
                                status: 'completed',
                                date: this.formatDate(log.run_at),
                                user: log.user_name
                            });
                            visitedIds.add(node.id);
                        }
                    }
                });

                // Current active node
                if (inst.current_node_id) {
                    const currNode = (inst.nodes || []).find(n => String(n.id) === String(inst.current_node_id));
                    if (currNode && currNode.type !== 'START' && currNode.type !== 'END') {
                        path.push({
                            id: currNode.id,
                            name: currNode.name,
                            type: currNode.type,
                            status: 'active'
                        });
                        visitedIds.add(currNode.id);
                    }
                }

                // Next potential nodes
                if (inst.current_node_id && inst.status === 'ACTIVE') {
                    const nextEdges = (inst.edges || []).filter(e => String(e.source_node_id) === String(inst.current_node_id));
                    nextEdges.forEach(edge => {
                        const targetNode = (inst.nodes || []).find(n => String(n.id) === String(edge.target_node_id));
                        if (targetNode && !visitedIds.has(targetNode.id)) {
                            path.push({
                                id: targetNode.id,
                                name: targetNode.name,
                                type: targetNode.type,
                                status: 'next',
                                condition: edge.condition
                            });
                        }
                    });
                }
                
                return path;
            },

            formatDate(isoString) {
                if (!isoString) return '—';
                try {
                    const d = new Date(isoString);
                    return d.toLocaleDateString('fa-IR') + ' ' + d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
                } catch(e) {
                    return isoString;
                }
            },

            getActiveTasks(inst) {
                return (inst.tasks || []).filter(t => String(t.workflow_node_id) === String(inst.current_node_id));
            },

            hasPendingTasks(inst) {
                return this.getActiveTasks(inst).some(t => t.status !== 'DONE');
            },

            isCurrentNodeAutoAdvance(inst) {
                const currentNode = (inst.nodes || []).find(n => String(n.id) === String(inst.current_node_id));
                if (currentNode && currentNode.config) {
                    const nodeAuto = currentNode.config.auto_advance !== false && currentNode.config.auto_advance !== 'false' && currentNode.config.auto_advance !== 0 && currentNode.config.auto_advance !== '0';
                    if (!nodeAuto) return false;
                }
                const currentTasks = this.getActiveTasks(inst);
                if (currentTasks.some(t => t.auto_advance === false || t.auto_advance === 'false' || t.auto_advance === 0 || t.auto_advance === '0')) {
                    return false;
                }
                return true;
            },

            getConditionVar(inst) {
                const expr = inst.current_node_expression || '';
                if (expr.includes('=')) {
                    return expr.split('=')[0].trim();
                }
                return 'condition_result';
            },

            getConditionOptions(inst) {
                const currentNode = inst.currentNode;
                if (!currentNode || currentNode.type !== 'CONDITION') return [];
                const edges = (inst.edges || []).filter(e => String(e.source_node_id) === String(currentNode.id));
                if (edges.length === 0) {
                    return [
                        { label: 'بله / تایید', value: 1 },
                        { label: 'خیر / رد', value: 0 }
                    ];
                }
                return edges.map(e => {
                    const label = e.condition || 'انتخاب';
                    let value = 1;
                    const norm = label.trim().toLowerCase();
                    if (norm === 'خیر' || norm === 'no' || norm === 'false' || norm === '0' || norm === 'رد') {
                        value = 0;
                    }
                    return { label: label, value: value };
                });
            },

            getNodeName(inst, nodeId) {
                const node = (inst.nodes || []).find(n => String(n.id) === String(nodeId));
                if (node) return node.name;
                const currentNode = inst.currentNode;
                if (currentNode && String(currentNode.id) === String(nodeId)) {
                    return currentNode.name;
                }
                return 'شناسه ' + nodeId;
            },

            notify(type, message) {
                // Flash alert fallback / standard notify helper
                if (window.toastr) {
                    window.toastr[type](message);
                } else {
                    alert(message);
                }
            },

            async toggleTaskStatus(taskId) {
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch(`/user/workflows/tasks/${taskId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'وضعیت کار به روز شد.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در ثبت وضعیت کار');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            },

            async advance(instanceId) {
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch(`/user/workflows/instances/${instanceId}/advance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'گردش‌کار با موفقیت به گام بعد هدایت شد.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در تایید مرحله');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            },

            async advanceWithChoice(instanceId, varName, value) {
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {};
                payload[varName] = value;
                try {
                    const res = await fetch(`/user/workflows/instances/${instanceId}/advance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'تصمیم شرط با موفقیت ثبت شد.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در ثبت پاسخ شرط');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            },

            async goBack(instanceId) {
                if (!confirm('آیا از بازگشت به مرحله قبل اطمینان دارید؟')) return;
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch(`/user/workflows/instances/${instanceId}/go-back`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'یک مرحله به عقب بازگشتید.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در بازگشت به مرحله قبل');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            },

            async cancelInstance(instanceId) {
                if (!confirm('آیا از لغو این گردش‌کار اطمینان دارید؟')) return;
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch(`/user/workflows/instances/${instanceId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'گردش‌کار با موفقیت لغو شد.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در لغو فرآیند');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            },

            async restartInstance(instanceId) {
                if (!confirm('آیا از شروع مجدد این گردش‌کار اطمینان دارید؟ تمام داده‌های قبلی لغو شده و فرآیند جدیدی ساخته خواهد شد.')) return;
                this.actionLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch(`/user/workflows/instances/${instanceId}/restart`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.notify('success', 'گردش‌کار مجدداً راه‌اندازی شد.');
                        this.loadData(true);
                    } else {
                        this.notify('error', data.message || 'خطا در راه‌اندازی مجدد');
                    }
                } catch(e) {
                    console.error(e);
                    this.notify('error', 'ارتباط با سرور برقرار نشد.');
                } finally {
                    this.actionLoading = false;
                }
            }
        };
    }
</script>
