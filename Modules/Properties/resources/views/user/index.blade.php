@extends('layouts.user')

@php
    $title = 'لیست املاک';
    // استایل‌های مشترک
    $badgeClass = "inline-flex items-center px-2 py-1 rounded-md text-xs font-medium";
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    لیست املاک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    مدیریت و مشاهده وضعیت املاک ثبت شده
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('user.properties.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن ملک جدید
                </a>
            </div>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">ملک</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">قیمت</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">نوع</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">ایجاد کننده</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($properties as $property)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">

                            {{-- عنوان و تصویر --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                        @if($property->cover_image)
                                            <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover" alt="{{ $property->title }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white line-clamp-1 max-w-[200px]" title="{{ $property->title }}">{{ $property->title }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">Code: {{ $property->code }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- قیمت --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @if($property->listing_type == 'sale' || $property->listing_type == 'presale')
                                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ number_format($property->price) }} <span class="text-xs text-gray-500">تومان</span>
                                        </span>
                                    @elseif($property->listing_type == 'rent')
                                        <div class="text-xs text-gray-500">
                                            <div>رهن: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($property->deposit_price) }}</span></div>
                                            <div>اجاره: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($property->rent_price) }}</span></div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- نوع معامله --}}
                            <td class="px-6 py-4">
                                @php
                                    $typeClass = match($property->listing_type) {
                                        'sale' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
                                        'rent' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                                        'presale' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
                                        default => 'bg-gray-50 text-gray-700 border-gray-100'
                                    };
                                    $typeLabel = match($property->listing_type) {
                                        'sale' => 'فروش',
                                        'rent' => 'رهن و اجاره',
                                        'presale' => 'پیش‌فروش',
                                        default => $property->listing_type
                                    };
                                @endphp
                                <span class="{{ $badgeClass }} border {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            {{-- وضعیت --}}
                            <td class="px-6 py-4">
                                @if($property->status)
                                    <span class="{{ $badgeClass }}"
                                          style="background-color: {{ $property->status->color }}15; color: {{ $property->status->color }}; border: 1px solid {{ $property->status->color }}30;">
                                        {{ $property->status->label ?? $property->status->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- ایجاد کننده --}}
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] text-gray-500">
                                        {{ mb_substr(optional($property->creator)->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-xs">{{ optional($property->creator)->name ?? 'نامشخص' }}</span>
                                </div>
                            </td>

                            {{-- عملیات --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('properties.show', $property->slug) }}" target="_blank"
                                       class="p-2 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 transition-colors"
                                       title="مشاهده در سایت">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('user.properties.edit', $property) }}"
                                       class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors"
                                       title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('user.properties.destroy', $property) }}" method="POST"
                                          onsubmit="return confirm('آیا از حذف این ملک اطمینان دارید؟');"
                                          class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors"
                                                title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    <p class="text-base font-medium text-gray-900 dark:text-white">هیچ ملکی یافت نشد</p>
                                    <p class="text-sm mt-1">اولین ملک خود را ثبت کنید.</p>
                                    <a href="{{ route('user.properties.create') }}" class="mt-4 text-indigo-600 hover:underline text-sm font-bold">افزودن ملک جدید</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($properties->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
