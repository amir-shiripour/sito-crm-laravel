@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'مدیریت چک‌ها')

@php
    $statusTexts = [
        'all' => 'همه وضعیت‌ها',
        'pending' => 'در جریان',
        'deposited' => 'واگذار به بانک',
        'cleared' => 'وصول شده',
        'bounced' => 'برگشتی',
        'transferred' => 'خرج شده',
        'returned' => 'عودت داده شده',
    ];
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'deposited' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400',
        'cleared' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'bounced' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
        'transferred' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
        'returned' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    ];
    $currencySuffix = $currencySuffix ?? CurrencyService::getBaseCurrency();

    $catTypePriority = ['asset' => 1, 'liability' => 2, 'equity' => 3, 'income' => 4, 'expense' => 5];
    $sortedCategories = collect($categories)->sort(function($a, $b) use ($catTypePriority) {
        $typeA = is_array($a) ? ($a['type'] ?? '') : ($a->type ?? '');
        $typeB = is_array($b) ? ($b['type'] ?? '') : ($b->type ?? '');
        $pA = $catTypePriority[$typeA] ?? 99;
        $pB = $catTypePriority[$typeB] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        $codeA = (string) (is_array($a) ? ($a['account_code'] ?? '') : ($a->account_code ?? ''));
        $codeB = (string) (is_array($b) ? ($b['account_code'] ?? '') : ($b->account_code ?? ''));
        if ($codeA !== $codeB) return strcmp($codeA, $codeB);
        $titleA = (string) (is_array($a) ? ($a['title'] ?? '') : ($a->title ?? ''));
        $titleB = (string) (is_array($b) ? ($b['title'] ?? '') : ($b->title ?? ''));
        return strcmp($titleA, $titleB);
    })->values()->all();

    $fundTypePriority = ['bank' => 1, 'cash' => 2, 'gateway' => 3, 'petty_cash' => 4];
    $sortedFundAccounts = collect($fundAccounts)->sort(function($a, $b) use ($fundTypePriority) {
        $typeA = is_array($a) ? ($a['type'] ?? '') : ($a->type ?? '');
        $typeB = is_array($b) ? ($b['type'] ?? '') : ($b->type ?? '');
        $pA = $fundTypePriority[$typeA] ?? 99;
        $pB = $fundTypePriority[$typeB] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        $nameA = (string) (is_array($a) ? ($a['name'] ?? '') : ($a->name ?? ''));
        $nameB = (string) (is_array($b) ? ($b['name'] ?? '') : ($b->name ?? ''));
        return strcmp($nameA, $nameB);
    })->values()->all();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
         x-data="chequeDashboard({ cheques: {{ json_encode($cheques) }}, fundAccounts: {{ json_encode($sortedFundAccounts) }}, categories: {{ json_encode($sortedCategories) }} })">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                    داشبورد مدیریت چک‌ها
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">مدیریت چک‌های دریافتی، پرداختی، وصول، اعلام
                    برگشت و واگذاری.</p>
            </div>
            <a href="{{ route('admin.accounting.cheques.create') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                ثبت چک جدید
            </a>
        </div>

        {{-- Tabs --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-2 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-x-auto hide-scrollbar">
            <nav class="flex space-x-2 space-x-reverse min-w-max" aria-label="Tabs">
                @foreach($statusTexts as $status => $text)
                    <button @click="activeTab = '{{ $status }}'"
                            :class="activeTab === '{{ $status }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-white font-semibold'"
                            class="px-5 py-2.5 rounded-xl text-sm transition-all duration-200">
                        {{ $text }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Cheques Table Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            نوع چک
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مشتری / طرف حساب
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ چک ({{ $currencySuffix }})
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            تاریخ صدور
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            تاریخ سررسید
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            وضعیت
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    <template x-for="cheque in filteredCheques" :key="cheque.id">
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                      :class="cheque.type === 'receivable' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="cheque.type === 'receivable' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span x-text="cheque.type === 'receivable' ? 'دریافتی' : 'پرداختی'"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                <div class="text-base"
                                     x-text="cheque.client ? cheque.client.full_name : 'نامشخص'"></div>
                                <template x-if="cheque.payee_name">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                         x-text="'در وجه: ' + cheque.payee_name"></div>
                                </template>
                                <template x-if="cheque.invoice_info">
                                    <a :href="'/user/services/invoices/' + cheque.invoice_info.id"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 mt-1 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        <span>فاکتور: </span>
                                        <span x-text="cheque.invoice_info.number"></span>
                                    </a>
                                </template>
                                <template x-if="cheque.service_payment_info">
                                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-1 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>بابت فاکتور: </span>
                                        <span x-text="cheque.service_payment_info.invoice_number"></span>
                                    </div>
                                </template>
                                <template x-if="cheque.status === 'transferred'">
                                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 dark:text-purple-400 mt-1 bg-purple-50 dark:bg-purple-500/10 px-2 py-0.5 rounded border border-purple-200/50 dark:border-purple-500/20">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>خرج شده: </span>
                                        <span x-text="cheque.description ? cheque.description : (cheque.expense_info ? cheque.expense_info.description : 'به علت ثبت دستی')"></span>
                                    </div>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 dark:text-white tabular-nums">
                                <span class="text-base" x-text="formatCurrency(cheque.amount)"></span> <span
                                    class="text-xs font-normal text-gray-400">{{ $currencySuffix }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium tabular-nums text-sm"
                                x-text="cheque.issue_date_jalali"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium tabular-nums text-sm"
                                x-text="cheque.due_date_jalali"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                                    :class="statusClasses[cheque.status]">
                                    <span x-text="statusTexts[cheque.status]"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                @include('accounting::cheques.partials.actions-menu')
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredCheques.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div
                                    class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4 shadow-inner mx-auto">
                                    <svg class="w-9 h-9 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">هیچ چکی در این بخش وجود
                                    ندارد</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">برای این وضعیت هیچ چکی ثبت نشده
                                    است.</p>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modals --}}
        @include('accounting::cheques.partials.actions-modal')
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('chequeDashboard', (config) => ({
                    cheques: config.cheques,
                    fundAccounts: config.fundAccounts,
                    categories: config.categories,
                    activeTab: 'all',
                    modalOpen: false,
                    modalAction: '',
                    modalTitle: '',
                    currentCheque: null,
                    statusTexts: @json($statusTexts),
                    statusClasses: @json($statusClasses),

                    get filteredCheques() {
                        if (this.activeTab === 'all') return this.cheques;
                        return this.cheques.filter(c => c.status === this.activeTab);
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('fa-IR').format(value);
                    },

                    openModal(action, cheque) {
                        this.modalAction = action;
                        this.currentCheque = cheque;
                        this.modalOpen = true;
                        switch (action) {
                            case 'deposit':
                                this.modalTitle = 'واگذاری چک به بانک';
                                break;
                            case 'clear':
                                this.modalTitle = 'اعلام وصول / پاس شدن چک';
                                break;
                            case 'bounce':
                                this.modalTitle = 'اعلام برگشتی چک';
                                break;
                            case 'endorse':
                                this.modalTitle = 'خرج کردن / انتقال چک';
                                break;
                            case 'return-with-cash':
                                this.modalTitle = 'تسویه نقدی و عودت';
                                break;
                            case 'revert-clearance':
                                this.modalTitle = 'لغو عملیات وصول';
                                break;
                        }
                    },

                    closeModal() {
                        this.modalOpen = false;
                        this.modalAction = '';
                        this.currentCheque = null;
                    }
                }));
            });
        </script>
    @endpush
@endsection
