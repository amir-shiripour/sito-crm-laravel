<div class="h-full flex flex-col space-y-8" dir="rtl">
    
    <!-- Hero / Welcome Section -->
    <div class="bg-gradient-to-br from-indigo-600 via-blue-600 to-indigo-800 rounded-[2rem] p-8 shadow-xl shadow-indigo-500/20 relative overflow-hidden flex-shrink-0 animate-fade-in-up">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white/10 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-64 h-64 bg-indigo-900/40 blur-3xl rounded-full"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-white mb-2 drop-shadow-sm tracking-tight">سلام {{ auth()->user()->name }} 👋</h1>
                <p class="text-indigo-100 font-medium text-sm">امروز {{ \Morilog\Jalali\Jalalian::now()->format('%A، %d %B %Y') }} است. بیایید یک روز پرفروش دیگر بسازیم!</p>
            </div>
            
            @if(count($goalsProgress) > 0)
                <div class="flex gap-4">
                    @foreach(array_slice($goalsProgress, 0, 2) as $gp)
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 w-48 shadow-lg">
                            <div class="flex items-center justify-between text-white text-xs mb-2">
                                <span class="font-bold opacity-90">{{ $gp['label'] }}</span>
                                <span class="font-black tabular-nums">{{ $gp['percent'] }}%</span>
                            </div>
                            <div class="w-full bg-black/20 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-white h-1.5 rounded-full" style="width: {{ $gp['percent'] }}%"></div>
                            </div>
                            <div class="mt-2 text-[10px] text-white/70 font-semibold tabular-nums text-left" dir="ltr">
                                {{ $gp['current'] }} / {{ $gp['target'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Feed Columns -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-hidden pb-4">
        
        <!-- Column 1: Today's Calls -->
        <div class="flex flex-col bg-white dark:bg-gray-800/50 rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden transform transition-all duration-300 hover:shadow-md animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 flex items-center justify-center shadow-inner">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    تماس‌های امروز
                </h3>
                <span class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 text-xs font-black px-2.5 py-1 rounded-lg tabular-nums">{{ $todayCalls->count() }}</span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-hide">
                @forelse($todayCalls as $call)
                    <div class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 rounded-[20px] p-4 shadow-sm hover:shadow-md transition-all cursor-pointer relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-1.5 h-full bg-blue-500 transform scale-y-0 group-hover:scale-y-100 transition-transform origin-top"></div>
                        
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="block text-sm font-black text-gray-900 dark:text-white">{{ $call->client ? $call->client->full_name : 'ناشناس' }}</span>
                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $call->reason }}</span>
                            </div>
                            <div class="bg-gray-100 dark:bg-gray-900 px-2.5 py-1 rounded-lg text-[10px] font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                                {{ $call->call_time }}
                            </div>
                        </div>
                        
                        @if($call->client)
                            <button wire:click="initiateTodayCall({{ $call->client_id }})" class="w-full py-2.5 bg-gray-50 hover:bg-blue-600 text-blue-600 hover:text-white dark:bg-gray-900 dark:hover:bg-blue-600 dark:text-blue-400 dark:hover:text-white rounded-xl text-[11px] font-bold transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                شروع تماس
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-400">تماس برنامه‌ریزی شده‌ای وجود ندارد</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Today's Tasks -->
        <div class="flex flex-col bg-white dark:bg-gray-800/50 rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden transform transition-all duration-300 hover:shadow-md animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400 flex items-center justify-center shadow-inner">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    تسک‌های سررسید امروز
                </h3>
                <span class="bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 text-xs font-black px-2.5 py-1 rounded-lg tabular-nums">{{ $todayTasks->count() }}</span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-hide">
                @forelse($todayTasks as $task)
                    <div class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 rounded-[20px] p-4 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-1.5 h-full bg-purple-500 transform scale-y-0 group-hover:scale-y-100 transition-transform origin-top"></div>
                        
                        <div class="mb-3">
                            <span class="block text-sm font-black text-gray-900 dark:text-white">{{ $task->title }}</span>
                            @if($task->relatedClient)
                                <button wire:click="$dispatch('clientSelected', { clientId: {{ $task->relatedClient->id }} })" class="inline-flex items-center gap-1 mt-1.5 text-[10px] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $task->relatedClient->full_name }}
                                </button>
                            @endif
                        </div>
                        
                        <button wire:click="completeTask({{ $task->id }})" class="w-full py-2.5 bg-gray-50 hover:bg-emerald-500 text-gray-600 hover:text-white dark:bg-gray-900 dark:hover:bg-emerald-600 dark:text-gray-400 dark:hover:text-white rounded-xl text-[11px] font-bold transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            انجام شد
                        </button>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-400">تمام تسک‌های امروز انجام شده‌اند 🎉</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Reminders -->
        <div class="flex flex-col bg-white dark:bg-gray-800/50 rounded-[2rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden transform transition-all duration-300 hover:shadow-md animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400 flex items-center justify-center shadow-inner">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    یادآوری‌های من
                </h3>
                <span class="bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 text-xs font-black px-2.5 py-1 rounded-lg tabular-nums">{{ $todayReminders->count() }}</span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-hide">
                @forelse($todayReminders as $reminder)
                    <div class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-600 rounded-[20px] p-4 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-1.5 h-full bg-amber-500 transform scale-y-0 group-hover:scale-y-100 transition-transform origin-top"></div>
                        
                        <div class="flex justify-between items-start mb-3">
                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200 leading-relaxed max-w-[80%]">{{ $reminder->message }}</p>
                            <span class="bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-1 rounded-lg text-[10px] font-bold tabular-nums">{{ $reminder->remind_at->format('H:i') }}</span>
                        </div>
                        
                        <button wire:click="dismissReminder({{ $reminder->id }})" class="w-full py-2.5 bg-gray-50 hover:bg-amber-100 text-gray-600 hover:text-amber-700 dark:bg-gray-900 dark:hover:bg-amber-900/40 dark:text-gray-400 dark:hover:text-amber-300 rounded-xl text-[11px] font-bold transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            متوجه شدم (رد کردن)
                        </button>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-400">یادآوری فعالی وجود ندارد</p>
                    </div>
                @endforelse
            </div>
        </div>
        
    </div>
</div>
