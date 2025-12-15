@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">ایجاد سرویس</h1>
        <a class="text-blue-600 hover:underline" href="{{ route('user.booking.services.index') }}">بازگشت</a>
    </div>

    <form method="POST" action="{{ route('user.booking.services.store') }}" class="bg-white rounded border p-4 space-y-4">
        @csrf
        @include('booking::user.services._form', ['service' => new \Modules\Booking\Entities\BookingService()])
        <div class="pt-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">ثبت</button>
        </div>
    </form>
</div>
@endsection
