@extends('layouts.user')

@section('title', 'مدیریت سفارش‌ها و مأموریت‌ها')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::orders-manager')
</div>
@endsection
