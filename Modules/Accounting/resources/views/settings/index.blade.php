@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')
@section('title', 'تنظیمات حسابداری')

@php
    $inputClass  = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass  = "block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2";
    $cardClass   = "bg-white dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm";
    $selectClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 appearance-none cursor-pointer";
    $v = fn(string $key, $default = '') => $settings->get($key, $default);

    $tabs = [
        'general'    => ['label' => 'عمومی و سیستم',          'icon' => 'cog'],
        // 'numbering'  => ['label' => 'شماره‌گذاری و مالیات',   'icon' => 'hash'],
        'appearance' => ['label' => 'اطلاعات هویتی',            'icon' => 'printer'],
        'defaults'   => ['label' => 'اتوماسیون (سرفصل‌ها)',   'icon' => 'bolt'],
    ];

    $typeLabels = [
        'asset' => 'دارایی',
        'liability' => 'بدهی',
        'equity' => 'سرمایه',
        'income' => 'درآمد',
        'revenue' => 'درآمد',
        'expense' => 'هزینه',
    ];

    $typeBadges = [
        'asset' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
        'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
        'equity' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
        'income' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'revenue' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
    ];

    $mapCategories = function($categories) use ($typeLabels, $typeBadges) {
        return $categories->sort(function($a, $b) {
            $codeA = $a->account_code ?? '';
            $codeB = $b->account_code ?? '';
            if ($codeA !== $codeB) return strcmp($codeA, $codeB);
            return strcmp($a->title ?? '', $b->title ?? '');
        })->map(function($c) use ($typeLabels, $typeBadges) {
            return [
                'id' => (string) $c->id,
                'title' => $c->title,
                'account_code' => (string) ($c->account_code ?? ''),
                'type' => $c->type,
                'type_label' => $typeLabels[$c->type] ?? $c->type,
                'type_badge' => $typeBadges[$c->type] ?? 'bg-gray-100 text-gray-700',
            ];
        })->values()->all();
    };

    $incomeCategoriesList = $mapCategories($incomeCategories);
    $expenseCategoriesList = $mapCategories($expenseCategories);
    $assetCategoriesList = $mapCategories($assetCategories);
    $liabilityCategoriesList = $mapCategories($liabilityCategories);

    $mapFunds = function($funds) {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق',
            'gateway' => 'درگاه پرداخت',
            'petty_cash' => 'تنخواه',
        ];
        $typeBadges = [
            'bank' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            'cash' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        ];
        return $funds->sort(function($a, $b) {
            return strcmp($a->name ?? '', $b->name ?? '');
        })->map(function($f) use ($types, $typeBadges) {
            return [
                'id' => (string) $f->id,
                'name' => $f->name,
                'bank_name' => $f->bank_name ?? '',
                'account_number' => $f->account_number ?? '',
                'type' => $f->type,
                'type_label' => $types[$f->type] ?? $f->type,
                'type_badge' => $typeBadges[$f->type] ?? 'bg-gray-100 text-gray-700',
            ];
        })->values()->all();
    };

    $cashFundsList = $mapFunds($fundAccounts->where('type', 'cash'));
    $bankFundsList = $mapFunds($fundAccounts->where('type', 'bank'));
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24" x-data="accountingSettingsForm({
    units: {{ json_encode($settings->get('units.list', [''])) }},
    custom_fields: {{ json_encode($settings->get('appearance.custom_fields', [['key' => '', 'value' => '']])) }}
})">

        {{-- Hero Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
            <span
                class="flex items-center justify-center w-14 h-14 rounded-2xl text-white shadow-lg shadow-indigo-500/30 shrink-0 bg-indigo-600"
                style="background-color: #4f46e5;">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">تنظیمات ماژول
                        حسابداری</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیکربندی قوانین مالی، شماره‌گذاری فاکتورها
                        و
                        اتوماسیون سند حسابداری.</p>
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

        <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data"
              @submit="isSubmitting = true">
            @csrf
            @method('PUT')

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
                                @case('cog')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    @break
                                @case('hash')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
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

            @php
                $isSuperAdmin = auth()->user()?->hasAnyRole(['super-admin', 'superadmin']);
            @endphp
            <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}" x-data="{ currency: '{{ $v('general.currency', 'تومان') }}' }">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                            واحد پول پیش‌فرض سیستم
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">واحد پولی پایه برای محاسبات و
                            نمایش
                            در تمام بخش‌های سیستم.</p>
                    </div>
                    <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                        <div class="flex flex-col">
                            <label class="{{ $labelClass }}">انتخاب واحد</label>
                            <div
                                class="flex items-center gap-2 p-1.5 rounded-xl bg-gray-100 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 {{ !$isSuperAdmin ? 'opacity-60 cursor-not-allowed' : '' }}">
                                <button type="button" @if($isSuperAdmin) @click="currency = 'تومان'" @else disabled
                                        @endif
                                        :class="currency === 'تومان' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                        class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200 {{ !$isSuperAdmin ? 'cursor-not-allowed' : '' }}">
                                    تومان (Toman)
                                </button>
                                <button type="button" @if($isSuperAdmin) @click="currency = 'ریال'" @else disabled
                                        @endif
                                        :class="currency === 'ریال' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                        class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200 {{ !$isSuperAdmin ? 'cursor-not-allowed' : '' }}">
                                    ریال (Rial)
                                </button>
                            </div>
                            @if($isSuperAdmin)
                                <input type="hidden" name="general[currency]" x-model="currency">
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 font-medium">تغییر واحد مالی
                                    فقط توسط سوپر ادمین امکان‌پذیر است.</p>
                            @endif
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
                                    x-text="currency === 'ریال' ? 'تمامی مبالغ در سیستم بر اساس ریال محاسبه خواهند شد.' : 'تمامی مبالغ در سیستم بر اساس تومان محاسبه خواهند شد.'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- اجازه موجودی منفی --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </span>
                            تنظیمات خزانه‌داری
                        </h2>
                    </div>
                    <div class="p-6 md:p-8">
                        <label for="banking_allow_negative_balance"
                               class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-red-200 dark:hover:border-red-500/30 transition-all">
                            <div class="flex-1">
                                <span class="text-base font-black text-gray-800 dark:text-gray-200 block">اجازه ثبت موجودی منفی در خزانه‌داری</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">اگر غیرفعال باشد، برداشت بیش از موجودی صندوق یا بانک خطای سیستمی خواهد داد.</span>
                            </div>
                            <div class="relative shrink-0">
                                <input type="hidden" name="banking[allow_negative_balance]" value="0">
                                <input type="checkbox" id="banking_allow_negative_balance"
                                       name="banking[allow_negative_balance]"
                                       value="1" @checked($v('banking.allow_negative_balance')) class="sr-only peer">
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

                        <label for="general_check_cheque_due_dates"
                               class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all mt-4">
                            <div class="flex-1">
                                <span class="text-base font-black text-gray-800 dark:text-gray-200 block">فعال‌سازی بررسی تاریخ سررسید چک‌ها (کرون جاب)</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">با فعال‌سازی این گزینه، فاکتورهای پرداخت شده با چک تا زمان وصول چک در حالت انتظار باقی می‌مانند و دکمه وصول چک نیز فقط پس از رسیدن تاریخ سررسید نمایش داده می‌شود.</span>
                            </div>
                            <div class="relative shrink-0">
                                <input type="hidden" name="general[check_cheque_due_dates]" value="0">
                                <input type="checkbox" id="general_check_cheque_due_dates"
                                       name="general[check_cheque_due_dates]"
                                       value="1"
                                       @checked($v('general.check_cheque_due_dates', true)) class="sr-only peer">
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
                @if(false)
                    <div class="{{ $cardClass }}">
                        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                                تنظیمات پیش‌فرض صدور فاکتور
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">مشخص کنید فاکتورهای جدید به
                                صورت
                                پیش‌فرض چگونه در سیستم ثبت شوند.</p>
                        </div>
                        <div class="p-6 md:p-8 space-y-4">
                            @php $currentStatus = $settings->get('invoice.default_status_on_create', 'draft'); @endphp

                            <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="invoiceDefaultStatus === 'draft' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-500/10 dark:border-indigo-500/50' : 'border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30'">
                                <input type="radio" name="invoice[default_status_on_create]" value="draft"
                                       class="mt-1 accent-indigo-600 shrink-0"
                                       x-model="invoiceDefaultStatus" @checked($currentStatus == 'draft')>
                                <div>
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">ثبت اولیه به عنوان پیش‌فاکتور (بدون سند)</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">فاکتورها ابتدا به صورت پیش‌نویس ذخیره می‌شوند. برای رسمی شدن و صدور سند مالی، نیاز به تایید دستی دارند.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="invoiceDefaultStatus === 'approved' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-500/10 dark:border-indigo-500/50' : 'border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30'">
                                <input type="radio" name="invoice[default_status_on_create]" value="approved"
                                       class="mt-1 accent-indigo-600 shrink-0"
                                       x-model="invoiceDefaultStatus" @checked($currentStatus == 'approved')>
                                <div>
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">ثبت و تایید مستقیم به عنوان فاکتور رسمی</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">فاکتورها در لحظه ثبت، تایید شده، شماره رسمی دریافت می‌کنند و سند حسابداری آن‌ها به صورت خودکار صادر می‌شود.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
            @if(false)
                <div x-show="activeTab === 'numbering'" x-cloak class="space-y-6">
                    <div class="{{ $cardClass }}">
                        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                                الگوی شماره‌گذاری فاکتور فروش
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">الگوی تولید شماره برای
                                فاکتورهای
                                جدید
                                را تعیین کنید.</p>
                        </div>
                        <div class="p-6 md:p-8 space-y-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="{{ $labelClass }}">حالت شماره‌گذاری</label>
                                    <select name="numbering[mode]" x-model="numbering.mode" class="{{ $selectClass }}">
                                        <option value="auto">تولید خودکار (پیوسته)</option>
                                        <option value="manual">دستی (توسط حسابدار)</option>
                                    </select>
                                </div>
                                <div x-show="numbering.mode === 'auto'" x-transition>
                                    <label class="{{ $labelClass }}">پیشوند (حروف)</label>
                                    <input type="text" name="numbering[prefix]" x-model="numbering.prefix"
                                           class="{{ $inputClass }} dir-ltr text-center" placeholder="P">
                                </div>
                                <div x-show="numbering.mode === 'auto'" x-transition>
                                    <label class="{{ $labelClass }}">جداکننده</label>
                                    <input type="text" name="numbering[separator]" x-model="numbering.separator"
                                           class="{{ $inputClass }} dir-ltr text-center" placeholder="-">
                                </div>
                                <div x-show="numbering.mode === 'auto'" x-transition>
                                    <label class="{{ $labelClass }}">طول عدد (تعداد ارقام)</label>
                                    <input type="number" name="numbering[length]" x-model.number="numbering.length"
                                           class="{{ $inputClass }} dir-ltr text-center" min="1" max="10">
                                </div>
                            </div>

                            <div x-show="numbering.mode === 'auto'" x-transition
                                 class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/10 border border-indigo-100 dark:border-indigo-500/20 flex flex-col items-center justify-center text-center">
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-indigo-400 dark:text-indigo-500 mb-2">پیش‌نمایش الگو</span>
                                <span
                                    class="font-mono text-3xl font-black text-indigo-600 dark:text-indigo-300 tracking-wider"
                                    x-text="generatePreview(numbering)"></span>
                            </div>

                            <div x-show="numbering.mode === 'auto'" x-transition
                                 class="border-t border-gray-100 dark:border-gray-700/60 pt-6">
                                <label for="numbering_include_year"
                                       class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                                    <div class="flex-1">
                                        <span class="text-base font-black text-gray-800 dark:text-gray-200 block">افزودن سال شمسی جاری به فرمت شماره‌گذاری</span>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">سال جاری شمسی به صورت خودکار در فرمت شماره فاکتور قرار می‌گیرد.</span>
                                    </div>
                                    <div class="relative shrink-0">
                                        <input type="hidden" name="numbering[include_year]" value="0">
                                        <input type="checkbox" id="numbering_include_year"
                                               name="numbering[include_year]"
                                               value="1" x-model="numbering.include_year" class="sr-only peer">
                                        <div
                                            class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors duration-300 shadow-inner"></div>
                                        <div
                                            class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                            <svg
                                                class="w-3 h-3 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
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
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                            الگوی شماره‌گذاری پیش‌فاکتور
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">الگوی تولید شماره برای
                            پیش‌فاکتورهای
                            جدید را تعیین کنید.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="{{ $labelClass }}">حالت شماره‌گذاری</label>
                                <select name="proforma[numbering_mode]" x-model="proforma_numbering.mode"
                                        class="{{ $selectClass }}">
                                    <option value="auto">تولید خودکار (پیوسته)</option>
                                    <option value="manual">دستی (توسط حسابدار)</option>
                                </select>
                            </div>
                            <div x-show="proforma_numbering.mode === 'auto'" x-transition>
                                <label class="{{ $labelClass }}">پیشوند (حروف)</label>
                                <input type="text" name="proforma[numbering_prefix]" x-model="proforma_numbering.prefix"
                                       class="{{ $inputClass }} dir-ltr text-center" placeholder="PF">
                            </div>
                            <div x-show="proforma_numbering.mode === 'auto'" x-transition>
                                <label class="{{ $labelClass }}">جداکننده</label>
                                <input type="text" name="proforma[numbering_separator]"
                                       x-model="proforma_numbering.separator"
                                       class="{{ $inputClass }} dir-ltr text-center"
                                       placeholder="-">
                            </div>
                            <div x-show="proforma_numbering.mode === 'auto'" x-transition>
                                <label class="{{ $labelClass }}">طول عدد (تعداد ارقام)</label>
                                <input type="number" name="proforma[numbering_length]"
                                       x-model.number="proforma_numbering.length"
                                       class="{{ $inputClass }} dir-ltr text-center" min="1" max="10">
                            </div>
                        </div>

                        <div x-show="proforma_numbering.mode === 'auto'" x-transition
                             class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/10 border border-blue-100 dark:border-blue-500/20 flex flex-col items-center justify-center text-center">
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-blue-400 dark:text-blue-500 mb-2">پیش‌نمایش الگو</span>
                            <span class="font-mono text-3xl font-black text-blue-600 dark:text-blue-300 tracking-wider"
                                  x-text="generatePreview(proforma_numbering)"></span>
                        </div>

                        <div x-show="proforma_numbering.mode === 'auto'" x-transition
                             class="border-t border-gray-100 dark:border-gray-700/60 pt-6">
                            <label for="proforma_include_year"
                                   class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                                <div class="flex-1">
                                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">افزودن سال شمسی جاری به فرمت شماره‌گذاری</span>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">سال جاری شمسی به صورت خودکار در فرمت شماره پیش‌فاکتور قرار می‌گیرد.</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="proforma[numbering_include_year]" value="0">
                                    <input type="checkbox" id="proforma_include_year"
                                           name="proforma[numbering_include_year]"
                                           value="1" x-model="proforma_numbering.include_year" class="sr-only peer">
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
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-inner">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                        </span>
                            مالیات بر ارزش افزوده
                        </h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2 mr-15">این درصد مالیات به
                            صورت
                            اتوماتیک در فاکتورهای جدید لحاظ می‌گردد.</p>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <label for="tax_enabled"
                               class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all">
                            <div class="flex-1">
                                <span class="text-base font-black text-gray-800 dark:text-gray-200 block">اعمال مالیات به صورت پیش‌فرض در فاکتورها</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">در صورت فعال بودن، مالیات به صورت خودکار در هنگام ثبت فاکتور اعمال می‌شود.</span>
                            </div>
                            <div class="relative shrink-0">
                                <input type="hidden" name="tax[enabled]" value="0">
                                <input type="checkbox" id="tax_enabled" name="tax[enabled]"
                                       value="1" x-model="taxEnabled" class="sr-only peer">
                                <div
                                    class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-emerald-600 transition-colors duration-300 shadow-inner"></div>
                                <div
                                    class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                    <svg
                                        class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                        <div x-show="taxEnabled" x-transition class="max-w-md">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">درصد مالیات
                                پیش‌فرض
                                (%)</label>
                            <div class="relative flex items-center">
                                <input type="number" name="tax[percentage]" id="tax_percentage"
                                       min="0" max="100" step="0.01"
                                       value="{{ $v('tax.percentage', 0) }}"
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
                                در صفحه ایجاد فاکتور، این مقدار به‌صورت خودکار دریافت می‌کنید. همچنین امکان ویرایش مقدار
                                مالیات برای هر فاکتور را خواهید داشت.
                            </p>
                        </div>
                        <div
                            class="border-t border-gray-100 dark:border-gray-700/60 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="rounding_mode" class="{{ $labelClass }}">نحوه گرد کردن مبلغ نهایی
                                    فاکتور</label>
                                <select name="tax[rounding_mode]" id="rounding_mode" x-model="roundingMode"
                                        class="{{ $selectClass }}">
                                    <option value="none">بدون گرد کردن (نمایش دقیق)</option>
                                    <option value="up">گرد کردن به بالا (سود سیستم)</option>
                                    <option value="down">گرد کردن به پایین (تخفیف جزئی مشتری)</option>
                                </select>
                            </div>
                            <div x-show="roundingMode !== 'none'" x-transition>
                                <label for="rounding_amount" class="{{ $labelClass }}">ضریب گرد کردن
                                    ({{ $v('general.currency', CurrencyService::getBaseCurrency()) }})</label>
                                <input type="number" name="tax[rounding_amount]" id="rounding_amount"
                                       value="{{ $v('tax.rounding_amount', 1000) }}"
                                       class="{{ $inputClass }} dir-ltr text-left" min="1">
                                <p class="text-xs text-gray-500 mt-2">مثال: اگر ۱۰۰۰ باشد، مبلغ
                                    ۳۴۵٬۲۰۰ {{ $v('general.currency', CurrencyService::getBaseCurrency()) }} به ۳۴۶٬۰۰۰
                                    (بالا) یا ۳۴۵٬۰۰۰ (پایین) تبدیل می‌شود.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div x-show="activeTab === 'appearance'" x-cloak class="space-y-6">
                @if(false)
                    <div class="{{ $cardClass }}">
                        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm-3-9.5a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"/>
                            </svg>
                        </span>
                                قالب چاپی فاکتورها
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">قالب پیش‌فرض پرینت فاکتور را
                                انتخاب
                                کنید.</p>
                        </div>
                        <div class="p-6 md:p-8">
                            <div class="max-w-sm">
                                <label for="invoice_template" class="{{ $labelClass }}">قالب پیش‌فرض پرینت</label>
                                <select name="appearance[invoice_template]" id="invoice_template"
                                        class="{{ $selectClass }}">
                                    <option
                                        value="standard" @selected(($settings->get('appearance.invoice_template') ?? null) == 'standard')>
                                        استاندارد (زیبا و مینیمال)
                                    </option>
                                    <option
                                        value="official" @selected(($settings->get('appearance.invoice_template') ?? null) == 'official')>
                                        رسمی (مورد تایید دارایی با جداول کامل)
                                    </option>
                                </select>
                            </div>
                        </div>
                </div>
                @endif
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 flex items-start justify-between gap-4 flex-wrap bg-gradient-to-r from-purple-50/50 to-transparent dark:from-purple-900/10 border-b border-gray-100 dark:border-gray-700/60">
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
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">اطلاعات هویتی فروشنده
                                    در</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    اطلاعات هویتی (نام، شماره اقتصادی، شناسه ملی، شماره ثبت، تلفن/نمابر، نشانی و مهر و
                                    امضا)
                                    به‌صورت خودکار از ماژول تنظیمات خوانده و در سربرگ فاکتور رسمی چاپ می‌شوند.
                                    هر فیلدی که در ماژول تنظیمات برایش مقداری ثبت نشده باشد، به‌طور خودکار از فاکتور حذف
                                    می‌شود.
                                </p>
                            </div>
                        </div>
                        @if(isset($identityTabRoute) && $identityTabRoute)
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
                    <div class="p-6 md:p-8">
                        <div
                            class="rounded-2xl bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-500/20 p-5 mb-6">
                            <p class="text-xs font-bold text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                مقادیر فعلی از ماژول تنظیمات (فقط خواندنی)
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                @php $idInfo = $identitySettings ?? []; @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 shrink-0">نام:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200">{{ $idInfo['name'] ?: '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 shrink-0">شناسه ملی:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr">{{ $idInfo['national_id'] ?: '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 shrink-0">کد اقتصادی:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr">{{ $idInfo['economic_code'] ?: '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 shrink-0">شماره ثبت:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr">{{ $idInfo['registration_number'] ?: '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 shrink-0">تلفن/نمابر:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200 dir-ltr">{{ $idInfo['phone_fax'] ?: '—' }}</span>
                                </div>
                                @if(!empty($idInfo['full_address']))
                                    <div class="md:col-span-2 flex items-start gap-2">
                                        <span class="text-gray-500 dark:text-gray-400 shrink-0">نشانی:</span>
                                        <span
                                            class="font-semibold text-gray-800 dark:text-gray-200">{{ $idInfo['full_address'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($idInfo['seal_signature']))
                                    <div class="md:col-span-2 flex items-center gap-3">
                                        <span class="text-gray-500 dark:text-gray-400 shrink-0">مهر و امضا:</span>
                                        <img src="{{ asset($idInfo['seal_signature']) }}" alt="مهر و امضا"
                                             class="h-12 object-contain rounded">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700/60 pt-6">
                            <h3 class="{{ $labelClass }} mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h16m-7 6h7"/>
                                </svg>
                                فیلدهای اطلاعاتی دلخواه اضافی
                            </h3>
                            <p class="text-xs text-gray-500 mb-4">اگر نیاز به نمایش اطلاعات اضافی (مثل شماره حساب بانکی،
                                کد
                                نمایندگی و...) در هدر فاکتور دارید، اینجا اضافه کنید.</p>
                            <div class="space-y-3">
                                <template x-for="(field, index) in custom_fields" :key="index">
                                    <div class="flex flex-col sm:flex-row gap-2 items-start">
                                        <input type="text" :name="'appearance[custom_fields][' + index + '][key]'"
                                               x-model="field.key" placeholder="عنوان (مثال: شماره حساب)"
                                               class="{{ $inputClass }} !mt-0 sm:w-1/3">
                                        <input type="text" :name="'appearance[custom_fields][' + index + '][value]'"
                                               x-model="field.value" placeholder="مقدار"
                                               class="{{ $inputClass }} !mt-0 sm:w-1/2">
                                        <button type="button" @click="removeCustomField(index)"
                                                class="mt-1 sm:mt-0 flex-shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-colors"
                                                title="حذف فیلد">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addCustomField()"
                                    class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                افزودن فیلد دلخواه
                            </button>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700/60 pt-6 mt-6">
                            <h3 class="{{ $labelClass }} mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                                واحدهای اندازه‌گیری کالا/خدمات
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="(unit, index) in units" :key="index">
                                    <div class="flex items-center gap-1 relative">
                                        <input type="text" :name="'units[list][' + index + ']'" x-model="units[index]"
                                               class="{{ $inputClass }} !mt-0 pr-8" placeholder="مثلاً: عدد">
                                        <button type="button" @click="removeUnit(index)"
                                                class="absolute right-2 text-gray-400 hover:text-red-500 transition-colors"
                                                title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addUnit()"
                                    class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                افزودن واحد جدید
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'defaults'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }} overflow-visible">
                    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                            اتوماسیون ثبت اسناد (هسته‌ی مرکزی)
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">با اتصال این سرفصل‌ها، موتور
                            حسابداری
                            بدون نیاز به دخالت کاربر، اسناد دوبل را به درستی صادر می‌کند.</p>
                    </div>
                    <div class="p-6 md:p-8 overflow-visible">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Sales Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.sales_income_category_id', '') }}',
                                options: @js($incomeCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل درآمدی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">درآمد فروش/خدمات <span
                                        class="font-normal text-gray-500 text-xs">(سمت بستانکار فاکتور)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[sales_income_category_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M12 4v16m8-8H4"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-emerald-50/70 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sales Discount Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.sales_discount_category_id', '') }}',
                                options: @js($expenseCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل هزینه‌ای...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">تخفیفات نقدی فروش <span
                                        class="font-normal text-gray-500 text-xs">(هزینه / کاهنده درآمد)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[sales_discount_category_id]"
                                           :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M20 12H4"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-rose-50/70 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Receivables Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.receivables_category_id', '') }}',
                                options: @js($assetCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل دارایی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">حساب‌های دریافتنی <span
                                        class="font-normal text-gray-500 text-xs">(مشتریان - بدهکار فاکتور)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[receivables_category_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Payables Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.payables_category_id', '') }}',
                                options: @js($liabilityCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل بدهی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">حساب‌های پرداختنی <span
                                        class="font-normal text-gray-500 text-xs">(تامین‌کنندگان / بستانکاران)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[payables_category_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-amber-50/70 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tax Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.sales_tax_category_id', '') }}',
                                options: @js($liabilityCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل بدهی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">مالیات بر ارزش افزوده فروش <span
                                        class="font-normal text-gray-500 text-xs">(بستانکار)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[sales_tax_category_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-amber-50/70 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Receivable Cheques Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.cheques_receivable_category_id', '') }}',
                                options: @js($assetCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل دارایی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">اسناد دریافتنی <span
                                        class="font-normal text-gray-500 text-xs">(گاوصندوق چک‌های دریافتی)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[cheques_receivable_category_id]"
                                           :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cheques In Transit Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.cheques_in_transit_category_id', '') }}',
                                options: @js($assetCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل دارایی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">اسناد در جریان وصول <span
                                        class="font-normal text-gray-500 text-xs">(چک‌های واگذار شده به بانک)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[cheques_in_transit_category_id]"
                                           :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Payable Cheques Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.cheques_payable_category_id', '') }}',
                                options: @js($liabilityCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل بدهی...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">اسناد پرداختنی <span
                                        class="font-normal text-gray-500 text-xs">(چک‌های صادره ما)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[cheques_payable_category_id]"
                                           :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-amber-50/70 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Default Cash Fund --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.cash_fund_id', '') }}',
                                options: @js($cashFundsList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.name && o.name.toLowerCase().includes(q)) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب از لیست صندوق‌ها...';
                                    return found.name;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">صندوق نقدی پیش‌فرض <span
                                        class="font-normal text-gray-500 text-xs">(خزانه‌داری)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[cash_fund_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو صندوق..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-emerald-50/70 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-text="opt.name" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ حسابی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Default Bank Fund --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.bank_fund_id', '') }}',
                                options: @js($bankFundsList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.name && o.name.toLowerCase().includes(q)) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب از لیست حساب‌های بانکی...';
                                    return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">حساب بانکی پیش‌فرض <span
                                        class="font-normal text-gray-500 text-xs">(خزانه‌داری)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[bank_fund_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو حساب یا بانک..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex flex-col gap-0.5 truncate">
                                                    <div class="flex items-center gap-1.5 truncate font-medium">
                                                        <span x-text="opt.name"></span>
                                                        <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                              x-text="'(' + opt.bank_name + ')'"></span>
                                                    </div>
                                                    <span x-show="opt.account_number"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ حسابی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bank Fee Category --}}
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ $v('defaults.bank_fee_category_id', '26') }}',
                                options: @js($expenseCategoriesList),
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        (o.title && o.title.toLowerCase().includes(q)) ||
                                        (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    this.selectedId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                    if (!found) return 'انتخاب سرفصل کارمزد...';
                                    return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                }
                            }"
                                 class="bg-gray-50/80 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-2 relative transition-all"
                                 :class="{ 'z-50': open, 'z-10': !open }">
                                <label class="{{ $labelClass }}">سرفصل کارمزد بانکی / خدمات بانکی <span
                                        class="font-normal text-gray-500 text-xs">(هزینه خدمات بانکی و تراکنش‌ها)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="defaults[bank_fee_category_id]" :value="selectedId">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل یا کد..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-rose-50/70 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate font-medium"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Action Bar --}}
            <div class="sticky bottom-4 z-40 mt-8">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-500 hover:to-indigo-600 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2"
                            :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }"
                            :disabled="isSubmitting">
                    <span x-show="!isSubmitting" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات
                    </span>
                        <span x-show="isSubmitting" class="flex items-center gap-2" style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال پردازش...
                    </span>
                    </button>
                    <div class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">
                        تغییرات بلافاصله در سیستم اعمال خواهند شد.
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('accountingSettingsForm', (data) => ({
                isSubmitting: false,
                activeTab: '{{ session('active_tab', old('active_tab')) }}' || localStorage.getItem('accountingSettingsTab') || 'general',
                invoiceDefaultStatus: '{{ $settings->get('invoice.default_status_on_create', 'draft') }}',

                // Numbering logic
                numbering: {
                    mode: '{{ $settings->get('numbering.mode', 'auto') }}',
                    prefix: '{{ $settings->get('numbering.prefix', 'P') }}',
                    separator: '{{ $settings->get('numbering.separator', '-') }}',
                    length: {{ $settings->get('numbering.length', 4) }},
                    include_year: {{ $settings->get('numbering.include_year', 1) ? 'true' : 'false' }},
                },
                proforma_numbering: {
                    mode: '{{ $settings->get('proforma.numbering_mode', 'auto') }}',
                    prefix: '{{ $settings->get('proforma.numbering_prefix', 'PF') }}',
                    separator: '{{ $settings->get('proforma.numbering_separator', '-') }}',
                    length: {{ $settings->get('proforma.numbering_length', 4) }},
                    include_year: {{ $settings->get('proforma.numbering_include_year', 1) ? 'true' : 'false' }},
                },

                // Tax
                taxEnabled: {{ $settings->get('tax.enabled', 0) ? 'true' : 'false' }},
                roundingMode: '{{ $settings->get('tax.rounding_mode', 'none') }}',

                // Arrays
                units: data.units && data.units.length > 0 ? data.units : [''],
                custom_fields: data.custom_fields && data.custom_fields.length > 0 ? data.custom_fields : [{
                    key: '',
                    value: ''
                }],

                init() {
                    this.$watch('activeTab', value => localStorage.setItem('accountingSettingsTab', value));
                },

                generatePreview(config) {
                    if (config.mode === 'manual') return 'توسط کاربر';
                    const sep = config.separator || '-';
                    let yearStr = config.include_year
                        ? new Date().toLocaleDateString('fa-IR-u-nu-latn', {year: 'numeric'}) + sep
                        : '';
                    let padding = '0'.repeat(Math.max(0, config.length - 1)) + '1';
                    return config.prefix + sep + yearStr + padding;
                },

                addUnit() {
                    this.units.push('');
                },
                removeUnit(index) {
                    if (this.units.length > 1) {
                        this.units.splice(index, 1);
                    } else {
                        this.units[0] = '';
                    }
                },
                addCustomField() {
                    this.custom_fields.push({key: '', value: ''});
                },
                removeCustomField(index) {
                    if (this.custom_fields.length > 1) {
                        this.custom_fields.splice(index, 1);
                    } else {
                        this.custom_fields = [{key: '', value: ''}];
                    }
                }
            }));
        });
    </script>
@endsection
