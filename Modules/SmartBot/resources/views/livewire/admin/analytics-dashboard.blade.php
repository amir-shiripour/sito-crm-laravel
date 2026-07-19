<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">گزارشات و آمار دستیار هوشمند</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">عملکرد گفتگوها، سوالات متداول و نرخ موفقیت دستیار را رصد کنید.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Sessions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">کل گفتگوها</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    {{ number_format($this->stats['total_sessions']) }}
                </h3>
                <p class="text-xs text-gray-400 mt-1">تعداد نشست‌های ایجاد شده</p>
            </div>
        </div>

        <!-- Card 2: Total Messages -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">کل پیام‌ها</span>
                <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    {{ number_format($this->stats['total_messages']) }}
                </h3>
                <p class="text-xs text-gray-400 mt-1">تبادل پیام کاربر و بات</p>
            </div>
        </div>

        <!-- Card 3: Resolved Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">نرخ پاسخ موفق</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    {{ $this->stats['resolved_rate'] }}%
                </h3>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $this->stats['resolved_rate'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Card 4: Conversion Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">نرخ تبدیل خرید</span>
                <span class="p-1.5 rounded-lg bg-orange-50 dark:bg-orange-950/30 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">
                    {{ $this->stats['conversion_rate'] }}%
                </h3>
                <p class="text-xs text-gray-400 mt-1">{{ $this->stats['sessions_with_cart'] }} بار افزودن به سبد</p>
            </div>
        </div>
    </div>

    <!-- Charts / Lists Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: FAQ list -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-6 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">بیشترین سوالات پرسیده شده (FAQ)</h3>
                <p class="text-xs text-gray-400 mt-0.5">سوالاتی که با موفقیت توسط موتور Q&A پاسخ داده شده‌اند.</p>
            </div>

            <div class="space-y-3">
                @forelse($this->stats['top_questions'] as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 w-6 h-6 flex items-center justify-center rounded-full">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $item['question_text'] }}</span>
                        </div>
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                            {{ $item['count'] }} بار
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">داده‌ای یافت نشد.</p>
                @endforelse
            </div>
        </div>

        <!-- Right: Unresolved Questions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 p-6 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">سوالات بی‌پاسخ (نیاز به تعریف Q&A)</h3>
                <p class="text-xs text-gray-400 mt-0.5">عباراتی که کاربر نوشته اما بات نتوانسته پاسخ دهد.</p>
            </div>

            <div class="space-y-3">
                @forelse($this->stats['unresolved_list'] as $item)
                    <div class="flex items-center justify-between p-3 bg-red-50/30 dark:bg-red-950/10 rounded-lg border border-red-50 dark:border-red-950/20">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">"{{ $item['content'] }}"</span>
                            <span class="text-[10px] text-gray-400">آخرین مشاهده: {{ \Carbon\Carbon::parse($item['last_seen'])->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-red-600 bg-red-50 dark:bg-red-950/30 px-2 py-0.5 rounded">
                                {{ $item['occurrences'] }} بار تکرار
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">همه گفتگوها با موفقیت پاسخ داده شده‌اند! 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
