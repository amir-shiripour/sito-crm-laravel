<div class="bg-white dark:bg-gray-800/90 rounded-xl border border-gray-200/80 dark:border-gray-700/80 p-3.5 shadow-xs hover:shadow-md transition-all space-y-2.5 border-r-4 {{ $task->priority === 'CRITICAL' ? 'border-r-rose-600' : ($task->priority === 'HIGH' ? 'border-r-amber-500' : ($task->priority === 'MEDIUM' ? 'border-r-indigo-500' : 'border-r-slate-400')) }}">
    <!-- Top info: Priority & Jalali Due Date -->
    <div class="flex items-center justify-between gap-2">
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $task->priority === 'CRITICAL' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : ($task->priority === 'HIGH' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400' : ($task->priority === 'MEDIUM' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300')) }}">
            {{ $task->priority === 'CRITICAL' ? 'بحرانی' : ($task->priority === 'HIGH' ? 'زیاد' : ($task->priority === 'MEDIUM' ? 'معمولی' : 'کم')) }}
        </span>

        @if($task->due_at)
            <div class="flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 font-sans">
                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ \Morilog\Jalali\Jalalian::fromDateTime($task->due_at)->format('Y/m/d H:i') }}</span>
                @if($task->due_at->isPast() && $task->status !== 'DONE' && $task->status !== 'CANCELED')
                    <span class="text-[9px] bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 px-1 rounded font-bold animate-pulse">معوق</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Title & Description -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white leading-snug {{ $task->status === 'DONE' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
            {{ $task->title }}
        </h4>
        @if($task->description)
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 leading-relaxed">
                {{ $task->description }}
            </p>
        @endif
    </div>

    <!-- Client / Deal Connection -->
    @php
        $meta = $task->meta ?? [];
        $dealId = $meta['deal_id'] ?? ($task->related_type === 'DEAL' ? $task->related_id : null);
        $deal = ($dealId && class_exists(\Modules\Sales\App\Models\SalesDeal::class)) ? \Modules\Sales\App\Models\SalesDeal::find($dealId) : null;
        
        $client = null;
        if ($task->related_type === 'CLIENT' && $task->relatedClient) {
            $client = $task->relatedClient;
        } elseif ($deal && $deal->client) {
            $client = $deal->client;
        } elseif ($task->relatedClient) {
            $client = $task->relatedClient;
        }
    @endphp
    @if($client || $deal)
        <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[10px]">
            @if($client)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 font-sans">
                    <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $client->full_name }}</span>
                </span>
            @endif

            @if($deal)
                @php
                    $cleanDealTitle = preg_replace('/^(پرونده\s*:\s*)+/u', '', trim($deal->title));
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 font-sans">
                    <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>پرونده: <span class="font-bold">{{ $cleanDealTitle }}</span></span>
                </span>
            @endif
        </div>
    @endif

    <!-- Footer: Assignee & Action buttons -->
    <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700/60 pt-2 text-[10px]">
        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="font-medium">{{ $task->assignee ? $task->assignee->name : 'ناشناس' }}</span>
        </div>

        <div class="flex items-center gap-1.5">
            @if($task->status !== 'DONE' && $task->status !== 'CANCELED')
                <button wire:click="completeTask({{ $task->id }})"
                        title="علامت‌گذاری به عنوان انجام شده"
                        class="p-1 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            @endif

            <button wire:click="editTask({{ $task->id }})"
                    title="ویرایش پیگیری"
                    class="p-1 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
