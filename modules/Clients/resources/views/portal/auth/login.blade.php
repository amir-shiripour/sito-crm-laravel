@extends('clients::layouts.client')
@php($title = 'ورود به پنل مشتریان')

@section('content')
    <div class="flex min-h-[calc(100vh-140px)] flex-col items-center justify-center py-10 px-4">

        <div class="w-full max-w-md animate-in fade-in slide-in-from-bottom-4 duration-700">
            {{-- کارت لاگین --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">

                {{-- هدر کارت --}}
                <div class="bg-gray-50/50 dark:bg-gray-900/30 px-6 py-6 border-b border-gray-100 dark:border-gray-700/60 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        ورود به پنل کاربری
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto leading-relaxed">
                        جهت دسترسی به داشبورد و پیگیری درخواست‌ها، وارد حساب خود شوید.
                    </p>
                </div>

                <form method="POST" action="{{ route('client.login.submit') }}" class="px-6 py-8 space-y-5">
                    @csrf

                    {{-- نمایش خطاهای کلی --}}
                    @if($errors->any())
                        <div class="rounded-xl bg-red-50 p-3 text-xs font-medium text-red-600 dark:bg-red-900/10 dark:text-red-400 border border-red-100 dark:border-red-900/20 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>اطلاعات ورود صحیح نیست. لطفاً مجدداً تلاش کنید.</span>
                        </div>
                    @endif

                    {{-- نام کاربری --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            نام کاربری
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="username"
                                   value="{{ old('username') }}"
                                   autocomplete="username"
                                   required
                                   autofocus
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                                          placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                          transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                                   placeholder="Username">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- رمز عبور --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            رمز عبور
                        </label>
                        <div class="relative">
                            <input type="password"
                                   name="password"
                                   autocomplete="current-password"
                                   required
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                                          placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                          transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                                   placeholder="••••••••">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- آپشن‌ها --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="remember" class="peer h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                            </div>
                            <span class="mr-2 text-xs font-medium text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none transition-colors">مرا به خاطر بسپار</span>
                        </label>
                    </div>

                    {{-- دکمه --}}
                    <button type="submit"
                            class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
                        ورود به پنل
                        <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                    </button>

                    {{-- فوتر فرم --}}
                    <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">
                            در صورت فراموشی رمز عبور، با پشتیبانی تماس بگیرید.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
