<x-app-layout>
    {{-- هدر صفحه که در لی‌آوت اصلی (app.blade.php) نمایش داده می‌شود --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('داشبورد مدیریت') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                {{-- محتوای خوش‌آمدگویی و لینک‌های سریع --}}
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        به پنل مدیریت CRM خوش آمدید!
                    </h1>

                    <p class="mt-4 text-gray-600">
                        از این بخش می‌توانید قسمت‌های مختلف سیستم را مدیریت کنید. برای شروع، یکی از گزینه‌های زیر را انتخاب کنید:
                    </p>
                </div>

                {{-- بخش لینک‌های سریع --}}
                <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">

                    {{-- لینک مدیریت کاربران --}}
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250">
                            <div classa="flex items-center justify-center">
                                <div class="h-16 w-16 bg-indigo-50 dark:bg-indigo-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372m-1.04-1.101M15 19.128c-.083.002-.166.004-.25.006a10.5 10.5 0 01-9.25-10.5C5.5 7.18 8.18 5.5 10.75 5.5c.343 0 .68.026 1.012.074m.022.022L12 5.64l.006-.006a.31.31 0 01.028-.028m.028-.028l.006-.006a.934.934 0 01.204-.133m.387.262c.11.06.222.113.337.158m-.337-.158l.006.006q.016.006.028.012m.028.012l.006.006a1.03 1.03 0 01.12.062m.12.062l.006.006a.31.31 0 01.028.028m.028.028l.006.006c.093.159.199.308.318.445m.318.445L15 8.711l.006.006a.31.31 0 01.028.028m.028.028l.006.006a.97.97 0 01.107.126m.107.126l.006.006a.31.31 0 01.028.028m.028.028l.006.006C15.94 10.12 16 10.574 16 11c0 .426-.06 1.03-.178 1.688m.178-1.688l.006.006a.31.31 0 01.028.028m.028.028l.006.006c.059.13.109.262.152.396m.152.396l.006.006a.31.31 0 01.028.028m.028.028l.006.006a.934.934 0 01.133.204m.262-.387a.934.934 0 01.133.204m.262-.387l.006.006a.31.31 0 01.028.028m.028.028l.006.006a.97.97 0 01.126.107m.126.107l.006.006a.31.31 0 01.028.028m.028.028l.006.006C17.43 12.94 17.5 13.5 17.5 14c0 .426-.06 1.03-.178 1.688m.178-1.688l.006.006a.31.31 0 01.028.028m.028.028l.006.006c.059.13.109.262.152.396m.152.396l.006.006a.31.31 0 01.028.028m.028.028l.006.006a.934.934 0 01.133.204m.262-.387a.934.934 0 01.133.204m.262-.387l.006.006a.31.31 0 01.028.028m.028.028l.006.006a.97.97 0 01.126.107m.126.107l.006.006a.31.31 0 01.028.028m.028.028l.V" />
                                    </svg>
                                </div>

                                <div class="ms-4">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">مدیریت کاربران</h2>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                        مشاهده لیست کاربران، ویرایش اطلاعات (موبایل، نقش و...) و مدیریت دسترسی‌ها.
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- لینک مدیریت ماژول‌ها (جدید) --}}
                    <div>
                        <a href="{{ route('admin.modules.index') }}" class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250">
                            <div class="flex items-center justify-center">
                                <div class="h-16 w-16 bg-indigo-50 dark:bg-indigo-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                </div>
                                <div class="ms-4">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">مدیریت ماژول‌ها</h2>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                        فعال‌سازی یا غیرفعال‌سازی فیچرها و افزونه‌های سیستم (مانند بلاگ، فروشگاه و...).
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

