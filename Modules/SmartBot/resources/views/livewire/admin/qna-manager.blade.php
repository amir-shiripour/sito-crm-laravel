<div class="space-y-6 font-iranYekan">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl p-6 rounded-[2rem] border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">مدیریت سوال و جواب دستیار هوشمند</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">سوالات، پاسخ‌های متنی، لیست محصولات و منوهای شرطی پاسخگو را مدیریت کنید.</p>
            </div>
        </div>
        <a
            href="{{ route('user.smartbot.qna.create') }}"
            wire:navigate
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/20 active:scale-95 transition-all duration-200 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>افزودن سوال و جواب جدید</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="w-full md:w-96 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جستجو در سوالات و کلمات کلیدی..."
                class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/80 dark:bg-slate-900/80 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
            />
            <span class="absolute left-3 top-3 text-slate-400 dark:text-slate-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">دسته‌بندی:</span>
            <select
                wire:model.live="category"
                class="border border-slate-200 dark:border-slate-700/80 rounded-xl bg-slate-50/80 dark:bg-slate-900/80 text-slate-800 dark:text-slate-200 text-xs py-2 px-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none font-bold transition-all"
            >
                <option value="all">همه دسته‌ها</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-xs font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="py-3.5 px-4 sm:px-6">سوال اصلی</th>
                        <th class="py-3.5 px-4">کلمات کلیدی</th>
                        <th class="py-3.5 px-4">نوع پاسخ</th>
                        <th class="py-3.5 px-4">دسته بندی</th>
                        <th class="py-3.5 px-4">اولویت</th>
                        <th class="py-3.5 px-4">وضعیت</th>
                        <th class="px-6 py-4 font-bold text-slate-600 dark:text-slate-300 text-left pl-6">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($questions as $question)
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-150">
                            <td class="py-4 px-4 sm:px-6 font-bold text-slate-900 dark:text-white max-w-xs sm:max-w-md">
                                <div class="line-clamp-2">{{ $question->question_text }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($question->keywords ?? [] as $kw)
                                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                                            {{ $kw }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if($question->defaultAnswer()?->answer_type === 'product_list')
                                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 px-2.5 py-1 rounded-lg font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        لیست محصولات
                                    </span>
                                @elseif($question->defaultAnswer()?->answer_type === 'menu_items')
                                    <span class="inline-flex items-center gap-1.5 text-xs text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-500/10 border border-purple-200/60 dark:border-purple-500/20 px-2.5 py-1 rounded-lg font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        منوی گزینه‌ای (شرطی)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200/60 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4z"/></svg>
                                        متنی
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-slate-500 dark:text-slate-400 text-xs font-semibold">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300">
                                    {{ $question->category }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-bold text-slate-700 dark:text-slate-300">
                                {{ $question->priority }}
                            </td>
                            <td class="py-4 px-4">
                                <button
                                    wire:click="toggleStatus({{ $question->id }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-xl font-bold transition-all {{ $question->is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20 hover:bg-emerald-100' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20 hover:bg-rose-100' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $question->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $question->is_active ? 'فعال' : 'غیرفعال' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a
                                        href="{{ route('user.smartbot.qna.edit', $question->id) }}"
                                        wire:navigate
                                        class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors inline-flex items-center justify-center"
                                        title="ویرایش"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button
                                        onclick="confirm('آیا مطمئن هستید؟') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $question->id }})"
                                        class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors"
                                        title="حذف"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.912-5.813a2 2 0 011.275-1.275L21 12l-5.813-1.912a2 2 0 01-1.275-1.275L12 3v0a2 2 0 00-2 2v10.904z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">هیچ سوال و جوابی یافت نشد.</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">می‌توانید با زدن دکمه «افزودن سوال و جواب جدید» نخستین سوال هوشمند را تعریف نمایید.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($questions->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
</div>
