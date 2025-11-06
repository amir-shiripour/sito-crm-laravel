{{-- layouts/admin.blade.php --}}
@extends('layouts.panel')

@section('panel.title', $title ?? 'پنل مدیریت')

@section('panel.sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('panel.topbar')
    @include('admin.partials.topbar', ['title' => $title ?? 'پنل مدیریت'])
@endsection

@section('panel.flash')
    @include('panel.flash')
@endsection

@section('panel.content')
    @if (View::hasSection('content'))
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif
@endsection

@section('panel.footer')
    <span>© {{ date('Y') }} {{ config('app.name', 'CRM') }} Admin</span>
@endsection
