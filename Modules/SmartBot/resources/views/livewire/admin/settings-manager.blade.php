<div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white" style="font-family: 'General Sans', sans-serif;">تنظیمات دستیار هوشمند</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویژگی‌های ظاهری، آیکون شناور، میزان دقت پاسخ‌دهی و پیام‌های ربات را پیکربندی کنید.</p>
    </div>

    <!-- Card Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">نام نمایشی دستیار</label>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Primary Color -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">رنگ تم آیکون شناور چت</label>
                    <div class="flex gap-2">
                        <input
                            type="color"
                            wire:model="primary_color"
                            class="w-12 h-9 p-0.5 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 cursor-pointer"
                        />
                        <input
                            type="text"
                            wire:model="primary_color"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="#6366f1"
                        />
                    </div>
                    @error('primary_color') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Welcome Message -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">پیام خوش‌آمدگویی اولیه چت</label>
                <textarea
                    wire:model="welcome_message"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
                @error('welcome_message') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Fallback Response -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">پیام پیش‌فرض (عدم تطابق سوال)</label>
                <textarea
                    wire:model="fallback_response"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
                @error('fallback_response') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                <!-- Match Threshold -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">حداقل میزان تطبیق کلمات (0.0 الی 1.0)</label>
                    <input
                        type="number"
                        step="0.05"
                        wire:model="match_threshold"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p class="text-[10px] text-gray-400 mt-1">هرچه مقدار کمتر باشد، بات آسان‌تر سوالات مشابه را متصل می‌کند.</p>
                    @error('match_threshold') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Max Suggestions -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">حداکثر سوالات پیشنهادی (Quick Replies)</label>
                    <input
                        type="number"
                        wire:model="max_suggestions"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('max_suggestions') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Toggle Widget Enabled -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="is_widget_enabled" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:-translate-x-full rtl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    <span class="ms-3 text-xs font-semibold text-gray-750 dark:text-gray-300">نمایش آیکون شناور در فرانت به عنوان ابزار گفتگو</span>
                </label>
                @error('is_widget_enabled') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Toggle Custom Typing -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="allow_custom_typing" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:-translate-x-full rtl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    <span class="ms-3 text-xs font-semibold text-gray-750 dark:text-gray-300">فعال بودن قابلیت تایپ و ارسال متن دلخواه توسط کاربر (در صورت غیرفعال بودن، کاربر فقط می‌تواند سوالات پیشنهادی را کلیک کند)</span>
                </label>
                @error('allow_custom_typing') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700 pt-4 mt-6">
                <button
                    type="submit"
                    class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm transition-all transform hover:-translate-y-0.5"
                >
                    ذخیره تنظیمات دستیار
                </button>
            </div>

        </form>
    </div>
</div>
