@extends('layouts.user')
@php($title = 'لیست '.config('clients.labels.plural'))


@section('content')

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        {{-- هدر جدول --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">
                {{ config('clients.labels.plural', 'مشتریان') }}
            </h1>

            @can('clients.create')
                @if (Route::has('user.clients.create'))
                    <a href="{{ route('user.clients.create') }}"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                        + {{ 'ایجاد ' . config('clients.labels.singular', 'مشتری') }}
                    </a>
                @endif
            @endcan
        </div>

        {{-- جدول --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">نام</th>
                    <th class="p-3 text-right">ایمیل</th>
                    <th class="p-3 text-right">تلفن</th>
                    <th class="p-3 text-right">ایجادکننده</th>
                    <th class="p-3 text-right">عملیات</th>
                </tr>
                </thead>

                <tbody>
                @forelse($clients as $client)
                    <tr class="border-t border-gray-100 dark:border-gray-700/50">
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $client->id }}</td>
                        <td class="p-3 text-gray-900 dark:text-gray-100">{{ $client->name }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $client->email ?? '—' }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $client->phone ?? '—' }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">
                            {{ optional($client->creator)->name ?? '—' }}
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @can('clients.view')
                                    <a href="{{ route('user.clients.show', $client) }}"
                                       class="text-blue-600 dark:text-blue-300 hover:underline">نمایش</a>
                                @endcan

                                @can('clients.edit')
                                    <a href="{{ route('user.clients.edit', $client) }}"
                                       class="text-indigo-600 dark:text-indigo-300 hover:underline">ویرایش</a>
                                @endcan

                                @can('clients.delete')
                                    <form action="{{ route('user.clients.destroy', $client) }}" method="POST"
                                          onsubmit="return confirm('حذف شود؟');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 dark:text-red-400 hover:underline">
                                            حذف
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-500 dark:text-gray-400" colspan="6">
                            {{ config('clients.labels.plural', 'مشتریان') }}ی یافت نشد.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- صفحه‌بندی --}}
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $clients->links() }}
        </div>
    </div>
@endsection
