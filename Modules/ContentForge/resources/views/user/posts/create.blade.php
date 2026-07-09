@extends('layouts.user')

@section('title', 'ایجاد نوشته جدید')

@section('content')
    @livewire('contentforge::admin.post-editor', ['type' => 'post'])
@endsection
