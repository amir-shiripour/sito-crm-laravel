@extends('layouts.user')

@section('title', 'مدیریت برگه‌ها')

@section('content')
    @livewire('contentforge::admin.post-list', ['type' => 'page'])
@endsection
