@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">برنامه زمانی ارائه‌دهندگان</h1>
            <a href="{{ route('user.booking.dashboard') }}" class="text-sm text-blue-600 hover:underline">
                بازگشت به نوبت‌دهی
            </a>
        </div>

        <div class="bg-white rounded border p-4 space-y-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs mb-1">جستجو در نام / ایمیل / موبایل</label>
                    <input type="text"
                           name="q"
                           value="{{ $q ?? '' }}"
                           class="w-full border rounded p-2 text-sm"
                           placeholder="مثلاً دکتر احمدی">
                </div>
                <div>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">
                        جستجو
                    </button>
                </div>
            </form>

            <div class="border rounded overflow-hidden mt-2">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-right">#</th>
                        <th class="p-2 text-right">نام</th>
                        <th class="p-2 text-right">ایمیل</th>
                        <th class="p-2 text-right">موبایل</th>
                        <th class="p-2 text-right">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($providers as $user)
                        <tr class="border-t">
                            <td class="p-2">{{ $user->id }}</td>
                            <td class="p-2">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->email }}</td>
                            <td class="p-2">{{ $user->phone ?? '-' }}</td>
                            <td class="p-2">
                                <a href="{{ route('user.booking.providers.availability.edit', $user) }}"
                                   class="text-xs px-3 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                                    برنامه زمانی
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500 text-sm">
                                هیچ کاربری یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $providers->links() }}
        </div>
    </div>
@endsection
