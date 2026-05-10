<footer class="bg-slate-900 text-slate-300 pb-8 z-10 relative mt-auto border-t border-slate-800">
    {{--<footer class="bg-slate-900 text-slate-300 pt-16 pb-8 z-10 relative mt-auto border-t border-slate-800">--}}

    {{-- بخش بالا و گریدها (اصلاح شده برای ریسپانسیو بودن در صورت استفاده در آینده) --}}
    {{--<div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 pt-8">
        <div class="space-y-6 sm:col-span-2 lg:col-span-1">
            <h3 class="font-black text-2xl text-white flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
                {{ $appName }}
            </h3>
            <p class="text-sm leading-relaxed text-slate-400">
                جامع‌ترین پلتفرم جستجو و ثبت آگهی املاک. ما به شما کمک می‌کنیم تا با خیالی آسوده و در سریع‌ترین زمان ممکن، خانه رویایی خود را پیدا کنید یا ملک خود را به فروش برسانید.
            </p>
        </div>

        <div>
            <h4 class="font-bold text-white mb-4 text-lg">مناطق پرجستجو</h4>
            <ul class="space-y-3 text-sm">
                <li><a href="#" class="hover:text-blue-400 transition-colors">خرید آپارتمان در سعادت‌آباد</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">اجاره ویلا در لواسان</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">خرید دفتر کار در ونک</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">پروژه‌های پیش‌فروش چیتگر</a></li>
            </ul>
        </div>

        <div>
            <h4 class="font-bold text-white mb-4 text-lg">خدمات سامانه</h4>
            <ul class="space-y-3 text-sm">
                <li><a href="#" class="hover:text-blue-400 transition-colors">ثبت آگهی فروش ملک</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">درخواست مشاوره حقوقی</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">محاسبه آنلاین کمیسیون</a></li>
                <li><a href="#" class="hover:text-blue-400 transition-colors">اخبار و تحلیل بازار مسکن</a></li>
            </ul>
        </div>

        <div class="sm:col-span-2 lg:col-span-1">
            <h4 class="font-bold text-white mb-4 text-lg">خبرنامه بازار مسکن</h4>
            <p class="text-xs text-slate-400 mb-4">برای اطلاع از جدیدترین فایل‌های اکازیون و تحلیل‌های بازار، ایمیل خود را وارد کنید.</p>
            <div class="relative">
                <input type="email" placeholder="آدرس ایمیل شما" class="w-full bg-slate-800 border-none rounded-xl py-3 px-4 pr-20 text-sm text-white focus:ring-2 focus:ring-blue-500 placeholder-slate-500">
                <button class="absolute left-1 top-1 bottom-1 bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg text-xs font-bold transition-colors">عضویت</button>
            </div>
        </div>
    </div>--}}

    {{--    <div class="max-w-7xl mx-auto px-6 border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">--}}
    <div class="max-w-7xl mx-auto px-6 pt-8 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4">
        <p class="text-sm font-medium text-slate-500 text-center sm:text-right w-full">
            &copy; {{ date('Y') }} {{ $footerText }}
        </p>
        {{--<div class="flex items-center gap-4 mt-2 sm:mt-0">
            <span class="text-sm text-slate-400 font-bold">۰۲۱-۹۱۰۰۰۰۰۰</span>
        </div>--}}
    </div>
</footer>
