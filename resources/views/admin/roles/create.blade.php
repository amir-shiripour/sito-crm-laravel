@extends('layouts.admin')
@php($title = 'ایجاد نقش')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نام نقش</label>
                <input name="name" value="{{ old('name') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">مجوزها</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 p-2 border rounded-lg text-sm bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                @checked(collect(old('permissions',[]))->contains($perm))>
                            <span>{{ $perm }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">ایجاد</button>
                <a href="{{ route('admin.roles.index') }}"
                   class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                    بازگشت
                </a>
            </div>
        </form>
    </div>
@endsection
