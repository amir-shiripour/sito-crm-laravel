@extends('layouts.user')
@php($title = 'پروفایل '.config('clients.labels.singular'))

@section('content')
    <div class="container mx-auto px‑4 py‑8">
        <h1 class="text‑2xl font‑semibold mb‑6">پروفایل {{ config('clients.labels.singular') }}</h1>

        @if($client)
            <div class="bg‑white shadow rounded‑lg p‑6 mb‑6">
                <div class="grid grid‑cols‑1 md:grid‑cols‑2 gap‑6">
                    <div>
                        <p class="text‑sm font‑medium text‑gray‑600">نام</p>
                        <p class="mt‑1 text‑gray‑900">{{ $client->name }}</p>
                    </div>
                    <div>
                        <p class="text‑sm font‑medium text‑gray‑600">ایمیل</p>
                        <p class="mt‑1 text‑gray‑900">{{ $client->email ?? '‑' }}</p>
                    </div>
                    <div>
                        <p class="text‑sm font‑medium text‑gray‑600">تلفن</p>
                        <p class="mt‑1 text‑gray‑900">{{ $client->phone ?? '‑' }}</p>
                    </div>
                    <div>
                        <p class="text‑sm font‑medium text‑gray‑600">یادداشت‌ها</p>
                        <p class="mt‑1 text‑gray‑900">{{ $client->notes ?? '‑' }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg‑yellow‑100 border‑border‑yellow‑300 text‑yellow‑800 p‑4 rounded‑md">
                هیچ {{ config('clients.labels.singular') }} ای برای شما ثبت نشده است.
            </div>
        @endif

        <a href="{{ route('user.clients.index') }}" class="inline‑flex items‑center px‑4 py‑2 bg‑gray‑300 border border‑transparent rounded‑md font‑semibold text‑gray‑700 hover:bg‑gray‑400 focus:outline‑none focus:ring‑2 focus:ring‑offset‑2 focus:ring‑gray‑500">بازگشت</a>
    </div>
@endsection
