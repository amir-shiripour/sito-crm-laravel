@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'لیست فاکتورها')

@php
    $currencyLabel = CurrencyService::getBaseCurrency();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                    مدیریت فاکتورهای فروش
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">لیست، پیگیری و مدیریت فاکتورها و اسناد
                    فروش.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('accounting.invoices.create')
                    <a href="{{ route('admin.accounting.invoices.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        صدور فاکتور جدید
                    </a>
                @endcan
            </div>
        </div>

        {{-- Filter Bar --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <form method="GET" action="{{ route('admin.accounting.invoices.index') }}"
                  class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو بر اساس شماره فاکتور یا نام مشتری..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="xl:col-span-3">
                    <select name="status"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>پیش‌فاکتور
                            (پیش‌نویس)
                        </option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>پرداخت نشده
                        </option>
                        <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>
                            پرداخت جزئی
                        </option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>پرداخت شده</option>
                    </select>
                </div>
                <div class="xl:col-span-3">
                    <input type="text" name="date" value="{{ request('date') }}" placeholder="تاریخ (۱۴۰۴/۰۱/۰۱)"
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="xl:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="w-full py-3 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all">
                        اعمال فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'status', 'date']))
                        <a href="{{ route('admin.accounting.invoices.index') }}"
                           class="p-3 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-200 transition-all"
                           title="پاکسازی">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Invoices Table Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شماره فاکتور
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            طرف حساب / مشتری
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ کل ({{ $currencyLabel }})
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
                    @forelse($invoices as $invoice)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white tabular-nums">
                                #{{ $invoice->display_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                {{ $invoice->customer->full_name ?? $invoice->customer->name ?? 'مشتری عمومی' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 dark:text-white tabular-nums">
                                {{ number_format($invoice->total) }} <span
                                    class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium tabular-nums">
                                {{ jdate($invoice->issue_date)->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($invoice->status)
                                    @case('paid')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            پرداخت شده
                                        </span>
                                        @break
                                    @case('partially_paid')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                            پرداخت جزئی
                                        </span>
                                        @break
                                    @case('unpaid')
                                    @case('approved')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            پرداخت نشده
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
                                <div class="flex items-center justify-end gap-2">
                                    @can('accounting.invoices.view')
                                        <a href="{{ route('admin.accounting.invoices.show', $invoice) }}"
                                           class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                           title="مشاهده">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    @if($invoice->status == 'draft')
                                        @can('accounting.invoices.edit')
                                            <a href="{{ route('admin.accounting.invoices.edit', $invoice) }}"
                                               class="p-2.5 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110"
                                               title="ویرایش">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                                                </svg>
                                            </a>
                                            @endcan
                                            @can('accounting.invoices.delete')
                                                <form
                                                    action="{{ route('admin.accounting.invoices.destroy', $invoice) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('آیا از حذف این فاکتور اطمینان دارید؟');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all hover:scale-110"
                                                            title="حذف">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div
                                    class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                                    <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ فاکتوری ثبت نشده
                                    است</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">با صدور فاکتور
                                    جدید، اطلاعات مالی طرف حساب‌های شما در این بخش ثبت می‌شود.</p>
                                @can('accounting.invoices.create')
                                    <a href="{{ route('admin.accounting.invoices.create') }}"
                                       class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-1">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 4v16m8-8H4"/>
                                        </svg>
                                        صدور اولین فاکتور
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($invoices->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
