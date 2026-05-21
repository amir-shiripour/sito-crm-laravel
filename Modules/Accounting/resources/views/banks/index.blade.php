@extends('layouts.user')

@section('title', 'حساب‌های بانکی')

@php
    // Fallback gradient if no color is set for a bank
    $defaultGradient = 'from-gray-500 to-gray-600';
@endphp

@section('content')
    <div x-data="bankIndexPage()" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-16 pt-8">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </span>
                    حساب‌های بانکی
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    حساب‌های بانکی خود را مدیریت کرده و موجودی آن‌ها را مشاهده کنید.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openTransferModal()" type="button" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700 shadow-lg shadow-teal-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    انتقال بین بانکی
                </button>
                <a href="{{ route('admin.accounting.banks.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    افزودن حساب جدید
                </a>
            </div>
        </div>

        {{-- نمایش خطاهای اعتبارسنجی --}}
        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-xl shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mr-3">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300">
                            خطاهایی رخ داده است:
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Banks Grid --}}
        @if($banks->count() > 0)
            {{-- با حذف lg:grid-cols-3 تنظیم شد که همیشه نهایتا ۲ کارت در هر ردیف باشد --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100 max-w-5xl mx-auto lg:max-w-none">
                @foreach($banks as $bank)
                    <div class="relative w-full h-60 rounded-3xl shadow-2xl transform hover:-translate-y-1 hover:shadow-3xl transition-all duration-300 text-white">

                        {{-- لایه پس‌زمینه (جدا شده تا overflow-hidden باعث برش خوردن منوی دراپ‌داون نشود) --}}
                        <div class="absolute inset-0 rounded-3xl overflow-hidden"
                             style="background-image: linear-gradient(to bottom right, {{ $bank->color ?? '#333333' }}, {{ \Illuminate\Support\Str::of($bank->color ?? '#333333')->replace('#', '')->length() == 6 ? '#' . dechex(hexdec(\Illuminate\Support\Str::of($bank->color ?? '#333333')->replace('#', '')) + 0x101010) : '#444444' }})">
                            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTYwMCA4MDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbD0iI2ZmZmZmZiIgb3BhY2l0eT0iMC4wNSIgZD0iTTAgMGgxNjAwdjgwMEgwWiIvPjxwYXRoIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMDQiIGQ9Ik0wIDBoMTYwMHY4MDBIMHoiLz48cGF0aCBmaWxsPSIjZmZmZmZmIiBvcGFjaXR5PSIwLjAzIiBkPSJNMCAwSDE2MDB2ODAwSDBaIi8+PHBhdGggZmlsbD0iI2ZmZmZmZiIgb3BhY2l0eT0iMC4wMiIgZD0iTTAgMGgxNjAwdjgwMEgwWiIvPjxwYXRoIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMDEiIGQ9Ik0wIDBoMTYwMHY4MDBIMHoiLz48L3N2Zz4=');"></div>
                        </div>

                        {{-- لایه محتوا --}}
                        <div class="relative p-6 h-full flex flex-col justify-between z-10">

                            {{-- Top Row: Bank Name, Status & Actions --}}
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col items-start gap-2">
                                    <h3 class="text-xl font-black drop-shadow-md">{{ $bank->bank_name }}</h3>
                                    <span class="px-3 py-1 inline-flex text-xs leading-4 font-bold rounded-full shadow-sm {{ $bank->status ? 'bg-white/20 text-white' : 'bg-red-500/80 text-white' }}">
                                        {{ $bank->status ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </div>

                                {{-- Actions Dropdown (کاملا درون جریان صفحه قرار گرفته و دیگر روی متن نمی‌افتد) --}}
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-black/10 hover:bg-black/20 text-white focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="origin-top-left absolute left-0 mt-2 w-48 rounded-xl shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50 text-gray-900 dark:text-gray-300 overflow-hidden">
                                        <div class="py-1">
                                            <a href="{{ route('admin.accounting.banks.edit', $bank) }}" class="group flex items-center px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                                                ویرایش
                                            </a>
                                            <form action="{{ route('admin.accounting.banks.destroy', $bank) }}" method="POST" onsubmit="return confirm('آیا از حذف این حساب بانکی اطمینان دارید؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-right group flex items-center px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                    <svg class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Number --}}
                            @if($bank->card_number)
                                <div class="text-2xl font-mono tracking-[0.2em] mt-auto mb-4 text-center opacity-90 drop-shadow-sm dir-ltr">
                                    {{ chunk_split($bank->card_number, 4, ' ') }}
                                </div>
                            @else
                                <div class="text-base font-mono tracking-wider mt-auto mb-4 text-center opacity-60">
                                    شماره کارت موجود نیست
                                </div>
                            @endif

                            {{-- Account Holder Name & Balance --}}
                            <div class="flex justify-between items-end mt-2 pt-4 border-t border-white/20">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1">صاحب حساب</p>
                                    <p class="text-sm font-bold drop-shadow-sm">{{ $bank->account_holder_name }}</p>
                                </div>
                                <div class="text-left">
                                    <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1 text-right">موجودی</p>
                                    <p class="text-lg font-black dir-ltr drop-shadow-sm">{{ number_format($bank->balance) }} <span class="text-xs font-normal opacity-80">ریال</span></p>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
            {{-- Pagination --}}
            @if($banks->hasPages())
                <div class="mt-8">
                    {{ $banks->links() }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-900/50 backdrop-blur-sm rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl p-16 text-center animate-in fade-in zoom-in duration-500 max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 dark:bg-gray-800 mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">هیچ حساب بانکی یافت نشد</h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">برای شروع، یک حساب بانکی جدید اضافه کنید.</p>
            </div>
        @endif

        {{-- Transfer Modal --}}
        <div x-show="transferModalOpen"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div x-show="transferModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                 @click="transferModalOpen = false"></div>

            <div class="flex min-h-dvh items-center justify-center p-4 text-center sm:p-0">
                <div x-show="transferModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-800 border border-gray-100 dark:border-gray-700">

                    <form action="{{ route('admin.accounting.banks.transfer') }}" method="POST">
                        @csrf

                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-teal-50/50 dark:bg-teal-900/10">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                انتقال بین بانکی
                            </h3>
                            <button type="button" @click="transferModalOpen = false"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-6 space-y-5">
                            <div>
                                <label for="from_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">از حساب (مبدا) <span class="text-red-500">*</span></label>
                                <select id="from_bank_id" name="from_bank_id" x-model="fromBankId" @change="updateMaxAmount" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="">انتخاب حساب مبدا...</option>
                                    @foreach($allBanks as $bank)
                                        <option value="{{ $bank->id }}" data-balance="{{ $bank->balance }}">{{ $bank->bank_name }} ({{ $bank->account_number }}) - موجودی: {{ number_format($bank->balance) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="to_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">به حساب (مقصد) <span class="text-red-500">*</span></label>
                                <select id="to_bank_id" name="to_bank_id" x-model="toBankId" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="">انتخاب حساب مقصد...</option>
                                    <template x-for="bank in allBanksList" :key="bank.id">
                                        <option :value="bank.id" x-text="bank.name" x-show="bank.id != fromBankId"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">مبلغ انتقال (ریال) <span class="text-red-500">*</span></label>
                                <input type="text" name="amount" id="amount" x-model="amount" @input="handleAmountInput" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dir-ltr text-left" required>
                                <p x-show="maxAmount > 0" class="text-xs text-gray-500 mt-1 cursor-pointer hover:text-teal-600" @click="setFullAmount">
                                    حداکثر قابل انتقال: <span x-text="formatNumber(maxAmount)"></span> ریال (انتقال کل موجودی)
                                </p>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">توضیحات (اختیاری)</label>
                                <textarea name="description" id="description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-4 flex flex-row-reverse gap-2">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 sm:w-auto transition-colors items-center gap-2">
                                تایید انتقال
                            </button>
                            <button type="button"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600"
                                    @click="transferModalOpen = false">
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
            function bankIndexPage() {
                return {
                    transferModalOpen: false,
                    fromBankId: '',
                    toBankId: '',
                    amount: '',
                    maxAmount: 0,
                    allBanksList: [
                            @foreach($allBanks as $bank)
                        { id: '{{ $bank->id }}', name: '{{ $bank->bank_name }} ({{ $bank->account_number }})' },
                        @endforeach
                    ],

                    openTransferModal() {
                        this.fromBankId = '';
                        this.toBankId = '';
                        this.amount = '';
                        this.maxAmount = 0;
                        this.transferModalOpen = true;
                    },

                    updateMaxAmount() {
                        let select = document.getElementById('from_bank_id');
                        let option = select.options[select.selectedIndex];
                        this.maxAmount = option ? (parseFloat(option.getAttribute('data-balance')) || 0) : 0;

                        // Reset toBankId if it matches the new fromBankId
                        if (this.fromBankId === this.toBankId) {
                            this.toBankId = '';
                        }
                    },

                    setFullAmount() {
                        if (this.maxAmount > 0) {
                            this.amount = this.formatNumber(this.maxAmount);
                        }
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
                    },

                    handleAmountInput(e) {
                        let val = this.toEnglishDigits(e.target.value);
                        this.amount = this.formatNumber(val);
                    }
                }
            }
        </script>
    @endpush
@endsection
