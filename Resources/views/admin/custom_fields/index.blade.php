{{-- resources/views/admin/custom_fields/index.blade.php --}}
@extends('layouts.user')
@php($title = 'مدیریت فیلدها')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">فیلدهای سفارشی</h1>
            @can('custom-fields.create')
                @if (Route::has('admin.custom-fields.create'))
                    <a href="{{ route('admin.custom-fields.create') }}"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                        + فیلد جدید
                    </a>
                @endif
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">نقش</th>
                    <th class="p-3 text-right">label</th>
                    <th class="p-3 text-right">field_name</th>
                    <th class="p-3 text-right">type</th>
                    <th class="p-3 text-right">required</th>
                    <th class="p-3 text-right">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @forelse($fields as $f)
                    <tr class="border-t border-gray-100 dark:border-gray-700/50">
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $f->id }}</td>
                        <td class="p-3 text-gray-900 dark:text-gray-100">{{ $f->role->display_name ?? $f->role_name }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $f->label }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200 font-mono">{{ $f->field_name }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $f->field_type }}</td>
                        <td class="p-3 text-gray-700 dark:text-gray-200">{{ $f->is_required ? 'بله' : 'خیر' }}</td>
                        <td class="p-3">
                            @can('custom-fields.update')
                                <a href="{{ route('admin.custom-fields.edit', $f) }}"
                                   class="text-indigo-600 dark:text-indigo-300 hover:underline">ویرایش</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endcan

                            @can('custom-fields.delete')
                                <form method="POST" action="{{ route('admin.custom-fields.destroy', $f) }}" class="inline"
                                      onsubmit="return confirm('حذف این فیلد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-300 hover:underline">حذف</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-500 dark:text-gray-400" colspan="7">فیلدی یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $fields->links() }}
        </div>
    </div>
@endsection
