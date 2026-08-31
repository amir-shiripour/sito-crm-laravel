@extends('layouts.user')

@section('title', 'حساب‌های خزانه‌داری')

@php
    use Modules\Accounting\App\Services\CurrencyService;

    if (!function_exists('is_color_light')) {
        function is_color_light($hexColor) {
            $lightColors = ['#ffd100', '#fad124', '#fcd34d'];
            return in_array(strtolower($hexColor), $lightColors);
        }
    }

    $fundTypePriority = ['bank' => 1, 'cash' => 2, 'gateway' => 3, 'petty_cash' => 4];
    $fundAccountsList = $allFundAccounts->sort(function($a, $b) use ($fundTypePriority) {
        $pA = $fundTypePriority[$a->type] ?? 99;
        $pB = $fundTypePriority[$b->type] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        return strcmp($a->name ?? '', $b->name ?? '');
    })->map(function($fa) {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق',
            'gateway' => 'درگاه پرداخت',
            'petty_cash' => 'تنخواه',
        ];
        $typeBadges = [
            'bank' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            'cash' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            'gateway' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
            'petty_cash' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
        ];
        return [
            'id' => (string) $fa->id,
            'name' => $fa->name,
            'bank_name' => $fa->bank_name ?? '',
            'account_number' => $fa->account_number ?? '',
            'type' => $fa->type,
            'type_label' => $types[$fa->type] ?? ($fa->type ?: 'خزانه'),
            'type_badge' => $typeBadges[$fa->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            'current_balance' => (float) $fa->current_balance,
        ];
    })->values()->all();
@endphp

@section('content')
<div x-data="fundAccountIndexPage" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                مدیریت خزانه‌داری و حساب‌ها
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">مدیریت حساب‌های بانکی، صندوق‌های نقدی و درگاه‌های
                پرداخت آنلاین.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openTransferModal()" type="button"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-teal-600 text-white font-bold text-sm shadow-md shadow-teal-500/30 hover:bg-teal-700 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                انتقال وجه بین حساب‌ها
            </button>
            <a href="{{ route('admin.accounting.fund-accounts.create') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                افزودن حساب جدید
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
        <div
            class="bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 p-5 rounded-2xl shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold">خطاهایی رخ داده است:</h3>
                    <ul class="mt-2 text-sm space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    @if (session('success'))
        <div
            class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div
            class="bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-bold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Fund Accounts Grid --}}
    @if($fundAccounts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($fundAccounts as $fundAccount)
                @php
                    $bgColor = '';
                    $textColor = 'text-white';
                    $borderColor = 'border-white/20';
                    switch ($fundAccount->type) {
                        case 'bank': $bgColor = '#4e91e6'; break;
                        case 'cash': $bgColor = '#00ab84'; break;
                        case 'gateway': $bgColor = '#6558b1'; break;
                        default: $bgColor = '#333333'; break;
                    }
                    $isLight = is_color_light($bgColor);
                    if ($isLight) {
                        $textColor = 'text-gray-900';
                        $borderColor = 'border-gray-900/20';
                    }
                    $balance = $fundAccount->current_balance;
                @endphp
                <div
                    class="relative w-full h-60 rounded-3xl shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 {{ $textColor }} overflow-hidden"
                    style="background-color: {{ $bgColor }}">
                    <div class="absolute inset-0 opacity-10"
                         style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTYwMCA4MDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbD0iI2ZmZmZmZiIgb3BhY2l0eT0iMC4wNSIgZD0iTTAgMGgxNjAwdjgwMEgwWiIvPjwvc3ZnPg==');"></div>

                    <div class="relative p-6 h-full flex flex-col justify-between z-10">
                        <div class="flex justify-between items-start w-full">
                            <div class="flex flex-col items-start gap-1">
                                <h3 class="text-xl font-black drop-shadow-md">{{ $fundAccount->name }}</h3>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-4 font-bold rounded-full shadow-sm {{ $fundAccount->status ? 'bg-white/20' : 'bg-rose-500/80' }}">
                                    {{ $fundAccount->status ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.accounting.fund-accounts.edit', $fundAccount) }}"
                                   class="p-2 rounded-xl bg-black/10 hover:bg-black/20 transition-all hover:scale-110 text-current"
                                   title="ویرایش">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.accounting.fund-accounts.destroy', $fundAccount) }}"
                                      method="POST"
                                      onsubmit="return confirm('آیا از حذف این حساب خزانه‌داری اطمینان دارید؟');"
                                      class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-2 rounded-xl bg-black/10 hover:bg-rose-600 hover:text-white transition-all hover:scale-110 text-current"
                                            title="حذف">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($fundAccount->isWalletAccount())
                            <div class="text-xl font-bold tracking-wider text-center opacity-90">
                                کیف پول (مجموع کیف پول‌های کاربران)
                            </div>
                        @elseif($fundAccount->type === 'bank')
                            @if($fundAccount->card_number)
                                @php
                                    $cleanCard = preg_replace('/[^0-9]/', '', $fundAccount->card_number);
                                    $formattedCard = strlen($cleanCard) >= 16 ? implode(' - ', str_split($cleanCard, 4)) : $fundAccount->card_number;
                                @endphp
                                <div dir="ltr" style="direction: ltr;"
                                     class="text-xl font-mono tracking-[0.15em] text-center opacity-90 drop-shadow-sm dir-ltr">
                                    {{ $formattedCard }}
                                </div>
                            @elseif($fundAccount->account_number)
                                <div dir="ltr" style="direction: ltr;"
                                     class="text-lg font-mono tracking-wider text-center opacity-90 dir-ltr">
                                    شماره حساب: {{ $fundAccount->account_number }}
                                </div>
                            @elseif($fundAccount->iban)
                                <div dir="ltr" style="direction: ltr;"
                                     class="text-sm font-mono tracking-wider text-center opacity-90 dir-ltr">
                                    {{ $fundAccount->iban }}
                                </div>
                            @else
                                <div class="text-xl font-bold tracking-wider text-center opacity-90">
                                    {{ $fundAccount->bank_name ?? 'حساب بانکی' }}
                                </div>
                            @endif
                        @elseif($fundAccount->type === 'cash')
                            <div class="text-xl font-bold tracking-wider text-center opacity-90">
                                صندوق نقدی
                            </div>
                        @elseif($fundAccount->type === 'gateway')
                            <div class="text-xl font-bold tracking-wider text-center opacity-90">
                                درگاه پرداخت آنلاین
                            </div>
                        @endif

                        <div class="flex justify-between items-end pt-3 border-t {{ $borderColor }}">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider opacity-70 mb-0.5">نام صاحب حساب</p>
                                <p class="text-sm font-bold drop-shadow-sm">{{ $fundAccount->account_holder_name ?? $fundAccount->name }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] uppercase tracking-wider opacity-70 mb-0.5 text-right">موجودی
                                    فعلی</p>
                                <p class="text-lg font-black dir-ltr drop-shadow-sm tabular-nums">{{ CurrencyService::formatWithSuffix($balance) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($fundAccounts->hasPages())
            <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                {{ $fundAccounts->links() }}
            </div>
        @endif
    @else
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-16 text-center backdrop-blur-xl">
            <div
                class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ حساب خزانه‌داری یافت نشد</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">برای ثبت تراکنش‌ها، ابتدا حساب
                بانکی یا صندوق نقدی جدید تعریف کنید.</p>
            <a href="{{ route('admin.accounting.fund-accounts.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                افزودن اولین حساب خزانه‌داری
            </a>
        </div>
    @endif

    {{-- Transfer Modal --}}
    <div x-show="transferModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="transferModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             @click="transferModalOpen = false"></div>
        <div class="flex min-h-dvh items-center justify-center p-4 text-center">
            <div x-show="transferModalOpen" x-transition
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
                <form action="{{ route('admin.accounting.fund-accounts.transfer') }}" method="POST">
                    @csrf
                    <div
                        class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                            انتقال وجه بین حساب‌های خزانه‌داری
                        </h3>
                        <button type="button" @click="transferModalOpen = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">از حساب (مبدا)
                                <span class="text-rose-500">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                get filteredOptions() {
                                    if (!this.search.trim()) return fundAccountsList;
                                    const q = this.search.toLowerCase();
                                    return fundAccountsList.filter(o =>
                                        (o.name && o.name.toLowerCase().includes(q)) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    fromFundAccountId = opt ? String(opt.id) : '';
                                    updateMaxAmount();
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = fundAccountsList.find(o => String(o.id) === String(fromFundAccountId));
                                    if (!found) return 'انتخاب حساب مبدا...';
                                    return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                },
                                formatNumber(num) {
                                    if (!num && num !== 0) return '۰';
                                    return this.formatFa(Number(num).toLocaleString('en-US'));
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="from_fund_account_id" :value="fromFundAccountId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search"
                                               placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none">
                                    </div>

                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-teal-50 dark:hover:bg-teal-900/30 hover:text-teal-600 dark:hover:text-teal-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-teal-50/70 dark:bg-teal-900/40 text-teal-600 dark:text-teal-300 font-bold': String(fromFundAccountId) === String(opt.id) }">
                                            <div class="flex flex-col gap-0.5 truncate">
                                                <div class="flex items-center gap-1.5 truncate font-medium">
                                                    <span x-text="opt.name"></span>
                                                    <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                          x-text="'(' + opt.bank_name + ')'"></span>
                                                </div>
                                                <div class="flex items-center gap-2 text-[10px] text-gray-400">
                                                    <span x-show="opt.account_number"
                                                          x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold"
                                                          x-text="'موجودی: ' + formatNumber(opt.current_balance) + ' {{ $currencySuffix }}'"></span>
                                                </div>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="opt.type_badge"
                                                  x-text="opt.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ حسابی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">به حساب (مقصد)
                                <span class="text-rose-500">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                get filteredOptions() {
                                    let list = fundAccountsList.filter(o => String(o.id) !== String(fromFundAccountId));
                                    if (!this.search.trim()) return list;
                                    const q = this.search.toLowerCase();
                                    return list.filter(o =>
                                        (o.name && o.name.toLowerCase().includes(q)) ||
                                        (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                        (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                        (o.type_label && o.type_label.toLowerCase().includes(q))
                                    );
                                },
                                select(opt) {
                                    toFundAccountId = opt ? String(opt.id) : '';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = fundAccountsList.find(o => String(o.id) === String(toFundAccountId));
                                    if (!found) return 'انتخاب حساب مقصد...';
                                    return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                                },
                                formatFa(str) {
                                    if (!str && str !== 0) return '';
                                    const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                },
                                formatNumber(num) {
                                    if (!num && num !== 0) return '۰';
                                    return this.formatFa(Number(num).toLocaleString('en-US'));
                                }
                            }" class="relative" :class="{ 'z-40': open }">
                                <input type="hidden" name="to_fund_account_id" :value="toFundAccountId" required>

                                <button type="button" @click="open = !open"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all dark:text-white flex items-center justify-between cursor-pointer text-start">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search"
                                               placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none">
                                    </div>

                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-teal-50 dark:hover:bg-teal-900/30 hover:text-teal-600 dark:hover:text-teal-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-teal-50/70 dark:bg-teal-900/40 text-teal-600 dark:text-teal-300 font-bold': String(toFundAccountId) === String(opt.id) }">
                                            <div class="flex flex-col gap-0.5 truncate">
                                                <div class="flex items-center gap-1.5 truncate font-medium">
                                                    <span x-text="opt.name"></span>
                                                    <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                          x-text="'(' + opt.bank_name + ')'"></span>
                                                </div>
                                                <div class="flex items-center gap-2 text-[10px] text-gray-400">
                                                    <span x-show="opt.account_number"
                                                          x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold"
                                                          x-text="'موجودی: ' + formatNumber(opt.current_balance) + ' {{ $currencySuffix }}'"></span>
                                                </div>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="opt.type_badge"
                                                  x-text="opt.type_label"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ حسابی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">مبلغ
                                انتقال ({{ $currencySuffix }}) <span class="text-rose-500">*</span></label>
                            <input type="text" name="amount" id="amount" x-model="amount"
                                   @input="handleAmountInput($event)"
                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all dark:text-white dir-ltr text-left"
                                   required>
                            <p x-show="maxAmount > 0"
                               class="text-xs text-gray-500 mt-2 cursor-pointer hover:text-teal-600 font-bold"
                               @click="setFullAmount">
                                حداکثر قابل انتقال: <span x-text="formatNumber(maxAmount)"></span> {{ $currencySuffix }}
                                (انتخاب کل موجودی)
                            </p>
                        </div>
                        <div>
                            <label for="description"
                                   class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">توضیحات
                                انتقال</label>
                            <textarea name="description" id="description" rows="2"
                                      class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-3xl">
                        <button type="submit"
                                class="px-6 py-3 rounded-xl bg-teal-600 text-white font-bold text-sm shadow-md hover:bg-teal-700 transition-all">
                            ثبت انتقال وجه
                        </button>
                        <button type="button" @click="transferModalOpen = false"
                                class="px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-sm hover:bg-gray-200 transition-all">
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fundAccountIndexPage', () => ({
                transferModalOpen: false,
                fromFundAccountId: '',
                toFundAccountId: '',
                amount: '',
                maxAmount: 0,
                baseCurrency: '{{ $currencySuffix }}',
                fundAccountsList: @js($fundAccountsList),
                openTransferModal() {
                    this.fromFundAccountId = '';
                    this.toFundAccountId = '';
                    this.amount = '';
                    this.maxAmount = 0;
                    this.transferModalOpen = true;
                },
                updateMaxAmount() {
                    let found = this.fundAccountsList.find(o => String(o.id) === String(this.fromFundAccountId));
                    let balance = found ? (parseFloat(found.current_balance) || 0) : 0;

                    if (this.baseCurrency === 'تومان') {
                        this.maxAmount = balance / 10;
                    } else {
                        this.maxAmount = balance;
                    }

                    if (this.fromFundAccountId === this.toFundAccountId) {
                        this.toFundAccountId = '';
                    }
                },
                setFullAmount() {
                    if (this.maxAmount > 0) {
                        this.amount = this.formatNumber(this.maxAmount);
                    }
                },
                handleAmountInput(e) {
                    let cleanVal = this.toEnglishDigits(e.target.value);
                    this.amount = cleanVal ? parseInt(cleanVal, 10).toLocaleString('en-US') : '';
                    e.target.value = this.amount;
                },
                toEnglishDigits(value) {
                    if (value === null || typeof value === 'undefined') return '';
                    let strValue = String(value)
                        .replace(/[\u0660-\u0669]/g, c => c.charCodeAt(0) - 0x0660)
                        .replace(/[\u06F0-\u06F9]/g, c => c.charCodeAt(0) - 0x06F0);
                    return strValue.replace(/[^0-9.]/g, '');
                },
                formatNumber(value) {
                    const cleanValue = this.toEnglishDigits(value);
                    const num = parseInt(cleanValue, 10);
                    if (isNaN(num)) return '';
                    return num.toLocaleString('en-US');
                }
            }));
        });
    </script>
@endpush
@endsection
