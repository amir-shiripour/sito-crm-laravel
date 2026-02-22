@extends('layouts.user')

@php
    $title = 'تنظیمات املاک';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $checkboxClass = "w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer";
@endphp

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </span>
                    تنظیمات عمومی املاک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">پیکربندی واحد پول، کدینگ و محدودیت‌های آپلود</p>
            </div>
        </div>

        <form action="{{ route('user.settings.properties.update') }}" method="POST" class="space-y-6 pb-20">
            @csrf

            {{-- کارت ۱: واحد پول --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    واحد پول سیستم
                </h2>
                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50">
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="radio" name="currency" value="toman" {{ $currency == 'toman' ? 'checked' : '' }} class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">تومان</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="radio" name="currency" value="rial" {{ $currency == 'rial' ? 'checked' : '' }} class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">ریال</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        این واحد پول در تمام بخش‌های نمایش قیمت (فروش، رهن، اجاره) اعمال خواهد شد.
                    </p>
                </div>
            </div>

            {{-- کارت ۲: تنظیمات کد ملک --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    قالب‌بندی کد ملک
                </h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelClass }}">پیش‌وند حروف (Prefix)</label>
                            <input type="text" name="property_code_prefix" value="{{ $property_code_prefix }}" class="{{ $inputClass }} dir-ltr text-center" placeholder="P">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">جداکننده (Separator)</label>
                            <input type="text" name="property_code_separator" value="{{ $property_code_separator }}" class="{{ $inputClass }} dir-ltr text-center" placeholder="-">
                        </div>
                    </div>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer transition-colors hover:bg-white dark:hover:bg-gray-800">
                        <input type="checkbox" name="property_code_include_year" value="1" {{ $property_code_include_year ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">درج سال شمسی در ابتدای کد</span>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer transition-colors hover:bg-white dark:hover:bg-gray-800">
                        <input type="checkbox" name="property_code_use_category_slug" value="1" {{ $property_code_use_category_slug ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">استفاده از نامک دسته‌بندی به عنوان پیش‌وند</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">در صورت انتخاب دسته‌بندی برای ملک، نامک (Slug) آن جایگزین پیش‌وند پیش‌فرض می‌شود.</span>
                        </div>
                    </label>

                    <div class="text-center py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-mono border border-indigo-100 dark:border-indigo-800 dir-ltr">
                        Sample: 1403{{ $property_code_separator ?: '-' }}{{ $property_code_prefix ?: 'P' }}{{ $property_code_separator ?: '-' }}1001
                    </div>
                </div>
            </div>

            {{-- کارت ۳: تنظیمات نمایش --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    تنظیمات نمایش
                </h2>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer transition-colors hover:bg-white dark:hover:bg-gray-800">
                        <input type="checkbox" name="show_features_in_card" value="1" {{ $show_features_in_card ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">نمایش بخش ویژگی‌ها (متراژ، کد ملک و...) در کارت ملک</span>
                    </label>
                </div>
            </div>

            {{-- کارت جدید: تنظیمات هوش مصنوعی --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                    تنظیمات هوش مصنوعی (AI)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    فعال‌سازی قابلیت‌های هوشمند برای تسهیل کار با سیستم املاک.
                </p>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer transition-colors hover:bg-white dark:hover:bg-gray-800">
                        <input type="checkbox" name="ai_property_completion" value="1" {{ ($ai_property_completion ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال‌سازی تکمیل هوشمند اطلاعات ملک (در فرم ایجاد/ویرایش)</span>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer transition-colors hover:bg-white dark:hover:bg-gray-800">
                        <input type="checkbox" name="ai_property_search" value="1" {{ ($ai_property_search ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال‌سازی جستجوی هوشمند ملک</span>
                    </label>
                </div>
            </div>

            {{-- کارت جدید: تنظیمات دسترسی (Visibility) --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                    تنظیمات دسترسی و نمایش اطلاعات
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                    مشخص کنید کدام نقش‌ها مجاز به مشاهده اطلاعات حساس هستند. اگر هیچ نقشی انتخاب نشود، اطلاعات برای همه (یا طبق پیش‌فرض سیستم) نمایش داده می‌شود.
                </p>

                <div class="space-y-6">
                    {{-- اطلاعات مالک --}}
                    <div>
                        <h3 class="{{ $labelClass }} mb-2">مشاهده اطلاعات مالک</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <input type="checkbox" name="visibility_owner_info[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, $visibility_owner_info) ? 'checked' : '' }}
                                        class="{{ $checkboxClass }}">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- یادداشت‌های محرمانه --}}
                    <div>
                        <h3 class="{{ $labelClass }} mb-2">مشاهده یادداشت‌های محرمانه</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <input type="checkbox" name="visibility_confidential_notes[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, $visibility_confidential_notes) ? 'checked' : '' }}
                                        class="{{ $checkboxClass }}">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- اطلاعات قیمت --}}
                    <div>
                        <h3 class="{{ $labelClass }} mb-2">مشاهده اطلاعات قیمت</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <input type="checkbox" name="visibility_price_info[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, $visibility_price_info) ? 'checked' : '' }}
                                        class="{{ $checkboxClass }}">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- اطلاعات نقشه و آدرس --}}
                    <div>
                        <h3 class="{{ $labelClass }} mb-2">مشاهده نقشه و آدرس دقیق</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <input type="checkbox" name="visibility_map_info[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, $visibility_map_info) ? 'checked' : '' }}
                                        class="{{ $checkboxClass }}">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- نقش‌های مجاز برای مشاور --}}
                    <div>
                        <h3 class="{{ $labelClass }} mb-2">نقش‌های مجاز برای انتخاب به عنوان مشاور</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <input type="checkbox" name="agent_roles[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, $agent_roles) ? 'checked' : '' }}
                                        class="{{ $checkboxClass }}">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت جدید: موقعیت دفتر مرکزی --}}
            <div class="{{ $cardClass }} p-6" x-data="officeMap()">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    موقعیت دفتر مرکزی
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    این موقعیت در نقشه کلی املاک به عنوان دفتر مرکزی نمایش داده می‌شود.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="{{ $labelClass }}">عنوان دفتر</label>
                        <input type="text" name="office_location_title" value="{{ $office_location_title }}" class="{{ $inputClass }}" placeholder="مثلا: دفتر مرکزی">
                    </div>

                    <div class="h-64 w-full rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0 relative" id="office-map"></div>

                    <input type="hidden" name="office_location_lat" x-model="lat">
                    <input type="hidden" name="office_location_lng" x-model="lng">

                    <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400 font-mono dir-ltr">
                        <span>Lat: <span x-text="lat"></span></span>
                        <span>Lng: <span x-text="lng"></span></span>
                    </div>
                </div>
            </div>

            {{-- کارت ۴: تنظیمات آپلود --}}
            <div class="{{ $cardClass }} p-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    محدودیت‌های آپلود
                </h2>

                <div class="space-y-6">
                    {{-- تصاویر --}}
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 pb-2">تصاویر گالری</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">حداکثر حجم هر تصویر (KB)</label>
                                <input type="number" name="max_file_size" value="{{ $max_file_size }}" class="{{ $inputClass }} dir-ltr text-center">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">حداکثر تعداد تصاویر</label>
                                <input type="number" name="max_gallery_images" value="{{ $max_gallery_images }}" class="{{ $inputClass }} dir-ltr text-center">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">فرمت‌های مجاز تصویر</label>
                            <input type="text" name="allowed_file_types" value="{{ $allowed_file_types }}" class="{{ $inputClass }} dir-ltr text-left font-mono text-xs" placeholder="jpg,jpeg,png,webp">
                        </div>
                    </div>

                    {{-- ویدیو --}}
                    <div class="space-y-3 pt-2">
                        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 pb-2">ویدیو</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">حداکثر حجم ویدیو (KB)</label>
                                <input type="number" name="max_video_size" value="{{ $max_video_size }}" class="{{ $inputClass }} dir-ltr text-center">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">فرمت‌های مجاز ویدیو</label>
                                <input type="text" name="allowed_video_types" value="{{ $allowed_video_types }}" class="{{ $inputClass }} dir-ltr text-left font-mono text-xs" placeholder="mp4,mkv">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- کارت ۵: وضعیت حافظه --}}
            <div class="{{ $cardClass }} p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    وضعیت فضای ذخیره‌سازی
                </h2>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-white dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                        <span class="block text-2xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $fileCount }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">فایل ذخیره شده</span>
                    </div>
                    <div class="bg-white dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                        <span class="block text-2xl font-bold text-indigo-600 dark:text-indigo-400 dir-ltr font-mono">{{ $formattedSize }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">فضای اشغال شده</span>
                    </div>
                </div>

                <div class="bg-gray-100 dark:bg-gray-900/50 rounded-lg p-2 text-center">
                    <code class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr font-mono">storage/app/public/properties</code>
                </div>
            </div>

            {{-- دکمه ذخیره شناور --}}
            <div class="fixed bottom-6 left-0 right-0 z-40 flex justify-center pointer-events-none px-4">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl pointer-events-auto max-w-sm w-full flex justify-between items-center gap-4 animate-in slide-in-from-bottom-6">
                    <span class="text-xs text-gray-500 dark:text-gray-400 mr-2 hidden sm:inline">تنظیمات را ذخیره کنید</span>
                    <button type="submit"
                            class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function officeMap() {
            return {
                lat: {{ $office_location_lat ?? 35.6892 }},
                lng: {{ $office_location_lng ?? 51.3890 }},
                map: null,
                marker: null,
                init() {
                    if (typeof L === 'undefined') return;

                    this.map = L.map('office-map').setView([this.lat, this.lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.marker = L.marker([this.lat, this.lng], {draggable: true}).addTo(this.map);

                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.lat = pos.lat;
                        this.lng = pos.lng;
                    });

                    this.map.on('click', (e) => {
                        this.lat = e.latlng.lat;
                        this.lng = e.latlng.lng;
                        this.marker.setLatLng([this.lat, this.lng]);
                    });
                }
            }
        }
    </script>
@endsection
