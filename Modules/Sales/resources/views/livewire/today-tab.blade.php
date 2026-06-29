<div class="space-y-6 text-right" dir="rtl">
    
    <!-- Goals progress section (if any) -->
    @if(count($goalsProgress) > 0)
        <div class="bg-indigo-50/20 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900 rounded-2xl p-4 space-y-4">
            <h3 class="text-xs font-black text-indigo-800 dark:text-indigo-400 flex items-center gap-1.5">
                🎯 پیشرفت اهداف فعال شما در این دوره
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($goalsProgress as $gp)
                    <div class="bg-white dark:bg-gray-800 border border-indigo-50 dark:border-gray-700/80 p-3 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $gp['label'] }}</span>
                            <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $gp['current'] }} / {{ $gp['target'] }} ({{ $gp['percent'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $gp['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Layout Agenda columns -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Column 1: Today's calls -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 pb-2 flex items-center gap-1.5">
                📞 تماس‌های برنامه‌ریزی‌شده امروز
            </h3>
            <div class="space-y-2">
                @forelse($todayCalls as $call)
                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-3 shadow-xs space-y-2 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $call->client ? $call->client->full_name : 'ناشناس' }}</span>
                                <span class="text-[10px] text-gray-400" dir="ltr">{{ $call->call_time }}</span>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">موضوع: {{ $call->reason }}</p>
                        </div>
                        @if($call->client)
                            <button wire:click="initiateTodayCall({{ $call->client_id }})" class="w-full py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-bold transition-all flex items-center justify-center gap-1">
                                📞 شروع تماس
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="text-[11px] text-gray-400 text-center py-6 bg-gray-50/50 dark:bg-gray-900/10 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">تماسی برای امروز برنامه‌ریزی نشده است.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Today's tasks -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 pb-2 flex items-center gap-1.5">
                ✅ تسک‌های سررسید امروز
            </h3>
            <div class="space-y-2">
                @forelse($todayTasks as $task)
                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-3 shadow-xs space-y-2 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $task->title }}</span>
                                <span class="text-[9px] bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-extrabold">امروز</span>
                            </div>
                            @if($task->relatedClient)
                                <p class="text-[10px] text-gray-500 mt-1">👤 مشتری: {{ $task->relatedClient->full_name }}</p>
                            @endif
                        </div>
                        <button wire:click="completeTask({{ $task->id }})" class="w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-bold transition-all">
                            ✓ انجام شد
                        </button>
                    </div>
                @empty
                    <p class="text-[11px] text-gray-400 text-center py-6 bg-gray-50/50 dark:bg-gray-900/10 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">تسکی برای امروز سررسید ندارد.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Reminders -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 pb-2 flex items-center gap-1.5">
                ⏰ یادآوری‌های امروز من
            </h3>
            <div class="space-y-2">
                @forelse($todayReminders as $reminder)
                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-3 shadow-xs space-y-2 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">🔔 یادآوری</span>
                                <span class="text-[10px] text-gray-400" dir="ltr">{{ $reminder->remind_at->format('H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-gray-700 dark:text-gray-300 mt-1 leading-relaxed">{{ $reminder->message }}</p>
                        </div>
                        <button wire:click="dismissReminder({{ $reminder->id }})" class="w-full py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-[10px] font-bold transition-all">
                            🔕 رد کردن یادآوری
                        </button>
                    </div>
                @empty
                    <p class="text-[11px] text-gray-400 text-center py-6 bg-gray-50/50 dark:bg-gray-900/10 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">یادآوری فعالی برای امروز وجود ندارد.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
