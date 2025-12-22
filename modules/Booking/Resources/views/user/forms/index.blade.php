@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">فرم‌های نوبت‌دهی</h1>

            @if(auth()->user()?->can('booking.forms.create'))
                <a class="px-4 py-2 bg-blue-600 text-white rounded" href="{{ route('user.booking.forms.create') }}">
                    ایجاد فرم
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
                    <th class="p-3 text-right">تعداد فیلد</th>
                    <th class="p-3 text-right">سازنده</th>
                    <th class="p-3 text-right">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($forms as $form)
                    @php
                        $fieldCount = is_array($form->schema_json['fields'] ?? null)
                            ? count($form->schema_json['fields'])
                            : 0;
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">{{ $form->id }}</td>
                        <td class="p-3 font-medium">{{ $form->name }}</td>
                        <td class="p-3">{{ $form->status }}</td>
                        <td class="p-3">{{ $fieldCount }}</td>
                        <td class="p-3">{{ optional($form->creator)->name ?? '-' }}</td>
                        <td class="p-3 space-x-2 space-x-reverse">
                            @if(auth()->user()?->can('booking.forms.edit') || auth()->user()?->can('booking.forms.manage'))
                                <a class="text-blue-600 hover:underline" href="{{ route('user.booking.forms.edit', $form) }}">ویرایش</a>
                            @endif

                            @if(auth()->user()?->can('booking.forms.delete') || auth()->user()?->can('booking.forms.manage'))
                                <form method="POST" action="{{ route('user.booking.forms.destroy', $form) }}" class="inline"
                                      onsubmit="return confirm('حذف این فرم انجام شود؟');">
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

        {{ $forms->links() }}
    </div>
@endsection
