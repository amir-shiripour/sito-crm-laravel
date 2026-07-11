@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2";
@endphp
<div class="space-y-4">
    <!-- Top Filter Bar -->
    <div class="flex flex-col gap-4 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm" dir="rtl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="w-full sm:w-80 relative">
                <input type="text" wire:model.live="search" class="{{ $inputClass }} pr-10" placeholder="جستجوی مشتری (نام، شماره، پرونده)...">
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <div>
                <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    مشتری جدید
                </button>
            </div>
        </div>
        
        <!-- Status & Sorting Filters -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">فیلتر وضعیت</label>
                <select wire:model.live="filterStatus" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="all">همه وضعیت‌ها</option>
                    @if(isset($statuses))
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->label }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 mb-1">مرتب‌سازی</label>
                <select wire:model.live="sortBy" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition-colors">
                    <option value="latest">جدیدترین</option>
                    <option value="name">بر اساس نام الفبا</option>
                    <option value="calls_count">تعداد تماس‌های اخیر</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Compact List -->
    <div class="flex flex-col gap-2" dir="rtl">
        @forelse($customers as $customer)
            <div wire:click="selectCustomer({{ $customer->id }})" class="group cursor-pointer p-3 rounded-xl border transition-colors flex items-center justify-between {{ $selectedClientId == $customer->id ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/30 dark:border-indigo-500 shadow-sm' : 'border-gray-100 dark:border-gray-800 hover:border-indigo-300 dark:hover:border-indigo-600 bg-white dark:bg-gray-800 hover:shadow-sm' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-50 group-hover:bg-indigo-50 dark:bg-gray-900 dark:group-hover:bg-indigo-900/30 flex items-center justify-center text-gray-500 group-hover:text-indigo-600 dark:text-gray-400 dark:group-hover:text-indigo-400 font-extrabold text-sm transition-colors">
                        {{ mb_substr($customer->full_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ $customer->full_name }}</h4>
                            @if($customer->status)
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-semibold" style="background-color: {{ $customer->status->color }}15; color: {{ $customer->status->color }}">
                                    {{ $customer->status->label }}
                                </span>
                            @endif
                            @if($selectedClientId == $customer->id)
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-gray-400 block mt-0.5" dir="ltr">{{ $customer->phone }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if(isset($customer->calls_count) && $customer->calls_count > 0)
                        <span class="text-[9px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-lg" title="تعداد تماس‌های ثبت‌شده">
                            📞 {{ $customer->calls_count }}
                        </span>
                    @endif
                    <div class="text-left hidden sm:block">
                        <span class="text-[10px] text-gray-400 block">پرونده: {{ $customer->case_number ?: '-' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-10 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="text-xs font-semibold">مشتری مورد نظر یافت نشد.</span>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($customers->count())
        <div class="pt-4" dir="rtl">
            {{ $customers->links() }}
        </div>
    @endif

    <!-- Create Customer Modal -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">👥 ثبت مشتری جدید</h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveCustomer" class="space-y-5">
                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model="full_name" class="{{ $inputClass }}" placeholder="نام کامل">
                                        @error('full_name') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">نام کاربری (username) <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model="username" class="{{ $inputClass }}" dir="ltr" placeholder="john_doe">
                                        @error('username') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="{{ $labelClass }}">شماره تماس (تلفن همراه) <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model="phone" class="{{ $inputClass }}" dir="ltr" placeholder="09123456789">
                                        @error('phone') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">ایمیل</label>
                                        <input type="email" wire:model="email" class="{{ $inputClass }}" dir="ltr" placeholder="example@email.com">
                                        @error('email') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5 bg-gray-50/50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-200 dark:border-gray-700/50">
                                    <div>
                                        <label class="{{ $labelClass }}">کد ملی</label>
                                        <input type="text" wire:model="national_code" class="{{ $inputClass }}" dir="ltr">
                                        @error('national_code') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">شماره پرونده اختصاصی</label>
                                        <input type="text" wire:model="case_number" class="{{ $inputClass }}" dir="ltr" placeholder="CRM-1234">
                                        @error('case_number') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">یادداشت اولیه پرونده</label>
                                    <textarea wire:model="notes" rows="3" class="{{ $inputClass }} resize-none" placeholder="نکاتی درباره این مشتری..."></textarea>
                                    @error('notes') <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700 mt-5">
                                    <button type="button" x-on:click="show = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-bold transition-colors">انصراف</button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors active:scale-95">ذخیره مشتری</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
