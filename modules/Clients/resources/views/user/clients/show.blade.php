@extends('layouts.user')
@php($title = 'مشاهده '.config('clients.labels.singular'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold mb-6">مشاهده {{ config('clients.labels.singular') }}</h1>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap‑6">
                <div>
                    <p class="text-sm font-medium text-gray-600">نام</p>
                    <p class="mt-1 text-gray-900">{{ $client->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">ایمیل</p>
                    <p class="mt‑1 text‑gray‑900">{{ $client->email ?? '‑' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray‑600">تلفن</p>
                    <p class="mt‑1 text‑gray‑900">{{ $client->phone ?? '‑' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray‑600">یادداشت‌ها</p>
                    <p class="mt‑1 text‑gray‑900">{{ $client->notes ?? '‑' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray‑600">ایجاد شده توسط</p>
                    <p class="mt‑1 text‑gray‑900">{{ optional($client->creator)->name ?? '‑' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray‑600">تاریخ ایجاد</p>
                    <p class="mt‑1 text‑gray‑900">{{ $client->created_at->format('Y‑m‑d H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x‑4">
            <a href="{{ route('user.clients.index') }}" class="inline‑flex items‑center px‑4 py‑2 bg‑gray‑300 border border‑transparent rounded‑md font‑semibold text‑gray‑700 hover:bg‑gray‑400 focus:outline‑none focus:ring‑2 focus:ring‑offset‑2 focus:ring‐gray‑500">بازگشت به لیست</a>
            <a href="{{ route('user.clients.edit', $client) }}" class="inline‑flex items‑center px‑4 py‑2 bg‑yellow‑600 border border‑transparent rounded‑md font‑semibold text‑white hover:bg‑yellow‑700 focus:outline‑none focus:ring‑2 focus:ring‑offset‑2 focus:ring‐yellow‑500">ویرایش</a>
            <form action="{{ route('user.clients.destroy', $client) }}" method="POST" class="inline‑block" onsubmit="return confirm('آیا مطمئن هستید؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline‑flex items‑center px‑4 py‑2 bg‑red‑600 border border‑transparent rounded‑md font‑semibold text‑white hover:bg‑red‑700 focus:outline‑none focus:ring‑2 focus:ring‑offset‑2 focus:ring‑red‑500">حذف</button>
            </form>
        </div>
    </div>
@endsection
