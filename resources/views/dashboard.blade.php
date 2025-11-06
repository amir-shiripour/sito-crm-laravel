@extends('layouts.user')
@php($title = 'داشبورد کاربری')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">کل وظایف</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">12</div>
            <div class="mt-3 flex items-center text-xs text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                <span>۴ مورد بیشتر از هفته قبل</span>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">جلسات امروز</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">3</div>
            <div class="mt-3 flex items-center text-xs text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5"/></svg>
                <span>اولین جلسه ساعت ۱۰:۳۰</span>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">تیکت‌های باز</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">5</div>
            <div class="mt-3 flex items-center text-xs text-amber-500 dark:text-amber-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>۲ مورد نیازمند پیگیری فوری</span>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">میانگین پاسخ</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">۱.۲ ساعت</div>
            <div class="mt-3 flex items-center text-xs text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25l3.5 3.5 10-10"/></svg>
                <span>۳۲٪ بهبود نسبت به ماه گذشته</span>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">فعالیت‌های اخیر</h3>
                <a href="#" class="text-xs text-indigo-600 dark:text-indigo-300 hover:text-indigo-500">نمایش همه</a>
            </div>
            <ul class="space-y-4">
                <li class="flex items-start gap-3">
                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">۱</span>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        تماس با مشتری «شرکت آلفا» ثبت شد و منتظر پیگیری است.
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">۵ دقیقه قبل</div>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">۲</span>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        یادداشت پیگیری برای فرصت فروش «سرنخ ۲۳۴» اضافه شد.
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">۱ ساعت قبل</div>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">۳</span>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        وضعیت قرارداد «پروژه فین‌تک» به «نیازمند تایید» تغییر یافت.
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">دیروز</div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">میانبرهای سریع</h3>
            <div class="flex flex-col gap-3 text-sm">
                <a href="{{ route('profile.show') }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">ویرایش پروفایل</a>
                <a href="#" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">ایجاد یادآور جدید</a>
                <a href="#" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">مشاهده گزارش عملکرد</a>
            </div>
        </div>
    </div>
@endsection
