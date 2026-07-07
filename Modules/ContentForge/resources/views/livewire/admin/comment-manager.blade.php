<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">مدیریت دیدگاه‌های کاربران</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تایید، حذف یا هرزنامه خواندن نظرات ارسال شده توسط کاربران و مهمانان</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="p-4 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Search --}}
            <div class="relative w-full md:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجو در نظرات..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            {{-- Status Filter tabs --}}
            <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-xl text-xs font-bold">
                <button wire:click="$set('statusFilter', 'all')" class="px-4 py-1.5 rounded-lg transition-all {{ $statusFilter === 'all' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">همه</button>
                <button wire:click="$set('statusFilter', 'pending')" class="px-4 py-1.5 rounded-lg transition-all {{ $statusFilter === 'pending' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">در انتظار تایید</button>
                <button wire:click="$set('statusFilter', 'approved')" class="px-4 py-1.5 rounded-lg transition-all {{ $statusFilter === 'approved' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">تایید شده</button>
                <button wire:click="$set('statusFilter', 'spam')" class="px-4 py-1.5 rounded-lg transition-all {{ $statusFilter === 'spam' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">هرزنامه</button>
            </div>
        </div>
    </div>

    {{-- Comments Listing --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm flex flex-col md:flex-row md:items-start justify-between gap-6">
                <div class="space-y-3 flex-grow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold">
                            {{ mb_substr($comment->author_name ?: ($comment->user->name ?? 'کاربر'), 0, 1) }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 dark:text-white block">{{ $comment->author_name ?: ($comment->user->name ?? 'کاربر مهمان') }}</span>
                            <span class="text-xs text-gray-400 font-mono mt-0.5 block">{{ $comment->author_email ?: ($comment->user->email ?? 'فاقد ایمیل') }}</span>
                        </div>
                        <span class="text-xs text-gray-300 dark:text-gray-600">|</span>
                        <div class="text-xs text-gray-400 font-mono">{{ $comment->created_at->format('Y-m-d H:i') }}</div>
                        <span class="text-xs text-gray-300 dark:text-gray-600">|</span>
                        <div class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">روی نوشته: <span class="font-bold">{{ $comment->post->title }}</span></div>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl">
                        {{ $comment->body }}
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex md:flex-col items-center justify-end gap-2 text-xs">
                    @if($comment->status->value === 'pending')
                        <button wire:click="approve({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold transition-all shadow-sm">تایید دیدگاه</button>
                        <button wire:click="markSpam({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-lg font-bold transition-all">علامت هرزنامه</button>
                    @elseif($comment->status->value === 'approved')
                        <button wire:click="reject({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 rounded-lg font-bold transition-all">لغو تایید</button>
                        <button wire:click="markSpam({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-lg font-bold transition-all">علامت هرزنامه</button>
                    @elseif($comment->status->value === 'spam')
                        <button wire:click="approve({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold transition-all">تایید مجدد</button>
                    @endif

                    <button onclick="confirm('آیا از حذف این نظر مطمئن هستید؟') || event.stopImmediatePropagation()" wire:click="delete({{ $comment->id }})" class="w-full md:w-32 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950/20 dark:text-red-400 rounded-lg font-bold transition-all">حذف دائمی</button>
                </div>
            </div>
        @empty
            <div class="p-12 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm text-center text-gray-400">
                هیچ دیدگاهی برای نمایش یافت نشد.
            </div>
        @endforelse
    </div>

    @if($comments->hasPages())
        <div>{{ $comments->links() }}</div>
    @endif
</div>
