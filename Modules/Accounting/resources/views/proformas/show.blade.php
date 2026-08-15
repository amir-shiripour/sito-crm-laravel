@extends('layouts.user')

@section('title', 'جزئیات پیش فاکتور شماره ' . $proforma->proforma_number)

@php
    // Use the CurrencyService to format all monetary values
    use Modules\Accounting\App\Services\CurrencyService;
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-8 space-y-8 pb-24">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-sky-600 text-white shadow-lg shadow-sky-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </span>
                    پیش فاکتور شماره <span class="font-mono">{{ $proforma->proforma_number }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    جزئیات کامل پیش فاکتور و اقلام آن.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.proformas.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95">
                    بازگشت به لیست
                </a>
                <a href="{{ route('admin.accounting.proformas.print', $proforma) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-sky-600 text-white font-bold hover:bg-sky-700 shadow-lg shadow-sky-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    چاپ پیش فاکتور
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
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">نام:</span> <span class="font-bold">{{ $proforma->client->full_name ?? '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">شماره تماس:</span> <span class="dir-ltr inline-block">{{ $proforma->client->phone ?? '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">کد ملی/شناسه:</span> <span>{{ $proforma->client->national_code ?? '---' }}</span></p>
                    </div>
                </div>

                {{-- Seller Info --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">اطلاعات فروشنده</h3>
                    <div class="space-y-3">
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">نام:</span> <span class="font-bold">{{ $sellerInfo['name'] ?: '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">شماره تماس:</span> <span class="dir-ltr inline-block">{{ $sellerInfo['phone_fax'] ?: '---' }}</span></p>
                        <p class="text-gray-900 dark:text-white"><span class="font-medium text-gray-500 dark:text-gray-400 w-24 inline-block">آدرس:</span> <span>{{ $sellerInfo['province_city'] ? $sellerInfo['province_city'] . ' - ' : '' }}{{ $sellerInfo['address'] ?: '---' }}</span></p>
                    </div>
                </div>

                {{-- Invoice Meta --}}
                <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">تاریخ صدور</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $proforma->issue_date ? jdate($proforma->issue_date)->format('Y/m/d') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">تاریخ اعتبار</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $proforma->due_date ? jdate($proforma->due_date)->format('Y/m/d') : '---' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">وضعیت</p>
                        <p class="mt-1">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    'accepted' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $statusNames = [
                                    'draft' => 'پیشنویس',
                                    'sent' => 'ارسال شده',
                                    'accepted' => 'تایید و فاکتور شده',
                                    'rejected' => 'رد شده',
                                ];
                                $badgeClass = $statusColors[$proforma->status] ?? $statusColors['draft'];
                                $statusName = $statusNames[$proforma->status] ?? 'نامشخص';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm font-bold rounded-full {{ $badgeClass }}">
                                {{ $statusName }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">مبلغ کل</p>
                        <p class="text-xl font-black text-sky-600 dark:text-sky-400 mt-1 dir-ltr">{{ CurrencyService::formatWithSuffix($proforma->total_amount) }}</p>
                    </div>
                </div>

            </div>

            {{-- Items Table --}}
            <div class="border-t border-gray-100 dark:border-gray-700">
                <div class="px-8 py-4 bg-gray-50/50 dark:bg-gray-800/30">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">اقلام پیش فاکتور</h3>
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
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">قیمت واحد</th>
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">تخفیف</th>
                                <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ کل</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($proforma->items as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->item_code ?? '---' }}</td>
                                    <td class="px-8 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center">{{ $item->quantity + 0 }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">{{ $item->unit_type ?? '---' }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-left dir-ltr">{{ CurrencyService::formatWithSuffix($item->unit_price) }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400 text-left dir-ltr">{{ CurrencyService::formatWithSuffix($item->discount) }}</td>
                                    <td class="px-8 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white text-left dir-ltr">{{ CurrencyService::formatWithSuffix(($item->quantity * $item->unit_price) - $item->discount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary --}}
            <div class="border-t border-gray-100 dark:border-gray-700 p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    @if($proforma->notes)
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">یادداشت‌ها و شرایط</h3>
                        <div class="bg-amber-50 dark:bg-amber-900/10 border-l-4 border-amber-400 p-4 rounded-r-xl text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ $proforma->notes }}
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">جمع کل ردیف‌ها:</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">{{ CurrencyService::formatWithSuffix($proforma->subtotal) }}</span>
                    </div>

                    @php
                        $itemsDiscount = $proforma->items->sum('discount');
                        $totalDiscount = $proforma->discount + $itemsDiscount;
                    @endphp

                    @if($totalDiscount > 0)
                    <div class="flex justify-between items-center text-sm text-red-600 dark:text-red-400">
                        <span class="font-medium">جمع کل تخفیف‌ها:</span>
                        <span class="font-bold dir-ltr">- {{ CurrencyService::formatWithSuffix($totalDiscount) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">مبلغ مشمول مالیات:</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">{{ CurrencyService::formatWithSuffix($proforma->subtotal - $totalDiscount) }}</span>
                    </div>

                    @if($proforma->tax > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-400">مالیات ({{ $proforma->tax + 0 }}%):</span>
                        <span class="font-bold text-gray-900 dark:text-white dir-ltr">+ {{ CurrencyService::formatWithSuffix((($proforma->subtotal - $totalDiscount) * $proforma->tax) / 100) }}</span>
                    </div>
                    @endif

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                        <span class="text-lg font-black text-gray-900 dark:text-white">مبلغ نهایی:</span>
                        <span class="text-2xl font-black text-sky-600 dark:text-sky-400 dir-ltr">{{ CurrencyService::formatWithSuffix($proforma->total_amount) }}</span>
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="border-t border-gray-100 dark:border-gray-700 mt-8 pt-8 pb-4 flex flex-col items-end px-12">
                <div class="text-center">
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">مهر و امضای فروشنده</p>
                    @if(!empty($sellerInfo['stamp_signature_image']))
                        <div class="h-24 flex flex-col items-center justify-end">
                            <img src="{{ Storage::url($sellerInfo['stamp_signature_image']) }}" alt="مهر و امضا" class="max-h-20 object-contain mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:rounded-lg dark:p-1 mb-1" style="width: {{ $sellerInfo['stamp_signature_width'] ?: 'auto' }}px;">
                        </div>
                    @else
                        <div class="h-24 flex items-end justify-center">
                            <p class="text-xs font-bold text-gray-600 dark:text-gray-400 border-t border-dashed border-gray-400 pt-2 w-48 mx-auto"></p>
                        </div>
                    @endif
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-400 mt-2">{{ $sellerInfo['name'] ?: '---' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
