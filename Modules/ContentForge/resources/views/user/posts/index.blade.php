@extends('layouts.user')

@section('title', 'مدیریت نوشته‌ها')

@section('content')
    @livewire('contentforge::admin.post-list', ['type' => 'post'])
@endsection
