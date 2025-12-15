@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="space-y-1">
                <h1 class="text-xl font-bold">ویرایش سرویس #{{ $service->id }}</h1>
                <div class="text-sm text-gray-500">{{ $service->name }}</div>
            </div>

            <div class="flex items-center gap-2">
                <a class="px-3 py-1.5 text-sm rounded border border-gray-300 hover:bg-gray-50"
                   href="{{ route('user.booking.services.index') }}">
                    بازگشت به لیست سرویس‌ها
                </a>

                <a class="px-3 py-1.5 text-sm rounded border border-blue-500 text-blue-600 hover:bg-blue-50"
                   href="{{ route('user.booking.services.availability.edit', $service) }}">
                    برنامه زمانی این سرویس
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('user.booking.services.update', $service) }}" class="bg-white rounded border p-4 space-y-4">
            @csrf
            @include('booking::user.services._form', ['service' => $service])
            <div class="pt-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded">ذخیره</button>
            </div>
        </form>
    </div>
@endsection
