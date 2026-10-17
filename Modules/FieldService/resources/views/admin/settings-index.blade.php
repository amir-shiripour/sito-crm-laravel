@extends('layouts.user')

@section('title', 'تنظیمات سامانه نصاب‌باشی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::settings')
</div>
@endsection
