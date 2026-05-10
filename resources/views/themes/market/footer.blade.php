<footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 pt-10 md:pt-12 pb-8 z-10 relative mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10 md:gap-8 mb-10 md:mb-12 text-center sm:text-right">

        {{-- توضیحات و لوگو --}}
        <div class="sm:col-span-2 space-y-5 flex flex-col items-center sm:items-start">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-10 w-auto">
                @endif
                <span class="font-black text-2xl text-orange-600">{{ $appName }}</span>
            </a>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-md">
                ما با ارائه بهترین کالاها از معتبرترین برندها، همراه با ضمانت اصالت و بازگشت وجه، تجربه یک خرید اینترنتی لذت‌بخش و مطمئن را برای شما فراهم می‌کنیم.
            </p>
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 text-gray-600 dark:text-gray-300">
                <div class="font-bold text-sm sm:text-base">پشتیبانی:</div>
                <div class="dir-ltr font-black text-xl md:text-2xl text-gray-900 dark:text-white">۰۲۱ - ۹۱۰۰۰۰۰۰</div>
            </div>
        </div>

        {{-- لینک‌های مفید --}}
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-lg">خدمات مشتریان</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="#" class="hover:text-orange-600 transition-colors inline-block">پاسخ به پرسش‌های متداول</a></li>
                <li><a href="#" class="hover:text-orange-600 transition-colors inline-block">رویه بازگرداندن کالا</a></li>
                <li><a href="#" class="hover:text-orange-600 transition-colors inline-block">شرایط استفاده</a></li>
                <li><a href="#" class="hover:text-orange-600 transition-colors inline-block">حریم خصوصی</a></li>
            </ul>
        </div>

        {{-- نمادها --}}
        <div class="flex flex-col items-center sm:items-start">
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-lg">نمادهای اعتماد</h4>
            <div class="flex gap-4 justify-center sm:justify-start">
                <div class="w-20 h-24 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 flex items-center justify-center text-xs text-gray-400 shadow-sm">ENAMAD</div>
                <div class="w-20 h-24 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 flex items-center justify-center text-xs text-gray-400 shadow-sm">SAMANDEHI</div>
            </div>
        </div>
    </div>

    {{-- بخش انتهایی (کپی رایت و دانلود اپلیکیشن) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 border-t border-gray-100 dark:border-gray-800/50 pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-right w-full md:w-auto">
            &copy; {{ date('Y') }} {{ $footerText }}
        </p>

        <div class="flex flex-wrap justify-center md:justify-end gap-3 w-full md:w-auto">
            {{-- دانلود اپلیکیشن (ویژوال) --}}
            <a href="#" class="bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                دانلود از بازار
            </a>
            <a href="#" class="bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                دریافت از مایکت
            </a>
        </div>
    </div>
</footer>
