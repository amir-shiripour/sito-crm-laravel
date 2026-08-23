@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Morilog\Jalali\Jalalian;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $formatShamsi = function($date, $format = 'Y/m/d') {
        if (!$date) return '';
        if (function_exists('jdate')) {
            return jdate($date)->format($format);
        }
        if (class_exists(Jalalian::class)) {
            return Jalalian::fromCarbon($date)->format($format);
        }
        return $date instanceof Carbon ? $date->format($format) : (string)$date;
    };

    $formatAgo = function($date) {
        if (!$date) return '';
        if (function_exists('jdate')) {
            return jdate($date)->ago();
        }
        if (class_exists(Jalalian::class)) {
            return Jalalian::fromCarbon($date)->ago();
        }
        return $date instanceof Carbon ? $date->diffForHumans() : (string)$date;
    };

    $canPinProjectMessages = auth()->user()?->can('pinMessage', $project) ?? false;

    $pinnedMessages = $project->messages->where('is_pinned', true)->values();
    $pinnedListForJs = $pinnedMessages->map(fn($m) => [
        'id' => $m->id,
        'user_name' => $m->user?->name ?? 'کاربر',
        'body' => mb_strlen($m->body) > 75 ? mb_substr($m->body, 0, 75) . '...' : $m->body,
    ]);

    // Mentionable users (Creator + Members)
    $mentionableUsers = collect();
    if ($project->createdBy) {
        $mentionableUsers->push($project->createdBy);
    }
    foreach ($project->members as $member) {
        if ($member->user && !$mentionableUsers->contains('id', $member->user->id)) {
            $mentionableUsers->push($member->user);
        }
    }
    $mentionableUsersList = $mentionableUsers->map(fn($u) => [
        'id' => $u->id,
        'name' => $u->name,
        'initial' => mb_substr($u->name, 0, 1),
    ])->values();

    // Data for reactive search and filtering
    $messagesDataForJs = $project->messages->map(fn($m) => [
        'id' => $m->id,
        'user_id' => $m->user_id,
        'user_name' => $m->user?->name ?? 'کاربر',
        'body' => $m->body,
        'is_pinned' => (bool) $m->is_pinned,
        'has_reply' => (bool) $m->parent_id,
        'is_mine' => $m->user_id === auth()->id(),
        'mentions_me' => auth()->user() && str_contains($m->body, '@' . auth()->user()->name),
    ])->values();

    // Helper to highlight @mentions safely in message text with high contrast for dark mode
    $formatMessageBody = function($body) use ($mentionableUsers) {
        $escaped = e($body);
        $placeholders = [];

        // 1. Replace known project members first with unique placeholders to avoid double replacement
        foreach ($mentionableUsers as $idx => $u) {
            $userName = e($u->name);
            $isAuthUser = auth()->id() === $u->id;
            $badgeClass = $isAuthUser
                ? 'inline-flex items-center gap-0.5 px-2 py-0.5 mx-0.5 rounded-lg text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-100 dark:text-amber-900 dark:border-amber-300 shadow-xs select-none'
                : 'inline-flex items-center gap-0.5 px-2 py-0.5 mx-0.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 dark:bg-indigo-100 dark:text-indigo-800 dark:border-indigo-200 shadow-xs select-none';

            $placeholder = '___MENTION_USER_' . $idx . '___';
            if (str_contains($escaped, '@' . $userName)) {
                $escaped = str_replace('@' . $userName, $placeholder, $escaped);
                $placeholders[$placeholder] = '<span class="' . $badgeClass . '"><span class="opacity-70 font-mono text-xs">@</span>' . $userName . '</span>';
            }
        }

        // 2. Generic @mention for any other @word
        $genericBadgeClass = 'inline-flex items-center gap-0.5 px-2 py-0.5 mx-0.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 dark:bg-indigo-100 dark:text-indigo-800 dark:border-indigo-200 shadow-xs select-none';
        $escaped = preg_replace_callback('/(?<!\w)@([\p{L}\p{N}_-]+)/u', function($matches) use ($genericBadgeClass) {
            return '<span class="' . $genericBadgeClass . '"><span class="opacity-70 font-mono text-xs">@</span>' . $matches[1] . '</span>';
        }, $escaped);

        // 3. Restore member placeholders
        foreach ($placeholders as $placeholder => $html) {
            $escaped = str_replace($placeholder, $html, $escaped);
        }

        return $escaped;
    };
@endphp

<div x-data="projectChatComponent()" x-init="init()"
     :class="isModalOpen ? 'fixed inset-0 z-[70] p-2 sm:p-4 md:p-8 lg:p-12 overflow-hidden' : 'space-y-6'"
     @keydown.escape.window="if(isModalOpen) isModalOpen = false"
     x-effect="document.body.style.overflow = isModalOpen ? 'hidden' : ''"
     dir="rtl">

    {{-- ══════ Fullscreen Modal Backdrop ══════ --}}
    <div x-show="isModalOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isModalOpen = false"
         class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer z-0"></div>
    {{-- Messages Container --}}
    <div class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 relative z-10"
         :class="isModalOpen ? 'h-full flex flex-col overflow-hidden shadow-2xl p-4 sm:p-6 space-y-4' : 'shadow-sm p-5 sm:p-7 space-y-5'">
        {{-- Chat Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        گفتگو و پیام‌های پروژه
                    </h3>
                    <p class="text-xs text-gray-400">فضای تبادل نظر، منشن، پاسخ و جستجوی پیام‌ها</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{-- ══════ Fullscreen Toggle Button ══════ --}}
                <button type="button"
                        @click="isModalOpen = !isModalOpen; if(isModalOpen) $nextTick(() => scrollToBottom())"
                        :class="isModalOpen ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-600 dark:hover:text-indigo-400 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-700'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all select-none cursor-pointer"
                        title="نمایش تمام‌صفحه گفتگو">
                    <svg x-show="!isModalOpen" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 8V4m0 0h4M4 4l5 5m11-5v4m0-4h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
                    </svg>
                    <svg x-show="isModalOpen" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                    </svg>
                    <span x-text="isModalOpen ? 'خروج از تمام‌صفحه' : 'تمام‌صفحه'"></span>
                </button>

                {{-- Search & Filter Toggle Button --}}
                <button type="button"
                        @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $refs.searchInput?.focus())"
                        :class="searchOpen || hasActiveFilters ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-300 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all select-none cursor-pointer"
                        title="جستجو و فیلتر پیشرفته پیام‌ها">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>جستجو و فیلتر</span>
                    <span x-show="hasActiveFilters" x-cloak
                          class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                </button>

                @if($pinnedMessages->isNotEmpty())
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-200/50 dark:border-amber-700/40">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M16 4h1a1 1 0 1 0 0-2H7a1 1 0 1 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 4 15.24V17h7v5a1 1 0 0 0 2 0v-5h7v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 16 10.76V4z"/>
                        </svg>
                        {{ $faNum($pinnedMessages->count()) }} سنجاق شده
                    </span>
                @endif

                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="faNum(visibleCount) + ' / ' + faNum(messages.length) + ' پیام'">{{ $faNum($project->messages->count()) }} پیام</span>
                </span>
            </div>
        </div>

        {{-- Search & Filter Panel (Expandable) --}}
        <div x-show="searchOpen || hasActiveFilters"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2 scale-99"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-99"
             class="bg-gray-50/90 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 rounded-2xl p-4 space-y-3 shadow-2xs">

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                {{-- Search Input --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           placeholder="جستجو در متن پیام‌ها یا نام فرستنده..."
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pr-9 pl-8 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white placeholder-gray-400">
                    <button type="button"
                            x-show="searchQuery"
                            @click="searchQuery = ''; $refs.searchInput?.focus()"
                            class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Sender Filter Dropdown --}}
                <div class="sm:w-56">
                    <select x-model="senderFilter"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="all">همه فرستنده‌ها</option>
                        <option value="me">فقط پیام‌های من</option>
                        @foreach($mentionableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills and Result Summary --}}
            <div
                class="flex flex-wrap items-center justify-between gap-2.5 pt-2 border-t border-gray-200/60 dark:border-gray-700/50">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] text-gray-400 font-bold ml-1">فیلتر سریع:</span>
                    <button type="button"
                            @click="filterType = 'all'"
                            :class="filterType === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                        </svg>
                        <span>همه</span>
                    </button>
                    <button type="button"
                            @click="filterType = 'pinned'"
                            :class="filterType === 'pinned' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M16 4h1a1 1 0 1 0 0-2H7a1 1 0 1 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 4 15.24V17h7v5a1 1 0 0 0 2 0v-5h7v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 16 10.76V4z"/>
                        </svg>
                        <span>سنجاق‌شده‌ها</span>
                    </button>
                    <button type="button"
                            @click="filterType = 'mentions_to_me'"
                            :class="filterType === 'mentions_to_me' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
                        </svg>
                        <span>منشن‌های من</span>
                    </button>
                    <button type="button"
                            @click="filterType = 'replies'"
                            :class="filterType === 'replies' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 17 4 12 9 7"></polyline>
                            <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                        </svg>
                        <span>دارای پاسخ</span>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium"
                          x-text="'نمایش ' + faNum(visibleCount) + ' از ' + faNum(messages.length) + ' پیام'"></span>
                    <button type="button"
                            x-show="hasActiveFilters"
                            @click="resetFilters()"
                            class="text-xs text-rose-500 hover:text-rose-700 font-bold transition-colors cursor-pointer">
                        پاک کردن فیلترها
                    </button>
                </div>
            </div>
        </div>

        {{-- Pinned Messages Banner (if any) --}}
        @if($pinnedMessages->isNotEmpty())
            <div x-data="{ currentPinnedIdx: 0, pinnedList: {{ json_encode($pinnedListForJs) }} }"
                 class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-3 flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer select-none"
                     @click="scrollToMessage(pinnedList[currentPinnedIdx]?.id)"
                     title="کلیک برای پرش به این پیام">
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M16 4h1a1 1 0 1 0 0-2H7a1 1 0 1 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 4 15.24V17h7v5a1 1 0 0 0 2 0v-5h7v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 16 10.76V4z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                پیام سنجاق شده
                                @if($pinnedMessages->count() > 1)
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-normal">({{ $faNum($pinnedMessages->count()) }} پیام)</span>
                                @endif
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400"
                                  x-text="'• ' + pinnedList[currentPinnedIdx]?.user_name"></span>
                        </div>
                        <p class="text-xs text-gray-700 dark:text-gray-300 truncate mt-0.5 font-medium"
                           x-text="pinnedList[currentPinnedIdx]?.body"></p>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    @if($pinnedMessages->count() > 1)
                        <button type="button"
                                @click="currentPinnedIdx = (currentPinnedIdx + 1) % pinnedList.length"
                                class="p-1.5 rounded-xl hover:bg-amber-200/60 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-200 text-xs transition-colors cursor-pointer"
                                title="پیام سنجاق‌شده بعدی">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    @endif

                    @if($canPinProjectMessages)
                        <button type="button"
                                @click="togglePin(pinnedList[currentPinnedIdx]?.id)"
                                class="p-1.5 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-950/50 text-gray-400 hover:text-rose-600 transition-colors cursor-pointer"
                                title="برداشتن سنجاق این پیام">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endif
        <style>
            .custom-messages-scrollbar {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }

            .dark .custom-messages-scrollbar {
                scrollbar-color: #475569 transparent;
            }

            .custom-messages-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-messages-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-messages-scrollbar::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 9999px;
            }

            .dark .custom-messages-scrollbar::-webkit-scrollbar-thumb {
                background-color: #475569;
            }

            .custom-messages-scrollbar::-webkit-scrollbar-thumb:hover {
                background-color: #94a3b8;
            }

            .dark .custom-messages-scrollbar::-webkit-scrollbar-thumb:hover {
                background-color: #64748b;
            }
        </style>

        {{-- Messages Stream Container with Scroll and Floating Scroll-to-Bottom Button --}}
        <div :class="isModalOpen ? 'relative flex-1 min-h-0 flex flex-col' : 'relative'">
            <div id="project-messages-stream"
                 x-ref="messagesContainer"
                 @scroll="handleScroll()"
                 :class="isModalOpen ? 'space-y-4 flex-1 min-h-0 overflow-y-auto pr-2 pl-2 scroll-smooth custom-messages-scrollbar' : 'space-y-4 h-[550px] max-h-[70vh] min-h-[250px] overflow-y-auto pr-2 pl-2 scroll-smooth custom-messages-scrollbar'">                @php
                    $lastMessageDate = null;
                @endphp
                @forelse($project->messages as $msg)
                    @php
                        $isMine = $msg->user_id === auth()->id();
                        $isSuperAdmin = auth()->user()?->hasAnyRole(['super-admin', 'superadmin']) ?? false;
                        $canDelete = $isMine || $isSuperAdmin || (auth()->user()?->can('deleteMessage', $project) ?? false);

                        $persianDate = $formatShamsi($msg->created_at, 'Y/m/d');
                        $persianDateReadable = $formatShamsi($msg->created_at, '%d %B %Y');
                        $exactTime = $msg->created_at ? $msg->created_at->format('H:i') : '';
                        $timeAgo = $formatAgo($msg->created_at);
                        $fullTime = $formatShamsi($msg->created_at, 'Y/m/d H:i');

                        $isNewDateGroup = $persianDate && $persianDate !== $lastMessageDate;
                        $lastMessageDate = $persianDate;
                    @endphp

                    {{-- Date Separator Badge --}}
                    @if($isNewDateGroup)
                        <div class="flex items-center justify-center my-4 select-none">
                            <div class="h-px bg-gray-100 dark:bg-gray-700/60 flex-1"></div>
                            <span
                                class="px-3.5 py-1 mx-3 rounded-full bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-300 text-[11px] font-bold border border-gray-200/50 dark:border-gray-600/40 shadow-2xs">
                            {{ $faNum($persianDateReadable) }}
                        </span>
                            <div class="h-px bg-gray-100 dark:bg-gray-700/60 flex-1"></div>
                        </div>
                @endif

                <div id="message-{{ $msg->id }}"
                     x-show="isMessageVisible({{ $msg->id }})"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-98"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="flex items-start gap-3.5 {{ $isMine ? 'flex-row-reverse justify-start' : 'justify-start' }} transition-all duration-300">
                    {{-- Avatar --}}
                    <div
                        class="w-9 h-9 rounded-2xl {{ $isMine ? 'bg-indigo-600 text-white ring-2 ring-indigo-100 dark:ring-indigo-900/50' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-2 ring-gray-200/50 dark:ring-gray-700/50' }} flex items-center justify-center text-xs font-black shrink-0 shadow-sm select-none"
                        title="{{ $msg->user?->name ?? 'کاربر' }}">
                        {{ mb_substr($msg->user?->name ?? 'U', 0, 1) }}
                    </div>

                    {{-- Message Bubble --}}
                    <div class="max-w-xl group relative">
                        <div
                            class="p-4 rounded-3xl transition-all {{ $msg->is_pinned ? 'ring-2 ring-amber-400/70 dark:ring-amber-500/60 bg-amber-50/30 dark:bg-amber-950/20' : '' }} {{ $isMine ? 'bg-indigo-50/90 dark:bg-indigo-950/50 rounded-tl-xs text-indigo-950 dark:text-indigo-100 border border-indigo-100 dark:border-indigo-800/50 shadow-sm' : 'bg-gray-50/90 dark:bg-gray-900/60 rounded-tr-xs text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-800 shadow-sm' }}">

                            {{-- Pinned indicator if message is pinned --}}
                            @if($msg->is_pinned)
                                <div
                                    class="flex items-center gap-1.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 mb-1.5 pb-1 border-b border-amber-200/60 dark:border-amber-800/50">
                                    <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M16 4h1a1 1 0 1 0 0-2H7a1 1 0 1 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 4 15.24V17h7v5a1 1 0 0 0 2 0v-5h7v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 16 10.76V4z"/>
                                    </svg>
                                    <span>پیام سنجاق شده {{ $msg->pinnedBy ? 'توسط ' . $msg->pinnedBy->name : '' }}</span>
                                </div>
                            @endif

                            {{-- Quoted Reply Box if replying to another message --}}
                            @if($msg->parent)
                                <div @click="scrollToMessage({{ $msg->parent_id }})"
                                     class="mb-2.5 p-2.5 rounded-2xl {{ $isMine ? 'bg-indigo-100/70 dark:bg-indigo-900/60 border-r-4 border-indigo-500' : 'bg-gray-200/60 dark:bg-gray-800/80 border-r-4 border-indigo-500' }} cursor-pointer hover:opacity-90 transition-all select-none text-right"
                                     title="کلیک برای پرش به پیام اصلی">
                                    <div
                                        class="flex items-center gap-1.5 text-[11px] font-bold {{ $isMine ? 'text-indigo-800 dark:text-indigo-300' : 'text-indigo-600 dark:text-indigo-400' }} mb-0.5">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="9 17 4 12 9 7"></polyline>
                                            <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                                        </svg>
                                        <span>پاسخ به {{ $msg->parent->user?->name ?? 'کاربر' }}</span>
                                    </div>
                                    <p class="text-xs {{ $isMine ? 'text-indigo-900/80 dark:text-indigo-200/80' : 'text-gray-600 dark:text-gray-300' }} truncate">{{ Str::limit($msg->parent->body, 70) }}</p>
                                </div>
                            @endif

                            <div
                                class="flex items-center justify-between gap-4 mb-2 pb-1.5 border-b {{ $isMine ? 'border-indigo-100/80 dark:border-indigo-900/60' : 'border-gray-100 dark:border-gray-800/80' }}">
                                <span
                                    class="font-bold text-xs flex items-center gap-1.5 {{ $isMine ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-200' }}">
                                    @if($isMine)
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        شما
                                    @else
                                        {{ $msg->user?->name ?? 'کاربر' }}
                                    @endif
                                </span>
                                <div
                                    class="flex items-center gap-1.5 text-[11px] {{ $isMine ? 'text-indigo-400 dark:text-indigo-300/80' : 'text-gray-400 dark:text-gray-400' }}"
                                    title="{{ $fullTime }}">
                                    <span class="font-medium">{{ $faNum($persianDate) }}</span>
                                    @if($exactTime)
                                        <span class="opacity-40">•</span>
                                        <span class="font-bold text-xs">{{ $faNum($exactTime) }}</span>
                                    @endif
                                    @if($timeAgo)
                                        <span class="opacity-40">•</span>
                                        <span class="text-[10px] opacity-80">({{ $faNum($timeAgo) }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm leading-relaxed whitespace-pre-line select-text text-right break-words"
                                 dir="auto">{!! $formatMessageBody($msg->body) !!}</div>
                        </div>

                        {{-- Action Buttons on Hover (Reply / Pin / Delete) --}}
                        <div
                            class="absolute -top-2.5 {{ $isMine ? '-left-2' : '-right-2' }} opacity-0 group-hover:opacity-100 transition-all duration-150 flex items-center gap-1 z-10">
                            {{-- Reply Button (پاسخ به پیام) --}}
                            <button type="button"
                                    @click="startReply({{ $msg->id }}, '{{ addslashes($msg->user?->name ?? 'کاربر') }}', '{{ addslashes(Str::limit($msg->body, 60)) }}')"
                                    class="p-1.5 rounded-full bg-white dark:bg-gray-800 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 border border-gray-200 dark:border-gray-700 shadow-md hover:scale-110 transition-all cursor-pointer"
                                    title="پاسخ به این پیام (ریپلای)">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 17 4 12 9 7"></polyline>
                                    <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                                </svg>
                            </button>
                            @if($canPinProjectMessages)
                                <button type="button"
                                        @click="togglePin({{ $msg->id }})"
                                        id="pin-btn-{{ $msg->id }}"
                                        class="p-1.5 rounded-full bg-white dark:bg-gray-800 {{ $msg->is_pinned ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700' : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 border-gray-200 dark:border-gray-700' }} border shadow-md hover:scale-110 transition-all cursor-pointer"
                                        title="{{ $msg->is_pinned ? 'برداشتن سنجاق (پین)' : 'سنجاق کردن پیام (پین)' }}">
                                    @if($msg->is_pinned)
                                        {{-- Filled Pushpin --}}
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M16 4h1a1 1 0 1 0 0-2H7a1 1 0 1 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 4 15.24V17h7v5a1 1 0 0 0 2 0v-5h7v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 16 10.76V4z"/>
                                        </svg>
                                    @else
                                        {{-- Outline Pushpin --}}
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <line x1="12" y1="17" x2="12" y2="22"></line>
                                            <path
                                                d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a1 1 0 0 0 0-2H8a1 1 0 0 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"></path>
                                        </svg>
                                    @endif
                                </button>
                            @endif
                            @if($canDelete)
                                <button type="button"
                                        @click="deleteMessage({{ $msg->id }})"
                                        class="p-1.5 rounded-full bg-white dark:bg-gray-800 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-gray-200 dark:border-gray-700 shadow-md hover:scale-110 transition-all cursor-pointer"
                                        title="حذف پیام">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                    <div class="py-14 text-center text-gray-400">
                        <div
                            class="w-16 h-16 rounded-3xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-500 flex items-center justify-center mx-auto mb-3 shadow-inner">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                    </div>
                    <p class="font-bold text-gray-700 dark:text-gray-300 text-sm">هنوز پیامی در این پروژه ثبت نشده
                        است.</p>
                    <p class="text-xs mt-1 text-gray-400">اولین یادداشت یا پیام هماهنگی را در کادر زیر بنویسید (پشتیبانی
                        از @منشن و ریپلای).</p>
                </div>
            @endforelse

            {{-- Empty search & filter result state --}}
            <div x-show="hasActiveFilters && visibleCount === 0"
                 x-cloak
                 class="py-12 text-center text-gray-400 space-y-3">
                <div
                    class="w-14 h-14 rounded-3xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">هیچ پیامی مطابق با جستجو و فیلترهای
                    انتخابی یافت نشد.</p>
                <button type="button"
                        @click="resetFilters()"
                        class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 transition-all cursor-pointer">
                    پاک کردن فیلترها و نمایش همه پیام‌ها
                </button>
            </div>
            </div>

            {{-- Floating Scroll to Bottom Button --}}
            <div x-show="showScrollBottom"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-3 scale-90"
                 class="absolute bottom-3 left-4 z-20">
                <button type="button"
                        @click="scrollToBottom(true)"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-500/30 transition-all hover:scale-105 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    <span>جدیدترین پیام‌ها</span>
                    <span x-show="newMessagesCount > 0"
                          x-text="faNum(newMessagesCount)"
                          class="px-1.5 py-0.5 rounded-full bg-amber-400 text-gray-900 text-[10px] font-black animate-pulse"></span>
                </button>
            </div>
        </div>

        {{-- Message Composer with Mention, Reply, and Emoji Support --}}
        <form @submit.prevent="sendMessage()"
              class="pt-4 border-t border-gray-100 dark:border-gray-700/50 space-y-3">
            @csrf

            {{-- Hidden Reply Parent ID --}}
            <input type="hidden" name="parent_id" :value="replyingTo ? replyingTo.id : ''">

            {{-- Reply Preview Bar (above textarea) --}}
            <div x-show="replyingTo"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="flex items-center justify-between gap-3 p-2.5 px-4 rounded-2xl bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-200/80 dark:border-indigo-800/60 shadow-2xs">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <div
                        class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 17 4 12 9 7"></polyline>
                            <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-900 dark:text-indigo-200">
                            <span>پاسخ به</span>
                            <span class="text-indigo-600 dark:text-indigo-400"
                                  x-text="replyingTo ? replyingTo.userName : ''"></span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5"
                           x-text="replyingTo ? replyingTo.text : ''"></p>
                    </div>
                </div>
                <button type="button"
                        @click="cancelReply()"
                        class="p-1 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                        title="لغو پاسخ">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Textarea Container --}}
            <div class="relative">
                <textarea name="body"
                          rows="3"
                          required
                          x-ref="messageTextarea"
                          x-model="messageText"
                          @input="handleInput($event)"
                          @keydown="handleKeydown($event)"
                          @keydown.ctrl.enter.prevent="sendMessage()"
                          @keydown.meta.enter.prevent="sendMessage()"
                          placeholder="پیام یا یادداشت جدید بنویسید... (برای منشن کردن کاراکتر @ را تایپ کنید)"
                          class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-3.5 pl-24 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white leading-relaxed text-right placeholder-gray-400 resize-y min-h-[95px]"></textarea>

                {{-- Mention Autocomplete Floating Dropdown --}}
                <div x-show="mentionOpen && filteredMembers.length > 0"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     @click.outside="mentionOpen = false"
                     class="absolute bottom-full mb-2 right-4 z-50 w-72 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl p-2 space-y-1 max-h-56 overflow-y-auto">
                    <div
                        class="px-2.5 py-1 text-[11px] font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between select-none">
                        <span>اعضای قابل منشن</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-mono font-black">@</span>
                    </div>
                    <template x-for="(member, idx) in filteredMembers" :key="member.id">
                        <button type="button"
                                @click="insertMention(member.name)"
                                :class="mentionSelectedIndex === idx ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-800 dark:text-gray-100'"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-colors text-right cursor-pointer">
                            <span
                                class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black shrink-0 transition-colors"
                                :class="mentionSelectedIndex === idx ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-600 dark:text-indigo-300'"
                                x-text="member.initial"></span>
                            <div class="truncate text-right">
                                <div class="font-bold truncate" x-text="member.name"></div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Toolbar Buttons (Mention + Emoji) --}}
                <div class="absolute bottom-3 left-3 flex items-center gap-1.5"
                     @click.outside="openEmojiPicker = false">

                    {{-- Mention Trigger Button (@) --}}
                    <button type="button"
                            @click="triggerMentionButton()"
                            class="p-2 rounded-xl bg-white dark:bg-gray-800 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200 dark:border-gray-700 shadow-sm hover:scale-105 transition-all flex items-center justify-center font-bold text-xs cursor-pointer"
                            title="منشن کردن اعضا (@)">
                        <span class="text-sm font-black leading-none">@</span>
                    </button>

                    {{-- Emoji Picker Popover Button --}}
                    <button type="button"
                            @click="toggleEmojiPicker()"
                            :class="openEmojiPicker ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:scale-105 transition-all flex items-center justify-center cursor-pointer"
                            title="ایموجی (یا کلیدهای Win + . در ویندوز / Cmd + Ctrl + Space در مک)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>

                    {{-- Dynamic Emoji Picker Popover --}}
                    <div x-show="openEmojiPicker"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute bottom-12 left-0 z-50 w-72 sm:w-80 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl p-3.5 space-y-2.5">

                        {{-- Tabs generated dynamically --}}
                        <div
                            class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2 gap-1 overflow-x-auto">
                            <template x-for="(tab, index) in categories" :key="index">
                                <button type="button"
                                        @click="activeTab = index"
                                        :class="activeTab === index ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-bold shadow-xs' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                                        class="px-2.5 py-1 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer"
                                        x-text="tab.name">
                                </button>
                            </template>
                            <button type="button" @click="openEmojiPicker = false"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-1 cursor-pointer">
                                ✕
                            </button>
                        </div>

                        {{-- Emoji Grid dynamically generated from Unicode code points --}}
                        <div class="grid grid-cols-7 gap-1.5 max-h-48 overflow-y-auto p-1 text-lg">
                            <template x-for="char in currentCategoryEmojis" :key="char">
                                <button type="button"
                                        @click="insertEmoji(char)"
                                        class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:scale-125 transition-all select-none cursor-pointer"
                                        x-text="char">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    همه اعضای تیم این پیام را مشاهده خواهند کرد.
                </span>

                <button type="submit"
                        :disabled="isSending || !messageText.trim()"
                        class="px-6 py-2.5 rounded-2xl bg-indigo-600 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-indigo-500/25 hover:bg-indigo-700 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                    <svg x-show="!isSending" class="w-4 h-4 rotate-180 transform" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <svg x-show="isSending" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="isSending ? 'در حال ارسال...' : 'ارسال پیام'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function projectChatComponent() {
        return {
            isModalOpen: false,
            openEmojiPicker: false,
            activeTab: 0,
            replyingTo: null,
            mentionOpen: false,
            mentionQuery: '',
            mentionSelectedIndex: 0,
            mentionStartIndex: -1,
            members: @json($mentionableUsersList),
            searchOpen: false,
            searchQuery: '',
            senderFilter: 'all',
            filterType: 'all',
            messages: @json($messagesDataForJs),
            messageText: '',
            isSending: false,
            showScrollBottom: false,
            newMessagesCount: 0,

            get hasActiveFilters() {
                return this.searchQuery.trim().length > 0 || this.senderFilter !== 'all' || this.filterType !== 'all';
            },

            get visibleMessageIds() {
                const q = this.searchQuery.trim().toLowerCase();
                const s = this.senderFilter;
                const t = this.filterType;

                const matched = new Set();
                for (const m of this.messages) {
                    if (q) {
                        const inBody = m.body && m.body.toLowerCase().includes(q);
                        const inUser = m.user_name && m.user_name.toLowerCase().includes(q);
                        if (!inBody && !inUser) continue;
                    }

                    if (s !== 'all') {
                        if (s === 'me' && !m.is_mine) continue;
                        if (s !== 'me' && String(m.user_id) !== String(s)) continue;
                    }

                    if (t === 'pinned' && !m.is_pinned) continue;
                    if (t === 'mentions_to_me' && !m.mentions_me) continue;
                    if (t === 'replies' && !m.has_reply) continue;

                    matched.add(m.id);
                }
                return matched;
            },

            get visibleCount() {
                return this.visibleMessageIds.size;
            },

            isMessageVisible(id) {
                if (!this.hasActiveFilters) return true;
                return this.visibleMessageIds.has(id);
            },

            resetFilters() {
                this.searchQuery = '';
                this.senderFilter = 'all';
                this.filterType = 'all';
            },

            faNum(str) {
                if (str === null || str === undefined) return '';
                const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                return String(str).replace(/[0-9]/g, w => persian[+w]);
            },

            categories: [
                {name: 'صورتک‌ها', range: [0x1F600, 0x1F644]},
                {name: 'دست‌ها', range: [0x1F44B, 0x1F450]},
                {name: 'قلب و نماد', range: [0x1F493, 0x1F4A5]},
                {name: 'کار و اشیاء', range: [0x1F4BC, 0x1F4DD]},
                {name: 'جشن و رویداد', range: [0x1F380, 0x1F38F]}
            ],

            get currentCategoryEmojis() {
                const cat = this.categories[this.activeTab];
                if (!cat) return [];
                const list = [];
                for (let cp = cat.range[0]; cp <= cat.range[1]; cp++) {
                    list.push(String.fromCodePoint(cp));
                }
                return list;
            },

            get filteredMembers() {
                if (!this.mentionQuery) return this.members;
                const q = this.mentionQuery.toLowerCase();
                return this.members.filter(m => m.name.toLowerCase().includes(q));
            },

            startReply(id, userName, text) {
                this.replyingTo = {id, userName, text};
                this.$nextTick(() => {
                    const textarea = this.$refs.messageTextarea;
                    if (textarea) {
                        textarea.focus();
                    }
                });
            },

            cancelReply() {
                this.replyingTo = null;
            },

            handleInput(event) {
                const textarea = event.target;
                const val = textarea.value;
                const cursor = textarea.selectionStart;
                const textBeforeCursor = val.substring(0, cursor);
                const lastAtPos = textBeforeCursor.lastIndexOf('@');

                if (lastAtPos !== -1) {
                    const query = textBeforeCursor.substring(lastAtPos + 1);
                    if (!/\s/.test(query)) {
                        this.mentionOpen = true;
                        this.mentionQuery = query;
                        this.mentionStartIndex = lastAtPos;
                        this.mentionSelectedIndex = 0;
                        return;
                    }
                }

                this.mentionOpen = false;
                this.mentionQuery = '';
                this.mentionStartIndex = -1;
            },

            handleKeydown(event) {
                if (!this.mentionOpen || this.filteredMembers.length === 0) {
                    return;
                }

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.mentionSelectedIndex = (this.mentionSelectedIndex + 1) % this.filteredMembers.length;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.mentionSelectedIndex = (this.mentionSelectedIndex - 1 + this.filteredMembers.length) % this.filteredMembers.length;
                } else if (event.key === 'Enter' || event.key === 'Tab') {
                    if (this.mentionOpen && this.filteredMembers[this.mentionSelectedIndex]) {
                        event.preventDefault();
                        this.insertMention(this.filteredMembers[this.mentionSelectedIndex].name);
                    }
                } else if (event.key === 'Escape') {
                    this.mentionOpen = false;
                }
            },

            insertMention(name) {
                const textarea = this.$refs.messageTextarea;
                if (!textarea) return;

                const val = textarea.value;
                const cursor = textarea.selectionStart;
                const atPos = this.mentionStartIndex !== -1 ? this.mentionStartIndex : val.lastIndexOf('@', cursor);

                let before = '';
                let after = '';

                if (atPos !== -1) {
                    before = val.substring(0, atPos);
                    after = val.substring(cursor);
                } else {
                    before = val.substring(0, cursor);
                    after = val.substring(cursor);
                }

                const mentionText = '@' + name + ' ';
                textarea.value = before + mentionText + after;
                this.messageText = textarea.value;

                const newCursor = before.length + mentionText.length;
                textarea.focus();
                textarea.setSelectionRange(newCursor, newCursor);

                this.mentionOpen = false;
                this.mentionQuery = '';
                this.mentionStartIndex = -1;
            },

            triggerMentionButton() {
                const textarea = this.$refs.messageTextarea;
                if (!textarea) return;

                const cursor = textarea.selectionStart;
                const val = textarea.value;

                textarea.value = val.substring(0, cursor) + '@' + val.substring(cursor);
                this.messageText = textarea.value;
                const newPos = cursor + 1;
                textarea.focus();
                textarea.setSelectionRange(newPos, newPos);

                this.mentionOpen = true;
                this.mentionQuery = '';
                this.mentionStartIndex = cursor;
                this.mentionSelectedIndex = 0;
            },

            toggleEmojiPicker() {
                this.openEmojiPicker = !this.openEmojiPicker;
            },

            insertEmoji(emoji) {
                const textarea = this.$refs.messageTextarea;
                if (!textarea) return;

                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const text = textarea.value;

                textarea.value = text.substring(0, start) + emoji + text.substring(end);
                this.messageText = textarea.value;

                const newPos = start + emoji.length;
                textarea.focus();
                textarea.setSelectionRange(newPos, newPos);
            },

            handleScroll() {
                const el = this.$refs.messagesContainer;
                if (!el) return;
                const distanceToBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
                this.showScrollBottom = distanceToBottom > 120;
                if (!this.showScrollBottom) {
                    this.newMessagesCount = 0;
                }
            },

            scrollToBottom(smooth = false) {
                this.newMessagesCount = 0;
                this.$nextTick(() => {
                    const el = this.$refs.messagesContainer;
                    if (el) {
                        if (smooth) {
                            el.scrollTo({top: el.scrollHeight, behavior: 'smooth'});
                        } else {
                            el.scrollTop = el.scrollHeight;
                        }
                        this.showScrollBottom = false;
                    }
                });
            },

            scrollToMessage(id) {
                if (!id) return;
                this.$nextTick(() => {
                    const el = document.getElementById('message-' + id);
                    if (el) {
                        el.scrollIntoView({behavior: 'smooth', block: 'center'});
                        el.classList.add('ring-4', 'ring-indigo-400/50', 'rounded-3xl');
                        setTimeout(() => {
                            el.classList.remove('ring-4', 'ring-indigo-400/50', 'rounded-3xl');
                        }, 2000);
                    }
                });
            },

            sse: null,
            lastMessageId: {{ $project->messages->isNotEmpty() ? $project->messages->last()->id : 0 }},

            connectSse() {
                // Close any existing connection
                if (this.sse) {
                    this.sse.close();
                    this.sse = null;
                }

                const url = `/user/projects/projects/{{ $project->id }}/messages/sse?last_id=${this.lastMessageId}`;
                this.sse = new EventSource(url);

                this.sse.addEventListener('new_message', (e) => {
                    const msg = JSON.parse(e.data);
                    // Update lastMessageId
                    if (msg.id > this.lastMessageId) {
                        this.lastMessageId = msg.id;
                    }
                    // Don't add if already exists in DOM
                    if (document.getElementById('message-' + msg.id)) return;
                    // Add to allMessages reactive array for filtering
                    this.allMessages = this.allMessages || this.messages || [];
                    this.allMessages.push({
                        id: msg.id,
                        user_id: msg.user_id,
                        user_name: msg.user_name,
                        body: msg.body,
                        is_pinned: msg.is_pinned,
                        has_reply: !!msg.parent_id,
                        is_mine: msg.is_mine,
                        mentions_me: false,
                    });
                    // Append to DOM
                    this.appendMessageToDOM(msg);
                    if (this.showScrollBottom) {
                        this.newMessagesCount++;
                    } else {
                        this.scrollToBottom();
                    }
                });

                this.sse.addEventListener('messages_deleted', (e) => {
                    const data = JSON.parse(e.data);
                    data.ids.forEach(id => {
                        document.getElementById('message-' + id)?.remove();
                        if (this.allMessages) {
                            this.allMessages = this.allMessages.filter(m => m.id !== id);
                        }
                    });
                });

                this.sse.addEventListener('pin_updated', (e) => {
                    const data = JSON.parse(e.data);
                    data.updates.forEach(u => {
                        const el = document.getElementById('message-' + u.id);
                        if (el) {
                            if (u.is_pinned) {
                                el.classList.add('border-amber-300', 'bg-amber-50/50');
                            } else {
                                el.classList.remove('border-amber-300', 'bg-amber-50/50');
                            }
                        }
                        if (this.allMessages) {
                            const m = this.allMessages.find(m => m.id === u.id);
                            if (m) m.is_pinned = u.is_pinned;
                        }
                    });
                });

                this.sse.addEventListener('reconnect', () => {
                    this.sse.close();
                    // Reconnect after 1 second
                    setTimeout(() => this.connectSse(), 1000);
                });

                this.sse.onerror = () => {
                    this.sse.close();
                    this.sse = null;
                    // Reconnect after 3 seconds on error
                    setTimeout(() => this.connectSse(), 3000);
                };
            },

            appendMessageToDOM(msg) {
                const container = this.$refs.messagesContainer;
                if (!container) return;

                const isRtl = document.documentElement.dir === 'rtl' || document.dir === 'rtl';
                const initials = msg.user_initial || msg.user_name?.charAt(0) || '?';

                const div = document.createElement('div');
                div.id = 'message-' + msg.id;
                div.className = 'flex items-start gap-3.5 ' + (msg.is_mine ? 'flex-row-reverse justify-start' : 'justify-start') + ' transition-all duration-300';

                div.innerHTML = `
                    <div class="w-9 h-9 rounded-2xl ${msg.is_mine ? 'bg-indigo-600 text-white ring-2 ring-indigo-100 dark:ring-indigo-900/50' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-2 ring-gray-200/50 dark:ring-gray-700/50'} flex items-center justify-center text-xs font-black shrink-0 shadow-sm select-none" title="${msg.user_name}">
                        ${initials}
                    </div>
                    <div class="max-w-xl group relative">
                        <div class="p-4 rounded-3xl transition-all ${msg.is_pinned ? 'ring-2 ring-amber-400/70 dark:ring-amber-500/60 bg-amber-50/30 dark:bg-amber-950/20' : ''} ${msg.is_mine ? 'bg-indigo-50/90 dark:bg-indigo-950/50 rounded-tl-xs text-indigo-950 dark:text-indigo-100 border border-indigo-100 dark:border-indigo-800/50 shadow-sm' : 'bg-gray-50/90 dark:bg-gray-900/60 rounded-tr-xs text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-800 shadow-sm'}">
                            <div class="flex items-center justify-between gap-4 mb-2 pb-1.5 border-b ${msg.is_mine ? 'border-indigo-100/80 dark:border-indigo-900/60' : 'border-gray-100 dark:border-gray-800/80'}">
                                <span class="font-bold text-xs flex items-center gap-1.5 ${msg.is_mine ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-200'}">
                                    ${msg.is_mine ? '<span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>شما' : msg.user_name}
                                </span>
                                <div class="flex items-center gap-1.5 text-[11px] ${msg.is_mine ? 'text-indigo-400 dark:text-indigo-300/80' : 'text-gray-400 dark:text-gray-400'}">
                                    <span class="font-bold text-xs">همین الان</span>
                                </div>
                            </div>
                            <div class="text-sm leading-relaxed whitespace-pre-line select-text text-right break-words" dir="auto">${msg.body.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            },

            async sendMessage() {
                const text = (this.messageText || '').trim();
                if (!text || this.isSending) return;

                this.isSending = true;
                const parentId = this.replyingTo ? this.replyingTo.id : null;

                try {
                    const res = await fetch('{{ route('projects.projects.messages.store', $project) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            body: text,
                            parent_id: parentId
                        })
                    });

                    if (res.ok) {
                        const data = await res.json();
                        this.messageText = '';
                        this.cancelReply();

                        if (this.$refs.messageTextarea) {
                            this.$refs.messageTextarea.value = '';
                            this.$refs.messageTextarea.style.height = '';
                        }

                        if (data && data.id) {
                            if (!document.getElementById('message-' + data.id)) {
                                const payload = {
                                    id: data.id,
                                    user_id: data.user_id,
                                    user_name: data.user?.name ?? 'شما',
                                    user_initial: (data.user?.name ?? 'U').charAt(0),
                                    body: data.body,
                                    is_pinned: !!data.is_pinned,
                                    parent_id: data.parent_id,
                                    is_mine: true,
                                };
                                this.appendMessageToDOM(payload);
                                this.messages.push(payload);
                                if (data.id > this.lastMessageId) {
                                    this.lastMessageId = data.id;
                                }
                            }
                            this.scrollToBottom();
                        }
                    } else {
                        const err = await res.json();
                        alert(err.message || 'خطا در ارسال پیام');
                    }
                } catch (e) {
                    console.error('Send message error:', e);
                } finally {
                    this.isSending = false;
                    this.$nextTick(() => {
                        if (this.$refs.messageTextarea) {
                            this.$refs.messageTextarea.focus();
                        }
                    });
                }
            },

            async togglePin(messageId) {
                if (!messageId) return;
                try {
                    const res = await fetch(`/user/projects/projects/{{ $project->id }}/messages/${messageId}/pin`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const el = document.getElementById('message-' + messageId);
                        if (el) {
                            if (data.is_pinned) {
                                el.classList.add('border-amber-300', 'bg-amber-50/50');
                            } else {
                                el.classList.remove('border-amber-300', 'bg-amber-50/50');
                            }
                        }
                        const btn = document.getElementById('pin-btn-' + messageId);
                        if (btn) {
                            if (data.is_pinned) {
                                btn.className = 'p-1.5 rounded-full bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700 border shadow-md hover:scale-110 transition-all cursor-pointer';
                            } else {
                                btn.className = 'p-1.5 rounded-full bg-white dark:bg-gray-800 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 border-gray-200 dark:border-gray-700 border shadow-md hover:scale-110 transition-all cursor-pointer';
                            }
                        }
                        const m = this.messages.find(msg => msg.id === messageId);
                        if (m) m.is_pinned = data.is_pinned;
                    }
                } catch (e) {
                    console.error('Pin error:', e);
                }
            },

            async deleteMessage(messageId) {
                if (!messageId || !confirm('آیا از حذف این پیام اطمینان دارید؟')) return;
                try {
                    const res = await fetch(`/user/projects/projects/{{ $project->id }}/messages/${messageId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        document.getElementById('message-' + messageId)?.remove();
                        this.messages = this.messages.filter(m => m.id !== messageId);
                    }
                } catch (e) {
                    console.error('Delete error:', e);
                }
            },

            init() {
                this.scrollToBottom();
                setTimeout(() => this.scrollToBottom(), 100);
                setTimeout(() => this.scrollToBottom(), 300);

                window.addEventListener('tab-changed', (e) => {
                    if (e.detail === 'messages') {
                        setTimeout(() => this.scrollToBottom(), 50);
                        setTimeout(() => this.scrollToBottom(), 200);
                        // (Re)start SSE when tab is opened
                        this.connectSse();
                    }
                });

                // Start SSE on load if already on messages tab
                this.connectSse();
            }
        };
    }
</script>
