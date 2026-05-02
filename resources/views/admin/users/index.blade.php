@extends('layouts.user')

@php
    $title = 'مدیریت کاربران';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </span>
                    مدیریت کاربران
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مشاهده و مدیریت کاربران سیستم و دسترسی‌های آن‌ها</p>
            </div>

            {{-- دکمه افزودن کاربر جدید --}}
            @can('users.create')
                @if (Route::has('admin.users.create'))
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        افزودن کاربر جدید
                    </a>
                @endif
            @endcan
        </div>

        {{-- جدول لیست کاربران --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">کاربر</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">شماره تماس</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">نقش‌ها</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($users as $user)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            {{-- شناسه --}}
                            <td class="px-6 py-4 text-gray-400 dark:text-gray-500 font-mono text-xs font-semibold">
                                {{ $user->id }}
                            </td>

                            {{-- مشخصات کاربر و ایمیل --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0">
                                        {{ mb_substr($user->name ?? 'ک', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right mt-0.5">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- موبایل --}}
                            <td class="px-6 py-4">
                                <span class="font-mono text-gray-600 dark:text-gray-300 dir-ltr inline-block">
                                    {{ $user->mobile ?? '—' }}
                                </span>
                            </td>

                            {{-- نقش‌ها --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-[250px]">
                                    @forelse($user->roles as $r)
                                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-md border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200">
                                            {{ $r->display_name ?? $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- عملیات --}}
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @can('users.update')
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <p class="text-base font-medium text-gray-900 dark:text-white">هیچ کاربری یافت نشد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
