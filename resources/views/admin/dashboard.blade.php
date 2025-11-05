<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('داشبورد مدیریت') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                    <h1 class="mt-2 text-2xl font-medium text-gray-900 dark:text-white">
                        به پنل مدیریت CRM خوش آمدید!
                    </h1>

                    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
                        از این بخش می‌توانید تنظیمات اصلی، کاربران و ماژول‌های سیستم را مدیریت کنید.
                    </p>
                </div>

                <div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">

                    {{-- اصلاح شد: این لینک‌ها اکنون باید به درستی نمایش داده شوند --}}

                    @if (Route::has('admin.users.index'))
                        <div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM12 12.75c0-1.113-.285-2.16-.786-3.07m.034 3.07a6.375 6.375 0 01-11.964-3.07M12 12.75c1.113 0 2.16-.285 3.07-.786m-3.07.786a6.375 6.375 0 00-3.07 11.964M12 12.75v.003c0 1.113.285 2.16.786 3.07M12 12.75v-.106a12.318 12.318 0 00-8.624-2.1c-2.331 0-4.512.645-6.374 1.766l-.001.109a6.375 6.375 0 0011.964 3.07m0-11.964a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                                </svg>
                                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    <a href="{{ route('admin.users.index') }}">مدیریت کاربران</a>
                                </h2>
                            </div>

                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                ایجاد کاربر جدید، ویرایش کاربران موجود، مدیریت نقش‌ها و سطوح دسترسی.
                            </p>

                            <p class="mt-4 text-sm">
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-300">
                                    مشاهده لیست کاربران
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500 dark:fill-indigo-200">
                                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </p>
                        </div>
                    @endif

                    @if (Route::has('admin.modules.index'))
                        <div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h12A2.25 2.25 0 0020.25 14.25V3M3.75 3H5.25m-1.5 0V3.75M3.75 3v.008m1.5 0h13.5m-13.5 0V3.75m13.5 0v.008m0-4.5v11.25A2.25 2.25 0 0118 16.5h-12A2.25 2.25 0 013.75 14.25V3.75m14.25 0v.008" />
                                </svg>

                                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    <a href="{{ route('admin.modules.index') }}">مدیریت ماژول‌ها</a>
                                </h2>
                            </div>

                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                فعال‌سازی یا غیرفعال‌سازی ماژول‌ها و افزودنی‌های اختیاری سیستم (مانند وبلاگ، فروشگاه و ...).
                            </p>

                            <p class="mt-4 text-sm">
                                <a href="{{ route('admin.modules.index') }}" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-300">
                                    مشاهده لیست ماژول‌ها
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500 dark:fill-indigo-200">
                                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

