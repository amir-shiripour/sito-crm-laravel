@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">نوبت‌ها</h1>
            <a class="px-4 py-2 bg-blue-600 text-white rounded" href="{{ route('user.booking.appointments.create') }}">
                ثبت نوبت جدید
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded border overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">سرویس</th>
                    <th class="p-3 text-right">ارائه‌دهنده</th>
                    <th class="p-3 text-right">مشتری</th>
                    <th class="p-3 text-right">تاریخ و ساعت نوبت (شمسی)</th>
                    <th class="p-3 text-right">وضعیت</th>
                </tr>
                </thead>
                <tbody>
                @foreach($appointments as $a)
                    @php
                        /** @var \Modules\Booking\Entities\Appointment $a */
                        $startJalali = $a->start_at_utc
                            ? \Morilog\Jalali\Jalalian::fromDateTime($a->start_at_utc)->format('Y/m/d H:i')
                            : '';
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">{{ $a->id }}</td>
                        <td class="p-3">{{ optional($a->service)->name }}</td>
                        <td class="p-3">{{ optional($a->provider)->name }}</td>
                        <td class="p-3">{{ optional($a->client)->full_name }}</td>
                        <td class="p-3 font-mono">{{ $startJalali }}</td>
                        <td class="p-3">{{ $a->status }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $appointments->links() }}
        </div>
    </div>
@endsection
