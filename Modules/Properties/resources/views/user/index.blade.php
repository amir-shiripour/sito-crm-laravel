@extends('layouts.user')

@php
    $title = 'لیست املاک';
@endphp

@section('content')
    <div class="space-y-4">
        <div class="flex flex-col-2 sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    لیست املاک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت املاک ثبت شده در سیستم
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('user.properties.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن ملک جدید
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">عنوان</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">قیمت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ایجاد کننده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($properties as $property)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                                {{ $property->id }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $property->title }}</span>
                            </td>

                            <td class="px-4 py-3">
                                {{ number_format($property->price) }} تومان
                            </td>

                            <td class="px-4 py-3">
                                @if($property->status)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs" style="background-color: {{ $property->status->color }}20; color: {{ $property->status->color }}">
                                        {{ $property->status->label }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ optional($property->creator)->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('user.properties.edit', $property) }}"
                                       class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                                       title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('user.properties.destroy', $property) }}" method="POST"
                                          onsubmit="return confirm('آیا از حذف این مورد اطمینان دارید؟');"
                                          class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
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
                            <td colspan="6" class="py-10 text-center text-gray-500">
                                هیچ ملکی یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($properties->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
