@extends('layouts.user')
@php($title = 'پروفایل '.config('clients.labels.singular'))

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">
                {{ 'پروفایل ' . config('clients.labels.singular', 'مشتری') }}
            </h1>
        </div>

        @if($client)
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><div class="text-sm text-gray-500">یوزرنیم</div><div class="font-medium">{{ $client->username }}</div></div>
                <div><div class="text-sm text-gray-500">نام کامل</div><div class="font-medium">{{ $client->full_name }}</div></div>
                <div><div class="text-sm text-gray-500">ایمیل</div><div class="font-medium dir-ltr">{{ $client->email ?? '—' }}</div></div>
                <div><div class="text-sm text-gray-500">تلفن</div><div class="font-medium">{{ $client->phone ?? '—' }}</div></div>
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">یادداشت</div>
                    <div class="font-medium whitespace-pre-wrap">{{ $client->notes ?? '—' }}</div>
                </div>
            </div>
        @else
            <div class="p-6 text-gray-500">کلاینتی یافت نشد.</div>
        @endif
    </div>
@endsection
