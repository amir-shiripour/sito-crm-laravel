@extends('clientcalls::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('clientcalls.name') !!}</p>
@endsection
