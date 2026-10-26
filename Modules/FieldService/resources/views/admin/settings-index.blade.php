@extends('layouts.user')

@section('title', 'تنظیمات سامانه خدمات فنی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::settings')
</div>
@endsection
