@extends('layouts.user')

@section('title', 'لیست فاکتورها')

@section('content')
    @includeIf('partials.jalali-date-picker')

    <div x-data="invoiceIndexPage()">
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-16 pt-8">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </span>
                        لیست فاکتورها
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.accounting.invoices.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        صدور فاکتور
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
                                خطاهایی در ثبت اطلاعات رخ داده است:
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

            {{-- Invoices Table --}}
            <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">شماره فاکتور</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مشتری</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ کل</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">تاریخ صدور</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">وضعیت</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">عملیات</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700 dark:text-gray-300">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->client->full_name ?? $invoice->client->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 dir-ltr font-bold">{{ number_format($invoice->total_amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ jdate($invoice->issue_date)->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status_badge_class }}">
                                            {{ __('accounting::invoices.statuses.' . $invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <div>
                                                <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="options-menu-{{ $invoice->id }}" aria-haspopup="true" aria-expanded="true">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                                </button>
                                            </div>

                                            <div x-show="open"
                                                 @click.away="open = false"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="origin-top-left absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none z-10"
                                                 role="menu" aria-orientation="vertical" aria-labelledby="options-menu-{{ $invoice->id }}">
                                                <div class="py-1" role="none">
                                                    <a href="{{ route('admin.accounting.invoices.show', $invoice) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                                                        <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                        مشاهده
                                                    </a>
                                                    <a href="{{ route('admin.accounting.invoices.edit', $invoice) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                                                        <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                                                        ویرایش
                                                    </a>

                                                    <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                                    @if($invoice->status !== 'paid')
                                                        <button @click="openPaymentModal({{ $invoice }}, '{{ route('admin.accounting.invoices.pay', $invoice) }}')" type="button" class="w-full text-right group flex items-center px-4 py-2 text-sm text-emerald-600 dark:text-emerald-400 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                                                            <svg class="mr-3 h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" /></svg>
                                                            ثبت پرداختی
                                                        </button>
                                                    @endif

                                                    @if(in_array($invoice->status, ['paid', 'partially_paid', 'pending_review']))
                                                        <form action="{{ route('admin.accounting.invoices.revert-payment', $invoice) }}" method="POST" onsubmit="return confirm('آیا از لغو پرداخت این فاکتور اطمینان دارید؟ تمام اسناد مالی و چک‌های مرتبط لغو خواهند شد.');">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="w-full text-right group flex items-center px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                                                                <svg class="mr-3 h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                                                لغو پرداخت
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                                    <form action="{{ route('admin.accounting.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('آیا از حذف این فاکتور اطمینان دارید؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-right group flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                                                            <svg class="mr-3 h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            حذف فاکتور
                                                        </button>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-16">
                                        <div class="max-w-md mx-auto">
                                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4 shadow-inner">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">هیچ صورتحسابی یافت نشد</h3>
                                            <p class="text-gray-500 dark:text-gray-400">برای شروع، یک صورتحساب جدید ایجاد کنید.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if($invoices->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 🔹 مودال ثبت پرداختی با طراحی جدید 🔹 --}}
        <div x-show="paymentModalOpen"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">

            {{-- پس‌زمینه تاریک --}}
            <div x-show="paymentModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                 @click="paymentModalOpen = false"></div>

            <div class="flex min-h-dvh items-center justify-center p-4 text-center sm:p-0">
                {{-- پنل مودال --}}
                <div x-show="paymentModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-800 border border-gray-100 dark:border-gray-700">

                    <form :action="paymentFormUrl" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- هدر مودال --}}
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                ثبت پرداخت صورتحساب
                            </h3>
                            <button type="button" @click="paymentModalOpen = false"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- بدنه فرم --}}
                        <div class="px-5 py-6 space-y-5 max-h-[70vh] overflow-y-auto">
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 flex items-center justify-between">
                                <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">شماره فاکتور:</span>
                                <span class="text-lg font-bold text-indigo-700 dark:text-indigo-400 font-mono" x-text="selectedInvoice ? selectedInvoice.invoice_number : ''"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">مبلغ (ریال) <span class="text-red-500">*</span></label>
                                    <input type="text" name="amount" id="amount" x-model="paymentAmount" @input="handleAmountInput" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dir-ltr text-left" required>
                                </div>
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">روش پرداخت <span class="text-red-500">*</span></label>
                                    <select x-model="paymentMethod" id="payment_method" name="payment_method" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                        <option value="transfer">حواله / کارت به کارت</option>
                                        <option value="cash">نقدی</option>
                                        <option value="card">کارت‌خوان (POS)</option>
                                        <option value="cheque">چک</option>
                                    </select>
                                </div>
                            </div>

                            {{-- بخش فیلدهای استاندارد پرداخت (غیر چک) --}}
                            <div x-show="paymentMethod !== 'cheque'" x-transition class="space-y-5">
                                <div>
                                    <label for="bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">واریز به حساب <span class="text-red-500">*</span></label>
                                    <select id="bank_id" name="bank_id" :required="paymentMethod !== 'cheque'" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        <option value="">انتخاب حساب بانکی...</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->account_number }})</option>
                                        @endforeach
                                    </select>
                                    @if($banks->isEmpty())
                                        <p class="text-xs text-red-500 mt-1">هیچ حساب بانکی فعالی در سیستم یافت نشد. لطفاً ابتدا یک حساب ثبت کنید.</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="reference_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">شماره مرجع / پیگیری تراکنش (اختیاری)</label>
                                    <input type="text" name="reference_number" id="reference_number" placeholder="مثال: 123456789" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                </div>

                                <div>
                                    <label for="attachment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">تصویر فیش واریزی (اختیاری)</label>
                                    <input type="file" name="attachment" id="attachment" accept="image/*" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <p class="text-xs text-gray-500 mt-1">فرمت‌های مجاز: JPG, PNG. حداکثر حجم: 2 مگابایت</p>
                                </div>
                            </div>

                            {{-- بخش فیلدهای ثبت چک --}}
                            <div x-show="paymentMethod === 'cheque'" x-transition class="space-y-5 bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-2">اطلاعات چک پس از ثبت به بخش «لیست چک‌ها» منتقل می‌شود.</p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="cheque_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">شماره چک <span class="text-red-500">*</span></label>
                                        <input type="text" name="cheque_number" id="cheque_number" :required="paymentMethod === 'cheque'" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dir-ltr text-left">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="cheque_payee_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">نام دریافت کننده (در وجه) <span class="text-red-500">*</span></label>
                                        <input type="text" name="cheque_payee_name" id="cheque_payee_name" :required="paymentMethod === 'cheque'" placeholder="مثال: شرکت مبنا" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    </div>
                                    <div>
                                        <label for="cheque_bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">نام بانک صادرکننده <span class="text-red-500">*</span></label>
                                        <input type="text" name="cheque_bank_name" id="cheque_bank_name" :required="paymentMethod === 'cheque'" placeholder="مثال: بانک ملی" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    </div>
                                    <div>
                                        <label for="cheque_due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">تاریخ سررسید چک <span class="text-red-500">*</span></label>
                                        <input type="text" data-jdp name="cheque_due_date" id="cheque_due_date" :required="paymentMethod === 'cheque'" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dir-ltr text-center">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="cheque_sayyad_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">شناسه صیادی (۱۶ رقم)</label>
                                        <input type="text" name="cheque_sayyad_id" id="cheque_sayyad_id" maxlength="16" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dir-ltr text-left">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- فوتر مودال --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-4 flex flex-row-reverse gap-2">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm sm:w-auto transition-colors items-center gap-2"
                                    :class="paymentMethod === 'cheque' ? 'bg-blue-600 hover:bg-blue-500' : 'bg-emerald-600 hover:bg-emerald-500'">
                                ثبت <span x-text="paymentMethod === 'cheque' ? 'چک' : 'پرداخت'"></span>
                            </button>

                            <button type="button"
                                    class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600"
                                    @click="paymentModalOpen = false">
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
        function invoiceIndexPage() {
            return {
                paymentModalOpen: false,
                selectedInvoice: null,
                paymentFormUrl: '',
                paymentMethod: 'transfer',
                paymentAmount: '',

                openPaymentModal(invoice, url) {
                    this.selectedInvoice = invoice;
                    this.paymentFormUrl = url;
                    this.paymentMethod = 'transfer';
                    // مقداری دهی پیش‌فرض با کل مبلغ فاکتور
                    this.paymentAmount = this.formatNumber(invoice.total_amount);
                    this.paymentModalOpen = true;
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
                    this.paymentAmount = this.formatNumber(val);
                }
            }
        }
    </script>
    @endpush
@endsection
