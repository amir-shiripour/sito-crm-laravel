@extends('layouts.user')

@section('title', 'لیست چک‌ها')

@section('content')
    @includeIf('partials.jalali-date-picker')

    <div x-data="chequeIndexPage({ initialViewMode: 'list' })"> {{-- Add initialViewMode to Alpine data --}}
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-16 pt-8">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </span>
                        لیست چک‌ها
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        تمام چک‌های ثبت شده در سیستم را مشاهده و مدیریت کنید.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- View Mode Toggle --}}
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <button type="button" @click="viewMode = 'list'" :class="{ 'bg-blue-600 text-white': viewMode === 'list', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': viewMode !== 'list' }" class="px-4 py-2 text-sm font-medium border border-gray-200 dark:border-gray-600 rounded-l-lg hover:bg-blue-500 hover:text-white focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-white transition-colors">
                            <svg class="w-5 h-5 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                            لیستی
                        </button>
                        <button type="button" @click="viewMode = 'card'" :class="{ 'bg-blue-600 text-white': viewMode === 'card', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': viewMode !== 'card' }" class="px-4 py-2 text-sm font-medium border border-gray-200 dark:border-gray-600 rounded-r-lg hover:bg-blue-500 hover:text-white focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-white transition-colors">
                            <svg class="w-5 h-5 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            کارتی
                        </button>
                    </div>

                    <a href="{{ route('admin.accounting.cheques.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        ثبت چک جدید
                    </a>
                </div>
            </div>

            {{-- Cheques Table (List View) --}}
            <div x-show="viewMode === 'list'" class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">نوع</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">شماره چک</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">بانک</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">تاریخ سررسید</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">وضعیت</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">عملیات</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($cheques as $cheque)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($cheque->type === 'received')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">دریافتی</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">پرداختی</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white dir-ltr">{{ number_format($cheque->amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $cheque->cheque_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $cheque->bank_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ jdate($cheque->due_date)->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($cheque->isReconciled())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">وصول شده</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">{{ $cheque->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" class="origin-top-left absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10">
                                                <div class="py-1" role="menu" aria-orientation="vertical">
                                                    @if(!$cheque->isReconciled())
                                                        <a href="{{ route('admin.accounting.cheques.reconcile.form', $cheque) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            تعیین وضعیت (وصول / برگشت)
                                                        </a>
                                                        <a href="{{ route('admin.accounting.cheques.edit', $cheque) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                                                            ویرایش
                                                        </a>
                                                        <form action="{{ route('admin.accounting.cheques.destroy', $cheque) }}" method="POST" onsubmit="return confirm('آیا از حذف این چک اطمینان دارید؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="w-full text-right group flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <svg class="mr-3 h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                حذف
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.accounting.cheques.cancel-reconcile', $cheque) }}" method="POST" onsubmit="return confirm('آیا از لغو وصول این چک اطمینان دارید؟ این عملیات موجودی بانک را نیز تغییر خواهد داد.');">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="w-full text-right group flex items-center px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <svg class="mr-3 h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                                لغو وصول
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-16">
                                        <div class="max-w-md mx-auto">
                                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4 shadow-inner">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">هیچ چکی یافت نشد</h3>
                                            <p class="text-gray-500 dark:text-gray-400">برای شروع، یک چک جدید ثبت کنید.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if($cheques->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                        {{ $cheques->links() }}
                    </div>
                @endif
            </div>

            {{-- Cheques Card View --}}
            <div x-show="viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
                @forelse ($cheques as $cheque)
                    @include('accounting::cheques.partials._cheque_card', ['cheque' => $cheque])
                @empty
                    <div class="md:col-span-full text-center py-16">
                        <div class="max-w-md mx-auto">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 mb-4 shadow-inner">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">هیچ چکی یافت نشد</h3>
                            <p class="text-gray-500 dark:text-gray-400">برای شروع، یک چک جدید ثبت کنید.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination for Card View (if needed, duplicate or adjust existing) --}}
            <div x-show="viewMode === 'card'">
                @if($cheques->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                        {{ $cheques->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function chequeIndexPage(data) {
            return {
                viewMode: localStorage.getItem('chequesViewMode') || data.initialViewMode, // Load from local storage
                reconcileModalOpen: false,
                reconcileUrl: '',
                chequeType: '',

                init() {
                    this.$watch('viewMode', (value) => {
                        localStorage.setItem('chequesViewMode', value); // Save to local storage
                    });
                },
                openReconcileModal(chequeId, type) {
                    this.reconcileUrl = `{{ url('user/accounting/cheques') }}/${chequeId}/reconcile`;
                    this.chequeType = type;
                    this.reconcileModalOpen = true;
                }
            }
        }
    </script>
    @endpush
@endsection
