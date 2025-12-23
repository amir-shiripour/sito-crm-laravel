@extends('layouts.guest')

@section('content')
    <div class="max-w-4xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $service->name }}</h1>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
               href="{{ route('booking.public.index') }}">بازگشت</a>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-2">
            <div><span class="text-gray-500 text-sm">قیمت:</span> {{ number_format($service->base_price) }}</div>
            <div><span class="text-gray-500 text-sm">پرداخت:</span> {{ $service->payment_mode }}</div>
            <div><span class="text-gray-500 text-sm">رزرو آنلاین:</span> {{ $service->online_booking_mode }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b font-medium bg-gray-50 text-gray-900">ارائه‌دهندگان</div>
            <div class="divide-y">
                @foreach($service->serviceProviders as $sp)
                    <div class="p-4">
                        <div class="font-semibold text-gray-900">{{ optional($sp->provider)->name }}</div>
                        <div class="text-xs text-gray-500">وضعیت فعال: {{ $sp->is_active ? 'بله' : 'خیر' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-sm text-gray-600 space-y-2">
            <p>برای دریافت اسلات‌ها، از API زیر استفاده کنید:</p>
            <pre
                class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">GET /api/booking/availability/slots?service_id={{ $service->id }}&provider_id=&from_local_date=YYYY-MM-DD&to_local_date=YYYY-MM-DD</pre>
        </div>
    </div>
@endsection
