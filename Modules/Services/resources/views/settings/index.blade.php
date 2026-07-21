@extends('layouts.user')
@section('title', 'تنظیمات سرویس‌ها')

@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2";
    $cardClass  = "bg-white dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden";
    $v = fn(string $key, $default='') => $raw[$key] ?? $default;

    $identityTabRoute = null;
    foreach (['settings.identity.index', 'settings.identity', 'settings.company.index'] as $candidateRoute) {
        if (\Illuminate\Support\Facades\Route::has($candidateRoute)) {
            $identityTabRoute = route($candidateRoute);
            break;
        }
    }

    $tabs = [
        'numbering'   => ['label' => 'شماره‌گذاری', 'icon' => 'hash'],
        'finance'     => ['label' => 'مالی و ارز', 'icon' => 'coin'],
        'print'       => ['label' => 'چاپ و فاکتور', 'icon' => 'printer'],
        'automation'  => ['label' => 'مدیریت گردش کارها', 'icon' => 'bolt'],
    ];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

        {{-- Hero Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">تنظیمات ماژول
                        سرویس‌ها و خدمات</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیکربندی قوانین صدور فاکتور و نصب گردش کارهای پیش‌فرض خدمات.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('services.settings.update') }}"
              x-data="settingsForm('{{ $v('services_invoice_prefix','SRV-') }}','{{ $v('services_invoice_middle_prefix',date('Y')) }}','{{ $v('services_invoice_suffix','') }}','{{ $v('services_invoice_padding',4) }}', '{{ $v('services_proforma_invoice_prefix','PI-') }}','{{ $v('services_proforma_invoice_middle_prefix',date('Y')) }}','{{ $v('services_proforma_invoice_suffix','') }}','{{ $v('services_proforma_invoice_padding',4) }}')">
            @csrf @method('PUT')

            {{-- Tab Navigation --}}
            <input type="hidden" name="active_tab" x-model="activeTab">
            <div class="sticky top-4 z-30 mb-6">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-2 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm flex flex-wrap items-center gap-1.5">
                    @foreach($tabs as $tabKey => $tab)
                        <button type="button" @click="activeTab = '{{ $tabKey }}'"
                                :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="flex-1 min-w-36 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                            @switch($tab['icon'])
                                @case('hash')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    @break
                                @case('coin')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @case('printer')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm-3-9.5a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"/>
                                    </svg>
                                    @break
                                @case('bolt')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    @break
                            @endswitch
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div x-show="activeTab === 'numbering'" x-cloak class="space-y-6">

                {{-- Invoice Number Builder --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            شماره‌گذاری فاکتور
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">الگوی تولید شماره برای فاکتورهای
                            جدید
                            را تعیین کنید.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">پیشوند (Prefix)</label>
                                <input type="text" name="services_invoice_prefix" x-model="prefix"
                                       @input="updatePreview()"
                                       value="{{ $v('services_invoice_prefix','SRV-') }}" class="{{ $inputClass }}"
                                       placeholder="SRV-">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">میانی (مثلاً سال)</label>
                                <input type="text" name="services_invoice_middle_prefix" x-model="middle"
                                       @input="updatePreview()"
                                       value="{{ $v('services_invoice_middle_prefix',date('Y')) }}"
                                       class="{{ $inputClass }}" placeholder="{{ date('Y') }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">پسوند (Suffix)</label>
                                <input type="text" name="services_invoice_suffix" x-model="suffix"
                                       @input="updatePreview()"
                                       value="{{ $v('services_invoice_suffix','') }}" class="{{ $inputClass }}"
                                       placeholder="-CRM">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">طول شماره</label>
                                <input type="number" name="services_invoice_padding" x-model.number="padding"
                                       @input="updatePreview()" min="1" max="10"
                                       value="{{ $v('services_invoice_padding',4) }}"
                                       class="{{ $inputClass }} dir-ltr text-left">
                            </div>
                        </div>
                        <div
                            class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/10 border border-indigo-100 dark:border-indigo-500/20 flex flex-col items-center justify-center text-center">
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-indigo-400 dark:text-indigo-500 mb-2">پیش‌نمایش شماره فاکتور</span>
                            <span
                                class="font-mono text-3xl font-black text-indigo-600 dark:text-indigo-300 tracking-wider"
                                x-text="preview"></span>
                        </div>
                        <div class="mt-6 border-t border-gray-100 dark:border-gray-700/60 pt-6">
                            <label for="services_invoice_auto"
                                   class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                                <div class="flex-1">
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">شماره‌گذاری اتوماتیک فاکتور</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">در صورت فعال بودن، در صفحه ایجاد فاکتور، سیستم به صورت خودکار فرمت را تکمیل و قفل می‌کند.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="services_invoice_auto" value="0">
                                    <input type="checkbox" id="services_invoice_auto" name="services_invoice_auto"
                                           value="1"
                                           @checked($v('services_invoice_auto') === '1') class="sr-only peer">
                                    <div
                                        class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors duration-300 shadow-inner"></div>
                                    <div
                                        class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                        <svg
                                            class="w-3 h-3 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            شماره‌گذاری پیش فاکتور
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">الگوی تولید شماره برای پیش
                            فاکتورهای
                            جدید را تعیین کنید.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">پیشوند (Prefix)</label>
                                <input type="text" name="services_proforma_invoice_prefix" x-model="proforma_prefix"
                                       @input="updateProformaPreview()"
                                       value="{{ $v('services_proforma_invoice_prefix','PI-') }}"
                                       class="{{ $inputClass }}"
                                       placeholder="PI-">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">میانی (مثلاً سال)</label>
                                <input type="text" name="services_proforma_invoice_middle_prefix"
                                       x-model="proforma_middle"
                                       @input="updateProformaPreview()"
                                       value="{{ $v('services_proforma_invoice_middle_prefix',date('Y')) }}"
                                       class="{{ $inputClass }}" placeholder="{{ date('Y') }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">پسوند (Suffix)</label>
                                <input type="text" name="services_proforma_invoice_suffix" x-model="proforma_suffix"
                                       @input="updateProformaPreview()"
                                       value="{{ $v('services_proforma_invoice_suffix','') }}" class="{{ $inputClass }}"
                                       placeholder="-CRM">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">طول شماره</label>
                                <input type="number" name="services_proforma_invoice_padding"
                                       x-model.number="proforma_padding"
                                       @input="updateProformaPreview()" min="1" max="10"
                                       value="{{ $v('services_proforma_invoice_padding',4) }}"
                                       class="{{ $inputClass }} dir-ltr text-left">
                            </div>
                        </div>
                        <div
                            class="relative overflow-hidden p-6 rounded-2xl bg-linear-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/10 border border-blue-100 dark:border-blue-500/20 flex flex-col items-center justify-center text-center">
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-blue-400 dark:text-blue-500 mb-2">پیش‌نمایش شماره پیش فاکتور</span>
                            <span class="font-mono text-3xl font-black text-blue-600 dark:text-blue-300 tracking-wider"
                                  x-text="proforma_preview"></span>
                        </div>
                        <div class="mt-6 border-t border-gray-100 dark:border-gray-700/60 pt-6">
                            <label for="services_proforma_invoice_auto"
                                   class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                                <div class="flex-1">
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">شماره‌گذاری اتوماتیک پیش فاکتور</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">تولید خودکار شماره پیش فاکتور در زمان ایجاد، به فرمت تنظیم شده</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="services_proforma_invoice_auto" value="0">
                                    <input type="checkbox" id="services_proforma_invoice_auto"
                                           name="services_proforma_invoice_auto" value="1"
                                           @checked($v('services_proforma_invoice_auto') === '1') class="sr-only peer">
                                    <div
                                        class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-300 shadow-inner"></div>
                                    <div
                                        class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                        <svg
                                            class="w-3 h-3 text-blue-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'finance'" x-cloak class="space-y-6">

                {{-- Default Tax --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-inner">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2"><path
                                        stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                            </span>
                            تنظیمات مالیات پیش‌فرض
                        </h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2 mr-15">این درصد مالیات به
                            صورت
                            اتوماتیک در فاکتورهای جدید لحاظ می‌گردد.</p>
                    </div>
                    <div class="p-6 md:p-8">
                        <div class="max-w-md">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">درصد مالیات
                                (٪)</label>
                            <div class="relative flex items-center">
                                <input type="number" name="services_default_tax_rate" min="0" max="100" step="0.01"
                                       value="{{ $v('services_default_tax_rate', 9) }}"
                                       class="w-full rounded-2xl border-2 border-gray-200 bg-gray-50 px-5 py-4 text-lg font-black text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-emerald-500 text-center dir-ltr shadow-sm">
                                <span
                                    class="absolute right-5 text-gray-400 font-black text-lg pointer-events-none">%</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-3 flex items-start gap-1.5 leading-relaxed">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                در صفحه ایجاد فاکتور، این مقدار را به‌صورت خودکار دریافت می‌کنید. همچنین امکان ویرایش
                                (تایپ
                                کردن) یا قفل‌کردن مقدار مالیات برای هر فاکتور را توسط دکمه‌ی تاگلِ ادیت خواهید داشت.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tax Mode: invoice-level vs per-item --}}
                <div class="{{ $cardClass }}" x-data="{ taxMode: '{{ $v('services_tax_mode','invoice') }}' }">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-inner">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </span>
                            نحوه محاسبه مالیات
                        </h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2 mr-15">مشخص کنید مالیات
                            روی کل فاکتور اعمال شود یا هر ردیف بتواند مالیات مستقل خودش را داشته باشد.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="taxMode === 'invoice' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-500/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                                <input type="radio" name="services_tax_mode" value="invoice" x-model="taxMode"
                                       class="mt-1 accent-emerald-600">
                                <span>
                                    <span class="block font-bold text-gray-800 dark:text-gray-100">مالیات کل فاکتور</span>
                                    <span class="block text-xs text-gray-400 mt-1">مثل قبل؛ یک درصد مالیات برای کل فاکتور تعیین می‌شود.</span>
                                </span>
                            </label>
                            <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="taxMode === 'item' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-500/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                                <input type="radio" name="services_tax_mode" value="item" x-model="taxMode"
                                       class="mt-1 accent-emerald-600">
                                <span>
                                    <span class="block font-bold text-gray-800 dark:text-gray-100">مالیات تفکیکی هر ردیف</span>
                                    <span class="block text-xs text-gray-400 mt-1">هر ردیف فاکتور (سرویس یا دستی) مالیات مستقل خودش را دارد.</span>
                                </span>
                            </label>
                        </div>

                        <div x-show="taxMode === 'item'" x-transition x-cloak
                             class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                            <div>
                                <label for="services_tax_apply_custom_fields"
                                       class="font-bold text-sm text-gray-800 dark:text-gray-100 cursor-pointer">اعمال مالیات روی فیلدهای سفارشی قیمت‌دار</label>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" id="services_tax_apply_custom_fields"
                                       name="services_tax_apply_custom_fields" value="1"
                                       @checked($v('services_tax_apply_custom_fields', false))
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-emerald-600 transition-colors"></div>
                                <div class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:-translate-x-5"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Currency Settings --}}
                <div class="{{ $cardClass }}"
                     x-data="{ currency: '{{ $v('currency','toman') }}' }">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            واحد پول سیستم
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">واحد پولی پایه برای محاسبات و
                            نمایش
                            در تمام بخش‌های سیستم.</p>
                    </div>

                    <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                        <div class="flex flex-col">
                            <label class="{{ $labelClass }}">انتخاب واحد</label>
                            <div
                                class="flex items-center gap-2 p-1.5 rounded-xl bg-gray-100 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                                <button type="button" @click="currency = 'toman'"
                                        :class="currency === 'toman' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                        class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200">
                                    تومان (Toman)
                                </button>
                                <button type="button" @click="currency = 'rial'"
                                        :class="currency === 'rial' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                        class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200">
                                    ریال (Rial)
                                </button>
                            </div>
                            <input type="hidden" name="currency" x-model="currency">
                        </div>

                        <div class="flex flex-col">
                            <div class="{{ $labelClass }} invisible">&nbsp;</div>
                            <div
                                class="flex-1 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700/60 p-4 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span
                                    x-text="currency === 'rial' ? 'تمامی مبالغ در سیستم بر اساس ریال محاسبه خواهند شد.' : 'تمامی مبالغ در سیستم بر اساس تومان محاسبه خواهند شد.'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'print'" x-cloak class="space-y-6">

                {{-- Print Mode --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm-3-9.5a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"/></svg>
                            </span>
                            تنظیمات چاپ
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">قالب پیش‌فرض چاپ فاکتور و پیش
                            فاکتور را انتخاب کنید.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                            <div>
                                <label for="services_print_mode" class="{{ $labelClass }}">حالت چاپ پیش‌فرض</label>
                                <select id="services_print_mode" name="services_print_mode" class="{{ $inputClass }}">
                                    <option value="standard" @selected($v('services_print_mode') == 'standard')>استاندارد
                                    </option>
                                    <option value="official" @selected($v('services_print_mode') == 'official')>رسمی
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="services_official_invoice_orientation" class="{{ $labelClass }}">جهت چاپ فاکتور رسمی</label>
                                <select id="services_official_invoice_orientation" name="services_official_invoice_orientation" class="{{ $inputClass }}">
                                    <option value="portrait" @selected($v('services_official_invoice_orientation', 'portrait') == 'portrait')>عمودی (Portrait)</option>
                                    <option value="landscape" @selected($v('services_official_invoice_orientation') == 'landscape')>افقی (Landscape)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="services_invoice_footer_note" class="{{ $labelClass }}">یادداشت پایین فاکتور
                                (اختیاری)</label>
                            <textarea id="services_invoice_footer_note" name="services_invoice_footer_note" rows="3"
                                      maxlength="1000" class="{{ $inputClass }} resize-none"
                                      placeholder="مثال: کالای فروخته شده پس گرفته نمی‌شود.">{{ $v('services_invoice_footer_note') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Identity Info note for Official Invoice --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 flex items-start justify-between gap-4 flex-wrap bg-linear-to-r from-purple-50/50 to-transparent dark:from-purple-900/10">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                    اطلاعات هویتی فروشنده در فاکتور رسمی
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    اطلاعات هویتی (نام، شماره اقتصادی، شناسه ملی، شماره ثبت، تلفن/نمابر، نشانی و مهر و
                                    امضا) به‌صورت خودکار از ماژول تنظیمات خوانده و در سربرگ فاکتور رسمی چاپ می‌شوند.
                                    هر فیلدی که در ماژول تنظیمات برایش مقداری ثبت نشده باشد، به‌طور خودکار از فاکتور
                                    حذف می‌شود و نیازی به فعال/غیرفعال کردن دستی آن در این صفحه نیست.
                                </p>
                            </div>
                        </div>
                        @if($identityTabRoute)
                            <a href="{{ $identityTabRoute }}" target="_blank"
                               class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                ویرایش در تنظیمات
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Client Fields on Invoice (Moved from its own tab) --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            فیلدهای مشتری در فاکتور
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">
                            مشخص کنید کدام فیلدهای فرم مشتری (سیستمی یا سفارشی) در بخش «اطلاعات خریدار» چاپ فاکتور
                            (رسمی و غیررسمی) و همچنین در صفحه‌ی جزئیات فاکتور نمایش داده شوند.
                        </p>
                    </div>

                    <div class="p-6 md:p-8">
                        @if(empty($clientFormFields))
                            <div
                                class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/30 rounded-2xl p-5 border border-dashed border-gray-200 dark:border-gray-700">
                                هیچ فرم فعالی برای مشتریان پیدا نشد. ابتدا یک فرم در «فرم‌ساز مشتریان» فعال کنید.
                            </div>
                        @else
                            @php
                                $groupedClientFields = collect($clientFormFields)->groupBy('group');
                            @endphp

                            <div class="space-y-7">
                                @foreach($groupedClientFields as $groupName => $fields)
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-3">{{ $groupName }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($fields as $field)
                                                @php
                                                    $selectedClientFields = $selectedClientFields ?? [];
                                                    $checked = in_array($field['id'], $selectedClientFields, true);
                                                @endphp
                                                <label
                                                    class="group relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all
                                                           {{ $checked
                                                              ? 'border-indigo-300 bg-indigo-50/70 dark:border-indigo-500/40 dark:bg-indigo-500/10'
                                                              : 'border-gray-100 bg-gray-50/60 hover:border-indigo-200 hover:bg-white dark:border-gray-700/50 dark:bg-gray-900/30 dark:hover:border-gray-600' }}">
                                                    <input type="checkbox"
                                                           name="services_invoice_client_fields[]"
                                                           value="{{ $field['id'] }}"
                                                           {{ $checked ? 'checked' : '' }}
                                                           class="peer sr-only">

                                                    <span
                                                        class="flex-shrink-0 w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 peer-checked:bg-indigo-600 peer-checked:border-indigo-600">
                                                        <svg
                                                            class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>

                                                    <span class="flex flex-col min-w-0">
                                                        <span
                                                            class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ $field['label'] }}</span>
                                                        <span
                                                            class="text-[10px] text-gray-400 dir-ltr font-mono truncate">{{ $field['id'] }}</span>
                                                    </span>

                                                    @if($field['is_system'])
                                                        <span
                                                            class="ms-auto shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                            سیستمی
                                                        </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'automation'" x-cloak class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Automation --}}
                    <div class="{{ $cardClass }} md:col-span-2">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                نصب گردش کارهای پیش‌فرض
                            </h2>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                با کلیک روی دکمه زیر، تمام گردش کارهای استاندارد و پیش‌فرض خدمات (مانند فعال‌سازی سفارش پس از پرداخت، معلق شدن سفارشات در صورت لغو فاکتور، و تعلیق خودکار ۷ روزه) مجدداً در سیستم نصب و تنظیم می‌شوند. 
                            </p>
                            <button type="button" onclick="document.getElementById('seedWorkflowsForm').submit();" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/20 transition-all text-sm font-bold shadow-sm group">
                                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                اجرا و نصب گردش کارها
                            </button>
                        </div>
                    </div>

                    {{-- Payment Synchronization --}}
                    <div class="{{ $cardClass }} md:col-span-2">
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700/60 bg-linear-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                همگام‌سازی روش‌های پرداخت
                            </h2>
                        </div>
                        <div class="p-6">
                            <label
                                class="flex items-center justify-between gap-6 cursor-pointer group p-5 rounded-2xl border-2 border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/30 hover:border-emerald-200 dark:hover:border-emerald-500/30 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-all">
                                <div class="flex-1">
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">فراخوانی روش‌های پرداخت از تنظیمات سیستم</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 block mt-1.5 leading-relaxed">
                                        با فعال‌سازی این گزینه، درگاه‌های آنلاین، حساب‌های بانکی و دستگاه‌های کارتخوان (POS) که در ماژول تنظیمات اصلی سیستم تعریف شده‌اند، به صورت خودکار در صفحه «ثبت پرداختی» نمایش داده خواهند شد.
                                    </span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="services_use_global_payment_settings" value="0">
                                    <input type="checkbox" id="services_use_global_payment_settings"
                                           name="services_use_global_payment_settings" value="1"
                                           @checked($v('services_use_global_payment_settings') === '1') class="sr-only peer">
                                    <div
                                        class="w-14 h-8 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                                    <div
                                        class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                        <svg
                                            class="w-3.5 h-3.5 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Action Bar (always visible regardless of active tab) --}}
            <div class="sticky bottom-4 z-40 mt-8">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-linear-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-500 hover:to-indigo-600 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات
                    </button>
                    <a href="{{ route('services.invoices.index') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>

        <form id="seedWorkflowsForm" action="{{ route('services.settings.seed-workflows') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                function toPersianNumber(str) {
                    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return str.toString().replace(/\d/g, digit => persianDigits[digit]);
                }

                function toEnglishNumber(str) {
                    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                    return str.toString().replace(/[۰-۹]/g, digit => persianDigits.indexOf(digit)).replace(/[٠-٩]/g, digit => arabicDigits.indexOf(digit));
                }

                function addThousandSeparator(number) {
                    if (!number) return '';
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }

                Alpine.data('priceInput', (initialValue = 0) => ({
                    value: initialValue,
                    displayValue: '',
                    init() {
                        this.value = Number(this.value) || 0;
                        this.formatDisplayValue();
                    },
                    onInput(input) {
                        const numeric = toEnglishNumber(input).replace(/[^\d]/g, '');
                        this.value = numeric ? Number(numeric) : 0;
                        this.displayValue = toPersianNumber(addThousandSeparator(numeric));
                    },
                    formatDisplayValue() {
                        this.displayValue = this.value > 0 ? toPersianNumber(addThousandSeparator(this.value)) : '';
                    }
                }));

                Alpine.data('settingsForm', (prefix, middle, suffix, padding, proforma_prefix, proforma_middle, proforma_suffix, proforma_padding) => ({
                    activeTab: '{{ session('active_tab', old('active_tab')) }}' || localStorage.getItem('servicesSettingsTab') || 'numbering',
                    prefix, middle, suffix,
                    padding: parseInt(padding) || 4,
                    preview: '',
                    proforma_prefix, proforma_middle, proforma_suffix,
                    proforma_padding: parseInt(proforma_padding) || 4,
                    proforma_preview: '',
                    init() {
                        this.updatePreview();
                        this.updateProformaPreview();
                        this.$watch('activeTab', value => localStorage.setItem('servicesSettingsTab', value));
                    },
                    updatePreview() {
                        const num = String(1).padStart(this.padding, '0');
                        this.preview = this.prefix + this.middle + '-' + num + this.suffix;
                    },
                    updateProformaPreview() {
                        const num = String(1).padStart(this.proforma_padding, '0');
                        this.proforma_preview = this.proforma_prefix + this.proforma_middle + '-' + num + this.proforma_suffix;
                    }
                }));
            });
        </script>
    @endpush
@endsection
