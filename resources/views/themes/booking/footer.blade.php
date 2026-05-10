{{--<footer class="border-t border-teal-100 dark:border-teal-900/30 bg-teal-50/50 dark:bg-gray-950 pt-16 pb-8 z-10 relative mt-auto">--}}
<footer class="border-t border-teal-100 dark:border-teal-900/30 bg-teal-50/50 dark:bg-gray-950 pb-8 z-10 relative mt-auto">

    {{-- بخش اطلاعات کلینیک (اصلاح شده برای ریسپانسیو بودن در صورت استفاده در آینده) --}}
    {{--<div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 pt-8">
        <div class="sm:col-span-2 space-y-4">
            <h3 class="font-black text-2xl text-teal-700 dark:text-teal-500">{{ $appName }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-md">
                با بهره‌گیری از مجرب‌ترین پزشکان متخصص و پیشرفته‌ترین تجهیزات پزشکی، متعهد به ارائه بالاترین سطح خدمات درمانی و مراقبتی به شما عزیزان هستیم. سلامتی شما، افتخار ماست.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-start sm:items-center gap-4 text-teal-800 dark:text-teal-400">
                <div class="w-12 h-12 rounded-full bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-500 mb-1">پاسخگویی اورژانسی (۲۴ ساعته)</div>
                    <div class="text-2xl font-black dir-ltr text-right sm:text-left">۰۲۱ - ۸۸۸۸ ۸۸۸۸</div>
                </div>
            </div>
        </div>

        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">دسترسی سریع</h4>
            <ul class="space-y-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                <li><a href="#" class="hover:text-teal-600 transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-teal-500"></span> رزرو نوبت آنلاین</a></li>
                <li><a href="#" class="hover:text-teal-600 transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-teal-500"></span> برنامه حضور پزشکان</a></li>
                <li><a href="#" class="hover:text-teal-600 transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-teal-500"></span> جواب‌دهی آزمایشگاه</a></li>
                <li><a href="#" class="hover:text-teal-600 transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-teal-500"></span> بیمه‌های طرف قرارداد</a></li>
            </ul>
        </div>

        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">اطلاعات مراجعه</h4>
            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex gap-2">
                    <svg class="w-5 h-5 text-teal-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>تهران، میدان ونک، خیابان ولیعصر، پلاک ۱۰۰</span>
                </li>
                <li class="flex gap-2">
                    <svg class="w-5 h-5 text-teal-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>شنبه تا پنجشنبه: ۸ صبح الی ۲۲ شب<br>جمعه‌ها: تعطیل</span>
                </li>
            </ul>
        </div>
    </div>--}}

    {{-- بخش کپی رایت --}}
    {{--<div class="max-w-7xl mx-auto px-6 border-t border-teal-100 dark:border-teal-900/30 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">--}}
    <div class="max-w-7xl mx-auto px-6 border-t border-teal-100 dark:border-teal-900/30 pt-8 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center sm:text-right w-full">
            &copy; {{ date('Y') }} {{ $footerText }}
        </p>
    </div>
</footer>
