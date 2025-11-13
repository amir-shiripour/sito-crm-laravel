@extends('layouts.user')
@php($title = 'لیست '.config('clients.labels.plural'))

@section('content')
    <div class="space-y-4">
        {{-- هدر و ابزارها --}}
        <div class="flex flex-col-2 sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ config('clients.labels.plural', 'مشتریان') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت و مشاهده لیست کامل {{ config('clients.labels.plural', 'مشتریان') }}
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                {{-- دکمه ایجاد کامل --}}
                @can('clients.create')
                    @if (Route::has('user.clients.create'))
                        <a href="{{ route('user.clients.create') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ 'ایجاد ' . config('clients.labels.singular', 'مشتری') }}
                        </a>
                    @endif
                @endcan

                {{-- ویجت ایجاد سریع (Livewire) --}}
                @livewire('clients.form', ['asQuickWidget' => true], key('clients-quick-widget'))
            </div>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">اطلاعات کاربری</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تماس</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ایجاد کننده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($clients as $client)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            {{-- ID --}}
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                                {{ $client->id }}
                            </td>

                            {{-- User Info --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $client->full_name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">@ {{ $client->username }}</span>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1 text-xs">
                                    @if($client->email)
                                        <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300 dir-ltr text-right">
                                            <span class="opacity-70">✉️</span> {{ $client->email }}
                                        </div>
                                    @endif
                                    @if($client->phone)
                                        <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300 dir-ltr text-right">
                                            <span class="opacity-70">📞</span> {{ $client->phone }}
                                        </div>
                                    @endif
                                    @if(!$client->email && !$client->phone)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Creator --}}
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                @if(optional($client->creator)->name)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs">
                                        {{ $client->creator->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @can('clients.view')
                                        <a href="{{ route('user.clients.show', $client) }}"
                                           class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                           title="مشاهده">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('clients.edit')
                                        <a href="{{ route('user.clients.edit', $client) }}"
                                           class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('clients.delete')
                                        <form action="{{ route('user.clients.destroy', $client) }}" method="POST"
                                              onsubmit="return confirm('آیا از حذف این مورد اطمینان دارید؟');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                    title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="text-base font-medium">هیچ مشتری‌ای یافت نشد</p>
                                    <p class="text-sm mt-1">می‌توانید یک مشتری جدید اضافه کنید.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه بندی --}}
            @if($clients->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
