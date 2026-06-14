<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">مدیریت پرسش‌ها و پاسخ‌ها</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تایید، رد و یا پاسخ‌دهی به پرسش‌های مطرح شده توسط کاربران و خریداران.</p>
        </div>
    </div>

    {{-- فیلترها و سرچ --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="setFilter('pending')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
                در انتظار بررسی
            </button>
            <button wire:click="setFilter('approved')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
                تایید شده
            </button>
            <button wire:click="setFilter('rejected')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
                رد شده
            </button>
            <button wire:click="setFilter('all')" class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $filterStatus === 'all' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700' }}">
                همه
            </button>
        </div>

        <div class="relative w-full md:w-80">
            <input type="text" wire:model.live.debounce.350ms="search" placeholder="جستجوی پرسش، کاربر، محصول..." class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
    </div>

    {{-- جدول لیست پرسش‌ها --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/80 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="p-4 w-10"></th>
                        <th class="p-4">کاربر نویسنده</th>
                        <th class="p-4">محصول کاتالوگ</th>
                        <th class="p-4">متن پرسش (خلاصه)</th>
                        <th class="p-4">تاریخ ثبت</th>
                        <th class="p-4">وضعیت</th>
                        <th class="p-4 text-left">عملیات</th>
                    </tr>
                </thead>
                @forelse($questions as $question)
                    <tbody wire:key="question-{{ $question->id }}" x-data="{ expanded: false }" class="divide-y divide-gray-100 dark:divide-gray-700 border-transparent">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer group" @click="expanded = !expanded">
                            <td class="p-4 text-center">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 transform transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm border border-indigo-100 dark:border-indigo-500/20">
                                        {{ mb_substr($question->client->full_name ?? $question->client->username ?? 'ک', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $question->client->full_name ?? 'کاربر مهمان' }}</span>
                                        <span class="text-[10px] font-mono text-gray-400 mt-0.5">{{ $question->client->phone ?? $question->client->username ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 overflow-hidden flex-shrink-0">
                                        @if($question->masterProduct && $question->masterProduct->main_image)
                                            <img src="{{ Storage::url($question->masterProduct->main_image) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200 line-clamp-1" style="max-width: 180px;" title="{{ $question->masterProduct->title ?? '' }}">{{ $question->masterProduct->title ?? 'محصول نامشخص' }}</span>
                                        <span class="text-[10px] font-mono text-gray-400 mt-0.5">{{ $question->masterProduct->crm_code ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-semibold line-clamp-1" style="max-width: 250px;">{{ $question->text }}</span>
                            </td>
                            <td class="p-4 text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ \Morilog\Jalali\Jalalian::fromCarbon($question->created_at)->format('Y/m/d H:i') }}
                            </td>
                            <td class="p-4">
                                @if($question->status === 'pending')
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200/50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 text-[10px] px-2 py-1 rounded-lg font-bold">در انتظار بررسی</span>
                                @elseif($question->status === 'approved')
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 text-[10px] px-2 py-1 rounded-lg font-bold">تایید شده</span>
                                @else
                                    <span class="bg-rose-50 text-rose-700 border border-rose-200/50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20 text-[10px] px-2 py-1 rounded-lg font-bold">رد شده</span>
                                @endif
                            </td>
                            <td class="p-4 text-left" @click.stop>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($question->status === 'pending' || $question->status === 'rejected')
                                        <button wire:click="approve({{ $question->id }})" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-white border border-emerald-100 dark:border-emerald-500/20 rounded-lg transition-colors cursor-pointer" title="تایید پرسش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    @endif
                                    @if($question->status === 'pending' || $question->status === 'approved')
                                        <button wire:click="promptReject({{ $question->id }})" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500 dark:hover:text-white border border-rose-100 dark:border-rose-500/20 rounded-lg transition-colors cursor-pointer" title="رد کردن پرسش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    @endif
                                    <button onclick="confirm('آیا از حذف این پرسش و تمامی پاسخ‌های آن اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="delete({{ $question->id }})" class="p-1.5 bg-gray-50 text-gray-500 hover:bg-red-600 hover:text-white dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-red-600 dark:hover:text-white border border-gray-100 dark:border-gray-700 rounded-lg transition-colors cursor-pointer" title="حذف دائمی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- سطر جزئیات متن پرسش و لیست پاسخ‌ها --}}
                        <tr x-show="expanded">
                            <td colspan="7" class="p-0 border-t border-gray-100 dark:border-gray-700">
                                <div x-show="expanded" x-collapse>
                                    <div class="p-6 bg-gray-50/50 dark:bg-gray-900/40 space-y-6">
                                        {{-- متن کامل پرسش --}}
                                        <div>
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1">متن کامل پرسش:</span>
                                            <p class="text-sm text-gray-800 dark:text-gray-100 leading-relaxed font-bold">{{ $question->text }}</p>
                                            
                                            @if($question->rejection_reason)
                                                <div class="p-3 bg-rose-50 dark:bg-rose-500/10 border-r-4 border-rose-500 dark:border-rose-500 rounded-l-lg flex gap-3 items-start mt-3 w-fit">
                                                    <svg class="w-4 h-4 text-rose-500 dark:text-rose-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                                    <div>
                                                        <span class="block text-xs font-bold text-rose-800 dark:text-rose-400">علت رد پرسش:</span>
                                                        <p class="text-xs text-gray-700 dark:text-gray-300 mt-0.5 leading-relaxed">{{ $question->rejection_reason }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- پاسخ‌های ثبت شده --}}
                                        <div class="space-y-4">
                                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800 pb-2">پاسخ‌های ثبت شده:</h4>
                                            
                                            @forelse($question->replies as $reply)
                                                <div class="flex gap-3 items-start bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-500 shrink-0">
                                                        ج
                                                    </div>
                                                    <div class="flex-1 min-w-0 space-y-1">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            @if($reply->user_id)
                                                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/45 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-900/30">مدیریت سایت</span>
                                                            @elseif($reply->vendor_id)
                                                                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/45 px-2 py-0.5 rounded border border-amber-100 dark:border-amber-900/30">فروشنده: {{ $reply->vendor->store_name }}</span>
                                                            @else
                                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $reply->client->full_name ?? 'کاربر سایت' }}</span>
                                                            @endif
                                                            
                                                            <span class="text-[10px] text-gray-400 font-medium">{{ \Morilog\Jalali\Jalalian::fromCarbon($reply->created_at)->format('Y/m/d H:i') }}</span>
                                                            
                                                            {{-- برچسب وضعیت پاسخ --}}
                                                            <div class="mr-auto flex items-center gap-1.5">
                                                                @if($reply->status === 'pending')
                                                                    <span class="bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 text-[9px] px-1.5 py-0.5 rounded font-bold">در انتظار بررسی</span>
                                                                @elseif($reply->status === 'approved')
                                                                    <span class="bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-[9px] px-1.5 py-0.5 rounded font-bold">تایید شده</span>
                                                                @else
                                                                    <span class="bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 text-[9px] px-1.5 py-0.5 rounded font-bold">رد شده</span>
                                                                @endif

                                                                {{-- عملیات تایید/رد پاسخ --}}
                                                                @if($reply->status === 'pending' || $reply->status === 'rejected')
                                                                    <button wire:click="approve({{ $reply->id }})" class="p-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400 rounded transition-colors cursor-pointer" title="تایید پاسخ">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                                    </button>
                                                                @endif
                                                                @if($reply->status === 'pending' || $reply->status === 'approved')
                                                                    <button wire:click="promptReject({{ $reply->id }})" class="p-1 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-500/10 dark:text-rose-400 rounded transition-colors cursor-pointer" title="رد پاسخ">
                                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                                    </button>
                                                                @endif
                                                                <button onclick="confirm('آیا از حذف این پاسخ اطمینان دارید؟') || event.stopImmediatePropagation()" wire:click="delete({{ $reply->id }})" class="p-1 bg-gray-50 text-gray-500 hover:bg-red-600 hover:text-white dark:bg-gray-700 dark:text-gray-400 rounded transition-colors cursor-pointer" title="حذف پاسخ">
                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-800 dark:text-gray-300 leading-relaxed font-semibold">{{ $reply->text }}</p>
                                                        @if($reply->rejection_reason)
                                                            <p class="text-[10px] text-rose-500 dark:text-rose-400">دلیل رد: {{ $reply->rejection_reason }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-500 dark:text-gray-500 italic">هنوز هیچ پاسخی ثبت نشده است.</p>
                                            @endforelse
                                        </div>

                                        {{-- فرم ثبت پاسخ مدیریت --}}
                                        <form wire:submit.prevent="submitReply({{ $question->id }})" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">ثبت پاسخ مدیریت به این پرسش:</label>
                                            <div class="flex gap-2">
                                                <textarea wire:model.defer="replyTexts.{{ $question->id }}" rows="2" class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:text-white resize-none" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
                                                <button type="submit" class="px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-colors shrink-0 flex items-center justify-center cursor-pointer">
                                                    ارسال پاسخ
                                                </button>
                                            </div>
                                            @error('replyTexts.' . $question->id) <span class="text-xs text-rose-500 dark:text-rose-400 font-bold mt-1 block">{{ $message }}</span> @enderror
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td colspan="7" class="p-10 text-center text-gray-400 dark:text-gray-500 text-sm font-bold bg-gray-50/50 dark:bg-gray-800/30">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                هیچ پرسش و پاسخی یافت نشد.
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        @if($questions->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $questions->links() }}
            </div>
        @endif

        {{-- مدال (Modal) رد پرسش/پاسخ --}}
        @if($rejectingQuestionId)
            <div class="absolute inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-lg overflow-hidden" @click.away="$wire.cancelReject()">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">رد کردن آیتم (پرسش یا پاسخ)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">علت رد شدن این مورد را درج کنید تا در سیستم ثبت شود.</p>
                    </div>
                    <div class="p-5">
                        <textarea wire:model="rejectionReason" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-sm focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:focus:border-rose-500 dark:text-white h-24 resize-none placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="مثال: حاوی توهین یا اطلاعات نامناسب..."></textarea>
                        @error('rejectionReason') <span class="text-xs text-rose-500 dark:text-rose-400 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="p-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button wire:click="cancelReject" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button wire:click="confirmReject" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white shadow-lg shadow-rose-600/30 dark:shadow-none transition-all cursor-pointer">
                            ثبت علت و رد آیتم
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
