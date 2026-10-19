<div class="space-y-6 font-sans max-w-4xl">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm">
        <h1 class="text-lg font-black text-gray-900 dark:text-white">پیکربندی و تنظیمات هوشمند نصاب‌باشی</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مدیریت وب‌سرویس نقشه‌های ایرانی و بین‌المللی، تعرفه‌های پایه و رادار اعزام</p>
    </div>

    <form wire:submit="saveSettings" class="space-y-6">
        {{-- Section 1: Map Tile Provider --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="text-xl">🗺️</span>
                <h2 class="text-sm font-black text-gray-900 dark:text-white">سرویس نقشه تعاملی (GIS Map Provider)</h2>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                سرویس نقشه مورد نظر خود را انتخاب کرده و در صورت نیاز کلید دسترسی (API Key) را وارد کنید. سیستم به صورت خودکار لایه تایل‌ها را بر روی رادار و مانیتورینگ استان مازندران اعمال می‌نماید.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $map_provider === 'osm' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-800 dark:text-gray-200">OpenStreetMap</span>
                        <input type="radio" wire:model.live="map_provider" value="osm" class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-2">رایگان و بدون نیاز به کلید</span>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $map_provider === 'neshan' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-800 dark:text-gray-200">نقشه نشان (Neshan)</span>
                        <input type="radio" wire:model.live="map_provider" value="neshan" class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-2">جزئیات کامل خیابان‌های مازندران</span>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $map_provider === 'map_ir' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-800 dark:text-gray-200">نقشه مپ (Map.ir)</span>
                        <input type="radio" wire:model.live="map_provider" value="map_ir" class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-2">تایل‌های ملی و فارسی</span>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $map_provider === 'custom' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-800 dark:text-gray-200">سرور تایل سفارشی</span>
                        <input type="radio" wire:model.live="map_provider" value="custom" class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-2">آدرس XYZ اختصاصی</span>
                </label>
            </div>

            {{-- Conditional fields based on provider --}}
            @if($map_provider === 'neshan')
            <div class="pt-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کلید وب‌سرویس نشان (Neshan API Key)</label>
                <input type="text" wire:model="map_neshan_api_key" placeholder="service.xxxxxxxxxxxxxxxxxxxx" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
            </div>
            @elseif($map_provider === 'map_ir')
            <div class="pt-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کلید وب‌سرویس مپ (Map.ir API Key)</label>
                <input type="text" wire:model="map_map_ir_api_key" placeholder="map_key_xxxxxxxx" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
            </div>
            @elseif($map_provider === 'custom')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">آدرس قالب تایل (Tile URL Template)</label>
                    <input type="text" wire:model="map_custom_tile_url" placeholder="https://tiles.example.com/{z}/{x}/{y}.png" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">کلید دسترسی (در صورت نیاز)</label>
                    <input type="text" wire:model="map_custom_api_key" placeholder="API Key" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
                </div>
            </div>
            @endif

            {{-- Coordinates --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">عرض جغرافیایی مرکز (Latitude)</label>
                    <input type="number" step="0.0001" wire:model="map_default_lat" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">طول جغرافیایی مرکز (Longitude)</label>
                    <input type="number" step="0.0001" wire:model="map_default_lng" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">بزرگ‌نمایی اولیه (Zoom)</label>
                    <input type="number" wire:model="map_default_zoom" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                </div>
            </div>
        </div>

        {{-- Section 2: Tariffs, Financial & Commission --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="text-xl">💰</span>
                <h2 class="text-sm font-black text-gray-900 dark:text-white">تعرفه‌ها و قوانین مالی پلتفرم</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">درصد کمیسیون نصاب‌باشی از اجرت</label>
                    <div class="relative">
                        <input type="number" step="0.5" wire:model="default_commission_percent" class="w-full h-9 pr-3 pl-8 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                        <span class="absolute left-3 top-2 text-xs text-gray-400">٪</span>
                    </div>
                    <span class="text-[10px] text-gray-400">پیش‌فرض طبق پروپوزال: ۱۵ درصد</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">هزینه ثابت ایاب و ذهاب تکنسین (تومان)</label>
                    <input type="number" wire:model="base_travel_fee" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                    <span class="text-[10px] text-gray-400">پیش‌فرض طبق سناریو: ۶۰,۰۰۰ تومان</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تایمر معکوس پذیرش تکنسین (ثانیه)</label>
                    <input type="number" wire:model="dispatch_timeout_seconds" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                    <span class="text-[10px] text-gray-400">مهلت تصمیم‌گیری تکنسین پیش از بسط شعاع</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">مدت گارانتی رسمی خدمات (روز)</label>
                    <input type="number" wire:model="default_warranty_days" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                    <span class="text-[10px] text-gray-400">پیش‌فرض: ۹۰ روز ضمانت کیفیت</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Frontend & Landing Page Settings --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="text-xl">🖥️</span>
                <h2 class="text-sm font-black text-gray-900 dark:text-white font-sans">تنظیمات فرانت و لندینگ پیج نصاب‌باشی</h2>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-sans">
                هنگامی که در «تنظیمات کلی سیستم» قالب اصلی بر روی «نصاب‌باشی» تنظیم شود، موارد زیر در بالای سایت و بخش لندینگ پیج عمومی اعمال خواهند شد.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 font-sans">متن نوار اطلاع‌رسانی بالای هدر (Announcement Bar)</label>
                    <input type="text" wire:model="landing_announcement_text" placeholder="مثال: پوشش سراسری اعزام تکنسین در کلیه شهرستان‌های مازندران" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 font-sans">شماره تماس فوری پشتیبانی و مشاوره ۲۴ ساعته</label>
                    <input type="text" wire:model="landing_support_phone" placeholder="۰۱۱-۳۲۲۲۴۴۵۵" class="w-full h-9 px-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-800 dark:text-gray-200 font-sans" dir="ltr">
                </div>

                <div class="flex items-center gap-3 pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="landing_show_tech_count" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="mr-3 text-xs font-bold text-gray-700 dark:text-gray-300 font-sans">نمایش زنده تعداد تکنسین‌های آنلاین در رادار لندینگ</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Submit button --}}
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-indigo-600/20 transition">
                ذخیره تنظیمات نصاب‌باشی
            </button>
        </div>
    </form>
</div>
