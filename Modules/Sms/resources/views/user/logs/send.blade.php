@extends('layouts.user')

@section('content')
    @php
        use Illuminate\Support\Js;

        $title = 'ارسال پیامک جدید';

        // آماده‌سازی داده‌ها
        $users            = $users ?? collect();
        $roles            = $roles ?? collect();
        $clients          = $clients ?? collect();
        $clientStatuses   = $clientStatuses ?? collect();
        $canTargetUsers   = $canTargetUsers ?? true;
        $canTargetClients = $canTargetClients ?? false;
        $sampleUserData   = $sampleUserData ?? [
            'name'          => 'علی محمدی',
            'username'      => 'alimohammadi',
            'national_code' => '0012345678',
            'phone'         => '09123456789',
            'email'         => 'ali@example.com',
            'role'          => 'مدیر سیستم',
        ];
        $sampleClientData = $sampleClientData ?? [
            'full_name'     => 'رضا رضایی',
            'username'      => 'reza_rezaei',
            'national_code' => '0087654321',
            'phone'         => '09351234567',
            'email'         => 'reza@example.com',
            'status'        => 'مشتری فعال',
            'case_number'   => 'CR-10492',
        ];

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
        $defaultSmsType    = old('type', 'manual');
        $defaultScope      = old('recipient_scope', 'selected');
        $oldPattern        = old('pattern', '');
        $oldBody           = old('body', '');

        // استایل‌های مشترک با پشتیبانی کامل از تم روشن و تاریک
        $cardClass  = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-200 mb-2";
        $inputClass = "w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 dark:focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/20 px-4 py-2.5 text-sm transition-all";
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

        function smsSendApp(config) {
            return {
                targetType: config.defaultTargetType || 'users',
                smsType: config.defaultSmsType || 'manual',
                recipientScope: config.defaultScope || 'selected',
                pattern: config.oldPattern || '',
                body: config.oldBody || '',
                sampleUserData: config.sampleUserData || {},
                sampleClientData: config.sampleClientData || {},
                activeGuideTab: 'preview',
                feedbackMessage: '',

                get currentSample() {
                    return this.targetType === 'users' ? this.sampleUserData : this.sampleClientData;
                },

                get sampleRecipientName() {
                    const sample = this.currentSample;
                    return this.targetType === 'users' ? (sample.name || 'علی محمدی') : (sample.full_name || 'رضا رضایی');
                },

                get sampleRecipientPhone() {
                    const sample = this.currentSample;
                    return sample.phone || '09123456789';
                },

                get availableVariables() {
                    if (this.targetType === 'users') {
                        return [
                            { tag: '{نام}', patternTag: '{0}', key: '{name}', label: 'نام کامل', sample: this.sampleUserData.name || 'علی محمدی', desc: 'نام و نام خانوادگی کاربر' },
                            { tag: '{نام_کاربری}', patternTag: '{1}', key: '{username}', label: 'نام کاربری', sample: this.sampleUserData.username || 'alimohammadi', desc: 'نام کاربری ورود به سیستم' },
                            { tag: '{کد_ملی}', patternTag: '{2}', key: '{national_code}', label: 'کد ملی', sample: this.sampleUserData.national_code || '0012345678', desc: 'کد ملی ثبت شده کاربر' },
                            { tag: '{شماره_موبایل}', patternTag: '{3}', key: '{phone}', label: 'شماره همراه', sample: this.sampleUserData.phone || '09123456789', desc: 'شماره موبایل دریافت‌کننده' },
                            { tag: '{ایمیل}', patternTag: '{4}', key: '{email}', label: 'ایمیل', sample: this.sampleUserData.email || 'ali@example.com', desc: 'آدرس پست الکترونیکی' },
                            { tag: '{نقش}', patternTag: '{5}', key: '{role}', label: 'نقش کاربری', sample: this.sampleUserData.role || 'مدیر سیستم', desc: 'عناوین نقش‌های فعال کاربر' },
                        ];
                    } else {
                        return [
                            { tag: '{نام}', patternTag: '{0}', key: '{full_name}', label: 'نام مشتری', sample: this.sampleClientData.full_name || 'رضا رضایی', desc: 'نام کامل مشتری' },
                            { tag: '{نام_کاربری}', patternTag: '{1}', key: '{username}', label: 'نام کاربری', sample: this.sampleClientData.username || 'reza_rezaei', desc: 'نام کاربری پرتال مشتری' },
                            { tag: '{کد_ملی}', patternTag: '{2}', key: '{national_code}', label: 'کد ملی', sample: this.sampleClientData.national_code || '0087654321', desc: 'کد ملی مشتری' },
                            { tag: '{شماره_موبایل}', patternTag: '{3}', key: '{phone}', label: 'شماره همراه', sample: this.sampleClientData.phone || '09351234567', desc: 'شماره موبایل مشتری' },
                            { tag: '{ایمیل}', patternTag: '{4}', key: '{email}', label: 'ایمیل', sample: this.sampleClientData.email || 'reza@example.com', desc: 'آدرس ایمیل مشتری' },
                            { tag: '{وضعیت}', patternTag: '{5}', key: '{status}', label: 'وضعیت مشتری', sample: this.sampleClientData.status || 'مشتری فعال', desc: 'برچسب وضعیت فعلی مشتری' },
                            { tag: '{شماره_پرونده}', patternTag: '-', key: '{case_number}', label: 'شماره پرونده', sample: this.sampleClientData.case_number || 'CR-10492', desc: 'شماره پرونده اختصاصی مشتری' },
                        ];
                    }
                },

                insertVariable(tag) {
                    const textarea = this.$refs.bodyTextarea;
                    if (!textarea) return;

                    const start = textarea.selectionStart ?? 0;
                    const end = textarea.selectionEnd ?? 0;
                    const text = this.body || '';

                    this.body = text.substring(0, start) + tag + text.substring(end);

                    this.$nextTick(() => {
                        textarea.focus();
                        const newPos = start + tag.length;
                        textarea.setSelectionRange(newPos, newPos);
                    });

                    this.showFeedback('متغیر ' + tag + ' به متن اضافه شد');
                },

                showFeedback(msg) {
                    this.feedbackMessage = msg;
                    setTimeout(() => {
                        if (this.feedbackMessage === msg) {
                            this.feedbackMessage = '';
                        }
                    }, 2500);
                },

                get renderedPreview() {
                    const rawText = this.body || '';
                    if (!rawText.trim()) {
                        return '';
                    }

                    let text = rawText;
                    const sample = this.currentSample;

                    if (this.targetType === 'users') {
                        const name     = sample.name || 'علی محمدی';
                        const username = sample.username || 'alimohammadi';
                        const national = sample.national_code || '0012345678';
                        const phone    = sample.phone || '09123456789';
                        const email    = sample.email || 'ali@example.com';
                        const role     = sample.role || 'مدیر سیستم';

                        const map = {
                            '{0}': name, '{1}': username, '{2}': national, '{3}': phone, '{4}': email, '{5}': role,
                            '{name}': name, '{full_name}': name, '{نام}': name, '{نام_خانوادگی}': name, '{نام_کامل}': name,
                            '{username}': username, '{نام_کاربری}': username,
                            '{national_code}': national, '{کد_ملی}': national,
                            '{phone}': phone, '{mobile}': phone, '{موبایل}': phone, '{تلفن}': phone, '{شماره_موبایل}': phone,
                            '{email}': email, '{ایمیل}': email,
                            '{roles}': role, '{role}': role, '{نقش}': role
                        };

                        for (const [k, v] of Object.entries(map)) {
                            text = text.split(k).join(v);
                        }
                    } else {
                        const name       = sample.full_name || 'رضا رضایی';
                        const username   = sample.username || 'reza_rezaei';
                        const national   = sample.national_code || '0087654321';
                        const phone      = sample.phone || '09351234567';
                        const email      = sample.email || 'reza@example.com';
                        const status     = sample.status || 'مشتری فعال';
                        const caseNumber = sample.case_number || 'CR-10492';

                        const map = {
                            '{0}': name, '{1}': username, '{2}': national, '{3}': phone, '{4}': email, '{5}': status,
                            '{full_name}': name, '{name}': name, '{نام}': name, '{نام_خانوادگی}': name, '{نام_کامل}': name,
                            '{username}': username, '{نام_کاربری}': username,
                            '{national_code}': national, '{کد_ملی}': national,
                            '{phone}': phone, '{mobile}': phone, '{موبایل}': phone, '{تلفن}': phone, '{شماره_موبایل}': phone,
                            '{email}': email, '{ایمیل}': email,
                            '{status}': status, '{وضعیت}': status,
                            '{case_number}': caseNumber, '{شماره_پرونده}': caseNumber
                        };

                        for (const [k, v] of Object.entries(map)) {
                            text = text.split(k).join(v);
                        }
                    }

                    return text;
                },

                get smsStats() {
                    const preview = this.renderedPreview || this.body || '';
                    const charCount = preview.length;

                    // بررسی وجود کاراکترهای یونیکد (فارسی / عربی / ...)
                    const isUnicode = /[^\u0000-\u007f]/.test(preview);

                    let maxFirst = isUnicode ? 70 : 160;
                    let maxSubsequent = isUnicode ? 67 : 153;
                    let parts = 0;
                    let remaining = maxFirst;

                    if (charCount > 0) {
                        if (charCount <= maxFirst) {
                            parts = 1;
                            remaining = maxFirst - charCount;
                        } else {
                            parts = 1 + Math.ceil((charCount - maxFirst) / maxSubsequent);
                            const currentPartUsed = (charCount - maxFirst) % maxSubsequent || maxSubsequent;
                            remaining = maxSubsequent - currentPartUsed;
                        }
                    }

                    return {
                        charCount,
                        parts: parts || (charCount > 0 ? 1 : 0),
                        isUnicode,
                        remaining,
                        langName: isUnicode ? 'فارسی (یونیکد)' : 'لاتین (GSM 7-bit)'
                    };
                }
            };
        }
    </script>

    <div class="w-full mx-auto space-y-6 pb-20"
         x-data="smsSendApp({
            defaultTargetType: {{ Js::from($defaultTargetType) }},
            defaultSmsType: {{ Js::from($defaultSmsType) }},
            defaultScope: {{ Js::from($defaultScope) }},
            oldPattern: {{ Js::from($oldPattern) }},
            oldBody: {{ Js::from($oldBody) }},
            sampleUserData: {{ Js::from($sampleUserData) }},
            sampleClientData: {{ Js::from($sampleClientData) }}
         })">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    </span>
                    ارسال پیامک جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تنظیمات ارسال پیامک متنی عادی و خدماتی (پترن) با پشتیبانی از متغیرهای داینامیک</p>
            </div>

            <a href="{{ route('user.sms.logs.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                تاریخچه پیامک‌ها
            </a>
        </div>

        {{-- اعلان فیدبک کپی/درج سریع --}}
        <div x-show="feedbackMessage"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-gray-900 text-white dark:bg-indigo-600 px-4 py-2.5 rounded-xl shadow-xl text-xs font-bold pointer-events-none"
             style="display: none;">
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span x-text="feedbackMessage"></span>
        </div>

        {{-- نمایش خطاها --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/40 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-300 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    خطا در ثبت فرم
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('status'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('user.sms.send.store') }}" class="space-y-6">
            @csrf

            {{-- بخش ۱: انتخاب مخاطب --}}
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
                                            dark:peer-checked:bg-indigo-900/30 dark:peer-checked:border-indigo-500">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center dark:bg-indigo-900/50 dark:text-indigo-300 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">کاربران سیستم</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ارسال به پرسنل، مدیران و همکاران</div>
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
                                            dark:peer-checked:bg-emerald-900/30 dark:peer-checked:border-emerald-500">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center dark:bg-emerald-900/50 dark:text-emerald-300 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">مشتریان</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ارسال به پرونده‌ها و لیست مشتریان</div>
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
                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 border border-gray-100 dark:border-gray-700/60">
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
                        <div class="mt-4 text-[11px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800/80 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700/80 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>
                                <span class="font-bold text-gray-800 dark:text-gray-200" x-text="targetType === 'users' ? 'کاربران' : 'مشتریان'"></span>
                                <span x-text="recipientScope === 'selected' ? 'انتخاب شده در لیست پایین پیام را دریافت می‌کنند.' : 'که دارای شرایط زیر باشند پیام را دریافت می‌کنند.'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- انتخابگرها (MultiSelects) --}}
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
                        <div class="absolute top-8 right-3 pointer-events-none text-gray-400 dark:text-gray-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                </div>
            </section>

            {{-- بخش ۳: محتوای پیام، راهنما و پیش‌نمایش زنده --}}
            <section class="{{ $cardClass }} p-6 relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700 mb-6">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                        تنظیم محتوای پیامک و پیش‌نمایش زنده
                    </h2>

                    {{-- نشانگر نوع ارسال --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold"
                         :class="pattern.trim() !== '' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="pattern.trim() !== '' ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                        <span x-text="pattern.trim() !== '' ? 'ارسال سریع با پترن خدماتی' : 'ارسال متنی عادی با متغیرهای داینامیک'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

                    {{-- ستون اصلی: فرم نوشتن پیامک (7 ستون در دسکتاپ بزرگ) --}}
                    <div class="xl:col-span-7 space-y-5">
                        {{-- کد پترن --}}
                        <div class="bg-gray-50/70 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="{{ $labelClass }} mb-0">کد پترن خدماتی (اختیاری)</label>
                                <span class="text-[11px] text-gray-400 dark:text-gray-500">برای ارسال بدون مسدودی بلک‌لیست</span>
                            </div>
                            <input type="text"
                                   name="pattern"
                                   x-model="pattern"
                                   placeholder="مثلاً: 18459"
                                   class="{{ $inputClass }} dir-ltr text-left">
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                در صورت وارد کردن کد پترن، ارسال از طریق متد الگو (Pattern) انجام شده و متغیرهای <code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded text-[10px] dir-ltr inline-block">{0}</code> تا <code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded text-[10px] dir-ltr inline-block">{5}</code> به عنوان پارامتر ارسال می‌شوند.
                            </p>
                        </div>

                        {{-- متن پیامک و ابزارهای متغیر --}}
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <label class="{{ $labelClass }} mb-0">
                                    متن پیامک
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 mr-1">(پشتیبانی از متغیرهای داینامیک)</span>
                                </label>

                                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" /></svg>
                                    برای درج متغیر روی دکمه‌های زیر کلیک کنید:
                                </span>
                            </div>

                            {{-- چیپ‌های درج سریع متغیرها --}}
                            <div class="flex flex-wrap gap-1.5 p-2.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
                                <template x-for="item in availableVariables" :key="item.tag">
                                    <button type="button"
                                            @click="insertVariable(item.tag)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition-all shadow-xs active:scale-95 group"
                                            :title="item.desc + ' (نمونه: ' + item.sample + ')'">
                                        <span class="text-indigo-400 group-hover:text-white transition-colors">+</span>
                                        <span x-text="item.label"></span>
                                        <span class="text-[10px] opacity-75 dir-ltr" x-text="item.tag"></span>
                                    </button>
                                </template>
                            </div>

                            {{-- کادر تکست‌اریا --}}
                            <div class="relative">
                                <textarea name="body"
                                          x-ref="bodyTextarea"
                                          x-model="body"
                                          rows="6"
                                          class="{{ $inputClass }} resize-none leading-relaxed"
                                          placeholder="متن پیام خود را بنویسید... می‌توانید از متغیرهایی مثل {نام} یا {0} استفاده کنید تا برای هر گیرنده به صورت هوشمند جایگزین شوند."></textarea>
                            </div>

                            {{-- نوار وضعیت و شمارنده کاراکتر و صفحات پیامک --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 text-xs bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-100 dark:border-gray-700/80">
                                <div class="flex items-center gap-4 text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                        <span>تعداد کاراکتر:</span>
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="smsStats.charCount"></span>
                                    </div>

                                    <div class="flex items-center gap-1.5 border-r border-gray-200 dark:border-gray-700 pr-4">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>تعداد پارت:</span>
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="smsStats.parts + ' پیامک'"></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] text-gray-400 dark:text-gray-400" x-text="smsStats.langName"></span>
                                    <template x-if="smsStats.charCount > 0">
                                        <span class="text-[11px] px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            <span x-text="smsStats.remaining"></span> کاراکتر تا پارت بعدی
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ستون پیش‌نمایش زنده و راهنمای متغیرها (5 ستون در دسکتاپ بزرگ) --}}
                    <div class="xl:col-span-5 space-y-4">
                        {{-- تب‌های سایدبار پیش‌نمایش / راهنما --}}
                        <div class="flex items-center p-1 bg-gray-100 dark:bg-gray-900 rounded-xl">
                            <button type="button"
                                    @click="activeGuideTab = 'preview'"
                                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                    :class="activeGuideTab === 'preview' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                پیش‌نمایش زنده پیامک
                            </button>
                            <button type="button"
                                    @click="activeGuideTab = 'regular'"
                                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                    :class="activeGuideTab === 'regular' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                راهنمای متغیرها
                            </button>
                        </div>

                        {{-- تب ۱: پیش‌نمایش زنده شبیه‌ساز گوشی --}}
                        <div x-show="activeGuideTab === 'preview'" class="animate-in fade-in duration-200">
                            <div class="rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 p-3 shadow-inner">
                                {{-- گوشی شبیه‌ساز --}}
                                <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md border border-gray-200/80 dark:border-gray-700">
                                    {{-- نوار وضعیت گوشی --}}
                                    <div class="bg-gray-50 dark:bg-gray-900/90 px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500">
                                        <span>09:41</span>
                                        <div class="w-12 h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full mx-auto"></div>
                                        <div class="flex items-center gap-1 dir-ltr">
                                            <span>4G</span>
                                            <span>100%</span>
                                        </div>
                                    </div>

                                    {{-- هدر مخاطب در پیامک --}}
                                    <div class="p-3 bg-gray-50/80 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            <span x-text="sampleRecipientName.charAt(0)"></span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="sampleRecipientName"></div>
                                            <div class="text-[10px] text-gray-400 dark:text-gray-400 dir-ltr text-right" x-text="sampleRecipientPhone"></div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            نمونه پیش‌نمایش
                                        </span>
                                    </div>

                                    {{-- بدنه چت و حباب پیامک --}}
                                    <div class="p-4 min-h-[190px] max-h-[280px] overflow-y-auto bg-slate-100 dark:bg-gray-900/90 flex flex-col justify-end space-y-2">
                                        <div class="text-center">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-400 bg-white/80 dark:bg-gray-800/80 px-2.5 py-0.5 rounded-full border border-gray-200/60 dark:border-gray-700">امروز</span>
                                        </div>

                                        {{-- حالت پترن --}}
                                        <div x-show="pattern.trim() !== ''" class="self-end max-w-[90%]">
                                            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 p-3 rounded-2xl rounded-tr-xs text-xs text-amber-900 dark:text-amber-200 leading-relaxed shadow-xs">
                                                <div class="font-bold flex items-center gap-1.5 text-amber-700 dark:text-amber-400 mb-1 pb-1 border-b border-amber-200/60 dark:border-amber-800/40">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                    <span>ارسال با پترن خدماتی</span>
                                                    <span class="dir-ltr inline-block text-amber-800 dark:text-amber-300" x-text="'#' + pattern"></span>
                                                </div>
                                                <p class="text-[11px]">
                                                    متن پیام مطابق با الگوی ثبت شده در پنل پیامک با مقادیر گیرنده ارسال خواهد شد.
                                                </p>
                                            </div>
                                        </div>

                                        {{-- حالت ارسال عادی با متغیر --}}
                                        <div x-show="pattern.trim() === ''" class="self-end max-w-[92%]">
                                            <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-xs text-xs leading-relaxed shadow-sm transition-all break-words whitespace-pre-wrap">
                                                <span x-text="renderedPreview || 'متن پیامک شما پس از تایپ به صورت زنده با داده‌های گیرنده در اینجا شبیه‌سازی خواهد شد...'"
                                                      :class="!renderedPreview ? 'opacity-70 italic text-[11px]' : ''"></span>
                                                <div class="flex items-center justify-end gap-1 mt-1 text-[9px] text-indigo-200">
                                                    <span>همین الان</span>
                                                    <svg class="w-3 h-3 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- نوار فوتر شبیه‌ساز --}}
                                    <div class="p-2.5 bg-gray-50 dark:bg-gray-800/90 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                        <span>خروجی نهایی برای گیرنده فوق</span>
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            آماده پردازش
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- تب ۲: راهنمای متغیرها --}}
                        <div x-show="activeGuideTab === 'regular'" class="animate-in fade-in duration-200">
                            <div class="bg-indigo-50/70 dark:bg-indigo-950/20 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-900/50 space-y-4">
                                <div>
                                    <h4 class="text-xs font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2 mb-1">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        راهنمای متغیرهای داینامیک در ارسال پیامک
                                    </h4>
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed">
                                        شما می‌توانید در هر دو حالت <strong>ارسال عادی</strong> و <strong>پترن خدماتی</strong> از متغیرهای زیر استفاده کنید. سیستم پیش از ارسال، متغیرها را با مشخصات هر مخاطب جایگزین می‌نماید:
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="item in availableVariables" :key="item.tag">
                                        <div class="flex items-center justify-between gap-2 p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-indigo-100/80 dark:border-indigo-900/40 text-xs">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 font-bold text-gray-800 dark:text-gray-200">
                                                    <span x-text="item.label"></span>
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 dir-ltr inline-block" x-text="item.tag"></span>
                                                    <template x-if="item.patternTag !== '-'">
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-400 dir-ltr inline-block" x-text="'یا ' + item.patternTag"></span>
                                                    </template>
                                                </div>
                                                <div class="text-[10px] text-gray-400 dark:text-gray-400 truncate mt-0.5" x-text="'نمونه: ' + item.sample"></div>
                                            </div>
                                            <button type="button"
                                                    @click="insertVariable(item.tag)"
                                                    class="shrink-0 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-gray-100 dark:bg-gray-700 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-gray-700 dark:text-gray-200 transition-colors">
                                                درج
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-[11px] text-amber-800 dark:text-amber-300">
                                    <span class="font-bold">نکته:</span> هر دو فرمت فارسی (مانند <code class="font-bold text-amber-900 dark:text-amber-200">{نام}</code>) و فرمت عددی پترن (مانند <code class="font-bold text-amber-900 dark:text-amber-200">{0}</code>) در ارسال متنی ساده پشتیبانی می‌شوند.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- فوتر و دکمه‌ها --}}
            <div class="flex items-center justify-end gap-3 pt-4 pb-12">
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
