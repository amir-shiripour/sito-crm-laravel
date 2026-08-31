@php
    use Morilog\Jalali\Jalalian;

    /** @var \Modules\Clients\Entities\Client $client */
    /** @var \Modules\Wallet\App\Models\Wallet|null $wallet */
    $wallet = $wallet ?? null;
    if (!$wallet && class_exists(\Modules\Wallet\App\Services\WalletService::class)) {
        try {
            $wallet = app(\Modules\Wallet\App\Services\WalletService::class)->getOrCreateWallet($client);
        } catch (\Exception $e) {
            $wallet = null;
        }
    }

    $transactions = $transactions ?? ($wallet ? $wallet->transactions()->latest()->limit(50)->get() : collect([]));
    $balance = $wallet ? (float)$wallet->balance : 0;
    $walletCurrency = $wallet ? $wallet->currency : 'IRR';
    $currencyLabel = 'ریال';

    $totalDeposits = $wallet ? $wallet->transactions()->whereIn('type', ['deposit', 'refund', 'bonus', 'commission'])->sum('amount') : 0;
    $totalWithdrawals = $wallet ? $wallet->transactions()->whereIn('type', ['withdraw', 'payment', 'fee', 'penalty'])->sum('amount') : 0;
    $txCount = $wallet ? $wallet->transactions()->count() : 0;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $toJalali = function ($date) {
        if (!$date) return null;
        try {
            if ($date instanceof \Carbon\Carbon && $date->year < 1900) {
                return new Jalalian($date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second);
            }
            if ($date instanceof \Carbon\Carbon) return Jalalian::fromCarbon($date);
            return Jalalian::fromDateTime($date);
        } catch (\Exception $e) { return null; }
    };
@endphp

<div class="p-6 sm:p-8 space-y-6" x-data="{
    showClientWalletModal: false,
    clientWalletMode: 'deposit',
    walletAmount: '',
    walletDescription: '',
    isSubmitting: false,
    clientQuickChips: [
        { label: '۱۰۰ هزار', value: 100000 },
        { label: '۵۰۰ هزار', value: 500000 },
        { label: '۱ میلیون', value: 1000000 },
        { label: '۵ میلیون', value: 5000000 },
        { label: '۱۰ میلیون', value: 10000000 }
    ],

    openWalletAction(mode) {
        this.clientWalletMode = mode;
        this.walletAmount = '';
        this.walletDescription = '';
        this.showClientWalletModal = true;
    },

    formatNumber(val) {
        if (!val) return '';
        const clean = String(val).replace(/,/g, '').trim();
        if (clean === '') return '';
        const num = Math.round(parseFloat(clean));
        if (isNaN(num)) return '';
        return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    unformatNumber(val) {
        if (!val) return 0;
        const clean = String(val).replace(/,/g, '').trim();
        if (clean === '') return 0;
        const num = Math.round(parseFloat(clean));
        return isNaN(num) ? 0 : num;
    },

    addAmount(val) {
        const current = this.unformatNumber(this.walletAmount) || 0;
        this.walletAmount = this.formatNumber(current + val);
    },

    numberToWords(value) {
        if (!value) return '';
        let num = String(value).replace(/,/g, '');
        if (num === '' || isNaN(num) || parseInt(num) === 0) return '';
        const ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
        const tens = ['', 'ده', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
        const hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
        const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
        const classes = ['', 'هزار', 'میلیون', 'میلیارد', 'هزار میلیارد'];
        let str = num.split('').reverse().join('');
        let result = [];
        for (let i = 0; i < str.length; i += 3) {
            let group = str.substr(i, 3).split('').reverse().join('');
            if (parseInt(group) === 0) continue;
            let groupWords = [];
            let h = parseInt(group.length === 3 ? group[0] : 0);
            let t = parseInt(group.length >= 2 ? group[group.length - 2] : 0);
            let o = parseInt(group[group.length - 1]);
            if (h > 0) groupWords.push(hundreds[h]);
            if (t === 1 && o >= 0) { groupWords.push(teens[o]); }
            else { if (t > 1) groupWords.push(tens[t]); if (o > 0) groupWords.push(ones[o]); }
            let groupText = groupWords.filter(Boolean).join(' و ');
            if (classes[i / 3]) groupText += ' ' + classes[i / 3];
            result.push(groupText);
        }
        return result.reverse().join(' و ');
    }
}">

    {{-- Stats Cards & Quick Action Header --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Balance --}}
        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50/50 dark:from-emerald-950/40 dark:to-teal-950/20 border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">موجودی کیف پول</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300 tabular-nums">
                    {{ $faNum(number_format($balance)) }}
                </span>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $currencyLabel }}</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-600/80 dark:text-emerald-400/80">
                <span class="w-2 h-2 rounded-full {{ ($wallet && $wallet->is_active) ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                <span>وضعیت: {{ ($wallet && $wallet->is_active) ? 'فعال و آماده استفاده' : 'مسدود' }}</span>
            </div>
        </div>

        {{-- Card 2: Total Deposits --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">مجموع شارژ و واریزی‌ها</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-1.5">
                <span class="text-xl font-bold text-gray-900 dark:text-white tabular-nums">
                    {{ $faNum(number_format($totalDeposits)) }}
                </span>
                <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
            </div>
            <span class="mt-2 block text-[11px] text-gray-400">واریزهای نقدی، آنلاین و پاداش</span>
        </div>

        {{-- Card 3: Total Withdrawals --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">مجموع مصرف و برداشتی‌ها</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-1.5">
                <span class="text-xl font-bold text-gray-900 dark:text-white tabular-nums">
                    {{ $faNum(number_format($totalWithdrawals)) }}
                </span>
                <span class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
            </div>
            <span class="mt-2 block text-[11px] text-gray-400">پرداخت سفارشات، نوبت‌ها و کسر دستی</span>
        </div>

        {{-- Card 4: Quick Actions --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">عملیات سریع کیف پول</span>
                <span class="text-xs font-mono font-bold text-gray-400">{{ $faNum($txCount) }} تراکنش</span>
            </div>
            <div class="mt-3 flex items-center gap-2">
                @can('wallet.deposit')
                    <button type="button" @click="openWalletAction('deposit')"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>شارژ حساب</span>
                    </button>
                @endcan

                @can('wallet.withdraw')
                    <button type="button" @click="openWalletAction('withdraw')"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-sm shadow-rose-600/20 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        <span>کسر موجودی</span>
                    </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">ریز تراکنش‌های کیف پول</h3>
                    <p class="text-[11px] text-gray-400">تاریخچه کامل شارژ، کسر و پرداخت‌های انجام شده توسط این مشتری</p>
                </div>
            </div>

            @can('wallet.transactions.view')
                <a href="{{ route('user.wallet.transactions.index', ['search' => $client->id]) }}"
                   class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                    <span>مشاهده در بخش تراکنش‌ها</span>
                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endcan
        </div>

        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">کد پیگیری</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">نوع تراکنش</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">مبلغ ({{ $currencyLabel }})</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">مانده پس از تراکنش</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">توضیحات و بابت</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">تاریخ و زمان</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @foreach($transactions as $tx)
                        @php
                            $isPositive = in_array($tx->type->value, ['deposit', 'refund', 'commission', 'bonus']);
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ Str::limit($tx->uuid, 10) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $isPositive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-rose-50 text-rose-700 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60' }}">
                                    {{ $tx->type->label() }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tabular-nums">
                                {{ $isPositive ? '+' : '-' }}{{ $faNum(number_format((float)$tx->amount)) }}
                                <span class="text-[11px] font-normal text-gray-400 mr-0.5">{{ $currencyLabel }}</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300 tabular-nums">
                                {{ $faNum(number_format((float)$tx->balance_after)) }}
                                <span class="text-[11px] font-normal text-gray-400 mr-0.5">{{ $currencyLabel }}</span>
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $tx->description }}">
                                {{ $tx->description ?? '—' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $tx->created_at ? $faNum($toJalali($tx->created_at)->format('Y/m/d H:i')) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 mb-3">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">تراکنشی ثبت نشده است</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">تاکنون هیچ تراکنش مالی برای کیف پول این کلاینت ثبت نشده است.</p>
                @can('wallet.deposit')
                    <button type="button" @click="openWalletAction('deposit')"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>اولین شارژ کیف پول</span>
                    </button>
                @endcan
            </div>
        @endif
    </div>

    {{-- Direct Deposit/Withdraw Modal for this Client --}}
    <div x-show="showClientWalletModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="showClientWalletModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 dark:border-gray-700 relative space-y-5 text-gray-900 dark:text-gray-100"
             @click.outside="showClientWalletModal = false">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-xs"
                         :class="clientWalletMode === 'deposit' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400'">
                        <template x-if="clientWalletMode === 'deposit'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </template>
                        <template x-if="clientWalletMode === 'withdraw'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white"
                            x-text="clientWalletMode === 'deposit' ? 'شارژ کیف پول کلاینت' : 'کسر موجودی کیف پول کلاینت'"></h3>
                        <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">
                            مشتری: <span class="font-bold text-gray-700 dark:text-gray-200">{{ $client->full_name }}</span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showClientWalletModal = false" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form --}}
            <form :action="clientWalletMode === 'deposit' ? '{{ route('user.wallet.deposit') }}' : '{{ route('user.wallet.withdraw') }}'"
                  method="POST"
                  class="space-y-4">
                @csrf
                <input type="hidden" name="holder_type" value="client">
                <input type="hidden" name="holder_id" value="{{ $client->id }}">

                {{-- Amount --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        <span>مبلغ</span>
                        <span class="text-gray-400 text-[11px] font-normal">(به {{ $currencyLabel }})</span>
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="text"
                               name="amount_formatted"
                               x-model="walletAmount"
                               @input="walletAmount = formatNumber($event.target.value)"
                               placeholder="مثلاً: ۵۰۰,۰۰۰"
                               required
                               class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/70 px-4 py-2.5 text-base font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 pl-14">
                        <span class="absolute left-3.5 top-2.5 text-xs font-bold text-gray-400 dark:text-gray-500">{{ $currencyLabel }}</span>
                    </div>

                    {{-- Verbal translation --}}
                    <div x-show="walletAmount && unformatNumber(walletAmount) > 0" class="mt-1.5 text-[11px] font-bold"
                         :class="clientWalletMode === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        <span x-text="numberToWords(unformatNumber(walletAmount)) + ' {{ $currencyLabel }}'"></span>
                    </div>

                    {{-- Chips --}}
                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                        <template x-for="chip in clientQuickChips" :key="chip.value">
                            <button type="button"
                                    @click="addAmount(chip.value)"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                <span x-text="'+ ' + chip.label"></span>
                            </button>
                        </template>
                        <button type="button" @click="walletAmount = ''" class="px-2 py-1 rounded-lg text-[10px] font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            پاک کردن
                        </button>
                    </div>
                </div>

                {{-- Hidden input for actual amount submission --}}
                <input type="hidden" name="amount" :value="unformatNumber(walletAmount)">

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات و بابت (اختیاری)</label>
                    <input type="text"
                           name="description"
                           x-model="walletDescription"
                           placeholder="دلیل تراکنش..."
                           class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/70 px-4 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button"
                            @click="showClientWalletModal = false"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        انصراف
                    </button>
                    <button type="submit"
                            :disabled="!unformatNumber(walletAmount) || (clientWalletMode === 'withdraw' && unformatNumber(walletAmount) > {{ $balance }})"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="clientWalletMode === 'deposit' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20'"
                            x-text="clientWalletMode === 'deposit' ? 'ثبت و واریز شارژ' : 'ثبت و کسر موجودی'">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
