@extends('layouts.guest')

@section('content')
    <div class="max-w-4xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">رزرو آنلاین</h1>
                <p class="text-gray-500 text-sm mt-1">UI ساده جهت تست؛ فلو کامل از API و فرانت سفارشی استفاده شود.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b font-medium text-gray-900 bg-gray-50">سرویس‌ها</div>
            <div class="divide-y">
                @foreach($services as $srv)
                    <a class="block p-4 hover:bg-gray-50 transition" href="{{ route('booking.public.service', $srv) }}">
                        <div class="font-semibold text-gray-900">{{ $srv->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            پرداخت: {{ $srv->payment_mode }} • رزرو آنلاین: {{ $srv->online_booking_mode }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
