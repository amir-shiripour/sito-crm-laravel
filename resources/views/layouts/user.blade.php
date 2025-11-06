{{-- layouts/user.blade.php --}}
@extends('layouts.panel')

@php($pageTitle = $title ?? 'حساب کاربری')

@section('panel.title', $pageTitle)

@section('panel.sidebar')
    @include('user.partials.sidebar')
@endsection

@section('panel.topbar')
    @include('user.partials.topbar', ['title' => $pageTitle])
@endsection

@section('panel.flash')
    @include('panel.partials.flash')
@endsection

@section('panel.content')
    @if (View::hasSection('content'))
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif
@endsection

@section('panel.footer')
    <span>© {{ date('Y') }} {{ config('app.name', 'CRM') }}</span>
@endsection
