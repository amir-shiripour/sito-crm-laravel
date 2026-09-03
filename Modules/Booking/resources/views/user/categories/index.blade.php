@extends('layouts.user')

@section('content')
    @php
        $serviceLabel = config('booking.labels.service', 'سرویس');
        $servicesLabel = config('booking.labels.services', 'سرویس‌ها');
    @endphp

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">دسته‌بندی‌های نوبت‌دهی</h1>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                            {{ $categories->total() }} دسته‌بندی
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">مدیریت گروه‌بندی و اولویت نمایش {{ $servicesLabel }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()?->can('booking.categories.create'))
                    <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 active:scale-[0.98] transition-all duration-200"
                       href="{{ route('user.booking.categories.create') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>ایجاد دسته‌بندی جدید</span>
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200/80 dark:border-emerald-700/60 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 px-5 py-3.5 shadow-xs">
                <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-800/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($categories->isEmpty())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">هنوز دسته‌بندی ایجاد نشده است</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">برای سازماندهی، مرتب‌سازی و نمایش بهتر {{ $servicesLabel }}، اولین دسته‌بندی را ایجاد کنید.</p>
                </div>
                @if(auth()->user()?->can('booking.categories.create'))
                    <a href="{{ route('user.booking.categories.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        <span>ایجاد اولین دسته‌بندی</span>
                    </a>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-sm text-right">
                        <thead class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700/60">
                            <tr>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300 w-16">#</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">نام دسته‌بندی</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">تعداد {{ $servicesLabel }}</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">ترتیب نمایش</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">وضعیت</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">سازنده</th>
                                <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300 text-left">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($categories as $category)
                                <tr class="group/row hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors duration-150">
                                    <td class="px-5 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                        {{ $category->id }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-900 dark:text-white">{{ $category->name }}</span>
                                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $category->slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                                            {{ $category->services_count ?? 0 }} {{ $serviceLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 text-xs">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700/60 font-semibold" title="اولویت نمایش: {{ $category->sort_order ?? 0 }}">
                                            {{ $category->sort_order ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($category->status === 'ACTIVE')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                فعال
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                غیرفعال
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 text-xs">
                                        {{ optional($category->creator)->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-left">
                                        <div class="flex items-center gap-1.5 justify-end">
                                            @if(auth()->user()?->can('booking.categories.edit') || auth()->user()?->can('booking.categories.manage'))
                                                <a href="{{ route('user.booking.categories.edit', $category) }}"
                                                   class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-colors"
                                                   title="ویرایش دسته‌بندی">
                                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            @if(auth()->user()?->can('booking.categories.delete') || auth()->user()?->can('booking.categories.manage'))
                                                <form method="POST" action="{{ route('user.booking.categories.destroy', $category) }}"
                                                      class="inline" onsubmit="return confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 transition-colors"
                                                            title="حذف دسته‌بندی">
                                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
