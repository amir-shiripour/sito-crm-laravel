@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 overflow-hidden";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center gap-3";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800 outline-none";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer transition-colors";
@endphp

<div class="space-y-6 pb-24 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">تنظیمات دستیار هوشمند</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویژگی‌های ظاهری، آیکون شناور، میزان دقت پاسخ‌دهی و پیام‌های ربات را پیکربندی کنید.</p>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        
        <!-- CARD 1: Identity & Design -->
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.912-5.813a2 2 0 011.275-1.275L21 12l-5.813-1.912a2 2 0 01-1.275-1.275L12 3v0a2 2 0 00-2 2v10.904z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">هویت و ظاهر دستیار هوشمند</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">نام، رنگ تم و پیام خوش‌آمدگویی ابتدایی چت را مشخص کنید.</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Display Name -->
                    <div>
                        <label class="{{ $labelClass }}">نام نمایشی دستیار</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="{{ $inputClass }}"
                            placeholder="SmartBot"
                        />
                        @error('name') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Primary Color -->
                    <div>
                        <label class="{{ $labelClass }}">رنگ تم آیکون شناور چت</label>
                        <div class="flex gap-2">
                            <input
                                type="color"
                                wire:model="primary_color"
                                class="w-12 h-10 p-1 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 cursor-pointer"
                            />
                            <input
                                type="text"
                                wire:model="primary_color"
                                class="{{ $inputClass }}"
                                placeholder="#6366f1"
                            />
                        </div>
                        @error('primary_color') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Welcome Message -->
                <div>
                    <label class="{{ $labelClass }}">
                        پیام خوش‌آمدگویی اولیه چت
                        <span class="text-[11px] font-normal text-gray-400 dark:text-gray-500 mr-1.5">(اختیاری - در صورت خالی بودن، پیامی به عنوان حباب در ابتدای چت درج نمی‌شود)</span>
                    </label>
                    <textarea
                        wire:model="welcome_message"
                        rows="3"
                        class="{{ $inputClass }} resize-y"
                        placeholder="اختیاری: مثلاً «سلام! چطور می‌توانم کمکتان کنم؟»..."
                    ></textarea>
                    @error('welcome_message') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                </div>

                <!-- Assistant Icon Upload -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100 dark:border-gray-700/60">
                    <div>
                        <label class="{{ $labelClass }}">آیکون اختصاصی دستیار</label>
                        <div class="flex items-center gap-4">
                            @if($existing_bot_icon)
                                <div class="relative w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-55/50 dark:bg-gray-900/50 flex-shrink-0 flex items-center justify-center p-1">
                                    <img src="{{ asset('storage/' . $existing_bot_icon) }}" class="w-full h-full object-contain rounded-lg" />
                                    <button type="button" wire:click="deleteIcon" class="absolute -top-1 -right-1 p-1 bg-red-500 hover:bg-red-600 text-white rounded-full transition-colors shadow-sm" title="حذف آیکون">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @elseif($bot_icon)
                                <div class="w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-55/50 dark:bg-gray-900/50 flex-shrink-0 flex items-center justify-center p-1">
                                    <img src="{{ $bot_icon->temporaryUrl() }}" class="w-full h-full object-contain rounded-lg" />
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 flex-shrink-0 bg-gray-50/50 dark:bg-gray-900/30">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="flex-grow">
                                <div class="relative">
                                    <input type="file" wire:model="bot_icon" id="bot_icon_input" class="hidden" accept="image/*" />
                                    <label for="bot_icon_input" class="inline-flex items-center justify-center px-4 py-2 border border-dashed border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 rounded-xl cursor-pointer transition-colors bg-gray-50 dark:bg-gray-900/50 text-xs font-bold text-gray-500 dark:text-gray-400">
                                        انتخاب آیکون جدید
                                    </label>
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5 leading-relaxed">فرمت‌های مجاز: JPG, PNG, WebP (حداکثر ۱ مگابایت)</p>
                                @error('bot_icon') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Match rules & Processing -->
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">موتور پردازش و قوانین تطبیق کلمات</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">میزان دقت و هوش دستیار را در تحلیل عبارات تعیین کنید.</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Match Threshold -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">حداقل میزان تطبیق کلمات (دقت پاسخ‌دهی)</label>
                            <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/55 px-2 py-0.5 rounded-lg">{{ $match_threshold }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input
                                type="range"
                                min="0.05"
                                max="1.0"
                                step="0.05"
                                wire:model.live="match_threshold"
                                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-650"
                            />
                        </div>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">هرچه مقدار کمتر باشد، پاسخ‌دهی آسان‌تر و منعطف‌تر است. مقادیر بالا نیاز به تطبیق دقیق عبارات دارند.</p>
                        @error('match_threshold') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Max Suggestions -->
                    <div>
                        <label class="{{ $labelClass }}">حداکثر سوالات پیشنهادی (Quick Replies)</label>
                        <input
                            type="number"
                            min="1"
                            max="10"
                            wire:model="max_suggestions"
                            class="{{ $inputClass }}"
                            placeholder="5"
                        />
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">حداکثر تعداد دکمه‌های پاسخ فوری که به صورت هوشمند زیر پیام نمایش داده می‌شوند.</p>
                        @error('max_suggestions') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Fallback Response -->
                <div>
                    <label class="{{ $labelClass }}">پیام پیش‌فرض (در صورت متوجه نشدن سوال)</label>
                    <textarea
                        wire:model="fallback_response"
                        rows="3"
                        class="{{ $inputClass }} resize-y"
                        placeholder="متأسفانه پاسخ مناسبی برای این سوال پیدا نکردم..."
                    ></textarea>
                    @error('fallback_response') <span class="text-xs text-red-500 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- CARD 3: Access & Feature Toggles -->
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات دسترسی و قابلیت‌ها</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">امکانات فعال در بخش گفتگو را خاموش یا روشن کنید.</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Toggle Widget Enabled -->
                <div class="flex items-start gap-4">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            id="is_widget_enabled"
                            wire:model="is_widget_enabled"
                            class="{{ $checkboxClass }}"
                        />
                    </div>
                    <div class="text-sm">
                        <label for="is_widget_enabled" class="font-bold text-gray-800 dark:text-gray-250 cursor-pointer select-none">نمایش آیکون شناور در فرانت‌فروشگاه</label>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">با فعال کردن این گزینه، ویجت دایره‌ای چت شناور در پایین صفحات فروشگاه برای همه بازدیدکنندگان نمایش داده می‌شود.</p>
                    </div>
                </div>

                <!-- Toggle Custom Typing -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-6 flex items-start gap-4">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            id="allow_custom_typing"
                            wire:model="allow_custom_typing"
                            class="{{ $checkboxClass }}"
                        />
                    </div>
                    <div class="text-sm">
                        <label for="allow_custom_typing" class="font-bold text-gray-800 dark:text-gray-250 cursor-pointer select-none">قابلیت تایپ متن دلخواه توسط کاربر</label>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">در صورت غیرفعال بودن، کادر ورودی متن قفل شده و کاربر صرفاً می‌تواند از سوالات پیشنهادی آماده جهت پیشبرد گفتگو استفاده کند.</p>
                    </div>
                </div>

                <!-- Toggle Require Client Auth -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-6 flex items-start gap-4">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            id="require_client_auth"
                            wire:model="require_client_auth"
                            class="{{ $checkboxClass }}"
                        />
                    </div>
                    <div class="text-sm">
                        <label for="require_client_auth" class="font-bold text-gray-800 dark:text-gray-250 cursor-pointer select-none">محدودیت استفاده فقط برای کلاینت‌های لاگین شده</label>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">در صورت فعال بودن، کاربران مهمان (مهمانانی که لاگین نکرده‌اند) امکان گفتگو با دستیار را نخواهند داشت و ابتدا فرم ورود/ثبت‌نام درون ویجت برای آن‌ها نمایش داده می‌شود.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Assistant Level -->
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">سطح عملکرد دستیار هوشمند (Assistant Level)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">سطح تعامل دستیار در نمایش محصولات متغیر و فرآیند خرید را تنظیم کنید.</p>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Level 1 Option -->
                    <label class="relative flex p-4 rounded-xl border {{ $assistant_level == 1 ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 dark:border-indigo-500' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50' }} cursor-pointer transition-all hover:border-indigo-400">
                        <div class="flex items-start gap-3 w-full">
                            <input type="radio" name="assistant_level" value="1" wire:model="assistant_level" class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">سطح ۱: حالت استاندارد</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">پیش‌فرض</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                                    در محصولات متغیر، دکمه «مشاهده و خرید» کاربر را به صفحه اختصاصی محصول هدایت می‌کند تا تنوع‌ها را انتخاب کند.
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Level 2 Option -->
                    <label class="relative flex p-4 rounded-xl border {{ $assistant_level == 2 ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 dark:border-indigo-500' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50' }} cursor-pointer transition-all hover:border-indigo-400">
                        <div class="flex items-start gap-3 w-full">
                            <input type="radio" name="assistant_level" value="2" wire:model="assistant_level" class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">سطح ۲: تعاملی پیوسته (خرید درون چت)</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">پیشرفته</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                                    در محصولات متغیر، دکمه «انتخاب تنوع» لیست تنوع‌ها را به‌صورت کارتی و بازشونده مستقیماً درون همان چت باز می‌کند تا کاربر بدون خروج روند خرید را ادامه دهد.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <button
                type="submit"
                class="px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-850 rounded-xl shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 duration-150 cursor-pointer"
            >
                ذخیره تغییرات و بروزرسانی دستیار
            </button>
        </div>

    </form>
</div>
