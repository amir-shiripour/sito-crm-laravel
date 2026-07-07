@extends('layouts.user')

@section('title', 'ایجاد برگه جدید')

@section('content')
    @livewire('contentforge::admin.post-editor', ['type' => 'page'])
@endsection
