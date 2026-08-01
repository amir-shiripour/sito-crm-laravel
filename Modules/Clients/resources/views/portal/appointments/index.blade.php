@extends('clients::layouts.client')

@php
    $title = 'نوبت‌های من';
@endphp

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                مدیریت نوبت‌ها
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده و پیگیری تمامی نوبت‌های ثبت‌شده شما</p>
        </div>
    </div>

    {{-- فیلتر وضعیت --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm overflow-x-auto">
        <div class="flex items-center gap-2 min-w-max">
            <a href="{{ route('client.appointments.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ !request('status') || request('status') === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">همه نوبت‌ها</a>
            <a href="{{ route('client.appointments.index', ['status' => 'PENDING_PAYMENT']) }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ request('status') === 'PENDING_PAYMENT' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">نیازمند پرداخت</a>
            <a href="{{ route('client.appointments.index', ['status' => 'CONFIRMED']) }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ request('status') === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">تایید شده</a>
            <a href="{{ route('client.appointments.index', ['status' => 'DONE']) }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ request('status') === 'DONE' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">انجام شده</a>
        </div>
    </div>

    @if($appointments->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center">
            <div class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">نوبتی یافت نشد</h3>
            <p class="text-gray-500 dark:text-gray-400">شما هنوز هیچ نوبتی در سیستم ثبت نکرده‌اید و یا نوبتی با این وضعیت وجود ندارد.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($appointments as $appointment)
                @php
                    $appStatusColor = match($appointment->status) {
                        'CONFIRMED' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                        'PENDING_PAYMENT' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                        'CANCELED_BY_CLIENT', 'CANCELED_BY_ADMIN' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        'DONE' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                    };

                    $appStatusLabel = match($appointment->status) {
                        'CONFIRMED' => 'تایید شده',
                        'PENDING' => 'در انتظار بررسی',
                        'PENDING_PAYMENT' => 'در انتظار پرداخت',
                        'CANCELED_BY_CLIENT' => 'لغو توسط شما',
                        'CANCELED_BY_ADMIN' => 'لغو سیستمی',
                        'DONE' => 'انجام شده',
                        'RESCHEDULED' => 'تغییر زمان',
                        'NO_SHOW' => 'عدم حضور',
                        'DRAFT' => 'پیش‌نویس',
                        default => $appointment->status
                    };
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $appointment->service?->name ?? 'سرویس نامشخص' }}</h3>
                            <span class="text-xs px-2 py-1 rounded-lg {{ $appStatusColor }} font-medium">
                                {{ $appStatusLabel }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-6">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span>
                                @php
                                    $tz = config('booking.timezones.display_default', 'Asia/Tehran');
                                    $startLocal = $appointment->start_at_utc?->copy()->timezone($tz);
                                @endphp
                                {{ $startLocal ? \Morilog\Jalali\Jalalian::fromDateTime($startLocal)->format('Y/m/d') : '—' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span dir="ltr">{{ $startLocal ? $startLocal->format('H:i') : '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>{{ $appointment->provider?->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-4 mt-auto">
                        <div class="text-sm">
                            <span class="text-gray-500">شماره پیگیری: </span>
                            <span class="font-bold text-gray-900 dark:text-white">#{{ $appointment->id }}</span>
                        </div>
                        <a href="{{ route('client.appointments.show', $appointment->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-xl text-sm font-medium transition-colors">
                            مشاهده جزئیات
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @endif
</div>
@endsection
