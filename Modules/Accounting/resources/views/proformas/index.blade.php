@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'لیست پیش‌فاکتورها')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-700 text-white shadow-lg shadow-sky-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                    مدیریت پیش‌فاکتورها
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">صدور، پیگیری و تبدیل پیش‌فاکتورها به فاکتور
                    نهایی فروش.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.proformas.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-sky-600 text-white font-bold text-sm shadow-md shadow-sky-500/30 hover:bg-sky-700 hover:shadow-sky-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    صدور پیش‌فاکتور جدید
                </a>
            </div>
        </div>

        {{-- Proformas Table Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شماره پیش‌فاکتور
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            طرف حساب / مشتری
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ کل ({{ CurrencyService::getBaseCurrency() }})
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            تاریخ صدور
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            وضعیت
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($proformas as $proforma)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white tabular-nums">
                                #{{ $proforma->proforma_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                {{ $proforma->client->full_name ?? $proforma->client->username ?? 'نامشخص' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 dark:text-white tabular-nums">
                                {{ CurrencyService::formatWithSuffix($proforma->total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium tabular-nums">
                                {{ jdate($proforma->issue_date)->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($proforma->status)
                                    @case('accepted')
                                    @case('converted')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            تایید و فاکتور شده
                                        </span>
                                        @break
                                    @case('sent')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                            ارسال شده
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            رد شده
                                        </span>
                                        @break
                                    @default
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            پیش‌نویس
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open" type="button"
                                            class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 5v.01M12 12v.01M12 19v.01"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false"
                                         class="origin-top-left absolute left-0 mt-2 w-48 rounded-2xl shadow-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 py-2 z-10">
                                        <a href="{{ route('admin.accounting.proformas.show', $proforma) }}"
                                           class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">مشاهده</a>
                                        <a href="{{ route('admin.accounting.proformas.print', $proforma) }}"
                                           target="_blank"
                                           class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">چاپ
                                            پیش‌فاکتور</a>
                                        <a href="{{ route('admin.accounting.proformas.edit', $proforma) }}"
                                           class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10">ویرایش</a>

                                        @if($proforma->status !== 'accepted' && $proforma->status !== 'converted')
                                            <form
                                                action="{{ route('admin.accounting.proformas.convertToInvoice', $proforma) }}"
                                                method="POST"
                                                onsubmit="return confirm('آیا از تبدیل این پیش‌فاکتور به فاکتور اطمینان دارید؟');">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full text-right flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                                                    تبدیل به فاکتور
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.accounting.proformas.destroy', $proforma) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از حذف این پیش‌فاکتور اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full text-right flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div
                                    class="w-24 h-24 rounded-full bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                                    <svg class="w-10 h-10 text-sky-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ پیش‌فاکتوری ثبت
                                    نشده است</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">پیش‌فاکتورها را
                                    قبل از نهایی شدن خرید صادر کرده و سپس به فاکتور تبدیل کنید.</p>
                                <a href="{{ route('admin.accounting.proformas.create') }}"
                                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-sky-600 text-white font-bold text-sm shadow-lg shadow-sky-500/30 hover:bg-sky-700 transition-all hover:-translate-y-1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    صدور اولین پیش‌فاکتور
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($proformas->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $proformas->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
