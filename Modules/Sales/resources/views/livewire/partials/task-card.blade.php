<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 shadow-sm hover:shadow transition-all space-y-2">
    <div class="flex items-center justify-between gap-1.5">
        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $task->priority === 'CRITICAL' ? 'bg-red-50 text-red-600 dark:bg-red-950/20' : ($task->priority === 'HIGH' ? 'bg-orange-50 text-orange-600 dark:bg-orange-950/20' : ($task->priority === 'MEDIUM' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20' : 'bg-gray-50 text-gray-600 dark:bg-gray-900')) }}">
            {{ $task->priority === 'CRITICAL' ? 'بحرانی' : ($task->priority === 'HIGH' ? 'زیاد' : ($task->priority === 'MEDIUM' ? 'معمولی' : 'کم')) }}
        </span>
        @if($task->due_at)
            <span class="text-[9px] text-gray-400" dir="ltr">{{ \Morilog\Jalali\Jalalian::fromDateTime($task->due_at)->format('Y/m/d') }}</span>
        @endif
    </div>

    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white {{ $task->status === 'DONE' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
            {{ $task->title }}
        </h4>
        @if($task->description)
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $task->description }}</p>
        @endif
    </div>

    @if($task->relatedClient)
        <div class="text-[10px] bg-gray-50 dark:bg-gray-900 p-1.5 rounded-lg text-gray-600 dark:text-gray-400">
            👤 مشتری: <span class="font-bold">{{ $task->relatedClient->full_name }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-700/50 pt-2 text-[10px]">
        <span class="text-gray-400">👤 {{ $task->assignee ? $task->assignee->name : 'ناشناس' }}</span>
        
        <div class="flex items-center gap-1.5">
            @if($task->status !== 'DONE' && $task->status !== 'CANCELED')
                <button wire:click="completeTask({{ $task->id }})" class="text-emerald-600 hover:text-emerald-700 font-extrabold">✓ انجام</button>
            @endif
            <button wire:click="editTask({{ $task->id }})" class="text-indigo-600 hover:text-indigo-700 font-extrabold">✏️</button>
        </div>
    </div>
</div>
