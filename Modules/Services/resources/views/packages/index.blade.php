@extends('layouts.user')

@section('title', 'لیست پکیج‌های سرویس و خدمات')

@php
    $currencyLabel = ($currency ?? 'toman') === 'rial' ? 'ریال' : 'تومان';
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500";
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                پکیج‌های سرویس و خدمات
            </h1>
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2">مدیریت پکیج‌های ترکیبی سرویس‌ها، تخفیفات
                پکیجی و اعمال مستقیم بر روی فاکتورها</p>
        </div>
        <a href="{{ route('services.packages.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-lg shadow-indigo-500/30 transition-all duration-300 active:scale-95">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            ایجاد پکیج جدید
        </a>
    </div>

    @if(session('success'))
        <div
            class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="{{ $cardClass }} p-5">
        <form method="GET" action="{{ route('services.packages.index') }}"
              class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="{{ $inputClass }}" placeholder="جستجو بر اساس عنوان یا کد پکیج...">
            </div>
            <div>
                <select name="status" class="{{ $inputClass }}">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                    اعمال فیلتر
                </button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('services.packages.index') }}"
                       class="py-2.5 px-4 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 text-sm font-bold rounded-xl hover:bg-gray-200 transition-all">
                        پاکسازی
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="{{ $cardClass }} overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-start border-collapse">
                <thead
                    class="bg-gray-50/80 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-start font-bold">عنوان پکیج</th>
                    <th class="px-6 py-4 text-center font-bold">کد پکیج</th>
                    <th class="px-6 py-4 text-center font-bold">تعداد سرویس‌ها</th>
                    <th class="px-6 py-4 text-center font-bold">مبلغ پایه</th>
                    <th class="px-6 py-4 text-center font-bold">تخفیف کلی پکیج</th>
                    <th class="px-6 py-4 text-center font-bold">قیمت نهایی پکیج</th>
                    <th class="px-6 py-4 text-center font-bold">وضعیت</th>
                    <th class="px-6 py-4 text-center font-bold">عملیات</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @forelse($packages as $package)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 align-middle font-bold text-gray-900 dark:text-white">
                            <div>
                                <a href="{{ route('services.packages.show', $package) }}"
                                   class="block text-sm font-black hover:text-indigo-600 transition-colors">
                                    {{ $package->name }}
                                </a>
                                @if($package->description)
                                    <span
                                        class="block text-xs font-normal text-gray-400 truncate max-w-xs mt-0.5">{{ $package->description }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle font-mono text-xs text-gray-600 dark:text-gray-300">
                            {{ $package->code ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                                <span
                                    class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-xs font-black border border-indigo-200/60 dark:border-indigo-500/20">
                                    {{ number_format($package->items_count) }} سرویس
                                </span>
                        </td>
                        <td class="px-6 py-4 text-center align-middle tabular-nums font-bold text-gray-600 dark:text-gray-300">
                            {{ number_format($package->total_amount) }} <span
                                class="text-[10px] font-normal text-gray-400">{{ $currencyLabel }}</span>
                        </td>
                        <td class="px-6 py-4 text-center align-middle tabular-nums font-bold text-rose-600 dark:text-rose-400">
                            @if($package->discount_value > 0)
                                @if($package->discount_type === 'percent')
                                    {{ number_format($package->discount_value) }}٪
                                @else
                                    {{ number_format($package->discount_value) }} <span
                                        class="text-[10px] font-normal text-gray-400">{{ $currencyLabel }}</span>
                                @endif
                            @else
                                بدون تخفیف
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-base">
                            {{ number_format($package->final_price) }} <span
                                class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if($package->status === 'active')
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-bold border border-emerald-200 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال
                                    </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> غیرفعال
                                    </span>
                            @endif
                        </td>
                            <td class="px-6 py-4 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('services.packages.show', $package) }}"
                                       class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors"
                                       title="مشاهده جزئیات">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('services.packages.edit', $package) }}"
                                       class="p-2 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors"
                                       title="ویرایش">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('services.packages.destroy', $package) }}" method="POST"
                                          onsubmit="return confirm('آیا از حذف این پکیج اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-xl text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                                                title="حذف">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                هیچ پکیجی ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packages->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700/50">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
