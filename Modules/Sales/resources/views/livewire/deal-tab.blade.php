@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-4">
    <!-- Top Filter Bar -->
    <div class="flex flex-col gap-4 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm" dir="rtl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="w-full sm:w-80 relative">
                <input type="text" wire:model.live="search" class="{{ $inputClass }} pr-10" placeholder="جستجوی پرونده (عنوان، نام مشتری، تلفن)...">
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <div>
                <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    پرونده جدید
                </button>
            </div>
        </div>
        
        <!-- Status & Sorting Filters -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">مرحله خط لوله</label>
                <select wire:model.live="filterStage" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="all">همه مراحل</option>
                    @foreach($pipelines as $pipe)
                        <option value="{{ $pipe->id }}">{{ $pipe->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">وضعیت پرونده</label>
                <select wire:model.live="filterStatus" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="open">باز (Open)</option>
                    <option value="won">موفق (Won)</option>
                    <option value="lost">ناموفق (Lost)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">مرتب‌سازی</label>
                <select wire:model.live="sortBy" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="latest">جدیدترین</option>
                    <option value="name">الفبایی (عنوان پرونده)</option>
                    <option value="revenue">مبلغ پیش‌بینی‌شده</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Compact List -->
    <div class="flex flex-col gap-2" dir="rtl">
        @forelse($deals as $deal)
            <div wire:click="selectDeal({{ $deal->id }})" class="group cursor-pointer p-3 rounded-xl border transition-colors flex items-center justify-between {{ $selectedDealId == $deal->id ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/30 dark:border-indigo-500 shadow-sm' : 'border-gray-100 dark:border-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600 bg-white dark:bg-gray-800 hover:shadow-sm' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-50 group-hover:bg-indigo-50 dark:bg-gray-900 dark:group-hover:bg-indigo-900/30 flex items-center justify-center text-gray-500 group-hover:text-indigo-600 dark:text-gray-400 dark:group-hover:text-indigo-400 font-extrabold text-sm transition-colors">
                        💼
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ $deal->title }}</h4>
                            
                            <!-- Pipeline Stage Badge -->
                            @if($deal->stage)
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-semibold" style="background-color: {{ $deal->stage->color }}15; color: {{ $deal->stage->color }}">
                                    {{ $deal->stage->name }}
                                </span>
                            @endif

                            @if($deal->status === 'won')
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400">
                                    برنده
                                </span>
                            @elseif($deal->status === 'lost')
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400">
                                    باخته
                                </span>
                            @endif

                            @if($selectedDealId == $deal->id)
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                مشتری: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $deal->client?->full_name ?? 'ندارد' }}</span>
                            </span>
                            @if($deal->client?->phone)
                                <span class="text-[9px] text-gray-400" dir="ltr">({{ $deal->client->phone }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3" @click.stop>
                    <div class="text-left pl-3 sm:border-l border-gray-100 dark:border-gray-800">
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                            {{ number_format((float)$deal->expected_revenue) }} <span class="text-[9px] font-normal text-gray-500">ریال</span>
                        </span>
                        @if($deal->expected_close_date)
                            <span class="text-[9px] text-gray-400 block mt-0.5">سررسید: {{ $deal->expected_close_date->format('Y/m/d') }}</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <!-- Edit Button -->
                        <button wire:click="editDeal({{ $deal->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 rounded-lg transition-colors" title="ویرایش پرونده">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        
                        <!-- Delete Button -->
                        <button onclick="confirm('آیا از حذف این پرونده مطمئن هستید؟') || event.stopImmediatePropagation()" wire:click="deleteDeal({{ $deal->id }})" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors" title="حذف پرونده">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-10 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-xs font-semibold">پرونده‌ای یافت نشد.</span>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($deals->count())
        <div class="pt-4" dir="rtl">
            {{ $deals->links() }}
        </div>
    @endif

    <!-- Create Deal Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal'), mode: @entangle('clientMode') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                                    {{ $editingDealId ? '📝 ویرایش پرونده فروش' : '💼 ثبت پرونده فروش جدید' }}
                                </h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveDeal" class="space-y-5">
                                <!-- Deal General Fields -->
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">عنوان پرونده <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model="newDealTitle" class="{{ $inputClass }}" placeholder="مثلاً: خرید سرور جدید">
                                        @error('newDealTitle') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">مبلغ پیش‌بینی‌شده (ریال) <span class="text-rose-500">*</span></label>
                                        <input type="number" wire:model="newDealExpectedRevenue" class="{{ $inputClass }}" placeholder="مبلغ به ریال">
                                        @error('newDealExpectedRevenue') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">مرحله خط لوله <span class="text-rose-500">*</span></label>
                                        <select wire:model="newDealStageId" class="{{ $inputClass }}">
                                            <option value="">انتخاب مرحله...</option>
                                            @foreach($pipelines as $pipe)
                                                <option value="{{ $pipe->id }}">{{ $pipe->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newDealStageId') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">احتمال موفقیت (٪)</label>
                                        <input type="number" wire:model="newDealProbability" class="{{ $inputClass }}" placeholder="10-100" min="0" max="100">
                                        @error('newDealProbability') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ بسته‌شدن پیش‌بینی‌شده</label>
                                        <input type="date" wire:model="newDealExpectedCloseDate" class="{{ $inputClass }}">
                                        @error('newDealExpectedCloseDate') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">مسئول پرونده <span class="text-rose-500">*</span></label>
                                        <select wire:model="newDealUserId" class="{{ $inputClass }}">
                                            <option value="">انتخاب کارشناس...</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newDealUserId') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">منبع سرنخ</label>
                                        <input type="text" wire:model="newDealSource" class="{{ $inputClass }}" placeholder="مثلاً: سایت، معرفی">
                                        @error('newDealSource') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- CLIENT CONNECTION AREA -->
                                <div class="bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-200 dark:border-gray-700/80 space-y-4">
                                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700/80 pb-3">
                                        <h4 class="text-xs font-black text-gray-900 dark:text-white">👤 اتصال مشتری به پرونده</h4>
                                        
                                        @if(!$editingDealId)
                                            <!-- Mode Switch Toggle -->
                                            <div class="flex bg-gray-200/60 dark:bg-gray-800 p-0.5 rounded-lg text-2xs">
                                                <button type="button" @click="mode = 'existing'" :class="mode === 'existing' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1 rounded-md transition-all">مشتری موجود</button>
                                                <button type="button" @click="mode = 'new'" :class="mode === 'new' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1 rounded-md transition-all">مشتری جدید</button>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Mode: Existing Client -->
                                    <div x-show="mode === 'existing'" class="space-y-4">
                                        <div>
                                            <label class="{{ $labelClass }}">انتخاب مشتری موجود <span class="text-rose-500">*</span></label>
                                            <select wire:model="newDealClientId" class="{{ $inputClass }}">
                                                <option value="">جستجو و انتخاب مشتری...</option>
                                                @foreach($clients as $c)
                                                    <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                                                @endforeach
                                            </select>
                                            @error('newDealClientId') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Mode: New Client (Create inline) -->
                                    <div x-show="mode === 'new'" class="space-y-4" style="display: none;">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="{{ $labelClass }}">نام و نام خانوادگی مشتری <span class="text-rose-500">*</span></label>
                                                <input type="text" wire:model="client_full_name" class="{{ $inputClass }}" placeholder="نام کامل مشتری">
                                                @error('client_full_name') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">شماره تماس (تلفن همراه) <span class="text-rose-500">*</span></label>
                                                <input type="text" wire:model="client_phone" class="{{ $inputClass }}" dir="ltr" placeholder="09123456789">
                                                @error('client_phone') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-3 gap-4">
                                            <div>
                                                <label class="{{ $labelClass }}">نام کاربری (اختیاری)</label>
                                                <input type="text" wire:model="client_username" class="{{ $inputClass }}" dir="ltr" placeholder="سیستمی تولید می‌شود">
                                                @error('client_username') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">ایمیل</label>
                                                <input type="email" wire:model="client_email" class="{{ $inputClass }}" dir="ltr" placeholder="example@email.com">
                                                @error('client_email') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">کد ملی</label>
                                                <input type="text" wire:model="client_national_code" class="{{ $inputClass }}" dir="ltr" placeholder="کد ده رقمی">
                                                @error('client_national_code') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div>
                                            <label class="{{ $labelClass }}">یادداشت اولیه مشتری</label>
                                            <textarea wire:model="client_notes" rows="2" class="{{ $inputClass }} resize-none" placeholder="نکاتی درباره این مشتری..."></textarea>
                                            @error('client_notes') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Deal Description -->
                                <div>
                                    <label class="{{ $labelClass }}">توضیحات پرونده</label>
                                    <textarea wire:model="newDealDescription" rows="3" class="{{ $inputClass }} resize-none" placeholder="جزئیات و توضیحات بیشتر درباره این معامله..."></textarea>
                                    @error('newDealDescription') <span class="text-rose-500 text-[10px] mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                    <button type="button" x-on:click="show = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">ذخیره پرونده</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
