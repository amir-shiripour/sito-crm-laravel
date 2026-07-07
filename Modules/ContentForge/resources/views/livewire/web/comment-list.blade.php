<div class="mt-12 space-y-6">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white">دیدگاه‌های کاربران ({{ $comments->count() }})</h3>

    <div class="space-y-6">
        @forelse($comments as $comment)
            <div class="p-5 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full flex items-center justify-center font-bold text-sm">
                        {{ mb_substr($comment->author_name, 0, 1) }}
                    </div>
                    <div>
                        <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $comment->author_name }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    {{ $comment->body }}
                </p>

                {{-- Replies --}}
                @if($comment->replies->isNotEmpty())
                    <div class="pr-6 border-r border-gray-100 dark:border-gray-700 space-y-4 mt-4">
                        @foreach($comment->replies as $reply)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold text-xs">
                                        {{ mb_substr($reply->author_name ?: ($reply->user->name ?? 'مدیر'), 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ $reply->author_name ?: ($reply->user->name ?? 'پاسخ مدیر') }}</span>
                                        <span class="text-[9px] text-gray-400 block mt-0.5">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {{ $reply->body }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-6">اولین نفری باشید که دیدگاه خود را ارسال می‌کند.</p>
        @endforelse
    </div>
</div>
