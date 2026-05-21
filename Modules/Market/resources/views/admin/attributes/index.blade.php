@extends('layouts.user')

@section('title', 'مدیریت ویژگی‌های تنوع‌ساز')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @livewire('market::admin.attribute-manager')
    </div>
@endsection
