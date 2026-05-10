@extends('layouts.web')

@section('title', 'فروشگاه آنلاین برتر')

@section('content')
    <div class="w-full flex-grow flex flex-col">

        {{-- 1. Hero Search Section --}}
        <div class="w-full bg-gradient-to-br from-orange-500 to-red-500 py-42 px-6 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==')] opacity-30"></div>

            <div class="max-w-4xl mx-auto text-center relative z-10 space-y-8 animate-in fade-in slide-in-from-bottom-4">
                <h1 class="text-4xl md:text-5xl font-black text-white leading-tight">
                    هر آنچه نیاز دارید، <br class="md:hidden" />با بهترین قیمت پیدا کنید
                </h1>

                {{-- Big Search Bar --}}
                <div class="relative w-full max-w-3xl mx-auto group">
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-orange-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" placeholder="جستجو در بین هزاران کالا (مثلا: گوشی موبایل، لباس...)"
                           class="w-full h-16 pl-32 pr-14 rounded-full border-0 bg-white dark:bg-gray-900 shadow-2xl text-lg text-gray-900 dark:text-white focus:ring-4 focus:ring-orange-300 dark:focus:ring-orange-900/50 transition-all">
                    <button class="absolute inset-y-2 left-2 px-6 rounded-full bg-orange-600 hover:bg-orange-700 text-white font-bold transition-colors">
                        جستجو
                    </button>
                </div>

                <div class="flex flex-wrap justify-center gap-3 text-sm text-white/80 font-medium">
                    <span>جستجوهای پرطرفدار:</span>
                    <a href="#" class="hover:text-white bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm transition-colors">گوشی سامسونگ</a>
                    <a href="#" class="hover:text-white bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm transition-colors">لپ‌تاپ گیمینگ</a>
                    <a href="#" class="hover:text-white bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm transition-colors">کفش ورزشی</a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 w-full -mt-8 relative z-20 pb-16 space-y-16">

            {{-- 2. Categories --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 md:p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-6 text-center">
                    @php
                        $cats = ['کالای دیجیتال', 'مد و پوشاک', 'خانه و آشپزخانه', 'لوازم تحریر', 'ورزش و سفر', 'زیبایی و سلامت', 'اسباب‌بازی', 'سوپرمارکت'];
                        $icons = ['M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z', 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'];
                    @endphp
                    @foreach($cats as $k => $cat)
                        <a href="#" class="flex flex-col items-center gap-3 group">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icons[$k] }}" /></svg>
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $cat }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- 3. Product Grid (Mock Data) --}}
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-8 rounded-full bg-orange-500"></span>
                        پیشنهادات شگفت‌انگیز
                    </h2>
                    <a href="#" class="text-sm font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1">مشاهده همه <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @for($i=1; $i<=8; $i++)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                            {{-- Product Image Placeholder --}}
                            <div class="h-48 bg-gray-100 dark:bg-gray-900 relative p-4 flex items-center justify-center">
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">٪۲۰ تخفیف</span>
                                <svg class="w-20 h-20 text-gray-300 dark:text-gray-700 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm mb-2 line-clamp-2">گوشی موبایل هوشمند مدل Pro Max با ظرفیت ۲۵۶ گیگابایت</h3>
                                <div class="flex items-center gap-1 text-amber-500 text-xs mb-4">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    ۴.۸ (۱۲۰ نظر)
                                </div>
                                <div class="flex items-end justify-between">
                                    <div class="text-left flex-1">
                                        <div class="text-xs text-gray-400 line-through mb-1">۴۵,۰۰۰,۰۰۰</div>
                                        <div class="text-lg font-black text-gray-900 dark:text-white">۳۶,۰۰۰,۰۰۰ <span class="text-xs font-normal text-gray-500">تومان</span></div>
                                    </div>
                                </div>
                                <button class="w-full mt-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-sm group-hover:bg-orange-500 group-hover:text-white transition-colors">
                                    افزودن به سبد خرید
                                </button>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- 4. Features/Guarantees --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 py-8 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg></div>
                    <div><div class="font-bold dark:text-white">ارسال سریع</div><div class="text-xs text-gray-500">برای سفارش‌های بالای ۵۰۰ هزارتومان</div></div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></div>
                    <div><div class="font-bold dark:text-white">ضمانت اصالت</div><div class="text-xs text-gray-500">تضمین کیفیت کالاها</div></div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></div>
                    <div><div class="font-bold dark:text-white">پرداخت امن</div><div class="text-xs text-gray-500">درگاه‌های معتبر بانکی</div></div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
                    <div><div class="font-bold dark:text-white">پشتیبانی ۲۴ ساعته</div><div class="text-xs text-gray-500">همیشه پاسخگوی شما هستیم</div></div>
                </div>
            </div>

        </div>
    </div>
@endsection
