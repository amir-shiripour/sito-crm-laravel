<footer class="border-t border-gray-200 dark:border-gray-800/60 bg-gray-50 dark:bg-gray-900/50 pt-16 pb-8 z-10 relative mt-auto">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
        <div class="space-y-4 sm:col-span-2 lg:col-span-1">
            <h3 class="font-black text-xl text-gray-900 dark:text-white">{{ $appName }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                ما به کسب‌وکارها کمک می‌کنیم تا با اتوماسیون فرآیندها و مدیریت هوشمند داده‌ها، با سرعت بیشتری رشد کنند.
            </p>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">محصولات</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="#" class="hover:text-indigo-600 transition-colors">مدیریت مشتریان (CRM)</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">اتوماسیون بازاریابی</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">فرم‌ساز هوشمند</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">شرکت</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="#" class="hover:text-indigo-600 transition-colors">درباره ما</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">فرصت‌های شغلی</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">ارتباط با ما</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">تماس با ما</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li class="dir-ltr text-right sm:text-left lg:text-right">۰۲۱-۹۱۰۰۰۰۰۰</li>
                <li class="dir-ltr text-right sm:text-left lg:text-right">info@domain.com</li>
                <li>تهران، خیابان ولیعصر، پلاک ۱</li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-6 border-t border-gray-200 dark:border-gray-800 pt-8 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center sm:text-right">
            &copy; {{ date('Y') }} {{ $footerText }}
        </p>
        <div class="flex items-center justify-center gap-4">
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">in</a>
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">tw</a>
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">ig</a>
        </div>
    </div>
</footer>
