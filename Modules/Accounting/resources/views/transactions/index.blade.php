@extends('layouts.user')

@section('title', 'لیست درآمدها')

@section('content')
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-16 pt-8">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-green-600 text-white shadow-lg shadow-green-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    لیست درآمدها
                </h1>

            </div>

        </div>

        {{-- Incomes Table --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">منبع درآمد</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ (ریال)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">واریز به حساب</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">توضیحات</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if ($record->source_type === 'cheque')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                            چک وصول شده
                                        </span>
                                    @else
                                        @if(isset($record->invoice))
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">
                                                پرداخت فاکتور
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                درآمد نقدی / واریزی
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 dark:text-green-400 dir-ltr">
                                    + {{ number_format($record->amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $record->bank ? $record->bank->bank_name : '---' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    @if(isset($record->invoice))
                                        پرداخت برای فاکتور <span class="font-bold text-indigo-600">{{ $record->invoice->invoice_number }}</span>
                                    @else
                                        {{ $record->description }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record->date ? jdate($record->date)->format('Y/m/d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-16">
                                    <div class="max-w-md mx-auto">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 dark:bg-green-900/30 mb-4 shadow-inner">
                                            <svg class="w-8 h-8 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">هیچ درآمدی یافت نشد</h3>
                                        <p class="text-gray-500 dark:text-gray-400">هنوز هیچ تراکنش ورودی یا درآمدی در سیستم ثبت نشده است.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            @if($records->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
