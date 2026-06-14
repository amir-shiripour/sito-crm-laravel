<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-6 dark:bg-gray-950">

        {{-- لوگو و عنوان --}}
        <div class="mb-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">ورود به حساب کاربری</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">برای ادامه لطفا وارد سیستم شوید</p>
        </div>

        {{-- کارت فرم --}}
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-gray-200/50 dark:bg-gray-900 dark:shadow-none border border-gray-100 dark:border-gray-800">

            {{-- نمایش خطاها --}}
            <x-validation-errors class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-900/30" />

            @if (session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- ایمیل یا موبایل --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">ایمیل یا شماره موبایل</label>
                    <div class="relative">
                        <input id="email" type="text" name="email" :value="old('email')" required autofocus autocomplete="username"
                               class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr"
                               placeholder="user@example.com یا 0912..." />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- رمز عبور --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">رمز عبور</label>
                    </div>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr"
                               placeholder="••••••••" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <script>
                        function togglePasswordVisibility() {
                            const passwordInput = document.getElementById('password');
                            const eyeIcon = document.getElementById('eye-icon');
                            const eyeOffIcon = document.getElementById('eye-off-icon');
                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                eyeIcon.classList.add('hidden');
                                eyeOffIcon.classList.remove('hidden');
                            } else {
                                passwordInput.type = 'password';
                                eyeIcon.classList.remove('hidden');
                                eyeOffIcon.classList.add('hidden');
                            }
                        }
                    </script>
                </div>

                {{-- یادآوری و فراموشی رمز --}}
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center cursor-pointer group">
                        <div class="relative flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" class="peer h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                        </div>
                        <span class="mr-2 text-sm text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none transition-colors">مرا به خاطر بسپار</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors" href="{{ route('password.request') }}">
                            فراموشی رمز عبور؟
                        </a>
                    @endif
                </div>

                {{-- دکمه ورود --}}
                <button type="submit" class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-200 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                    </span>
                    ورود به سیستم
                </button>
            </form>
        </div>

        {{-- فوتر لاگین --}}
        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} تمامی حقوق محفوظ است.
        </p>
    </div>
</x-guest-layout>
