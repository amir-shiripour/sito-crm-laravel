@php
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
    focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
    dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="mx-auto" x-data="{ tab: 'basic' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none overflow-hidden">

        {{-- هدر فرم --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $vendor?->exists ? 'بررسی و ویرایش فروشگاه: ' . $vendor->store_name : 'ثبت فروشگاه جدید' }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">بررسی اطلاعات هویتی و مدیریت سطح دسترسی فروشنده</p>
            </div>

            <button type="button" wire:click="cancelReview"
                    class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all">
                <span>بازگشت و لغو بررسی</span>
            </button>
        </div>

        {{-- تب‌های ناوبری --}}
        <div class="flex items-center gap-6 px-6 border-b border-gray-100 dark:border-gray-700 overflow-x-auto bg-white dark:bg-gray-800">
            <button @click="tab = 'basic'" :class="tab === 'basic' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                اطلاعات پایه و هویتی
            </button>
            <button @click="tab = 'financial'" :class="tab === 'financial' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                اطلاعات مالی
            </button>
            @if($vendor?->exists)
                <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    بررسی مدارک <span class="mr-1 bg-amber-100 text-amber-700 py-0.5 px-2 rounded-full text-xs">{{ $documents->where('status', 'pending')->count() }}</span>
                </button>
                <button @click="tab = 'addresses'" :class="tab === 'addresses' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    آدرس‌ها و انبارها
                </button>
            @endif
        </div>

        <div class="p-6 sm:p-8">

            {{-- 💡 هشدار هوشمند برای ادمین: تاریخچه رد شدن قبلی --}}
            @if($kyc_status === 'pending' && !empty($kyc_rejection_reason))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-800/50 flex gap-4 animate-in fade-in duration-500">
                    <div class="text-amber-500 mt-0.5">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400">یادآوری برای مدیریت:</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                            این فروشگاه قبلاً توسط شما رد شده بود. دلیل رد ثبت شده: <br>
                            <span class="font-mono bg-white/60 dark:bg-black/20 px-2 py-1 rounded mt-1 inline-block text-amber-900 dark:text-amber-200 shadow-sm">«{{ $kyc_rejection_reason }}»</span>
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-500 mt-2 font-medium">لطفاً بررسی کنید که آیا فروشنده موارد فوق را در این درخواست اصلاح کرده است یا خیر.</p>
                    </div>
                </div>
            @endif

            {{-- تب اطلاعات پایه --}}
            <div x-show="tab === 'basic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">مالک فروشگاه (کاربر) <span class="text-red-500">*</span></label>
                        <select wire:model.defer="user_id" class="{{ $baseInputClass }}" @if($vendor?->exists) disabled @endif>
                            <option value="">انتخاب کاربر...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->mobile }})</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نام فروشگاه <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="store_name" class="{{ $baseInputClass }}" placeholder="مثال: فروشگاه امید">
                        @error('store_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">شناسه یکتا (Slug)</label>
                        <input type="text" wire:model.defer="slug" class="{{ $baseInputClass }} dir-ltr text-right" placeholder="omid-store">
                        @error('slug') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نوع شخص <span class="text-red-500">*</span></label>
                        <select wire:model.defer="legal_type" class="{{ $baseInputClass }}">
                            <option value="real">شخص حقیقی (عادی)</option>
                            <option value="legal">شخص حقوقی (شرکت)</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">کد ملی / شناسه ملی</label>
                        <input type="text" wire:model.defer="national_code" class="{{ $baseInputClass }} dir-ltr text-right">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">کد اقتصادی (برای حقوقی)</label>
                        <input type="text" wire:model.defer="economic_code" class="{{ $baseInputClass }} dir-ltr text-right">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">وضعیت فروشگاه (فعالیت)</label>
                        <select wire:model.defer="status" class="{{ $baseInputClass }}">
                            <option value="pending">در انتظار تایید</option>
                            <option value="active">فعال</option>
                            <option value="suspended">مسدود شده</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">وضعیت احراز هویت (KYC)</label>
                        <select wire:model.live="kyc_status" class="{{ $baseInputClass }}">
                            <option value="pending">در حال بررسی مدارک</option>
                            <option value="approved">تایید شده کامل</option>
                            <option value="rejected">رد شده / ناقص</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">پورسانت اختصاصی (%)</label>
                        <input type="number" wire:model.defer="commission_rate" class="{{ $baseInputClass }}" placeholder="پیش‌فرض سیستم">
                    </div>

                    @if($kyc_status === 'rejected')
                        <div class="md:col-span-3 animate-in fade-in duration-300">
                            <label class="{{ $labelClass }}">دلیل رد کلی احراز هویت (نمایش به فروشنده) <span class="text-red-500">*</span></label>
                            <textarea wire:model.defer="kyc_rejection_reason" rows="2" class="{{ $baseInputClass }} border-red-300 focus:border-red-500 dark:border-red-900/50" placeholder="مثال: اطلاعات بانکی با تصویر کارت ملی مطابقت ندارد."></textarea>
                        </div>
                    @endif
                </div>
            </div>

            {{-- تب اطلاعات مالی --}}
            <div x-show="tab === 'financial'" x-cloak class="space-y-6">
                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-sm mb-6">
                    این اطلاعات برای تسویه حساب مالی با فروشنده استفاده خواهد شد. لطفا از تطابق نام صاحب حساب با کارت ملی اطمینان حاصل کنید.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">شماره شبا بانکی (بدون IR)</label>
                        <div class="relative">
                            <input type="text" wire:model.defer="shaba_number" class="{{ $baseInputClass }} dir-ltr pl-10" placeholder="000000000000000000000000" maxlength="24">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-mono font-bold">IR</div>
                        </div>
                        @error('shaba_number') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نام صاحب حساب</label>
                        <input type="text" wire:model.defer="account_owner_name" class="{{ $baseInputClass }}">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نام بانک</label>
                        <input type="text" wire:model.defer="bank_name" class="{{ $baseInputClass }}" placeholder="مثال: بانک ملت">
                    </div>
                </div>
            </div>

            {{-- تب مدارک --}}
            @if($vendor?->exists)
                <div x-show="tab === 'documents'" x-cloak class="space-y-6">
                    @if($documents->isEmpty())
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl">
                            هیچ مدرکی توسط فروشنده آپلود نشده است.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($documents as $doc)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex flex-col gap-4">
                                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <span class="font-bold text-gray-800 dark:text-gray-200">
                                            {{ match($doc->type) { 'national_card' => 'کارت ملی', 'business_license' => 'جواز کسب', 'vat_certificate' => 'ارزش افزوده', default => 'مدرک متفرقه' } }}
                                        </span>
                                        @if($doc->status === 'approved')
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold">تایید شده</span>
                                        @elseif($doc->status === 'rejected')
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">رد شده</span>
                                        @else
                                            <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-bold">در انتظار بررسی</span>
                                        @endif
                                    </div>

                                    <div class="h-48 bg-gray-100 dark:bg-gray-900 rounded-xl overflow-hidden flex items-center justify-center border border-gray-200 dark:border-gray-700 relative group">
                                        <img src="{{ Storage::url($doc->file_path) }}" alt="مدرک" class="w-full h-full object-cover cursor-pointer group-hover:scale-105 transition-transform duration-300" onclick="window.open(this.src, '_blank')">
                                        <div class="absolute bottom-2 left-2 bg-black/50 text-white text-[10px] px-2 py-1 rounded backdrop-blur-sm pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">کلیک برای بزرگنمایی</div>
                                    </div>

                                    @if($doc->status === 'rejected')
                                        <p class="text-xs text-red-500 bg-red-50 dark:bg-red-900/20 p-2 rounded">دلیل رد: {{ $doc->rejection_reason }}</p>
                                    @endif

                                    <div class="flex items-center gap-2 mt-2">
                                        <button wire:click="approveDocument({{ $doc->id }})" class="flex-1 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-lg transition-colors text-sm font-medium dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white">تایید مدرک</button>
                                        <button wire:click="promptRejectDocument({{ $doc->id }})" class="flex-1 py-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-lg transition-colors text-sm font-medium dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">رد مدرک</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- تب آدرس‌ها --}}
                <div x-show="tab === 'addresses'" x-cloak class="space-y-6">
                    @if($addresses->isEmpty())
                        <div class="py-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-xl dark:border-gray-700">هیچ آدرسی ثبت نشده است.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($addresses as $address)
                                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800/50 flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs font-bold dark:bg-indigo-900/40 dark:text-indigo-300">{{ $address->type === 'warehouse' ? 'انبار' : 'دفتر' }}</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $address->province }} - {{ $address->city }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $address->address }} (کد پستی: {{ $address->postal_code ?? 'ندارد' }})</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- دکمه ذخیره کلی فرم --}}
            <div x-show="tab === 'basic' || tab === 'financial'" class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-100 dark:border-gray-700">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all text-sm font-medium">
                    <span wire:loading.remove>ذخیره اطلاعات فروشگاه</span>
                    <span wire:loading.flex class="flex items-center gap-2">... در حال پردازش</span>
                </button>
            </div>
        </div>
    </div>

    {{-- مودال رد مدرک --}}
    @if($rejectingDocId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-2xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">دلیل رد مدرک</h3>
                <textarea wire:model.defer="rejectionReason" class="{{ $baseInputClass }} resize-none mb-4" rows="3" placeholder="مثال: عکس کارت ملی ناخوانا است..."></textarea>
                @error('rejectionReason') <span class="text-xs text-red-500 block mb-4">{{ $message }}</span> @enderror

                <div class="flex justify-end gap-2">
                    <button wire:click="cancelReject" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm transition-colors">انصراف</button>
                    <button wire:click="confirmRejectDocument" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg text-sm shadow-lg shadow-red-500/30 transition-colors">ثبت و رد مدرک</button>
                </div>
            </div>
        </div>
    @endif
</div>
