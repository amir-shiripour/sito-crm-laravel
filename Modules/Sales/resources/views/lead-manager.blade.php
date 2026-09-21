@extends('layouts.user')

@php
    $title = 'مدیریت لیدهای فروش';
@endphp

@section('content')
    <div class="space-y-5 pb-10 font-sans" dir="rtl">
        {{-- کامپوننت اصلی مدیریت لیدهای فروش --}}
        @livewire('sales::campaign-lead-manager')
    </div>
@endsection
