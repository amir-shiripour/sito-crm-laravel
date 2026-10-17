@extends('layouts.user')

@section('title', 'تسویه حساب‌های بانکی پایا')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::settlements-manager')
</div>
@endsection
