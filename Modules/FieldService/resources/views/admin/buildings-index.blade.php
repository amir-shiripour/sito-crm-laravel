@extends('layouts.user')

@section('title', 'مدیریت مجتمع‌ها و شناسنامه تاسیسات (B2B)')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::buildings-manager')
</div>
@endsection
