@extends('layouts.guest')

@section('content')
<div class="max-w-3xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">رزرو آنلاین</h1>
    <p class="text-gray-600 text-sm">این UI حداقلی است. برای فلو کامل (hold -> login -> فرم -> پرداخت) از API استفاده کنید.</p>

    <div class="bg-white rounded border overflow-hidden">
        <div class="p-4 border-b font-medium">سرویس‌ها</div>
        <div class="divide-y">
            @foreach($services as $srv)
                <a class="block p-4 hover:bg-gray-50" href="{{ route('booking.public.service', $srv) }}">
                    <div class="font-medium">{{ $srv->name }}</div>
                    <div class="text-xs text-gray-500">{{ $srv->payment_mode }} • {{ $srv->online_booking_mode }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
