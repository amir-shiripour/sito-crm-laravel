@extends('layouts.user')

@section('title', 'ویرایش برگه')

@section('content')
    @livewire('contentforge::admin.post-editor', ['post' => $post, 'type' => 'page'])
@endsection
