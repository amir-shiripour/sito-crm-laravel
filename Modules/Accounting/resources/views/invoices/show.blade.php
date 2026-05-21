@extends('layouts.user')

@section('title', 'جزئیات فاکتور شماره ' . $invoice->invoice_number)

@section('content')
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-8 space-y-8 pb-24">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </span>
                    فاکتور شماره <span class="font-mono">{{ $invoice->invoice_number }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    جزئیات کامل فاکتور، اقلام و سوابق پرداخت.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.invoices.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95">
                    بازگشت به لیست
                </a>
                <a href="{{ route('admin.accounting.invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    چاپ فاکتور
                </a>
            </div>
        </div>

        {{-- Invoice Details --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">

                {{-- Client Info --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">اطلاعات خریدار (مشتری)</h3>
                    <div class="space-y-3">
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">نام:</span> <span class="font-bold">{{ $invoice->client->full_name ?? '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">شماره تماس:</span> <span class="dir-ltr inline-block">{{ $invoice->client->phone ?? '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">کد ملی/شناسه:</span> <span>{{ $invoice->client->national_code ?? '---' }}</span></p>
                    </div>
                </div>

                {{-- Seller Info --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">اطلاعات فروشنده</h3>
                    <div class="space-y-3">
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">نام:</span> <span class="font-bold">{{ $sellerInfo['name'] ?: '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">شماره تماس:</span> <span class="dir-ltr inline-block">{{ $sellerInfo['phone_fax'] ?: '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">آدرس:</span> <span>{{ $sellerInfo['address'] ?: '---' }}</span></p>
                    </div>
                </div>

                {{-- Invoice Meta --}}
                <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">تاریخ صدور</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $invoice->issue_date ? jdate($invoice->issue_date)->format('Y/m/d') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">تاریخ سررسید</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $invoice->due_date ? jdate($invoice->due_date)->format('Y/m/d') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">وضعیت فاکتور</p>
                        <p class="mt-1">
                            <span class="px-3 py-1 inline-flex text-sm font-bold rounded-full {{ $invoice->status_badge_class }}">
                                {{ __('accounting::invoices.statuses.' . $invoice->status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">مبلغ کل (ریال)</p>
                        <p class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 dir-ltr">{{ number_format($invoice->total_amount) }}</p>
                    </div>
                </div>

            </div>

            {{-- Items Table --}}
            <div class="border-t border-gray-100 dark:border-gray-700">
                <div class="px-8 py-4 bg-gray-50/50 dark:bg-gray-800/30">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">اقلام فاکتور</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-white dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-8 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ردیف</th>
                                <th scope="col" class="px-8 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">کد کالا</th>
                                <th scope="col" class="px-8 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">شرح کالا / خدمات</th>
                                <th scope="col" class="px-8 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">تعداد</th>
                                <th scope="col" class="px-8 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">واحد</th>
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">قیمت واحد (ریال)</th>
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">تخفیف (ریال)</th>
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ کل (ریال)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($invoice->items as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->item_code ?? '---' }}</td>
                                    <td class="px-8 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center">{{ $item->quantity + 0 }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">{{ $item->unit_type ?? '---' }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-left dir-ltr">{{ number_format($item->unit_price) }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400 text-left dir-ltr">{{ number_format($item->discount) }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white text-left dir-ltr">{{ number_format($item->total_price - $item->discount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary --}}
            <div class="border-t border-gray-100 dark:border-gray-700 p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    @if($invoice->notes)
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">یادداشت‌ها و شرایط</h3>
                        <div class="bg-amber-50 dark:bg-amber-900/10 border-l-4 border-amber-400 p-4 rounded-r-xl text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ $invoice->notes }}
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">جمع کل ردیف‌ها:</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">{{ number_format($invoice->subtotal) }} ریال</span>
                    </div>

                    @if($invoice->discount > 0)
                    <div class="flex justify-between items-center text-sm text-red-600 dark:text-red-400">
                        <span class="font-medium">جمع تخفیف‌ها:</span>
                        <span class="font-bold dir-ltr">- {{ number_format($invoice->discount) }} ریال</span>
                    </div>
                    @endif

                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">مبلغ مشمول مالیات:</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">{{ number_format($invoice->subtotal - $invoice->discount) }} ریال</span>
                    </div>

                    @if($invoice->tax > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">مالیات ({{ $invoice->tax + 0 }}%):</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">+ {{ number_format((($invoice->subtotal - $invoice->discount) * $invoice->tax) / 100) }} ریال</span>
                    </div>
                    @endif

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                        <span class="text-lg font-black text-gray-900 dark:text-white">مبلغ نهایی:</span>
                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 dir-ltr">{{ number_format($invoice->total_amount) }} ریال</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History --}}
        @if($invoice->documents->count() > 0 || $invoice->cheques->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-200">
                <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">سوابق پرداخت</h2>
                </div>

                <div class="p-8">
                    <div class="space-y-4">
                        {{-- Cheques --}}
                        @foreach($invoice->cheques as $cheque)
                            <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">پرداخت با چک <span class="text-sm font-normal text-gray-500">(شماره: {{ $cheque->cheque_number }})</span></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">بانک {{ $cheque->bank_name }} - سررسید: {{ jdate($cheque->due_date)->format('Y/m/d') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-gray-900 dark:text-white dir-ltr">{{ number_format($cheque->amount) }} ریال</p>
                                    <p class="text-xs mt-1">
                                        @if($cheque->status === 'passed')
                                            <span class="text-emerald-600 font-bold">وصول شده در تاریخ {{ jdate($cheque->reconciliation_date)->format('Y/m/d') }}</span>
                                        @elseif($cheque->status === 'returned')
                                            <span class="text-red-600 font-bold">برگشت خورده</span>
                                        @else
                                            <span class="text-amber-600 font-bold">در انتظار سررسید / بررسی</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Other Documents (Cash, Transfer, POS) --}}
                        @foreach($invoice->documents as $doc)
                            <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                        @if($doc->payment_method === 'cash')
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        @elseif($doc->payment_method === 'card')
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">
                                            @if($doc->payment_method === 'cash') پرداخت نقدی
                                            @elseif($doc->payment_method === 'card') پرداخت با کارتخوان
                                            @else حواله / انتقال بانکی
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            واریز به: {{ $doc->bank->bank_name ?? '---' }}
                                            @if($doc->reference_number) | کد پیگیری: {{ $doc->reference_number }} @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-gray-900 dark:text-white dir-ltr">{{ number_format($doc->amount) }} ریال</p>
                                    <p class="text-xs mt-1 text-gray-500">{{ jdate($doc->document_date)->format('Y/m/d H:i') }}</p>
                                    @if($doc->attachment)
                                        <a href="{{ Storage::url($doc->attachment) }}" target="_blank" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">مشاهده فیش</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
