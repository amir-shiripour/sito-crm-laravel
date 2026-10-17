@extends('layouts.user')

@section('title', 'مدیریت سفارش‌ها و ماموریت‌های نصاب‌باشی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::orders-manager')
</div>
@endsection
