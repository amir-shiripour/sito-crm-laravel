@extends('layouts.user')

@section('title', 'لیست هزینه‌ها')

@section('content')
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-16 pt-8">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-red-600 text-white shadow-lg shadow-red-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                    </span>
                    لیست هزینه‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    تمام هزینه‌های ثبت شده در سیستم را مشاهده و مدیریت کنید.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.expenses.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    ثبت هزینه جدید
                </a>
            </div>
        </div>

        {{-- Expenses Table --}}
        <div class="bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">شرح هزینه</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">مبلغ</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">دسته‌بندی</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">حساب پرداختی</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">تاریخ</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">عملیات</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($documents as $expense)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($expense->description, 50) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600 dark:text-red-400 dir-ltr">{{ number_format($expense->amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->category?->title ?? '---' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->bank?->bank_name ?? '---' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ jdate($expense->document_date)->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="origin-top-left absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1">
                                                <a href="{{ route('admin.accounting.expenses.edit', $expense) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                                                    ویرایش
                                                </a>
                                                <form action="{{ route('admin.accounting.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('آیا از حذف این هزینه اطمینان دارید؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-right group flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg class="mr-3 h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        حذف
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
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 12H6" /></svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">هیچ هزینه‌ای یافت نشد</h3>
                                        <p class="text-gray-500 dark:text-gray-400">برای شروع، یک هزینه جدید ثبت کنید.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            @if($documents->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
