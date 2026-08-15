@extends('layouts.user')

@section('title', 'لیست هزینه‌ها')

@php
    use Modules\Accounting\App\Services\CurrencyService;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/></svg>
                </span>
                    مدیریت هزینه‌ها
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">ثبت و دسته‌بندی هزینه‌های جاری، قبوض، خدمات و
                    مخارج
                    کلینیک.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.expenses.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-rose-600 text-white font-bold text-sm shadow-md shadow-rose-500/30 hover:bg-rose-700 hover:shadow-rose-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    ثبت هزینه جدید
                </a>
            </div>
        </div>

        @if(session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/50 flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div
                class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-800 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/50 space-y-1 shadow-xs">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-bold text-sm">{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.accounting.expenses.index') }}"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="relative md:col-span-6 lg:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: شرح هزینه، شماره پیگیری، سند..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="md:col-span-3 lg:col-span-2">
                    <select name="payment_type"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه روش‌های پرداخت</option>
                        <option value="bank" @selected(request('payment_type') == 'bank')>خزانه‌داری (بانک / صندوق)
                        </option>
                        <option value="cheque" @selected(request('payment_type') == 'cheque')>چک صیادی</option>
                        <option value="split" @selected(request('payment_type') == 'split')>پرداخت ترکیبی (چک +
                            خزانه‌داری)
                        </option>
                    </select>
                </div>
                <div class="md:col-span-3 lg:col-span-2">
                    <select name="category_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه سرفصل‌ها</option>
                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-6 lg:col-span-2">
                    <select name="fund_account_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه حساب‌های خزانه‌داری</option>
                        @foreach($fundAccounts as $fa)
                            <option
                                value="{{ $fa->id }}" @selected(request('fund_account_id') == $fa->id)>{{ $fa->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-6 lg:col-span-2 flex gap-2">
                    <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'payment_type', 'category_id', 'fund_account_id']))
                        <a href="{{ route('admin.accounting.expenses.index') }}" title="پاک کردن فیلترها"
                           class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شرح هزینه
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ ({{ CurrencyService::getBaseCurrency() }})
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            سرفصل
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            روش پرداخت
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            پرداخت از
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مانده پس از پرداخت
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            تاریخ
                        </th>
                        <th scope="col"
                            class="px-3.5 py-3.5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse ($documents as $expense)
                        @php
                            $debitTx = $expense->transactions->firstWhere('debit', '>', 0) ?? $expense->transactions->first();
                            $bankTx = $expense->transactions->firstWhere('fund_account_id', '!=', null);
                            $creditTx = $bankTx ?? $expense->transactions->firstWhere('credit', '>', 0);
                            $expenseAmount = $debitTx?->debit ?? $expense->transactions->sum('debit');
                            $categoryName = $debitTx?->category?->title ?? 'سرفصل عمومی';
                            $fundAccountName = $bankTx?->fundAccount?->bank_display_name ?? $creditTx?->fundAccount?->bank_display_name ?? '—';
                            $balAfter = $bankTx?->account_balance_after ?? $creditTx?->account_balance_after;

                            $attachedCheques = $expense->cheques->count() > 0 ? $expense->cheques : ($expense->cheque ? collect([$expense->cheque]) : collect());
                            $chequesCount = $attachedCheques->count();
                            $chequeNumbers = $attachedCheques->pluck('cheque_number')->toArray();

                            $hasBank = $bankTx !== null;
                            $hasCheques = $chequesCount > 0;

                            $isSplitPayment = $hasCheques && $hasBank;
                            $isMultiCheque = $chequesCount > 1 && !$hasBank;
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200 @if($expense->status === 'cancelled') bg-gray-50/50 dark:bg-gray-900/20 opacity-75 @endif">
                            <td class="px-3.5 py-3.5 font-bold text-gray-900 dark:text-white max-w-[180px]">
                                <div class="flex items-center gap-1.5 truncate">
                                    <a href="{{ route('admin.accounting.expenses.show', $expense) }}"
                                       title="{{ $expense->description }}"
                                       class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate @if($expense->status === 'cancelled') line-through text-gray-400 dark:text-gray-500 @endif">
                                        {{ Str::limit($expense->description, 35) }}
                                    </a>
                                    @if($expense->status === 'cancelled')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                            لغو
                                        </span>
                                    @endif
                                    @if($expense->attachment)
                                        <a href="{{ Storage::url($expense->attachment) }}" target="_blank"
                                           title="مشاهده ضمیمه"
                                           class="text-indigo-500 hover:text-indigo-700 transition-colors shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap font-black @if($expense->status === 'cancelled') text-gray-400 dark:text-gray-500 line-through @else text-rose-600 dark:text-rose-400 @endif tabular-nums">
                                {{ CurrencyService::formatWithSuffix($expenseAmount) }}
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                {{ $categoryName }}
                            </span>
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                                @if($isSplitPayment)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-gradient-to-r from-purple-50 via-amber-50 to-indigo-50 text-purple-800 dark:from-purple-900/30 dark:via-amber-900/20 dark:to-indigo-900/30 dark:text-purple-300 border border-purple-200/60 dark:border-purple-500/30 shadow-xs">
                                        ترکیبی
                                    </span>
                                @elseif($isMultiCheque)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200/50 dark:border-amber-500/20 shadow-xs">
                                        چند چکی ({{ $faNum($chequesCount) }})
                                    </span>
                                @elseif($hasCheques)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-200/50 dark:border-amber-500/20 shadow-xs">
                                        چک صیادی
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20 shadow-xs">
                                        بانک / صندوق
                                    </span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300 max-w-[160px] truncate">
                                @if($isSplitPayment)
                                    <div class="flex flex-col gap-0.5 text-xs truncate">
                                        <span class="text-amber-800 dark:text-amber-300 font-bold truncate">
                                            چک: {{ $faNum(implode('، ', $chequeNumbers)) }}
                                        </span>
                                        <span class="text-indigo-700 dark:text-indigo-300 font-bold truncate">
                                            {{ $fundAccountName }}
                                        </span>
                                    </div>
                                @elseif($isMultiCheque)
                                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 truncate block">
                                        چک: {{ $faNum(implode('، ', $chequeNumbers)) }}
                                    </span>
                                @elseif($hasCheques)
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate block">
                                        چک: {{ $faNum($chequeNumbers[0] ?? '') }}
                                    </span>
                                @else
                                    <span class="font-bold text-gray-900 dark:text-white truncate block" title="{{ $fundAccountName }}">{{ $fundAccountName }}</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap">
                                @if(!is_null($balAfter))
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-bold text-xs">
                                        <span class="tabular-nums font-black">{{ $faNum(number_format(CurrencyService::convertForDisplay($balAfter))) }}</span>
                                        <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 ms-0.5">{{ CurrencyService::getBaseCurrency() }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 font-mono">—</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium tabular-nums text-xs">
                                {{ jdate($expense->document_date)->format('Y/m/d') }}
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap text-end">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.accounting.expenses.show', $expense) }}"
                                       class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all"
                                       title="مشاهده جزئیات">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($expense->status === 'cancelled')
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 dark:text-gray-500">
                                            لغو شده
                                        </span>
                                    @else
                                        <a href="{{ route('admin.accounting.expenses.edit', $expense) }}"
                                           class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.accounting.expenses.cancel', $expense) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از لغو این هزینه و بازگشت مبلغ به خزانه‌داری اطمینان دارید؟');"
                                              class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="p-2 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all"
                                                    title="لغو هزینه">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-24 text-center">
                                <div
                                    class="w-24 h-24 rounded-full bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                                    <svg class="w-10 h-10 text-rose-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M18 12H6"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ هزینه‌ای با این
                                    مشخصات
                                    یافت نشد</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">عبارت یا
                                    فیلترهای
                                    انتخابی را تغییر داده یا فیلترها را پاک کنید.</p>
                                <a href="{{ route('admin.accounting.expenses.index') }}"
                                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition-all">
                                    پاک کردن فیلترها
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
@endsection
