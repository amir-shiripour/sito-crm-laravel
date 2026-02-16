@extends('layouts.admin')

@section('title', 'تنظیمات سیستم')

@php
    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    تنظیمات کلی سیستم
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    پیکربندی اطلاعات پایه سایت، لوگو، اطلاعات تماس و سایر تنظیمات عمومی.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-24">
            @csrf

            {{-- کارت ۱: اطلاعات پایه --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات پایه</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نام و نشان تجاری سایت</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="app_name" class="{{ $labelClass }}">عنوان سایت</label>
                        <input type="text" class="{{ $inputClass }}" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? config('app.name') }}">
                    </div>

                    <div>
                        <label for="app_logo" class="{{ $labelClass }}">لوگو</label>
                        <div class="flex items-center gap-4">
                            <div class="relative flex-1">
                                <input type="file" class="hidden" id="app_logo" name="app_logo" onchange="document.getElementById('logo-preview').src = window.URL.createObjectURL(this.files[0])">
                                <label for="app_logo" class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    انتخاب فایل...
                                </label>
                            </div>
                            @if(isset($settings['app_logo']))
                                <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white flex items-center justify-center">
                                    <img id="logo-preview" src="{{ asset($settings['app_logo']) }}" alt="Logo" class="max-w-full max-h-full">
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white flex items-center justify-center">
                                    <img id="logo-preview" src="" alt="" class="max-w-full max-h-full hidden">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="footer_text" class="{{ $labelClass }}">متن فوتر</label>
                        <input type="text" class="{{ $inputClass }}" id="footer_text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}">
                    </div>
                </div>
            </div>

            {{-- کارت ۲: اطلاعات تماس --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات تماس</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">راه‌های ارتباطی نمایش داده شده در سایت</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_email" class="{{ $labelClass }}">ایمیل تماس</label>
                        <input type="email" class="{{ $inputClass }} dir-ltr text-left" id="contact_email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">
                    </div>

                    <div>
                        <label for="contact_phone" class="{{ $labelClass }}">شماره تماس</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="contact_phone" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="{{ $labelClass }}">آدرس</label>
                        <textarea class="{{ $inputClass }}" id="address" name="address" rows="3">{{ $settings['address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- کارت ۳: تنظیمات هوش مصنوعی (GapGPT) --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات هوش مصنوعی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی اتصال به سرویس‌های هوش مصنوعی (GapGPT)</p>
                            </div>
                            <a href="{{ route('settings.gapgpt-logs.index') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                مشاهده تاریخچه درخواست‌ها
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="gapgpt_api_key" class="{{ $labelClass }}">کلید دسترسی (API Key)</label>
                        <input type="password" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_api_key" name="gapgpt_api_key" value="{{ $settings['gapgpt_api_key'] ?? '' }}" placeholder="sk-...">
                        <p class="text-xs text-gray-500 mt-1">کلید API دریافتی از پنل GapGPT را اینجا وارد کنید.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="gapgpt_base_url" class="{{ $labelClass }}">آدرس پایه (Base URL)</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_base_url" name="gapgpt_base_url" value="{{ $settings['gapgpt_base_url'] ?? 'https://api.gapgpt.app' }}" placeholder="https://api.gapgpt.app">
                        <p class="text-xs text-gray-500 mt-1">در صورت نیاز به تغییر آدرس پیش‌فرض API، آن را اینجا وارد کنید.</p>
                    </div>

                    <div>
                        <label for="gapgpt_default_model" class="{{ $labelClass }}">مدل پیش‌فرض</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_default_model" name="gapgpt_default_model" value="{{ $settings['gapgpt_default_model'] ?? 'gpt-4o-mini' }}" placeholder="gpt-4o-mini">
                        <p class="text-xs text-gray-500 mt-1">مدل زبانی پیش‌فرض برای درخواست‌ها (مثلاً gpt-4o-mini یا gpt-4).</p>
                    </div>

                    <div>
                        <label for="gapgpt_timeout" class="{{ $labelClass }}">تایم‌اوت (ثانیه)</label>
                        <input type="number" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_timeout" name="gapgpt_timeout" value="{{ $settings['gapgpt_timeout'] ?? '30' }}" placeholder="30">
                        <p class="text-xs text-gray-500 mt-1">حداکثر زمان انتظار برای پاسخ (به ثانیه).</p>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end pt-2">
                        <button type="button" onclick="testConnection()" id="test-btn" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            تست اتصال
                        </button>
                    </div>

                    <div id="test-result" class="md:col-span-2 hidden"></div>
                </div>
            </div>

            {{-- دکمه ذخیره --}}
            <div class="sticky bottom-4 z-40 flex justify-end">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function testConnection() {
            const btn = document.getElementById('test-btn');
            const resultDiv = document.getElementById('test-result');
            const apiKey = document.getElementById('gapgpt_api_key').value;
            const baseUrl = document.getElementById('gapgpt_base_url').value;

            if (!apiKey) {
                alert('لطفاً ابتدا کلید API را وارد کنید.');
                return;
            }

            // حالت لودینگ
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> در حال بررسی...';
            resultDiv.classList.add('hidden');
            resultDiv.className = 'md:col-span-2 hidden'; // ریست کلاس‌ها

            fetch('{{ route('settings.test-gapgpt') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    gapgpt_api_key: apiKey,
                    gapgpt_base_url: baseUrl
                })
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.classList.remove('hidden');
                if (data.success) {
                    resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm';
                    resultDiv.innerHTML = `
                        <div class="flex items-center gap-2 font-bold mb-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            ${data.message}
                        </div>
                        <div class="text-xs opacity-80">
                            مدل‌های نمونه: ${data.models.map(m => m.id).join(', ')}
                        </div>
                    `;
                } else {
                    resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm';
                    resultDiv.innerHTML = `
                        <div class="flex items-center gap-2 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.classList.remove('hidden');
                resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm';
                resultDiv.innerHTML = 'خطای غیرمنتظره رخ داد. لطفاً کنسول مرورگر را بررسی کنید.';
                console.error('Error:', error);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> تست اتصال';
            });
        }
    </script>
@endsection
