<div id="product-questions-section" class="scroll-mt-36">
    {{-- هدر بخش پرسش و پاسخ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100/80 dark:border-gray-800/80">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
            <span class="w-1.5 h-6 bg-{{ $t['name'] ?? 'indigo' }}-600 dark:bg-{{ $t['name'] ?? 'indigo' }}-500 rounded-full"></span>
            پرسش و پاسخ کاربران
        </h2>
        
        <button 
            wire:click="toggleForm" 
            class="cursor-pointer inline-flex items-center justify-center gap-2 px-4 h-10 rounded-xl bg-{{ $t['name'] ?? 'indigo' }}-600 hover:bg-{{ $t['name'] ?? 'indigo' }}-700 text-white font-bold text-xs transition-all duration-300 shadow-sm shadow-{{ $t['name'] ?? 'indigo' }}-600/10 dark:shadow-none"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                @if($showForm)
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                @endif
            </svg>
            {{ $showForm ? 'بستن فرم' : 'طرح پرسش جدید' }}
        </button>
    </div>

    {{-- فرم ثبت پرسش جدید --}}
    @if($showForm)
        <div class="bg-gradient-to-br from-white to-gray-50/30 dark:from-gray-900/50 dark:to-gray-900/20 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 md:p-6 mb-6 shadow-sm shadow-gray-100/30 dark:shadow-none transition-all duration-400">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2 h-4 bg-{{ $t['name'] ?? 'indigo' }}-500 rounded-full"></span>
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">سوال خود را درباره این محصول مطرح کنید:</span>
            </div>

            @if(!auth()->guard('client')->check() && !auth()->check())
                <div class="text-center py-6 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">برای ثبت پرسش ابتدا باید وارد حساب کاربری خود شوید.</p>
                    <a href="/login" class="inline-flex items-center justify-center px-4 h-9 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">
                        ورود به حساب کاربری
                    </a>
                </div>
            @else
                <form wire:submit.prevent="submitQuestion" class="space-y-4">
                    <div>
                        <textarea 
                            wire:model.defer="questionText" 
                            rows="4" 
                            placeholder="متن پرسش خود را اینجا بنویسید (حداقل ۵ کاراکتر)..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:border-{{ $t['name'] ?? 'indigo' }}-500 focus:ring-1 focus:ring-{{ $t['name'] ?? 'indigo' }}-500 focus:outline-none transition-colors"
                        ></textarea>
                        @error('questionText')
                            <span class="text-rose-600 dark:text-rose-500 text-xs font-bold block mt-1.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="toggleForm" 
                            class="cursor-pointer px-4 h-9 rounded-lg border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 font-bold text-xs hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
                        >
                            انصراف
                        </button>
                        <button 
                            type="submit" 
                            class="cursor-pointer px-5 h-9 rounded-lg bg-{{ $t['name'] ?? 'indigo' }}-600 hover:bg-{{ $t['name'] ?? 'indigo' }}-600 text-white font-bold text-xs shadow-sm shadow-{{ $t['name'] ?? 'indigo' }}-600/10 dark:shadow-none transition-colors"
                        >
                            ثبت پرسش
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- پیام‌های عمومی --}}
    @if(session()->has('message'))
        <div class="mb-6 p-4 text-xs font-bold text-emerald-800 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
            {{ session('message') }}
        </div>
    @endif

    {{-- لیست پرسش‌ها و پاسخ‌ها --}}
    <div class="space-y-6">
        @php
            $liked = session()->get('liked_questions', []);
            $disliked = session()->get('disliked_questions', []);
        @endphp

        @forelse($questions as $q)
            @php
                $isQuestionLiked = in_array($q->id, $liked);
                $isQuestionDisliked = in_array($q->id, $disliked);
            @endphp

            <div class="bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 p-5 md:p-6 shadow-sm shadow-gray-50/50 dark:shadow-none">
                
                {{-- ردیف اطلاعات پرسش‌کننده --}}
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- آواتار --}}
                        <div class="shrink-0">
                            @php
                                $avatarUrl = null;
                                if ($q->client) {
                                    if (!empty($q->client->avatar)) {
                                        $avatarUrl = $q->client->avatar;
                                    } elseif (!empty($q->client->meta['avatar'])) {
                                        $avatarUrl = $q->client->meta['avatar'];
                                    } elseif (!empty($q->client->profile_photo_url)) {
                                        $avatarUrl = $q->client->profile_photo_url;
                                    }
                                } elseif ($q->vendor) {
                                    if (!empty($q->vendor->logo)) {
                                        $avatarUrl = $q->vendor->logo;
                                    }
                                }
                                if ($avatarUrl && !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
                                    $avatarUrl = \Illuminate\Support\Facades\Storage::url($avatarUrl);
                                }
                            @endphp
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" class="w-9 h-9 rounded-full object-cover border border-gray-100 dark:border-gray-800" alt="avatar">
                            @else
                                <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 border border-gray-300/20 dark:border-gray-700/20">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- نام و نقش --}}
                        <div class="space-y-0.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    @if($q->user)
                                        {{ $q->user->name ?? 'مدیریت سایت' }}
                                    @elseif($q->vendor)
                                        {{ $q->vendor->store_name }}
                                    @else
                                        {{ $q->client->full_name ?? 'کاربر سایت' }}
                                    @endif
                                </span>

                                {{-- نشان/تگ نقش --}}
                                @if($q->user)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-{{ $t['name'] ?? 'indigo' }}-50 dark:bg-{{ $t['name'] ?? 'indigo' }}-900/40 text-{{ $t['name'] ?? 'indigo' }}-600 dark:text-{{ $t['name'] ?? 'indigo' }}-400 border border-{{ $t['name'] ?? 'indigo' }}-100/50 dark:border-{{ $t['name'] ?? 'indigo' }}-900/30">
                                        مدیریت سایت
                                    </span>
                                @elseif($q->vendor)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 border border-amber-100/50 dark:border-amber-900/30">
                                        فروشنده کالا
                                    </span>
                                @else
                                    @if($this->hasPurchased($q->client_id))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/50 dark:border-emerald-900/30">
                                            خریدار کالا
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                            کاربر سایت
                                        </span>
                                    @endif
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-400 dark:text-gray-500">{{ $q->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    {{-- پاسخ به پرسش --}}
                    <div>
                        <button 
                            wire:click="toggleReplyForm({{ $q->id }})" 
                            class="cursor-pointer inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 hover:text-{{ $t['name'] ?? 'indigo' }}-600 dark:text-gray-500 dark:hover:text-{{ $t['name'] ?? 'indigo' }}-400 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            <span>پاسخ دهید</span>
                        </button>
                    </div>
                </div>

                {{-- متن پرسش --}}
                <div class="mt-3 text-sm font-bold text-gray-800 dark:text-gray-200 leading-relaxed pl-6">
                    <p class="inline-flex items-center justify-center w-5 h-5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 text-[10px] font-black align-middle ml-1.5 shrink-0 select-none">س</p>
                    {{ $q->text }}
                </div>

                {{-- دکمه‌های ری‌اکشن پرسش --}}
                <div class="mt-4 flex items-center justify-between border-t border-gray-50 dark:border-gray-800/40 pt-3">
                    <div class="flex items-center gap-4">
                        <button 
                            wire:click="likeQuestion({{ $q->id }})" 
                            class="flex items-center gap-1.5 text-[11px] font-bold transition-all duration-300 focus:outline-none cursor-pointer {{ $isQuestionLiked ? 'text-emerald-600 scale-105' : 'text-gray-400 hover:text-emerald-500 dark:text-gray-500 dark:hover:text-emerald-400' }}"
                            title="مفید بود"
                        >
                            <svg class="w-4 h-4 {{ $isQuestionLiked ? 'fill-current' : 'fill-none' }} stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.757c1.246 0 2.256 1.01 2.256 2.256 0 .426-.12.842-.347 1.2l-3.556 5.626c-.394.623-1.077.994-1.81.994H7V10l4.382-4.382a1 1 0 011.414 0L14 7v3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 22H4a1 1 0 01-1-1v-7a1 1 0 011-1h3" />
                            </svg>
                            <span>{{ $q->likes_count ?: 0 }}</span>
                        </button>

                        <button 
                            wire:click="dislikeQuestion({{ $q->id }})" 
                            class="flex items-center gap-1.5 text-[11px] font-bold transition-all duration-300 focus:outline-none cursor-pointer {{ $isQuestionDisliked ? 'text-rose-600 scale-105' : 'text-gray-400 hover:text-rose-500 dark:text-gray-500 dark:hover:text-rose-400' }}"
                            title="مفید نبود"
                        >
                            <svg class="w-4 h-4 {{ $isQuestionDisliked ? 'fill-current' : 'fill-none' }} stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.243c-1.246 0-2.256-1.01-2.256-2.256 0-.426.12-.842.347-1.2l3.556-5.626C7.284 4.3 7.967 4.004 8.7 4.004H17v10l-4.382 4.382a1 1 0 01-1.414 0L10 17v-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 2h3a1 1 0 011 1v7a1 1 0 01-1 1h-3" />
                            </svg>
                            <span>{{ $q->dislikes_count ?: 0 }}</span>
                        </button>
                    </div>
                </div>

                {{-- لیست پاسخ‌های تایید شده برای این پرسش --}}
                @if($q->approvedReplies->isNotEmpty())
                    <div class="mt-4 mr-6 sm:mr-10 border-r-2 border-gray-100 dark:border-gray-800/80 pr-4 sm:pr-6 space-y-4">
                        @foreach($q->approvedReplies as $reply)
                            @php
                                $isReplyLiked = in_array($reply->id, $liked);
                                $isReplyDisliked = in_array($reply->id, $disliked);
                            @endphp

                            <div class="bg-gray-50/40 dark:bg-gray-900/10 rounded-xl p-4 border border-gray-100/50 dark:border-gray-800/30">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2">
                                        {{-- آواتار پاسخ دهنده --}}
                                        <div>
                                            @php
                                                $replyAvatarUrl = null;
                                                if ($reply->client) {
                                                    if (!empty($reply->client->avatar)) {
                                                        $replyAvatarUrl = $reply->client->avatar;
                                                    } elseif (!empty($reply->client->meta['avatar'])) {
                                                        $replyAvatarUrl = $reply->client->meta['avatar'];
                                                    } elseif (!empty($reply->client->profile_photo_url)) {
                                                        $replyAvatarUrl = $reply->client->profile_photo_url;
                                                    }
                                                } elseif ($reply->vendor) {
                                                    if (!empty($reply->vendor->logo)) {
                                                        $replyAvatarUrl = $reply->vendor->logo;
                                                    }
                                                }
                                                if ($replyAvatarUrl && !filter_var($replyAvatarUrl, FILTER_VALIDATE_URL)) {
                                                    $replyAvatarUrl = \Illuminate\Support\Facades\Storage::url($replyAvatarUrl);
                                                }
                                            @endphp
                                            @if($replyAvatarUrl)
                                                <img src="{{ $replyAvatarUrl }}" class="w-7 h-7 rounded-full object-cover border border-gray-100 dark:border-gray-800" alt="avatar">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 border border-gray-300/20 dark:border-gray-700/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- اطلاعات نویسنده پاسخ --}}
                                        <div class="space-y-0.5">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-300">
                                                    @if($reply->user)
                                                        {{ $reply->user->name ?? 'مدیریت سایت' }}
                                                    @elseif($reply->vendor)
                                                        {{ $reply->vendor->store_name }}
                                                    @else
                                                        {{ $reply->client->full_name ?? 'کاربر سایت' }}
                                                    @endif
                                                </span>

                                                @if($reply->user)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-{{ $t['name'] ?? 'indigo' }}-50 dark:bg-{{ $t['name'] ?? 'indigo' }}-900/40 text-{{ $t['name'] ?? 'indigo' }}-600 dark:text-{{ $t['name'] ?? 'indigo' }}-400 border border-{{ $t['name'] ?? 'indigo' }}-100/50 dark:border-{{ $t['name'] ?? 'indigo' }}-900/30">
                                                        مدیریت سایت
                                                    </span>
                                                @elseif($reply->vendor)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 border border-amber-100/50 dark:border-amber-900/30">
                                                        فروشنده کالا
                                                    </span>
                                                @else
                                                    @if($this->hasPurchased($reply->client_id))
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/50 dark:border-emerald-900/30">
                                                            خریدار کالا
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                            کاربر سایت
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="text-[9px] text-gray-400 dark:text-gray-500">{{ $reply->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- متن پاسخ --}}
                                <div class="mt-2 text-xs text-gray-700 dark:text-gray-300 leading-relaxed pl-5">
                                    <p class="inline-flex items-center justify-center w-5 h-5 rounded bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-black align-middle ml-1.5 shrink-0 select-none">پ</p>
                                    {{ $reply->text }}
                                </div>

                                {{-- دکمه‌های ری‌اکشن پاسخ --}}
                                <div class="mt-3 flex items-center justify-between border-t border-gray-100/40 dark:border-gray-800/20 pt-2">
                                    <div class="flex items-center gap-3">
                                        <button 
                                            wire:click="likeQuestion({{ $reply->id }})" 
                                            class="flex items-center gap-1 text-[10px] font-bold transition-all duration-300 focus:outline-none cursor-pointer {{ $isReplyLiked ? 'text-emerald-600 scale-105' : 'text-gray-400 hover:text-emerald-500 dark:text-gray-500 dark:hover:text-emerald-400' }}"
                                            title="مفید بود"
                                        >
                                            <svg class="w-3.5 h-3.5 {{ $isReplyLiked ? 'fill-current' : 'fill-none' }} stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.757c1.246 0 2.256 1.01 2.256 2.256 0 .426-.12.842-.347 1.2l-3.556 5.626c-.394.623-1.077.994-1.81.994H7V10l4.382-4.382a1 1 0 011.414 0L14 7v3z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 22H4a1 1 0 01-1-1v-7a1 1 0 011-1h3" />
                                            </svg>
                                            <span>{{ $reply->likes_count ?: 0 }}</span>
                                        </button>

                                        <button 
                                            wire:click="dislikeQuestion({{ $reply->id }})" 
                                            class="flex items-center gap-1 text-[10px] font-bold transition-all duration-300 focus:outline-none cursor-pointer {{ $isReplyDisliked ? 'text-rose-600 scale-105' : 'text-gray-400 hover:text-rose-500 dark:text-gray-500 dark:hover:text-rose-400' }}"
                                            title="مفید نبود"
                                        >
                                            <svg class="w-3.5 h-3.5 {{ $isReplyDisliked ? 'fill-current' : 'fill-none' }} stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.243c-1.246 0-2.256-1.01-2.256-2.256 0-.426.12-.842.347-1.2l3.556-5.626C7.284 4.3 7.967 4.004 8.7 4.004H17v10l-4.382 4.382a1 1 0 01-1.414 0L10 17v-3z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 2h3a1 1 0 011 1v7a1 1 0 01-1 1h-3" />
                                            </svg>
                                            <span>{{ $reply->dislikes_count ?: 0 }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- فرم پاسخ دهی به این پرسش (در صورت فعال بودن) --}}
                @if($showReplyForm[$q->id] ?? false)
                    <div class="mt-4 mr-6 sm:mr-10 border-r-2 border-gray-200 dark:border-gray-800 pr-4 sm:pr-6">
                        @if(session()->has('message_' . $q->id))
                            <div class="mb-3 p-3 text-xs font-bold text-emerald-800 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                {{ session('message_' . $q->id) }}
                            </div>
                        @endif

                        @if(!auth()->guard('client')->check() && !auth()->check())
                            <div class="text-center py-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100/50 dark:border-gray-800/40">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">برای ثبت پاسخ باید وارد حساب کاربری خود شوید.</p>
                                <a href="/login" class="inline-flex items-center justify-center px-3 h-8 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-[11px] hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    ورود به حساب کاربری
                                </a>
                            </div>
                        @else
                            <form wire:submit.prevent="submitReply({{ $q->id }})" class="space-y-3">
                                <div>
                                    <textarea 
                                        wire:model.defer="replyTexts.{{ $q->id }}" 
                                        rows="3" 
                                        placeholder="پاسخ خود را بنویسید (حداقل ۳ کاراکتر)..."
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:border-{{ $t['name'] ?? 'indigo' }}-500 focus:ring-1 focus:ring-{{ $t['name'] ?? 'indigo' }}-500 focus:outline-none transition-colors"
                                    ></textarea>
                                    @error('replyTexts.' . $q->id)
                                        <span class="text-rose-600 dark:text-rose-500 text-xs font-bold block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="flex justify-end gap-2.5">
                                    <button 
                                        type="button" 
                                        wire:click="toggleReplyForm({{ $q->id }})" 
                                        class="cursor-pointer px-3 h-8 rounded-lg border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 font-bold text-[11px] hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
                                    >
                                        انصراف
                                    </button>
                                    <button 
                                        type="submit" 
                                        class="cursor-pointer px-4 h-8 rounded-lg bg-{{ $t['name'] ?? 'indigo' }}-600 hover:bg-{{ $t['name'] ?? 'indigo' }}-600 text-white font-bold text-[11px] shadow-sm shadow-{{ $t['name'] ?? 'indigo' }}-600/10 dark:shadow-none transition-colors"
                                    >
                                        ثبت پاسخ
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif

            </div>
        @empty
            <div class="text-center py-12 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-800/80">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-xs font-bold">هنوز پرسشی برای این محصول مطرح نشده است. اولین پرسش را شما ثبت کنید!</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($questions->hasPages())
        <div class="pt-6 border-t border-gray-100 dark:border-gray-800/60 mt-6">
            {{ $questions->links() }}
        </div>
    @endif
</div>
