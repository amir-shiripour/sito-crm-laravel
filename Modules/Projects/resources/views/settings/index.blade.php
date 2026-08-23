@extends('layouts.user')
@section('title', 'تنظیمات پروژه‌ها')

@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2";
    $cardClass  = "bg-white dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden";
    $v = fn(string $key, $default='') => (!isset($raw[$key]) || $raw[$key] === '' || $raw[$key] === null) ? $default : $raw[$key];

    $tabs = [
        'numbering'   => ['label' => 'شماره‌گذاری و کدینگ', 'icon' => 'hash'],
        'general'     => ['label' => 'عمومی و پیش‌فرض‌ها', 'icon' => 'sliders'],
        'roles'       => ['label' => 'نقش‌ها و دسترسی‌ها', 'icon' => 'shield'],
        'documents'   => ['label' => 'اسناد و فایل‌ها', 'icon' => 'document']
            ];

    $initialTab = session('active_tab', 'numbering');
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

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
                        پروژه‌ها</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیکربندی الگوهای کدگذاری، پیش‌فرض‌ها، گردش
                        کار وظایف و کنترل اسناد پروژه.</p>
                </div>
            </div>
            <a href="{{ route('projects.projects.index') }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all flex items-center gap-2 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به پروژه‌ها
            </a>
        </div>

        {{-- Success / Error Alerts --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-800/50 text-rose-700 dark:text-rose-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div
                class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-800/50 text-rose-700 dark:text-rose-400 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('projects.settings.update') }}" novalidate
              x-data="projectsSettingsForm(
                  '{{ $initialTab }}',
                  '{{ $v('projects_code_prefix', 'PRJ-') }}',
                  '{{ $v('projects_code_middle', date('Y')) }}',
                  '{{ $v('projects_code_suffix', '') }}',
                  {{ (int)$v('projects_code_padding', 4) }},
                  '{{ $v('projects_document_allowed_extensions', 'pdf,doc,docx,xls,xlsx,zip,rar,png,jpg,jpeg,webp,txt') }}',
                  '{{ $v('projects_document_categories', 'قراردادها,طراحی و UI/UX,مستندات فنی,صورت‌جلسات,خروجی نهایی') }}'
              )">
            @csrf @method('PUT')

            {{-- Tab Navigation --}}
            <input type="hidden" name="active_tab" x-model="activeTab">
            <div class="sticky top-4 z-30 mb-6">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-2 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm flex flex-wrap items-center gap-1.5">
                    @foreach($tabs as $tabKey => $tab)
                        <button type="button" @click="activeTab = '{{ $tabKey }}'"
                                :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="flex-1 min-w-36 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200">
                            @switch($tab['icon'])
                                @case('hash')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                    @break
                                @case('sliders')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    @break
                                @case('check-circle')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @case('shield')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    @break
                                @case('document')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
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

            {{-- TAB 1: Numbering & Code Scheme --}}
            <div x-show="activeTab === 'numbering'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </span>
                            الگوی تولید کد پروژه (Project Code Scheme)
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">فرمت و پیشوند خودکار برای کدهای
                            شناسه یکتا در پروژه‌های جدید را تعیین نمایید.</p>
                    </div>

                    <div class="p-6 md:p-8 space-y-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">پیشوند (Prefix)</label>
                                <input type="text" name="projects_code_prefix" x-model="prefix" @input="updatePreview()"
                                       value="{{ $v('projects_code_prefix', 'PRJ-') }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono" placeholder="PRJ-">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">میانی (مثلاً سال)</label>
                                <input type="text" name="projects_code_middle" x-model="middle" @input="updatePreview()"
                                       value="{{ $v('projects_code_middle', date('Y')) }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono"
                                       placeholder="{{ date('Y') }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">پسوند (Suffix)</label>
                                <input type="text" name="projects_code_suffix" x-model="suffix" @input="updatePreview()"
                                       value="{{ $v('projects_code_suffix', '') }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono" placeholder="-CRM">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">تعداد ارقام سریال (Padding)</label>
                                <input type="number" name="projects_code_padding" x-model.number="padding"
                                       @input="updatePreview()" min="1" max="10"
                                       value="{{ $v('projects_code_padding', 4) }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono">
                            </div>
                        </div>

                        {{-- Live Preview --}}
                        <div
                            class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/10 border border-indigo-100 dark:border-indigo-500/20 flex flex-col items-center justify-center text-center">
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-indigo-400 dark:text-indigo-500 mb-2">پیش‌نمایش زنده نمونه کد پروژه</span>
                            <span
                                class="font-mono text-3xl font-black text-indigo-600 dark:text-indigo-300 tracking-wider dir-ltr"
                                x-text="preview"></span>
                        </div>

                        {{-- Auto-code Toggle --}}
                        <div class="border-t border-gray-100 dark:border-gray-700/60 pt-6">
                            <label for="projects_code_auto"
                                   class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                                <div class="flex-1">
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">تولید و قفل خودکار کد پروژه</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">در صورت فعال بودن، در فرم تعریف پروژه، کد پروژه طبق این فرمت به‌صورت خودکار ایجاد و نمایش داده می‌شود.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="projects_code_auto" value="0">
                                    <input type="checkbox" id="projects_code_auto" name="projects_code_auto" value="1"
                                           @checked($v('projects_code_auto', '1') === '1') class="sr-only peer">
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
            </div>

            {{-- TAB 2: General & Project Defaults --}}
            <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/40 to-transparent dark:from-indigo-900/10">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </span>
                            مقادیر اولیه و پیش‌فرض‌های پروژه
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">دسته‌بندی، وضعیت و نقش‌های
                            پیش‌فرضی که در زمان ثبت پروژه جدید انتخاب می‌شوند.</p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Default Category --}}
                            <div>
                                <label for="projects_default_category_id" class="{{ $labelClass }}">دسته‌بندی
                                    پیش‌فرض</label>
                                <select id="projects_default_category_id" name="projects_default_category_id"
                                        class="{{ $inputClass }}">
                                    <option value="">انتخاب نشده (اجبار به انتخاب توسط کاربر)</option>
                                    @foreach($categories as $cat)
                                        <option
                                            value="{{ $cat->id }}" @selected($v('projects_default_category_id') == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Default Status --}}
                            <div>
                                <label for="projects_default_status_id" class="{{ $labelClass }}">وضعیت اولیه
                                    پیش‌فرض</label>
                                <select id="projects_default_status_id" name="projects_default_status_id"
                                        class="{{ $inputClass }}">
                                    <option value="">پیش‌فرض سیستم (اولین وضعیت تعریف‌شده)</option>
                                    @foreach($projectStatuses as $st)
                                        <option
                                            value="{{ $st->id }}" @selected($v('projects_default_status_id') == $st->id)>
                                             {{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Toggles Section --}}
                        <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            {{-- Auto Assign Creator --}}
                            <label
                                class="flex items-center justify-between gap-4 cursor-pointer p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 hover:bg-gray-100/70 transition-all">
                                <div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 block">افزودن خودکار سازنده به تیم پروژه</span>
                                    <span class="text-xs text-gray-400 block mt-0.5">کاربری که پروژه را ایجاد می‌کند، به‌صورت خودکار در لیست اعضای تیم ثبت شود.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="projects_auto_assign_creator" value="0">
                                    <input type="checkbox" name="projects_auto_assign_creator" value="1"
                                           @checked($v('projects_auto_assign_creator', '1') === '1') class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
                                    <div
                                        class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:-translate-x-5"></div>
                                </div>
                            </label>

                            {{-- Require Client --}}
                            <label
                                class="flex items-center justify-between gap-4 cursor-pointer p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 hover:bg-gray-100/70 transition-all">
                                <div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 block">اجباری بودن انتخاب مشتری</span>
                                    <span class="text-xs text-gray-400 block mt-0.5">در زمان تعریف پروژه، انتخاب یک مشتری از لیست مشتریان سیستم اجباری باشد.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="projects_require_client" value="0">
                                    <input type="checkbox" name="projects_require_client" value="1"
                                           @checked($v('projects_require_client', '0') === '1') class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
                                    <div
                                        class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:-translate-x-5"></div>
                                </div>
                            </label>

                            {{-- Require Dates --}}
                            <label
                                class="flex items-center justify-between gap-4 cursor-pointer p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 hover:bg-gray-100/70 transition-all">
                                <div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 block">اجباری بودن تاریخ شروع و سررسید</span>
                                    <span class="text-xs text-gray-400 block mt-0.5">تکمیل فیلدهای تاریخ شروع و پایان پروژه در فرم ایجاد الزامی شود.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="projects_require_dates" value="0">
                                    <input type="checkbox" name="projects_require_dates" value="1"
                                           @checked($v('projects_require_dates', '0') === '1') class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
                                    <div
                                        class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:-translate-x-5"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: Roles & Permissions (نقش‌ها و دسترسی‌ها) --}}
            <div x-show="activeTab === 'roles'" x-cloak class="space-y-6">

                {{-- Default Role Assignments Box --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-linear-to-r from-indigo-50/40 to-transparent dark:from-indigo-900/10">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </span>
                            نقش‌های پیش‌فرض پروژه
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">تعیین نقش‌های اولیه برای ایجادکننده و اعضای جدید پروژه.</p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Creator Default Role --}}
                            <div>
                                <label for="projects_default_creator_role_tab" class="{{ $labelClass }}">نقش پیش‌فرض ایجادکننده پروژه</label>
                                <select id="projects_default_creator_role_tab" name="projects_default_creator_role" class="{{ $inputClass }}">
                                    @foreach($projectRoles as $r)
                                        <option value="{{ $r->name }}" @selected($v('projects_default_creator_role', 'manager') === $r->name)>
                                            {{ $r->display_name }} ({{ ucfirst($r->name) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1.5">نقشی که به صورت خودکار به کاربر سازنده پروژه اختصاص داده می‌شود.</p>
                            </div>

                            {{-- New Member Default Role --}}
                            <div>
                                <label for="projects_default_member_role_tab" class="{{ $labelClass }}">نقش پیش‌فرض اعضای جدید</label>
                                <select id="projects_default_member_role_tab" name="projects_default_member_role" class="{{ $inputClass }}">
                                    @foreach($projectRoles as $r)
                                        <option value="{{ $r->name }}" @selected($v('projects_default_member_role', 'viewer') === $r->name)>
                                            {{ $r->display_name }} ({{ ucfirst($r->name) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1.5">نقش اولیه هنگام افزودن عضو جدید به تیم پروژه.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Roles & Granular Permissions List Section --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-linear-to-r from-purple-50/40 to-indigo-50/20 dark:from-purple-900/10 dark:to-transparent flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </span>
                                مدیریت نقش‌ها و ماتریس دسترسی‌ها (Project Roles & Permissions)
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">تعریف نقش‌های سفارشی نامحدود، تعیین آیکون و رنگ اختصاصی و شخصی‌سازی دقیق دسترسی‌های مجاز در سطح پروژه.</p>
                        </div>

                        <button type="button"
                                @click="$dispatch('open-role-create')"
                                class="px-5 py-2.5 rounded-xl bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 self-start sm:self-center shrink-0 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            تعریف نقش جدید
                        </button>
                    </div>

                    <div class="p-6 md:p-8">
                        {{-- Total permissions count --}}
                        @php
                            $totalPermissionsCount = 0;
                            foreach($availablePermissions as $cat) {
                                $totalPermissionsCount += count($cat['items']);
                            }
                        @endphp

                        {{-- Roles Grid Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projectRoles as $role)
                                @php
                                    $cClasses = $role->colorClasses();
                                    $rolePerms = $role->permissions ?? [];
                                    $permCount = count($rolePerms);
                                    $permPercentage = $totalPermissionsCount > 0 ? round(($permCount / $totalPermissionsCount) * 100) : 0;
                                @endphp

                                <div class="rounded-3xl border border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800/60 hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden group">
                                    {{-- Role Card Header --}}
                                    <div class="p-5 space-y-4">
                                        <div class="flex items-start justify-between gap-3">
                                            {{-- Icon & Titles --}}
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-sm {{ $cClasses['btn'] }}">
                                                    @switch($role->icon)
                                                        @case('crown')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18h18M4 14l3-7 5 5 5-5 3 7H4z"/></svg>
                                                            @break
                                                        @case('pencil')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                            @break
                                                        @case('eye')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            @break
                                                        @case('code')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                                            @break
                                                        @case('palette')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 4 4 0 014-4h2a2 2 0 002-2V9a2 2 0 012-2h1a5 5 0 015 5v1a7 7 0 01-7 7H7z"/></svg>
                                                            @break
                                                        @case('bug')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM5 11l-3 1m19-1l3 1m-4 5l3 2M3 18l3-2m2-5h8m-8 4h8m-6 4h4a3 3 0 003-3v-4H8v4a3 3 0 003 3z"/></svg>
                                                            @break
                                                        @case('shield')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                            @break
                                                        @case('briefcase')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                            @break
                                                        @case('user')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                            @break
                                                        @case('star')
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                            @break
                                                        @default
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                    @endswitch
                                                </span>
                                                <div class="min-w-0">
                                                    <h3 class="text-sm font-black text-gray-900 dark:text-white truncate">{{ $role->display_name }}</h3>
                                                    <span class="text-[11px] font-mono text-gray-400 block mt-0.5">{{ $role->name }}</span>
                                                </div>
                                            </div>

                                            {{-- System vs Custom Badge --}}
                                            @if($role->is_system)
                                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700/60 dark:text-gray-300 shrink-0 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    سیستمی
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 shrink-0">
                                                    سفارشی
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Description --}}
                                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 min-h-8 leading-relaxed">
                                            {{ $role->description ?: 'بدون توضیحات ثبت‌شده.' }}
                                        </p>

                                        {{-- Coverage Progress Bar --}}
                                        <div class="space-y-1.5 pt-1">
                                            <div class="flex items-center justify-between text-[11px] font-bold text-gray-500 dark:text-gray-400">
                                                <span>پوشش دسترسی‌ها:</span>
                                                <span class="font-mono">{{ $permCount }} از {{ $totalPermissionsCount }} ({{ $permPercentage }}%)</span>
                                            </div>
                                            <div class="w-full bg-gray-100 dark:bg-gray-700/60 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full {{ $cClasses['btn'] }} transition-all duration-500" style="width: {{ $permPercentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Role Card Footer Actions --}}
                                    <div class="p-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between gap-2">
                                        <button type="button"
                                                @click='$dispatch("open-role-edit", {{ json_encode($role, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                                class="flex-1 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            ویرایش و دسترسی‌ها
                                        </button>

                                        @if(!$role->is_system)
                                            <button type="button"
                                                    onclick="deleteProjectRole({{ $role->id }}, '{{ $role->display_name }}')"
                                                    class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all cursor-pointer"
                                                    title="حذف نقش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- TAB 5: Documents & Files --}}
            <div x-show="activeTab === 'documents'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-blue-50/50 via-indigo-50/20 to-transparent dark:from-blue-950/20 dark:via-indigo-950/10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <span
                                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-xs shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">تنظیمات اسناد، فرمت‌ها و سهمیه ذخیره‌سازی</h2>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت فرمت‌های مجاز آپلود، محدودیت‌های حجم و دسته‌بندی‌های پیش‌فرض اسناد پروژه.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-auto">
                                <span
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-bold text-xs border border-blue-200/60 dark:border-blue-800/40">
                                    <span x-text="selectedExtensions.length"></span> فرمت مجاز
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-8">

                        {{-- 1. ADVANCED MULTI-SELECT EXTENSIONS WITH SVG ICONS --}}
                        <div class="space-y-4">
                            <div
                                class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        فرمت‌ها و پسوندهای مجاز آپلود (Multi-Select)
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">پسوندهای مجاز را با کلیک روی دسته‌ها یا گزینه‌ها به راحتی انتخاب یا سفارشی‌سازی کنید.</p>
                                </div>

                                {{-- Preset Actions --}}
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button type="button" @click="applyPreset('recommended')"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 hover:bg-indigo-100 transition-all border border-indigo-100 dark:border-indigo-800/50 cursor-pointer">
                                        پیش‌فرض پیشنهادی
                                    </button>
                                    <button type="button" @click="applyPreset('docs_images')"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 hover:bg-emerald-100 transition-all border border-emerald-100 dark:border-emerald-800/50 cursor-pointer">
                                        فقط اسناد و عکس‌ها
                                    </button>
                                    <button type="button" @click="applyPreset('all')"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 transition-all cursor-pointer">
                                        انتخاب همه
                                    </button>
                                    <button type="button" @click="applyPreset('clear')"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 hover:bg-rose-100 transition-all cursor-pointer">
                                        پاک‌سازی
                                    </button>
                                </div>
                            </div>

                            {{-- Search and Quick Add Bar --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="relative md:col-span-2">
                                    <input type="text" x-model="extSearch"
                                           placeholder="جستجوی سریع فرمت (مثلاً: pdf, figma, mp4, zip)..."
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2 pl-9 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white transition-all">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="customExtInput"
                                           @keydown.enter.prevent="addCustomExtension()"
                                           placeholder="افزودن فرمت دلخواه (مثلاً: webp)..."
                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white transition-all">
                                    <button type="button" @click="addCustomExtension()"
                                            class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shrink-0 transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>افزودن</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Selected Formats Cloud --}}
                            <div
                                class="p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">
                                        فرمت‌های انتخاب‌شده فعلی (<span x-text="selectedExtensions.length"></span> مورد):
                                    </span>
                                    <span x-show="selectedExtensions.length === 0" class="text-rose-500 font-bold">هیچ فرمتی انتخاب نشده است (کاربران قادر به آپلود نخواهند بود)</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto pr-1">
                                    <template x-for="ext in selectedExtensions" :key="'sel-' + ext">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/60 shadow-2xs transition-all">
                                            <span x-text="'.' + ext"></span>
                                            <button type="button" @click="removeExtension(ext)"
                                                    class="text-blue-400 hover:text-blue-700 dark:hover:text-blue-200 mr-0.5 cursor-pointer"
                                                    title="حذف">×</button>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Categorized Grid Multi-Select --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 pt-2">
                                <template x-for="cat in filteredCategories" :key="cat.id">
                                    <div
                                        class="p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/50 space-y-3 shadow-2xs hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                                        <div
                                            class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-700/50 pb-2.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                {{-- Category SVG Icons --}}
                                                <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                                                      :class="{
                                                          'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400': cat.id === 'docs',
                                                          'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400': cat.id === 'images',
                                                          'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': cat.id === 'archives',
                                                          'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': cat.id === 'code',
                                                          'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400': cat.id === 'media',
                                                          'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400': cat.id === 'cad'
                                                      }">
                                                    <template x-if="cat.id === 'docs'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="cat.id === 'images'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="cat.id === 'archives'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="cat.id === 'code'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="cat.id === 'media'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="cat.id === 'cad'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                                                        </svg>
                                                    </template>
                                                </span>
                                                <h4 class="font-bold text-xs text-gray-800 dark:text-gray-200 truncate"
                                                    x-text="cat.name"></h4>
                                            </div>
                                            <button type="button" @click="toggleCategory(cat)"
                                                    class="text-[11px] font-bold px-2 py-0.5 rounded-md transition-all cursor-pointer"
                                                    :class="isCategoryFullySelected(cat) ? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100'">
                                                <span
                                                    x-text="isCategoryFullySelected(cat) ? 'لغو این دسته' : 'انتخاب همه'"></span>
                                            </button>
                                        </div>

                                        {{-- Extension Pills --}}
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="ext in cat.extensions" :key="cat.id + '-' + ext">
                                                <button type="button" @click="toggleExtension(ext)"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition-all flex items-center gap-1 cursor-pointer border"
                                                        :class="isExtSelected(ext)
                                                            ? 'bg-blue-600 text-white border-blue-600 shadow-2xs'
                                                            : 'bg-gray-50 dark:bg-gray-700/40 text-gray-600 dark:text-gray-300 border-gray-200/80 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                                    <span x-show="isExtSelected(ext)" class="text-[10px]">✓</span>
                                                    <span x-text="ext"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Hidden input for Form Submission --}}
                            <input type="hidden" name="projects_document_allowed_extensions"
                                   :value="selectedExtensions.join(',')">
                        </div>

                        {{-- 2. STORAGE LIMITS & QUOTAS (CLEAN AND SPACED INPUTS) --}}
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                                سهمیه فضا و محدودیت‌های حجم ذخیره‌سازی
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                {{-- Max Size MB --}}
                                <div>
                                    <label for="projects_document_max_size_mb" class="{{ $labelClass }}">حداکثر حجم هر فایل</label>
                                    <div class="relative flex items-center mt-1">
                                        <input type="number" id="projects_document_max_size_mb"
                                               name="projects_document_max_size_mb" min="1" max="500"
                                               value="{{ $v('projects_document_max_size_mb', 20) }}"
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 pl-14 pr-4 py-2.5 text-xs text-left font-mono dir-ltr focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                                        <span class="absolute left-2.5 top-2 px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-700 text-[11px] font-bold font-mono text-gray-600 dark:text-gray-300 pointer-events-none">MB</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1.5">حداکثر سایز مجاز برای هر فایل تکی (پیش‌فرض: 20 مگابایت).</p>
                                </div>

                                {{-- Project Quota MB --}}
                                <div>
                                    <label for="projects_document_project_quota_mb" class="{{ $labelClass }}">سقف فضای اسناد هر پروژه</label>
                                    <div class="relative flex items-center mt-1">
                                        <input type="number" id="projects_document_project_quota_mb"
                                               name="projects_document_project_quota_mb" min="10" max="10000"
                                               value="{{ $v('projects_document_project_quota_mb', 500) }}"
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 pl-14 pr-4 py-2.5 text-xs text-left font-mono dir-ltr focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                                        <span class="absolute left-2.5 top-2 px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-700 text-[11px] font-bold font-mono text-gray-600 dark:text-gray-300 pointer-events-none">MB</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1.5">سقف کل فایل‌های ذخیره شده در هر پروژه (پیش‌فرض: 500MB).</p>
                                </div>

                                {{-- Max Files Count --}}
                                <div>
                                    <label for="projects_document_max_files_count" class="{{ $labelClass }}">حداکثر تعداد اسناد در هر پروژه</label>
                                    <div class="relative flex items-center mt-1">
                                        <input type="number" id="projects_document_max_files_count"
                                               name="projects_document_max_files_count" min="1" max="500"
                                               value="{{ $v('projects_document_max_files_count', 50) }}"
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 pl-16 pr-4 py-2.5 text-xs text-left font-mono dir-ltr focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                                        <span class="absolute left-2.5 top-2 px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-700 text-[11px] font-bold text-gray-600 dark:text-gray-300 pointer-events-none">فایل</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1.5">حداکثر تعداد پیوست‌های ثبت‌شده در پروژه (پیش‌فرض: 50 عدد).</p>
                                </div>
                            </div>
                        </div>

                        {{-- 3. DEFAULT DOCUMENT CATEGORIES / FOLDERS --}}
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                دسته‌بندی‌ها و گروه‌های پیش‌فرض اسناد پروژه
                            </h3>
                            <p class="text-xs text-gray-400">عناوین دسته‌بندی موضوعی که هنگام ثبت یا فیلتر اسناد پروژه در دسترس خواهند بود.</p>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50 space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(catName, cIdx) in docCategories" :key="'cat-' + cIdx">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span x-text="catName"></span>
                                            <button type="button" @click="removeCategory(cIdx)"
                                                    class="text-gray-400 hover:text-rose-600 transition-colors mr-1 cursor-pointer">×</button>
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2 pt-1 max-w-md">
                                    <input type="text" x-model="newCategoryInput" @keydown.enter.prevent="addCategory()"
                                           placeholder="افزودن دسته‌بندی جدید (مثلاً: پروپوزال، صورت‌جلسه)..."
                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:text-white transition-all">
                                    <button type="button" @click="addCategory()"
                                            class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shrink-0 transition-all shadow-xs cursor-pointer flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>افزودن دسته</span>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="projects_document_categories" :value="docCategories.join(',')">
                        </div>

                    </div>
                </div>
            </div>
            {{-- Sticky Action Bar --}}
            <div class="sticky bottom-4 z-40 mt-8">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.4)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-linear-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-500 hover:to-indigo-600 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات
                    </button>
                    <a href="{{ route('projects.projects.index') }}"
                       class="px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>

        <form id="seedStatusesForm" action="{{ route('projects.settings.seed-statuses') }}" method="POST"
              class="hidden">
            @csrf
        </form>

        {{-- Include Project Role Create & Edit Modal --}}
        @include('projects::settings.partials.modal-role', ['availablePermissions' => $availablePermissions])
    </div>

    @push('scripts')
        <script>
            function projectsSettingsForm(initialTab, initialPrefix, initialMiddle, initialSuffix, initialPadding, initialAllowedExts, initialDocCategories) {
                const parseExts = (str) => {
                    if (!str) return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'webp', 'txt'];
                    return str.split(',').map(s => s.trim().toLowerCase().replace('.', '')).filter(Boolean);
                };

                const parseCategories = (str) => {
                    if (!str) return ['قراردادها', 'طراحی و UI/UX', 'مستندات فنی', 'صورت‌جلسات', 'خروجی نهایی'];
                    return str.split(',').map(s => s.trim()).filter(Boolean);
                };

                return {
                    activeTab: initialTab || 'numbering',
                    prefix: initialPrefix,
                    middle: initialMiddle,
                    suffix: initialSuffix,
                    padding: Math.max(1, parseInt(initialPadding) || 4),
                    preview: '',

                    // Multi-select Extensions
                    extSearch: '',
                    customExtInput: '',
                    selectedExtensions: parseExts(initialAllowedExts),
                    customExtensions: [],

                    extensionCategories: [
                        {
                            id: 'docs',
                            name: 'اسناد و متنی',
                            extensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt']
                        },
                        {
                            id: 'images',
                            name: 'تصاویر و گرافیک',
                            extensions: ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'bmp', 'ico', 'psd', 'ai', 'eps', 'fig']
                        },
                        {
                            id: 'archives',
                            name: 'فشرده و آرشیو',
                            extensions: ['zip', 'rar', '7z', 'tar', 'gz', 'bz2']
                        },
                        {
                            id: 'code',
                            name: 'کد و دیتا',
                            extensions: ['json', 'xml', 'sql', 'log', 'md', 'html', 'css', 'js', 'py', 'php', 'yaml']
                        },
                        {
                            id: 'media',
                            name: 'صوت و ویدیو',
                            extensions: ['mp4', 'mov', 'avi', 'mkv', 'mp3', 'wav', 'm4a', 'ogg']
                        },
                        {
                            id: 'cad',
                            name: 'مهندسی و ۳D',
                            extensions: ['dwg', 'dxf', 'skp', 'obj', 'stl', 'blend']
                        }
                    ],

                    // Document Categories
                    docCategories: parseCategories(initialDocCategories),
                    newCategoryInput: '',

                    init() {
                        this.updatePreview();
                        const allStandard = this.extensionCategories.flatMap(c => c.extensions);
                        this.selectedExtensions.forEach(ext => {
                            if (!allStandard.includes(ext) && !this.customExtensions.includes(ext)) {
                                this.customExtensions.push(ext);
                            }
                        });
                    },

                    updatePreview() {
                        const count = 1;
                        const pad = Math.max(1, parseInt(this.padding) || 4);
                        const paddedNumber = String(count).padStart(pad, '0');
                        const middlePart = this.middle ? this.middle + '-' : '';
                        this.preview = (this.prefix || '') + middlePart + paddedNumber + (this.suffix || '');
                    },

                    isExtSelected(ext) {
                        return this.selectedExtensions.includes(ext);
                    },

                    toggleExtension(ext) {
                        ext = ext.toLowerCase().trim();
                        if (this.selectedExtensions.includes(ext)) {
                            this.selectedExtensions = this.selectedExtensions.filter(e => e !== ext);
                        } else {
                            this.selectedExtensions.push(ext);
                        }
                    },

                    removeExtension(ext) {
                        this.selectedExtensions = this.selectedExtensions.filter(e => e !== ext);
                    },

                    isCategoryFullySelected(cat) {
                        return cat.extensions.every(e => this.selectedExtensions.includes(e));
                    },

                    toggleCategory(cat) {
                        if (this.isCategoryFullySelected(cat)) {
                            this.selectedExtensions = this.selectedExtensions.filter(e => !cat.extensions.includes(e));
                        } else {
                            cat.extensions.forEach(e => {
                                if (!this.selectedExtensions.includes(e)) {
                                    this.selectedExtensions.push(e);
                                }
                            });
                        }
                    },

                    addCustomExtension() {
                        let val = (this.customExtInput || '').toLowerCase().replace(/[^a-z0-9]/g, '').trim();
                        if (val && !this.selectedExtensions.includes(val)) {
                            this.selectedExtensions.push(val);
                            if (!this.customExtensions.includes(val)) {
                                this.customExtensions.push(val);
                            }
                        }
                        this.customExtInput = '';
                    },

                    applyPreset(preset) {
                        if (preset === 'recommended') {
                            this.selectedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'webp', 'txt'];
                        } else if (preset === 'docs_images') {
                            const docs = this.extensionCategories.find(c => c.id === 'docs')?.extensions || [];
                            const images = this.extensionCategories.find(c => c.id === 'images')?.extensions || [];
                            this.selectedExtensions = [...docs, ...images];
                        } else if (preset === 'all') {
                            this.selectedExtensions = [...new Set([...this.extensionCategories.flatMap(c => c.extensions), ...this.customExtensions])];
                        } else if (preset === 'clear') {
                            this.selectedExtensions = [];
                        }
                    },

                    get filteredCategories() {
                        if (!this.extSearch.trim()) return this.extensionCategories;
                        const query = this.extSearch.toLowerCase().trim();
                        return this.extensionCategories.map(cat => {
                            const filteredExts = cat.extensions.filter(e => e.includes(query) || cat.name.includes(query));
                            return { ...cat, extensions: filteredExts };
                        }).filter(cat => cat.extensions.length > 0);
                    },

                    addCategory() {
                        let val = (this.newCategoryInput || '').trim();
                        if (val && !this.docCategories.includes(val)) {
                            this.docCategories.push(val);
                        }
                        this.newCategoryInput = '';
                    },

                    removeCategory(idx) {
                        this.docCategories.splice(idx, 1);
                    }
                }
            }
        </script>
    @endpush
@endsection
