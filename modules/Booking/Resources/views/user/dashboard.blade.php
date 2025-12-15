@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">داشبورد نوبت‌دهی</h1>
        <div class="text-sm text-gray-500">۳۰ روز اخیر</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="p-4 bg-white rounded border">
            <div class="text-sm text-gray-500">کل نوبت‌ها</div>
            <div class="text-2xl font-bold">{{ $total }}</div>
        </div>
        <div class="p-4 bg-white rounded border">
            <div class="text-sm text-gray-500">تایید شده</div>
            <div class="text-2xl font-bold">{{ $confirmed }}</div>
        </div>
        <div class="p-4 bg-white rounded border">
            <div class="text-sm text-gray-500">لغو شده</div>
            <div class="text-2xl font-bold">{{ $canceled }}</div>
        </div>
        <div class="p-4 bg-white rounded border">
            <div class="text-sm text-gray-500">عدم حضور</div>
            <div class="text-2xl font-bold">{{ $noShow }}</div>
        </div>
        <div class="p-4 bg-white rounded border">
            <div class="text-sm text-gray-500">درآمد پرداخت‌شده</div>
            <div class="text-2xl font-bold">{{ number_format($revenue) }}</div>
        </div>
    </div>

    <div class="flex gap-3">
        <a class="px-4 py-2 bg-blue-600 text-white rounded" href="{{ route('user.booking.services.index') }}">مدیریت سرویس‌ها</a>
        <a class="px-4 py-2 bg-green-600 text-white rounded" href="{{ route('user.booking.appointments.index') }}">لیست نوبت‌ها</a>
        <a class="px-4 py-2 bg-gray-700 text-white rounded" href="{{ route('user.booking.settings.edit') }}">تنظیمات</a>
    </div>
</div>
@endsection
