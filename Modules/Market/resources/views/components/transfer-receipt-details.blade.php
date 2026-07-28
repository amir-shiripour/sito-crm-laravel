@props(['order'])

@php
    $metas = $order->meta ? $order->meta->pluck('value', 'key')->toArray() : [];
    $bankAccountJson = $metas['transfer_bank_account'] ?? null;
    $bankAccount = $bankAccountJson ? json_decode($bankAccountJson, true) : null;
    $senderName = $metas['transfer_sender_name'] ?? null;
    $senderMobile = $metas['transfer_mobile'] ?? null;
    $refNumber = $metas['transfer_ref_number'] ?? null;
    $paymentDate = $metas['transfer_payment_date'] ?? null;
    $receiptPath = $metas['transfer_receipt_path'] ?? null;
@endphp

@if($order->payment_method === 'transfer' && ($senderName || $refNumber || $bankAccount || $receiptPath))
<div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-5 text-right" dir="rtl">
    <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700">
        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات واریز بانکی / کارت به کارت</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">اطلاعات ثبت‌شده توسط خریدار جهت تأیید واریز</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        @if($senderName)
            <div class="bg-gray-50 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                <span class="text-gray-400 block mb-1">نام واریزکننده:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $senderName }}</span>
            </div>
        @endif

        @if($senderMobile)
            <div class="bg-gray-50 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                <span class="text-gray-400 block mb-1">شماره موبایل واریزکننده:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm dir-ltr text-right">{{ $senderMobile }}</span>
            </div>
        @endif

        @if($refNumber)
            <div class="bg-gray-50 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                <span class="text-gray-400 block mb-1">شماره مرجع / پیگیری:</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm dir-ltr text-right">{{ $refNumber }}</span>
            </div>
        @endif

        @if($paymentDate)
            <div class="bg-gray-50 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                <span class="text-gray-400 block mb-1">تاریخ واریز:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $paymentDate }}</span>
            </div>
        @endif

        @if($bankAccount)
            @php
                $ownerName = $bankAccount['owner_name'] ?? ($bankAccount['name'] ?? '');
                $bankName = $bankAccount['bank_name'] ?? 'بانک';
                $cardNumber = !empty($bankAccount['card_number']) ? preg_replace('/[^0-9]/', '', $bankAccount['card_number']) : '';
                $accountNumber = !empty($bankAccount['account_number']) ? trim($bankAccount['account_number']) : '';
                $ibanNumber = !empty($bankAccount['iban']) ? trim($bankAccount['iban']) : '';
            @endphp
            <div class="md:col-span-2 space-y-2">
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">حساب مقصد انتخابی:</span>
                <div class="relative overflow-hidden rounded-3xl p-5 text-white bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-900 border border-indigo-500/30 shadow-lg max-w-md space-y-3">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
                    
                    {{-- Header: Owner Name --}}
                    <div class="relative z-10 flex items-center justify-between border-b border-white/10 pb-2.5">
                        <div>
                            <span class="text-[9px] text-white/50 block font-medium">صاحب حساب:</span>
                            <span class="text-sm font-extrabold text-white tracking-wide">
                                {{ !empty($ownerName) ? $ownerName : $bankName }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/80 text-white border border-indigo-300/30">
                            واریز شده
                        </span>
                    </div>

                    {{-- Middle Section: Numbers Left-Aligned --}}
                    <div class="relative z-10 space-y-2 text-xs">
                        @if(!empty($cardNumber))
                            <div class="bg-black/25 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-white/10 dir-ltr text-left">
                                <span class="text-[9px] text-white/50 block text-right">شماره کارت:</span>
                                <span class="text-sm font-extrabold tracking-widest text-white block">
                                    {{ implode(' - ', str_split($cardNumber, 4)) }}
                                </span>
                            </div>
                        @endif

                        @if(!empty($accountNumber))
                            <div class="bg-black/25 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-white/10 dir-ltr text-left">
                                <span class="text-[9px] text-white/50 block text-right">شماره حساب:</span>
                                <span class="text-xs font-bold text-white block">
                                    {{ $accountNumber }}
                                </span>
                            </div>
                        @endif

                        @if(!empty($ibanNumber))
                            <div class="bg-black/25 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-white/10 dir-ltr text-left truncate">
                                <span class="text-[9px] text-white/50 block text-right">شماره شبا:</span>
                                <span class="text-xs font-bold text-white block truncate">
                                    {{ $ibanNumber }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Footer: Bank Name --}}
                    <div class="relative z-10 flex items-center justify-between pt-2 border-t border-white/10 text-xs">
                        <span class="text-[9px] text-white/60 font-medium">بانک صادرکننده:</span>
                        <span class="font-extrabold text-amber-300 uppercase drop-shadow text-xs">
                            {{ $bankName }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($receiptPath)
        <div class="pt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-2 font-bold">تصویر رسید پرداخت:</span>
            <a href="{{ Storage::url($receiptPath) }}" target="_blank" class="inline-block group relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-indigo-500 transition-all">
                <img src="{{ Storage::url($receiptPath) }}" alt="رسید پرداخت" class="max-h-64 object-contain rounded-2xl group-hover:scale-105 transition-transform duration-300" />
                <div class="absolute inset-0 bg-indigo-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    مشاهده اندازه کامل
                </div>
            </a>
        </div>
    @endif
</div>
@endif
