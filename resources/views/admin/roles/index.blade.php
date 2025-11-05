@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">نقش‌ها</h1>
            <a href="{{ route('admin.roles.create') }}" class="px-3 py-2 bg-gray-900 text-white rounded">نقش جدید</a>
        </div>

        @if (session('success'))
            <div class="mt-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        @error('role')
        <div class="mt-4 p-3 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full border">
                <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-right border">نام</th>
                    <th class="p-2 text-right border">تعداد کاربران</th>
                    <th class="p-2 text-right border">عملیات</th>
                </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr class="border-b">
                        <td class="p-2 border">{{ $role->name }}</td>
                        <td class="p-2 border">{{ $roleUserCounts[$role->name] ?? 0 }}</td>
                        <td class="p-2 border">
                            <a href="{{ route('admin.roles.edit',$role) }}" class="text-blue-600">ویرایش</a>
                            @if($role->name !== 'super-admin')
                                <form method="POST" action="{{ route('admin.roles.destroy',$role) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 ml-2" onclick="return confirm('حذف این نقش؟')">حذف</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-3" colspan="3">نقشی وجود ندارد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
