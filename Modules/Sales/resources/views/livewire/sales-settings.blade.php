<div class="space-y-6 pb-10" dir="rtl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">تنظیمات فروش</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تنظیمات پایه‌ای و رفتاری ماژول مدیریت فروش و سرنخ‌ها</p>
            </div>
        </div>
        <button wire:click="saveSettings" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            ذخیره تنظیمات
        </button>
    </div>

    {{-- Settings List --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8 space-y-6">
        <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-base font-extrabold text-gray-900 dark:text-white">اتوماسیون سرنخ‌ها و پرونده‌ها</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت رفتار خودکار سیستم هنگام تبدیل سرنخ‌های ورودی</p>
        </div>

        <div class="flex items-start justify-between p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-800">
            <div class="space-y-1.5 max-w-[80%]">
                <label for="autoCreateDeal" class="block text-sm font-bold text-gray-900 dark:text-white cursor-pointer select-none">
                    ایجاد خودکار پرونده فروش پس از ساخت کلاینت
                </label>
                <span class="block text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    در صورتی که این گزینه فعال باشد، به محض تبدیل سرنخ به کلاینت (چه به صورت تخصیص مستقیم به کارشناس و چه در زمان پذیرش سرنخ در میزکار)، یک پرونده فروش (معامله) در اولین مرحله خط لوله به طور خودکار ساخته می‌شود. در غیر این صورت، نیاز به زدن دستی دکمه «تبدیل به پرونده» در میزکار خواهد بود.
                </span>
            </div>
            
            <!-- Toggle Switch -->
            <div class="flex items-center h-6">
                <button type="button" 
                        wire:click="$toggle('autoCreateDeal')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $autoCreateDeal ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}" 
                        role="switch" 
                        aria-checked="{{ $autoCreateDeal ? 'true' : 'false' }}">
                    <span aria-hidden="true" 
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $autoCreateDeal ? '-translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </div>
</div>
