@extends('layouts.admin')
@php
    $title = 'مدیریت ماژول‌ها';
@endphp

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">ماژول‌ها</h1>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-right">نام</th>
                    <th class="px-4 py-2 text-right">توضیحات</th>
                    <th class="px-4 py-2 text-right">وضعیت (فیزیکی)</th>
{{--                    <th class="px-4 py-2 text-left">وضعیت (دیتابیس)</th>--}}
                    <th class="px-4 py-2 text-right">عملیات</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                @foreach($dbModules as $m)
                    <tr>
                        <td class="px-4 py-2">{{ $m->name }}</td>
                        <td class="px-4 py-2">{{ $m->description }}</td>
                        <td class="px-4 py-2">
                            @if(!$m->installed)
                                <span class="text-yellow-600 font-semibold">نیاز به نصب</span>
                            @else
                                <span class="{{ $m->active ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $m->active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if(!$m->installed)
                                <form method="POST" action="{{ route('admin.modules.install') }}">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $m->slug }}">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none">
                                        نصب
                                    </button>
                                </form>
                            @else
                                @if($m->active)
                                    <form method="POST" action="{{ route('admin.modules.disable') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="slug" value="{{ $m->slug }}">
                                        <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none">
                                            غیرفعال کردن
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.modules.enable') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="slug" value="{{ $m->slug }}">
                                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none">
                                            فعال کردن
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.modules.reset') }}" class="inline" onsubmit="return confirm('Reset will remove module data and reseed — are you sure?');">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $m->slug }}">
                                    <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none">
                                        ریست
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.modules.uninstall') }}" class="inline" onsubmit="return confirm('Uninstall will remove module files and DB tables — BACKUP first. Continue?');">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $m->slug }}">
                                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none">
                                        حذف
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
