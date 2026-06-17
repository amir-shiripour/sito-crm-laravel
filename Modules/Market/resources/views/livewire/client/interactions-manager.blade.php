<div>
    {{-- Header --}}
    <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                دیدگاه‌ها و پرسش‌های من
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                در این بخش می‌توانید نظرات و پرسش‌های ثبت شده خود را مشاهده و مدیریت کنید.
            </p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="inline-flex items-center justify-center gap-2 px-4 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 font-bold text-xs transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            بازگشت به داشبورد
        </a>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 text-xs font-bold text-emerald-800 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex flex-wrap items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-2">
        <button wire:click="setTab('reviews')" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'reviews' ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 dark:border-indigo-800/50 border' : 'bg-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 border border-transparent' }}">
            دیدگاه‌های من
        </button>
        <button wire:click="setTab('questions')" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'questions' ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 dark:border-indigo-800/50 border' : 'bg-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 border border-transparent' }}">
            پرسش‌های من
        </button>
    </div>

    {{-- Content --}}
    <div class="space-y-6">
        @if($activeTab === 'reviews')
            {{-- Reviews List --}}
            @forelse($reviews as $review)
                <div class="bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 md:p-6 shadow-sm shadow-gray-100/30 dark:shadow-none transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            {{-- Product Image --}}
                            <div class="w-14 h-14 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden bg-gray-50 dark:bg-gray-900 shrink-0 flex items-center justify-center">
                                @if($review->masterProduct && $review->masterProduct->main_image)
                                    <img src="{{ Storage::url($review->masterProduct->main_image) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                @endif
                            </div>
                            
                            {{-- Info --}}
                            <div>
                                <a href="{{ route('market.public.product.show', $review->masterProduct->slug ?? '') }}" target="_blank" class="text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-1 mb-1">
                                    {{ $review->masterProduct->title ?? 'محصول نامشخص' }}
                                </a>
                                
                                {{-- Stars --}}
                                @if($editingReviewId !== $review->id)
                                    <div class="flex items-center gap-0.5 text-amber-500 mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-200 dark:text-gray-700' }}" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                @endif

                                {{-- Meta --}}
                                <div class="flex flex-wrap items-center gap-2 text-[10px] font-medium text-gray-500 dark:text-gray-400">
                                    <span>{{ \Morilog\Jalali\Jalalian::fromCarbon($review->created_at)->format('%d %B %Y') }}</span>
                                    @if($review->vendorProduct)
                                        <span>•</span>
                                        <span>فروشگاه: {{ $review->vendorProduct->vendor->store_name ?? '---' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status & Actions --}}
                        <div class="flex items-center sm:flex-col sm:items-end justify-between gap-3 border-t sm:border-0 border-gray-100 dark:border-gray-800 pt-4 sm:pt-0 shrink-0">
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-50 text-amber-700 border-amber-200/50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'],
                                    'approved' => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20'],
                                    'rejected' => ['label' => 'رد شده', 'class' => 'bg-rose-50 text-rose-700 border-rose-200/50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20'],
                                ];
                                $s = $statusMap[$review->status] ?? $statusMap['pending'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>

                            @if(in_array($review->status, ['pending', 'rejected']) && $editingReviewId !== $review->id)
                                <div class="flex items-center gap-2">
                                    <button wire:click="startEditReview({{ $review->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 transition-colors" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    @if($review->status === 'pending')
                                        <button onclick="confirm('آیا از حذف این دیدگاه اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="deleteReview({{ $review->id }})" class="p-1.5 text-gray-400 hover:text-rose-600 dark:text-gray-500 dark:hover:text-rose-400 transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Review Content or Edit Form --}}
                    <div class="mt-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                        @if($editingReviewId === $review->id)
                            <form wire:submit.prevent="updateReview" class="space-y-3">
                                <div>
                                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-2">امتیاز (از ۵):</label>
                                    <div class="flex items-center gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="$set('editReviewRating', {{ $i }})" class="focus:outline-none">
                                                <svg class="w-6 h-6 {{ $i <= $editReviewRating ? 'text-amber-500 fill-current' : 'text-gray-200 dark:text-gray-700' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </button>
                                        @endfor
                                    </div>
                                    @error('editReviewRating') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <textarea wire:model.defer="editReviewComment" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:text-white resize-none" placeholder="متن دیدگاه..."></textarea>
                                    @error('editReviewComment') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">انصراف</button>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors">ذخیره تغییرات</button>
                                </div>
                            </form>
                        @else
                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium whitespace-pre-wrap">{{ $review->comment }}</p>
                            
                            @if($review->rejection_reason)
                                <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-900/20 border-r-2 border-rose-500 rounded-l-lg">
                                    <span class="block text-[10px] font-bold text-rose-800 dark:text-rose-400 mb-1">علت رد شدن:</span>
                                    <p class="text-xs text-rose-700 dark:text-rose-300">{{ $review->rejection_reason }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-800">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500">هیچ دیدگاهی ثبت نکرده‌اید.</p>
                </div>
            @endforelse

            @if($reviews->hasPages())
                <div class="mt-4">
                    {{ $reviews->links() }}
                </div>
            @endif

        @else
            {{-- Questions List --}}
            @forelse($questions as $question)
                <div class="bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 md:p-6 shadow-sm shadow-gray-100/30 dark:shadow-none transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            {{-- Product Image --}}
                            <div class="w-14 h-14 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden bg-gray-50 dark:bg-gray-900 shrink-0 flex items-center justify-center">
                                @if($question->masterProduct && $question->masterProduct->main_image)
                                    <img src="{{ Storage::url($question->masterProduct->main_image) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                @endif
                            </div>
                            
                            {{-- Info --}}
                            <div>
                                <a href="{{ route('market.public.product.show', $question->masterProduct->slug ?? '') }}" target="_blank" class="text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-1 mb-1.5">
                                    {{ $question->masterProduct->title ?? 'محصول نامشخص' }}
                                </a>

                                <div class="flex flex-wrap items-center gap-2 text-[10px] font-medium text-gray-500 dark:text-gray-400">
                                    <span>{{ \Morilog\Jalali\Jalalian::fromCarbon($question->created_at)->format('%d %B %Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Status & Actions --}}
                        <div class="flex items-center sm:flex-col sm:items-end justify-between gap-3 border-t sm:border-0 border-gray-100 dark:border-gray-800 pt-4 sm:pt-0 shrink-0">
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-50 text-amber-700 border-amber-200/50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'],
                                    'approved' => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20'],
                                    'rejected' => ['label' => 'رد شده', 'class' => 'bg-rose-50 text-rose-700 border-rose-200/50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20'],
                                ];
                                $s = $statusMap[$question->status] ?? $statusMap['pending'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>

                            @if(in_array($question->status, ['pending', 'rejected']) && $editingQuestionId !== $question->id)
                                <div class="flex items-center gap-2">
                                    <button wire:click="startEditQuestion({{ $question->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 transition-colors" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    @if($question->status === 'pending')
                                        <button onclick="confirm('آیا از حذف این پرسش اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="deleteQuestion({{ $question->id }})" class="p-1.5 text-gray-400 hover:text-rose-600 dark:text-gray-500 dark:hover:text-rose-400 transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Question Content or Edit Form --}}
                    <div class="mt-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                        @if($editingQuestionId === $question->id)
                            <form wire:submit.prevent="updateQuestion" class="space-y-3">
                                <div>
                                    <textarea wire:model.defer="editQuestionText" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:text-white resize-none" placeholder="متن پرسش..."></textarea>
                                    @error('editQuestionText') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">انصراف</button>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors">ذخیره تغییرات</button>
                                </div>
                            </form>
                        @else
                            <div class="flex items-start gap-2">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-black shrink-0 mt-0.5">س</span>
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed font-bold">{{ $question->text }}</p>
                            </div>
                            
                            @if($question->rejection_reason)
                                <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-900/20 border-r-2 border-rose-500 rounded-l-lg mr-7">
                                    <span class="block text-[10px] font-bold text-rose-800 dark:text-rose-400 mb-1">علت رد شدن:</span>
                                    <p class="text-xs text-rose-700 dark:text-rose-300">{{ $question->rejection_reason }}</p>
                                </div>
                            @endif

                            {{-- Replies --}}
                            @if($question->replies->isNotEmpty())
                                <div class="mt-4 mr-7 space-y-3 border-r-2 border-gray-100 dark:border-gray-800 pr-4">
                                    <span class="text-[10px] font-bold text-gray-400 block mb-2">پاسخ‌ها:</span>
                                    @foreach($question->replies as $reply)
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-bold text-gray-800 dark:text-gray-200">
                                                        @if($reply->user_id)
                                                            مدیریت سایت
                                                        @elseif($reply->vendor_id)
                                                            فروشگاه: {{ $reply->vendor->store_name ?? '---' }}
                                                        @else
                                                            {{ $reply->client->full_name ?? 'کاربر' }}
                                                        @endif
                                                    </span>
                                                    @if($reply->user_id)
                                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[9px] font-bold">مدیر</span>
                                                    @endif
                                                </div>
                                                <span class="text-[9px] text-gray-400">{{ \Morilog\Jalali\Jalalian::fromCarbon($reply->created_at)->ago() }}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $reply->text }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-800">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500">هیچ پرسشی ثبت نکرده‌اید.</p>
                </div>
            @endforelse

            @if($questions->hasPages())
                <div class="mt-4">
                    {{ $questions->links() }}
                </div>
            @endif

        @endif
    </div>
</div>
