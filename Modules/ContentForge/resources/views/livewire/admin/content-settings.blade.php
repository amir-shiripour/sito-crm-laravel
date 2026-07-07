<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">تنظیمات ماژول محتوا</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت رفتار عمومی، سئو، ادیتور و لینک‌های کوتاه ماژول</p>
        </div>
        <div>
            <button wire:click="save" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all">
                ذخیره تنظیمات
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('warning'))
        <div class="p-4 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50 rounded-xl text-sm font-semibold">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Form sections --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- General Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                تنظیمات عمومی وبلاگ
            </h3>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">تعداد مقالات در هر صفحه آرشیو</label>
                <input type="number" wire:model.live="settings.general.posts_per_page" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نام کلید قالب پیش‌فرض فرانت</label>
                <input type="text" wire:model.live="settings.general.default_theme_key" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سرعت تخمینی مطالعه (کلمه در دقیقه)</label>
                    <input type="number" wire:model.live="settings.general.reading_time_wpm" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سیستم نظرات وبلاگ</label>
                    <select wire:model.live="settings.general.enable_comments" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">فعال</option>
                        <option value="false">غیرفعال</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEO Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                تنظیمات سئو (SEO)
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">تولید خودکار دسکریپشن</label>
                    <select wire:model.live="settings.seo.auto_generate_description" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">بله</option>
                        <option value="false">خیر</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">تعداد کاراکتر دسکریپشن</label>
                    <input type="number" wire:model.live="settings.seo.description_length" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">تولید خودکار نشانه‌گذاری ساختاریافته (Schema.org)</label>
                <select wire:model.live="settings.seo.auto_schema_markup" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                    <option value="true">فعال</option>
                    <option value="false">غیرفعال</option>
                </select>
            </div>
        </div>

        {{-- Short Link Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                تنظیمات لینک‌های کوتاه
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سیستم لینک کوتاه</label>
                    <select wire:model.live="settings.short_link.enabled" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">فعال</option>
                        <option value="false">غیرفعال</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">طول کد لینک کوتاه (کاراکتر)</label>
                    <input type="number" wire:model.live="settings.short_link.code_length" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">پیشوند آدرس لینک کوتاه (URL Prefix)</label>
                <input type="text" wire:model.live="settings.short_link.prefix" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none">
                <span class="text-[10px] text-gray-400 block mt-1">مثال: انتخاب s آدرس لینک کوتاه را به شکل domain.com/s/code درمی‌آورد.</span>
            </div>
        </div>
    </div>
</div>
