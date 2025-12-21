@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">دسته‌بندی‌های نوبت‌دهی</h1>

            @if(auth()->user()?->can('booking.categories.create'))
                <a class="px-4 py-2 bg-blue-600 text-white rounded" href="{{ route('user.booking.categories.create') }}">
                    ایجاد دسته‌بندی
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded border overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">نام</th>
                    <th class="p-3 text-right">وضعیت</th>
                    <th class="p-3 text-right">سازنده</th>
                    <th class="p-3 text-right">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($categories as $category)
                    <tr class="border-t">
                        <td class="p-3">{{ $category->id }}</td>
                        <td class="p-3 font-medium">{{ $category->name }}</td>
                        <td class="p-3">{{ $category->status }}</td>
                        <td class="p-3">{{ optional($category->creator)->name ?? '-' }}</td>
                        <td class="p-3 space-x-2 space-x-reverse">
                            @if(auth()->user()?->can('booking.categories.edit') || auth()->user()?->can('booking.categories.manage'))
                                <a class="text-blue-600 hover:underline" href="{{ route('user.booking.categories.edit', $category) }}">ویرایش</a>
                            @endif

                            @if(auth()->user()?->can('booking.categories.delete') || auth()->user()?->can('booking.categories.manage'))
                                <form method="POST" action="{{ route('user.booking.categories.destroy', $category) }}" class="inline"
                                      onsubmit="return confirm('حذف این دسته‌بندی انجام شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">حذف</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
@endsection
