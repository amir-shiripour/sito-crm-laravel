@extends('layouts.user')

@section('content')
    @php
        use Illuminate\Support\Js;

        $title = 'ارسال پیامک جدید';

        // آماده‌سازی داده‌ها (با بررسی نال بودن جهت جلوگیری از ارور)
        $users            = $users ?? collect();
        $roles            = $roles ?? collect();
        $clients          = $clients ?? collect();
        $clientStatuses   = $clientStatuses ?? collect();
        $canTargetUsers   = $canTargetUsers ?? true;
        $canTargetClients = $canTargetClients ?? false;

        // مپ کردن آپشن‌ها برای سلکت
        $userSelectOptions = $users->map(function ($u) {
            $labelParts = [$u->name];
            if (!empty($u->phone)) $labelParts[] = $u->phone;
            elseif (!empty($u->email)) $labelParts[] = $u->email;
            return ['value' => (string) $u->id, 'label' => implode(' - ', $labelParts)];
        })->values()->all();

        $roleOptions = $roles->map(fn($r) => ['value' => (string) $r->id, 'label' => $r->name])->values()->all();

        $clientSelectOptions = $clients->map(function ($c) {
            $labelParts = [$c->full_name];
            if (!empty($c->phone)) $labelParts[] = $c->phone;
            return ['value' => (string) $c->id, 'label' => implode(' - ', $labelParts)];
        })->values()->all();

        $clientStatusOptions = $clientStatuses->map(fn($st) => [
            'value' => (string) $st->id,
            'label' => $st->label ?? $st->key,
        ])->values()->all();

        // مقادیر پیش‌فرض
        $defaultTargetType = old('target_type', $canTargetUsers ? 'users' : ($canTargetClients ? 'clients' : 'users'));
        $defaultSmsType = old('type', 'manual');
        $defaultScope   = old('recipient_scope', 'selected');

        // استایل‌های مشترک
        // نکته مهم: overflow-hidden حذف شد تا دراپ‌داون‌ها نمایش داده شوند
        $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    @endphp

    {{-- منطق جاوااسکریپت MultiSelect --}}
    <script>
        function smsMultiSelect(config) {
            return {
                name: config.name,
                options: config.options || [],
                selectedValues: [],
                search: '',
                open: false,

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);
                },

                toggle(value) {
                    value = String(value);
                    if (this.selectedValues.includes(value)) {
                        this.selectedValues = this.selectedValues.filter(v => v !== value);
                    } else {
                        this.selectedValues.push(value);
                    }
                },

                remove(value) {
                    this.selectedValues = this.selectedValues.filter(v => v !== String(value));
                },

                get filteredOptions() {
                    if (!this.search) return this.options;
                    return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                },

                get selectedLabels() {
                    return this.options.filter(o => this.selectedValues.includes(o.value));
                }
            };
        }
    </script>

    <div class="w-full mx-auto space-y-6 pb-20"
         x-data='{
            targetType: "{{ $defaultTargetType }}",
            smsType: "{{ $defaultSmsType }}",
            recipientScope: "{{ $defaultScope }}"
         }'>

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    </span>
                    ارسال پیامک جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تنظیمات ارسال پیامک به کاربران یا مشتریان</p>
            </div>

            <a href="{{ route('user.sms.logs.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                تاریخچه پیامک‌ها
            </a>
        </div>

        {{-- نمایش خطاها --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    خطا در ثبت فرم
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('status'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('user.sms.send.store') }}" class="space-y-6">
            @csrf

            {{-- بخش ۱: انتخاب مخاطب --}}
            {{-- z-index بالا برای اینکه دراپ‌داون‌ها روی بخش‌های پایین بیفتند --}}
            <section class="{{ $cardClass }} p-6 relative z-30">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                    گیرندگان پیام
                </h2>

                <div class="space-y-6">
                    {{-- نوع مخاطب --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($canTargetUsers)
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="target_type" value="users" class="peer sr-only" x-model="targetType">
                                <div class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 transition-all
                                            hover:border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:hover:border-gray-600
                                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-sm
                                            dark:peer-checked:bg-indigo-900/20 dark:peer-checked:border-indigo-500/70">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center dark:bg-indigo-900/40 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">کاربران سیستم</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ارسال به پنل مدیریت/ادمین‌ها</div>
                                        </div>
                                        <div class="mr-auto opacity-0 peer-checked:opacity-100 text-indigo-600 dark:text-indigo-400 transition-opacity">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endif

                        @if($canTargetClients)
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="target_type" value="clients" class="peer sr-only" x-model="targetType">
                                <div class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 transition-all
                                            hover:border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:hover:border-gray-600
                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 peer-checked:shadow-sm
                                            dark:peer-checked:bg-emerald-900/20 dark:peer-checked:border-emerald-500/70">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center dark:bg-emerald-900/40 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">مشتریان</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ارسال به لیست مشتریان</div>
                                        </div>
                                        <div class="mr-auto opacity-0 peer-checked:opacity-100 text-emerald-600 dark:text-emerald-400 transition-opacity">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>

                    {{-- نحوه انتخاب (Scope) --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">نحوه انتخاب گیرندگان</label>
                        <div class="flex flex-col sm:flex-row gap-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="recipient_scope" value="selected" class="peer w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:checked:bg-indigo-500" x-model="recipientScope">
                                <span class="text-sm text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white transition-colors peer-checked:font-bold peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400">
                                    انتخاب دستی از لیست
                                </span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="recipient_scope" value="filters" class="peer w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:checked:bg-indigo-500" x-model="recipientScope">
                                <span class="text-sm text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white transition-colors peer-checked:font-bold peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400">
                                    ارسال گروهی بر اساس فیلتر
                                </span>
                            </label>
                        </div>

                        {{-- پیام راهنما --}}
                        <div class="mt-4 text-[11px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>
                                <span class="font-bold" x-text="targetType === 'users' ? 'کاربران' : 'مشتریان'"></span>
                                <span x-text="recipientScope === 'selected' ? 'انتخاب شده در لیست پایین پیام را دریافت می‌کنند.' : 'که دارای شرایط زیر باشند پیام را دریافت می‌کنند.'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- انتخابگرها (MultiSelects) --}}
                    {{-- z-index 50 برای اطمینان از اینکه لیست روی المان‌های پایین باز شود --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-2 relative z-50">

                        {{-- 1. انتخاب نقش (فیلتر کاربران) --}}
                        <div x-show="targetType === 'users' && recipientScope === 'filters'" class="w-full">
                            <label class="{{ $labelClass }}">نقش‌های کاربری <span class="text-red-500">*</span></label>
                            <div x-data="smsMultiSelect({
                                name: 'user_role_ids[]',
                                options: {{ Js::from($roleOptions) }},
                                oldValues: {{ Js::from(old('user_role_ids', [])) }}
                            })" class="relative">
                                @include('sms::components.inline-multiselect')
                            </div>
                        </div>

                        {{-- 2. انتخاب کاربر (دستی) --}}
                        <div x-show="targetType === 'users' && recipientScope === 'selected'" class="w-full">
                            <label class="{{ $labelClass }}">انتخاب کاربران <span class="text-red-500">*</span></label>
                            <div x-data="smsMultiSelect({
                                name: 'user_ids[]',
                                options: {{ Js::from($userSelectOptions) }},
                                oldValues: {{ Js::from(old('user_ids', [])) }}
                            })" class="relative">
                                @include('sms::components.inline-multiselect')
                            </div>
                        </div>

                        {{-- 3. انتخاب وضعیت (فیلتر مشتریان) --}}
                        <div x-show="targetType === 'clients' && recipientScope === 'filters'" class="w-full">
                            <label class="{{ $labelClass }}">وضعیت‌های مشتری <span class="text-red-500">*</span></label>
                            <div x-data="smsMultiSelect({
                                name: 'client_status_ids[]',
                                options: {{ Js::from($clientStatusOptions) }},
                                oldValues: {{ Js::from(old('client_status_ids', [])) }}
                            })" class="relative">
                                @include('sms::components.inline-multiselect')
                            </div>
                        </div>

                        {{-- 4. انتخاب مشتری (دستی) --}}
                        <div x-show="targetType === 'clients' && recipientScope === 'selected'" class="w-full">
                            <label class="{{ $labelClass }}">انتخاب مشتریان <span class="text-red-500">*</span></label>
                            <div x-data="smsMultiSelect({
                                name: 'client_ids[]',
                                options: {{ Js::from($clientSelectOptions) }},
                                oldValues: {{ Js::from(old('client_ids', [])) }}
                            })" class="relative">
                                @include('sms::components.inline-multiselect')
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- بخش ۲: زمان‌بندی --}}
            <section class="{{ $cardClass }} p-6 relative z-20">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                    زمان ارسال
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $labelClass }}">نوع زمان‌بندی</label>
                        <div class="relative">
                            <select name="type" x-model="smsType" class="{{ $inputClass }} appearance-none">
                                <option value="manual">ارسال آنی (همین الان)</option>
                                <option value="scheduled">زمان‌بندی شده (آینده)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div x-show="smsType === 'scheduled'" x-transition class="relative">
                        <label class="{{ $labelClass }}">تاریخ و ساعت اجرا</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="{{ $inputClass }} dir-ltr text-left [color-scheme:light] dark:[color-scheme:dark]">
                        <div class="absolute top-8 right-3 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                </div>
            </section>

            {{-- بخش ۳: محتوا --}}
            <section class="{{ $cardClass }} p-6 relative z-10">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                    محتوای پیام
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-5">
                        <div>
                            <label class="{{ $labelClass }}">کد پترن (اختیاری)</label>
                            <input type="text" name="pattern" value="{{ old('pattern') }}" placeholder="مثلاً: 18459" class="{{ $inputClass }} dir-ltr text-left w-full md:w-1/2 font-mono">
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">در صورت وارد کردن کد پترن، ارسال از طریق متد Pattern (سرعت بالا) انجام می‌شود.</p>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">متن پیامک</label>
                            <textarea name="body" rows="5" class="{{ $inputClass }} resize-none leading-relaxed" placeholder="متن پیام خود را اینجا بنویسید...">{{ old('body') }}</textarea>
                        </div>
                    </div>

                    {{-- راهنمای متغیرها --}}
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800 h-fit">
                        <h4 class="text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            راهنمای متغیرهای پترن
                        </h4>
                        <p class="text-[11px] text-indigo-600/80 dark:text-indigo-300/70 mb-4 leading-relaxed">
                            سیستم به صورت خودکار مقادیر زیر را در پترن جایگزین می‌کند:
                        </p>
                        <ul class="space-y-2">
                            @foreach([
                                '{0}' => 'نام و نام خانوادگی',
                                '{1}' => 'نام کاربری',
                                '{2}' => 'کد ملی',
                                '{3}' => 'شماره موبایل',
                                '{4}' => 'ایمیل',
                                '{5}' => 'نقش / وضعیت'
                            ] as $key => $val)
                                <li class="flex items-center justify-between text-[11px] border-b border-indigo-100 dark:border-indigo-800/50 pb-1.5 last:border-0 last:pb-0">
                                    <span class="font-mono bg-white dark:bg-gray-800 px-1.5 py-0.5 rounded text-indigo-600 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">{{ $key }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $val }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            {{-- فوتر و دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-6 pb-20">
                <button type="button" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium text-sm hover:bg-gray-50 transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" onclick="window.history.back()">
                    انصراف
                </button>
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all active:scale-95 focus:ring-4 focus:ring-indigo-500/30">
                    ارسال پیامک
                </button>
            </div>
        </form>
    </div>
@endsection
