@extends('layouts.user')

@section('title', 'سرفصل‌های حسابداری')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </span>
                درخت سرفصل‌های حسابداری (کدینگ)
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">مدیریت حساب‌های کل، معین و تفصیلی (دارایی‌ها، بدهی‌ها، سرمایه، درآمد و هزینه).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounting.categories.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                افزودن سرفصل جدید
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-bold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4  xl:gap-5 w-full">
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 ps-5 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="جستجو: عنوان سرفصل، کد حساب..."
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-12 pe-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
            </div>
            <div class="w-full">
                <select name="type" class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                    <option value="">همه ماهیت‌ها</option>
                    <option value="asset" @selected(request('type') == 'asset')>دارایی</option>
                    <option value="liability" @selected(request('type') == 'liability')>بدهی</option>
                    <option value="equity" @selected(request('type') == 'equity')>سرمایه</option>
                    <option value="income" @selected(request('type') == 'income')>درآمد</option>
                    <option value="expense" @selected(request('type') == 'expense')>هزینه</option>
                </select>
            </div>
            <div class="w-full">
                <select name="status" class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1" @selected(request('status') === '1')>فعال</option>
                    <option value="0" @selected(request('status') === '0')>غیرفعال</option>
                </select>
            </div>
            <div class="flex gap-2 w-full">
                <button type="submit" class="flex-1 px-6 py-3.5 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    فیلتر
                </button>
                @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('admin.accounting.categories.index') }}" title="پاک کردن فیلترها"
                       class="px-5 py-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Categories Table Card --}}
    <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col" class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">کد حساب</th>
                        <th scope="col" class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">عنوان سرفصل</th>
                        <th scope="col" class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">نوع ماهیت</th>
                        <th scope="col" class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">وضعیت</th>
                        <th scope="col" class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($categories as $category)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-500 dark:text-gray-400">
                                {{ $category->account_code ?? $category->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2" style="margin-right: {{ ($category->level ?? 0) * 1.5 }}rem">
                                    @if(($category->level ?? 0) > 0)
                                        <span class="text-gray-300 dark:text-gray-600">└─</span>
                                    @endif
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $category->title }}</span>
                                    @if($category->is_system)
                                        <span title="سرفصل سیستمی غیرقابل حذف" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($category->type)
                                    @case('asset')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                                            دارایی
                                        </span>
                                        @break
                                    @case('liability')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">
                                            بدهی
                                        </span>
                                        @break
                                    @case('equity')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            سرمایه
                                        </span>
                                        @break
                                    @case('income')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            درآمد
                                        </span>
                                        @break
                                    @case('expense')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                            هزینه
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $category->type }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->status)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        فعال
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        غیرفعال
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.accounting.reports.ledger', $category->id) }}" class="p-2.5 rounded-xl text-gray-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-all hover:scale-110" title="دفتر معین / گردش حساب">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </a>

                                    <a href="{{ route('admin.accounting.categories.edit', $category->id) }}" class="p-2.5 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110" title="ویرایش">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                                    </a>

                                    @if(!$category->is_system)
                                        <form action="{{ route('admin.accounting.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('آیا از حذف این سرفصل اطمینان دارید؟');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all hover:scale-110" title="حذف">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                                    <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ سرفصلی تعریف نشده است</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">سرفصل‌های حسابداری ساختار کدینگ کل و معین مجموعه شما را تشکیل می‌دهند.</p>
                                <a href="{{ route('admin.accounting.categories.create') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    ایجاد اولین سرفصل
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
