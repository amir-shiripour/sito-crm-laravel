@extends('layouts.user')

@section('title', 'ویرایش نوشته')

@section('content')
    @livewire('contentforge::admin.post-editor', ['post' => $post, 'type' => 'post'])
@endsection
